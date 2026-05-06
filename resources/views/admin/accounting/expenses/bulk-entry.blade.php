<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expenses</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Bulk Expense Entry</h1>
            <p class="text-sm text-gray-500 mt-1">Enter multiple expenses at once. All will be saved as <span class="font-medium text-amber-600">Draft</span> for review.</p>
        </div>
        <a href="{{ route('admin.accounting.expenses.bulk-import') }}"
           class="inline-flex items-center gap-2 text-sm border border-gray-300 rounded-lg px-4 py-2 text-gray-600 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import from CSV instead
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-sm">
        <p class="font-semibold mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.accounting.expenses.bulk-store') }}" id="bulkForm">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" id="bulkTable">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-3 text-left w-32">Date <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-left min-w-48">Description <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-left w-52">Account (COA) <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-left w-40">Pay From <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-left w-40">Supplier / Vendor</th>
                            <th class="px-3 py-3 text-right w-28">Amount (TZS) <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-center w-20">No VAT</th>
                            <th class="px-3 py-3 text-right w-24">VAT</th>
                            <th class="px-3 py-3 text-right w-28">Total</th>
                            <th class="px-3 py-3 text-left w-28">Reference</th>
                            <th class="px-3 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody">
                        {{-- rows injected by JS --}}
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <button type="button" id="addRowBtn"
                    class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Row
                </button>
                <div class="text-sm text-gray-500">
                    Total: <span id="grandTotal" class="font-semibold text-gray-800">TZS 0.00</span>
                    &nbsp;|&nbsp;
                    <span id="rowCount" class="font-medium">0</span> row(s)
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                Save All as Draft
            </button>
            <a href="{{ route('admin.accounting.expenses.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>

    {{-- Data for JS --}}
    @php
        $jsAccounts = $accounts->map(function($a) {
            return [
                'id'    => (string) $a->id,
                'code'  => $a->code,
                'name'  => $a->name,
                'label' => $a->code . ' — ' . $a->name,
            ];
        })->values();
        $jsBankAccounts = $bankAccounts->map(function($b) {
            return ['id' => (string) $b->id, 'name' => $b->name];
        })->values();
        $jsSuppliers = $suppliers->map(function($s) {
            return ['id' => (string) $s->id, 'name' => $s->name];
        })->values();
    @endphp
    <script>
    const ACCOUNTS = @json($jsAccounts);
    const BANK_ACCOUNTS = @json($jsBankAccounts);
    const SUPPLIERS = @json($jsSuppliers);

    let rowIndex = 0;

    // ── Shared floating dropdown for COA account typeahead ─────────────
    const sharedDrop = document.createElement('div');
    sharedDrop.className = 'fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto hidden text-sm';
    document.body.appendChild(sharedDrop);
    let activeDrop = null, closeTimer = null;

    function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function openAccountDrop(searchEl, hiddenEl) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        activeDrop = { searchEl, hiddenEl };
        renderAccountDrop(searchEl.value);
        const r = searchEl.getBoundingClientRect();
        sharedDrop.style.top   = (r.bottom + window.scrollY + 2) + 'px';
        sharedDrop.style.left  = (r.left + window.scrollX) + 'px';
        sharedDrop.style.width = Math.max(r.width, 260) + 'px';
        sharedDrop.classList.remove('hidden');
    }

    function renderAccountDrop(q) {
        q = (q || '').toLowerCase().trim();
        const list = q ? ACCOUNTS.filter(a => a.label.toLowerCase().includes(q)) : ACCOUNTS;
        sharedDrop.innerHTML = list.length
            ? list.map(a => `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 flex gap-2 items-baseline" data-value="${escH(a.id)}" data-label="${escH(a.label)}"><span class="font-mono text-xs text-gray-400 shrink-0">${escH(a.code)}</span><span class="truncate">${escH(a.name)}</span></div>`).join('')
            : '<div class="px-3 py-2 text-gray-400 italic text-xs">No results</div>';
    }

    function closeAccountDrop() { sharedDrop.classList.add('hidden'); activeDrop = null; }
    function scheduleClose()    { closeTimer = setTimeout(closeAccountDrop, 180); }

    sharedDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('[data-value]');
        if (!opt || !activeDrop) return;
        e.preventDefault();
        setAccount(activeDrop.searchEl, activeDrop.hiddenEl, opt.dataset.value, opt.dataset.label);
        closeAccountDrop();
    });
    document.addEventListener('mousedown', e => {
        if (!sharedDrop.contains(e.target) && !e.target.classList.contains('acct-search')) closeAccountDrop();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAccountDrop(); });

    function setAccount(searchEl, hiddenEl, value, label) {
        hiddenEl.value = value;
        searchEl.value = label;
        const wrap = searchEl.closest('.acct-wrap');
        wrap?.querySelector('.acct-clear')?.classList.toggle('hidden', !value);
    }

    function initAccountTypeahead(wrap, defaultId) {
        const s = wrap.querySelector('.acct-search');
        const h = wrap.querySelector('.acct-id-value');
        const c = wrap.querySelector('.acct-clear');
        s.addEventListener('focus', () => openAccountDrop(s, h));
        s.addEventListener('input', () => { h.value = ''; c?.classList.add('hidden'); openAccountDrop(s, h); });
        s.addEventListener('blur', scheduleClose);
        c?.addEventListener('click', () => { setAccount(s, h, '', ''); s.focus(); });
        if (defaultId) {
            const a = ACCOUNTS.find(x => x.id === String(defaultId));
            if (a) setAccount(s, h, a.id, a.label);
        }
    }

    function bankOptions(selectedId = '') {
        let html = '<option value="">Select account</option>';
        BANK_ACCOUNTS.forEach(b => {
            const sel = b.id === String(selectedId) ? 'selected' : '';
            html += `<option value="${b.id}" ${sel}>${b.name}</option>`;
        });
        return html;
    }

    function supplierOptions(selectedId = '') {
        let html = '<option value="">— None —</option>';
        SUPPLIERS.forEach(s => {
            const sel = s.id === String(selectedId) ? 'selected' : '';
            html += `<option value="${s.id}" ${sel}>${s.name}</option>`;
        });
        return html;
    }

    function addRow(defaults = {}) {
        const i = rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100 hover:bg-gray-50';
        tr.dataset.rowIndex = i;
        tr.innerHTML = `
            <td class="px-2 py-2">
                <input type="date" name="rows[${i}][expense_date]"
                    value="${defaults.expense_date || '{{ now()->format('Y-m-d') }}'}"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
            </td>
            <td class="px-2 py-2">
                <input type="text" name="rows[${i}][description]"
                    value="${defaults.description || ''}"
                    placeholder="Description"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
            </td>
            <td class="px-2 py-2">
                <div class="acct-wrap relative">
                    <input type="hidden" name="rows[${i}][account_id]" class="acct-id-value" value="${defaults.account_id || ''}">
                    <input type="text" class="acct-search w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-6 truncate" placeholder="Search account…" autocomplete="off" required>
                    <button type="button" class="acct-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs p-0.5">&times;</button>
                </div>
            </td>
            <td class="px-2 py-2">
                <select name="rows[${i}][bank_account_id]"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
                    ${bankOptions(defaults.bank_account_id || '')}
                </select>
            </td>
            <td class="px-2 py-2">
                <select name="rows[${i}][supplier_id]"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                    ${supplierOptions(defaults.supplier_id || '')}
                </select>
            </td>
            <td class="px-2 py-2">
                <input type="number" name="rows[${i}][amount]"
                    value="${defaults.amount || ''}"
                    placeholder="0.00" min="0.01" step="0.01"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 amount-input" required>
            </td>
            <td class="px-2 py-2 text-center">
                <input type="hidden" name="rows[${i}][is_zero_rated]" value="0">
                <input type="checkbox" name="rows[${i}][is_zero_rated]" value="1"
                    ${defaults.is_zero_rated ? 'checked' : ''}
                    class="w-4 h-4 accent-amber-500 zero-rated-check">
            </td>
            <td class="px-2 py-2">
                <input type="text" readonly tabindex="-1"
                    class="w-full bg-gray-50 border border-gray-200 rounded px-2 py-1 text-sm text-right text-gray-500 vat-display" value="0.00">
            </td>
            <td class="px-2 py-2">
                <input type="text" readonly tabindex="-1"
                    class="w-full bg-gray-50 border border-gray-200 rounded px-2 py-1 text-sm text-right font-medium total-display" value="0.00">
            </td>
            <td class="px-2 py-2">
                <input type="text" name="rows[${i}][reference]"
                    value="${defaults.reference || ''}"
                    placeholder="Ref #"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
            </td>
            <td class="px-2 py-2 text-center">
                <button type="button" class="text-gray-400 hover:text-red-500 remove-row" title="Remove row">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>
        `;

        document.getElementById('rowsBody').appendChild(tr);
        wireRow(tr, defaults);
        recalcGrandTotal();
        updateRowCount();
    }

    function wireRow(tr, defaults = {}) {
        const amtInput   = tr.querySelector('.amount-input');
        const zeroCheck  = tr.querySelector('.zero-rated-check');
        const vatDisplay = tr.querySelector('.vat-display');
        const totDisplay = tr.querySelector('.total-display');

        // Init COA typeahead
        const acctWrap = tr.querySelector('.acct-wrap');
        if (acctWrap) initAccountTypeahead(acctWrap, defaults.account_id || null);

        function recalc() {
            const isZero = zeroCheck.checked;
            const amount = parseFloat(amtInput.value) || 0;
            const vat    = isZero ? 0 : Math.round(amount * 0.18 * 100) / 100;
            const total  = amount + vat;
            vatDisplay.value = vat.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totDisplay.value = total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            recalcGrandTotal();
        }

        amtInput.addEventListener('input', recalc);
        zeroCheck.addEventListener('change', recalc);
        tr.querySelector('.remove-row').addEventListener('click', () => {
            tr.remove();
            recalcGrandTotal();
            updateRowCount();
        });

        // Run initial recalc if defaults have amount
        if (defaults.amount) recalc();
    }

    function recalcGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.total-display').forEach(el => {
            grand += parseFloat(el.value.replace(/,/g, '')) || 0;
        });
        document.getElementById('grandTotal').textContent =
            'TZS ' + grand.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function updateRowCount() {
        document.getElementById('rowCount').textContent =
            document.getElementById('rowsBody').querySelectorAll('tr').length;
    }

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());

    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const rows = document.getElementById('rowsBody').querySelectorAll('tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one row before saving.');
        }
    });

    // Start with 3 empty rows
    addRow(); addRow(); addRow();
    </script>
</x-admin-layout>
