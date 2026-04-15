<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashRequest;
use App\Models\CreditNote;
use App\Models\Expense;
use App\Models\FuelLog;
use App\Models\Genset;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\MaintenanceRecord;
use App\Models\PurchaseOrder;
use App\Models\QuotationItemType;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

/**
 * JournalEntryService
 *
 * Central accounting engine. Every financial event that touches money should
 * call a method here. Methods silently return null if system accounts don't
 * exist yet — so existing functionality never breaks before COA is seeded.
 */
class JournalEntryService
{
    // ─── Cached system account IDs ───────────────────────────────────

    private function account(string $code): ?Account
    {
        return Account::where('code', $code)->where('is_active', true)->first();
    }

    // ─── Core JE builder ─────────────────────────────────────────────

    /**
     * Create and immediately post a journal entry with multiple lines.
     *
     * $lines = [
     *   ['account_code' => '1140', 'debit' => 500, 'credit' => 0,   'description' => '...'],
     *   ['account_code' => '4100', 'debit' => 0,   'credit' => 423, 'description' => '...'],
     *   ...
     * ]
     */
    public function createAndPost(
        string $description,
        string $sourceType,
        int $sourceId,
        array $lines,
        ?string $date = null,
        ?int $userId = null,
        ?string $reference = null
    ): ?JournalEntry {
        // Resolve account IDs — if any account is missing, abort gracefully
        $resolvedLines = [];
        foreach ($lines as $line) {
            $account = $this->account($line['account_code']);
            if (!$account) return null;  // COA not set up yet — skip silently
            $resolvedLines[] = [
                'account_id'  => $account->id,
                'description' => $line['description'] ?? null,
                'debit'       => (float) ($line['debit'] ?? 0),
                'credit'      => (float) ($line['credit'] ?? 0),
            ];
        }

        // Validate balance
        $totalDebit  = array_sum(array_column($resolvedLines, 'debit'));
        $totalCredit = array_sum(array_column($resolvedLines, 'credit'));
        if (round($totalDebit, 2) !== round($totalCredit, 2)) return null;

        return DB::transaction(function () use ($description, $sourceType, $sourceId, $resolvedLines, $date, $userId, $reference) {
            $je = JournalEntry::create([
                'entry_date'  => $date ?? now()->toDateString(),
                'description' => $description,
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
                'reference'   => $reference,
                'status'      => 'draft',
                'created_by'  => $userId,
            ]);

            foreach ($resolvedLines as $line) {
                $je->lines()->create($line);
            }

            $je->post();

            return $je->fresh();
        });
    }

    // ─── INVOICE — Revenue Recognition ───────────────────────────────

