<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Genset;
use App\Models\QuoteRequest;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Managers/approvers see all bookings; limited users see only their own created bookings
        $seeAll = PermissionService::can($user, 'manage_bookings')
               || PermissionService::can($user, 'approve_bookings');

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
        $seeAll = PermissionService::can($user, 'manage_bookings')
               || PermissionService::can($user, 'approve_bookings');
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
            'created_by'           => auth()->id(),
        ]);

        // If no prospect linked, store customer info in notes header
        if (!$validated['quote_request_id'] && $validated['customer_name']) {
            $booking->notes = "Customer: {$validated['customer_name']}" .
                ($validated['customer_email'] ? "\nEmail: {$validated['customer_email']}" : '') .
                ($validated['customer_phone'] ? "\nPhone: {$validated['customer_phone']}" : '') .
                ($validated['company_name'] ? "\nCompany: {$validated['company_name']}" : '') .
                ($validated['notes'] ? "\n\n" . $validated['notes'] : '');
            $booking->save();
        }

        AppNotification::notify(
            null,
            'booking',
            'New Booking: ' . $booking->booking_number,
            ($booking->client?->display_name ?? $booking->customer_name) . ' — awaiting approval.',
            route('admin.bookings.show', $booking),
            'booking'
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

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' has been cancelled.');
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

        $currency = $validated['currency'] ?? $booking->currency ?? 'TZS';

        $startDate = \Carbon\Carbon::parse($validated['rental_start_date']);
        $endDate   = $startDate->copy()->addDays((int) $validated['rental_duration_days']);

        $booking->update([
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
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking ' . $booking->booking_number . ' updated successfully.');
    }
}
