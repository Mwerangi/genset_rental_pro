<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Stock Movements</h1>
        <p class="text-sm text-gray-500 mt-0.5">All inventory inflows, outflows, and adjustments</p>
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
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Types</option>
                @foreach(['in','out','adjustment','transfer'] as $t)
                <option value="{{ $t }}" @selected($type === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Item</label>
            <select name="item_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[180px]">
                <option value="">All Items</option>
                @foreach($itemsList as $item)
                <option value="{{ $item->id }}" @selected($itemId == $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Item name or reference…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm text-center">
            <p class="text-xs text-green-600">Total In</p>
            <p class="text-xl font-bold text-green-700 mt-1">{{ number_format($totals['in'] ?? 0) }} units</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm text-center">
            <p class="text-xs text-red-600">Total Out</p>
            <p class="text-xl font-bold text-red-700 mt-1">{{ number_format($totals['out'] ?? 0) }} units</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm text-center">
            <p class="text-xs text-blue-600">Adjustments</p>
            <p class="text-xl font-bold text-blue-700 mt-1">{{ number_format($totals['adjustment'] ?? 0) }} units</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $movements->total() }} movements</span>
            <a href="{{ route('admin.reports.inventory.movements.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Item</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Type</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Quantity</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Unit Cost</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Reference</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $typeColors = [
                            'in' => 'bg-green-100 text-green-700',
                            'out' => 'bg-red-100 text-red-700',
                            'adjustment' => 'bg-blue-100 text-blue-700',
                            'transfer' => 'bg-purple-100 text-purple-700',
                        ];
                    @endphp
                    @forelse($movements as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($m->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $m->item?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$m->type] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($m->type) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium {{ in_array($m->type, ['in','adjustment']) ? 'text-green-700' : 'text-red-600' }}">
                            {{ in_array($m->type, ['in','adjustment']) ? '+' : '-' }}{{ number_format(abs($m->quantity)) }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $m->unit_cost ? number_format($m->unit_cost) : '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-500 font-mono">{{ $m->reference ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-400 max-w-xs truncate">{{ $m->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">No stock movements found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $movements->links() }}</div>
    </div>
</x-admin-layout>
