<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashRequest;
use App\Models\CreditNote;
use App\Models\Expense;
use App\Models\FuelLog;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\MaintenanceRecord;
use App\Models\PurchaseOrder;
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
     * When an invoice is marked SENT:
     *   DR 1140 Accounts Receivable   (total_amount)
     *   CR 4100 Rental Income         (subtotal - discount)
     *   CR 2120 VAT Payable           (vat_amount)   [if VAT > 0]
     */
    public function onInvoiceSent(Invoice $invoice): ?JournalEntry
    {
        $revenue = (float) $invoice->total_amount - (float) $invoice->vat_amount;
        $vat     = (float) $invoice->vat_amount;
        $total   = (float) $invoice->total_amount;

        $lines = [
            ['account_code' => '1140', 'debit' => $total,   'credit' => 0,       'description' => 'AR — ' . $invoice->invoice_number],
            ['account_code' => '4100', 'debit' => 0,         'credit' => $revenue, 'description' => 'Revenue — ' . $invoice->invoice_number],
        ];
        if ($vat > 0) {
            $lines[] = ['account_code' => '2120', 'debit' => 0, 'credit' => $vat, 'description' => 'VAT — ' . $invoice->invoice_number];
        }

        return $this->createAndPost(
            "Invoice {$invoice->invoice_number} sent",
            'invoice',
            $invoice->id,
            $lines,
            $invoice->issue_date?->toDateString(),
            $invoice->created_by
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

        $lines = [
            ['account_code' => $bankCoa->code, 'debit' => (float) $payment->amount, 'credit' => 0,                          'description' => 'Receipt — ' . ($payment->receipt_number ?? '')],
            ['account_code' => '1140',          'debit' => 0,                         'credit' => (float) $payment->amount, 'description' => 'AR settled — ' . ($payment->invoice->invoice_number ?? '')],
        ];

        $je = $this->createAndPost(
            "Payment received on invoice " . ($payment->invoice->invoice_number ?? $payment->invoice_id),
            'payment',
            $payment->id,
            $lines,
            $payment->payment_date->toDateString(),
            $payment->recorded_by
        );

        if ($je) {

            BankAccount::where('id', $bankAccount->id)->increment('current_balance', (float) $payment->amount);
        }

        return $je;
    }

    // ─── PURCHASE ORDER — Inventory Receipt ──────────────────────────

    /**
     * When a PO is marked received:
     *   DR 1150 Inventory Asset   (total_value)
     *   CR 2110 Accounts Payable  (total_value)
     */
    public function onPurchaseOrderReceived(PurchaseOrder $po, float $receivedValue = 0): ?JournalEntry
    {
        $total = $receivedValue > 0 ? round($receivedValue, 2) : (float) $po->total_value;
        if ($total <= 0) return null;

        $isPartial = $po->status === 'partial';
        $label     = $isPartial ? 'Stock received (partial)' : 'Stock received';

        return $this->createAndPost(
            "{$label} — PO {$po->po_number}",
            'purchase_order',
            $po->id,
            [
                ['account_code' => '1150', 'debit' => $total, 'credit' => 0,      'description' => "Inventory in — {$po->po_number}"],
                ['account_code' => '2110', 'debit' => 0,       'credit' => $total, 'description' => 'AP — ' . ($po->supplier->name ?? $po->supplier_id)],
            ],
            now()->toDateString(),
            auth()->id()
        );
    }

    // ─── SUPPLIER PAYMENT — AP Settlement ────────────────────────────

    /**
     * When a supplier is paid:
     *   DR 2110 Accounts Payable   (amount)
     *   CR bank_account COA        (amount)
     */
    public function onSupplierPayment(SupplierPayment $sp): ?JournalEntry
    {
        $bankAccount = $sp->bankAccount;
        if (!$bankAccount || !$bankAccount->account_id) return null;

        $bankCoa = Account::find($bankAccount->account_id);
        if (!$bankCoa) return null;

        $je = $this->createAndPost(
            "Supplier payment — {$sp->payment_number} to {$sp->supplier->name}",
            'supplier_payment',
            $sp->id,
            [
                ['account_code' => '2110',          'debit' => (float) $sp->amount, 'credit' => 0,                       'description' => "AP settled — {$sp->supplier->name}"],
                ['account_code' => $bankCoa->code,  'debit' => 0,                    'credit' => (float) $sp->amount,    'description' => "Bank out — {$sp->payment_number}"],
            ],
            $sp->payment_date->toDateString(),
            $sp->created_by
        );

        if ($je) {
            BankAccount::where('id', $bankAccount->id)->decrement('current_balance', (float) $sp->amount);
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

        $total = (float) $expense->total_amount;

        $je = $this->createAndPost(
            "Expense — {$expense->expense_number}: {$expense->description}",
            'expense',
            $expense->id,
            [
                ['account_code' => $expenseAccount->code, 'debit' => $total, 'credit' => 0,      'description' => $expense->description],
                ['account_code' => $bankCoa->code,         'debit' => 0,      'credit' => $total, 'description' => "Payment via {$bankAccount->name}"],
            ],
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
     *   DR 1160 Staff Advances  (total_amount)
     *   CR 1130 Petty Cash      (total_amount)
     */
    public function onCashRequestDisbursed(CashRequest $cr): ?JournalEntry
    {
        return $this->createAndPost(
            "Cash disbursed — {$cr->request_number}: {$cr->purpose}",
            'cash_request',
            $cr->id,
            [
                ['account_code' => '1160', 'debit' => (float) $cr->total_amount, 'credit' => 0,                            'description' => "Advance — {$cr->requestedBy->name}"],
                ['account_code' => '1130', 'debit' => 0,                          'credit' => (float) $cr->total_amount,   'description' => "Petty cash out — {$cr->request_number}"],
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
}
