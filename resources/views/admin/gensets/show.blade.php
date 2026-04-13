<x-admin-layout>
    @php
        $statusStyles = [
            'available'   => 'background:#dcfce7;color:#166534;',
            'rented'      => 'background:#dbeafe;color:#1e40af;',
            'maintenance' => 'background:#fef9c3;color:#854d0e;',
            'repair'      => 'background:#ffedd5;color:#9a3412;',
            'retired'     => 'background:#f3f4f6;color:#6b7280;',
            'reserved'    => 'background:#f3e8ff;color:#6b21a8;',
        ];
        $ss = $statusStyles[$genset->status] ?? 'background:#f3f4f6;color:#374151;';
    @endphp

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
            <a href="{{ route('admin.gensets.index') }}" class="hover:text-red-600">Fleet</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $genset->asset_number }}</span>
        </div>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $genset->asset_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" style="{{ $ss }}">{{ $genset->status_label }}</span>
                </div>
                <p class="text-gray-500 mt-1">{{ $genset->name }}@if($genset->brand) — {{ $genset->brand }}{{ $genset->model ? ' ' . $genset->model : '' }}@endif</p>
            </div>
            <div class="flex gap-2">
                @permission('edit_gensets')
                <a href="{{ route('admin.gensets.edit', $genset) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endpermission
            </div>
        </div>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Specifications -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Specifications</h2>
                </div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->type_formatted }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Power Rating</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->power_rating }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fuel Type</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->fuel_type ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Serial Number</p>
                        <p class="mt-1 font-mono text-sm text-gray-800">{{ $genset->serial_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Color</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->color ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Weight</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->weight_kg ? $genset->weight_kg . ' kg' : '—' }}</p>
                    </div>
                    @if($genset->dimensions)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Dimensions</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->dimensions }}</p>
                    </div>
                    @endif
                    @if($genset->tank_capacity_litres)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tank Capacity</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->tank_capacity_litres }} L</p>
                    </div>
                    @endif
                    @if($genset->run_hours !== null)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Run Hours</p>
                        <p class="mt-1 font-medium text-gray-800">{{ number_format($genset->run_hours) }} hrs</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rates & Acquisition -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Rates & Financial</h2>
                </div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Daily Rate</p>
                        <p class="mt-1 font-bold text-gray-900">@if($genset->daily_rate) TZS {{ number_format($genset->daily_rate, 0) }} @else — @endif</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Weekly Rate</p>
                        <p class="mt-1 font-bold text-gray-900">@if($genset->weekly_rate) TZS {{ number_format($genset->weekly_rate, 0) }} @else — @endif</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Monthly Rate</p>
                        <p class="mt-1 font-bold text-gray-900">@if($genset->monthly_rate) TZS {{ number_format($genset->monthly_rate, 0) }} @else — @endif</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Purchase Price</p>
                        <p class="mt-1 font-medium text-gray-800">@if($genset->purchase_price) TZS {{ number_format($genset->purchase_price, 0) }} @else — @endif</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Purchase Date</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->purchase_date ? $genset->purchase_date->format('d M Y') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Supplier</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->supplier ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Warranty</p>
                        <p class="mt-1 font-medium" @if($genset->warranty_status === 'expired') style="color:#dc2626;" @elseif($genset->warranty_status === 'active') style="color:#16a34a;" @else class="text-gray-400" @endif>
                            {{ $genset->warranty_expiry ? $genset->warranty_expiry->format('d M Y') . ' (' . ucfirst($genset->warranty_status) . ')' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Maintenance</h2>
                    <a href="{{ route('admin.maintenance.create', ['genset_id' => $genset->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white" style="background:#dc2626;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Log Maintenance
                    </a>
                </div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Service</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->last_service_date ? $genset->last_service_date->format('d M Y') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Next Service</p>
                        <p class="mt-1 font-medium @if($genset->service_due) text-red-600 font-bold @else text-gray-800 @endif">
                            {{ $genset->next_service_date ? $genset->next_service_date->format('d M Y') : '—' }}
                            @if($genset->service_due) <span class="ml-1 text-xs">⚠ OVERDUE</span> @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Service Interval</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $genset->service_interval_hours ? $genset->service_interval_hours . ' hrs' : '—' }}</p>
                    </div>
                </div>
                @if($genset->notes)
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Notes</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $genset->notes }}</p>
                </div>
                @endif
                {{-- Maintenance History --}}
                @if($genset->maintenanceRecords->isEmpty())
                    <div class="px-5 py-6 text-center text-sm text-gray-400">No maintenance records yet</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Title</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Cost</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($genset->maintenanceRecords()->orderByDesc('scheduled_date')->take(10)->get() as $mr)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $mr->maintenance_number }}</td>
                                <td class="px-4 py-2.5 text-gray-800 max-w-[160px]"><span class="truncate block">{{ $mr->title }}</span></td>
                                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $mr->type_label }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $mr->scheduled_date ? $mr->scheduled_date->format('d M Y') : '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-700">{{ $mr->cost > 0 ? 'Tsh '.number_format($mr->cost) : '—' }}</td>
                                <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $mr->status_style }}">{{ $mr->status_label }}</span></td>
                                <td class="px-4 py-2.5 text-right"><a href="{{ route('admin.maintenance.show', $mr) }}" class="text-xs text-red-600 hover:underline">View</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Booking History -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Booking History</h2>
                    <span class="text-xs text-gray-500">{{ $recentBookings->count() }} recent</span>
                </div>
                @if($recentBookings->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">No bookings yet</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Ref</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Client</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Period</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentBookings as $booking)
                            @php
                                $bStyles = ['draft'=>'background:#f3f4f6;color:#374151;','pending'=>'background:#fef9c3;color:#854d0e;','created'=>'background:#dbeafe;color:#1e40af;','approved'=>'background:#dbeafe;color:#1e40af;','active'=>'background:#dcfce7;color:#166534;','returned'=>'background:#f3e8ff;color:#6b21a8;','invoiced'=>'background:#fee2e2;color:#991b1b;','paid'=>'background:#d1fae5;color:#065f46;','cancelled'=>'background:#f3f4f6;color:#6b7280;'];
                                $bs = $bStyles[$booking->status] ?? 'background:#f3f4f6;';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><a href="{{ route('admin.bookings.show', $booking) }}" class="text-red-600 hover:underline font-medium font-mono">{{ $booking->booking_number }}</a></td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $booking->client?->company_name ?? $booking->client?->full_name ?? $booking->customer_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $booking->rental_start_date ? $booking->rental_start_date->format('d M Y') : '—' }}
                                    @if($booking->rental_end_date) – {{ $booking->rental_end_date->format('d M Y') }} @endif
                                </td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $bs }}">{{ ucfirst($booking->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Fuel Logs Summary -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Fuel Logs</h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.fuel-logs.genset', $genset) }}" class="text-xs text-red-600 hover:underline font-medium">View All</a>
                    @permission('create_fuel_logs')
                    <button onclick="document.getElementById('fuelLogModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white" style="background:#dc2626;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Log Fuel
                    </button>
                    @endpermission
                </div>
            </div>
            @php
                $fuelStats = [
                    'total_litres' => $genset->fuelLogs()->sum('litres'),
                    'total_cost'   => $genset->fuelLogs()->sum('total_cost'),
                ];
                $recentFuelLogs = $genset->fuelLogs()->orderByDesc('fuelled_at')->take(5)->get();
            @endphp
            <div class="grid grid-cols-2 divide-x divide-gray-100 border-b border-gray-100">
                <div class="px-5 py-3">
                    <p class="text-xs text-gray-500">Total Litres</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($fuelStats['total_litres'], 0) }} L</p>
                </div>
                <div class="px-5 py-3">
                    <p class="text-xs text-gray-500">Total Fuel Cost</p>
                    <p class="text-lg font-bold text-gray-900">Tsh {{ number_format($fuelStats['total_cost'], 0) }}</p>
                </div>
            </div>
            @if($recentFuelLogs->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No fuel logs recorded yet.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Litres</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Cost</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">L/hr</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentFuelLogs as $fl)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $fl->fuelled_at->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 font-semibold text-gray-800">{{ number_format($fl->litres, 1) }} L</td>
                            <td class="px-4 py-2.5 text-xs text-gray-600">Tsh {{ number_format($fl->total_cost, 0) }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $fl->consumption_rate ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Log Fuel Modal (on genset page) --}}
        <div id="fuelLogModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 sticky top-0 bg-white">
                    <h3 class="text-lg font-bold text-gray-900">Log Fuelling — {{ $genset->asset_number }}</h3>
                    <button onclick="document.getElementById('fuelLogModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.fuel-logs.store') }}">
                    @csrf
                    <input type="hidden" name="genset_id" value="{{ $genset->id }}">
                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Litres <span class="text-red-500">*</span></label>
                                <input type="number" name="litres" required min="0.1" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost per Litre (Tsh) <span class="text-red-500">*</span></label>
                                <input type="number" name="cost_per_litre" required min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Run Hours Before</label>
                                <input type="number" name="run_hours_before" min="0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Run Hours After</label>
                                <input type="number" name="run_hours_after" min="0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="fuelled_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fuelled By</label>
                                <input type="text" name="fuelled_by" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location / Site</label>
                                <input type="text" name="location" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 px-6 pb-5">
                        <button type="button" onclick="document.getElementById('fuelLogModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Save Log</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Info -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Quick Info</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="px-5 py-3 flex justify-between">
                        <span class="text-xs text-gray-500">Asset Number</span>
                        <span class="text-sm font-bold font-mono text-gray-900">{{ $genset->asset_number }}</span>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <span class="text-xs text-gray-500">Serial Number</span>
                        <span class="text-sm font-mono text-gray-800">{{ $genset->serial_number ?? '—' }}</span>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <span class="text-xs text-gray-500">Location</span>
                        <span class="text-sm text-gray-800">{{ $genset->location ?? '—' }}</span>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <span class="text-xs text-gray-500">Total Bookings</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $genset->bookings->count() }}</span>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <span class="text-xs text-gray-500">Added</span>
                        <span class="text-sm text-gray-700">{{ $genset->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Change Status -->
            @permission('update_genset_status')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" x-data="{ open: false }">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Change Status</h2>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-500 mb-3">Current: <strong>{{ $genset->status_label }}</strong></p>
                    <form method="POST" action="{{ route('admin.gensets.status', $genset) }}">
                        @csrf
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 mb-3">
                            <option value="available" @selected($genset->status === 'available')>Available</option>
                            <option value="rented" @selected($genset->status === 'rented')>On Rent</option>
                            <option value="maintenance" @selected($genset->status === 'maintenance')>Maintenance</option>
                            <option value="repair" @selected($genset->status === 'repair')>Under Repair</option>
                            <option value="reserved" @selected($genset->status === 'reserved')>Reserved</option>
                            <option value="retired" @selected($genset->status === 'retired')>Retired</option>
                        </select>
                        <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">Update Status</button>
                    </form>
                </div>
            </div>
            @endpermission

            <!-- Danger Zone -->
            @permission('delete_gensets')
            @if($genset->status !== 'rented')
            <div class="bg-white border border-red-100 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-red-100">
                    <h2 class="font-semibold text-red-600">Danger Zone</h2>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-500 mb-3">Permanently delete this genset. This action cannot be undone.</p>
                    <form method="POST" action="{{ route('admin.gensets.destroy', $genset) }}" onsubmit="return confirm('Delete {{ $genset->asset_number }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50">Delete Genset</button>
                    </form>
                </div>
            </div>
            @endif
            @endpermission
        </div>
    </div>
</x-admin-layout>
