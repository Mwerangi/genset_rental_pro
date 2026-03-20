<x-admin-layout>
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.purchase-orders.index') }}" class="hover:text-red-600">Purchase Orders</a>
        <span>/</span><span>New</span>
    </div>

    <div class="max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">New Purchase Order</h1>

        @if($errors->any())
        <div class="mb-5 border rounded-xl p-4 text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.purchase-orders.store') }}" x-data="poForm()">
            @csrf

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Supplier</label>
                        <select name="supplier_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— No supplier —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Expected Delivery Date</label>
                        <input type="date" name="expected_at" value="{{ old('expected_at') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes') }}</textarea>
                </div>

                {{-- Line Items --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-700">Order Items <span class="text-red-500">*</span></h3>
                        <button type="button" @click="addLine()" class="text-sm text-red-600 hover:underline">+ Add item</button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(line, i) in lines" :key="i">
                            <div class="flex flex-wrap gap-3 items-start border border-gray-200 rounded-lg p-3">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs text-gray-500 mb-0.5">Item *</label>
                                    <select :name="'items['+i+'][inventory_item_id]'" x-model="line.item_id" @change="onItemChange(i)" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <option value="">— Select item —</option>
                                        @foreach($items as $invItem)
                                        <option value="{{ $invItem->id }}" data-cost="{{ $invItem->unit_cost }}" data-unit="{{ $invItem->unit_label }}">{{ $invItem->name }} ({{ $invItem->sku }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500 mb-0.5">Qty *</label>
                                    <input type="number" :name="'items['+i+'][quantity_ordered]'" x-model="line.qty" required min="0.001" step="0.001" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500 mb-0.5">Unit Cost (Tsh) *</label>
                                    <input type="number" :name="'items['+i+'][unit_cost]'" x-model="line.cost" required min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div class="flex-1 min-w-[120px]">
                                    <label class="block text-xs text-gray-500 mb-0.5">Notes</label>
                                    <input type="text" :name="'items['+i+'][notes]'" x-model="line.notes" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div class="flex items-end pb-1">
                                    <button type="button" @click="removeLine(i)" class="text-red-400 hover:text-red-600 text-xl leading-none">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3 text-right text-sm">
                        Total: <strong x-text="'Tsh ' + totalValue()"></strong>
                    </div>
                </div>

            </div>

            <div class="mt-5 flex gap-3 justify-end">
                <a href="{{ route('admin.purchase-orders.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Discard</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Create PO</button>
            </div>
        </form>
    </div>

    <script>
    function poForm() {
        return {
            lines: [{ item_id: '', qty: 1, cost: 0, notes: '' }],
            addLine() { this.lines.push({ item_id: '', qty: 1, cost: 0, notes: '' }); },
            removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); },
            onItemChange(i) {
                const sel = document.querySelectorAll('[name="items['+i+'][inventory_item_id]"]')[0]
                    || document.querySelector('select[name*="inventory_item_id"]:nth-of-type('+i+')');
                const selected = event.target.options[event.target.selectedIndex];
                if (selected) {
                    const cost = selected.getAttribute('data-cost');
                    if (cost) this.lines[i].cost = parseFloat(cost);
                }
            },
            totalValue() {
                const total = this.lines.reduce((sum, l) => sum + (parseFloat(l.qty)||0) * (parseFloat(l.cost)||0), 0);
                return total.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
    </script>
</x-admin-layout>