    /**
     * Build revenue/AR lines for an invoice, splitting by item type.
     *   genset_rental / extra_days / damage / penalty / credit → 4100
     *   delivery → 4110
     *   fuel / maintenance / other → 4120
     * If $reverse=true the debits/credits are swapped (for void reversal).
     */
    private function buildInvoiceJeLines(Invoice $invoice, bool $reverse = false): array
    {
        $invoice->loadMissing('items');

        // All JE amounts are in TZS (functional currency).
        // For USD invoices, multiply by the locked exchange rate.
        $rate  = (float) ($invoice->exchange_rate_to_tzs ?? 1.0);
        $vat   = round((float) $invoice->vat_amount  * $rate, 2);
        $total = round((float) $invoice->total_amount * $rate, 2);

        $currencyNote = $invoice->currency !== 'TZS'
            ? " [{$invoice->currency} @ {$rate}]"
            : '';

        // Classify each item type from DB so new types are picked up automatically.
        // delivery → 4110 | fuel/maintenance → 4120 | rental flag → 4100 | fallback → 4120
        $itemTypeMeta = QuotationItemType::pluck('is_rental', 'key')->toArray();

        $deliveryRevenue = 0.0;
        $otherRevenue    = 0.0;

        foreach ($invoice->items as $item) {
            $amt = round((float) $item->subtotal * $rate, 2);
            if ($item->item_type === 'delivery') {
                $deliveryRevenue += $amt;
            } elseif (!($itemTypeMeta[$item->item_type] ?? false)) {
                // Non-rental, non-delivery → other income (4120)
                $otherRevenue += $amt;
            }
            // Rental types fall through — picked up as remainder below
        }

        // Rental revenue is the remainder — guarantees the JE always balances
        $rentalRevenue = round($total - $vat - $deliveryRevenue - $otherRevenue, 2);

        $dr = $reverse ? 0 : 1;
        $cr = $reverse ? 1 : 0;

        $lines = [];
        $lines[] = ['account_code' => '1140', 'debit' => $total * $dr, 'credit' => $total * $cr,
                    'description'  => ($reverse ? 'AR reversed — ' : 'AR — ') . $invoice->invoice_number . $currencyNote];

        if ($rentalRevenue > 0.005) {
            $lines[] = ['account_code' => '4100',
                        'debit'        => round($rentalRevenue, 2) * $cr,
                        'credit'       => round($rentalRevenue, 2) * $dr,
                        'description'  => ($reverse ? 'Rental void — ' : 'Rental — ') . $invoice->invoice_number . $currencyNote];
        }
        if ($deliveryRevenue > 0.005) {
            $lines[] = ['account_code' => '4110',
                        'debit'        => round($deliveryRevenue, 2) * $cr,
                        'credit'       => round($deliveryRevenue, 2) * $dr,
                        'description'  => ($reverse ? 'Delivery void — ' : 'Delivery — ') . $invoice->invoice_number . $currencyNote];
        }
        if ($otherRevenue > 0.005) {
            $lines[] = ['account_code' => '4120',
                        'debit'        => round($otherRevenue, 2) * $cr,
                        'credit'       => round($otherRevenue, 2) * $dr,
                        'description'  => ($reverse ? 'Other void — ' : 'Other income — ') . $invoice->invoice_number . $currencyNote];
        }
        if ($vat > 0) {
            $lines[] = ['account_code' => '2120',
                        'debit'        => $vat * $cr,
                        'credit'       => $vat * $dr,
                        'description'  => ($reverse ? 'VAT reversed — ' : 'VAT — ') . $invoice->invoice_number . $currencyNote];
        }

        return $lines;
    }

    /**
     * When an invoice is marked SENT:
     *   DR 1140 Accounts Receivable   (total_amount)
     *   CR 4100 Rental Income         (rental items)
     *   CR 4110 Delivery Income       (delivery items)
     *   CR 4120 Other Income          (other items)
     *   CR 2120 VAT Payable           (vat_amount — if VAT > 0)
     */
    public function onInvoiceSent(Invoice $invoice): ?JournalEntry
    {
        return $this->createAndPost(
            "Invoice {$invoice->invoice_number} sent",
            'invoice',
            $invoice->id,
            $this->buildInvoiceJeLines($invoice, false),
            $invoice->issue_date?->toDateString(),
            $invoice->created_by
        );
    }

    /**
     * When a sent invoice is voided — reverses the sent JE:
     *   DR 4100/4110/4120 Revenue     (reverse)
     *   DR 2120 VAT Payable           (reverse)
     *   CR 1140 Accounts Receivable   (reverse)
     */
    public function onInvoiceVoided(Invoice $invoice): ?JournalEntry
    {
        if ((float) $invoice->total_amount <= 0) return null;

        return $this->createAndPost(
            "Invoice voided — {$invoice->invoice_number}",
            'invoice',
            $invoice->id,
            $this->buildInvoiceJeLines($invoice, true),
            now()->toDateString(),
            auth()->id()
        );
    }

    // ─── PAYMENT — Cash Receipt ───────────────────────────────────────

