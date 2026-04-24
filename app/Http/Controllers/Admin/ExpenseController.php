<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\UserActivityLog;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_expenses');

        $query = Expense::with(['category', 'bankAccount', 'createdBy'])->latest('expense_date');

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
}
