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
            'transactions.*.amount'        => 'required|numeric|min:0.01',
            'transactions.*.type'          => 'required|in:debit,credit',
            'transactions.*.contra_account_id' => 'nullable|exists:accounts,id',
            'transactions.*.partner'       => 'nullable|string',
            'transactions.*.notes'         => 'nullable|string|max:500',
        ]);

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
                $statement->transactions()->create([
                    'transaction_date'  => $row['date'],
                    'description'       => $row['description'],
                    'reference'         => $row['reference'] ?? null,
                    'amount'            => $row['amount'],
                    'type'              => $row['type'],
                    'status'            => 'pending',
                    'contra_account_id' => $row['contra_account_id'] ?? null ?: null,
                    'partner_type'      => $partnerType,
                    'partner_id'        => $partnerId,
                    'notes'             => $row['notes'] ?? null,
                ]);
            }

            return $statement;
        });

        return redirect()->route('admin.accounting.bank-statements.show', $statement)
                         ->with('success', "Statement created with {$statement->transactions()->count()} transactions. Review and post pending items.");
    }

    // ── Show statement + transactions ────────────────────────────────
    public function show(BankStatement $bankStatement)
    {
        $bankStatement->load(['bankAccount.account', 'createdBy']);
        $transactions = $bankStatement->transactions()
            ->with(['contraAccount', 'journalEntry'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $accounts  = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients   = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.accounting.bank-statements.show', compact('bankStatement', 'transactions', 'accounts', 'clients', 'suppliers'));
    }

    // ── Post a single transaction → create JE ────────────────────────
    public function postTransaction(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Already posted.');

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
                'status'      => 'posted',
                'created_by'  => auth()->id(),
                'posted_by'   => auth()->id(),
                'posted_at'   => now(),
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

            // Update transaction
            $transaction->update([
                'status'            => 'posted',
                'contra_account_id' => $request->contra_account_id,
                'partner_type'      => $partnerType,
                'partner_id'        => $partnerId,
                'journal_entry_id'  => $je->id,
            ]);
        });

        return back()->with('success', "Transaction posted → JE created.");
    }

    // ── Post ALL pending transactions at once ────────────────────────
    public function postAll(Request $request, BankStatement $bankStatement)
    {
        $pending = $bankStatement->transactions()->where('status', 'pending')
            ->whereNotNull('contra_account_id')->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'No pending transactions with a contra account selected.');
        }

        $bankAccount = $bankStatement->bankAccount;
        abort_if(!$bankAccount->account_id, 422, 'Bank account has no linked GL account.');

        $posted = 0;

        DB::transaction(function () use ($bankStatement, $pending, $bankAccount, &$posted) {
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
                    'status'      => 'posted',
                    'created_by'  => auth()->id(),
                    'posted_by'   => auth()->id(),
                    'posted_at'   => now(),
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

                $transaction->update([
                    'status'           => 'posted',
                    'journal_entry_id' => $je->id,
                ]);

                $posted++;
            }
        });

        return back()->with('success', "{$posted} transaction(s) posted successfully.");
    }

    // ── Ignore a transaction ─────────────────────────────────────────
    public function ignoreTransaction(BankStatement $bankStatement, BankTransaction $transaction)
    {
        abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
        abort_if($transaction->status === 'posted', 422, 'Cannot ignore a posted transaction.');

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
        return redirect()->route('admin.accounting.bank-statements.show', $statement)
            ->with('success', "Statement created with {$count} transactions imported from file. Review and post when ready.");
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
