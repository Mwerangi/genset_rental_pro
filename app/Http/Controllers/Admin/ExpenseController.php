<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Models\UserActivityLog;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_expenses');

        $query = Expense::with(['category', 'bankAccount', 'createdBy'])->orderByDesc('id');

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('from')) {
            $query->where('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('expense_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('expense_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        $perPage    = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100]) ? (int) $request->get('per_page', 25) : 25;
        $expenses   = $query->paginate($perPage)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        $base = $seeAll ? Expense::query() : Expense::where('created_by', $user->id);
        $stats = [
            'total_this_month' => (clone $base)->whereMonth('expense_date', now()->month)
                                               ->whereYear('expense_date', now()->year)
                                               ->where('status', 'posted')
                                               ->sum('total_amount'),
            'pending_approval' => (clone $base)->where('status', 'draft')->count(),
            'approved'         => (clone $base)->where('status', 'approved')->count(),
            'posted'           => (clone $base)->where('status', 'posted')->count(),
        ];

        return view('admin.accounting.expenses.index', compact('expenses', 'categories', 'stats', 'seeAll', 'perPage'));
    }

    public function export(Request $request)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_expenses');

        $query = Expense::with(['category', 'bankAccount', 'createdBy', 'approvedBy'])->latest('expense_date');
        if (!$seeAll) $query->where('created_by', $user->id);
        if ($request->filled('status') && $request->status !== 'all') $query->where('status', $request->status);
        if ($request->filled('category_id')) $query->where('expense_category_id', $request->category_id);
        if ($request->filled('from')) $query->where('expense_date', '>=', $request->from);
        if ($request->filled('to')) $query->where('expense_date', '<=', $request->to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('expense_number', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%")->orWhere('reference', 'like', "%{$s}%"));
        }

        $expenses = $query->get();
        $filename = 'expenses-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Expense #', 'Date', 'Description', 'Category', 'Bank Account', 'Amount', 'VAT', 'Total', 'Status', 'Reference', 'Source', 'Created By', 'Approved By']);
            foreach ($expenses as $e) {
                fputcsv($handle, [
                    $e->expense_number,
                    $e->expense_date?->format('d/m/Y'),
                    $e->description,
                    $e->category?->name ?? '',
                    $e->bankAccount?->name ?? '',
                    number_format($e->amount, 2, '.', ''),
                    number_format($e->vat_amount, 2, '.', ''),
                    number_format($e->total_amount, 2, '.', ''),
                    ucfirst($e->status),
                    $e->reference ?? '',
                    $e->source_label,
                    $e->createdBy?->name ?? '',
                    $e->approvedBy?->name ?? '',
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function create()
    {
        $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.expenses.create', compact('categories', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'bank_account_id'     => 'required|exists:bank_accounts,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'is_zero_rated'       => 'nullable|boolean',
            'vat_amount'          => 'nullable|numeric|min:0',
            'expense_date'        => 'required|date',
            'reference'           => 'nullable|string|max:100',
            'attachment'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $data['is_zero_rated'] = $request->boolean('is_zero_rated');
        if ($data['is_zero_rated']) {
            $data['vat_amount'] = 0;
        }
        $data['total_amount'] = $data['amount'] + ($data['vat_amount'] ?? 0);
        $data['status']       = 'draft';
        $data['created_by']   = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $expense = Expense::create($data);

        UserActivityLog::record(
            auth()->id(), 'created',
            'Created expense ' . $expense->expense_number,
            Expense::class, $expense->id
        );

        return redirect()->route('admin.accounting.expenses.index')
                         ->with('success', 'Expense saved as draft.');
    }

    public function show(Expense $expense)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_expenses')
            && $expense->created_by !== $user->id) {
            abort(403, 'You do not have permission to view this expense.');
        }
        $expense->load(['category', 'bankAccount', 'journalEntry.lines.account', 'createdBy', 'approvedBy', 'bankTransaction.bankStatement', 'bankReconciledBy']);
        return view('admin.accounting.expenses.show', compact('expense'));
    }

    public function approve(Expense $expense)
    {
        if ($expense->status !== 'draft') {
            return back()->with('error', 'Only draft expenses can be approved.');
        }

        $expense->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        UserActivityLog::record(
            auth()->id(), 'approved',
            'Approved expense ' . $expense->expense_number,
            Expense::class, $expense->id
        );

        return back()->with('success', 'Expense approved.');
    }

    public function post(Expense $expense)
    {
        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expenses can be posted.');
        }

        $je = app(JournalEntryService::class)->onExpensePosted($expense);

        if (!$je) {
            return back()->with('error', 'Could not post — check Chart of Accounts is seeded and expense category has a linked ledger account.');
        }

        UserActivityLog::record(
            auth()->id(), 'posted',
            'Posted expense ' . $expense->expense_number . ' (JE: ' . $je->entry_number . ')',
            Expense::class, $expense->id
        );

        return back()->with('success', "Expense posted. Journal entry {$je->entry_number} created.");
    }

    public function edit(Expense $expense)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_expenses') && $expense->created_by !== $user->id) {
            abort(403);
        }
        if ($expense->status !== 'draft') {
            return redirect()->route('admin.accounting.expenses.show', $expense)
                ->with('error', 'Only draft expenses can be edited.');
        }

        $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.expenses.edit', compact('expense', 'categories', 'bankAccounts'));
    }

    public function update(Request $request, Expense $expense)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_expenses') && $expense->created_by !== $user->id) {
            abort(403);
        }
        if ($expense->status !== 'draft') {
            return redirect()->route('admin.accounting.expenses.show', $expense)
                ->with('error', 'Only draft expenses can be edited.');
        }

        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'bank_account_id'     => 'required|exists:bank_accounts,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'is_zero_rated'       => 'nullable|boolean',
            'vat_amount'          => 'nullable|numeric|min:0',
            'expense_date'        => 'required|date',
            'reference'           => 'nullable|string|max:100',
            'attachment'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $data['is_zero_rated'] = $request->boolean('is_zero_rated');
        if ($data['is_zero_rated']) {
            $data['vat_amount'] = 0;
        }
        $data['total_amount'] = $data['amount'] + ($data['vat_amount'] ?? 0);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        } else {
            unset($data['attachment']);
        }

        $expense->update($data);

        UserActivityLog::record(
            auth()->id(), 'updated',
            'Updated expense ' . $expense->expense_number,
            Expense::class, $expense->id
        );

        return redirect()->route('admin.accounting.expenses.show', $expense)
                         ->with('success', 'Expense updated.');
    }

    public function reject(Expense $expense)
    {
        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expenses can be rejected back to draft.');
        }

        $expense->update([
            'status'      => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        UserActivityLog::record(
            auth()->id(), 'rejected',
            'Rejected expense ' . $expense->expense_number . ' back to draft',
            Expense::class, $expense->id
        );

        return back()->with('success', 'Expense sent back to draft.');
    }

    public function destroy(Expense $expense)
    {
        $user = auth()->user();

        // Only the creator can delete their own draft; approve_expenses users can delete any draft
        if (!PermissionService::can($user, 'approve_expenses') && $expense->created_by !== $user->id) {
            abort(403, 'You do not have permission to delete this expense.');
        }

        if ($expense->status !== 'draft') {
            return back()->with('error', 'Only draft expenses can be deleted. Reverse the journal entry instead.');
        }

        $number = $expense->expense_number;
        $expenseId = $expense->id;
        $expense->delete();

        UserActivityLog::record(
            auth()->id(), 'deleted',
            'Deleted expense ' . $number,
            Expense::class, $expenseId
        );

        return redirect()->route('admin.accounting.expenses.index')
                         ->with('success', 'Expense deleted.');
    }

    // ─── BULK APPROVE ──────────────────────────────────────────────────────────

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:expenses,id',
        ]);

        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_expenses');

        $query = Expense::whereIn('id', $request->ids)->where('status', 'draft');
        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        $expenses = $query->get();
        $count    = 0;

        foreach ($expenses as $expense) {
            $expense->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            UserActivityLog::record(
                $user->id, 'approved',
                'Bulk approved expense ' . $expense->expense_number,
                Expense::class, $expense->id
            );

            $count++;
        }

        $skipped = count($request->ids) - $count;
        $msg     = "{$count} expense(s) approved.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (not draft or no permission).";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function bulkPost(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:expenses,id',
        ]);

        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_expenses');

        $query = Expense::whereIn('id', $request->ids)->where('status', 'approved');
        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        $expenses = $query->get();
        $je       = app(JournalEntryService::class);
        $posted   = 0;
        $failed   = 0;

        foreach ($expenses as $expense) {
            $result = $je->onExpensePosted($expense);
            if ($result) {
                UserActivityLog::record(
                    $user->id, 'posted',
                    'Bulk posted expense ' . $expense->expense_number . ' (JE: ' . $result->entry_number . ')',
                    Expense::class, $expense->id
                );
                $posted++;
            } else {
                $failed++;
            }
        }

        $skipped = count($request->ids) - $posted - $failed;
        $msg     = "{$posted} expense(s) posted to ledger.";
        if ($failed  > 0) $msg .= " {$failed} failed (check COA / category setup).";
        if ($skipped > 0) $msg .= " {$skipped} skipped (not approved or no permission).";

        return redirect()->back()->with($failed > 0 ? 'error' : 'success', $msg);
    }

    // ─── BULK ENTRY (multi-row form) ──────────────────────────────────────────

    public function bulkEntry()
    {
        $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $suppliers    = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.expenses.bulk-entry', compact('categories', 'bankAccounts', 'suppliers'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'rows'                           => 'required|array|min:1',
            'rows.*.expense_date'            => 'required|date',
            'rows.*.description'             => 'required|string|max:500',
            'rows.*.expense_category_id'     => 'required|exists:expense_categories,id',
            'rows.*.bank_account_id'         => 'required|exists:bank_accounts,id',
            'rows.*.supplier_id'             => 'nullable|exists:suppliers,id',
            'rows.*.amount'                  => 'required|numeric|min:0.01',
            'rows.*.is_zero_rated'           => 'nullable|boolean',
            'rows.*.reference'               => 'nullable|string|max:100',
        ]);

        $saved = 0;
        foreach ($request->input('rows') as $row) {
            $isZero     = !empty($row['is_zero_rated']);
            $amount     = round((float) $row['amount'], 2);
            $vat        = $isZero ? 0 : round($amount * 0.18, 2);
            $total      = $amount + $vat;

            $expense = Expense::create([
                'expense_category_id' => $row['expense_category_id'],
                'bank_account_id'     => $row['bank_account_id'],
                'supplier_id'         => $row['supplier_id'] ?? null,
                'description'         => $row['description'],
                'amount'              => $amount,
                'is_zero_rated'       => $isZero,
                'vat_amount'          => $vat,
                'total_amount'        => $total,
                'expense_date'        => $row['expense_date'],
                'reference'           => $row['reference'] ?? null,
                'status'              => 'draft',
                'created_by'          => auth()->id(),
            ]);

            UserActivityLog::record(
                auth()->id(), 'created',
                'Created expense ' . $expense->expense_number . ' (bulk entry)',
                Expense::class, $expense->id
            );

            $saved++;
        }

        return redirect()->route('admin.accounting.expenses.index')
                         ->with('success', "{$saved} expense(s) saved as draft.");
    }

    // ─── CSV IMPORT ───────────────────────────────────────────────────────────

    public function bulkImportTemplate()
    {
        $categories   = ExpenseCategory::where('is_active', true)->orderBy('name')->pluck('name');
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->pluck('name');
        $suppliers    = Supplier::where('is_active', true)->orderBy('name')->pluck('name');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Data entry ──────────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import');

        // Header row
        $headers = ['date', 'description', 'category_name', 'bank_account_name', 'amount', 'is_zero_rated', 'reference', 'supplier_name'];
        foreach ($headers as $col => $header) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DC2626');
            $sheet->getStyle($cellRef)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Example rows
        $examples = [
            ['2026-05-01', 'Office supplies purchase', $categories->first() ?? 'Office Supplies', $bankAccounts->first() ?? 'Petty Cash', 50000, 'no', 'REF-001', ''],
            ['2026-05-01', 'Fuel for Generator',       $categories->get(1) ?? 'Fuel',            $bankAccounts->first() ?? 'Petty Cash', 120000, 'no', '', $suppliers->first() ?? ''],
        ];
        foreach ($examples as $r => $row) {
            foreach ($row as $col => $val) {
                $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . ($r + 2);
                $sheet->setCellValue($cellRef, $val);
            }
        }

        // Column widths
        foreach ([12, 40, 30, 30, 14, 14, 20, 30] as $col => $width) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->getColumnDimension($colLetter)->setWidth($width);
        }

        // ── Sheet 2: Hidden lists for dropdown values ────────────────
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('_lists');

        foreach ($categories as $r => $name) {
            $listSheet->setCellValue('A' . ($r + 1), $name);
        }
        foreach ($bankAccounts as $r => $name) {
            $listSheet->setCellValue('B' . ($r + 1), $name);
        }
        foreach ($suppliers as $r => $name) {
            $listSheet->setCellValue('C' . ($r + 1), $name);
        }

        $catCount      = max($categories->count(), 1);
        $bankCount     = max($bankAccounts->count(), 1);
        $supplierCount = max($suppliers->count(), 1);

        $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // Named ranges
        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
            'CategoryList', $listSheet, "'_lists'!\$A\$1:\$A\${$catCount}"
        ));
        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
            'BankList', $listSheet, "'_lists'!\$B\$1:\$B\${$bankCount}"
        ));
        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
            'SupplierList', $listSheet, "'_lists'!\$C\$1:\$C\${$supplierCount}"
        ));

        // ── Dropdowns on data rows 2–500 ─────────────────────────────
        $addDropdown = function (string $col, string $formula) use ($sheet) {
            $v = $sheet->getCell("{$col}2")->getDataValidation();
            $v->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
              ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
              ->setAllowBlank(false)
              ->setShowDropDown(false)  // false = show the arrow in Excel
              ->setShowErrorMessage(true)
              ->setErrorTitle('Invalid value')
              ->setError('Please select a value from the dropdown list.')
              ->setFormula1($formula);
            $sheet->setDataValidation("{$col}2:{$col}500", $v);
        };

        $addDropdown('C', 'CategoryList');
        $addDropdown('D', 'BankList');

        // Supplier dropdown (optional — allow blank)
        $suppV = $sheet->getCell('H2')->getDataValidation();
        $suppV->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(false)
              ->setFormula1('SupplierList');
        $sheet->setDataValidation('H2:H500', $suppV);

        // yes/no dropdown for is_zero_rated
        $yn = $sheet->getCell('F2')->getDataValidation();
        $yn->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
           ->setAllowBlank(false)
           ->setShowDropDown(false)
           ->setFormula1('"yes,no"');
        $sheet->setDataValidation('F2:F500', $yn);

        // ── Write to temp file and stream ────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);
        $writer  = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmpFile = tempnam(sys_get_temp_dir(), 'exp_tpl_');
        $writer->save($tmpFile);

        return response()->download($tmpFile, 'expenses-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function bulkImport()
    {
        $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.expenses.bulk-import', compact('categories', 'bankAccounts'));
    }

    public function bulkImportPreview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        $catMap      = $categories->keyBy(fn($c) => strtolower(trim($c->name)));
        $bankMap     = $bankAccounts->keyBy(fn($b) => strtolower(trim($b->name)));
        $supplierMap = Supplier::where('is_active', true)->get()->keyBy(fn($s) => strtolower(trim($s->name)));

        $file      = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows      = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // ── Read via PhpSpreadsheet ──────────────────────────────
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            // Use first sheet that isn't the hidden _lists sheet
            $sheet = null;
            foreach ($spreadsheet->getAllSheets() as $s) {
                if ($s->getTitle() !== '_lists') { $sheet = $s; break; }
            }
            if (!$sheet) {
                return back()->with('error', 'Could not find a data sheet in the uploaded file.');
            }
            $highestRow = $sheet->getHighestDataRow();
            $lineNum    = 1;
            for ($row = 2; $row <= $highestRow; $row++) {
                $lineNum++;
                $date        = trim((string) $sheet->getCell('A' . $row)->getFormattedValue());
                $description = trim((string) $sheet->getCell('B' . $row)->getValue());
                $catName     = trim((string) $sheet->getCell('C' . $row)->getValue());
                $bankName    = trim((string) $sheet->getCell('D' . $row)->getValue());
                $amount      = $sheet->getCell('E' . $row)->getValue();
                $isZeroRaw   = trim((string) $sheet->getCell('F' . $row)->getValue());
                $reference   = trim((string) $sheet->getCell('G' . $row)->getValue());
                $supplierName = trim((string) $sheet->getCell('H' . $row)->getValue());

                if ($date === '' && $description === '' && $catName === '') continue; // blank row

                $catMatch      = $catMap->get(strtolower($catName));
                $bankMatch     = $bankMap->get(strtolower($bankName));
                $supplierMatch = $supplierName !== '' ? $supplierMap->get(strtolower($supplierName)) : null;
                $isZero        = strtolower($isZeroRaw) === 'yes' || $isZeroRaw === '1';
                $amtVal        = (float) str_replace(',', '', (string) $amount);

                $errors = [];
                if (!$date || !\Carbon\Carbon::canBeCreatedFromFormat($date, 'Y-m-d')) $errors[] = 'Invalid date';
                if (!$description) $errors[] = 'Missing description';
                if (!$catMatch)    $errors[] = "Category not found: \"{$catName}\"";
                if (!$bankMatch)   $errors[] = "Bank account not found: \"{$bankName}\"";
                if ($amtVal <= 0)  $errors[] = 'Amount must be > 0';
                if ($supplierName !== '' && !$supplierMatch) $errors[] = "Supplier not found: \"{$supplierName}\"";

                $rows[] = [
                    'line'                => $lineNum,
                    'expense_date'        => $date,
                    'description'         => $description,
                    'category_name'       => $catName,
                    'expense_category_id' => $catMatch?->id,
                    'bank_account_name'   => $bankName,
                    'bank_account_id'     => $bankMatch?->id,
                    'supplier_name'       => $supplierName,
                    'supplier_id'         => $supplierMatch?->id,
                    'amount'              => $amtVal,
                    'is_zero_rated'       => $isZero,
                    'reference'           => $reference,
                    'errors'              => $errors,
                ];
            }
        } else {
            // ── Read CSV ─────────────────────────────────────────────
            $handle = fopen($file->getRealPath(), 'r');
            $bom    = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") { rewind($handle); }

            $headers = fgetcsv($handle); // skip header row
            $lineNum = 1;

            while (($cols = fgetcsv($handle)) !== false) {
                $lineNum++;
                if (count($cols) < 5) continue;

                [$date, $description, $catName, $bankName, $amount, $isZeroRaw, $reference, $supplierName] = array_pad($cols, 8, '');

                $catMatch      = $catMap->get(strtolower(trim($catName)));
                $bankMatch     = $bankMap->get(strtolower(trim($bankName)));
                $supplierName  = trim($supplierName);
                $supplierMatch = $supplierName !== '' ? $supplierMap->get(strtolower($supplierName)) : null;
                $isZero        = strtolower(trim($isZeroRaw)) === 'yes' || trim($isZeroRaw) === '1';
                $amtVal        = (float) str_replace(',', '', trim($amount));

                $errors = [];
                if (!$date || !\Carbon\Carbon::canBeCreatedFromFormat(trim($date), 'Y-m-d')) $errors[] = 'Invalid date';
                if (!$description) $errors[] = 'Missing description';
                if (!$catMatch)    $errors[] = "Category not found: \"{$catName}\"";
                if (!$bankMatch)   $errors[] = "Bank account not found: \"{$bankName}\"";
                if ($amtVal <= 0)  $errors[] = 'Amount must be > 0';
                if ($supplierName !== '' && !$supplierMatch) $errors[] = "Supplier not found: \"{$supplierName}\"";

                $rows[] = [
                    'line'                => $lineNum,
                    'expense_date'        => trim($date),
                    'description'         => trim($description),
                    'category_name'       => trim($catName),
                    'expense_category_id' => $catMatch?->id,
                    'bank_account_name'   => trim($bankName),
                    'bank_account_id'     => $bankMatch?->id,
                    'supplier_name'       => $supplierName,
                    'supplier_id'         => $supplierMatch?->id,
                    'amount'              => $amtVal,
                    'is_zero_rated'       => $isZero,
                    'reference'           => trim($reference),
                    'errors'              => $errors,
                ];
            }
            fclose($handle);
        }

        return view('admin.accounting.expenses.bulk-import-preview',
            compact('rows', 'categories', 'bankAccounts'));
    }

    public function bulkImportConfirm(Request $request)
    {
        $request->validate([
            'rows'                       => 'required|array|min:1',
            'rows.*.expense_date'        => 'required|date',
            'rows.*.description'         => 'required|string|max:500',
            'rows.*.expense_category_id' => 'required|exists:expense_categories,id',
            'rows.*.bank_account_id'     => 'required|exists:bank_accounts,id',
            'rows.*.supplier_id'         => 'nullable|exists:suppliers,id',
            'rows.*.amount'              => 'required|numeric|min:0.01',
            'rows.*.is_zero_rated'       => 'nullable|boolean',
            'rows.*.reference'           => 'nullable|string|max:100',
        ]);

        $saved = 0;
        foreach ($request->input('rows') as $row) {
            $amount = round((float) $row['amount'], 2);

            // Respect the imported is_zero_rated flag; also check the category's flag
            $isZero = !empty($row['is_zero_rated']);
            if (!$isZero) {
                $cat = ExpenseCategory::find($row['expense_category_id']);
                if ($cat && $cat->is_zero_rated) $isZero = true;
            }

            $vatRate = 0.18;
            $vat     = $isZero ? 0 : round($amount * $vatRate, 2);

            $expense = Expense::create([
                'expense_category_id' => $row['expense_category_id'],
                'bank_account_id'     => $row['bank_account_id'],
                'supplier_id'         => $row['supplier_id'] ?? null,
                'description'         => $row['description'],
                'amount'              => $amount,
                'is_zero_rated'       => $isZero,
                'vat_amount'          => $vat,
                'total_amount'        => $amount + $vat,
                'expense_date'        => $row['expense_date'],
                'reference'           => $row['reference'] ?? null,
                'status'              => 'draft',
                'created_by'          => auth()->id(),
            ]);

            UserActivityLog::record(
                auth()->id(), 'created',
                'Created expense ' . $expense->expense_number . ' (CSV import)',
                Expense::class, $expense->id
            );

            $saved++;
        }

        return redirect()->route('admin.accounting.expenses.index')
                         ->with('success', "{$saved} expense(s) imported and saved as draft.");
    }
}
