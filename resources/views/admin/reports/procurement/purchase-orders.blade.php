<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Purchase Order Summary</h1>
        <p class="text-sm text-gray-500 mt-0.5">Overview of all purchase orders — committed spend vs. received vs. paid</p>
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
                @foreach(['draft','sent','partial','received','paid','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="PO # or supplier…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    @php
        $statusColors = [
            'draft'      => 'bg-gray-100 text-gray-600',
            'sent'       => 'bg-blue-100 text-blue-700',
            'partial'    => 'bg-yellow-100 text-yellow-700',
            'received'   => 'bg-green-100 text-green-700',
            'paid'       => 'bg-emerald-100 text-emerald-700',
            'cancelled'  => 'bg-red-100 text-red-500',
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        @foreach($byStatus as $key => $stat)
        <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$key] ?? '' }} mb-1">{{ ucfirst($key) }}</span>
            <p class="text-lg font-bold text-gray-900">{{ $stat['count'] }}</p>
            <p class="text-xs text-gray-400">{{ number_format($stat['committed']) }} TZS</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Committed</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['committed'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-green-700 mt-1">{{ number_format($totals['paid'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Outstanding Balance</p>
            <p class="text-xl font-bold text-orange-600 mt-1">{{ number_format($totals['balance'] ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $pos->total() }} purchase orders</span>
            <a href="{{ route('admin.reports.procurement.purchase-orders.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">PO Number</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Supplier</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Committed</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Received</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Paid</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pos as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.purchase-orders.show', $po['id']) }}" class="font-medium text-blue-600 hover:underline">{{ $po['po_number'] }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700">{{ $po['supplier_name'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ $po['ordered_at'] }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$po['status']] ?? '' }}">{{ ucfirst($po['status']) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($po['committed']) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($po['received'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ number_format($po['paid']) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $po['balance'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ number_format($po['balance']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No purchase orders found.</td></tr>
                    @endforelse
                </tbody>
                @if($pos->total() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ number_format($totals['committed'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-600">{{ number_format($totals['received'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-green-700">{{ number_format($totals['paid'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-orange-600">{{ number_format($totals['balance'] ?? 0) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $pos->links() }}</div>
    </div>
</x-admin-layout>