    /**
     * When a payment is recorded against an invoice:
     *   DR bank_account COA account  (amount)
     *   CR 1140 Accounts Receivable  (amount)
     */
    public function onPaymentRecorded(InvoicePayment $payment): ?JournalEntry
    {
        $bankAccount = $payment->bankAccount;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $ar = $this->account('1140');
        if (!$ar) return null;

        // Amount in TZS (functional currency) — convert if invoice is USD
        $invoice      = $payment->invoice;
        $rate         = $invoice ? (float) ($invoice->exchange_rate_to_tzs ?? 1.0) : 1.0;
        $amountInTzs  = round((float) $payment->amount * $rate, 2);
        $currencyNote = ($invoice && $invoice->currency !== 'TZS')
            ? " [{$invoice->currency} @ {$rate}]"
            : '';

        $lines = [
            ['account_code' => $bankCoa->code, 'debit' => $amountInTzs, 'credit' => 0,              'description' => 'Receipt — ' . ($payment->receipt_number ?? '') . $currencyNote],
            ['account_code' => '1140',          'debit' => 0,            'credit' => $amountInTzs,   'description' => 'AR settled — ' . ($invoice->invoice_number ?? $payment->invoice_id) . $currencyNote],
        ];

        $je = $this->createAndPost(
            "Payment received on invoice " . ($invoice->invoice_number ?? $payment->invoice_id),
            'payment',
            $payment->id,
            $lines,
            $payment->payment_date->toDateString(),
            $payment->recorded_by
        );

        if ($je) {
            // Increment bank balance in the account's own currency.
            // If the bank account holds the same currency as the invoice (e.g. both USD),
            // store the native payment amount. Otherwise store the TZS equivalent.
            $bankIncrement = ($bankAccount->currency !== 'TZS' && $invoice && $bankAccount->currency === $invoice->currency)
                ? round((float) $payment->amount, 2)
                : $amountInTzs;

            BankAccount::where('id', $bankAccount->id)->increment('current_balance', $bankIncrement);
        }

        return $je;
    }

    // ─── HISTORICAL SALE — Cash Sale (no AR leg) ─────────────────────

