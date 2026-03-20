<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genset;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRecord::with('genset')->latest('reported_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('maintenance_number', 'like', "%$s%")
                  ->orWhere('title', 'like', "%$s%")
                  ->orWhere('technician_name', 'like', "%$s%")
                  ->orWhereHas('genset', fn ($gq) => $gq->where('asset_number', 'like', "%$s%"));
            });
        }

        $records = $query->paginate(25)->withQueryString();

        $stats = [
            'total'       => MaintenanceRecord::count(),
            'scheduled'   => MaintenanceRecord::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceRecord::where('status', 'in_progress')->count(),
            'overdue'     => MaintenanceRecord::where('status', 'scheduled')
                ->whereNotNull('scheduled_date')
                ->where('scheduled_date', '<', today())->count(),
        ];

        return view('admin.maintenance.index', compact('records', 'stats'));
    }

    public function create(Request $request)
    {
        $gensets           = Genset::orderBy('asset_number')->get();
        $preselectedGenset = $request->filled('genset_id') ? Genset::find($request->genset_id) : null;
        return view('admin.maintenance.create', compact('gensets', 'preselectedGenset'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'genset_id'            => 'required|exists:gensets,id',
            'booking_id'           => 'nullable|exists:bookings,id',
            'type'                 => 'required|in:scheduled,preventive,repair,breakdown,inspection',
            'priority'             => 'required|in:low,medium,high,critical',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'scheduled_date'       => 'nullable|date',
            'technician_name'      => 'nullable|string|max:255',
            'technician_phone'     => 'nullable|string|max:50',
            'cost'                 => 'nullable|numeric|min:0',
            'run_hours_at_service' => 'nullable|integer|min:0',
            'internal_notes'       => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['cost']       = $validated['cost'] ?? 0;

        $record = MaintenanceRecord::create($validated);

        // Flag genset as under maintenance for breakdown or high/critical priority
        if (in_array($request->priority, ['critical', 'high']) || $request->type === 'breakdown') {
            $genset = Genset::find($validated['genset_id']);
            if ($genset && $genset->status === 'available') {
                $genset->update(['status' => 'maintenance']);
            }
        }

        return redirect()->route('admin.maintenance.show', $record)
            ->with('success', 'Maintenance record ' . $record->maintenance_number . ' created.');
    }

    public function show(MaintenanceRecord $maintenance)
    {
        $maintenance->load(['genset', 'booking.client', 'createdBy']);
        return view('admin.maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceRecord $maintenance)
    {
        $gensets = Genset::orderBy('asset_number')->get();
        return view('admin.maintenance.create', compact('maintenance', 'gensets'));
    }

    public function update(Request $request, MaintenanceRecord $maintenance)
    {
        $validated = $request->validate([
            'type'                 => 'required|in:scheduled,preventive,repair,breakdown,inspection',
            'priority'             => 'required|in:low,medium,high,critical',
            'title'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'scheduled_date'      => 'nullable|date',
            'technician_name'     => 'nullable|string|max:255',
            'technician_phone'    => 'nullable|string|max:50',
            'cost'                => 'nullable|numeric|min:0',
            'run_hours_at_service' => 'nullable|integer|min:0',
            'internal_notes'      => 'nullable|string',
        ]);

        $maintenance->update($validated);

        return redirect()->route('admin.maintenance.show', $maintenance)
            ->with('success', 'Record updated.');
    }

    public function start(Request $request, MaintenanceRecord $maintenance)
    {
        if ($maintenance->status !== 'scheduled') {
            return back()->with('error', 'Only scheduled records can be started.');
        }

        $maintenance->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        $maintenance->genset->update(['status' => 'maintenance']);

        return back()->with('success', 'Maintenance started. Genset marked as under maintenance.');
    }

    public function complete(Request $request, MaintenanceRecord $maintenance)
    {
        if (!in_array($maintenance->status, ['scheduled', 'in_progress'])) {
            return back()->with('error', 'Record cannot be completed from its current status.');
        }

        $request->validate([
            'parts_used'           => 'nullable|string',
            'cost'                 => 'nullable|numeric|min:0',
            'run_hours_at_service' => 'nullable|integer|min:0',
            'next_service_date'    => 'nullable|date',
            'next_service_hours'   => 'nullable|integer|min:0',
            'internal_notes'       => 'nullable|string',
        ]);

        $maintenance->update([
            'status'               => 'completed',
            'completed_at'         => now(),
            'parts_used'           => $request->parts_used           ?? $maintenance->parts_used,
            'cost'                 => $request->filled('cost') ? $request->cost : $maintenance->cost,
            'run_hours_at_service' => $request->run_hours_at_service  ?? $maintenance->run_hours_at_service,
            'next_service_date'    => $request->next_service_date,
            'next_service_hours'   => $request->next_service_hours,
            'internal_notes'       => $request->internal_notes        ?? $maintenance->internal_notes,
        ]);

        // Update genset service info and restore availability
        $gensetUpdates = ['status' => 'available'];

        $gensetUpdates['last_service_date'] = now()->toDateString();

        if ($request->filled('next_service_date')) {
            $gensetUpdates['next_service_date'] = $request->next_service_date;
        }
        if ($request->filled('run_hours_at_service')) {
            $gensetUpdates['run_hours'] = $request->run_hours_at_service;
        }

        $maintenance->genset->update($gensetUpdates);

        return back()->with('success', 'Maintenance completed. Genset is now available.');
    }

    public function cancel(Request $request, MaintenanceRecord $maintenance)
    {
        if (in_array($maintenance->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Record is already ' . $maintenance->status . '.');
        }

        $maintenance->update(['status' => 'cancelled']);

        // Restore genset status only if no other active records exist for it
        if ($maintenance->genset->status === 'maintenance') {
            $otherActive = MaintenanceRecord::where('genset_id', $maintenance->genset_id)
                ->where('id', '!=', $maintenance->id)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->exists();

            if (!$otherActive) {
                $maintenance->genset->update(['status' => 'available']);
            }
        }

        return back()->with('success', 'Maintenance record cancelled.');
    }
}
