<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines', 'createdBy'])->latest('entry_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }
        if ($request->filled('from')) {
            $query->where('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('entry_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('entry_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        $entries = $query->paginate(30)->withQueryString();

        $stats = [
            'total'  => JournalEntry::count(),
            'draft'  => JournalEntry::where('status', 'draft')->count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
        ];

        return view('admin.accounting.journal-entries.index', compact('entries', 'stats'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        return view('admin.accounting.journal-entries.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'              => 'required|date',
            'description'             => 'required|string|max:500',
            'reference'               => 'nullable|string|max:100',
            'notes'                   => 'nullable|string',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:accounts,id',
            'lines.*.description'     => 'nullable|string|max:500',
            'lines.*.debit'           => 'required|numeric|min:0',
            'lines.*.credit'          => 'required|numeric|min:0',
        ]);

        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withErrors(['lines' => 'Debits must equal credits.'])->withInput();
        }

        DB::transaction(function () use ($request) {
            $je = JournalEntry::create([
                'entry_date'  => $request->entry_date,
                'description' => $request->description,
                'reference'   => $request->reference,
                'source_type' => 'manual',
                'notes'       => $request->notes,
                'status'      => 'draft',
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    $je->lines()->create([
                        'account_id'  => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'debit'       => $line['debit'],
                        'credit'      => $line['credit'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.accounting.journal-entries.index')
                         ->with('success', 'Journal entry saved as draft.');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'createdBy', 'reversedBy']);
        return view('admin.accounting.journal-entries.show', compact('journalEntry'));
    }

    public function post(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'Entry is already posted.');
        }

        if (!$journalEntry->isBalanced()) {
            return back()->with('error', 'Journal entry is not balanced — debits must equal credits.');
        }

        $journalEntry->post();

        return back()->with('success', 'Journal entry posted successfully.');
    }

    public function reverse(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'posted') {
            return back()->with('error', 'Only posted entries can be reversed.');
        }

        if ($journalEntry->is_reversed) {
            return back()->with('error', 'Entry has already been reversed.');
        }

        $reason = $request->input('reason', 'Manual reversal');
        $reversed = $journalEntry->reverse($reason, auth()->id());

        return redirect()->route('admin.accounting.journal-entries.show', $reversed)
                         ->with('success', "Reversal entry {$reversed->entry_number} created and posted.");
    }
}
