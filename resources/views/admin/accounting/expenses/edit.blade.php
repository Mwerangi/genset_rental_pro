<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.expenses.show', $expense) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $expense->expense_number }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Expense</h1>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.expenses.update', $expense) }}" enctype="multipart/form-data"
              x-data="{
                  zeroRated: {{ old('is_zero_rated', $expense->is_zero_rated) ? 'true' : 'false' }},
                  amount: '{{ old('amount', $expense->amount) }}',
                  selectedCat: '{{ old('expense_category_id', $expense->expense_category_id) }}',
                  cats: {{ $categories->map(fn($c) => ['id' => (string)$c->id, 'hasAccount' => (bool)$c->account_id, 'isZeroRated' => (bool)$c->is_zero_rated])->toJson() }},
                  get vatAmount() { return this.zeroRated ? 0 : Math.round((parseFloat(this.amount) || 0) * 0.18 * 100) / 100; },
                  get vatDisplay() { return this.vatAmount.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}); },
                  get total() {
                      return ((parseFloat(this.amount) || 0) + this.vatAmount).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                  }
              }"
              x-init="$watch('selectedCat', val => {
                  const cat = cats.find(c => c.id === val);
                  if (cat && cat.isZeroRated) { zeroRated = true; }
              })">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">

                {{-- Description --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Category --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="expense_category_id" x-model="selectedCat"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('expense_category_id', $expense->expense_category_id) == $cat->id)>
                            {{ $cat->name }}{{ $cat->account ? ' — ' . $cat->account->code . ' ' . $cat->account->name : ' ⚠ No ledger account' }}
                        </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    <template x-if="selectedCat && cats.find(c => c.id === selectedCat && !c.hasAccount)">
                        <p class="mt-1 text-xs text-amber-600 font-medium">⚠ This category has no ledger account — posting will fail. <a href="{{ route('admin.accounting.expense-categories.index') }}" class="underline">Link one in Categories</a>.</p>
                    </template>
                    <template x-if="selectedCat && cats.find(c => c.id === selectedCat && c.isZeroRated)">
                        <p class="mt-1 text-xs text-green-600 font-medium">✓ This category is VAT-exempt — zero-rated applied automatically.</p>
                    </template>
                </div>

                {{-- COA Account (searchable typeahead) --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account (COA) <span class="text-red-500">*</span></label>
                    @php
                        $jsAccounts     = $accounts->map(fn($a) => ['id' => (string)$a->id, 'code' => $a->code, 'name' => $a->name, 'label' => $a->code.' — '.$a->name]);
                        $currentAcctId  = old('account_id', $expense->account_id);
                        $currentAcct    = $accounts->firstWhere('id', $currentAcctId);
                        $currentAcctLbl = $currentAcct ? $currentAcct->code . ' — ' . $currentAcct->name : '';
                    @endphp
                    <div class="relative" id="acctWrap">
                        <input type="hidden" name="account_id" id="acctId" value="{{ $currentAcctId }}">
                        <input type="text" id="acctSearch"
                               value="{{ $currentAcctLbl }}"
                               placeholder="Search by code or name…"
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 pr-7">
                        <button type="button" id="acctClear"
                                class="{{ $currentAcctId ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-sm">&times;</button>
                    </div>
                    @error('account_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Supplier --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier / Vendor</label>
                    @php
                        $jsSuppliers      = $suppliers->map(fn($s) => ['id' => (string)$s->id, 'name' => $s->name]);
                        $currentSuppId    = old('supplier_id', $expense->supplier_id);
                        $currentSupplier  = $suppliers->firstWhere('id', $currentSuppId);
                        $currentSuppLbl   = $currentSupplier?->name ?? '';
                    @endphp
                    <div class="relative" id="supplierWrap">
                        <input type="hidden" name="supplier_id" id="supplierId" value="{{ $currentSuppId }}">
                        <input type="text" id="supplierSearch"
                               value="{{ $currentSuppLbl }}"
                               placeholder="Search supplier… (optional)"
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 pr-7">
                        <button type="button" id="supplierClear"
                                class="{{ $currentSuppId ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-sm">&times;</button>
                    </div>
                    @error('supplier_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Pay From Account --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay From Account <span class="text-red-500">*</span></label>
                    <select name="bank_account_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="">Select account</option>
                        @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" @selected(old('bank_account_id', $expense->bank_account_id) == $ba->id)>
                            {{ $ba->name }} ({{ $ba->currency }} {{ number_format($ba->current_balance, 0) }})
                        </option>
                        @endforeach
                    </select>
                    @error('bank_account_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (excl. VAT) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" x-model="amount"
                           value="{{ old('amount', $expense->amount) }}"
                           step="0.01" min="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Zero-rated toggle --}}
                <div class="col-span-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_zero_rated" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-red-600"
                               x-model="zeroRated">
                        <span class="text-sm font-medium text-gray-700">Zero-rated expense (no VAT applicable)</span>
                    </label>
                </div>

                {{-- VAT (auto-calculated at 18%) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT (18% auto-calculated)</label>
                    <div x-show="!zeroRated" x-cloak
                         class="w-full border border-blue-200 bg-blue-50 rounded-lg px-3 py-2 text-sm text-blue-800 font-semibold font-mono"
                         x-text="vatDisplay"></div>
                    <div x-show="zeroRated" x-cloak
                         class="w-full border border-green-200 bg-green-50 rounded-lg px-3 py-2 text-sm text-green-700 font-semibold">
                        Zero-rated — VAT: 0.00
                    </div>
                    <input type="hidden" name="vat_amount" :value="vatAmount">
                </div>

                {{-- Live total --}}
                <div class="col-span-2">
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <span class="text-sm font-semibold text-gray-700">Total (incl. VAT)</span>
                        <span class="text-lg font-bold text-gray-900 font-mono" x-text="'Tsh ' + total"></span>
                    </div>
                </div>

                {{-- Expense Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date"
                           value="{{ old('expense_date', $expense->expense_date?->toDateString()) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>

                {{-- Reference --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Receipt #</label>
                    <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                {{-- Attachment --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Attachment (PDF/Image)
                        @if($expense->attachment)
                        — <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="text-blue-600 hover:underline text-xs">View current</a>
                        @endif
                    </label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    @if($expense->attachment)
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep the existing attachment.</p>
                    @endif
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.expenses.show', $expense) }}"
                   class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>

{{-- Floating COA typeahead dropdown --}}
<div id="acctDrop"
     style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 4px 12px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;min-width:280px;font-size:.875rem">
</div>

<script>
const ACCOUNTS = @json($jsAccounts);
const acctDrop = document.getElementById('acctDrop');
let acctDropTarget = null;

function openAccountDrop(wrap) {
    acctDropTarget = wrap;
    const rect = wrap.querySelector('#acctSearch').getBoundingClientRect();
    acctDrop.style.left  = rect.left + window.scrollX + 'px';
    acctDrop.style.top   = rect.bottom + window.scrollY + 4 + 'px';
    acctDrop.style.width = rect.width + 'px';
    renderAccountDrop(wrap);
    acctDrop.style.display = 'block';
}

function renderAccountDrop(wrap) {
    const q = (wrap.querySelector('#acctSearch').value || '').toLowerCase().trim();
    const rows = q.length < 1
        ? ACCOUNTS.slice(0, 40)
        : ACCOUNTS.filter(a => a.code.toLowerCase().includes(q) || a.name.toLowerCase().includes(q)).slice(0, 40);
    if (!rows.length) {
        acctDrop.innerHTML = '<div style="padding:10px 14px;color:#9ca3af">No accounts found</div>';
        return;
    }
    acctDrop.innerHTML = rows.map(a =>
        `<div class="acct-opt" data-id="${a.id}" data-label="${a.label.replace(/"/g,'&quot;')}"
              style="padding:8px 14px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
              onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">${a.code} — ${a.name}</div>`
    ).join('');
}

function setAccount(wrap, id, label) {
    wrap.querySelector('#acctId').value = id;
    wrap.querySelector('#acctSearch').value = label;
    const clr = wrap.querySelector('#acctClear');
    if (clr) clr.classList.toggle('hidden', !id);
    acctDrop.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('acctWrap');
    const srch = wrap.querySelector('#acctSearch');
    const clr  = wrap.querySelector('#acctClear');

    const defaultId = '{{ $currentAcctId }}';
    if (defaultId) {
        const found = ACCOUNTS.find(a => a.id === String(defaultId));
        if (found) setAccount(wrap, found.id, found.label);
    }

    srch.addEventListener('focus', () => openAccountDrop(wrap));
    srch.addEventListener('input', () => {
        renderAccountDrop(wrap);
        acctDrop.style.display = 'block';
        acctDropTarget = wrap;
    });
    srch.addEventListener('blur', () => setTimeout(() => {
        if (acctDropTarget === wrap) acctDrop.style.display = 'none';
    }, 160));

    acctDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('.acct-opt');
        if (!opt || acctDropTarget !== wrap) return;
        setAccount(wrap, opt.dataset.id, opt.dataset.label);
    });

    if (clr) clr.addEventListener('click', () => setAccount(wrap, '', ''));

    // Supplier typeahead
    const SUPPLIERS = @json($jsSuppliers);
    const suppDrop  = (() => {
        const d = document.createElement('div');
        d.style.cssText = 'display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 4px 12px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;min-width:200px;font-size:.875rem';
        document.body.appendChild(d);
        return d;
    })();
    const sWrap = document.getElementById('supplierWrap');
    const sSrch = document.getElementById('supplierSearch');
    const sClr  = document.getElementById('supplierClear');
    const sId   = document.getElementById('supplierId');

    function renderSuppDrop(q) {
        const rows = q.length < 1
            ? SUPPLIERS.slice(0, 40)
            : SUPPLIERS.filter(s => s.name.toLowerCase().includes(q.toLowerCase())).slice(0, 40);
        if (!rows.length) { suppDrop.innerHTML = '<div style="padding:10px 14px;color:#9ca3af">No suppliers found</div>'; return; }
        suppDrop.innerHTML = rows.map(s =>
            `<div class="supp-opt" data-id="${s.id}" data-name="${s.name.replace(/"/g,'&quot;')}"
                  style="padding:8px 14px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                  onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">${s.name}</div>`
        ).join('');
    }
    function openSuppDrop() {
        const rect = sSrch.getBoundingClientRect();
        suppDrop.style.left  = rect.left + window.scrollX + 'px';
        suppDrop.style.top   = rect.bottom + window.scrollY + 4 + 'px';
        suppDrop.style.width = rect.width + 'px';
        renderSuppDrop(sSrch.value.trim());
        suppDrop.style.display = 'block';
    }
    function setSupplier(id, name) {
        sId.value   = id;
        sSrch.value = name;
        sClr.classList.toggle('hidden', !id);
        suppDrop.style.display = 'none';
    }
    sSrch.addEventListener('focus', openSuppDrop);
    sSrch.addEventListener('input', () => { renderSuppDrop(sSrch.value.trim()); suppDrop.style.display = 'block'; });
    sSrch.addEventListener('blur', () => setTimeout(() => { suppDrop.style.display = 'none'; }, 160));
    suppDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('.supp-opt');
        if (!opt) return;
        setSupplier(opt.dataset.id, opt.dataset.name);
    });
    sClr.addEventListener('click', () => setSupplier('', ''));

    // Pre-populate supplier if editing
    const defaultSuppId = '{{ $currentSuppId }}';
    if (defaultSuppId) {
        const found = SUPPLIERS.find(s => s.id === String(defaultSuppId));
        if (found) setSupplier(found.id, found.name);
    }
});
</script>
</x-admin-layout>
