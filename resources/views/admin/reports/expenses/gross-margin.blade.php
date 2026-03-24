<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gross Margin Analysis</h1>
        <p class="text-sm text-gray-500 mt-0.5">Revenue, direct costs, and gross profit per booking</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Booking # or client…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Revenue</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['revenue'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Fuel Costs</p>
            <p class="text-xl font-bold text-orange-600 mt-1">{{ number_format($totals['fuel_cost'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Maintenance Costs</p>
            <p class="text-xl font-bold text-orange-600 mt-1">{{ number_format($totals['maintenance_cost'] ?? 0) }}</p>
        </div>
        <div class="bg-{{ ($totals['gross_profit'] ?? 0) >= 0 ? 'green' : 'red' }}-50 border border-{{ ($totals['gross_profit'] ?? 0) >= 0 ? 'green' : 'red' }}-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-{{ ($totals['gross_profit'] ?? 0) >= 0 ? 'green' : 'red' }}-600">Gross Profit</p>
            <p class="text-xl font-bold text-{{ ($totals['gross_profit'] ?? 0) >= 0 ? 'green' : 'red' }}-700 mt-1">{{ number_format($totals['gross_profit'] ?? 0) }}</p>
            @php
                $totalRev = $totals['revenue'] ?? 0;
                $totalGp = $totals['gross_profit'] ?? 0;
                $overallMargin = $totalRev > 0 ? round($totalGp / $totalRev * 100, 1) : 0;
            @endphp
            <p class="text-xs text-gray-500 mt-0.5">{{ $overallMargin }}% margin</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $rows->total() }} bookings</span>
            <a href="{{ route('admin.reports.expenses.gross-margin.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Booking</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Revenue</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Fuel Cost</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Maint. Cost</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Gross Profit</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.bookings.show', $row['id']) }}" class="font-medium text-blue-600 hover:underline text-xs">{{ $row['booking_number'] }}</a>
                            <p class="text-xs text-gray-400">{{ $row['start_date'] }} · {{ $row['duration'] }}d</p>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700 text-xs">{{ $row['client_name'] }}</td>
                        <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $row['genset_name'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($row['revenue']) }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-600">{{ number_format($row['fuel_cost']) }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-600">{{ number_format($row['maintenance_cost']) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $row['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($row['gross_profit']) }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <span class="text-xs font-medium {{ $row['margin_pct'] >= 30 ? 'text-green-600' : ($row['margin_pct'] >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $row['margin_pct'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No bookings found for this period.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->total() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($totals['revenue'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-orange-600">{{ number_format($totals['fuel_cost'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-orange-600">{{ number_format($totals['maintenance_cost'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold {{ ($totals['gross_profit'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($totals['gross_profit'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ $overallMargin }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $rows->links() }}</div>
    </div>
</x-admin-layout>
