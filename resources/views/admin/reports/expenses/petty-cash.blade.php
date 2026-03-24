<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Petty Cash / Imprest Summary</h1>
        <p class="text-sm text-gray-500 mt-0.5">Cash advance requests and retirement status</p>
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
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach(['pending','approved','issued','retired','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Purpose or ref #…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    @php
        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-blue-100 text-blue-700',
            'issued' => 'bg-purple-100 text-purple-700',
            'retired' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-gray-100 text-gray-500',
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
        @foreach(['pending','approved','issued','retired','cancelled'] as $s)
        <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm text-center">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$s] ?? '' }} mb-1">{{ ucfirst($s) }}</span>
            <p class="text-lg font-bold text-gray-900">{{ $stats['by_status'][$s]['count'] ?? 0 }}</p>
            <p class="text-xs text-gray-400">{{ number_format($stats['by_status'][$s]['total'] ?? 0) }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Requested</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_estimated'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Actual Spend</p>
            <p class="text-xl font-bold text-gray-700 mt-1">{{ number_format($stats['total_actual'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Variance (Est. vs Actual)</p>
            @php $variance = ($stats['total_estimated'] ?? 0) - ($stats['total_actual'] ?? 0); @endphp
            <p class="text-xl font-bold {{ $variance >= 0 ? 'text-green-700' : 'text-red-600' }} mt-1">{{ number_format(abs($variance)) }} {{ $variance >= 0 ? '↑ under' : '↓ over' }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $requests->total() }} requests</span>
            <a href="{{ route('admin.reports.expenses.petty-cash.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Ref #</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Requested by</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Purpose</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Requested</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Actual</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-700">{{ $req->reference_number ?? 'CR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-2.5 text-gray-700">{{ $req->requestedBy?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600 max-w-xs truncate">{{ $req->purpose }}</td>
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($req->estimated_amount ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ $req->actual_amount ? number_format($req->actual_amount) : '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($req->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">No cash requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $requests->links() }}</div>
    </div>
</x-admin-layout>
