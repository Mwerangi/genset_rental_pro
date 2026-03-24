<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Pipeline</h1>
            <p class="text-sm text-gray-500 mt-0.5">Live open quotations — draft, sent and viewed</p>
        </div>
        <a href="{{ route('admin.quotations.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">+ New Quotation</a>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search quotation number or client…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[240px]">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Search</button>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Open</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_count'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Tsh {{ number_format($stats['total_value'], 0) }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Draft</p>
            <p class="text-3xl font-bold text-gray-700 mt-1">{{ $stats['draft_count'] }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Sent</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['sent_count'] }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Viewed</p>
            <p class="text-3xl font-bold text-yellow-900 mt-1">{{ $stats['viewed_count'] }}</p>
        </div>
    </div>

    @if($stats['expiring_soon'] > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 mb-5 flex items-center gap-2 text-sm text-orange-800">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <strong>{{ $stats['expiring_soon'] }}</strong> quotation{{ $stats['expiring_soon'] !== 1 ? 's' : '' }} expiring within 7 days &nbsp;·&nbsp; {{ $stats['expired'] }} already expired.
    </div>
    @endif

    @if($quotations->total() === 0)
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500">No open quotations in the pipeline.</p>
        </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $quotations->total() }} quotations</span>
            <a href="{{ route('admin.reports.sales.pipeline.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Quotation</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Value (TZS)</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Valid Until</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Created</th>
                        <th class="px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($quotations as $q)
                    @php
                        $isExpired = $q->valid_until && \Carbon\Carbon::parse($q->valid_until)->lt(now());
                        $isExpiring = $q->valid_until && !$isExpired && \Carbon\Carbon::parse($q->valid_until)->lte(now()->addDays(7));
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isExpired ? 'opacity-60' : '' }}">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.quotations.show', $q->id) }}" class="font-mono text-xs font-medium text-red-600 hover:underline">{{ $q->quotation_number }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700">{{ $q->client?->company_name ?: $q->client?->full_name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($q->total_amount * ($q->exchange_rate_to_tzs ?? 1), 0) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $q->status==='draft' ? 'bg-gray-100 text-gray-600' : ($q->status==='sent' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($q->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs {{ $isExpired ? 'text-red-500 font-semibold' : ($isExpiring ? 'text-orange-600 font-semibold' : 'text-gray-500') }}">
                            {{ $q->valid_until ? \Carbon\Carbon::parse($q->valid_until)->format('d M Y') : '—' }}
                            @if($isExpired) <span class="block text-red-400">Expired</span> @elseif($isExpiring) <span class="block text-orange-400">Expiring soon</span> @endif
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs text-gray-400">{{ $q->created_at->format('d M Y') }}</td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.quotations.show', $q->id) }}" class="text-gray-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $quotations->links() }}</div>
    </div>
    @endif
</x-admin-layout>
