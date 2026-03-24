<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Payment Methods Breakdown</h1>
        <p class="text-sm text-gray-500 mt-0.5">Revenue collected grouped by payment method</p>
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
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    @php
        $methodColors = [
            'bank_transfer' => 'bg-blue-100 text-blue-700',
            'cash' => 'bg-green-100 text-green-700',
            'cheque' => 'bg-purple-100 text-purple-700',
            'mobile_money' => 'bg-orange-100 text-orange-700',
        ];
        $grandTotal = $totals['total'] ?? 0;
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        @forelse($byMethod as $method)
        @php $pct = $grandTotal > 0 ? round($method['total'] / $grandTotal * 100, 1) : 0; @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $method['method']) }}</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($method['total']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $method['count'] }} payments · {{ $pct }}%</p>
            <div class="w-full h-1 rounded-full bg-gray-100 mt-2">
                <div class="h-full rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-4 text-center py-8 text-gray-400">No payments found for this period.</div>
        @endforelse
    </div>

    @if($byMethod->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Monthly Trend by Method</h3>
            <a href="{{ route('admin.reports.invoices.payment-methods.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Month</th>
                        @foreach($byMethod as $method)
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 capitalize">{{ str_replace('_', ' ', $method['method']) }}</th>
                        @endforeach
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($monthly as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-900">{{ $row['month'] }}</td>
                        @foreach($byMethod as $method)
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row[$method['method']] ?? 0) }}</td>
                        @endforeach
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($row['total'] ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ 2 + count($byMethod) }}" class="text-center py-6 text-gray-400">—</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        @foreach($byMethod as $method)
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ number_format($method['total']) }}</td>
                        @endforeach
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($grandTotal) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</x-admin-layout>