    /**
     * For historical bookings where cash is received immediately — no AR step:
     *   DR [bank COA account]   (total_amount in TZS)
     *   CR 4100 Rental Income   (rental items)
     *   CR 4110 Delivery Income (delivery items, if any)
     *   CR 4120 Other Income    (other items, if any)
     *   CR 2120 VAT Payable     (vat_amount — if not zero-rated)
     */
    public function onHistoricalSale(Invoice $invoice, BankAccount $bankAccount): ?JournalEntry
    {
        if (!$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $invoice->loadMissing('items');

        $rate  = (float) ($invoice->exchange_rate_to_tzs ?? 1.0);
        $vat   = round((float) $invoice->vat_amount  * $rate, 2);
        $total = round((float) $invoice->total_amount * $rate, 2);

        if ($total <= 0) return null;

        $currencyNote = $invoice->currency !== 'TZS'
            ? " [{$invoice->currency} @ {$rate}]"
            : '';

        $itemTypeMeta = QuotationItemType::pluck('is_rental', 'key')->toArray();

        $deliveryRevenue = 0.0;
        $otherRevenue    = 0.0;

        foreach ($invoice->items as $item) {
            $amt = round((float) $item->subtotal * $rate, 2);
            if ($item->item_type === 'delivery') {
                $deliveryRevenue += $amt;
            } elseif (!($itemTypeMeta[$item->item_type] ?? false)) {
                $otherRevenue += $amt;
            }
        }

        $rentalRevenue = round($total - $vat - $deliveryRevenue - $otherRevenue, 2);

        $lines = [];
        $lines[] = [
            'account_code' => $bankCoa->code,
            'debit'        => $total,
            'credit'       => 0,
            'description'  => 'Historical cash sale — ' . $invoice->invoice_number . $currencyNote,
        ];

        if ($rentalRevenue > 0.005) {
            $lines[] = [
                'account_code' => '4100',
                'debit'        => 0,
                'credit'       => round($rentalRevenue, 2),
                'description'  => 'Rental income — ' . $invoice->invoice_number . $currencyNote,
            ];
        }
        if ($deliveryRevenue > 0.005) {
            $lines[] = [
                'account_code' => '4110',
                'debit'        => 0,
                'credit'       => round($deliveryRevenue, 2),
                'description'  => 'Delivery income — ' . $invoice->invoice_number . $currencyNote,
            ];
        }
        if ($otherRevenue > 0.005) {
            $lines[] = [
                'account_code' => '4120',
                'debit'        => 0,
                'credit'       => round($otherRevenue, 2),
                'description'  => 'Other income — ' . $invoice->invoice_number . $currencyNote,
            ];
        }
        if ($vat > 0) {
            $lines[] = [
                'account_code' => '2120',
                'debit'        => 0,
                'credit'       => $vat,
                'description'  => 'VAT payable — ' . $invoice->invoice_number . $currencyNote,
            ];
        }

        $je = $this->createAndPost(
            "Historical sale — " . $invoice->invoice_number,
            'payment',
            $invoice->id,
            $lines,
            $invoice->issue_date?->toDateString() ?? now()->toDateString(),
            $invoice->created_by
        );

        if ($je) {
            $bankIncrement = ($bankAccount->currency !== 'TZS' && $invoice->currency === $bankAccount->currency)
                ? round((float) $invoice->total_amount, 2)
                : $total;

            BankAccount::where('id', $bankAccount->id)->increment('current_balance', $bankIncrement);
        }

        return $je;
    }

    // ─── PURCHASE ORDER — Inventory Receipt ──────────────────────────

    /**
     * When a PO is marked received.
     * $receivedItems holds per-item routing: [['account_code'=>'1150','value'=>100.0], ...]
     * If an inventory category has a linked COA account (e.g. 5110 for Fuel),
     * that account is used instead of 1150 Inventory Asset.
     *
     *   DR [category COA / 1150]  (per item value)
     *   CR 2110 Accounts Payable  (total)
     */
    public function onPurchaseOrderReceived(PurchaseOrder $po, float $receivedValue = 0, array $receivedItems = []): ?JournalEntry
    {
        $total = $receivedValue > 0 ? round($receivedValue, 2) : (float) $po->total_value;
        if ($total <= 0) return null;

        $isPartial = $po->status === 'partial';
        $label     = $isPartial ? 'Stock received (partial)' : 'Stock received';

        $lines = [];

        if (!empty($receivedItems)) {
            // Group debit lines by account code
            $byAccount = [];
            foreach ($receivedItems as $ri) {
                $code = $ri['account_code'];
                $byAccount[$code] = ($byAccount[$code] ?? 0) + $ri['value'];
            }
            foreach ($byAccount as $code => $value) {
                if ($value > 0.005) {
                    $lines[] = ['account_code' => $code, 'debit' => round($value, 2), 'credit' => 0,
                                'description'  => "Stock in — {$po->po_number}"];
                }
            }
        } else {
            $lines[] = ['account_code' => '1150', 'debit' => $total, 'credit' => 0,
                        'description'  => "Inventory in — {$po->po_number}"];
        }

        // Single AP credit
        $lines[] = ['account_code' => '2110', 'debit' => 0, 'credit' => $total,
                    'description'  => 'AP — ' . ($po->supplier->name ?? $po->supplier_id)];

        return $this->createAndPost(
            "{$label} — PO {$po->po_number}",
            'purchase_order',
            $po->id,
            $lines,
            now()->toDateString(),
            auth()->id()
        );
    }

    // ─── SUPPLIER PAYMENT — AP Settlement ────────────────────────────

    /**
     * When a supplier is paid:
     *   DR 2110 Accounts Payable   (gross amount)
     *   CR bank_account COA        (net = amount - withholding_tax)
     *   CR 2130 WHT Payable        (withholding_tax — if > 0)
     */
    public function onSupplierPayment(SupplierPayment $sp): ?JournalEntry
    {
        $bankAccount = $sp->bankAccount;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $gross = (float) $sp->amount;
        $wht   = (float) ($sp->withholding_tax ?? 0);
        $net   = round($gross - $wht, 2);

        $lines = [
            ['account_code' => '2110',         'debit' => $gross, 'credit' => 0,    'description' => "AP settled — {$sp->supplier->name}"],
            ['account_code' => $bankCoa->code, 'debit' => 0,      'credit' => $net, 'description' => "Bank out — {$sp->payment_number}"],
        ];
        if ($wht > 0) {
            $lines[] = ['account_code' => '2130', 'debit' => 0, 'credit' => $wht,
                        'description'  => "WHT withheld — {$sp->payment_number}"];
        }

        $je = $this->createAndPost(
            "Supplier payment — {$sp->payment_number} to {$sp->supplier->name}",
            'supplier_payment',
            $sp->id,
            $lines,
            $sp->payment_date->toDateString(),
            $sp->created_by
        );

        if ($je) {
            // Decrement bank balance by net amount paid out
            BankAccount::where('id', $bankAccount->id)->decrement('current_balance', $net);
        }

        return $je;
    }

    // ─── EXPENSE — Operational Cost ──────────────────────────────────

    /**
     * When an expense is posted:
     *   DR expense_category COA account  (total_amount)
     *   CR bank_account COA              (total_amount)
     */
    public function onExpensePosted(Expense $expense): ?JournalEntry
    {
        $bankAccount = $expense->bankAccount;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $expenseAccount = $expense->category?->account;
        if (!$expenseAccount) return null;

        $amount = round((float) $expense->amount, 2);
        $vat    = round((float) ($expense->vat_amount ?? 0), 2);
        $total  = round((float) $expense->total_amount, 2);

        $lines = [
            ['account_code' => $expenseAccount->code, 'debit' => $amount, 'credit' => 0,
             'description'  => $expense->description],
        ];

        if ($vat > 0 && !$expense->is_zero_rated) {
            $vatInput = $this->account('1180');
            if ($vatInput) {
                $lines[] = ['account_code' => '1180', 'debit' => $vat, 'credit' => 0,
                            'description'  => "VAT input — {$expense->expense_number}"];
            } else {
                // Fallback: fold VAT into expense line if 1180 not seeded yet
                $lines[0]['debit'] = $total;
            }
        }

        $lines[] = ['account_code' => $bankCoa->code, 'debit' => 0, 'credit' => $total,
                    'description'  => "Payment via {$bankAccount->name}"];

        $je = $this->createAndPost(
            "Expense — {$expense->expense_number}: {$expense->description}",
            'expense',
            $expense->id,
            $lines,
            $expense->expense_date->toDateString(),
            $expense->created_by
        );

        if ($je) {
            BankAccount::where('id', $bankAccount->id)->decrement('current_balance', $total);
            $expense->update(['journal_entry_id' => $je->id, 'status' => 'posted']);
        }

        return $je;
    }

    // ─── FUEL LOG — Auto Expense + JE ────────────────────────────────

    /**
     * When a fuel log is saved (if bank_account_id is set on the log):
     *   DR 5110 Fuel Expense   (total_cost)
     *   CR bank_account COA    (total_cost)
     *
     * Also auto-creates an Expense record linked to this fuel log.
     */
    public function onFuelLogged(FuelLog $fuelLog, ?int $bankAccountId = null): ?JournalEntry
    {
        $bankAccount = $bankAccountId ? BankAccount::find($bankAccountId) : null;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $total = (float) $fuelLog->total_cost;
        if ($total <= 0) return null;

        // Create the Expense record
        $fuelCat = \App\Models\ExpenseCategory::whereHas('account', fn($q) => $q->where('code', '5110'))->first();
        $expense = Expense::create([
            'expense_category_id' => $fuelCat?->id,
            'bank_account_id'     => $bankAccount->id,
            'description'         => "Fuel — {$fuelLog->genset->asset_number} — " . $fuelLog->fuelled_at->format('d M Y'),
            'amount'              => $total,
            'total_amount'        => $total,
            'expense_date'        => $fuelLog->fuelled_at->toDateString(),
            'source_type'         => 'fuel_log',
            'source_id'           => $fuelLog->id,
            'status'              => 'approved',
            'created_by'          => $fuelLog->created_by,
        ]);

        return $this->onExpensePosted($expense);
    }

    // ─── MAINTENANCE — Auto Expense + JE ─────────────────────────────

    /**
     * When a maintenance record is completed with a cost:
     *   DR 5120 Maintenance Expense  (cost)
     *   CR 2110 AP                   (cost)   [if payable to supplier]
     *   — or —
     *   CR bank_account COA          (cost)   [if paid directly]
     */
    public function onMaintenanceCompleted(MaintenanceRecord $record, ?int $bankAccountId = null): ?JournalEntry
    {
        $cost = (float) $record->cost;
        if ($cost <= 0) return null;

        $maintCat = \App\Models\ExpenseCategory::whereHas('account', fn($q) => $q->where('code', '5120'))->first();

        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
            if (!$bankAccount || !$bankAccount->account_id) return null;
            $bankCoa = Account::find($bankAccount->account_id);
            if (!$bankCoa) return null;

            $expense = Expense::create([
                'expense_category_id' => $maintCat?->id,
                'bank_account_id'     => $bankAccount->id,
                'description'         => "Maintenance — {$record->maintenance_number}: {$record->title}",
                'amount'              => $cost,
                'total_amount'        => $cost,
                'expense_date'        => ($record->completed_date ?? now())->toDateString(),
                'source_type'         => 'maintenance',
                'source_id'           => $record->id,
                'status'              => 'approved',
                'created_by'          => auth()->id(),
            ]);

            return $this->onExpensePosted($expense);
        }

        // Post to AP (no bank account — charged to supplier)
        $maintAccount = $this->account('5120');
        if (!$maintAccount) return null;

        return $this->createAndPost(
            "Maintenance — {$record->maintenance_number}: {$record->title}",
            'expense',
            $record->id,
            [
                ['account_code' => '5120', 'debit' => $cost, 'credit' => 0,     'description' => $record->title],
                ['account_code' => '2110', 'debit' => 0,      'credit' => $cost, 'description' => 'Maintenance AP'],
            ],
            ($record->completed_date ?? now())->toDateString(),
            auth()->id()
        );
    }

