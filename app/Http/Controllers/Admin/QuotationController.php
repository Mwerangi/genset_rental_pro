<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\QuotationItemType;
use App\Models\UserActivityLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    /**
     * Display a listing of quotations
     */
    public function index(Request $request)
    {
        // "Created Quotations" only shows active (non-terminal) statuses
        $user   = auth()->user();
        // Only users who manage quotations see all; others see only their own
        $seeAll = PermissionService::can($user, 'view_all_quotations');

        $query = Quotation::with(['quoteRequest', 'createdBy', 'client'])
            ->whereIn('status', ['draft', 'sent', 'viewed'])
            ->latest();

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('client_number', 'like', "%{$search}%");
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
            'total_value' => Quotation::where('status', 'accepted')->selectRaw('SUM(total_amount * exchange_rate_to_tzs)')->value('SUM(total_amount * exchange_rate_to_tzs)') ?? 0,
        ];

        return view('admin.quotations.index', compact('quotations', 'stats'));
    }

    /**
     * Display approved (accepted) quotations
     */
    public function approved(Request $request)
    {
        $query = Quotation::with(['quoteRequest', 'createdBy', 'client'])
            ->where('status', 'accepted')
            ->latest('accepted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('client_number', 'like', "%{$search}%");
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
            'total_value' => Quotation::where('status', 'accepted')->selectRaw('SUM(total_amount * exchange_rate_to_tzs)')->value('SUM(total_amount * exchange_rate_to_tzs)') ?? 0,
            'this_month'  => Quotation::where('status', 'accepted')
                                ->whereMonth('accepted_at', now()->month)
                                ->whereYear('accepted_at', now()->year)
                                ->count(),
            'month_value' => Quotation::where('status', 'accepted')
                                ->whereMonth('accepted_at', now()->month)
                                ->whereYear('accepted_at', now()->year)
                                ->selectRaw('SUM(total_amount * exchange_rate_to_tzs)')
                                ->value('SUM(total_amount * exchange_rate_to_tzs)') ?? 0,
        ];

        return view('admin.quotations.approved', compact('quotations', 'stats'));
    }

    /**
     * Display rejected quotations
     */
    public function rejected(Request $request)
    {
        $query = Quotation::with(['quoteRequest', 'createdBy', 'client'])
            ->where('status', 'rejected')
            ->latest('rejected_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhereHas('quoteRequest', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('client_number', 'like', "%{$search}%");
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

        $clients = Client::orderBy('company_name')->orderBy('full_name')
            ->get(['id', 'client_number', 'full_name', 'company_name', 'email', 'phone']);

        $itemTypes = QuotationItemType::active()->get(['id', 'key', 'label', 'is_rental']);

        return view('admin.quotations.create', compact('quoteRequest', 'clients', 'itemTypes'));
    }

    /**
     * Store a newly created quotation
     */
    public function store(Request $request)
    {
        $validItemTypeKeys = QuotationItemType::active()->pluck('key')->toArray();

        $validated = $request->validate([
            'quote_request_id'  => 'nullable|exists:quote_requests,id',
            'client_id'         => 'nullable|exists:clients,id',
            'customer_name'     => 'required_without_all:quote_request_id,client_id|nullable|string|max:255',
            'customer_email'    => 'required_without_all:quote_request_id,client_id|nullable|email|max:255',
            'customer_phone'    => 'nullable|string|max:50',
            'company_name'      => 'nullable|string|max:255',
            'valid_until' => 'required|date|after:today',
            'currency' => 'required|in:TZS,USD',
            'exchange_rate_to_tzs' => 'required_if:currency,USD|nullable|numeric|min:0.0001',
            'payment_terms' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => ['required', Rule::in($validItemTypeKeys)],
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.duration_days' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Resolve client from client_id or manual fields
            $linkedClient = !empty($validated['client_id']) ? Client::find($validated['client_id']) : null;
            $hasQr = !empty($validated['quote_request_id']);

            // Create quotation
            $quotation = Quotation::create([
                'quote_request_id'     => $validated['quote_request_id'] ?? null,
                'client_id'            => $linkedClient?->id ?? null,
                'customer_name'        => !$hasQr ? ($linkedClient?->full_name ?? ($validated['customer_name'] ?? null)) : null,
                'customer_email'       => !$hasQr ? ($linkedClient?->email ?? ($validated['customer_email'] ?? null)) : null,
                'customer_phone'       => !$hasQr ? ($linkedClient?->phone ?? ($validated['customer_phone'] ?? null)) : null,
                'company_name'         => !$hasQr ? ($linkedClient?->company_name ?? ($validated['company_name'] ?? null)) : null,
                'valid_until'          => $validated['valid_until'],
                'currency'             => $validated['currency'],
                'exchange_rate_to_tzs' => $validated['currency'] === 'USD'
                    ? $validated['exchange_rate_to_tzs']
                    : 1.0,
                'payment_terms'        => $validated['payment_terms'] ?? 'Payment due within 30 days of acceptance',
                'terms_conditions'     => $validated['terms_conditions'],
                'notes'                => $validated['notes'],
                'vat_rate'             => 18, // Tanzania standard VAT
                'status'               => $request->has('send') ? 'sent' : 'draft',
                'created_by'           => auth()->id(),
                'sent_at'              => $request->has('send') ? now() : null,
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
                UserActivityLog::record(
                    auth()->id(), 'created',
                    'Created quotation ' . $quotation->quotation_number . ' (sent)',
                    Quotation::class, $quotation->id
                );
                return redirect()
                    ->route('admin.quotations.show', $quotation)
                    ->with('success', 'Quotation created and sent successfully!');
            }

            UserActivityLog::record(
                auth()->id(), 'created',
                'Created quotation ' . $quotation->quotation_number . ' (draft)',
                Quotation::class, $quotation->id
            );
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
        $user = auth()->user();
        if (!PermissionService::can($user, 'view_all_quotations') && $quotation->created_by !== $user->id) {
            abort(403, 'You do not have permission to view this quotation.');
        }
        $quotation->load(['quoteRequest', 'client', 'createdBy', 'items']);

        $availableGensets = \App\Models\Genset::where('status', 'available')
            ->orderBy('asset_number')
            ->get();

        return view('admin.quotations.show', compact('quotation', 'availableGensets'));
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

        $itemTypes = QuotationItemType::active()->get(['id', 'key', 'label', 'is_rental']);

        return view('admin.quotations.edit', compact('quotation', 'itemTypes'));
    }

    /**
     * Update the specified quotation
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->canBeEdited()) {
            return back()->with('error', 'This quotation cannot be edited.');
        }

        $validItemTypeKeys = QuotationItemType::active()->pluck('key')->toArray();

        $validated = $request->validate([
            'valid_until' => 'required|date|after:today',
            'currency' => 'required|in:TZS,USD',
            'exchange_rate_to_tzs' => 'required_if:currency,USD|nullable|numeric|min:0.0001',
            'payment_terms' => 'nullable|string|max:500',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => ['required', Rule::in($validItemTypeKeys)],
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.duration_days' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update quotation fields
            $quotation->update([
                'valid_until'          => $validated['valid_until'],
                'currency'             => $validated['currency'],
                'exchange_rate_to_tzs' => $validated['currency'] === 'USD'
                    ? $validated['exchange_rate_to_tzs']
                    : 1.0,
                'payment_terms'        => $validated['payment_terms'],
                'terms_conditions'     => $validated['terms_conditions'],
                'notes'                => $validated['notes'],
            ]);

            // Hard-delete all existing items via direct query (no Eloquent events)
            QuotationItem::where('quotation_id', $quotation->id)->delete();

            // Recreate items without firing model observers to prevent
            // calculateTotals() being called multiple times with stale instances
            QuotationItem::withoutEvents(function () use ($quotation, $validated) {
                $rentalKeys = QuotationItemType::where('is_rental', true)->pluck('key')->flip()->toArray();

                foreach ($validated['items'] as $item) {
                    $quantity  = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $duration  = $item['duration_days'] ?? null;

                    $subtotal = (isset($rentalKeys[$item['item_type']]) && $duration)
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

            UserActivityLog::record(
                auth()->id(), 'updated',
                'Updated quotation ' . $quotation->quotation_number,
                Quotation::class, $quotation->id
            );

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
    public function approve(Quotation $quotation, Request $request)
    {
        if (!in_array($quotation->status, ['draft', 'sent', 'viewed'])) {
            return back()->with('error', 'Only active quotations can be approved.');
        }

        $request->validate([
            'genset_id'            => 'required|exists:gensets,id',
            'rental_start_date'    => 'required|date',
            'rental_duration_days' => 'required|integer|min:1',
            'delivery_location'    => 'required|string|max:500',
            'pickup_location'      => 'nullable|string|max:500',
        ]);

        $genset = \App\Models\Genset::where('id', $request->genset_id)
            ->where('status', 'available')
            ->firstOrFail();

        $quotation->load(['quoteRequest', 'items']);
        $quotation->markAsAccepted();

        $qr = $quotation->quoteRequest;

        // ── Step A: Find or create a Client record ────────────────────────────
        $client = null;
        $clientEmail = $qr?->email ?? $quotation->customer_email;

        if ($clientEmail) {
            $client = Client::where('email', $clientEmail)->first();

            if (!$client) {
                $client = Client::create([
                    'full_name'        => $qr?->full_name ?? $quotation->customer_name,
                    'company_name'     => $qr?->company_name ?? $quotation->company_name,
                    'email'            => $clientEmail,
                    'phone'            => $qr?->phone ?? $quotation->customer_phone,
                    'source'           => $qr ? 'quote_request' : 'manual',
                    'quote_request_id' => $qr?->id,
                    'created_by'       => auth()->id(),
                ]);

                ClientContact::create([
                    'client_id'              => $client->id,
                    'name'                   => $qr?->full_name ?? $quotation->customer_name,
                    'email'                  => $clientEmail,
                    'phone'                  => $qr?->phone ?? $quotation->customer_phone,
                    'is_primary'             => true,
                    'can_authorize_bookings' => true,
                    'can_receive_invoices'   => true,
                ]);
            }

            if ($qr) {
                $qr->update(['client_id' => $client->id, 'status' => 'converted']);
            }
        }

        // ── Step B: Create the Booking ────────────────────────────────────────
        $startDate = \Carbon\Carbon::parse($request->rental_start_date);
        $endDate   = $startDate->copy()->addDays((int) $request->rental_duration_days);

        $booking = Booking::create([
            'client_id'            => $client?->id,
            'quote_request_id'     => $quotation->quote_request_id,
            'quotation_id'         => $quotation->id,
            'genset_id'            => $genset->id,
            'status'               => 'created',
            'genset_type'          => $genset->type,
            'rental_start_date'    => $startDate,
            'rental_end_date'      => $endDate,
            'rental_duration_days' => (int) $request->rental_duration_days,
            'delivery_location'    => $request->delivery_location,
            'pickup_location'      => $request->pickup_location,
            'total_amount'         => $quotation->total_amount,
            'currency'             => $quotation->currency ?? 'TZS',
            'exchange_rate_to_tzs' => $quotation->exchange_rate_to_tzs ?? 1.0,
            'customer_name'        => $qr?->full_name ?? $quotation->customer_name,
            'customer_email'       => $qr?->email ?? $quotation->customer_email,
            'customer_phone'       => $qr?->phone ?? $quotation->customer_phone,
            'company_name'         => $qr?->company_name ?? $quotation->company_name,
            'created_by'           => auth()->id(),
        ]);

        UserActivityLog::record(
            auth()->id(), 'approved',
            'Approved quotation ' . $quotation->quotation_number . ' — created booking ' . $booking->booking_number,
            Quotation::class, $quotation->id
        );

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

        UserActivityLog::record(
            auth()->id(), 'rejected',
            'Rejected quotation ' . $quotation->quotation_number . ': ' . $request->rejection_reason,
            Quotation::class, $quotation->id
        );

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
