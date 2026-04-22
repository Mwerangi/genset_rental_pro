<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $perPage = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100]) ? (int) $request->get('per_page', 25) : 25;

        // Latest created entry always on top
        $entries = $query->orderBy('created_at', 'desc')
                         ->paginate($perPage)
                         ->withQueryString();

        $stats = [
            'total'  => JournalEntry::count(),
            'draft'  => JournalEntry::where('status', 'draft')->count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
        ];

        // Dynamic source type list — always show all known types, plus any
        // additional ones found in the DB (future-proof).
        $knownLabels = [
            'manual'                    => 'Manual Entry',
            'invoice'                   => 'Invoice',
            'payment'                   => 'Payment',
            'purchase_order'            => 'Purchase Order',
            'supplier_payment'          => 'Supplier Payment',
            'expense'                   => 'Expense',
            'cash_request'              => 'Cash Request',
            'credit_note'               => 'Credit Note',
            'account_transfer'          => 'Account Transfer',
            'account_transfer_reversal' => 'Transfer Reversal',
            'bank_statement'            => 'Bank Statement',
            'genset'                    => 'Genset Capitalization',
            'fuel_log'                  => 'Fuel Log',
            'maintenance'               => 'Maintenance',
        ];
        $dbTypes = JournalEntry::query()
            ->selectRaw("COALESCE(source_type, 'manual') as src")
            ->distinct()
            ->pluck('src')
            ->toArray();
        // Merge: known labels first, then any DB-only extras
        $sourceTypes = collect($knownLabels)
            ->merge(
                collect($dbTypes)
                    ->diff(array_keys($knownLabels))
                    ->mapWithKeys(fn($v) => [$v => ucfirst(str_replace('_', ' ', $v))])
            )
            ->sortBy(fn($label) => $label)
            ->all();

        return view('admin.accounting.journal-entries.index', compact('entries', 'stats', 'perPage', 'sourceTypes'));
    }

    public function export(Request $request)
    {
        $entries = $this->buildQuery($request)
            ->with(['lines.account', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'journal-entries-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Entry #', 'Date', 'Description', 'Source', 'Status',
                'DR Account Code', 'DR Account Name', 'CR Account Code', 'CR Account Name',
                'Total Debit (TZS)', 'Created By',
            ]);

            foreach ($entries as $je) {
                $drLines = $je->lines->filter(fn($l) => $l->debit > 0);
                $crLines = $je->lines->filter(fn($l) => $l->credit > 0);

                $drCodes = $drLines->map(fn($l) => $l->account?->code ?? '')->join(' / ');
                $drNames = $drLines->map(fn($l) => $l->account?->name ?? '')->join(' / ');
                $crCodes = $crLines->map(fn($l) => $l->account?->code ?? '')->join(' / ');
                $crNames = $crLines->map(fn($l) => $l->account?->name ?? '')->join(' / ');

                fputcsv($handle, [
                    $je->entry_number,
                    $je->entry_date?->format('d/m/Y'),
                    $je->description,
                    ucfirst(str_replace('_', ' ', $je->source_type ?? 'manual')),
                    ucfirst($je->status),
                    $drCodes,
                    $drNames,
                    $crCodes,
                    $crNames,
                    number_format($je->lines->sum('debit'), 2, '.', ''),
                    $je->createdBy?->name ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery(Request $request)
    {
        $query = JournalEntry::with(['lines.account', 'createdBy']);

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

        return $query;
    }

    public function create()
    {
        $accounts     = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients      = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers    = Supplier::orderBy('name')->get(['id', 'name']);
        $suggestedRef = static::generateReferenceNumber();
        return view('admin.accounting.journal-entries.create', compact('accounts', 'clients', 'suppliers', 'suggestedRef'));
    }

    private static function generateReferenceNumber(): string
    {
        $prefix = 'MJE-' . now()->format('Y') . '-' . now()->format('md') . '-';
        $count  = JournalEntry::whereDate('created_at', today())->count();
        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'              => 'required|date',
            'reference'               => 'nullable|string|max:100',
            'notes'                   => 'nullable|string',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:accounts,id',
            'lines.*.partner'         => 'nullable|string',
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
                'description' => auth()->user()->name,
                'reference'   => $request->reference,
                'source_type' => 'manual',
                'notes'       => $request->notes,
                'status'      => 'draft',
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    [$partnerType, $partnerId] = static::parsePartner($line['partner'] ?? null);
                    $je->lines()->create([
                        'account_id'   => $line['account_id'],
                        'partner_type' => $partnerType,
                        'partner_id'   => $partnerId,
                        'description'  => $line['description'] ?? null,
                        'debit'        => $line['debit'],
                        'credit'       => $line['credit'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.accounting.journal-entries.index')
                         ->with('success', 'Journal entry saved as draft.');
    }

    private static function parsePartner(?string $value): array
    {
        if (!$value || !str_contains($value, ':')) return [null, null];
        [$type, $id] = explode(':', $value, 2);
        return in_array($type, ['client', 'supplier']) ? [$type, (int) $id] : [null, null];
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

        $reason = trim((string) $request->input('reason', '')) ?: 'Manual reversal';
        $reversed = $journalEntry->reverse($reason, auth()->id());

        // If this JE came from a bank statement posting, reset the bank transaction
        // back to pending so it can be re-posted with the correct contra account.
        if ($journalEntry->source_type === 'bank_statement') {
            $bankTx = BankTransaction::where('journal_entry_id', $journalEntry->id)->first();
            if ($bankTx) {
                // Undo the bank account current_balance change that was applied on posting
                $bankStatement = $bankTx->bankStatement;
                $bankAccount   = $bankStatement?->bankAccount;
                if ($bankAccount) {
                    if ($bankTx->type === 'credit') {
                        // Was incremented on post → decrement to undo
                        BankAccount::where('id', $bankAccount->id)->decrement('current_balance', (float) $bankTx->amount);
                    } else {
                        // Was decremented on post → increment to undo
                        BankAccount::where('id', $bankAccount->id)->increment('current_balance', (float) $bankTx->amount);
                    }
                }

                $bankTx->update([
                    'status'           => 'pending',
                    'journal_entry_id' => null,
                ]);
            }
        }

        return redirect()->route('admin.accounting.journal-entries.show', $reversed)
                         ->with('success', "Reversal entry {$reversed->entry_number} created and posted. The bank transaction has been reset to pending — you can re-post it with the correct settings.");
    }

    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return redirect()->route('admin.accounting.journal-entries.show', $journalEntry)
                             ->with('error', 'Only draft entries can be edited.');
        }

        $journalEntry->load('lines');
        $accounts  = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        $clients   = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.accounting.journal-entries.edit', compact('journalEntry', 'accounts', 'clients', 'suppliers'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return back()->with('error', 'Only draft entries can be edited.');
        }

        $request->validate([
            'entry_date'              => 'required|date',
            'reference'               => 'nullable|string|max:100',
            'notes'                   => 'nullable|string',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:accounts,id',
            'lines.*.partner'         => 'nullable|string',
            'lines.*.description'     => 'nullable|string|max:500',
            'lines.*.debit'           => 'required|numeric|min:0',
            'lines.*.credit'          => 'required|numeric|min:0',
        ]);

        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withErrors(['lines' => 'Debits must equal credits.'])->withInput();
        }

        DB::transaction(function () use ($request, $journalEntry) {
            $journalEntry->update([
                'entry_date' => $request->entry_date,
                'reference'  => $request->reference,
                'notes'      => $request->notes,
            ]);

            $journalEntry->lines()->delete();

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    [$partnerType, $partnerId] = static::parsePartner($line['partner'] ?? null);
                    $journalEntry->lines()->create([
                        'account_id'   => $line['account_id'],
                        'partner_type' => $partnerType,
                        'partner_id'   => $partnerId,
                        'description'  => $line['description'] ?? null,
                        'debit'        => $line['debit'],
                        'credit'       => $line['credit'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.accounting.journal-entries.show', $journalEntry)
                         ->with('success', 'Journal entry updated successfully.');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        $user = auth()->user();

        if ($journalEntry->status === 'posted') {
            if (!$user->hasPermission('force_delete_journal_entries')) {
                return back()->with('error', 'You do not have permission to delete posted journal entries.');
            }
        }

        $number = $journalEntry->entry_number;
        $label  = $journalEntry->status === 'posted' ? 'Posted' : 'Draft';
        $journalEntry->delete();

        return redirect()->route('admin.accounting.journal-entries.index')
                         ->with('success', "{$label} entry {$number} deleted.");
    }
}