    // ─── CASH REQUEST — Disbursement JE ──────────────────────────────

    /**
     * When a cash request is disbursed (paid):
     *   DR 1160 Staff Advances       (total_amount)
     *   CR [actual bank account COA] (total_amount)
     *
     * Uses the bank_account already saved on the CashRequest.
     */
    public function onCashRequestDisbursed(CashRequest $cr): ?JournalEntry
    {
        $cr->load('bankAccount');
        $bankAccount = $cr->bankAccount;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        return $this->createAndPost(
            "Cash disbursed — {$cr->request_number}: {$cr->purpose}",
            'cash_request',
            $cr->id,
            [
                ['account_code' => '1160',          'debit' => (float) $cr->total_amount, 'credit' => 0,                           'description' => "Advance — {$cr->requestedBy->name}"],
                ['account_code' => $bankCoa->code,  'debit' => 0,                          'credit' => (float) $cr->total_amount,  'description' => "Cash out — {$cr->request_number}"],
            ],
            now()->toDateString(),
            auth()->id()
        );
    }

    /**
     * When a cash request is retired (reconciled with receipts):
     *   DR 5XXX Expense accounts  (actual per item)
     *   CR 1160 Staff Advances    (actual_amount)
     *   DR/CR 1130 Petty Cash     (over/under spent)
     */
    public function onCashRequestRetired(CashRequest $cr): ?JournalEntry
    {
        $cr->loadMissing('items.expenseCategory.account');
        $actual = (float) $cr->actual_amount;
        $disbursed = (float) $cr->total_amount;

        $lines = [];

        // DR each expense category
        foreach ($cr->items as $item) {
            $amt = (float) ($item->actual_amount ?? $item->estimated_amount);
            if ($amt <= 0) continue;
            $acctCode = $item->expenseCategory?->account?->code ?? '5240'; // default to admin
            $lines[] = ['account_code' => $acctCode, 'debit' => $amt, 'credit' => 0, 'description' => $item->description];
        }

        // CR Staff Advances (actual amount spent)
        $lines[] = ['account_code' => '1160', 'debit' => 0, 'credit' => $actual, 'description' => "Advance retired — {$cr->request_number}"];

        // Handle over/under spend
        $diff = round($disbursed - $actual, 2);
        if ($diff > 0) {
            // Under-spent — cash returned to petty cash
            $lines[] = ['account_code' => '1160', 'debit' => 0,     'credit' => $diff, 'description' => 'Under-spend returned'];
            $lines[] = ['account_code' => '1130', 'debit' => $diff, 'credit' => 0,     'description' => 'Cash return to petty cash'];
        } elseif ($diff < 0) {
            // Over-spent — additional charge
            $extra = abs($diff);
            $lines[] = ['account_code' => '5240', 'debit' => $extra, 'credit' => 0,      'description' => 'Over-spend'];
            $lines[] = ['account_code' => '1160', 'debit' => 0,      'credit' => $extra, 'description' => 'Over-spend settled from advance'];
        }

        return $this->createAndPost(
            "Cash request retired — {$cr->request_number}",
            'cash_request',
            $cr->id,
            $lines,
            now()->toDateString(),
            auth()->id()
        );
    }

