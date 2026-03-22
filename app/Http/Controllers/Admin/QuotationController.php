<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Display a listing of quotations
     */
    public function index(Request $request)
    {
        // "Created Quotations" only shows active (non-terminal) statuses
        $query = Quotation::with(['quoteRequest', 'createdBy'])
            ->whereIn('status', ['draft', 'sent', 'viewed'])
            ->latest();

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $quotations = $query->paginate($request->get('per_page', 25));

        $stats = [
            'total_quotations' => Quotation::whereIn('status', ['draft', 'sent', 'viewed'])->count(),
            'draft'   => Quotation::where('status', 'draft')->count(),
            'sent'    => Quotation::where('status', 'sent')->count(),
            'viewed'  => Quotation::where('status', 'viewed')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
            'total_value' => Quotation::where('status', 'accepted')->sum('total_amount'),
        ];

        return view('admin.quotations.index', compact('quotations', 'stats'));
    }

    /**
     * Display approved (accepted) quotations
     */
    public function approved(Request $request)
    {
        $query = Quotation::with(['quoteRequest', 'createdBy'])
            ->where('status', 'accepted')
            ->latest('accepted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('accepted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('accepted_at', '<=', $request->date_to);
        }

        $quotations = $query->paginate(25);

        $stats = [
            'total'       => Quotation::where('status', 'accepted')->count(),
            'total_value' => Quotation::where('status', 'accepted')->sum('total_amount'),
            'this_month'  => Quotation::where('status', 'accepted')
                                ->whereMonth('accepted_at', now()->month)
                                ->whereYear('accepted_at', now()->year)
                                ->count(),
            'month_value' => Quotation::where('status', 'accepted')
                                ->whereMonth('accepted_at', now()->month)
                                ->whereYear('accepted_at', now()->year)
                                ->sum('total_amount'),
        ];

        return view('admin.quotations.approved', compact('quotations', 'stats'));
    }

    /**
     * Display rejected quotations
     */
    public function rejected(Request $request)
    {
        $query = Quotation::with(['quoteRequest', 'createdBy'])
            ->where('status', 'rejected')
            ->latest('rejected_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $quotations = $query->paginate(25);

        $stats = [
            'total'      => Quotation::where('status', 'rejected')->count(),
            'this_month' => Quotation::where('status', 'rejected')
                               ->whereMonth('rejected_at', now()->month)
                               ->whereYear('rejected_at', now()->year)
                               ->count(),
        ];

        return view('admin.quotations.rejected', compact('quotations', 'stats'));
    }

    /**
     * Show the form for creating a new quotation from a quote request
     */
    public function create(Request $request)
    {
        $quoteRequestId = $request->get('quote_request_id');
        $quoteRequest = null;

        if ($quoteRequestId) {
            $quoteRequest = QuoteRequest::findOrFail($quoteRequestId);
        }

        return view('admin.quotations.create', compact('quoteRequest'));
    }

    /**
     * Store a newly created quotation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_request_id' => 'nullable|exists:quote_requests,id',
            'valid_until' => 'required|date|after:today',
            'payment_terms' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:genset_rental,delivery,fuel,maintenance,other',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.duration_days' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Create quotation
            $quotation = Quotation::create([
                'quote_request_id' => $validated['quote_request_id'],
                'valid_until' => $validated['valid_until'],
                'payment_terms' => $validated['payment_terms'] ?? 'Payment due within 30 days of acceptance',
                'terms_conditions' => $validated['terms_conditions'],
                'notes' => $validated['notes'],
                'vat_rate' => 18, // Tanzania standard VAT
                'status' => $request->has('send') ? 'sent' : 'draft',
                'created_by' => auth()->id(),
                'sent_at' => $request->has('send') ? now() : null,
            ]);

            // Create quotation items
            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'duration_days' => $item['duration_days'] ?? null,
                ]);
            }

            // Calculate totals (handled by model events)
            $quotation->fresh();

            // Update quote request status
            if ($quotation->quote_request_id) {
                $quoteRequest = QuoteRequest::find($quotation->quote_request_id);
                if ($quoteRequest && $quoteRequest->status === 'new') {
                    $quoteRequest->update(['status' => 'quoted']);
                }
            }

            DB::commit();

            if ($request->has('send')) {
                // TODO: Send email notification
                return redirect()
                    ->route('admin.quotations.show', $quotation)
                    ->with('success', 'Quotation created and sent successfully!');
            }

            return redirect()
                ->route('admin.quotations.show', $quotation)
                ->with('success', 'Quotation saved as draft successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create quotation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified quotation
     */
    public function show(Quotation $quotation)
    {
        $quotation->load(['quoteRequest', 'client', 'createdBy', 'items']);
        
        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified quotation
     */
    public function edit(Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return back()->with('error', 'This quotation cannot be edited.');
        }

        $quotation->load(['items', 'quoteRequest']);
        
        return view('admin.quotations.edit', compact('quotation'));
    }

    /**
     * Update the specified quotation
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return back()->with('error', 'This quotation cannot be edited.');
        }

        $validated = $request->validate([
            'valid_until' => 'required|date|after:today',
            'payment_terms' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:genset_rental,delivery,fuel,maintenance,other',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.duration_days' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update quotation fields
            $quotation->update([
                'valid_until' => $validated['valid_until'],
                'payment_terms' => $validated['payment_terms'],
                'terms_conditions' => $validated['terms_conditions'],
                'notes' => $validated['notes'],
            ]);

            // Hard-delete all existing items via direct query (no Eloquent events)
            QuotationItem::where('quotation_id', $quotation->id)->delete();

            // Recreate items without firing model observers to prevent
            // calculateTotals() being called multiple times with stale instances
            QuotationItem::withoutEvents(function () use ($quotation, $validated) {
                foreach ($validated['items'] as $item) {
                    $quantity  = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $duration  = $item['duration_days'] ?? null;

                    $subtotal = ($item['item_type'] === 'genset_rental' && $duration)
                        ? $quantity * $unitPrice * $duration
                        : $quantity * $unitPrice;

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'item_type'    => $item['item_type'],
                        'description'  => $item['description'],
                        'quantity'     => $quantity,
                        'unit_price'   => $unitPrice,
                        'duration_days' => $duration,
                        'subtotal'     => $subtotal,
                    ]);
                }
            });

            // Recalculate totals once on a fresh instance
            $quotation->refresh();
            $quotation->calculateTotals();

            DB::commit();

            return redirect()
                ->route('admin.quotations.show', $quotation)
                ->with('success', 'Quotation updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update quotation: ' . $e->getMessage());
        }
    }

    /**
     * Approve (mark as accepted) a quotation and create a Booking
     */
    public function approve(Quotation $quotation)
    {
        if (!in_array($quotation->status, ['draft', 'sent', 'viewed'])) {
            return back()->with('error', 'Only active quotations can be approved.');
        }

        $quotation->load('quoteRequest');
        $quotation->markAsAccepted();

        $qr = $quotation->quoteRequest;

        // ── Step A: Find or create a Client record ────────────────────────────
        $client = null;
        if ($qr) {
            $client = Client::where('email', $qr->email)->first();

            if (!$client) {
                $client = Client::create([
                    'full_name'        => $qr->full_name,
                    'company_name'     => $qr->company_name,
                    'email'            => $qr->email,
                    'phone'            => $qr->phone,
                    'source'           => 'quote_request',
                    'quote_request_id' => $qr->id,
                    'created_by'       => auth()->id(),
                ]);

                // Auto-create primary contact
                ClientContact::create([
                    'client_id'            => $client->id,
                    'name'                 => $qr->full_name,
                    'email'                => $qr->email,
                    'phone'                => $qr->phone,
                    'is_primary'           => true,
                    'can_authorize_bookings' => true,
                    'can_receive_invoices' => true,
                ]);
            }

            // Link client back to the quote request
            $qr->update(['client_id' => $client->id, 'status' => 'converted']);
        }

        // ── Step B: Create the Booking ────────────────────────────────────────
        $booking = Booking::create([
            'client_id'            => $client?->id,
            'quote_request_id'     => $quotation->quote_request_id,
            'quotation_id'         => $quotation->id,
            'status'               => 'created',
            'genset_type'          => $qr?->genset_type,
            'rental_start_date'    => $qr?->rental_start_date,
            'rental_end_date'      => $qr ? $qr->rental_start_date->addDays($qr->rental_duration_days) : null,
            'rental_duration_days' => $qr?->rental_duration_days,
            'delivery_location'    => $qr?->delivery_location,
            'pickup_location'      => $qr?->pickup_location,
            'total_amount'         => $quotation->total_amount,
            'customer_name'        => $qr?->full_name,
            'customer_email'       => $qr?->email,
            'customer_phone'       => $qr?->phone,
            'created_by'           => auth()->id(),
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Quotation accepted! Booking ' . $booking->booking_number . ' has been created.' . ($client ? ' Client ' . $client->client_number . ' created.' : ''));
    }

    /**
     * Reject a quotation
     */
    public function reject(Request $request, Quotation $quotation)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($quotation->status === 'accepted') {
            return back()->with('error', 'Accepted quotations cannot be rejected.');
        }

        $quotation->markAsRejected($request->rejection_reason);

        // Revert the linked quote request status to reviewed if present
        if ($quotation->quoteRequest && $quotation->quoteRequest->status === 'converted') {
            $quotation->quoteRequest->update(['status' => 'reviewed']);
        }

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation rejected.');
    }

    /**
     * Download quotation as PDF
     */
    public function downloadPdf(Quotation $quotation)
    {
        $quotation->load(['quoteRequest.client', 'client', 'createdBy', 'items']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.quotations.pdf', compact('quotation'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($quotation->quotation_number . '.pdf');
    }
}
