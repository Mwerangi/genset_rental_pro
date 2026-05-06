<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expenses</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Expense</h1>
    </div>

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.expenses.store') }}" enctype="multipart/form-data"
              x-data="{
                  zeroRated: {{ old('is_zero_rated') ? 'true' : 'false' }},
                  amount: '{{ old('amount', '') }}',
                  get vatAmount() { return this.zeroRated ? 0 : Math.round((parseFloat(this.amount) || 0) * 0.18 * 100) / 100; },
                  get vatDisplay() { return this.vatAmount.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}); },
                  get total() {
                      return ((parseFloat(this.amount) || 0) + this.vatAmount).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                  }
              }">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account (COA) <span class="text-red-500">*</span></label>
                    @php
                        $jsAccounts = $accounts->map(fn($a) => ['id' => (string)$a->id, 'code' => $a->code, 'name' => $a->name, 'label' => $a->code.' — '.$a->name]);
                        $oldAccountId = old('account_id');
                        $oldAccountLabel = $oldAccountId ? ($accounts->firstWhere('id', $oldAccountId)?->code . ' — ' . $accounts->firstWhere('id', $oldAccountId)?->name) : '';
                    @endphp
                    <div class="relative" id="acctWrap">
                        <input type="hidden" name="account_id" id="acctId" value="{{ $oldAccountId }}">
                        <input type="text" id="acctSearch"
                               value="{{ $oldAccountLabel }}"
                               placeholder="Search by code or name…"
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 pr-7">
                        <button type="button" id="acctClear"
                                class="{{ $oldAccountId ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-sm">&times;</button>
                    </div>
                    @error('account_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier / Vendor</label>
                    <select name="supplier_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">— None —</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay From Account <span class="text-red-500">*</span></label>
                    <select name="bank_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="">Select account</option>
                        @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" @selected(old('bank_account_id') == $ba->id)>{{ $ba->name }} ({{ $ba->currency }} {{ number_format($ba->current_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (excl. VAT) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" x-model="amount" value="{{ old('amount') }}" step="0.01" min="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Receipt #</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (PDF/Image)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.expenses.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Expense</button>
            </div>
        </form>
    </div>

<div id="acctDrop"
     style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 4px 12px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;min-width:280px;font-size:.875rem">
</div>

<script>
const ACCOUNTS = @json($jsAccounts);
const acctDrop = document.getElementById('acctDrop');
let acctDropTarget = null;

function openAccountDrop(wrap) {
    acctDropTarget = wrap;
    const rect = wrap.querySelector('.acct-search, #acctSearch').getBoundingClientRect();
    acctDrop.style.left  = rect.left + window.scrollX + 'px';
    acctDrop.style.top   = rect.bottom + window.scrollY + 4 + 'px';
    acctDrop.style.width = rect.width + 'px';
    renderAccountDrop(wrap);
    acctDrop.style.display = 'block';
}

function renderAccountDrop(wrap) {
    const q = (wrap.querySelector('.acct-search, #acctSearch').value || '').toLowerCase().trim();
    const rows = q.length < 1 ? ACCOUNTS.slice(0, 40)
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
    wrap.querySelector('#acctId, .acct-id-value').value = id;
    const srch = wrap.querySelector('#acctSearch, .acct-search');
    srch.value = label;
    const clr = wrap.querySelector('#acctClear, .acct-clear');
    if (clr) clr.classList.toggle('hidden', !id);
    acctDrop.style.display = 'none';
}

function initAccountTypeahead(wrap, defaultId) {
    const srch = wrap.querySelector('#acctSearch, .acct-search');
    const clr  = wrap.querySelector('#acctClear, .acct-clear');

    if (defaultId) {
        const a = ACCOUNTS.find(a => a.id === String(defaultId));
        if (a) setAccount(wrap, a.id, a.label);
    }

    srch.addEventListener('focus', () => openAccountDrop(wrap));
    srch.addEventListener('input', () => { renderAccountDrop(wrap); acctDrop.style.display = 'block'; acctDropTarget = wrap; });
    srch.addEventListener('blur', () => setTimeout(() => { if (acctDropTarget === wrap) acctDrop.style.display = 'none'; }, 160));

    acctDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('.acct-opt');
        if (!opt || acctDropTarget !== wrap) return;
        setAccount(wrap, opt.dataset.id, opt.dataset.label);
    });

    if (clr) clr.addEventListener('click', () => setAccount(wrap, '', ''));
}

document.addEventListener('DOMContentLoaded', () => {
    initAccountTypeahead(document.getElementById('acctWrap'), '{{ $oldAccountId }}');
});
</script>
</x-admin-layout>
