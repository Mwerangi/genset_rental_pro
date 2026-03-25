<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Genset;
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

        $query = Booking::with(['quoteRequest', 'createdBy', 'approvedBy'])->latest();

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

        $base = $seeAll ? Booking::query() : Booking::where('created_by', $user->id);
        $stats = [
            'total'    => (clone $base)->count(),
            'created'  => (clone $base)->where('status', 'created')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'active'   => (clone $base)->where('status', 'active')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats', 'seeAll'));
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

    public function create(Request $request)
    {
        $quoteRequests = QuoteRequest::whereNotIn('status', ['converted', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        $preselected = $request->filled('quote_request_id')
            ? QuoteRequest::find($request->quote_request_id)
            : null;

        return view('admin.bookings.create', compact('quoteRequests', 'preselected'));
    }

    public function store(Request $request)
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
            'delivery_location'    => 'required|string|max:500',
            'pickup_location'      => 'nullable|string|max:500',
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
            'delivery_location'    => $validated['delivery_location'],
            'pickup_location'      => $validated['pickup_location'] ?? null,
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

        $request->validate([
            'genset_id' => 'required|exists:gensets,id',
        ]);

        $genset = Genset::find($request->genset_id);

        if ($genset->status !== 'available') {
            return back()->with('error', 'The selected genset is no longer available.');
        }

        $booking->activate(auth()->id(), $genset->id);

        UserActivityLog::record(
            auth()->id(), 'activated',
            'Activated booking ' . $booking->booking_number . ' — genset ' . $genset->asset_number . ' deployed',
            Booking::class, $booking->id
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', $genset->asset_number . ' deployed — booking is now active!');
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
        $booking->load(['client', 'genset', 'quotation.items', 'approvedBy']);

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

        return view('admin.bookings.edit', compact('booking', 'quoteRequests', 'gensets'));
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
            'delivery_location'    => 'required|string|max:500',
            'pickup_location'      => 'nullable|string|max:500',
            'total_amount'         => 'required|numeric|min:0',
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
            'delivery_location'    => $validated['delivery_location'],
            'pickup_location'      => $validated['pickup_location'] ?? null,
            'total_amount'         => $validated['total_amount'],
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
}
