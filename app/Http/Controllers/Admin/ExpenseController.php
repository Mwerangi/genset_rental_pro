<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Accounting managers and approvers see all expenses; others see only their own
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

        $expenses   = $query->paginate(25)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        $base = $seeAll ? Expense::query() : Expense::where('created_by', $user->id);
        $stats = [
            'total_this_month' => (clone $base)->whereMonth('expense_date', now()->month)
                                               ->whereYear('expense_date', now()->year)
                                               ->whereNotIn('status', ['draft'])
                                               ->sum('total_amount'),
            'pending_approval' => (clone $base)->where('status', 'draft')->count(),
            'posted'           => (clone $base)->where('status', 'posted')->count(),
        ];

        return view('admin.accounting.expenses.index', compact('expenses', 'categories', 'stats', 'seeAll'));
    }

    public function create()
    {
        $categories   = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
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
            'vat_amount'          => 'nullable|numeric|min:0',
            'expense_date'        => 'required|date',
            'reference'           => 'nullable|string|max:100',
            'attachment'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $data['total_amount'] = $data['amount'] + ($data['vat_amount'] ?? 0);
        $data['status']       = 'draft';
        $data['created_by']   = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        Expense::create($data);

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
        $expense->load(['category', 'bankAccount', 'journalEntry.lines.account', 'createdBy', 'approvedBy']);
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

        return back()->with('success', "Expense posted. Journal entry {$je->entry_number} created.");
    }

    public function destroy(Expense $expense)
    {
        if ($expense->status === 'posted') {
            return back()->with('error', 'Posted expenses cannot be deleted. Reverse the journal entry instead.');
        }

        $expense->delete();

        return redirect()->route('admin.accounting.expenses.index')
                         ->with('success', 'Expense deleted.');
    }
}
