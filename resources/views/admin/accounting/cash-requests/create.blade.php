<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.cash-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Cash Requests</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Cash Request</h1>
    </div>

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.cash-requests.store') }}" id="crForm">
            @csrf
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Description <span class="text-red-500">*</span></label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Items -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-700">Cost Items</p>
                    <button type="button" id="addItem" class="text-sm text-red-600 hover:text-red-700 font-medium">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Item Description <span class="text-red-500">*</span></th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Category</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-36">Est. Amount <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php $items = old('items', [['description'=>'','expense_category_id'=>'','estimated_amount'=>'']]); @endphp
                            @foreach($items as $i=>$item)
                            <tr class="item-row border-t border-gray-100">
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $i }}][description]" value="{{ $item['description'] ?? '' }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
                                </td>
                                <td class="px-3 py-2">
                                    <select name="items[{{ $i }}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                                        <option value="">—</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(($item['expense_category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[{{ $i }}][estimated_amount]" value="{{ $item['estimated_amount'] ?? '' }}" step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 amount-input" oninput="updateTotal()" required>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" class="remove-item text-red-400 hover:text-red-600" @if($i === 0) disabled @endif>✕</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Total Requested</td>
                                <td class="px-3 py-2 text-right font-bold font-mono text-gray-900" id="totalAmount">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.accounting.cash-requests.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Draft</button>
            </div>
        </form>
    </div>

    <script>
    let itemIndex = {{ count($items) }};
    const catOptions = `{!! collect($categories)->map(fn($c) => '<option value="'.$c->id.'">'.htmlspecialchars($c->name, ENT_QUOTES).'</option>')->implode('') !!}`;

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value) || 0);
        document.getElementById('totalAmount').textContent = total.toLocaleString('en', {minimumFractionDigits:0});
    }

    document.getElementById('addItem').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'item-row border-t border-gray-100';
        tr.innerHTML = `<td class="px-3 py-2"><input type="text" name="items[${itemIndex}][description]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required></td>
        <td class="px-3 py-2"><select name="items[${itemIndex}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm"><option value="">—</option>${catOptions}</select></td>
        <td class="px-3 py-2"><input type="number" name="items[${itemIndex}][estimated_amount]" step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right amount-input" oninput="updateTotal()" required></td>
        <td class="px-3 py-2 text-center"><button type="button" class="remove-item text-red-400 hover:text-red-600">✕</button></td>`;
        document.getElementById('itemsBody').appendChild(tr);
        tr.querySelector('.remove-item').addEventListener('click', () => { tr.remove(); updateTotal(); });
        itemIndex++;
    });

    document.getElementById('itemsBody').addEventListener('click', e => {
        if (e.target.classList.contains('remove-item') && !e.target.disabled) {
            e.target.closest('tr').remove(); updateTotal();
        }
    });
    </script>
</x-admin-layout>
