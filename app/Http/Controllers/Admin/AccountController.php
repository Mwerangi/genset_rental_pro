<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $type    = $request->get('type');
        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $query  = Account::with('parent')
                    ->when($type, fn($q) => $q->where('type', $type))
                    ->orderBy('code');

        $accounts = $query->paginate($perPage)->withQueryString();

        // Build a recursive rolled-up balance map: each parent shows sum of all descendants
        $allAccs    = Account::get(['id', 'parent_id', 'balance']);
        $rawBal     = $allAccs->pluck('balance', 'id')->map(fn($v) => (float) $v)->toArray();
        $childrenOf = [];
        foreach ($allAccs as $a) {
            if ($a->parent_id) {
                $childrenOf[$a->parent_id][] = $a->id;
            }
        }
        $rolledBalances = [];
        $rollup = null;
        $rollup = function (int $id) use (&$rollup, $childrenOf, $rawBal, &$rolledBalances): float {
            if (array_key_exists($id, $rolledBalances)) return $rolledBalances[$id];
            $total = $rawBal[$id] ?? 0.0;
            foreach ($childrenOf[$id] ?? [] as $cid) {
                $total += $rollup($cid);
            }
            return $rolledBalances[$id] = $total;
        };
        foreach ($allAccs as $a) {
            $rollup($a->id);
        }

        $totals = [
            'asset'     => Account::where('type', 'asset')->sum('balance'),
            'liability' => Account::where('type', 'liability')->sum('balance'),
            'equity'    => Account::where('type', 'equity')->sum('balance'),
            'revenue'   => Account::where('type', 'revenue')->sum('balance'),
            'expense'   => Account::where('type', 'expense')->sum('balance'),
        ];

        return view('admin.accounting.accounts.index', compact('accounts', 'totals', 'type', 'perPage', 'rolledBalances'));
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
        $account->load(['parent', 'children']);

        // Order lines oldest-first so running balance is chronologically meaningful
        $lines = $account->journalEntryLines()
                         ->with('journalEntry')
                         ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                         ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                         ->orderBy('je.entry_date', 'asc')
                         ->orderBy('journal_entry_lines.id', 'asc')
                         ->select('journal_entry_lines.*')
                         ->paginate(30);

        // Compute the net balance of all lines BEFORE this page so we can display
        // a per-row running balance. Split into two queries: first fetch the prior
        // row IDs (ORDER BY + LIMIT), then aggregate over those IDs (no LIMIT + SUM).
        $offset = ($lines->currentPage() - 1) * $lines->perPage();
        if ($offset > 0) {
            $priorIds = $account->journalEntryLines()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->join('journal_entries as je2', 'journal_entry_lines.journal_entry_id', '=', 'je2.id')
                ->orderBy('je2.entry_date', 'asc')
                ->orderBy('journal_entry_lines.id', 'asc')
                ->select('journal_entry_lines.id')
                ->take($offset)
                ->pluck('journal_entry_lines.id');

            $priorNet = (float) $account->journalEntryLines()
                ->whereIn('id', $priorIds)
                ->selectRaw('SUM(debit) - SUM(credit) as net')
                ->value('net');
        } else {
            $priorNet = 0.0;
        }

        // Opening balance for this page in "normal balance direction"
        $openingBalance = $account->normal_balance === 'debit' ? $priorNet : -$priorNet;

        return view('admin.accounting.accounts.show', compact('account', 'lines', 'openingBalance'));
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
