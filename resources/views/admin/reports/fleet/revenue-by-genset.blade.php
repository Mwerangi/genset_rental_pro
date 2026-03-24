<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Revenue per Generator</h1>
        <p class="text-sm text-gray-500 mt-0.5">Revenue, direct costs and gross profit per asset</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Revenue (TZS)</p>
            <p class="text-xl font-bold text-indigo-900 mt-1">{{ number_format($totals['revenue'], 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Fuel Cost</p>
            <p class="text-xl font-bold text-orange-900 mt-1">{{ number_format($totals['fuel_cost'], 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Maintenance</p>
            <p class="text-xl font-bold text-yellow-900 mt-1">{{ number_format($totals['maintenance_cost'], 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Gross Profit</p>
            <p class="text-xl font-bold text-green-900 mt-1">{{ number_format($totals['gross_profit'], 0) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Bookings</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-indigo-600">Revenue</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-orange-600">Fuel</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-yellow-700">Maintenance</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-green-700">Gross Profit</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                    <tr class="hover:bg-gray-50 {{ $row['revenue'] == 0 ? 'opacity-50' : '' }}">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.gensets.show', $row['id']) }}" class="font-medium text-gray-900 hover:text-red-600">{{ $row['asset_number'] }}</a>
                            <p class="text-xs text-gray-400">{{ $row['name'] }} · {{ $row['kva_rating'] }} KVA</p>
                        </td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ $row['booking_count'] }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($row['revenue'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-600">{{ number_format($row['fuel_cost'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-yellow-700">{{ number_format($row['maintenance_cost'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $row['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($row['gross_profit'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">
                            @php $m = $row['revenue'] > 0 ? round($row['gross_profit'] / $row['revenue'] * 100, 1) : 0; @endphp
                            <span class="{{ $m >= 50 ? 'text-green-700 font-semibold' : ($m >= 0 ? 'text-yellow-700' : 'text-red-600') }}">{{ $m }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No data for the selected period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold">
                    <tr>
                        <td colspan="2" class="px-4 py-2.5 text-xs uppercase text-gray-600">Totals</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($totals['revenue'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-600">{{ number_format($totals['fuel_cost'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-yellow-700">{{ number_format($totals['maintenance_cost'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right {{ $totals['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totals['gross_profit'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-700">
                            @php $tm = $totals['revenue'] > 0 ? round($totals['gross_profit'] / $totals['revenue'] * 100, 1) : 0; @endphp
                            {{ $tm }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-admin-layout>
