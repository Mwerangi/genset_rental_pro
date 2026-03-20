<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Accounts Receivable Aging</h1>
            <p class="text-sm text-gray-500 mt-0.5">Outstanding balances by client, bucketed by days overdue</p>
        </div>
        <a href="{{ route('admin.accounting.reports.statement') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Statement of Accounts</a>
    </div>

    <!-- Date filter -->
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">As at:</label>
        <input type="date" name="as_at" value="{{ $asAt }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Show</button>
    </form>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <div class="bg-green-50 border border-green-100 rounded-xl p-3 text-center">
            <p class="text-xs text-green-600 font-medium">Current</p>
            <p class="text-lg font-bold text-green-800 mt-0.5">Tsh {{ number_format($totals['current'], 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3 text-center">
            <p class="text-xs text-yellow-700 font-medium">1–30 Days</p>
            <p class="text-lg font-bold text-yellow-800 mt-0.5">Tsh {{ number_format($totals['days_1_30'], 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-3 text-center">
            <p class="text-xs text-orange-600 font-medium">31–60 Days</p>
            <p class="text-lg font-bold text-orange-800 mt-0.5">Tsh {{ number_format($totals['days_31_60'], 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-center">
            <p class="text-xs text-red-600 font-medium">61–90 Days</p>
            <p class="text-lg font-bold text-red-800 mt-0.5">Tsh {{ number_format($totals['days_61_90'], 0) }}</p>
        </div>
        <div class="bg-red-100 border border-red-200 rounded-xl p-3 text-center">
            <p class="text-xs text-red-700 font-medium">90+ Days</p>
            <p class="text-lg font-bold text-red-900 mt-0.5">Tsh {{ number_format($totals['days_90plus'], 0) }}</p>
        </div>
    </div>

    @if(empty($clients))
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-500 font-medium">No outstanding balances as at {{ \Carbon\Carbon::parse($asAt)->format('d F Y') }}</p>
        </div>
    @else
    <!-- Main table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-800">Aging Report — as at {{ \Carbon\Carbon::parse($asAt)->format('d F Y') }}</p>
            <span class="text-xs text-gray-500">{{ count($clients) }} client{{ count($clients) !== 1 ? 's' : '' }} with outstanding balances</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-right px-3 py-2.5 text-xs font-medium text-green-700">Current</th>
                        <th class="text-right px-3 py-2.5 text-xs font-medium text-yellow-700">1–30 Days</th>
                        <th class="text-right px-3 py-2.5 text-xs font-medium text-orange-600">31–60 Days</th>
                        <th class="text-right px-3 py-2.5 text-xs font-medium text-red-600">61–90 Days</th>
                        <th class="text-right px-3 py-2.5 text-xs font-medium text-red-800">90+ Days</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-700">Total</th>
                        <th class="px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($clients as $row)
                    <tr x-data="{ open: false }" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <button @click="open = !open" class="flex items-center gap-1.5 text-left">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $row['name'] }}</p>
                                    @if($row['email'])
                                    <p class="text-xs text-gray-400">{{ $row['email'] }}</p>
                                    @endif
                                </div>
                            </button>
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-xs {{ $row['current'] > 0 ? 'text-green-700 font-medium' : 'text-gray-300' }}">
                            {{ $row['current'] > 0 ? number_format($row['current'], 0) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-xs {{ $row['days_1_30'] > 0 ? 'text-yellow-700 font-medium' : 'text-gray-300' }}">
                            {{ $row['days_1_30'] > 0 ? number_format($row['days_1_30'], 0) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-xs {{ $row['days_31_60'] > 0 ? 'text-orange-600 font-medium' : 'text-gray-300' }}">
                            {{ $row['days_31_60'] > 0 ? number_format($row['days_31_60'], 0) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-xs {{ $row['days_61_90'] > 0 ? 'text-red-600 font-medium' : 'text-gray-300' }}">
                            {{ $row['days_61_90'] > 0 ? number_format($row['days_61_90'], 0) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-mono text-xs {{ $row['days_90plus'] > 0 ? 'text-red-800 font-bold' : 'text-gray-300' }}">
                            {{ $row['days_90plus'] > 0 ? number_format($row['days_90plus'], 0) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold text-gray-900">
                            {{ number_format($row['total'], 0) }}
                        </td>
                        <td class="px-3 py-3">
                            <a href="{{ route('admin.accounting.reports.statement', ['client_id' => $row['id']]) }}"
                               class="text-xs text-blue-600 hover:underline whitespace-nowrap">Statement</a>
                        </td>
                    </tr>
                    <!-- Invoice detail rows (collapsible) -->
                    <tr x-show="open" x-cloak class="bg-blue-50/40">
                        <td colspan="8" class="px-6 py-0">
                            <table class="w-full text-xs my-2">
                                <thead>
                                    <tr class="text-gray-500">
                                        <th class="text-left py-1.5 pr-4 font-medium">Invoice</th>
                                        <th class="text-left py-1.5 pr-4 font-medium">Issue Date</th>
                                        <th class="text-left py-1.5 pr-4 font-medium">Due Date</th>
                                        <th class="text-right py-1.5 pr-4 font-medium">Amount</th>
                                        <th class="text-right py-1.5 pr-4 font-medium">Paid</th>
                                        <th class="text-right py-1.5 pr-4 font-medium">Balance</th>
                                        <th class="text-right py-1.5 font-medium">Days Overdue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-100">
                                    @foreach($row['invoices'] as $inv)
                                    <tr>
                                        <td class="py-1.5 pr-4">
                                            <a href="{{ route('admin.invoices.show', $inv['invoice_id']) }}" class="text-blue-600 hover:underline font-mono">{{ $inv['invoice_number'] }}</a>
                                        </td>
                                        <td class="py-1.5 pr-4 text-gray-600">{{ $inv['issue_date']?->format('d M Y') }}</td>
                                        <td class="py-1.5 pr-4 text-gray-600">{{ $inv['due_date']?->format('d M Y') ?? '—' }}</td>
                                        <td class="py-1.5 pr-4 text-right font-mono">{{ number_format($inv['total_amount'], 0) }}</td>
                                        <td class="py-1.5 pr-4 text-right font-mono text-green-700">{{ number_format($inv['amount_paid'], 0) }}</td>
                                        <td class="py-1.5 pr-4 text-right font-mono font-semibold">{{ number_format($inv['balance'], 0) }}</td>
                                        <td class="py-1.5 text-right">
                                            @if($inv['days_overdue'] === null || $inv['days_overdue'] <= 0)
                                                <span class="px-1.5 py-0.5 rounded-full bg-green-100 text-green-700">Current</span>
                                            @elseif($inv['days_overdue'] <= 30)
                                                <span class="px-1.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700">{{ $inv['days_overdue'] }}d</span>
                                            @elseif($inv['days_overdue'] <= 60)
                                                <span class="px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700">{{ $inv['days_overdue'] }}d</span>
                                            @elseif($inv['days_overdue'] <= 90)
                                                <span class="px-1.5 py-0.5 rounded-full bg-red-100 text-red-600">{{ $inv['days_overdue'] }}d</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded-full bg-red-200 text-red-800 font-bold">{{ $inv['days_overdue'] }}d</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-700">Grand Total</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-green-700">{{ number_format($totals['current'], 0) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-yellow-700">{{ number_format($totals['days_1_30'], 0) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-orange-600">{{ number_format($totals['days_31_60'], 0) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-red-600">{{ number_format($totals['days_61_90'], 0) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-red-800">{{ number_format($totals['days_90plus'], 0) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-base font-bold text-gray-900">Tsh {{ number_format($totals['total'], 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</x-admin-layout>
