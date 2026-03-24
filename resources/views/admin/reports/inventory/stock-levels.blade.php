<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Stock Levels</h1>
        <p class="text-sm text-gray-500 mt-0.5">Current inventory quantities and reorder status</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[180px]">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Filter</label>
            <select name="filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="all" @selected($filter === 'all')>All Items</option>
                <option value="low" @selected($filter === 'low')>Low Stock</option>
                <option value="out" @selected($filter === 'out')>Out of Stock</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Item name or SKU…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Items</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $summary['total'] ?? 0 }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-yellow-600">Low Stock</p>
            <p class="text-xl font-bold text-yellow-700 mt-1">{{ $summary['low'] ?? 0 }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-red-600">Out of Stock</p>
            <p class="text-xl font-bold text-red-700 mt-1">{{ $summary['out'] ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Value (TZS)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_value'] ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $items->total() }} items</span>
            <a href="{{ route('admin.reports.inventory.stock-levels.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">SKU</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Item Name</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Category</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Stock</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Min Level</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Unit Cost</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Total Value</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $item)
                    @php
                        $stock = $item['current_stock'] ?? 0;
                        $minStock = $item['min_stock_level'] ?? 0;
                        if ($stock <= 0) {
                            $statusLabel = 'Out of Stock'; $statusClass = 'bg-red-100 text-red-700';
                        } elseif ($minStock > 0 && $stock <= $minStock) {
                            $statusLabel = 'Low Stock'; $statusClass = 'bg-yellow-100 text-yellow-700';
                        } else {
                            $statusLabel = 'OK'; $statusClass = 'bg-green-100 text-green-700';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $item['sku'] ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['name'] }}</td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $item['category'] ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900 font-medium">{{ number_format($stock) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $minStock > 0 ? number_format($minStock) : '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($item['unit_cost'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($item['total_value'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No inventory items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
</x-admin-layout>