    // ─── CREDIT NOTE ─────────────────────────────────────────────────

    /**
     * When a credit note is issued:
     *   DR 4100 Revenue (or discount)   (amount)
     *   DR 2120 VAT Payable             (vat_amount)  [if any]
     *   CR 1140 Accounts Receivable     (total_amount)
     */
    public function onCreditNoteIssued(CreditNote $cn): ?JournalEntry
    {
        $amount  = (float) $cn->amount;
        $vat     = (float) $cn->vat_amount;
        $total   = (float) $cn->total_amount;

        $lines = [
            ['account_code' => '4100', 'debit' => $amount, 'credit' => 0,      'description' => "Credit note — {$cn->cn_number}"],
            ['account_code' => '1140', 'debit' => 0,        'credit' => $total, 'description' => "AR reduced — {$cn->cn_number}"],
        ];
        if ($vat > 0) {
            $lines[] = ['account_code' => '2120', 'debit' => $vat, 'credit' => 0, 'description' => "VAT reversed — {$cn->cn_number}"];
        }

        return $this->createAndPost(
            "Credit note issued — {$cn->cn_number}",
            'credit_note',
            $cn->id,
            $lines,
            $cn->issued_date->toDateString(),
            $cn->issued_by
        );
    }

    // ─── STOCK OUT (Maintenance use) ─────────────────────────────────

