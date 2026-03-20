<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.purchase-orders.index') }}" class="hover:text-red-600">Purchase Orders</a>
                <span>/</span>
                <span class="font-mono">{{ $purchaseOrder->po_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $purchaseOrder->po_number }}</h1>
            <div class="mt-2">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $purchaseOrder->status_style }}">{{ $purchaseOrder->status_label }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($purchaseOrder->status === 'draft')
                <form method="POST" action="{{ route('admin.purchase-orders.send', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1e40af;">Mark Sent to Supplier</button>
                </form>
            @endif
            @if(in_array($purchaseOrder->status, ['sent','partial']))
                <button onclick="document.getElementById('receiveModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#166534;">Receive Stock</button>
            @endif
            @if(!in_array($purchaseOrder->status, ['received','cancelled']))
                <form method="POST" action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this PO?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold border border-red-300 text-red-600 hover:bg-red-50">Cancel PO</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- Line Items --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Order Items</h2>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Item</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Ordered</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Received</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Pending</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Unit Cost</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($purchaseOrder->items as $line)
                        @php $pending = $line->pending_qty; @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.inventory.items.show', $line->inventoryItem) }}" class="font-medium text-gray-800 hover:text-red-600">{{ $line->inventoryItem->name }}</a>
                                <p class="text-xs text-gray-400 font-mono">{{ $line->inventoryItem->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($line->quantity_ordered+0, 2) }} {{ $line->inventoryItem->unit_label }}</td>
                            <td class="px-4 py-3 text-right" style="{{ $line->quantity_received > 0 ? 'color:#166534;font-weight:600;' : 'color:#9ca3af;' }}">
                                {{ number_format($line->quantity_received+0, 2) }} {{ $line->inventoryItem->unit_label }}
                            </td>
                            <td class="px-4 py-3 text-right" style="{{ $pending > 0 ? 'color:#b45309;font-weight:600;' : 'color:#166534;' }}">{{ number_format($pending, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">Tsh {{ number_format($line->unit_cost, 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800">Tsh {{ number_format($line->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-sm font-semibold text-right text-gray-600">Order Total</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">Tsh {{ number_format($purchaseOrder->total_value, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500 text-xs">Supplier</dt><dd class="font-medium text-gray-800">{{ $purchaseOrder->supplier?->name ?? '—' }}</dd></div>
                    @if($purchaseOrder->supplier?->phone)
                    <div><dt class="text-gray-500 text-xs">Phone</dt><dd class="text-gray-700">{{ $purchaseOrder->supplier->phone }}</dd></div>
                    @endif
                    @if($purchaseOrder->supplier?->email)
                    <div><dt class="text-gray-500 text-xs">Email</dt><dd class="text-gray-700">{{ $purchaseOrder->supplier->email }}</dd></div>
                    @endif
                    @if($purchaseOrder->notes)
                    <div class="pt-2 border-t border-gray-100"><dt class="text-gray-500 text-xs mb-1">Notes</dt><dd class="text-gray-700 text-xs">{{ $purchaseOrder->notes }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Timeline</h2>
                <ul class="space-y-2 text-xs text-gray-500">
                    <li class="flex justify-between"><span>Created</span><span class="font-medium text-gray-700">{{ $purchaseOrder->created_at->format('d M Y H:i') }}</span></li>
                    @if($purchaseOrder->ordered_at)
                    <li class="flex justify-between"><span>Sent</span><span class="font-medium text-gray-700">{{ $purchaseOrder->ordered_at->format('d M Y H:i') }}</span></li>
                    @endif
                    @if($purchaseOrder->expected_at)
                    <li class="flex justify-between"><span>Expected</span><span class="font-medium text-gray-700">{{ $purchaseOrder->expected_at->format('d M Y') }}</span></li>
                    @endif
                    @if($purchaseOrder->received_at)
                    <li class="flex justify-between"><span>Received</span><span class="font-medium" style="color:#166534;">{{ $purchaseOrder->received_at->format('d M Y H:i') }}</span></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Receive Stock Modal --}}
    @if(in_array($purchaseOrder->status, ['sent','partial']))
    <div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 sticky top-0 bg-white">
                <h3 class="text-lg font-bold text-gray-900">Receive Stock</h3>
                <button onclick="document.getElementById('receiveModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}">
                @csrf
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-500 mb-4">Enter the quantity received for each item. Leave at 0 to skip. Inventory will be updated automatically.</p>
                    <div class="space-y-3">
                        @foreach($purchaseOrder->items as $i => $line)
                        <div class="flex items-center gap-4 border border-gray-100 rounded-lg p-3">
                            <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $line->id }}">
                            <div class="flex-1">
                                <p class="font-medium text-sm text-gray-800">{{ $line->inventoryItem->name }}</p>
                                <p class="text-xs text-gray-400">Pending: {{ number_format($line->pending_qty, 2) }} {{ $line->inventoryItem->unit_label }}</p>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs text-gray-500 mb-0.5">Qty Received</label>
                                <input type="number" name="items[{{ $i }}][quantity_received]" value="{{ $line->pending_qty }}" min="0" step="0.001"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#166534;">Confirm Receipt</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-admin-layout>
