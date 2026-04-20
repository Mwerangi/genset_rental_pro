<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $type   = $request->get('type');
        $query  = Account::with('parent')
                    ->when($type, fn($q) => $q->where('type', $type))
                    ->orderBy('code');

        $accounts = $query->get();

        $totals = [
            'asset'     => Account::where('type', 'asset')->sum('balance'),
            'liability' => Account::where('type', 'liability')->sum('balance'),
            'equity'    => Account::where('type', 'equity')->sum('balance'),
            'revenue'   => Account::where('type', 'revenue')->sum('balance'),
            'expense'   => Account::where('type', 'expense')->sum('balance'),
        ];

        return view('admin.accounting.accounts.index', compact('accounts', 'totals', 'type'));
    }

    public function create()
    {
        $parents = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        return view('admin.accounting.accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:accounts,code',
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:asset,liability,equity,revenue,expense',
            'sub_type'       => 'nullable|string|max:100',
            'parent_id'      => 'nullable|exists:accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'currency'       => 'nullable|string|max:10',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        Account::create($data + ['is_system' => false]);

        return redirect()->route('admin.accounting.accounts.index')
                         ->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        $account->load(['parent', 'children', 'journalEntryLines.journalEntry']);

        $lines = $account->journalEntryLines()
                         ->with('journalEntry')
                         ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                         ->latest('created_at')
                         ->paginate(30);

        return view('admin.accounting.accounts.show', compact('account', 'lines'));
    }

    public function edit(Account $account)
    {
        $parents = Account::where('is_active', true)
                          ->where('id', '!=', $account->id)
                          ->orderBy('code')->get(['id', 'code', 'name', 'type']);

        return view('admin.accounting.accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'sub_type'       => 'nullable|string|max:100',
            'parent_id'      => 'nullable|exists:accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'currency'       => 'nullable|string|max:10',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        // Don't allow type or code changes on system accounts
        if (!$account->is_system) {
            $data += $request->validate([
                'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
                'type' => 'required|in:asset,liability,equity,revenue,expense',
            ]);
        }

        $account->update($data);

        return redirect()->route('admin.accounting.accounts.index')
                         ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }

        if ($account->journalEntryLines()->exists()) {
            return back()->with('error', 'Cannot delete account with journal entry lines.');
        }

        $account->delete();

        return redirect()->route('admin.accounting.accounts.index')
                         ->with('success', 'Account deleted.');
    }
}
