<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\UserActivityLog;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Managers who can manage invoices see all; view-only users see only their own
        $seeAll = PermissionService::can($user, 'view_all_invoices');

        $cancelledStatuses = ['void', 'declined', 'written_off'];

        $query = Invoice::with(['client', 'booking', 'createdBy'])
            ->whereNotIn('status', $cancelledStatuses)
            ->latest();

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%"))
                  ->orWhereHas('booking', fn($q) => $q->where('booking_number', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->paginate(25);

        $base = $seeAll
            ? Invoice::whereNotIn('status', $cancelledStatuses)
            : Invoice::where('created_by', $user->id)->whereNotIn('status', $cancelledStatuses);
        $stats = [
            'total'          => (clone $base)->count(),
            'draft'          => (clone $base)->where('status', 'draft')->count(),
            'sent'           => (clone $base)->where('status', 'sent')->count(),
            'partially_paid' => (clone $base)->where('status', 'partially_paid')->count(),
            'paid'           => (clone $base)->where('status', 'paid')->count(),
            'overdue'        => (clone $base)->whereNotIn('status', ['paid', 'void', 'declined'])
                                    ->whereDate('due_date', '<', now())->count(),
            'total_outstanding' => (clone $base)->whereNotIn('status', ['paid', 'void', 'declined'])
                                        ->selectRaw('SUM((total_amount - amount_paid) * exchange_rate_to_tzs)')
                                        ->value('SUM((total_amount - amount_paid) * exchange_rate_to_tzs)') ?? 0,
        ];

        return view('admin.invoices.index', compact('invoices', 'stats', 'seeAll'));
    }

    public function voided(Request $request)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_invoices');

        $cancelledStatuses = ['void', 'declined', 'written_off'];

        $query = Invoice::with(['client', 'booking', 'createdBy'])
            ->whereIn('status', $cancelledStatuses)
            ->latest();

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%"))
                  ->orWhereHas('booking', fn($q) => $q->where('booking_number', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->paginate(25);

        $base = $seeAll
            ? Invoice::whereIn('status', $cancelledStatuses)
            : Invoice::where('created_by', $user->id)->whereIn('status', $cancelledStatuses);

        $stats = [
            'total'        => (clone $base)->count(),
            'void'         => (clone $base)->where('status', 'void')->count(),
            'declined'     => (clone $base)->where('status', 'declined')->count(),
            'written_off'  => (clone $base)->where('status', 'written_off')->count(),
        ];

        return view('admin.invoices.voided', compact('invoices', 'stats', 'seeAll'));
    }

    public function show(Invoice $invoice)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_invoices') && $invoice->created_by !== $user->id) {
            abort(403, 'You do not have permission to view this invoice.');
        }
        $invoice->load(['client', 'booking.genset', 'booking.gensets', 'quotation', 'items', 'payments.recordedBy', 'createdBy']);

        $bankAccounts = \App\Models\BankAccount::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'account_type', 'currency']);

        return view('admin.invoices.show', compact('invoice', 'bankAccounts'));
    }

    /**
     * Generate an invoice from an approved booking.
     */
    public function generate(Booking $booking)
    {
        if (!in_array($booking->status, ['approved', 'active', 'returned'])) {
            return back()->with('error', 'Invoice can only be generated for approved or active bookings.');
        }

        if ($booking->invoice_id) {
            return redirect()->route('admin.invoices.show', $booking->invoice)
                ->with('error', 'An invoice already exists for this booking.');
        }

        $booking->load('quotation.items', 'client');

        $quotation = $booking->quotation;

        // Determine pricing from quotation or booking total
        $subtotal    = $quotation ? (float) $quotation->subtotal : (float) $booking->total_amount;
        $isZeroRated = $booking->is_zero_rated ?: ($quotation ? (bool) $quotation->is_zero_rated : false);
        $vatRate     = $isZeroRated ? 0 : ($quotation ? (float) $quotation->vat_rate : 18.00);
        $vatAmount   = $isZeroRated ? 0 : ($quotation ? (float) $quotation->vat_amount : round($subtotal * $vatRate / 100, 2));
        $total       = $subtotal + $vatAmount;

        // Inherit currency from quotation → booking → default TZS
        $currency    = $quotation?->currency ?? $booking->currency ?? 'TZS';
        $exRate      = $quotation?->exchange_rate_to_tzs ?? $booking->exchange_rate_to_tzs ?? 1.0;

        $invoice = Invoice::create([
            'booking_id'           => $booking->id,
            'client_id'            => $booking->client_id,
            'quotation_id'         => $quotation?->id,
            'status'               => 'draft',
            'issue_date'           => now()->toDateString(),
            'due_date'             => now()->addDays(30)->toDateString(),
            'subtotal'             => $subtotal,
            'is_zero_rated'        => $isZeroRated,
            'vat_rate'             => $vatRate,
            'vat_amount'           => $vatAmount,
            'currency'             => $currency,
            'exchange_rate_to_tzs' => $exRate,
            'total_amount'         => $total,
            'amount_paid'          => 0,
            'payment_terms'        => $quotation?->payment_terms ?? 'Net 30',
            'terms_conditions'     => $quotation?->terms_conditions,
            'created_by'           => auth()->id(),
        ]);

        // Copy items from quotation, or create single line item from booking total
        if ($quotation && $quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $item) {
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'item_type'     => $item->item_type,
                    'description'   => $item->description,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'duration_days' => $item->duration_days,
                    'subtotal'      => $item->subtotal,
                ]);
            }
        } else {
            // Fallback: single line item
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'item_type'   => 'genset_rental',
                'description' => 'Genset Rental — ' . ($booking->booking_number),
                'quantity'    => 1,
                'unit_price'  => $subtotal,
                'subtotal'    => $subtotal,
            ]);
        }

        // Link invoice to booking
        $booking->update(['invoice_id' => $invoice->id]);

        UserActivityLog::record(
            auth()->id(), 'created',
            'Generated invoice ' . $invoice->invoice_number . ' for booking ' . $booking->booking_number,
            Invoice::class, $invoice->id
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated successfully.');
    }

    /**
     * Record a payment against an invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['void', 'declined', 'written_off'])) {
            return back()->with('error', 'Cannot record payment for a ' . $invoice->status . ' invoice.');
        }

        $validated = $request->validate([
            'payment_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,mpesa,bank_transfer,cheque,other',
            'reference'       => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:500',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'receipt_number'  => 'nullable|string|max:100',
        ]);

        $payment = InvoicePayment::create([
            'invoice_id'      => $invoice->id,
            'payment_date'    => $validated['payment_date'],
            'amount'          => $validated['amount'],
            'payment_method'  => $validated['payment_method'],
            'reference'       => $validated['reference'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'recorded_by'     => auth()->id(),
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'receipt_number'  => $validated['receipt_number'] ?? null,
        ]);

        // Post DR Bank / CR AR journal entry
        $je = app(JournalEntryService::class)->onPaymentRecorded($payment);

        if ($je) {
            $payment->update(['journal_entry_id' => $je->id]);
        }

        $invoice->recalculatePayments();

        $invoice->refresh();
        $sym = $invoice->currencySymbol();
        $msg = $invoice->status === 'paid'
            ? 'Payment recorded — invoice is now FULLY PAID!'
            : 'Payment of ' . $sym . ' ' . number_format($validated['amount'], 0) . ' recorded. Balance: ' . $sym . ' ' . number_format($invoice->balance_due, 0);

        UserActivityLog::record(
            auth()->id(), 'payment_recorded',
            'Recorded payment of ' . $sym . ' ' . number_format($validated['amount'], 0) . ' on invoice ' . $invoice->invoice_number,
            Invoice::class, $invoice->id
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', $msg);
    }

    /**
     * Delete a single payment (correction).
     */
    public function deletePayment(Invoice $invoice, InvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->delete();
        $invoice->recalculatePayments();

        return back()->with('success', 'Payment removed and invoice totals recalculated.');
    }

    /**
     * Mark invoice as sent (emailed/handed to client).
     */
    public function markSent(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be marked as sent.');
        }

        $invoice->markSent();

        // Auto-post AR / Revenue / VAT journal entry
        app(JournalEntryService::class)->onInvoiceSent($invoice);

        UserActivityLog::record(
            auth()->id(), 'invoiced',
            'Marked invoice ' . $invoice->invoice_number . ' as sent',
            Invoice::class, $invoice->id
        );

        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Void an invoice.
     */
    public function void(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be voided.');
        }

        $validated = $request->validate([
            'void_reason' => 'nullable|string|max:500',
        ]);

        $wasPosted = in_array($invoice->status, ['sent', 'partially_paid', 'disputed']);

        $invoice->voidInvoice($validated['void_reason'] ?? '');

        if ($wasPosted) {
            app(JournalEntryService::class)->onInvoiceVoided($invoice);
        }

        UserActivityLog::record(
            auth()->id(), 'voided',
            'Voided invoice ' . $invoice->invoice_number . ($validated['void_reason'] ?? '' ? ': ' . $validated['void_reason'] : ''),
            Invoice::class, $invoice->id
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' has been voided.');
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'booking.genset', 'booking.gensets', 'items', 'payments', 'createdBy']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    // ─── Amendment Methods ────────────────────────────────────────────────────

    /**
     * Add a new line item to an invoice.
     */
    public function storeItem(Request $request, Invoice $invoice)
    {
        if (!$invoice->is_editable) {
            return back()->with('error', 'This invoice cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'item_type'     => 'required|in:genset_rental,delivery,fuel,maintenance,extra_days,damage,penalty,credit,other',
            'description'   => 'required|string|max:500',
            'quantity'      => 'required|numeric|min:0.01',
            'unit_price'    => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
        ]);

        $subtotal = round($validated['quantity'] * $validated['unit_price'], 2);
        // For rental/extra-day items, multiply by duration_days when provided
        if (in_array($validated['item_type'], ['genset_rental', 'extra_days']) && !empty($validated['duration_days'])) {
            $subtotal = round($validated['quantity'] * $validated['unit_price'] * (int) $validated['duration_days'], 2);
        }
        // Credit items are negative
        if ($validated['item_type'] === 'credit') {
            $subtotal = -abs($subtotal);
        }

        $invoice->items()->create([
            'item_type'     => $validated['item_type'],
            'description'   => $validated['description'],
            'quantity'      => $validated['quantity'],
            'unit_price'    => $validated['unit_price'],
            'duration_days' => $validated['duration_days'] ?? null,
            'subtotal'      => $subtotal,
        ]);

        $invoice->recalculateTotals();

        return back()->with('success', 'Line item added.');
    }

    /**
     * Update an existing line item.
     */
    public function updateItem(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        if (!$invoice->is_editable) {
            return back()->with('error', 'This invoice cannot be edited in its current status.');
        }

        if ($item->invoice_id !== $invoice->id) {
            abort(403);
        }

        $validated = $request->validate([
            'item_type'     => 'required|in:genset_rental,delivery,fuel,maintenance,extra_days,damage,penalty,credit,other',
            'description'   => 'required|string|max:500',
            'quantity'      => 'required|numeric|min:0.01',
            'unit_price'    => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
        ]);

        $subtotal = round($validated['quantity'] * $validated['unit_price'], 2);
        // For rental/extra-day items, multiply by duration_days when provided
        if (in_array($validated['item_type'], ['genset_rental', 'extra_days']) && !empty($validated['duration_days'])) {
            $subtotal = round($validated['quantity'] * $validated['unit_price'] * (int) $validated['duration_days'], 2);
        }
        if ($validated['item_type'] === 'credit') {
            $subtotal = -abs($subtotal);
        }

        $item->update([
            'item_type'     => $validated['item_type'],
            'description'   => $validated['description'],
            'quantity'      => $validated['quantity'],
            'unit_price'    => $validated['unit_price'],
            'duration_days' => $validated['duration_days'] ?? null,
            'subtotal'      => $subtotal,
        ]);

        $invoice->recalculateTotals();

        return back()->with('success', 'Line item updated.');
    }

    /**
     * Delete a line item from an invoice.
     */
    public function deleteItem(Invoice $invoice, InvoiceItem $item)
    {
        if (!$invoice->is_editable) {
            return back()->with('error', 'This invoice cannot be edited in its current status.');
        }

        if ($item->invoice_id !== $invoice->id) {
            abort(403);
        }

        $item->delete();
        $invoice->recalculateTotals();

        return back()->with('success', 'Line item removed.');
    }

    /**
     * Update invoice discount.
     */
    public function updateDiscount(Request $request, Invoice $invoice)
    {
        if (!$invoice->is_editable) {
            return back()->with('error', 'This invoice cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
        ]);

        $invoice->update([
            'discount_amount' => $validated['discount_amount'],
            'discount_reason' => $validated['discount_reason'] ?? null,
        ]);

        $invoice->recalculateTotals();

        return back()->with('success', 'Discount updated.');
    }

    /**
     * Toggle zero-rated VAT on an invoice.
     */
    public function toggleZeroRated(Invoice $invoice)
    {
        if (!$invoice->is_editable) {
            return back()->with('error', 'This invoice cannot be edited in its current status.');
        }

        $newValue = !$invoice->is_zero_rated;

        $invoice->update([
            'is_zero_rated' => $newValue,
            'vat_rate'      => $newValue ? 0 : 18,
        ]);

        $invoice->recalculateTotals();

        $label = $newValue ? 'marked as zero rated' : 'VAT restored to 18%';
        return back()->with('success', 'Invoice ' . $label . '.');
    }

    /**
     * Reverse a payment (audit trail — payment record is kept).
     */
    public function reversePayment(Request $request, Invoice $invoice, InvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        if ($payment->is_reversed) {
            return back()->with('error', 'This payment has already been reversed.');
        }

        $validated = $request->validate([
            'reversal_note' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'is_reversed'  => true,
            'reversal_note'=> $validated['reversal_note'] ?? null,
            'reversed_at'  => now(),
        ]);

        $invoice->recalculatePayments();

        UserActivityLog::record(
            auth()->id(), 'payment_reversed',
            'Reversed a payment on invoice ' . $invoice->invoice_number,
            Invoice::class, $invoice->id
        );

        return back()->with('success', 'Payment reversed. Balance updated.');
    }

    /**
     * Mark invoice as disputed.
     */
    public function dispute(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['void', 'paid', 'written_off'])) {
            return back()->with('error', 'Cannot dispute a ' . $invoice->status . ' invoice.');
        }

        $validated = $request->validate([
            'dispute_reason' => 'required|string|max:1000',
        ]);

        $invoice->markDisputed($validated['dispute_reason']);

        return back()->with('success', 'Invoice marked as disputed.');
    }

    /**
     * Write off a disputed invoice.
     */
    public function writeOff(Invoice $invoice)
    {
        if ($invoice->status !== 'disputed') {
            return back()->with('error', 'Only disputed invoices can be written off.');
        }

        $invoice->writeOff();

        // Auto-post bad debt: DR Bad Debt Expense / CR Accounts Receivable
        app(JournalEntryService::class)->onInvoiceWrittenOff($invoice);

        UserActivityLog::record(
            auth()->id(), 'voided',
            'Wrote off invoice ' . $invoice->invoice_number,
            Invoice::class, $invoice->id
        );

        return back()->with('success', 'Invoice written off.');
    }

    /**
     * Generate a proforma invoice from a booking (no accounting impact).
     */
    public function generateProforma(Booking $booking)
    {
        if (!in_array($booking->status, ['created', 'approved', 'active', 'returned'])) {
            return back()->with('error', 'Proforma can only be generated for valid bookings.');
        }

        // Check if a proforma already exists for this booking
        $existing = Invoice::where('booking_id', $booking->id)
            ->where('invoice_type', 'proforma')
            ->where('status', '!=', 'void')
            ->first();

        if ($existing) {
            return redirect()->route('admin.invoices.show', $existing)
                ->with('info', 'A proforma already exists for this booking.');
        }

        $booking->load('quotation.items', 'client');
        $quotation  = $booking->quotation;
        $subtotal   = $quotation ? (float) $quotation->subtotal : (float) $booking->total_amount;
        $vatRate    = $quotation ? (float) $quotation->vat_rate : 18.00;
        $vatAmount  = round($subtotal * $vatRate / 100, 2);
        $total      = $subtotal + $vatAmount;

        $proforma = Invoice::create([
            'invoice_number'  => Invoice::generateProformaNumber(),
            'invoice_type'    => 'proforma',
            'booking_id'      => $booking->id,
            'client_id'       => $booking->client_id,
            'quotation_id'    => $quotation?->id,
            'status'          => 'draft',
            'issue_date'      => now()->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'subtotal'        => $subtotal,
            'is_zero_rated'   => false,
            'vat_rate'        => $vatRate,
            'vat_amount'      => $vatAmount,
            'total_amount'    => $total,
            'amount_paid'     => 0,
            'payment_terms'   => $quotation?->payment_terms ?? 'Net 30',
            'terms_conditions'=> $quotation?->terms_conditions,
            'notes'           => 'This is a proforma invoice and is not a tax invoice.',
            'created_by'      => auth()->id(),
        ]);

        if ($quotation && $quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $item) {
                InvoiceItem::create([
                    'invoice_id'    => $proforma->id,
                    'item_type'     => $item->item_type,
                    'description'   => $item->description,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'duration_days' => $item->duration_days,
                    'subtotal'      => $item->subtotal,
                ]);
            }
        } else {
            InvoiceItem::create([
                'invoice_id'  => $proforma->id,
                'item_type'   => 'genset_rental',
                'description' => 'Genset Rental — ' . $booking->booking_number,
                'quantity'    => 1,
                'unit_price'  => $subtotal,
                'subtotal'    => $subtotal,
            ]);
        }

        return redirect()
            ->route('admin.invoices.show', $proforma)
            ->with('success', 'Proforma invoice ' . $proforma->invoice_number . ' created.');
    }

    /**
     * Convert a proforma invoice into a proper tax invoice.
     */
    public function convertProforma(Invoice $invoice)
    {
        if (!$invoice->isProforma()) {
            return back()->with('error', 'Only proforma invoices can be converted.');
        }

        if ($invoice->booking && $invoice->booking->invoice_id) {
            return back()->with('error', 'A tax invoice already exists for this booking.');
        }

        // Create tax invoice
        $taxInvoice = $invoice->replicate(['invoice_number', 'invoice_type', 'converted_from_id']);
        $taxInvoice->invoice_number    = Invoice::generateInvoiceNumber();
        $taxInvoice->invoice_type      = 'tax_invoice';
        $taxInvoice->converted_from_id = $invoice->id;
        $taxInvoice->status            = 'draft';
        $taxInvoice->notes             = null;
        $taxInvoice->save();

        // Copy items
        foreach ($invoice->items as $item) {
            $newItem = $item->replicate(['invoice_id']);
            $newItem->invoice_id = $taxInvoice->id;
            $newItem->save();
        }

        // Link to booking
        if ($invoice->booking_id) {
            $invoice->booking->update(['invoice_id' => $taxInvoice->id]);
        }

        // Void the proforma
        $invoice->update(['status' => 'void', 'void_at' => now(), 'void_reason' => 'Converted to ' . $taxInvoice->invoice_number]);

        return redirect()
            ->route('admin.invoices.show', $taxInvoice)
            ->with('success', 'Proforma converted to tax invoice ' . $taxInvoice->invoice_number . '.');
    }
}
