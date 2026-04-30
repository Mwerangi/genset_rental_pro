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

    <div class="max-w-4xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.cash-requests.update', $cashRequest) }}" enctype="multipart/form-data" id="crForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Description <span class="text-red-500">*</span></label>
                    <input type="text" name="purpose" value="{{ old('purpose', $cashRequest->purpose) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $cashRequest->expense_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment <span class="text-xs text-gray-400">(leave blank to keep existing)</span></label>
                    @if($cashRequest->attachment)
                    <p class="text-xs text-green-600 mb-1">✓ File already attached — upload a new one to replace it.</p>
                    @endif
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.heic"
                           class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-red-50 file:text-red-700">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes', $cashRequest->notes) }}</textarea>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Cost Items</p>
                        <p class="text-xs text-gray-500 mt-0.5">VAT (18%) is auto-applied per item. Zero-rated categories (★) have no VAT.</p>
                    </div>
                    <button type="button" id="addItem" class="text-sm text-red-600 hover:text-red-700 font-medium">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600 min-w-[200px]">Description <span class="text-red-500">*</span></th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600 min-w-[180px]">Category <span class="text-red-500">*</span></th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Net Amount <span class="text-red-500">*</span></th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-28">VAT (18%)</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Total</th>
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
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $item->description ?? '' }}"
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required>
                                </td>
                                <td class="px-3 py-2">
                                    <select name="items[{{ $idx }}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm cat-select" required>
                                        <option value="">— Select —</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                                data-zero="{{ $cat->is_zero_rated ? '1' : '0' }}"
                                                data-coa="{{ $cat->account?->code }} - {{ $cat->account?->name }}"
                                                @selected(($item->expense_category_id ?? '') == $cat->id)>
                                            {{ $cat->name }}{{ $cat->is_zero_rated ? ' ★' : '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-blue-600 mt-0.5 coa-hint"></p>
                                    @php
                                        $manualOverride = isset($item->is_zero_rated) && $item->is_zero_rated
                                            && !($categories->firstWhere('id', $item->expense_category_id)?->is_zero_rated ?? false);
                                    @endphp
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer select-none">
                                            <input type="checkbox" name="items[{{ $idx }}][zero_vat_override]" value="1"
                                                   class="zero-override rounded"
                                                   {{ (old("items.{$idx}.zero_vat_override") ?? ($manualOverride ? '1' : '')) ? 'checked' : '' }}>
                                            <span>No VAT</span>
                                        </label>
                                        <input type="text" name="items[{{ $idx }}][vat_justification]"
                                               value="{{ old("items.{$idx}.vat_justification", $item->vat_justification ?? '') }}"
                                               placeholder="Justification for zero VAT…"
                                               class="vat-justification {{ ($manualOverride || old("items.{$idx}.zero_vat_override")) ? '' : 'hidden' }} flex-1 text-xs border border-amber-300 bg-amber-50 rounded px-2 py-0.5 focus:outline-none focus:ring-1 focus:ring-amber-400">
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="items[{{ $idx }}][estimated_amount]" value="{{ $item->estimated_amount ?? '' }}"
                                           step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right amount-input" required>
                                </td>
                                <td class="px-3 py-2 text-right text-xs text-gray-500 vat-cell">—</td>
                                <td class="px-3 py-2 text-right text-xs font-semibold text-gray-800 total-cell">—</td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" class="remove-item text-red-400 hover:text-red-600 {{ $loop->first ? 'opacity-30 cursor-not-allowed' : '' }}" {{ $loop->first ? 'disabled' : '' }}>✕</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200 text-sm font-semibold">
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-right text-gray-600">Subtotal</td>
                                <td class="px-3 py-2 text-right font-mono text-gray-900" id="footNet">0.00</td>
                                <td class="px-3 py-2 text-right font-mono text-gray-500" id="footVat">0.00</td>
                                <td class="px-3 py-2 text-right font-mono text-gray-900" id="footTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p class="text-xs text-gray-400 mt-1">★ = zero-rated / VAT-exempt category</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.accounting.cash-requests.show', $cashRequest) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>

@php
$catJson = $categories->map(fn($c) => [
    'id'        => (string) $c->id,
    'name'      => $c->name . ($c->is_zero_rated ? ' ★' : ''),
    'coa'       => $c->account ? $c->account->code . ' - ' . $c->account->name : '',
    'zeroRated' => (bool) $c->is_zero_rated,
])->values();
@endphp
<script>
let itemIndex = {{ $cashRequest->items->count() }};
const catData = @json($catJson);

function buildOptions(sel = '') {
    return catData.map(c =>
        `<option value="${c.id}" data-zero="${c.zeroRated ? '1' : '0'}" data-coa="${c.coa}" ${c.id === sel ? 'selected' : ''}>${c.name}</option>`
    ).join('');
}

function fmt(n) {
    return n.toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function recalcRow(tr) {
    const sel      = tr.querySelector('.cat-select');
    const amtIn    = tr.querySelector('.amount-input');
    const vatCel   = tr.querySelector('.vat-cell');
    const totCel   = tr.querySelector('.total-cell');
    const override = tr.querySelector('.zero-override');
    const justBox  = tr.querySelector('.vat-justification');
    const opt      = sel ? sel.options[sel.selectedIndex] : null;
    const catZero  = opt?.dataset.zero === '1';
    const manZero  = override?.checked ?? false;
    const zero     = catZero || manZero;
    if (justBox) {
        justBox.classList.toggle('hidden', !manZero);
        if (!manZero) justBox.value = '';
    }
    const net   = parseFloat(amtIn?.value) || 0;
    const vat   = zero ? 0 : Math.round(net * 0.18 * 100) / 100;
    const total = net + vat;
    if (vatCel) vatCel.textContent = net > 0 ? (zero ? 'Exempt' : fmt(vat)) : '—';
    if (totCel) totCel.textContent = net > 0 ? fmt(total) : '—';
    return { net, vat, total };
}

function recalcAll() {
    let sN = 0, sV = 0, sT = 0;
    document.querySelectorAll('#itemsBody .item-row').forEach(tr => {
        const r = recalcRow(tr); sN += r.net; sV += r.vat; sT += r.total;
    });
    document.getElementById('footNet').textContent   = fmt(sN);
    document.getElementById('footVat').textContent   = fmt(sV);
    document.getElementById('footTotal').textContent = fmt(sT);
}

function wireRow(tr) {
    const sel  = tr.querySelector('.cat-select');
    const hint = tr.querySelector('.coa-hint');
    if (sel && hint) {
        function upHint() {
            const opt = sel.options[sel.selectedIndex];
            hint.textContent = opt?.dataset.coa ? 'Ledger: ' + opt.dataset.coa : '';
            recalcAll();
        }
        sel.addEventListener('change', upHint);
        upHint();
    }
    tr.querySelector('.amount-input')?.addEventListener('input', recalcAll);
    tr.querySelector('.zero-override')?.addEventListener('change', recalcAll);
    const rm = tr.querySelector('.remove-item');
    if (rm && !rm.disabled) rm.addEventListener('click', () => { tr.remove(); recalcAll(); });
}

document.querySelectorAll('#itemsBody .item-row').forEach(wireRow);
recalcAll();

document.getElementById('addItem').addEventListener('click', () => {
    const tr = document.createElement('tr');
    tr.className = 'item-row border-t border-gray-100';
    tr.innerHTML = `
        <td class="px-3 py-2"><input type="text" name="items[${itemIndex}][description]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required></td>
        <td class="px-3 py-2">
            <select name="items[${itemIndex}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm cat-select" required>
                <option value="">— Select —</option>${buildOptions()}
            </select>
            <p class="text-xs text-blue-600 mt-0.5 coa-hint"></p>
            <div class="mt-1.5 flex items-center gap-2">
                <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer select-none">
                    <input type="checkbox" name="items[${itemIndex}][zero_vat_override]" value="1" class="zero-override rounded">
                    <span>No VAT</span>
                </label>
                <input type="text" name="items[${itemIndex}][vat_justification]"
                       placeholder="Justification for zero VAT…"
                       class="vat-justification hidden flex-1 text-xs border border-amber-300 bg-amber-50 rounded px-2 py-0.5 focus:outline-none focus:ring-1 focus:ring-amber-400">
            </div>
        </td>
        <td class="px-3 py-2"><input type="number" name="items[${itemIndex}][estimated_amount]" step="0.01" min="0.01" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right amount-input" required></td>
        <td class="px-3 py-2 text-right text-xs text-gray-500 vat-cell">—</td>
        <td class="px-3 py-2 text-right text-xs font-semibold text-gray-800 total-cell">—</td>
        <td class="px-3 py-2 text-center"><button type="button" class="remove-item text-red-400 hover:text-red-600">✕</button></td>`;
    document.getElementById('itemsBody').appendChild(tr);
    wireRow(tr);
    itemIndex++;
});
</script>
</x-admin-layout>
