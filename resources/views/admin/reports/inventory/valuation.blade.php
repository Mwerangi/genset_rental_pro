<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory Valuation</h1>
        <p class="text-sm text-gray-500 mt-0.5">Total stock value at current unit costs</p>
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
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Item name or SKU…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Stock Value</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['total_value'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Units</p>
            <p class="text-xl font-bold text-gray-700 mt-1">{{ number_format($totals['total_units'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Active Categories</p>
            <p class="text-xl font-bold text-gray-700 mt-1">{{ count($byCategory) }}</p>
        </div>
    </div>

    @if(!empty($byCategory))
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Value by Category</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($byCategory as $cat)
            @php $pct = ($totals['total_value'] ?? 0) > 0 ? round($cat['total_value'] / $totals['total_value'] * 100, 1) : 0; @endphp
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">{{ $cat['name'] }}</span>
                        <span class="text-gray-600">{{ $pct }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $cat['item_count'] }} items · {{ number_format($cat['total_units']) }} units · {{ number_format($cat['total_value']) }} TZS</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $items->total() }} items</span>
            <a href="{{ route('admin.reports.inventory.valuation.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">SKU</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Item</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Category</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Unit</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Qty on Hand</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Unit Cost</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Total Value</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">% of Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $grandTotal = $totals['total_value'] ?? 0; @endphp
                    @forelse($items as $item)
                    @php
                        $val = $item['total_value'];
                        $pct = $grandTotal > 0 ? round($val / $grandTotal * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $item['sku'] ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['name'] }}</td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $item['category'] }}</td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $item['unit'] ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($item['stock']) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($item['unit_cost']) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($val) }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-gray-500">{{ $pct }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No inventory items found.</td></tr>
                    @endforelse
                </tbody>
                @if($items->total() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ number_format($totals['total_units'] ?? 0) }}</td>
                        <td></td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($totals['total_value'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
</x-admin-layout>
