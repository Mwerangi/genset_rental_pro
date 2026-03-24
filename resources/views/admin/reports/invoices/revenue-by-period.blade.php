<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Revenue by Period</h1>
        <p class="text-sm text-gray-500 mt-0.5">Invoice revenue grouped by time period</p>
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
            <label class="block text-xs font-medium text-gray-600 mb-1">Group by</label>
            <select name="group_by" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="month" @selected($groupBy === 'month')>Month</option>
                <option value="quarter" @selected($groupBy === 'quarter')>Quarter</option>
                <option value="year" @selected($groupBy === 'year')>Year</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Invoiced (TZS)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['tzs_total'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Invoiced (USD)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['usd_total'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Amount Paid (TZS)</p>
            <p class="text-xl font-bold text-green-700 mt-1">{{ number_format($totals['paid_tzs'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Invoices Count</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $totals['count'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-end">
            <a href="{{ route('admin.reports.invoices.revenue-by-period.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Period</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Invoices</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Total TZS</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Paid TZS</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Total USD</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Collection %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($periods as $row)
                    @php
                        $pct = $row['total_tzs'] > 0 ? round($row['paid_tzs'] / $row['total_tzs'] * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $row['period'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ $row['count'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($row['total_tzs']) }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ number_format($row['paid_tzs']) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['usd_total'] ?? 0, 2) }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 h-1.5 rounded-full bg-gray-200 overflow-hidden">
                                    <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                         style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">No invoices found for this period.</td></tr>
                    @endforelse
                </tbody>
                @if(!empty($periods) && count($periods) > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ $totals['count'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($totals['tzs_total'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-green-700">{{ number_format($totals['paid_tzs'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-600">{{ number_format($totals['usd_total'] ?? 0, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-admin-layout>
