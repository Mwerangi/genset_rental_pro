<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Full accounting managers see all credit notes; others see only ones they issued
        $seeAll = PermissionService::can($user, 'view_all_credit_notes');

        $query = CreditNote::with(['client', 'invoice', 'issuedBy'])->latest();

        if (!$seeAll) {
            $query->where('issued_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('cn_number', 'like', "%{$s}%")
                  ->orWhere('reason', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($q) => $q->where('full_name', 'like', "%{$s}%")
                                                      ->orWhere('company_name', 'like', "%{$s}%"));
            });
        }

        $creditNotes = $query->paginate(25)->withQueryString();
        $clients     = Client::orderBy('full_name')->get(['id', 'full_name', 'company_name']);

        $base   = $seeAll ? CreditNote::query() : CreditNote::where('issued_by', $user->id);
        $totals = [];
        foreach (['draft', 'issued', 'applied', 'voided'] as $s) {
            $totals[$s]             = (clone $base)->where('status', $s)->count();
            $totals[$s . '_amount'] = (clone $base)->where('status', $s)->sum('total_amount');
        }

        return view('admin.accounting.credit-notes.index', compact('creditNotes', 'clients', 'totals', 'seeAll'));
    }

    public function create(Request $request)
    {
        $clients  = Client::orderBy('full_name')->get(['id', 'full_name', 'company_name']);

        // Pre-select invoice if provided
        $invoice = $request->filled('invoice') ? Invoice::find($request->invoice) : null;

        // Paid/partial invoices that can be credited
        $invoices = Invoice::with('client')
                           ->whereIn('status', ['paid', 'partially_paid', 'sent'])
                           ->orderBy('invoice_number', 'desc')
                           ->get();

        return view('admin.accounting.credit-notes.create', compact('clients', 'invoice', 'invoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id'  => 'nullable|exists:invoices,id',
            'client_id'   => 'required|exists:clients,id',
            'reason'      => 'required|string|max:500',
            'amount'      => 'required|numeric|min:0.01',
            'vat_amount'  => 'nullable|numeric|min:0',
            'issued_date' => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        // If an invoice is linked, enforce client must match — prevent mismatch
        if (!empty($data['invoice_id'])) {
            $invoice = \App\Models\Invoice::find($data['invoice_id']);
            if ($invoice && (int) $invoice->client_id !== (int) $data['client_id']) {
                return back()->withInput()
                    ->withErrors(['client_id' => 'The selected client does not match the invoice\'s client.']);
            }
        }

        $data['total_amount'] = $data['amount'] + ($data['vat_amount'] ?? 0);
        $data['status']       = 'draft';
        $data['issued_by']    = auth()->id();

        CreditNote::create($data);

        return redirect()->route('admin.accounting.credit-notes.index')
                         ->with('success', 'Credit note saved as draft.');
    }

    public function show(CreditNote $creditNote)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_credit_notes') && $creditNote->issued_by !== $user->id) {
            abort(403, 'You do not have permission to view this credit note.');
        }
        $creditNote->load(['client', 'invoice', 'issuedBy', 'journalEntry.lines.account']);
        return view('admin.accounting.credit-notes.show', compact('creditNote'));
    }

    /** Issue (finalize) a draft credit note — creates JE */
    public function issue(CreditNote $creditNote)
    {
        if ($creditNote->status !== 'draft') {
            return back()->with('error', 'Only draft credit notes can be issued.');
        }

        $je = app(JournalEntryService::class)->onCreditNoteIssued($creditNote);

        $creditNote->update([
            'status'          => 'issued',
            'journal_entry_id' => $je?->id,
        ]);

        // If linked to an invoice, apply credit to invoice
        if ($creditNote->invoice_id) {
            $invoice = $creditNote->invoice;
            $newPaid = min($invoice->total_amount, (float) $invoice->amount_paid + (float) $creditNote->total_amount);
            $status  = $newPaid >= $invoice->total_amount ? 'paid' : 'partially_paid';
            $invoice->update(['amount_paid' => $newPaid, 'status' => $status]);
            $creditNote->update(['status' => 'applied']);
        }

        return back()->with('success', 'Credit note issued' . ($je ? " (JE: {$je->entry_number})." : '.'));
    }

    /** Void a credit note */
    public function void(CreditNote $creditNote)
    {
        if (!in_array($creditNote->status, ['issued', 'draft'])) {
            return back()->with('error', 'Cannot void this credit note.');
        }

        // Reverse the JE if already posted
        if ($creditNote->journalEntry && $creditNote->journalEntry->status === 'posted' && !$creditNote->journalEntry->is_reversed) {
            $creditNote->journalEntry->reverse('Credit note voided', auth()->id());
        }

        $creditNote->update(['status' => 'voided']);

        return back()->with('success', 'Credit note voided.');
    }
}
