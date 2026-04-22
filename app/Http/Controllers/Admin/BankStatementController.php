<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankStatementController extends Controller
{
    // ── Index ────────────────────────────────────────────────────────
    public function index()
    {
        $statements = BankStatement::with('bankAccount', 'createdBy')
            ->withCount([
                'transactions',
                'transactions as pending_count' => fn($q) => $q->where('status', 'pending'),
                'transactions as posted_count'  => fn($q) => $q->where('status', 'posted'),
            ])
            ->latest()
            ->paginate(20);

        return view('admin.accounting.bank-statements.index', compact('statements'));
    }

    // ── Create form ──────────────────────────────────────────────────
    public function create()
    {
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $accounts     = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients      = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers    = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.accounting.bank-statements.create', compact('bankAccounts', 'accounts', 'clients', 'suppliers'));
    }

    // ── Download Excel import template ───────────────────────────────
    public function downloadTemplate()
    {
        try {
            if (!class_exists('ZipArchive')) {
                throw new \RuntimeException('The php-zip extension is required to generate Excel files. Please enable it on this server.');
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Transactions');

            // ── Headers ──────────────────────────────────────────────
            $headers = ['date', 'description', 'debit', 'credit', 'reference', 'notes'];
            foreach ($headers as $col => $header) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
                $sheet->setCellValue($cell, $header);
            }

            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

            // Style header row
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']],
            ]);

            // Column widths: date, description, debit, credit, reference, notes
            foreach (['A' => 14, 'B' => 40, 'C' => 16, 'D' => 16, 'E' => 22, 'F' => 35] as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }

            // ── Sample data rows ─────────────────────────────────────
            $samples = [
                ['2025-01-15', 'Cash deposit — client payment',            '5000000', '',        'BNK-REF-001', ''],
                ['2025-01-18', 'Fuel purchase — Generator site A',         '',        '850000',  'RCP-2025-01', 'Monthly fuel run'],
                ['2025-01-22', 'Bank transfer received — MileleInv-00045', '2500000', '',        'TRF-45',      ''],
                ['2025-01-28', 'Bank charges',                             '',        '15000',   '',            'Monthly service fee'],
            ];

            foreach ($samples as $rowIndex => $row) {
                foreach ($row as $col => $value) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . ($rowIndex + 2);
                    $sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }

            // Light yellow background on sample rows
            $sheet->getStyle("A2:{$lastCol}" . (count($samples) + 1))->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF9C3']],
            ]);

            // ── Instructions sheet ────────────────────────────────────
            $info = $spreadsheet->createSheet();
            $info->setTitle('Instructions');

            $infoRows = [
                ['Column',      'Required?', 'Notes'],
                ['date',        'Yes',       'Transaction date. Format: YYYY-MM-DD (e.g. 2025-01-15)'],
                ['description', 'Yes',       'Narrative / payee description for the transaction.'],
                ['debit',       'Yes*',      'Amount received / money in. Numeric only, no commas. Use 0 or leave blank for credit-side entries.'],
                ['credit',      'Yes*',      'Amount paid / money out. Numeric only, no commas. Use 0 or leave blank for debit-side entries.'],
                ['reference',   'No',        'Bank reference number, receipt number, or transaction ID.'],
                ['notes',       'No',        'Any additional context or memo for this line.'],
                ['',            '',          ''],
                ['* Each row must have exactly one of debit or credit greater than zero.', '', ''],
                ['* Rows where both debit and credit are 0 will be skipped.', '', ''],
            ];

            foreach ($infoRows as $r => $cols) {
                foreach ($cols as $c => $val) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1) . ($r + 1);
                    $info->setCellValue($cell, $val);
                }
            }
            $info->getStyle('A1:C1')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']],
            ]);
            foreach (['A' => 14, 'B' => 14, 'C' => 80] as $col => $w) {
                $info->getColumnDimension($col)->setWidth($w);
            }

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'bank_statement_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);

        } catch (\Throwable $e) {
            \Log::error('Bank statement template generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Template generation failed: ' . $e->getMessage());
        }
    }

    // ── Store (create statement + transactions) ──────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'bank_account_id'              => 'required|exists:bank_accounts,id',
            'reference'                    => 'nullable|string|max:150',
            'period_from'                  => 'nullable|date',
            'period_to'                    => 'nullable|date|after_or_equal:period_from',
            'notes'                        => 'nullable|string',
            'transactions'                 => 'required|array|min:1',
            'transactions.*.date'          => 'required|date',
            'transactions.*.description'   => 'required|string|max:500',
            'transactions.*.reference'     => 'nullable|string|max:150',
            'transactions.*.debit'         => 'required|numeric|min:0',
            'transactions.*.credit'        => 'required|numeric|min:0',
            'transactions.*.contra_account_id' => 'nullable|exists:accounts,id',
            'transactions.*.partner'       => 'nullable|string',
            'transactions.*.notes'         => 'nullable|string|max:500',
        ]);

        // Ensure at least one side has a value per row
        foreach ($request->transactions as $i => $row) {
            $dr = (float) ($row['debit'] ?? 0);
            $cr = (float) ($row['credit'] ?? 0);
            if ($dr <= 0 && $cr <= 0) {
                return back()->withErrors(["transactions.{$i}.debit" => 'Each transaction must have a debit or credit amount greater than zero.'])->withInput();
            }
            if ($dr > 0 && $cr > 0) {
                return back()->withErrors(["transactions.{$i}.debit" => 'Each transaction can have either a debit or a credit, not both.'])->withInput();
            }
        }

        // Warn if another statement already covers an overlapping period for this bank account
        $overlapWarning = null;
        if ($request->period_from && $request->period_to) {
            $overlap = BankStatement::where('bank_account_id', $request->bank_account_id)
                ->whereNotNull('period_from')->whereNotNull('period_to')
                ->where('period_from', '<=', $request->period_to)
                ->where('period_to', '>=', $request->period_from)
                ->first();
            if ($overlap) {
                $overlapWarning = "Another statement ({$overlap->reference}) already covers an overlapping period for this bank account — check for duplicate transactions.";
            }
        }

        $statement = DB::transaction(function () use ($request) {
            $statement = BankStatement::create([
                'bank_account_id' => $request->bank_account_id,
                'created_by'      => auth()->id(),
                'reference'       => $request->reference,
                'period_from'     => $request->period_from,
                'period_to'       => $request->period_to,
                'notes'           => $request->notes,
            ]);

            foreach ($request->transactions as $row) {
                [$partnerType, $partnerId] = static::parsePartner($row['partner'] ?? null);
                $debit  = (float) ($row['debit']  ?? 0);
                $credit = (float) ($row['credit'] ?? 0);
                $statement->transactions()->create([
                    'transaction_date'  => $row['date'],
                    'description'       => $row['description'],
                    'reference'         => $row['reference'] ?? null,
                    'amount'            => $debit > 0 ? $debit : $credit,
                    'type'              => $debit > 0 ? 'debit' : 'credit',
                    'status'            => 'pending',
                    'contra_account_id' => $row['contra_account_id'] ?? null ?: null,
                    'partner_type'      => $partnerType,
                    'partner_id'        => $partnerId,
                    'notes'             => $row['notes'] ?? null,
                ]);
            }

            return $statement;
        });

        $msg = "Statement created with {$statement->transactions()->count()} transactions. Review and post pending items.";
        $redirect = redirect()->route('admin.accounting.bank-statements.show', $statement)->with('success', $msg);
        if ($overlapWarning) {
            $redirect->with('warning', $overlapWarning);
        }
        return $redirect;
    }

    // ── Show statement + transactions ────────────────────────────────
    public function show(Request $request, BankStatement $bankStatement)
    {
        $bankStatement->load(['bankAccount.account', 'createdBy']);

        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $allTransactions = $bankStatement->transactions()
            ->with(['contraAccount', 'journalEntry'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $transactions = $bankStatement->transactions()
            ->with(['contraAccount', 'journalEntry'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $accounts  = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients   = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.accounting.bank-statements.show', compact('bankStatement', 'allTransactions', 'transactions', 'accounts', 'clients', 'suppliers', 'perPage'));
    }

    // ── Post a single transaction → create JE ────────────────────────
    public function postTransaction(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Already posted.');
        abort_if($transaction->status === 'reconciled', 422, 'This transaction has been reconciled against an existing payment. Use the reconciled payment\'s journal entry — do not post again.');

        $request->validate([
            'contra_account_id' => 'required|exists:accounts,id',
            'partner'           => 'nullable|string',
            'notes'             => 'nullable|string|max:500',
        ]);

        $bankAccount = $bankStatement->bankAccount;
        abort_if(!$bankAccount->account_id, 422, 'The bank account has no linked GL account. Please set one in Bank Accounts settings.');

        DB::transaction(function () use ($request, $bankStatement, $transaction, $bankAccount) {
            [$partnerType, $partnerId] = static::parsePartner($request->partner);

            // Determine which side bank account goes (credit=money in → bank Dr, debit=money out → bank Cr)
            $bankDr   = $transaction->type === 'credit' ? $transaction->amount : 0;
            $bankCr   = $transaction->type === 'debit'  ? $transaction->amount : 0;
            $contraDr = $bankCr; // opposite
            $contraCr = $bankDr;

            $je = JournalEntry::create([
                'entry_date'  => $transaction->transaction_date,
                'description' => auth()->user()->name,
                'reference'   => $transaction->reference ?? $bankStatement->reference,
                'source_type' => 'bank_statement',
                'notes'       => $transaction->description . ($request->notes ? ' — ' . $request->notes : ''),
                'status'      => 'draft',
                'created_by'  => auth()->id(),
            ]);

            // Bank GL line
            $je->lines()->create([
                'account_id'   => $bankAccount->account_id,
                'description'  => $transaction->description,
                'partner_type' => null,
                'partner_id'   => null,
                'debit'        => $bankDr,
                'credit'       => $bankCr,
            ]);

            // Contra line
            $je->lines()->create([
                'account_id'   => $request->contra_account_id,
                'description'  => $transaction->description,
                'partner_type' => $partnerType,
                'partner_id'   => $partnerId,
                'debit'        => $contraDr,
                'credit'       => $contraCr,
            ]);

            // Post the JE — this applies applyDebit/applyCredit to COA account balances
            $je->post();
            $je->update(['posted_by' => auth()->id()]);

            // Update transaction
            $transaction->update([
                'status'            => 'posted',
                'contra_account_id' => $request->contra_account_id,
                'partner_type'      => $partnerType,
                'partner_id'        => $partnerId,
                'journal_entry_id'  => $je->id,
            ]);

            // Sync bank account current_balance:
            // credit = money IN to the bank  → increment
            // debit  = money OUT of the bank → decrement
            if ($transaction->type === 'credit') {
                BankAccount::where('id', $bankAccount->id)->increment('current_balance', $transaction->amount);
            } else {
                BankAccount::where('id', $bankAccount->id)->decrement('current_balance', $transaction->amount);
            }
        });

        return back()->with('success', "Transaction posted → JE created.");
    }

    // ── Post ALL pending transactions at once ────────────────────────
    public function postAll(Request $request, BankStatement $bankStatement)
    {
        $skipped = $bankStatement->transactions()->where('status', 'pending')
            ->whereNull('contra_account_id')->count();

        $pending = $bankStatement->transactions()->where('status', 'pending')
            ->whereNotNull('contra_account_id')->get();

        if ($pending->isEmpty()) {
            $msg = 'No pending transactions with a contra account selected.';
            if ($skipped) $msg .= " ({$skipped} pending transaction(s) are missing a contra account — set them individually first.)";
            return back()->with('error', $msg);
        }

        $bankAccount = $bankStatement->bankAccount;
        abort_if(!$bankAccount->account_id, 422, 'Bank account has no linked GL account.');

        $posted = 0;
        $netBalanceChange = 0.0;

        DB::transaction(function () use ($bankStatement, $pending, $bankAccount, &$posted, &$netBalanceChange) {
            foreach ($pending as $transaction) {
                $bankDr   = $transaction->type === 'credit' ? $transaction->amount : 0;
                $bankCr   = $transaction->type === 'debit'  ? $transaction->amount : 0;
                $contraDr = $bankCr;
                $contraCr = $bankDr;

                $je = JournalEntry::create([
                    'entry_date'  => $transaction->transaction_date,
                    'description' => auth()->user()->name,
                    'reference'   => $transaction->reference ?? $bankStatement->reference,
                    'source_type' => 'bank_statement',
                    'notes'       => $transaction->description,
                    'status'      => 'draft',
                    'created_by'  => auth()->id(),
                ]);

                $je->lines()->create([
                    'account_id'  => $bankAccount->account_id,
                    'description' => $transaction->description,
                    'debit'       => $bankDr,
                    'credit'      => $bankCr,
                ]);

                $je->lines()->create([
                    'account_id'   => $transaction->contra_account_id,
                    'description'  => $transaction->description,
                    'partner_type' => $transaction->partner_type,
                    'partner_id'   => $transaction->partner_id,
                    'debit'        => $contraDr,
                    'credit'       => $contraCr,
                ]);

                // Post the JE — applies applyDebit/applyCredit to COA account balances
                $je->post();
                $je->update(['posted_by' => auth()->id()]);

                $transaction->update([
                    'status'           => 'posted',
                    'journal_entry_id' => $je->id,
                ]);

                // Accumulate net balance change:
                // credit = money IN → positive, debit = money OUT → negative
                $netBalanceChange += $transaction->type === 'credit'
                    ? (float) $transaction->amount
                    : -(float) $transaction->amount;

                $posted++;
            }

            // Apply net balance change to the bank account in one query
            if ($netBalanceChange > 0) {
                BankAccount::where('id', $bankAccount->id)->increment('current_balance', $netBalanceChange);
            } elseif ($netBalanceChange < 0) {
                BankAccount::where('id', $bankAccount->id)->decrement('current_balance', abs($netBalanceChange));
            }
        });

        $msg = "{$posted} transaction(s) posted successfully.";
        if ($skipped) $msg .= " {$skipped} pending transaction(s) were skipped — set their contra account to post them.";
        return back()->with('success', $msg);
    }

    // ── Ignore a transaction ─────────────────────────────────────────
    public function ignoreTransaction(BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Cannot ignore a posted transaction.');
        abort_if($transaction->status === 'reconciled', 422, 'Cannot ignore a reconciled transaction. Un-reconcile it first.');

        $transaction->update(['status' => $transaction->status === 'ignored' ? 'pending' : 'ignored']);

        return back()->with('success', $transaction->status === 'ignored' ? 'Transaction marked as ignored.' : 'Transaction restored to pending.');
    }

    // ── Update a single transaction's contra account / partner ───────
    public function updateTransaction(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Cannot edit a posted transaction.');

        $request->validate([
            'contra_account_id' => 'nullable|exists:accounts,id',
            'partner'           => 'nullable|string',
            'notes'             => 'nullable|string|max:500',
        ]);

        [$partnerType, $partnerId] = static::parsePartner($request->partner);

        $transaction->update([
            'contra_account_id' => $request->contra_account_id ?: null,
            'partner_type'      => $partnerType,
            'partner_id'        => $partnerId,
            'notes'             => $request->notes,
        ]);

        return back()->with('success', 'Transaction updated.');
    }

    // ── Suggest matching payments for reconciliation ─────────────────
    // Returns JSON list of candidate InvoicePayments / SupplierPayments
    // that match this bank transaction (same bank account, amount ±1%, date ±7 days, not yet reconciled)
    public function suggestMatches(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if(!in_array($transaction->status, ['pending', 'ignored']), 422, 'Only pending transactions can be reconciled.');

        $bankAccountId = $bankStatement->bank_account_id;
        $amount        = (float) $transaction->amount;
        $date          = $transaction->transaction_date;
        $isCredit      = $transaction->type === 'credit';
        $q             = trim((string) $request->query('q', ''));
        $isSearch      = $q !== '';

        // Already-reconciled payment IDs (to exclude).
        // For InvoicePayments and SupplierPayments these are 1:1 with a bank transaction.
        // For AccountTransfers: a single transfer touches TWO bank accounts, so we only
        // exclude it for the bank account side that has already been reconciled.
        $usedInvoiceIds   = \App\Models\BankTransaction::where('reconciled_payment_type', \App\Models\InvoicePayment::class)
                              ->whereNotNull('reconciled_payment_id')->pluck('reconciled_payment_id');
        $usedSupplierIds  = \App\Models\BankTransaction::where('reconciled_payment_type', \App\Models\SupplierPayment::class)
                              ->whereNotNull('reconciled_payment_id')->pluck('reconciled_payment_id');
        // Only exclude a transfer if it was already reconciled from THIS bank account's statement.
        $usedTransferIds  = \App\Models\BankTransaction::where('reconciled_payment_type', \App\Models\AccountTransfer::class)
                              ->whereNotNull('reconciled_payment_id')
                              ->whereHas('bankStatement', fn($q) => $q->where('bank_account_id', $bankAccountId))
                              ->pluck('reconciled_payment_id');

        $results = [];

        if ($isCredit || $isSearch) {
            // For credit lines (money in) or free-text search: look at InvoicePayments
            $query = \App\Models\InvoicePayment::with(['invoice.client'])
                ->where('bank_account_id', $bankAccountId)
                ->where('is_reversed', false)
                ->whereNotIn('id', $usedInvoiceIds);

            if ($isSearch) {
                // Free-text: match against reference, receipt_number, invoice number, client name
                $query->where(function ($w) use ($q) {
                    $w->where('reference', 'like', "%{$q}%")
                      ->orWhere('receipt_number', 'like', "%{$q}%")
                      ->orWhereHas('invoice', fn($i) => $i->where('invoice_number', 'like', "%{$q}%"))
                      ->orWhereHas('invoice.client', fn($c) =>
                            $c->where('company_name', 'like', "%{$q}%")
                              ->orWhere('full_name', 'like', "%{$q}%")
                      );
                });
            } else {
                // Auto-match: same bank, ±7 days, ±1% amount
                $tolerance = max($amount * 0.01, 1);
                $query->whereBetween('payment_date', [
                    $date->copy()->subDays(7)->toDateString(),
                    $date->copy()->addDays(7)->toDateString(),
                ])->whereBetween('amount', [$amount - $tolerance, $amount + $tolerance]);
            }

            $matches = $query
                ->orderByRaw('ABS(DATEDIFF(payment_date, ?))', [$date->toDateString()])
                ->orderByRaw('ABS(amount - ?)', [$amount])
                ->limit(15)
                ->get();

            foreach ($matches as $pmt) {
                $results[] = [
                    'type'           => 'invoice_payment',
                    'id'             => $pmt->id,
                    'date'           => $pmt->payment_date->format('d M Y'),
                    'amount'         => (float) $pmt->amount,
                    'reference'      => $pmt->reference ?? $pmt->receipt_number ?? '—',
                    'method'         => ucfirst(str_replace('_', ' ', $pmt->payment_method)),
                    'description'    => ($pmt->invoice?->client?->company_name ?? $pmt->invoice?->client?->full_name ?? 'Unknown Client')
                                        . ' — Invoice ' . ($pmt->invoice?->invoice_number ?? '#'),
                    'invoice_number' => $pmt->invoice?->invoice_number ?? null,
                ];
            }
        }

        if (!$isCredit || $isSearch) {
            // For debit lines (money out) or free-text search: look at SupplierPayments
            $query = \App\Models\SupplierPayment::with(['supplier'])
                ->where('bank_account_id', $bankAccountId)
                ->where('status', '!=', 'cancelled')
                ->whereNotIn('id', $usedSupplierIds);

            if ($isSearch) {
                $query->where(function ($w) use ($q) {
                    $w->where('reference', 'like', "%{$q}%")
                      ->orWhere('payment_number', 'like', "%{$q}%")
                      ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$q}%"));
                });
            } else {
                $tolerance = max($amount * 0.01, 1);
                $query->whereBetween('payment_date', [
                    $date->copy()->subDays(7)->toDateString(),
                    $date->copy()->addDays(7)->toDateString(),
                ])->whereBetween('amount', [$amount - $tolerance, $amount + $tolerance]);
            }

            $matches = $query
                ->orderByRaw('ABS(DATEDIFF(payment_date, ?))', [$date->toDateString()])
                ->orderByRaw('ABS(amount - ?)', [$amount])
                ->limit(15)
                ->get();

            foreach ($matches as $pmt) {
                $results[] = [
                    'type'           => 'supplier_payment',
                    'id'             => $pmt->id,
                    'date'           => $pmt->payment_date->format('d M Y'),
                    'amount'         => (float) $pmt->amount,
                    'reference'      => $pmt->reference ?? $pmt->payment_number ?? '—',
                    'method'         => ucfirst(str_replace('_', ' ', $pmt->payment_method)),
                    'description'    => ($pmt->supplier?->name ?? 'Unknown Supplier')
                                        . ($pmt->payment_number ? ' — ' . $pmt->payment_number : ''),
                    'invoice_number' => null,
                ];
            }
        }

        // Account Transfers — match both credit (money came IN → to_bank_account) and debit (money went OUT → from_bank_account)
        {
            $query = \App\Models\AccountTransfer::with(['fromAccount', 'toAccount'])
                ->whereNotIn('id', $usedTransferIds);

            if ($isCredit) {
                // Money arrived in this account → was transferred FROM somewhere TO here
                $query->where('to_bank_account_id', $bankAccountId);
            } else {
                // Money left this account → was transferred FROM here TO somewhere
                $query->where('from_bank_account_id', $bankAccountId);
            }

            if ($isSearch) {
                $query->where(function ($w) use ($q) {
                    $w->where('reference', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('fromAccount', fn($a) => $a->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('toAccount',   fn($a) => $a->where('name', 'like', "%{$q}%"));
                });
            } else {
                $tolerance = max($amount * 0.01, 1);
                // For FX transfers, the amount column holds the FROM-side amount.
                // If we're matching from the TO side (credit / destination account),
                // compare against to_amount instead so a USD statement correctly
                // matches a TZS→USD transfer.
                $query->whereBetween('transfer_date', [
                    $date->copy()->subDays(7)->toDateString(),
                    $date->copy()->addDays(7)->toDateString(),
                ]);
                if ($isCredit) {
                    $query->where(function ($w) use ($amount, $tolerance) {
                        $w->whereBetween('to_amount', [$amount - $tolerance, $amount + $tolerance])
                          ->orWhereBetween('amount',    [$amount - $tolerance, $amount + $tolerance]);
                    });
                } else {
                    $query->whereBetween('amount', [$amount - $tolerance, $amount + $tolerance]);
                }
            }

            $matches = $query
                ->orderByRaw('ABS(DATEDIFF(transfer_date, ?))', [$date->toDateString()])
                ->orderByRaw('ABS(COALESCE(to_amount, amount) - ?)', [$amount])
                ->limit(10)
                ->get();

            foreach ($matches as $trf) {
                $from = $trf->fromAccount?->name ?? '?';
                $to   = $trf->toAccount?->name   ?? '?';
                $results[] = [
                    'type'           => 'account_transfer',
                    'id'             => $trf->id,
                    'date'           => $trf->transfer_date->format('d M Y'),
                    'amount'         => (float) $trf->amount,
                    'reference'      => $trf->reference ?? '—',
                    'method'         => 'Transfer',
                    'description'    => "{$from} → {$to}",
                    'invoice_number' => null,
                ];
            }
        }

        return response()->json(['matches' => $results]);
    }

    // ── Reconcile a transaction against an existing payment ──────────
    public function reconcileTransaction(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Already posted — cannot reconcile.');
        abort_if($transaction->status === 'reconciled', 422, 'Already reconciled.');

        $request->validate([
            'payment_type' => 'required|in:invoice_payment,supplier_payment,account_transfer',
            'payment_id'   => 'required|integer|min:1',
        ]);

        if ($request->payment_type === 'account_transfer') {
            $transfer = \App\Models\AccountTransfer::findOrFail($request->payment_id);

            // Guard: the transfer must involve this bank account
            abort_if(
                $transfer->from_bank_account_id !== $bankStatement->bank_account_id &&
                $transfer->to_bank_account_id   !== $bankStatement->bank_account_id,
                422, 'The selected transfer does not involve the bank account of this statement.'
            );

            // Guard: not already reconciled from the SAME bank account side.
            // A transfer has two sides (from + to), each side can be reconciled once independently.
            $alreadyUsed = \App\Models\BankTransaction::where('reconciled_payment_type', \App\Models\AccountTransfer::class)
                ->where('reconciled_payment_id', $transfer->id)
                ->whereHas('bankStatement', fn($q) => $q->where('bank_account_id', $bankStatement->bank_account_id))
                ->exists();
            abort_if($alreadyUsed, 409, 'This transfer has already been reconciled from this bank account\'s statement.');

            $transaction->update([
                'status'                   => 'reconciled',
                'reconciled_payment_type'  => \App\Models\AccountTransfer::class,
                'reconciled_payment_id'    => $transfer->id,
                'reconciled_at'            => now(),
                'reconciled_by'            => auth()->id(),
                'journal_entry_id'         => $transfer->journal_entry_id,
            ]);

            return back()->with('success',
                "Transaction reconciled → linked to Account Transfer {$transfer->reference}. No duplicate journal entry was created.");
        }

        $modelClass = $request->payment_type === 'invoice_payment'
            ? \App\Models\InvoicePayment::class
            : \App\Models\SupplierPayment::class;

        $payment = $modelClass::findOrFail($request->payment_id);

        // Guard: ensure payment is for the same bank account
        abort_if($payment->bank_account_id !== $bankStatement->bank_account_id, 422,
            'The selected payment is not for the same bank account as this statement.');

        // Guard: ensure this payment isn't already reconciled to another transaction
        $alreadyUsed = \App\Models\BankTransaction::where('reconciled_payment_type', $modelClass)
            ->where('reconciled_payment_id', $payment->id)
            ->exists();
        abort_if($alreadyUsed, 409, 'This payment is already reconciled to another bank transaction.');

        $transaction->update([
            'status'                   => 'reconciled',
            'reconciled_payment_type'  => $modelClass,
            'reconciled_payment_id'    => $payment->id,
            'reconciled_at'            => now(),
            'reconciled_by'            => auth()->id(),
            // Link to the payment's JE for traceability — no new JE created
            'journal_entry_id'         => $payment->journal_entry_id ?? $transaction->journal_entry_id,
        ]);

        $label = $request->payment_type === 'invoice_payment' ? 'Invoice payment' : 'Supplier payment';

        return back()->with('success',
            "Transaction reconciled → linked to existing {$label}. No duplicate journal entry was created.");
    }

    // ── Un-reconcile a transaction → reset to pending ────────────────
    public function unreconcileTransaction(BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status !== 'reconciled', 422, 'Transaction is not reconciled.');

        $transaction->update([
            'status'                  => 'pending',
            'reconciled_payment_type' => null,
            'reconciled_payment_id'   => null,
            'reconciled_at'           => null,
            'reconciled_by'           => null,
            'journal_entry_id'        => null,
        ]);

        return back()->with('success', 'Reconciliation removed — transaction reset to pending.');
    }

    // ── Parse uploaded file (CSV or XLSX) → array of rows ───────────
    private function parseUploadedFile(\Illuminate\Http\UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls', 'ods'])) {
            ini_set('memory_limit', '512M');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());

            // Detect CRBN multi-sheet bank statement format
            // Read only first 3 rows of each of the first 4 sheets to find "TRANSACTION DATE"
            $sheetCount = $spreadsheet->getSheetCount();
            $isCRBN = false;
            if ($sheetCount > 1) {
                for ($s = 0; $s < min($sheetCount, 4) && !$isCRBN; $s++) {
                    $ws     = $spreadsheet->getSheet($s);
                    $maxCol = $ws->getHighestColumn();
                    $sample = $ws->rangeToArray('A1:' . $maxCol . '3', null, false, false, false);
                    foreach ($sample as $row) {
                        foreach ($row as $cell) {
                            if (strtoupper(trim((string)$cell)) === 'TRANSACTION DATE') {
                                $isCRBN = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($isCRBN) {
                return $this->parseCRBNFormat($spreadsheet);
            }

            // Generic single-sheet Excel
            $sheet   = $spreadsheet->getActiveSheet();
            $rawRows = $sheet->toArray(null, true, true, false);
        } else {
            // CSV / TXT
            $rawRows = array_map('str_getcsv', array_filter(file($file->getRealPath())));
        }

        if (empty($rawRows)) return ['rows' => [], 'error' => 'File appears to be empty.'];

        // Find the header row (skip blank rows at top)
        $headerIdx = 0;
        foreach ($rawRows as $i => $row) {
            $vals = array_filter(array_map(fn($v) => trim((string)$v), $row));
            if (!empty($vals)) { $headerIdx = $i; break; }
        }
        $header  = array_map(fn($h) => strtolower(trim((string)$h)), $rawRows[$headerIdx]);
        $rawRows = array_slice($rawRows, $headerIdx + 1);

        // Flexible column map
        $colMap = [
            'date'        => ['date', 'transaction_date', 'txn_date', 'value_date', 'trans date'],
            'description' => ['description', 'narration', 'details', 'particulars', 'memo', 'trans description'],
            'reference'   => ['reference', 'ref', 'ref_no', 'cheque', 'check', 'chq no'],
            'debit'       => ['debit', 'dr', 'withdrawal', 'out', 'debit amount'],
            'credit'      => ['credit', 'cr', 'deposit', 'in', 'credit amount'],
            'amount'      => ['amount', 'value'],
            'type'        => ['type', 'dr/cr'],
        ];

        $cols = [];
        foreach ($colMap as $field => $candidates) {
            foreach ($candidates as $c) {
                $idx = array_search($c, $header);
                if ($idx !== false) { $cols[$field] = $idx; break; }
            }
        }

        if (!isset($cols['date']) || !isset($cols['description'])) {
            return ['rows' => [], 'error' => 'File must have at least "date" and "description" columns.'];
        }

        $rows = [];
        foreach ($rawRows as $row) {
            if (count(array_filter(array_map('strval', $row))) === 0) continue;

            $dateRaw = trim((string)($row[$cols['date']] ?? ''));
            $desc    = trim((string)($row[$cols['description']] ?? ''));
            if (!$dateRaw || !$desc) continue;

            try {
                if (is_numeric($dateRaw)) {
                    $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$dateRaw)->format('Y-m-d');
                } else {
                    $parsedDate = \Carbon\Carbon::parse($dateRaw)->toDateString();
                }
            } catch (\Exception) {
                continue;
            }

            $amount = null;
            $type   = null;

            if (isset($cols['debit']) && isset($cols['credit'])) {
                $dr = (float) str_replace([',', ' ', '$', 'KES', 'TZS', 'USD'], '', $row[$cols['debit']] ?? 0);
                $cr = (float) str_replace([',', ' ', '$', 'KES', 'TZS', 'USD'], '', $row[$cols['credit']] ?? 0);
                if ($dr > 0)      { $amount = $dr; $type = 'debit'; }
                elseif ($cr > 0)  { $amount = $cr; $type = 'credit'; }
            } elseif (isset($cols['amount'])) {
                $raw = (float) str_replace([',', ' ', '$', 'KES', 'TZS', 'USD'], '', $row[$cols['amount']] ?? 0);
                if (isset($cols['type'])) {
                    $t = strtolower(trim((string)($row[$cols['type']] ?? '')));
                    $type = in_array($t, ['cr', 'credit', 'in', 'c']) ? 'credit' : 'debit';
                } else {
                    $type = $raw >= 0 ? 'credit' : 'debit';
                }
                $amount = abs($raw);
            }

            if (!$amount || $amount <= 0) continue;

            $rows[] = [
                'date'        => $parsedDate,
                'description' => $desc,
                'reference'   => trim((string)($row[$cols['reference'] ?? -1] ?? '')),
                'amount'      => round($amount, 2),
                'type'        => $type,
            ];
        }

        return ['rows' => $rows, 'error' => null];
    }

    // ── CRBN Bank multi-sheet format parser ──────────────────────────
    // Odd sheets  = 3-row account metadata (Account No / Account Description / Currency) — skipped
    // Even sheets = transaction pages with 7 columns:
    //   TRANSACTION DATE | DETAILS | CHANNEL ID | VALUE DATE | DEBIT | CREDIT | BOOK BALANCE
    // Multi-line descriptions: rows where TRANSACTION DATE is null continue the previous row's description
    private function parseCRBNFormat(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $rows       = [];
        $sheetCount = $spreadsheet->getSheetCount();

        for ($s = 0; $s < $sheetCount; $s++) {
            $sheet   = $spreadsheet->getSheet($s);
            $allRows = $sheet->toArray(null, true, true, false);

            // Find header row: look for a cell containing "TRANSACTION DATE"
            $headerIdx = null;
            foreach ($allRows as $i => $row) {
                foreach ($row as $cell) {
                    if (strtoupper(trim((string)$cell)) === 'TRANSACTION DATE') {
                        $headerIdx = $i;
                        break 2;
                    }
                }
            }

            if ($headerIdx === null) continue; // metadata/empty sheet — skip

            $header   = array_map(fn($h) => strtolower(trim(preg_replace('/\s+/', ' ', (string)$h))), $allRows[$headerIdx]);
            $dataRows = array_slice($allRows, $headerIdx + 1);

            // Map columns by header name
            $colDate    = array_search('transaction date', $header);
            $colDetails = array_search('details', $header);
            $colChannel = array_search('channel id', $header);
            $colDebit   = false;
            $colCredit  = false;

            foreach ($header as $idx => $h) {
                if ($h === 'debit')  $colDebit  = $idx;
                if ($h === 'credit') $colCredit = $idx;
            }

            if ($colDate === false || $colDetails === false) continue;

            $currentTx = null;

            foreach ($dataRows as $row) {
                $dateVal    = $row[$colDate] ?? null;
                $detailsVal = trim((string)($row[$colDetails] ?? ''));
                $debit      = $colDebit  !== false ? (float) str_replace([',', ' '], '', (string)($row[$colDebit]  ?? 0)) : 0;
                $credit     = $colCredit !== false ? (float) str_replace([',', ' '], '', (string)($row[$colCredit] ?? 0)) : 0;

                // Continuation row: no date — append description to current transaction
                if ($dateVal === null || (string)$dateVal === '') {
                    if ($currentTx !== null && $detailsVal !== '') {
                        $currentTx['description'] .= ' ' . $detailsVal;
                    }
                    continue;
                }

                // New transaction row — flush previous
                if ($currentTx !== null) {
                    $rows[] = $currentTx;
                }
                $currentTx = null;

                // Parse date — CRBN files use DD-MM-YY string format e.g. "22-01-24"
                try {
                    if ($dateVal instanceof \DateTime || $dateVal instanceof \DateTimeInterface) {
                        $parsedDate = \Carbon\Carbon::instance($dateVal)->toDateString();
                    } elseif (is_numeric($dateVal)) {
                        $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$dateVal)->format('Y-m-d');
                    } else {
                        $str = trim((string)$dateVal);
                        // Try DD-MM-YY then DD-MM-YYYY then DD/MM/YYYY then generic
                        $parsed = \Carbon\Carbon::createFromFormat('d-m-y', $str)
                            ?? \Carbon\Carbon::createFromFormat('d-m-Y', $str)
                            ?? \Carbon\Carbon::createFromFormat('d/m/Y', $str)
                            ?? \Carbon\Carbon::parse($str);
                        $parsedDate = $parsed->toDateString();
                    }
                } catch (\Exception) {
                    continue;
                }

                // Skip balance-only rows (no amount)
                if ($debit <= 0 && $credit <= 0) continue;

                // Skip brought-forward / carried-forward balance entries
                $descUpper = strtoupper($detailsVal);
                if (str_contains($descUpper, 'BROUGHT FORWARD') || str_contains($descUpper, 'CARRIED FORWARD')) {
                    continue;
                }

                $amount = null;
                $type   = null;
                if ($debit > 0)      { $amount = $debit;  $type = 'debit'; }
                elseif ($credit > 0) { $amount = $credit; $type = 'credit'; }
                else                 { continue; }

                $channel = trim((string)($colChannel !== false ? ($row[$colChannel] ?? '') : ''));

                $currentTx = [
                    'date'        => $parsedDate,
                    'description' => $detailsVal,
                    'reference'   => $channel,
                    'amount'      => round($amount, 2),
                    'type'        => $type,
                ];
            }

            // Flush last transaction of this sheet
            if ($currentTx !== null) {
                $rows[] = $currentTx;
            }
        }

        // Clean up accumulated multi-line descriptions
        foreach ($rows as &$row) {
            $row['description'] = trim(preg_replace('/\s+/', ' ', $row['description']));
        }

        if (empty($rows)) {
            return ['rows' => [], 'error' => 'No valid transactions found. The file was recognised as a CRBN Bank statement but contained no parseable transactions.'];
        }

        return ['rows' => $rows, 'error' => null];
    }
    // ── Preview import: parse file → store in session → preview page ─
    public function previewImport(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reference'       => 'nullable|string|max:150',
            'period_from'     => 'nullable|date',
            'period_to'       => 'nullable|date',
            'notes'           => 'nullable|string',
            'import_file'     => 'required|file|mimes:csv,txt,xlsx,xls,ods|max:10240',
        ]);

        $result = $this->parseUploadedFile($request->file('import_file'));

        if ($result['error']) {
            return back()->withInput()->with('error', $result['error']);
        }

        if (empty($result['rows'])) {
            return back()->withInput()->with('error', 'No valid transactions found in the file. Check that the file has date, description, and amount columns.');
        }

        // Store parsed rows + statement meta in session (no DB write yet)
        session([
            'bs_import' => [
                'bank_account_id' => $request->bank_account_id,
                'reference'       => $request->reference,
                'period_from'     => $request->period_from,
                'period_to'       => $request->period_to,
                'notes'           => $request->notes,
                'rows'            => $result['rows'],
            ],
        ]);

        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $accounts     = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients      = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers    = Supplier::orderBy('name')->get(['id', 'name']);
        $importData   = session('bs_import');

        return view('admin.accounting.bank-statements.preview-import',
            compact('bankAccounts', 'accounts', 'clients', 'suppliers', 'importData'));
    }

    // ── Confirm import: read session → save statement + transactions ──
    public function confirmImport(Request $request)
    {
        $import = session('bs_import');
        abort_if(!$import, 422, 'Preview session expired. Please upload the file again.');

        $request->validate([
            'transactions'                     => 'required|array|min:1',
            'transactions.*.date'              => 'required|date',
            'transactions.*.description'       => 'required|string|max:500',
            'transactions.*.reference'         => 'nullable|string|max:150',
            'transactions.*.amount'            => 'required|numeric|min:0.01',
            'transactions.*.type'              => 'required|in:debit,credit',
            'transactions.*.contra_account_id' => 'nullable|exists:accounts,id',
            'transactions.*.partner'           => 'nullable|string',
        ]);

        // Warn if period overlaps an existing statement for the same bank account
        $overlapWarning = null;
        if (!empty($import['period_from']) && !empty($import['period_to'])) {
            $overlap = BankStatement::where('bank_account_id', $import['bank_account_id'])
                ->whereNotNull('period_from')->whereNotNull('period_to')
                ->where('period_from', '<=', $import['period_to'])
                ->where('period_to', '>=', $import['period_from'])
                ->first();
            if ($overlap) {
                $overlapWarning = "Another statement ({$overlap->reference}) already covers an overlapping period for this bank account — check for duplicate transactions.";
            }
        }

        $statement = DB::transaction(function () use ($request, $import) {
            $statement = BankStatement::create([
                'bank_account_id' => $import['bank_account_id'],
                'created_by'      => auth()->id(),
                'reference'       => $import['reference'],
                'period_from'     => $import['period_from'],
                'period_to'       => $import['period_to'],
                'notes'           => $import['notes'],
            ]);

            foreach ($request->transactions as $row) {
                [$partnerType, $partnerId] = static::parsePartner($row['partner'] ?? null);
                $statement->transactions()->create([
                    'transaction_date'  => $row['date'],
                    'description'       => $row['description'],
                    'reference'         => $row['reference'] ?? null ?: null,
                    'amount'            => $row['amount'],
                    'type'              => $row['type'],
                    'status'            => 'pending',
                    'contra_account_id' => $row['contra_account_id'] ?? null ?: null,
                    'partner_type'      => $partnerType,
                    'partner_id'        => $partnerId,
                ]);
            }

            return $statement;
        });

        session()->forget('bs_import');

        $count = $statement->transactions()->count();
        $redirect = redirect()->route('admin.accounting.bank-statements.show', $statement)
            ->with('success', "Statement created with {$count} transactions imported from file. Review and post when ready.");
        if ($overlapWarning) {
            $redirect->with('warning', $overlapWarning);
        }
        return $redirect;
    }

    // ── CSV import ───────────────────────────────────────────────────
    public function importCsv(Request $request, BankStatement $bankStatement)
    {
        abort_if($bankStatement->transactions()->where('status', 'posted')->exists(), 422, 'Cannot import into a statement that already has posted transactions.');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file  = $request->file('csv_file');
        $lines = array_filter(array_map('str_getcsv', file($file->getRealPath())));
        $header = array_map('strtolower', array_map('trim', array_shift($lines)));

        // Accept flexible column names
        $colMap = [
            'date'        => ['date', 'transaction_date', 'txn_date', 'value_date'],
            'description' => ['description', 'narration', 'details', 'particulars', 'memo'],
            'reference'   => ['reference', 'ref', 'ref_no', 'cheque', 'check'],
            'debit'       => ['debit', 'dr', 'withdrawal', 'out'],
            'credit'      => ['credit', 'cr', 'deposit', 'in'],
            'amount'      => ['amount'],
            'type'        => ['type'],
        ];

        $cols = [];
        foreach ($colMap as $field => $candidates) {
            foreach ($candidates as $c) {
                $idx = array_search($c, $header);
                if ($idx !== false) { $cols[$field] = $idx; break; }
            }
        }

        if (!isset($cols['date']) || !isset($cols['description'])) {
            return back()->with('error', 'CSV must have at least "date" and "description" columns.');
        }

        $imported = 0;
        $errors   = [];

        DB::transaction(function () use ($lines, $cols, $bankStatement, &$imported, &$errors) {
            foreach ($lines as $i => $row) {
                if (count(array_filter($row)) === 0) continue; // skip blank rows

                $date = trim($row[$cols['date']] ?? '');
                $desc = trim($row[$cols['description']] ?? '');

                if (!$date || !$desc) {
                    $errors[] = "Row " . ($i + 2) . ": missing date or description — skipped.";
                    continue;
                }

                // Parse date flexibly
                try {
                    $parsedDate = \Carbon\Carbon::parse($date)->toDateString();
                } catch (\Exception) {
                    $errors[] = "Row " . ($i + 2) . ": invalid date '{$date}' — skipped.";
                    continue;
                }

                // Resolve amount + type
                $amount = null;
                $type   = null;

                if (isset($cols['debit']) && isset($cols['credit'])) {
                    $dr = (float) str_replace([',', ' '], '', $row[$cols['debit']] ?? 0);
                    $cr = (float) str_replace([',', ' '], '', $row[$cols['credit']] ?? 0);
                    if ($dr > 0) { $amount = $dr; $type = 'debit'; }
                    elseif ($cr > 0) { $amount = $cr; $type = 'credit'; }
                } elseif (isset($cols['amount']) && isset($cols['type'])) {
                    $amount = abs((float) str_replace([',', ' '], '', $row[$cols['amount']] ?? 0));
                    $rawType = strtolower(trim($row[$cols['type']] ?? ''));
                    $type = in_array($rawType, ['debit', 'dr', 'out', 'withdrawal']) ? 'debit' : 'credit';
                } elseif (isset($cols['amount'])) {
                    $raw = (float) str_replace([',', ' '], '', $row[$cols['amount']] ?? 0);
                    $amount = abs($raw);
                    $type   = $raw < 0 ? 'debit' : 'credit';
                }

                if (!$amount || $amount <= 0) {
                    $errors[] = "Row " . ($i + 2) . ": zero or missing amount — skipped.";
                    continue;
                }

                $bankStatement->transactions()->create([
                    'transaction_date' => $parsedDate,
                    'description'      => $desc,
                    'reference'        => isset($cols['reference']) ? trim($row[$cols['reference']] ?? '') ?: null : null,
                    'amount'           => $amount,
                    'type'             => $type,
                    'status'           => 'pending',
                ]);

                $imported++;
            }
        });

        $msg = "{$imported} transaction(s) imported from CSV.";
        if ($errors) $msg .= ' Skipped: ' . implode('; ', $errors);

        return back()->with($errors && $imported === 0 ? 'error' : 'success', $msg);
    }

    // ── Helper ───────────────────────────────────────────────────────
    private static function parsePartner(?string $value): array
    {
        if (!$value || !str_contains($value, ':')) return [null, null];
        [$type, $id] = explode(':', $value, 2);
        return in_array($type, ['client', 'supplier']) ? [$type, (int) $id] : [null, null];
    }
}
