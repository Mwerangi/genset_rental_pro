<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.cash-requests.show', $cashRequest) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $cashRequest->request_number }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Cash Request</h1>
        <p class="text-sm text-gray-500 mt-0.5">Only drafts can be edited. Changes replace all existing line items.</p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.cash-requests.update', $cashRequest) }}" id="crForm">
            @csrf
            @method('PUT')

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Description <span class="text-red-500">*</span></label>
                    <input type="text" name="purpose" value="{{ old('purpose', $cashRequest->purpose) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes', $cashRequest->notes) }}</textarea>
                </div>
            </div>

            <!-- Items -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-700">Cost Items</p>
                    <button type="button" id="addItem" class="text-sm text-red-600 hover:text-red-700 font-medium">+ Add Item</button>
                </div>
                <p class="text-xs text-gray-500 mb-3">Each item must be categorised — the category determines which ledger account gets debited when you retire this request.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Item Description <span class="text-red-500">*</span></th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Category <span class="text-red-500">*</span></th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-36">Est. Amount (TZS) <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php
                                $existingItems = old('items') ? collect(old('items'))->map(fn($i) => (object) $i) : $cashRequest->items;
                            @endphp
                            @foreach($existingItems as $idx => $item)
                            <tr class="item-row border-t border-gray-100">
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item->description ?? '' }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
                                </td>
                                <td class="px-3 py-2 min-w-[200px]">
                                    <select name="items[{{ $idx }}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 cat-select" required>
                                        <option value="">— Select category —</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-coa="{{ $cat->account?->code }} - {{ $cat->account?->name }}"
                                            @selected(($item->expense_category_id ?? '') == $cat->id)>
                                            {{ $cat->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-blue-600 mt-0.5 coa-hint"></p>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[{{ $idx }}][estimated_amount]" value="{{ $item->estimated_amount ?? '' }}" step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 amount-input" oninput="updateTotal()" required>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" class="remove-item text-red-400 hover:text-red-600 {{ $loop->first ? 'opacity-30 cursor-not-allowed' : '' }}" {{ $loop->first ? 'disabled' : '' }}>✕</button>
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
                <a href="{{ route('admin.accounting.cash-requests.show', $cashRequest) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>

    <script>
    let itemIndex = {{ $existingItems->count() }};
    const catData = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'coa' => ($c->account ? $c->account->code . ' - ' . $c->account->name : '')]));

    function buildOptions(selectedId = '') {
        return catData.map(c =>
            `<option value="${c.id}" data-coa="${c.coa}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`
        ).join('');
    }

    function wireCoaHints(container) {
        container.querySelectorAll('.cat-select').forEach(sel => {
            function updateHint() {
                const opt = sel.options[sel.selectedIndex];
                const hint = sel.closest('td').querySelector('.coa-hint');
                if (hint) hint.textContent = opt?.dataset.coa ? 'Ledger: ' + opt.dataset.coa : '';
            }
            sel.addEventListener('change', updateHint);
            updateHint();
        });
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value) || 0);
        document.getElementById('totalAmount').textContent = total.toLocaleString('en', { minimumFractionDigits: 0 });
    }

    wireCoaHints(document.getElementById('itemsBody'));
    updateTotal();

    document.getElementById('addItem').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'item-row border-t border-gray-100';
        tr.innerHTML = `
        <td class="px-3 py-2"><input type="text" name="items[${itemIndex}][description]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required></td>
        <td class="px-3 py-2 min-w-[200px]">
            <select name="items[${itemIndex}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm cat-select" required>
                <option value="">— Select category —</option>${buildOptions()}
            </select>
            <p class="text-xs text-blue-600 mt-0.5 coa-hint"></p>
        </td>
        <td class="px-3 py-2"><input type="number" name="items[${itemIndex}][estimated_amount]" step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right amount-input" oninput="updateTotal()" required></td>
        <td class="px-3 py-2 text-center"><button type="button" class="remove-item text-red-400 hover:text-red-600">✕</button></td>`;
        document.getElementById('itemsBody').appendChild(tr);
        wireCoaHints(tr);
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