    /**
     * When inventory items are used in maintenance (stock movement 'out'):
     *   DR 5130 Parts & Consumables  (total cost)
     *   CR 1150 Inventory Asset      (total cost)
     */
    public function onStockUsedForMaintenance(float $cost, int $maintenanceRecordId, ?int $userId = null): ?JournalEntry
    {
        if ($cost <= 0) return null;

        return $this->createAndPost(
            "Parts used in maintenance",
            'expense',
            $maintenanceRecordId,
            [
                ['account_code' => '5130', 'debit' => $cost, 'credit' => 0,     'description' => 'Parts consumed'],
                ['account_code' => '1150', 'debit' => 0,      'credit' => $cost, 'description' => 'Inventory reduced'],
            ],
            now()->toDateString(),
            $userId
        );
    }

    // ─── WRITE-OFF / BAD DEBT ─────────────────────────────────────────

    /**
     * When a disputed invoice is written off:
     *   DR 5320 Bad Debt Expense  (total)
     *   CR 1140 Accounts Receivable (total)
     */
    public function onInvoiceWrittenOff(Invoice $invoice): ?JournalEntry
    {
        $total = (float) ($invoice->total_amount - $invoice->amount_paid);
        if ($total <= 0) return null;

        return $this->createAndPost(
            "Write-off {$invoice->invoice_number}",
            'invoice',
            $invoice->id,
            [
                ['account_code' => '5320', 'debit' => $total, 'credit' => 0,      'description' => 'Bad debt written off'],
                ['account_code' => '1140', 'debit' => 0,       'credit' => $total, 'description' => 'AR written off'],
            ],
            now()->toDateString(),
            auth()->id()
        );
    }

