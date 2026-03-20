<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.fuel-logs.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to All Fuel Logs
        </a>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $genset->asset_number }}</h1>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">{{ $genset->name }}</span>
                </div>
                <p class="text-gray-500 mt-1 text-sm">Fuel consumption history — all time</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.gensets.show', $genset) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">View Genset</a>
                <button onclick="document.getElementById('addFuelModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Log Fuelling
                </button>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Litres Consumed</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_litres'], 0) }} L</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Fuel Cost</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">Tsh {{ number_format($stats['total_cost'], 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Avg Consumption Rate</p>
            @if($stats['avg_consumption'])
                <p class="text-3xl font-bold mt-1" style="color:#d97706;">{{ $stats['avg_consumption'] }} L/hr</p>
            @else
                <p class="text-xl text-gray-400 mt-1">Not enough data</p>
            @endif
        </div>
    </div>

    {{-- Logs table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Fuelling Events</h2>
            <span class="text-sm text-gray-500">{{ $logs->total() }} records</span>
        </div>
        @if($logs->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400 text-sm">No fuel logs for this genset yet.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Litres</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cost/L</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total Cost</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Run Hours</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">L/hr</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Booking</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Location</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-gray-600">{{ $log->fuelled_at->format('d M Y') }}<br>{{ $log->fuelled_at->format('H:i') }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-800">{{ number_format($log->litres, 1) }} L</td>
                        <td class="px-5 py-3 text-xs text-gray-500">Tsh {{ number_format($log->cost_per_litre, 2) }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-800">Tsh {{ number_format($log->total_cost, 0) }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($log->run_hours_before !== null)
                                {{ number_format($log->run_hours_before, 1) }} → {{ number_format($log->run_hours_after, 1) }}
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($log->consumption_rate) {{ $log->consumption_rate }} @else — @endif
                        </td>
                        <td class="px-5 py-3 text-xs">
                            @if($log->booking)
                                <a href="{{ route('admin.bookings.show', $log->booking) }}" class="text-red-600 hover:underline font-mono">{{ $log->booking->booking_number }}</a>
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $log->location ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $log->fuelled_by ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($logs->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Add Fuel Modal --}}
    <div id="addFuelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 sticky top-0 bg-white">
                <h3 class="text-lg font-bold text-gray-900">Log Fuelling — {{ $genset->asset_number }}</h3>
                <button onclick="document.getElementById('addFuelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
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
                    <button type="button" onclick="document.getElementById('addFuelModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
