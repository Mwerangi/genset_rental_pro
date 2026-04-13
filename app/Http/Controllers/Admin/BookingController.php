<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Genset;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\QuoteRequest;
use App\Models\UserActivityLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Managers/approvers see all bookings; limited users see only their own created bookings
        $seeAll = PermissionService::can($user, 'view_all_bookings');

        $cancelledStatuses = ['cancelled', 'rejected'];

        $query = Booking::with(['quoteRequest', 'client', 'createdBy', 'approvedBy'])
            ->whereNotIn('status', $cancelledStatuses)
            ->latest();

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(25);

        $base = $seeAll
            ? Booking::whereNotIn('status', $cancelledStatuses)
            : Booking::where('created_by', $user->id)->whereNotIn('status', $cancelledStatuses);
        $stats = [
            'total'    => (clone $base)->count(),
            'created'  => (clone $base)->where('status', 'created')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'active'   => (clone $base)->where('status', 'active')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats', 'seeAll'));
    }

    public function cancelled(Request $request)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_bookings');

        $cancelledStatuses = ['cancelled', 'rejected'];

        $query = Booking::with(['quoteRequest', 'createdBy', 'cancelledBy'])
            ->whereIn('status', $cancelledStatuses)
            ->latest();

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(25);

        $base = $seeAll
            ? Booking::whereIn('status', $cancelledStatuses)
            : Booking::where('created_by', $user->id)->whereIn('status', $cancelledStatuses);
        $stats = [
            'total'     => (clone $base)->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'rejected'  => (clone $base)->where('status', 'rejected')->count(),
        ];

        return view('admin.bookings.cancelled', compact('bookings', 'stats', 'seeAll'));
    }

    public function show(Booking $booking)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_bookings');
        if (!$seeAll && $booking->created_by !== $user->id) {
            abort(403, 'You do not have permission to view this booking.');
        }

        $booking->load([
            'quoteRequest', 'quotation.items', 'client',
            'createdBy', 'approvedBy', 'activatedBy',
            'returnedBy', 'invoicedBy', 'paidBy', 'cancelledBy',
            'genset', 'deliveries',
        ]);

        $availableGensets = Genset::where('status', 'available')->orderBy('asset_number')->get();

        return view('admin.bookings.show', compact('booking', 'availableGensets'));
    }

    public function create()
    {
        return redirect()->route('admin.quotations.index')
            ->with('info', 'Bookings are created automatically when a quotation is approved. Please start from a quotation.');
    }

    public function store()
    {
        return redirect()->route('admin.quotations.index')
            ->with('info', 'Bookings are created automatically when a quotation is approved. Please start from a quotation.');
    }

    /** @deprecated kept as tombstone — direct booking creation is disabled */
    private function _storeDeprecated(Request $request)
    {
        $validated = $request->validate([
            'quote_request_id'     => 'nullable|exists:quote_requests,id',
            'customer_name'        => 'required_without:quote_request_id|nullable|string|max:255',
            'customer_email'       => 'required_without:quote_request_id|nullable|email|max:255',
            'customer_phone'       => 'nullable|string|max:50',
            'company_name'         => 'nullable|string|max:255',
            'genset_type'          => 'required|string|max:100',
            'rental_start_date'    => 'required|date',
            'rental_duration_days' => 'required|integer|min:1',
            'drop_on_location'     => 'required|string|max:500',
            'drop_off_location'    => 'nullable|string|max:500',
            'destination'          => 'nullable|string|max:500',
            'total_amount'         => 'required|numeric|min:0',
            'currency'             => 'nullable|in:TZS,USD',
            'exchange_rate_to_tzs' => 'required_if:currency,USD|nullable|numeric|min:0.0001',
            'notes'                => 'nullable|string',
        ]);

        $currency = $validated['currency'] ?? 'TZS';

        $startDate = \Carbon\Carbon::parse($validated['rental_start_date']);
        $endDate   = $startDate->copy()->addDays((int) $validated['rental_duration_days']);

        $booking = Booking::create([
            'quote_request_id'     => $validated['quote_request_id'] ?? null,
            'quotation_id'         => null,
            'status'               => 'created',
            'genset_type'          => $validated['genset_type'],
            'rental_start_date'    => $startDate,
            'rental_end_date'      => $endDate,
            'rental_duration_days' => $validated['rental_duration_days'],
            'drop_on_location'     => $validated['drop_on_location'],
            'drop_off_location'    => $validated['drop_off_location'] ?? null,
            'destination'          => $validated['destination'] ?? null,
            'total_amount'         => $validated['total_amount'],
            'currency'             => $currency,
            'exchange_rate_to_tzs' => $currency === 'USD' ? $validated['exchange_rate_to_tzs'] : 1.0,
            'notes'                => $validated['notes'] ?? null,
            'customer_name'        => !$validated['quote_request_id'] ? ($validated['customer_name'] ?? null) : null,
            'customer_email'       => !$validated['quote_request_id'] ? ($validated['customer_email'] ?? null) : null,
            'customer_phone'       => !$validated['quote_request_id'] ? ($validated['customer_phone'] ?? null) : null,
            'company_name'         => !$validated['quote_request_id'] ? ($validated['company_name'] ?? null) : null,
            'created_by'           => auth()->id(),
        ]);

        AppNotification::notify(
            null,
            'booking',
            'New Booking: ' . $booking->booking_number,
            ($booking->client?->display_name ?? $booking->customer_name) . ' — awaiting approval.',
            route('admin.bookings.show', $booking),
            'booking'
        );

        UserActivityLog::record(
            auth()->id(), 'created',
            'Created booking ' . $booking->booking_number,
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' created successfully!');
    }

    public function approve(Booking $booking)
    {
        if (!$booking->canBeApproved()) {
            return back()->with('error', 'Only newly created bookings can be approved.');
        }

        $booking->approve(auth()->id());

        AppNotification::notify(
            $booking->created_by ?? null,
            'booking',
            'Booking Approved: ' . $booking->booking_number,
            'Your booking has been approved and is ready for activation.',
            route('admin.bookings.show', $booking),
            'booking'
        );

        UserActivityLog::record(
            auth()->id(), 'approved',
            'Approved booking ' . $booking->booking_number,
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been approved.');
    }

    public function reject(Request $request, Booking $booking)
    {
        if (!$booking->canBeRejected()) {
            return back()->with('error', 'This booking cannot be rejected at its current stage.');
        }

        $booking->reject(auth()->id(), $request->input('reason'));

        AppNotification::notify(
            $booking->created_by ?? null,
            'booking',
            'Booking Rejected: ' . $booking->booking_number,
            $request->input('reason') ? 'Reason: ' . $request->input('reason') : null,
            route('admin.bookings.show', $booking),
            'booking'
        );

        UserActivityLog::record(
            auth()->id(), 'rejected',
            'Rejected booking ' . $booking->booking_number . ($request->input('reason') ? ': ' . $request->input('reason') : ''),
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been rejected.');
    }

    public function activate(Request $request, Booking $booking)
    {
        if (!$booking->canBeActivated()) {
            return back()->with('error', 'Only approved bookings can be activated.');
        }

        $booking->load('gensets', 'genset');

        // Resolve all assigned gensets (pivot first, fall back to legacy FK)
        $assignedGensets = $booking->gensets->isNotEmpty()
            ? $booking->gensets
            : ($booking->genset ? collect([$booking->genset]) : collect());

        if ($assignedGensets->isEmpty()) {
            return back()->with('error', 'No gensets are assigned to this booking. Please re-assign from the quotation.');
        }

        $booking->activate(auth()->id());

        // Mark every assigned genset as rented
        $assetNumbers = $assignedGensets->pluck('asset_number')->join(', ');
        \App\Models\Genset::whereIn('id', $assignedGensets->pluck('id'))->update(['status' => 'rented']);

        UserActivityLog::record(
            auth()->id(), 'activated',
            'Activated booking ' . $booking->booking_number . ' — genset(s) deployed: ' . $assetNumbers,
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking activated! Genset(s) deployed: ' . $assetNumbers);
    }

    public function markReturned(Booking $booking)
    {
        if (!$booking->canBeMarkedReturned()) {
            return back()->with('error', 'Only active bookings can be marked as returned.');
        }

        $booking->markReturned(auth()->id());

        UserActivityLog::record(
            auth()->id(), 'returned',
            'Marked booking ' . $booking->booking_number . ' as returned',
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' marked as returned.');
    }

    public function markInvoiced(Request $request, Booking $booking)
    {
        if (!$booking->canBeInvoiced()) {
            return back()->with('error', 'Only returned bookings can be invoiced.');
        }

        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:100',
        ]);

        $booking->markInvoiced(auth()->id(), $validated['invoice_number'] ?? null);

        UserActivityLog::record(
            auth()->id(), 'invoiced',
            'Marked booking ' . $booking->booking_number . ' as invoiced',
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been invoiced.');
    }

    public function markPaid(Request $request, Booking $booking)
    {
        if (!$booking->canBeMarkedPaid()) {
            return back()->with('error', 'Only invoiced bookings can be marked as paid.');
        }

        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $booking->markPaid(auth()->id(), $validated['payment_reference'] ?? null);

        UserActivityLog::record(
            auth()->id(), 'paid',
            'Marked booking ' . $booking->booking_number . ' as paid',
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been marked as paid!');
    }

    public function cancel(Request $request, Booking $booking)
    {
        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'This booking cannot be cancelled at its current stage.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->cancel(auth()->id(), $validated['reason'] ?? null);

        UserActivityLog::record(
            auth()->id(), 'cancelled',
            'Cancelled booking ' . $booking->booking_number . ($validated['reason'] ? ': ' . $validated['reason'] : ''),
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been cancelled.');
    }

    public function contractPdf(Booking $booking)
    {
        $booking->load(['client', 'genset', 'gensets', 'quotation.items', 'approvedBy']);

        // Resolve all assigned gensets: prefer pivot relation, fall back to legacy FK
        $contractGensets = $booking->gensets->isNotEmpty()
            ? $booking->gensets
            : ($booking->genset ? collect([$booking->genset]) : collect());

        $client = $booking->client;

        $clientName     = $client?->display_name
                            ?? $booking->company_name
                            ?? $booking->customer_name
                            ?? 'N/A';
        $clientTin      = $client?->tin_number;
        $clientVrn      = $client?->vrn;
        $clientPhone    = $client?->phone ?? $booking->customer_phone;
        $clientEmail    = $client?->email ?? $booking->customer_email;
        $clientAddress  = null; // Client model has no dedicated address field
        $clientLocation = null;

        $quotationItems = $booking->quotation?->items ?? collect();
        $currency       = $booking->currency ?? 'TZS';
        $totalAmount    = (float) $booking->total_amount;

        // Try to derive VAT from quotation; default to 0 if not available
        $vatRate   = 18;
        $vatAmount = $quotationItems->isNotEmpty()
            ? (float) ($booking->quotation?->vat_amount ?? 0)
            : 0;
        $subtotal  = $totalAmount - $vatAmount;

        // Lift-on/off and transport from quotation items if present
        $lifting_fee   = null;
        $transport_fee = null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.bookings.contract-pdf', compact(
            'booking',
            'contractGensets',
            'clientName',
            'clientAddress',
            'clientLocation',
            'clientTin',
            'clientVrn',
            'clientPhone',
            'clientEmail',
            'quotationItems',
            'currency',
            'totalAmount',
            'vatAmount',
            'vatRate',
            'subtotal',
            'lifting_fee',
            'transport_fee'
        ));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('CONTRACT-' . $booking->booking_number . '.pdf');
    }

    public function activeRentals()
    {
        $rentals = Booking::with(['quoteRequest', 'client', 'genset', 'activatedBy'])
            ->where('status', 'active')
            ->orderBy('activated_at', 'asc')
            ->get();

        return view('admin.bookings.active-rentals', compact('rentals'));
    }

    public function edit(Booking $booking)
    {
        if (!in_array($booking->status, ['created', 'approved'])) {
            return redirect()->route('admin.bookings.show', $booking)
                ->with('error', 'Only bookings in Created or Approved status can be edited.');
        }

        $quoteRequests = QuoteRequest::whereNotIn('status', ['converted', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        $gensets = Genset::where('status', 'available')->orderBy('asset_number')->get();

        // All genset types for the dropdown (distinct type+kva from entire fleet)
        $gensetTypeOptions = Genset::select('type', 'kva_rating', 'name')
            ->orderBy('kva_rating')
            ->orderBy('type')
            ->get()
            ->unique(fn($g) => $g->type . '_' . $g->kva_rating)
            ->values();

        return view('admin.bookings.edit', compact('booking', 'quoteRequests', 'gensets', 'gensetTypeOptions'));
    }

    public function update(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['created', 'approved'])) {
            return back()->with('error', 'Only bookings in Created or Approved status can be edited.');
        }

        $wasApproved      = $booking->status === 'approved';
        $previousApproverId = $booking->approved_by;

        $validated = $request->validate([
            'genset_type'          => 'required|string|max:100',
            'rental_start_date'    => 'required|date',
            'rental_duration_days' => 'required|integer|min:1',
            'drop_on_location'     => 'required|string|max:500',
            'drop_off_location'    => 'nullable|string|max:500',
            'destination'          => 'nullable|string|max:500',
            'total_amount'         => 'required|numeric|min:0',
            'is_zero_rated'        => 'nullable|boolean',
            'currency'             => 'nullable|in:TZS,USD',
            'exchange_rate_to_tzs' => 'required_if:currency,USD|nullable|numeric|min:0.0001',
            'notes'                => 'nullable|string',
        ]);

        $currency  = $validated['currency'] ?? $booking->currency ?? 'TZS';
        $startDate = \Carbon\Carbon::parse($validated['rental_start_date']);
        $endDate   = $startDate->copy()->addDays((int) $validated['rental_duration_days']);

        $updateData = [
            'genset_type'          => $validated['genset_type'],
            'rental_start_date'    => $startDate,
            'rental_end_date'      => $endDate,
            'rental_duration_days' => $validated['rental_duration_days'],
            'drop_on_location'     => $validated['drop_on_location'],
            'drop_off_location'    => $validated['drop_off_location'] ?? null,
            'destination'          => $validated['destination'] ?? null,
            'total_amount'         => $validated['total_amount'],
            'is_zero_rated'        => !empty($validated['is_zero_rated']),
            'currency'             => $currency,
            'exchange_rate_to_tzs' => $currency === 'USD' ? $validated['exchange_rate_to_tzs'] : 1.0,
            'notes'                => $validated['notes'] ?? null,
        ];

        // If the booking was approved, revoke the approval so it must be re-approved
        if ($wasApproved) {
            $updateData['status']      = 'created';
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;
        }

        $booking->update($updateData);

        UserActivityLog::record(
            auth()->id(), 'updated',
            'Updated booking ' . $booking->booking_number . ($wasApproved ? ' (approval revoked — re-approval required)' : ''),
            Booking::class, $booking->id
        );

        // Notify the original approver that their approval has been invalidated
        if ($wasApproved && $previousApproverId) {
            AppNotification::notify(
                $previousApproverId,
                'booking',
                'Re-approval Required: ' . $booking->booking_number,
                'Booking details were edited by ' . auth()->user()->name . ' after your approval. Please review and re-approve.',
                route('admin.bookings.show', $booking),
                'booking'
            );
        }

        $message = $wasApproved
            ? 'Booking ' . $booking->booking_number . ' updated. Approval has been revoked — the booking must be re-approved before deployment.'
            : 'Booking ' . $booking->booking_number . ' updated successfully.';

        $flashKey = $wasApproved ? 'warning' : 'success';

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with($flashKey, $message);
    }

    // ── Historical Sales ──────────────────────────────────────────────────────

    public function recordHistoricalForm()
    {
        $clients = Client::orderBy('company_name')->orderBy('full_name')->get(['id', 'company_name', 'full_name', 'client_number']);
        $gensets = Genset::orderBy('asset_number')->get(['id', 'asset_number', 'name', 'kva_rating', 'type']);
        return view('admin.bookings.record-historical', compact('clients', 'gensets'));
    }

    public function storeHistorical(Request $request)
    {
        $validated = $request->validate([
            'client_id'            => 'required|exists:clients,id',
            'genset_id'            => 'nullable|exists:gensets,id',
            'genset_type'          => 'nullable|string|max:100',
            'rental_start_date'    => 'required|date',
            'rental_end_date'      => 'required|date|after_or_equal:rental_start_date',
            'drop_on_location'     => 'required|string|max:500',
            'drop_off_location'    => 'nullable|string|max:500',
            'destination'          => 'nullable|string|max:500',
            'currency'             => 'required|in:TZS,USD',
            'exchange_rate_to_tzs' => 'required_if:currency,USD|nullable|numeric|min:0.0001',
            'subtotal'             => 'required|numeric|min:0',
            'is_zero_rated'        => 'nullable|boolean',
            'description'          => 'required|string|max:500',
            'payment_date'         => 'required|date',
            'payment_method'       => 'required|in:cash,mpesa,bank_transfer,cheque,other',
            'payment_reference'    => 'nullable|string|max:100',
            'issue_date'           => 'required|date',
            'notes'                => 'nullable|string',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['rental_start_date']);
        $endDate   = \Carbon\Carbon::parse($validated['rental_end_date']);
        $durationDays = $startDate->diffInDays($endDate);

        $currency    = $validated['currency'];
        $exRate      = $currency === 'USD' ? (float) $validated['exchange_rate_to_tzs'] : 1.0;
        $subtotal    = (float) $validated['subtotal'];
        $isZeroRated = !empty($validated['is_zero_rated']);
        $vatRate     = $isZeroRated ? 0 : 18.00;
        $vatAmount   = $isZeroRated ? 0 : round($subtotal * $vatRate / 100, 2);
        $total       = $subtotal + $vatAmount;

        DB::transaction(function () use ($validated, $startDate, $endDate, $durationDays, $currency, $exRate, $subtotal, $isZeroRated, $vatRate, $vatAmount, $total) {
            // Create booking (pre-completed)
            $booking = Booking::create([
                'client_id'            => $validated['client_id'],
                'genset_id'            => $validated['genset_id'] ?? null,
                'genset_type'          => $validated['genset_type'] ?? null,
                'status'               => 'paid',
                'is_historical'        => true,
                'rental_start_date'    => $startDate,
                'rental_end_date'      => $endDate,
                'rental_duration_days' => $durationDays,
                'drop_on_location'     => $validated['drop_on_location'],
                'drop_off_location'    => $validated['drop_off_location'] ?? null,
                'destination'          => $validated['destination'] ?? null,
                'currency'             => $currency,
                'exchange_rate_to_tzs' => $exRate,
                'notes'                => $validated['notes'] ?? null,
                'created_by'           => auth()->id(),
            ]);

            // Create invoice (already paid)
            $invoice = Invoice::create([
                'booking_id'           => $booking->id,
                'client_id'            => $validated['client_id'],
                'status'               => 'paid',
                'issue_date'           => $validated['issue_date'],
                'due_date'             => $validated['issue_date'],
                'subtotal'             => $subtotal,
                'is_zero_rated'        => $isZeroRated,
                'vat_rate'             => $vatRate,
                'vat_amount'           => $vatAmount,
                'currency'             => $currency,
                'exchange_rate_to_tzs' => $exRate,
                'total_amount'         => $total,
                'amount_paid'          => $total,
                'created_by'           => auth()->id(),
            ]);

            // Single line item
            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'item_type'     => 'genset_rental',
                'description'   => $validated['description'],
                'quantity'      => 1,
                'unit_price'    => $subtotal,
                'duration_days' => $durationDays,
                'subtotal'      => $subtotal,
            ]);

            // Payment record
            InvoicePayment::create([
                'invoice_id'     => $invoice->id,
                'payment_date'   => $validated['payment_date'],
                'amount'         => $total,
                'payment_method' => $validated['payment_method'],
                'reference'      => $validated['payment_reference'] ?? null,
                'notes'          => 'Historical record imported',
                'recorded_by'    => auth()->id(),
            ]);

            // Link invoice back to booking
            $booking->update(['invoice_id' => $invoice->id]);

            UserActivityLog::record(
                auth()->id(), 'created',
                'Recorded historical sale: booking ' . $booking->booking_number . ' / invoice ' . $invoice->invoice_number,
                Booking::class, $booking->id
            );

            session()->flash('success', 'Historical sale recorded: ' . $booking->booking_number . ' / Invoice ' . $invoice->invoice_number);
        });

        return redirect()->route('admin.bookings.index');
    }

    // ── Bulk Historical Import ─────────────────────────────────────────────

    public function historicalTemplate()
    {
        try {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('The php-zip extension is not enabled on this server. Please ask your hosting provider to enable it.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Historical Sales');

        // Headers
        $headers = [
            'client_identifier',   // Client number (CL-YYYY-####) OR company/person name
            'client_phone',        // Required only when creating a new client
            'client_email',        // Required only when creating a new client
            'genset_type',         // e.g. 45 KVA Clip-on
            'rental_start_date',   // YYYY-MM-DD
            'rental_end_date',     // YYYY-MM-DD
            'drop_on_location',
            'currency',            // TZS or USD
            'exchange_rate',       // Leave blank for TZS; e.g. 2550 for USD
            'subtotal',            // Excl. VAT, numeric
            'zero_rated',          // YES or NO
            'description',         // Invoice line-item description
            'invoice_date',        // YYYY-MM-DD
            'payment_date',        // YYYY-MM-DD
            'payment_method',      // cash / bank_transfer / mpesa / cheque / other
            'payment_reference',   // Optional
            'notes',               // Optional
        ];

        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Style header row
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']],
        ]);

        // Column widths
        $widths = [28, 16, 26, 22, 16, 16, 30, 10, 14, 14, 11, 35, 14, 14, 16, 20, 30];
        foreach ($widths as $col => $width) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        // Two sample rows
        $samples = [
            [
                'ABC Logistics Ltd',        // client_identifier
                '+255 712 000 001',         // client_phone
                'abc@abclogistics.co.tz',   // client_email
                '45 KVA Clip-on',           // genset_type
                '2024-01-15',               // rental_start_date
                '2024-02-15',               // rental_end_date
                'Dar Es Salaam Port, Gate 3', // delivery_location
                'TZS',                      // currency
                '',                         // exchange_rate
                '3500000',                  // subtotal
                'NO',                       // zero_rated
                'Genset Rental — 45 KVA, Jan 2024', // description
                '2024-02-16',               // invoice_date
                '2024-02-20',               // payment_date
                'bank_transfer',            // payment_method
                'REF-20240220-001',         // payment_reference
                '',                         // notes
            ],
            [
                'Kilimanjaro Breweries',
                '+255 754 000 002',
                'info@kilibreweries.co.tz',
                '100 KVA Underslung',
                '2024-03-01',
                '2024-05-31',
                'Moshi Industrial Area',
                'USD',
                '2550',
                '4200',
                'NO',
                'Genset Rental — 100 KVA, Mar–May 2024',
                '2024-06-01',
                '2024-06-05',
                'bank_transfer',
                'REF-USD-001',
                'Long-term rental contract',
            ],
        ];

        foreach ($samples as $rowIndex => $row) {
            foreach ($row as $col => $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . ($rowIndex + 2);
                $sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }

        // Style data rows
        $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']],
        ]);

        // Instructions sheet
        $info = $spreadsheet->createSheet();
        $info->setTitle('Instructions');
        $infoRows = [
            ['Column', 'Required?', 'Notes'],
            ['client_identifier', 'Yes', 'Use existing client number (CL-YYYY-####) OR type the exact company/person name. If not found, a new client is created.'],
            ['client_phone', 'Only for new clients', 'Phone number for the new client.'],
            ['client_email', 'Only for new clients', 'Email address for the new client. Required when creating a new client.'],
            ['genset_type', 'No', 'Text description of the generator.'],
            ['rental_start_date', 'Yes', 'Format: YYYY-MM-DD (e.g. 2024-01-15)'],
            ['rental_end_date', 'Yes', 'Format: YYYY-MM-DD. Must be on or after start date.'],
            ['drop_on_location', 'Yes', 'Site where genset was dropped on (delivered).'],
            ['currency', 'Yes', 'TZS or USD'],
            ['exchange_rate', 'Only for USD', 'How many TZS per 1 USD (e.g. 2550). Leave blank for TZS.'],
            ['subtotal', 'Yes', 'Amount excluding VAT. Numeric only, no commas.'],
            ['zero_rated', 'Yes', 'YES = 0% VAT (exempt). NO = 18% VAT applies.'],
            ['description', 'Yes', 'Invoice line-item description.'],
            ['invoice_date', 'Yes', 'Format: YYYY-MM-DD'],
            ['payment_date', 'Yes', 'Format: YYYY-MM-DD'],
            ['payment_method', 'Yes', 'One of: cash, bank_transfer, mpesa, cheque, other'],
            ['payment_reference', 'No', 'Receipt number, transfer ref, cheque number, etc.'],
            ['notes', 'No', 'Any additional context.'],
        ];
        foreach ($infoRows as $r => $cols) {
            foreach ($cols as $c => $val) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1) . ($r + 1);
                $info->setCellValue($cell, $val);
            }
        }
        $info->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']],
        ]);
        foreach (['A' => 28, 'B' => 22, 'C' => 80] as $col => $w) {
            $info->getColumnDimension($col)->setWidth($w);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'historical_sales_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);

        } catch (\Throwable $e) {
            \Log::error('Historical template generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Template generation failed: ' . $e->getMessage());
        }
    }

    public function bulkHistoricalPreview(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'bulk_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        ini_set('memory_limit', '256M');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('bulk_file')->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $rows  = $sheet->toArray(null, true, false, false);

        // Row 0 = headers, rows 1+ = data
        $headerRow = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0] ?? []);
        $colMap = array_flip($headerRow);

        $get = function (array $row, string $key) use ($colMap): string {
            $idx = $colMap[$key] ?? null;
            return $idx !== null ? trim((string) ($row[$idx] ?? '')) : '';
        };

        // Date-aware getter: handles Excel serial numbers (e.g. 45307.0) that Excel
        // auto-creates when the user types a date into a non-text cell.
        // $rowIndex is the 1-based sheet row number (header = 1, first data row = 2).
        $getDate = function (array $row, string $key, int $sheetRowNumber) use ($colMap, $sheet): string {
            $idx = $colMap[$key] ?? null;
            if ($idx === null) return '';
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $cell = $sheet->getCell($colLetter . $sheetRowNumber);
            $value = $cell->getValue();
            if ($value === null || $value === '') return '';
            // If Excel stored it as a date/time serial number, convert to Y-m-d
            if ((is_float($value) || is_int($value))
                && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)
                    ->format('Y-m-d');
            }
            return trim((string) $value);
        };

        $parsed = [];
        $allClients = Client::orderBy('company_name')->get(['id', 'client_number', 'company_name', 'full_name']);

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Skip completely empty rows
            if (implode('', array_map('trim', array_map('strval', $row))) === '') {
                continue;
            }

            // Sheet row index: header is row 1, data rows start at row 2. So sheet row = $i + 1.
            $sheetRow = $i + 1;

            $clientId        = $get($row, 'client_identifier');
            $clientPhone     = $get($row, 'client_phone');
            $clientEmail     = $get($row, 'client_email');
            $currency        = strtoupper($get($row, 'currency')) ?: 'TZS';
            $zeroRated       = strtoupper($get($row, 'zero_rated')) === 'YES';
            $subtotal        = (float) str_replace([',', ' '], '', $get($row, 'subtotal'));
            $vatRate         = $zeroRated ? 0 : 18.0;
            $vatAmount       = $zeroRated ? 0 : round($subtotal * $vatRate / 100, 2);
            $total           = $subtotal + $vatAmount;
            $exRate          = $currency === 'USD' ? (float) str_replace([',', ' '], '', $get($row, 'exchange_rate')) : 1.0;
            $rentalStartDate = $getDate($row, 'rental_start_date', $sheetRow);
            $rentalEndDate   = $getDate($row, 'rental_end_date',   $sheetRow);
            $invoiceDate     = $getDate($row, 'invoice_date',      $sheetRow);
            $paymentDate     = $getDate($row, 'payment_date',      $sheetRow);

            // Resolve client
            $matchedClient = null;
            $clientStatus  = 'existing'; // 'existing' | 'new'
            if (!empty($clientId)) {
                // Try client_number first, then company_name, then full_name
                $matchedClient = $allClients->first(fn($c) => strtolower($c->client_number) === strtolower($clientId))
                    ?? $allClients->first(fn($c) => strtolower($c->company_name ?? '') === strtolower($clientId))
                    ?? $allClients->first(fn($c) => strtolower($c->full_name ?? '') === strtolower($clientId));
                if (!$matchedClient) {
                    $clientStatus = 'new';
                }
            }

            $errors = [];
            if (empty($clientId)) $errors[] = 'client_identifier is required';
            if ($clientStatus === 'new' && empty($clientEmail)) $errors[] = 'client_email required for new client';
            // phone is optional
            if (empty($rentalStartDate)) $errors[] = 'rental_start_date required';
            if (empty($rentalEndDate))   $errors[] = 'rental_end_date required';
            if (empty($get($row, 'drop_on_location'))) $errors[] = 'drop_on_location required';
            if ($subtotal <= 0) $errors[] = 'subtotal must be > 0';
            if (empty($get($row, 'description'))) $errors[] = 'description required';
            if (empty($invoiceDate))  $errors[] = 'invoice_date required';
            if (empty($paymentDate))  $errors[] = 'payment_date required';
            if (!in_array(strtolower($get($row, 'payment_method')), ['cash', 'bank_transfer', 'mpesa', 'cheque', 'other'])) {
                $errors[] = 'invalid payment_method';
            }
            if ($currency === 'USD' && $exRate <= 0) $errors[] = 'exchange_rate required for USD';

            $parsed[] = [
                'row'               => $i + 1,
                'client_identifier' => $clientId,
                'client_phone'      => $clientPhone,
                'client_email'      => $clientEmail,
                'client_id'         => $matchedClient?->id,
                'client_label'      => $matchedClient
                    ? ($matchedClient->company_name ?? $matchedClient->full_name) . ' (' . $matchedClient->client_number . ')'
                    : null,
                'client_status'     => $clientStatus,
                'genset_type'       => $get($row, 'genset_type'),
                'rental_start_date' => $rentalStartDate,
                'rental_end_date'   => $rentalEndDate,
                'drop_on_location'  => $get($row, 'drop_on_location'),
                'currency'          => $currency,
                'exchange_rate'     => $exRate,
                'subtotal'          => $subtotal,
                'zero_rated'        => $zeroRated,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'description'       => $get($row, 'description'),
                'invoice_date'      => $invoiceDate,
                'payment_date'      => $paymentDate,
                'payment_method'    => strtolower($get($row, 'payment_method')),
                'payment_reference' => $get($row, 'payment_reference'),
                'notes'             => $get($row, 'notes'),
                'errors'            => $errors,
            ];
        }

        session(['bulk_historical' => $parsed]);

        return view('admin.bookings.bulk-historical-preview', [
            'rows'       => $parsed,
            'validCount' => count(array_filter($parsed, fn($r) => empty($r['errors']))),
        ]);
    }

    public function bulkHistoricalConfirm(\Illuminate\Http\Request $request)
    {
        $rows = session('bulk_historical', []);
        if (empty($rows)) {
            return redirect()->route('admin.bookings.record-historical')
                ->with('error', 'Session expired. Please upload the file again.');
        }

        $saved = 0;
        $failed = 0;

        foreach ($rows as $row) {
            if (!empty($row['errors'])) {
                $failed++;
                continue;
            }

            try {
                DB::transaction(function () use ($row) {
                    // Resolve or create client
                    if ($row['client_id']) {
                        $clientId = $row['client_id'];
                    } else {
                        $newClient = Client::create(array_filter([
                            'full_name'          => $row['client_identifier'],
                            'phone'              => $row['client_phone'] ?: null,
                            'email'              => $row['client_email'] ?: null,
                            'status'             => 'active',
                            'risk_level'         => 'low',
                            'credit_limit'       => 0,
                            'payment_terms_days' => 30,
                            'source'             => 'manual',
                            'created_by'         => auth()->id(),
                        ], fn($v) => $v !== null));
                        $clientId = $newClient->id;
                    }

                    $startDate    = \Carbon\Carbon::parse($row['rental_start_date']);
                    $endDate      = \Carbon\Carbon::parse($row['rental_end_date']);
                    $durationDays = $startDate->diffInDays($endDate);
                    $subtotal     = (float) $row['subtotal'];
                    $vatAmount    = (float) $row['vat_amount'];
                    $total        = $subtotal + $vatAmount;
                    $currency     = $row['currency'];
                    $exRate       = (float) $row['exchange_rate'];
                    $vatRate      = $row['zero_rated'] ? 0 : 18.0;

                    $booking = Booking::create([
                        'client_id'            => $clientId,
                        'genset_type'          => $row['genset_type'] ?: null,
                        'status'               => 'paid',
                        'is_historical'        => true,
                        'rental_start_date'    => $startDate,
                        'rental_end_date'      => $endDate,
                        'rental_duration_days' => $durationDays,
                        'drop_on_location'     => $row['drop_on_location'],
                        'total_amount'         => $total,
                        'currency'             => $currency,
                        'exchange_rate_to_tzs' => $exRate,
                        'notes'                => $row['notes'] ?: null,
                        'created_by'           => auth()->id(),
                    ]);

                    $invoice = Invoice::create([
                        'booking_id'           => $booking->id,
                        'client_id'            => $clientId,
                        'status'               => 'paid',
                        'issue_date'           => $row['invoice_date'],
                        'due_date'             => $row['invoice_date'],
                        'subtotal'             => $subtotal,
                        'is_zero_rated'        => $row['zero_rated'],
                        'vat_rate'             => $vatRate,
                        'vat_amount'           => $vatAmount,
                        'currency'             => $currency,
                        'exchange_rate_to_tzs' => $exRate,
                        'total_amount'         => $total,
                        'amount_paid'          => $total,
                        'created_by'           => auth()->id(),
                    ]);

                    InvoiceItem::create([
                        'invoice_id'    => $invoice->id,
                        'item_type'     => 'genset_rental',
                        'description'   => $row['description'],
                        'quantity'      => 1,
                        'unit_price'    => $subtotal,
                        'duration_days' => $durationDays,
                        'subtotal'      => $subtotal,
                    ]);

                    InvoicePayment::create([
                        'invoice_id'     => $invoice->id,
                        'payment_date'   => $row['payment_date'],
                        'amount'         => $total,
                        'payment_method' => $row['payment_method'],
                        'reference'      => $row['payment_reference'] ?: null,
                        'notes'          => 'Bulk historical import',
                        'recorded_by'    => auth()->id(),
                    ]);

                    $booking->update(['invoice_id' => $invoice->id]);
                });
                $saved++;
            } catch (\Throwable $e) {
                \Log::error('Bulk historical import row failed: ' . $e->getMessage(), ['exception' => $e]);
                $failed++;
            }
        }

        session()->forget('bulk_historical');

        UserActivityLog::record(
            auth()->id(), 'created',
            "Bulk historical import: {$saved} sales saved, {$failed} skipped.",
            Booking::class, null
        );

        $message = "Bulk import complete: {$saved} sales recorded" . ($failed ? ", {$failed} skipped due to errors." : '.');
        $flashKey = ($saved > 0) ? 'success' : 'error';

        return redirect()->route('admin.bookings.index')->with($flashKey, $message);
    }
}
