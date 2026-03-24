<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Fuel Consumption</h1>
        <p class="text-sm text-gray-500 mt-0.5">Fuel usage and cost per generator</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <select name="genset_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Generators</option>
            @foreach($gensetList as $g)
            <option value="{{ $g->id }}" @selected($gensetId == $g->id)>{{ $g->asset_number }} — {{ $g->name }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search generator, fuelled by…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[200px]">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Total Litres</p>
            <p class="text-2xl font-bold text-orange-900 mt-1">{{ number_format($totals['litres'], 1) }} L</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Total Cost</p>
            <p class="text-2xl font-bold text-red-900 mt-1">Tsh {{ number_format($totals['cost'], 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Avg / Litre</p>
            <p class="text-2xl font-bold text-yellow-900 mt-1">Tsh {{ number_format($totals['avg_per_litre'], 0) }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Log Entries</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['log_count'] }}</p>
        </div>
    </div>

    @if($byGenset->count())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-4 py-3 border-b border-gray-100"><p class="font-semibold text-gray-800">By Generator</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Entries</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-orange-600">Litres</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-red-600">Cost (TZS)</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-yellow-700">Avg / Litre</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($byGenset as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $row['log_count'] }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-700">{{ number_format($row['litres'], 1) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($row['cost'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-yellow-700">{{ number_format($row['avg_per_litre'], 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($logs->total() > 0)
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <p class="font-semibold text-gray-800">Fuel Log Entries</p>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400">{{ $logs->total() }} records</span>
                <a href="{{ route('admin.reports.fleet.fuel.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Litres</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Cost/L</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Total Cost</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Fuelled By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($log->fuelled_at)->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ $log->genset?->asset_number }} {{ $log->genset?->name }}</td>
                        <td class="px-4 py-2 text-right text-orange-600">{{ number_format($log->litres, 1) }}</td>
                        <td class="px-4 py-2 text-right text-gray-500">{{ number_format($log->cost_per_litre, 0) }}</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($log->total_cost, 0) }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $log->fuelled_by ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
    @else
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-400">No fuel logs found for the selected period.</p>
        </div>
    @endif
</x-admin-layout>
