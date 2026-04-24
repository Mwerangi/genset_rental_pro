<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories    = ExpenseCategory::with('account')->withCount('expenses')->orderBy('name')->get();
        $unlinkedCount = $categories->whereNull('account_id')->count();
        return view('admin.accounting.expense-categories.index', compact('categories', 'unlinkedCount'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.accounting.expense-categories.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200|unique:expense_categories,name',
            'account_id'  => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        ExpenseCategory::create($data);

        return redirect()->route('admin.accounting.expense-categories.index')
                         ->with('success', 'Expense category created.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.accounting.expense-categories.edit', compact('expenseCategory', 'accounts'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200|unique:expense_categories,name,' . $expenseCategory->id,
            'account_id'  => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $expenseCategory->update($data);

        return redirect()->route('admin.accounting.expense-categories.index')
                         ->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }

        $expenseCategory->delete();

        return redirect()->route('admin.accounting.expense-categories.index')
                         ->with('success', 'Category deleted.');
    }
}
