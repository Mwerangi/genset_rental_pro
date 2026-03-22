<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Genset;
use App\Models\Booking;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;

class GensetController extends Controller
{
    public function index(Request $request)
    {
        $query = Genset::withCount('bookings')->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('asset_number', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('serial_number', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%");
            });
        }

        $gensets = $query->paginate(20);

        $stats = [
            'total'       => Genset::count(),
            'available'   => Genset::where('status', 'available')->count(),
            'rented'      => Genset::where('status', 'rented')->count(),
            'maintenance' => Genset::whereIn('status', ['maintenance', 'repair'])->count(),
        ];

        return view('admin.gensets.index', compact('gensets', 'stats'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        return view('admin.gensets.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'asset_number'           => 'nullable|string|max:50|unique:gensets,asset_number',
            'serial_number'          => 'nullable|string|max:100',
            'type'                   => 'required|in:clip-on,underslung,open-frame,canopy,other',
            'brand'                  => 'nullable|string|max:100',
            'model'                  => 'nullable|string|max:100',
            'kva_rating'             => 'nullable|integer|min:1',
            'kw_rating'              => 'nullable|integer|min:1',
            'fuel_type'              => 'nullable|string|max:50',
            'status'                 => 'required|in:available,rented,maintenance,repair,retired,reserved',
            'color'                  => 'nullable|string|max:50',
            'weight_kg'              => 'nullable|numeric|min:0',
            'dimensions'             => 'nullable|string|max:100',
            'tank_capacity_litres'   => 'nullable|string|max:50',
            'run_hours'              => 'nullable|integer|min:0',
            'purchase_date'          => 'nullable|date',
            'purchase_price'         => 'nullable|numeric|min:0',
            'supplier'               => 'nullable|string|max:255',
            'warranty_expiry'        => 'nullable|date',
            'location'               => 'nullable|string|max:255',
            'daily_rate'             => 'nullable|numeric|min:0',
            'weekly_rate'            => 'nullable|numeric|min:0',
            'monthly_rate'           => 'nullable|numeric|min:0',
            'last_service_date'      => 'nullable|date',
            'next_service_date'      => 'nullable|date',
            'service_interval_hours' => 'nullable|integer|min:1',
            'notes'                  => 'nullable|string',
        ]);

        $genset = Genset::create($validated);

        // Capitalise asset if purchase price provided
        if (!empty($validated['purchase_price']) && (float) $validated['purchase_price'] > 0) {
            $capBankId = $request->filled('capitalize_bank_account_id')
                ? (int) $request->capitalize_bank_account_id
                : null;
            $je = app(JournalEntryService::class)->onGensetCapitalized($genset, $capBankId);
            if ($je) {
                $genset->update(['journal_entry_id' => $je->id]);
            }
        }

        return redirect()
            ->route('admin.gensets.show', $genset)
            ->with('success', 'Genset ' . $genset->asset_number . ' created successfully.');
    }

    public function show(Genset $genset)
    {
        $genset->load('bookings.quoteRequest', 'maintenanceRecords', 'fuelLogs');

        $recentBookings = $genset->bookings()
            ->with('quoteRequest', 'client')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.gensets.show', compact('genset', 'recentBookings'));
    }

    public function edit(Genset $genset)
    {
        return view('admin.gensets.edit', compact('genset'));
    }

    public function update(Request $request, Genset $genset)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'asset_number'           => 'nullable|string|max:50|unique:gensets,asset_number,' . $genset->id,
            'serial_number'          => 'nullable|string|max:100',
            'type'                   => 'required|in:clip-on,underslung,open-frame,canopy,other',
            'brand'                  => 'nullable|string|max:100',
            'model'                  => 'nullable|string|max:100',
            'kva_rating'             => 'nullable|integer|min:1',
            'kw_rating'              => 'nullable|integer|min:1',
            'fuel_type'              => 'nullable|string|max:50',
            'status'                 => 'required|in:available,rented,maintenance,repair,retired,reserved',
            'color'                  => 'nullable|string|max:50',
            'weight_kg'              => 'nullable|numeric|min:0',
            'dimensions'             => 'nullable|string|max:100',
            'tank_capacity_litres'   => 'nullable|string|max:50',
            'run_hours'              => 'nullable|integer|min:0',
            'purchase_date'          => 'nullable|date',
            'purchase_price'         => 'nullable|numeric|min:0',
            'supplier'               => 'nullable|string|max:255',
            'warranty_expiry'        => 'nullable|date',
            'location'               => 'nullable|string|max:255',
            'daily_rate'             => 'nullable|numeric|min:0',
            'weekly_rate'            => 'nullable|numeric|min:0',
            'monthly_rate'           => 'nullable|numeric|min:0',
            'last_service_date'      => 'nullable|date',
            'next_service_date'      => 'nullable|date',
            'service_interval_hours' => 'nullable|integer|min:1',
            'notes'                  => 'nullable|string',
        ]);

        $genset->update($validated);

        return redirect()
            ->route('admin.gensets.show', $genset)
            ->with('success', 'Genset updated successfully.');
    }

    public function destroy(Genset $genset)
    {
        if ($genset->status === 'rented') {
            return back()->with('error', 'Cannot delete a genset that is currently on rent.');
        }

        $genset->delete();

        return redirect()
            ->route('admin.gensets.index')
            ->with('success', 'Genset ' . $genset->asset_number . ' deleted.');
    }

    public function updateStatus(Request $request, Genset $genset)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,rented,maintenance,repair,retired,reserved',
        ]);

        $genset->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated to ' . $genset->fresh()->status_label . '.');
    }
}
