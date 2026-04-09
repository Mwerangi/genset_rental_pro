<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientAddress;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['primaryContact', 'createdBy'])->withCount('bookings')->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_number', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(25);

        $stats = [
            'total'       => Client::count(),
            'active'      => Client::where('status', 'active')->count(),
            'inactive'    => Client::where('status', 'inactive')->count(),
            'blacklisted' => Client::where('status', 'blacklisted')->count(),
        ];

        return view('admin.clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'          => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'email'              => 'required|email|unique:clients,email',
            'phone'              => 'required|string|max:30',
            'tin_number'         => 'nullable|string|max:50',
            'vrn'                => 'nullable|string|max:50',
            'status'             => 'required|in:active,inactive,blacklisted',
            'risk_level'         => 'required|in:low,medium,high',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'notes'              => 'nullable|string|max:2000',
            'source'             => 'nullable|in:manual,referral,website,phone',
            // Primary contact
            'contact_name'       => 'nullable|string|max:255',
            'contact_position'   => 'nullable|string|max:255',
            'contact_email'      => 'nullable|email|max:255',
            'contact_phone'      => 'nullable|string|max:30',
            // Address
            'address_type'       => 'nullable|in:billing,service,office,other',
            'street_address'     => 'nullable|string|max:500',
            'city'               => 'nullable|string|max:100',
            'region'             => 'nullable|string|max:100',
        ]);

        $client = Client::create([
            'full_name'          => $validated['full_name'],
            'company_name'       => $validated['company_name'] ?? null,
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'tin_number'         => $validated['tin_number'] ?? null,
            'vrn'                => $validated['vrn'] ?? null,
            'status'             => $validated['status'],
            'risk_level'         => $validated['risk_level'],
            'credit_limit'       => $validated['credit_limit'] ?? 0,
            'payment_terms_days' => $validated['payment_terms_days'] ?? 30,
            'notes'              => $validated['notes'] ?? null,
            'source'             => $validated['source'] ?? 'manual',
            'created_by'         => auth()->id(),
        ]);

        // Optional primary contact
        if (!empty($validated['contact_name'])) {
            ClientContact::create([
                'client_id'              => $client->id,
                'name'                   => $validated['contact_name'],
                'position'               => $validated['contact_position'] ?? null,
                'email'                  => $validated['contact_email'] ?? null,
                'phone'                  => $validated['contact_phone'] ?? null,
                'is_primary'             => true,
                'can_authorize_bookings' => true,
                'can_receive_invoices'   => true,
            ]);
        }

        // Optional address
        if (!empty($validated['street_address'])) {
            ClientAddress::create([
                'client_id'      => $client->id,
                'type'           => $validated['address_type'] ?? 'service',
                'street_address' => $validated['street_address'],
                'city'           => $validated['city'] ?? null,
                'region'         => $validated['region'] ?? null,
                'is_default'     => true,
            ]);
        }

        UserActivityLog::record(
            auth()->id(), 'created',
            'Added client ' . $client->client_number . ' (' . ($client->company_name ?: $client->full_name) . ')',
            Client::class, $client->id
        );

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client ' . $client->client_number . ' created successfully.');
    }

    public function show(Client $client)
    {
        $client->load([
            'contacts',
            'addresses',
            'bookings' => fn($q) => $q->latest()->limit(10),
            'quoteRequest',
            'createdBy',
        ]);

        $totalSpend = $client->bookings()
            ->whereIn('status', ['invoiced', 'paid'])
            ->selectRaw('SUM(total_amount * exchange_rate_to_tzs)')
            ->value('SUM(total_amount * exchange_rate_to_tzs)') ?? 0;

        $activeBookings = $client->bookings()
            ->whereIn('status', ['approved', 'active'])
            ->count();

        return view('admin.clients.show', compact('client', 'totalSpend', 'activeBookings'));
    }

    public function edit(Client $client)
    {
        $client->load(['contacts', 'addresses']);
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'full_name'          => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'email'              => 'required|email|unique:clients,email,' . $client->id,
            'phone'              => 'required|string|max:30',
            'tin_number'         => 'nullable|string|max:50',
            'vrn'                => 'nullable|string|max:50',
            'status'             => 'required|in:active,inactive,blacklisted',
            'risk_level'         => 'required|in:low,medium,high',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $client->update($validated);

        UserActivityLog::record(
            auth()->id(), 'updated',
            'Updated client ' . $client->client_number . ' (' . ($client->company_name ?: $client->full_name) . ')',
            Client::class, $client->id
        );

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    // ── Contacts ──────────────────────────────────────────────────────────────

    public function storeContact(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'position'               => 'nullable|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:30',
            'is_primary'             => 'boolean',
            'can_authorize_bookings' => 'boolean',
            'can_receive_invoices'   => 'boolean',
        ]);

        if (!empty($validated['is_primary'])) {
            $client->contacts()->update(['is_primary' => false]);
        }

        $client->contacts()->create($validated);

        return back()->with('success', 'Contact added successfully.');
    }

    public function destroyContact(Client $client, ClientContact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contact removed.');
    }

    // ── Addresses ─────────────────────────────────────────────────────────────

    public function storeAddress(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type'           => 'required|in:billing,service,office,other',
            'label'          => 'nullable|string|max:255',
            'street_address' => 'required|string|max:500',
            'city'           => 'nullable|string|max:100',
            'region'         => 'nullable|string|max:100',
            'is_default'     => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            $client->addresses()->update(['is_default' => false]);
        }

        $client->addresses()->create($validated);

        return back()->with('success', 'Address added successfully.');
    }

    public function destroyAddress(Client $client, ClientAddress $address)
    {
        $address->delete();
        return back()->with('success', 'Address removed.');
    }

    /**
     * Quick-create a client via JSON (used by inline modals on other pages).
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|unique:clients,email|max:255',
            'phone'        => 'required|string|max:30',
            'tin_number'   => 'nullable|string|max:50',
        ]);

        $client = Client::create([
            'full_name'          => $validated['full_name'],
            'company_name'       => $validated['company_name'] ?? null,
            'email'              => $validated['email'] ?? null,
            'phone'              => $validated['phone'],
            'tin_number'         => $validated['tin_number'] ?? null,
            'status'             => 'active',
            'risk_level'         => 'low',
            'credit_limit'       => 0,
            'payment_terms_days' => 30,
            'source'             => 'manual',
            'created_by'         => auth()->id(),
        ]);

        UserActivityLog::record(
            auth()->id(), 'created',
            'Quick-added client ' . $client->client_number . ' (' . ($client->company_name ?: $client->full_name) . ')',
            Client::class, $client->id
        );

        return response()->json([
            'id'    => $client->id,
            'label' => ($client->company_name ?? $client->full_name) . ' (' . $client->client_number . ')',
        ]);
    }
}
