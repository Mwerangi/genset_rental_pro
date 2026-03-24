<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Funnel</h1>
            <p class="text-sm text-gray-500 mt-0.5">Quote requests through to invoiced revenue</p>
        </div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
    </form>

    <!-- Funnel Steps -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @php
            $steps = [
                ['label'=>'Quote Requests','value'=>$stats['quote_requests'],'color'=>'blue'],
                ['label'=>'Quotations','value'=>$stats['quotations_created'],'color'=>'indigo'],
                ['label'=>'Accepted','value'=>$stats['quotations_accepted'],'color'=>'yellow'],
                ['label'=>'Bookings','value'=>$stats['bookings_created'],'color'=>'orange'],
                ['label'=>'Invoices','value'=>$stats['invoices_issued'],'color'=>'green'],
            ];
            $colors = ['blue'=>['bg'=>'bg-blue-50','border'=>'border-blue-100','label'=>'text-blue-600','val'=>'text-blue-900'],'indigo'=>['bg'=>'bg-indigo-50','border'=>'border-indigo-100','label'=>'text-indigo-600','val'=>'text-indigo-900'],'yellow'=>['bg'=>'bg-yellow-50','border'=>'border-yellow-100','label'=>'text-yellow-700','val'=>'text-yellow-900'],'orange'=>['bg'=>'bg-orange-50','border'=>'border-orange-100','label'=>'text-orange-600','val'=>'text-orange-900'],'green'=>['bg'=>'bg-green-50','border'=>'border-green-100','label'=>'text-green-700','val'=>'text-green-900']];
        @endphp
        @foreach($steps as $step)
        @php $c = $colors[$step['color']]; @endphp
        <div class="{{ $c['bg'] }} {{ $c['border'] }} border rounded-xl p-4 text-center">
            <p class="text-xs font-semibold uppercase tracking-wide {{ $c['label'] }}">{{ $step['label'] }}</p>
            <p class="text-3xl font-bold {{ $c['val'] }} mt-1">{{ number_format($step['value']) }}</p>
        </div>
        @endforeach
    </div>

    <!-- Revenue -->
    <div class="bg-red-600 text-white rounded-xl p-4 mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold opacity-80">Total Revenue Invoiced (TZS)</p>
            <p class="text-3xl font-bold mt-0.5">Tsh {{ number_format($stats['revenue_tzs'], 0) }}</p>
        </div>
        <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>

    <!-- Monthly Breakdown -->
    @if($monthly->count())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800">Monthly Funnel Breakdown</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Month</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-blue-600">Requests</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-indigo-600">Quotations</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-yellow-700">Accepted</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-orange-600">Bookings</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-green-700">Invoices</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($monthly as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $row['month'] }}</td>
                        <td class="px-4 py-2.5 text-right text-blue-700">{{ $row['requests'] }}</td>
                        <td class="px-4 py-2.5 text-right text-indigo-700">{{ $row['quotations'] }}</td>
                        <td class="px-4 py-2.5 text-right text-yellow-700">{{ $row['accepted'] }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-700">{{ $row['bookings'] }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ $row['invoices'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-admin-layout>
