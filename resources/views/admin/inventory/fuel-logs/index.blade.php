<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fuel Logs</h1>
            <p class="text-gray-500 mt-1">Fleet-wide fuel consumption tracking</p>
        </div>
        <button onclick="document.getElementById('addFuelModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Log Fuelling
        </button>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Litres (all time)</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_litres'], 0) }} L</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Fuel Cost</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">Tsh {{ number_format($stats['total_cost'], 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-3xl font-bold mt-1" style="color:#1e40af;">{{ number_format($stats['this_month'], 0) }} L</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div>
                <select name="genset_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Gensets</option>
                    @foreach($gensets as $gs)
                        <option value="{{ $gs->id }}" {{ request('genset_id') == $gs->id ? 'selected' : '' }}>{{ $gs->asset_number }} — {{ $gs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request()->hasAny(['genset_id','from','to']))
                <a href="{{ route('admin.fuel-logs.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($logs->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400 text-sm">No fuel logs yet.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Genset</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Booking</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Litres</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cost/L</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">L/hr</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.fuel-logs.genset', $log->genset) }}" class="font-medium text-red-600 hover:underline">{{ $log->genset->asset_number }}</a>
                            <p class="text-xs text-gray-400">{{ $log->genset->name }}</p>
                        </td>
                        <td class="px-5 py-3 text-xs">
                            @if($log->booking)
                                <a href="{{ route('admin.bookings.show', $log->booking) }}" class="text-red-600 hover:underline font-mono">{{ $log->booking->booking_number }}</a>
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 font-semibold text-gray-800">{{ number_format($log->litres, 1) }} L</td>
                        <td class="px-5 py-3 text-gray-600 text-xs">Tsh {{ number_format($log->cost_per_litre, 2) }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-800">Tsh {{ number_format($log->total_cost, 0) }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($log->consumption_rate) {{ $log->consumption_rate }} L/hr @else — @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $log->fuelled_at->format('d M Y H:i') }}</td>
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
                <h3 class="text-lg font-bold text-gray-900">Log Fuelling Event</h3>
                <button onclick="document.getElementById('addFuelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.fuel-logs.store') }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Genset <span class="text-red-500">*</span></label>
                        <select name="genset_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select genset —</option>
                            @foreach($gensets as $gs)
                                <option value="{{ $gs->id }}" {{ request('genset_id') == $gs->id ? 'selected' : '' }}>{{ $gs->asset_number }} — {{ $gs->name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                            <input type="number" name="run_hours_before" min="0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Meter reading">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Run Hours After</label>
                            <input type="number" name="run_hours_after" min="0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Meter reading">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pay From (Bank Account) <span class="text-red-500">*</span></label>
                        <select name="bank_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select bank account —</option>
                            @foreach($bankAccounts as $ba)
                                <option value="{{ $ba->id }}">{{ $ba->name }} (Tsh {{ number_format($ba->current_balance, 0) }})</option>
                            @endforeach
                        </select>
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