    // ─── GENSET CAPITALIZATION — Fixed Asset ──────────────────────────

    /**
     * When a genset is added to the fleet with a purchase price:
     *   DR 1210 Generator Fleet — Cost  (purchase_price)
     *   CR bank_account COA             (if paid from bank account)
     *   — or —
     *   CR 2210 Loans Payable           (if financed / no bank account)
     */
    public function onGensetCapitalized(Genset $genset, ?int $bankAccountId = null): ?JournalEntry
    {
        $cost = (float) $genset->purchase_price;
        if ($cost <= 0) return null;

        $date = $genset->purchase_date?->toDateString() ?? now()->toDateString();

        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
            if (!$bankAccount || !$bankAccount->account_id) return null;

            $bankCoa = Account::find($bankAccount->account_id);
            if (!$bankCoa) return null;

            $je = $this->createAndPost(
                "Genset capitalized — {$genset->asset_number}",
                'genset',
                $genset->id,
                [
                    ['account_code' => '1210',         'debit' => $cost, 'credit' => 0,     'description' => "Fleet — {$genset->asset_number}: {$genset->name}"],
                    ['account_code' => $bankCoa->code, 'debit' => 0,     'credit' => $cost, 'description' => "Purchase — {$genset->asset_number}"],
                ],
                $date,
                auth()->id()
            );

            if ($je) {
                BankAccount::where('id', $bankAccountId)->decrement('current_balance', $cost);
            }

            return $je;
        }

        // Financed — credit Loans Payable
        return $this->createAndPost(
            "Genset capitalized (financed) — {$genset->asset_number}",
            'genset',
            $genset->id,
            [
                ['account_code' => '1210', 'debit' => $cost, 'credit' => 0,     'description' => "Fleet — {$genset->asset_number}: {$genset->name}"],
                ['account_code' => '2210', 'debit' => 0,     'credit' => $cost, 'description' => "Loan for — {$genset->asset_number}"],
            ],
            $date,
            auth()->id()
        );
    }

    // ─── ACCOUNT TRANSFER ─────────────────────────────────────────────

    /**
     * Internal transfer between two bank/cash accounts.
     * DR destination COA account / CR source COA account.
     */
    public function onAccountTransfer(\App\Models\AccountTransfer $transfer): ?JournalEntry
    {
        $from = BankAccount::with('account')->find($transfer->from_bank_account_id);
        $to   = BankAccount::with('account')->find($transfer->to_bank_account_id);

        if (!$from?->account || !$to?->account) return null;

        $amt  = (float) $transfer->amount;
        $desc = $transfer->description
            ? "Transfer: {$from->name} → {$to->name} — {$transfer->description}"
            : "Transfer: {$from->name} → {$to->name}";

        return $this->createAndPost(
            $desc,
            'account_transfer',
            $transfer->id,
            [
                ['account_code' => $to->account->code,   'debit' => $amt, 'credit' => 0,   'description' => "Received from {$from->name}"],
                ['account_code' => $from->account->code, 'debit' => 0,    'credit' => $amt, 'description' => "Transferred to {$to->name}"],
            ],
            $transfer->transfer_date->toDateString(),
            auth()->id(),
            $transfer->reference
        );
    }
}
