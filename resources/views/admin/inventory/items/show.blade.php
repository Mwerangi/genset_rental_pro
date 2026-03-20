<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.inventory.items.index') }}" class="hover:text-red-600">Inventory</a>
                <span>/</span>
                <span class="font-mono">{{ $item->sku }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-sm text-gray-500">{{ $item->category?->name ?? 'No category' }}</span>
                @if($item->isLowStock())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#fee2e2;color:#991b1b;">⚠ Low Stock</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('adjustModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Adjust Stock</button>
            <a href="{{ route('admin.inventory.items.edit', $item) }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Stock info cards --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">Current Stock</p>
                    <p class="text-3xl font-bold {{ $item->isLowStock() ? '' : 'text-gray-900' }}" style="{{ $item->isLowStock() ? 'color:#dc2626;' : '' }}">
                        {{ number_format($item->current_stock + 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->unit_label }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">Min Level</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($item->min_stock_level + 0, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->unit_label }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm text-center">
                    <p class="text-xs text-gray-500 mb-1">Unit Cost</p>
                    <p class="text-xl font-bold text-gray-900">Tsh {{ number_format($item->unit_cost, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">per {{ $item->unit_label }}</p>
                </div>
            </div>

            {{-- Stock movements --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Movement History</h2>
                </div>
                @if($movements->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">No movements yet</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Unit Cost</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Reference</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Notes</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($movements as $mv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $mv->type_style }}">{{ $mv->type_label }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-semibold {{ $mv->type === 'out' ? '' : 'text-green-700' }}" style="{{ $mv->type === 'out' ? 'color:#dc2626;' : '' }}">
                                    {{ $mv->type === 'out' ? '-' : '+' }}{{ number_format($mv->quantity + 0, 2) }} {{ $item->unit_label }}
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 text-xs">
                                    {{ $mv->unit_cost > 0 ? 'Tsh '.number_format($mv->unit_cost, 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">
                                    @if($mv->maintenanceRecord)
                                        <a href="{{ route('admin.maintenance.show', $mv->maintenanceRecord) }}" class="text-red-600 hover:underline">{{ $mv->maintenanceRecord->maintenance_number }}</a>
                                    @elseif($mv->purchaseOrder)
                                        <a href="{{ route('admin.purchase-orders.show', $mv->purchaseOrder) }}" class="text-red-600 hover:underline">{{ $mv->purchaseOrder->po_number }}</a>
                                    @else
                                        {{ $mv->reference ?? 'Manual' }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 text-xs max-w-[200px]"><span class="truncate block">{{ $mv->notes ?? '—' }}</span></td>
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ $mv->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($movements->hasPages())
                        <div class="px-5 py-4 border-t border-gray-100">{{ $movements->links() }}</div>
                    @endif
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">SKU</dt><dd class="font-mono text-gray-800">{{ $item->sku }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Unit</dt><dd class="text-gray-800">{{ ucfirst($item->unit) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd class="text-gray-800">{{ $item->category?->name ?? '—' }}</dd></div>
                    @if($item->notes)
                    <div class="pt-2 border-t border-gray-100"><dt class="text-gray-500 text-xs mb-1">Notes</dt><dd class="text-gray-700 text-xs">{{ $item->notes }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

    </div>

    {{-- Adjust Stock Modal --}}
    <div id="adjustModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">Adjust Stock</h3>
                <button onclick="document.getElementById('adjustModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.items.adjust', $item) }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="in">Stock In (add)</option>
                            <option value="out">Stock Out (remove)</option>
                            <option value="adjustment">Manual Adjustment (set)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity ({{ $item->unit_label }}) <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" required min="0.001" step="0.001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (Tsh — optional)</label>
                        <input type="number" name="unit_cost" min="0" step="0.01" value="{{ $item->unit_cost }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Reason for adjustment...">
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
