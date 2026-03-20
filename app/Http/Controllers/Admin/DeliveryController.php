<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\Genset;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['booking.client', 'genset'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('delivery_number', 'like', "%$s%")
                  ->orWhere('driver_name', 'like', "%$s%")
                  ->orWhereHas('booking', fn ($bq) => $bq->where('booking_number', 'like', "%$s%"))
                  ->orWhereHas('genset', fn ($gq) => $gq->where('asset_number', 'like', "%$s%"));
            });
        }

        $deliveries = $query->paginate(25)->withQueryString();

        $stats = [
            'total'      => Delivery::count(),
            'pending'    => Delivery::where('status', 'pending')->count(),
            'dispatched' => Delivery::where('status', 'dispatched')->count(),
            'completed'  => Delivery::where('status', 'completed')->count(),
        ];

        return view('admin.deliveries.index', compact('deliveries', 'stats'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['booking.client', 'genset', 'createdBy']);
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id'          => 'required|exists:bookings,id',
            'genset_id'           => 'required|exists:gensets,id',
            'type'                => 'required|in:delivery,return',
            'driver_name'         => 'nullable|string|max:255',
            'driver_phone'        => 'nullable|string|max:50',
            'vehicle_details'     => 'nullable|string|max:255',
            'origin_address'      => 'nullable|string|max:500',
            'destination_address' => 'nullable|string|max:500',
            'scheduled_at'        => 'nullable|date',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = auth()->id();
        $delivery = Delivery::create($validated);

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', 'Delivery order ' . $delivery->delivery_number . ' created.');
    }

    public function dispatch(Request $request, Delivery $delivery)
    {
        if ($delivery->status !== 'pending') {
            return back()->with('error', 'Delivery can only be dispatched from pending status.');
        }

        $request->validate([
            'driver_name'     => 'required|string|max:255',
            'driver_phone'    => 'nullable|string|max:50',
            'vehicle_details' => 'nullable|string|max:255',
            'scheduled_at'    => 'nullable|date',
        ]);

        $delivery->update([
            'status'          => 'dispatched',
            'dispatched_at'   => now(),
            'driver_name'     => $request->driver_name,
            'driver_phone'    => $request->driver_phone,
            'vehicle_details' => $request->vehicle_details ?? $delivery->vehicle_details,
            'scheduled_at'    => $request->scheduled_at ?? $delivery->scheduled_at,
        ]);

        return back()->with('success', $delivery->delivery_number . ' dispatched.');
    }

    public function complete(Request $request, Delivery $delivery)
    {
        if (!in_array($delivery->status, ['pending', 'dispatched'])) {
            return back()->with('error', 'Delivery cannot be completed from its current status.');
        }

        $request->validate([
            'pod_notes'     => 'nullable|string|max:1000',
            'pod_signed_by' => 'nullable|string|max:255',
        ]);

        $delivery->update([
            'status'        => 'completed',
            'completed_at'  => now(),
            'pod_notes'     => $request->pod_notes,
            'pod_signed_by' => $request->pod_signed_by,
        ]);

        return back()->with('success', $delivery->delivery_number . ' marked as completed.');
    }

    public function fail(Request $request, Delivery $delivery)
    {
        if (!in_array($delivery->status, ['pending', 'dispatched'])) {
            return back()->with('error', 'Cannot update delivery in its current status.');
        }

        $request->validate(['notes' => 'nullable|string|max:1000']);

        $combined = collect([$delivery->notes, $request->notes])->filter()->implode("\n");
        $delivery->update([
            'status' => 'failed',
            'notes'  => $combined ?: null,
        ]);

        return back()->with('success', $delivery->delivery_number . ' marked as failed.');
    }
}
