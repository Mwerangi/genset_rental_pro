<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Revenue by Client</h1>
            <p class="text-sm text-gray-500 mt-0.5">Invoiced amounts, collections and outstanding balances per client</p>
        </div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <label class="text-sm font-medium text-gray-700">Sort:</label>
        <select name="sort" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="invoiced_desc" @selected($sort==='invoiced_desc')>Highest Invoiced</option>
            <option value="collected_desc" @selected($sort==='collected_desc')>Highest Collected</option>
            <option value="outstanding_desc" @selected($sort==='outstanding_desc')>Highest Outstanding</option>
            <option value="name" @selected($sort==='name')>Client Name</option>
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search client name or email…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[200px]">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Total Invoiced</p>
            <p class="text-2xl font-bold text-indigo-900 mt-1">Tsh {{ number_format($totals['invoiced'], 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Total Collected</p>
            <p class="text-2xl font-bold text-green-900 mt-1">Tsh {{ number_format($totals['collected'], 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Total Outstanding</p>
            <p class="text-2xl font-bold text-red-900 mt-1">Tsh {{ number_format($totals['outstanding'], 0) }}</p>
        </div>
    </div>

    @if($clients->total() === 0)
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500">No invoices found for the selected period.</p>
        </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <p class="font-semibold text-gray-800">Client Revenue</p>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500">{{ $clients->total() }} clients</span>
                <a href="{{ route('admin.reports.sales.revenue-by-client.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">#</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Invoices</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-indigo-600">Invoiced (TZS)</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-green-700">Collected</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-red-600">Outstanding</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Collection %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($clients as $i => $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400 text-xs">{{ ($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.clients.show', $c['id']) }}" class="font-medium text-gray-900 hover:text-red-600">{{ $c['name'] }}</a>
                            @if($c['email'])<p class="text-xs text-gray-400">{{ $c['email'] }}</p>@endif
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ $c['invoice_count'] }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($c['invoiced'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ number_format($c['collected'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right {{ $c['outstanding'] > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ number_format($c['outstanding'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">
                            @php $pct = $c['invoiced'] > 0 ? round($c['collected'] / $c['invoiced'] * 100) : 0; @endphp
                            <span class="{{ $pct >= 100 ? 'text-green-700 font-semibold' : ($pct >= 75 ? 'text-yellow-700' : 'text-red-600') }}">{{ $pct }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-2.5 font-bold text-gray-700 text-xs uppercase">Totals</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($totals['invoiced'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-green-700">{{ number_format($totals['collected'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-red-600">{{ number_format($totals['outstanding'], 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">
                            @php $totalPct = $totals['invoiced'] > 0 ? round($totals['collected'] / $totals['invoiced'] * 100) : 0; @endphp
                            {{ $totalPct }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $clients->links() }}</div>
    </div>
    @endif
</x-admin-layout>
