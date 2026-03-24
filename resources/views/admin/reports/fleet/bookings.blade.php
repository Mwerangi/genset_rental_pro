<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Booking Summary</h1>
        <p class="text-sm text-gray-500 mt-0.5">Booking counts and values over the selected period</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex flex-col gap-1 min-w-[260px]">
            <label class="text-xs font-medium text-gray-500">Search client / booking no. / opened by</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Deogratius, BK-2026-0012, Acme" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
        </div>
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
        @if($search)
        <a href="{{ route('admin.reports.fleet.bookings', ['from' => $from, 'to' => $to]) }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200">Clear</a>
        @endif
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-5">
        @php
            $statItems = [
                ['Total','total','gray'],['Pending','pending','yellow'],
                ['Approved','approved','blue'],['Active','active','green'],
                ['Completed','returned','indigo'],['Cancelled','cancelled','red'],
            ];
            $colors2 = ['gray'=>'bg-gray-50 text-gray-700','yellow'=>'bg-yellow-50 text-yellow-800','blue'=>'bg-blue-50 text-blue-800','green'=>'bg-green-50 text-green-800','indigo'=>'bg-indigo-50 text-indigo-800','red'=>'bg-red-50 text-red-700'];
        @endphp
        @foreach($statItems as [$label, $key, $color])
        <div class="{{ $colors2[$color] }} border border-current border-opacity-20 rounded-xl p-3 text-center">
            <p class="text-xs font-semibold uppercase tracking-wide opacity-70">{{ $label }}</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats[$key]) }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Booking Value (TZS)</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Tsh {{ number_format($stats['total_value'], 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Rental Duration</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['avg_duration'] }} days</p>
        </div>
    </div>

    @if($monthly->count())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-4 py-3 border-b border-gray-100"><p class="font-semibold text-gray-800">Monthly Breakdown</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Month</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Bookings</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-red-500">Cancelled</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-indigo-600">Value (TZS)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($monthly as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $row['month'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-700">{{ $row['count'] }}</td>
                        <td class="px-4 py-2.5 text-right {{ $row['cancelled'] > 0 ? 'text-red-500' : 'text-gray-300' }}">{{ $row['cancelled'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-800">{{ number_format($row['value'], 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Individual Bookings --}}
    @if($bookings->total() > 0)
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">Individual Bookings</p>
                @if($search)
                <p class="text-xs text-gray-400 mt-0.5">Filtered by &ldquo;{{ $search }}&rdquo; &mdash; {{ $bookings->total() }} result(s)</p>
                @else
                <p class="text-xs text-gray-400 mt-0.5">{{ $bookings->total() }} record(s)</p>
                @endif
            </div>
            <a href="{{ route('admin.reports.fleet.bookings.export', array_filter(['from' => $from, 'to' => $to, 'search' => $search]))}}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Export CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">#</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Booking No.</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Genset</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Start Date</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Days</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Value (TZS)</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Opened By</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $statusColors = [
                            'pending'   => 'bg-yellow-100 text-yellow-800',
                            'approved'  => 'bg-blue-100 text-blue-800',
                            'active'    => 'bg-green-100 text-green-800',
                            'returned'  => 'bg-indigo-100 text-indigo-800',
                            'invoiced'  => 'bg-purple-100 text-purple-800',
                            'paid'      => 'bg-emerald-100 text-emerald-800',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    @foreach($bookings as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-center text-xs text-gray-400">{{ ($bookings->currentPage() - 1) * $bookings->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-700">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="text-red-600 hover:underline">{{ $b->booking_number }}</a>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-900">
                            {{ $b->client?->company_name ?: ($b->client?->full_name ?? $b->customer_name ?? '—') }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">
                            {{ $b->genset ? $b->genset->asset_number . ' — ' . $b->genset->name : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">
                            {{ $b->rental_start_date ? $b->rental_start_date->format('d M Y') : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ $b->rental_duration_days ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-800 font-medium">
                            {{ number_format($b->total_amount * ($b->exchange_rate_to_tzs ?? 1), 0) }}
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$b->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($b->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $b->createdBy?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($b->created_at)->format('d M Y, H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="6" class="px-4 py-2.5 text-xs font-semibold text-gray-600 text-right">Period Total Value:</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                            {{ number_format($stats['total_value'], 0) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
    @endif
</x-admin-layout>
