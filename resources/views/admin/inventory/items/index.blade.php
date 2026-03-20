<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Inventory Items</h1>
            <p class="text-gray-500 mt-1">Parts, consumables and materials stock</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventory.categories.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Categories</a>
            <a href="{{ route('admin.inventory.items.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Item
            </a>
        </div>
    </div>

    @if($lowStockCount > 0)
    <div class="mb-5 border rounded-xl p-4 text-sm flex items-center gap-3" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <span><strong>{{ $lowStockCount }} item{{ $lowStockCount !== 1 ? 's' : '' }}</strong> {{ $lowStockCount !== 1 ? 'are' : 'is' }} at or below minimum stock level.
            <a href="{{ route('admin.inventory.items.index', ['stock' => 'low']) }}" class="underline ml-1">View low stock</a>
        </span>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="stock" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock Only</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request()->hasAny(['search','category_id','stock']))
                <a href="{{ route('admin.inventory.items.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($items->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <p class="text-sm">No items found.</p>
                <a href="{{ route('admin.inventory.items.create') }}" class="mt-2 inline-block text-sm text-red-600 hover:underline">Add first item</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Min Level</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Unit Cost</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                    @php $lowStock = $item->isLowStock(); @endphp
                    <tr class="hover:bg-gray-50 {{ $lowStock ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $item->sku }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-800">
                            {{ $item->name }}
                            @if($lowStock) <span class="ml-1 text-xs" style="color:#dc2626;">⚠ Low</span> @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $item->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-semibold {{ $lowStock ? '' : 'text-gray-800' }}" style="{{ $lowStock ? 'color:#dc2626;' : '' }}">
                            {{ number_format($item->current_stock, 2) + 0 }} {{ $item->unit_label }}
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $item->min_stock_level + 0 }} {{ $item->unit_label }}</td>
                        <td class="px-5 py-3 text-gray-700">Tsh {{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.inventory.items.show', $item) }}" class="text-sm text-red-600 hover:underline font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($items->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $items->links() }}</div>
            @endif
        @endif
    </div>
</x-admin-layout>
