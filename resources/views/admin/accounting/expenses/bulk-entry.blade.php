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
                            <th class="px-3 py-3 text-left w-44">Category <span class="text-red-500">*</span></th>
                            <th class="px-3 py-3 text-left w-40">Pay From <span class="text-red-500">*</span></th>
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
        $jsCategories = $categories->map(function($c) {
            return [
                'id'          => (string) $c->id,
                'name'        => $c->name,
                'isZeroRated' => (bool) $c->is_zero_rated,
                'hasAccount'  => (bool) $c->account_id,
                'accountCode' => optional($c->account)->code,
            ];
        })->values();
        $jsBankAccounts = $bankAccounts->map(function($b) {
            return ['id' => (string) $b->id, 'name' => $b->name];
        })->values();
    @endphp
    <script>
    const CATEGORIES = @json($jsCategories);
    const BANK_ACCOUNTS = @json($jsBankAccounts);

    let rowIndex = 0;

    function catOptions(selectedId = '') {
        let html = '<option value="">Select category</option>';
        CATEGORIES.forEach(c => {
            const sel = c.id === String(selectedId) ? 'selected' : '';
            const warn = !c.hasAccount ? ' ⚠' : '';
            html += `<option value="${c.id}" ${sel}>${c.name}${warn}</option>`;
        });
        return html;
    }

    function bankOptions(selectedId = '') {
        let html = '<option value="">Select account</option>';
        BANK_ACCOUNTS.forEach(b => {
            const sel = b.id === String(selectedId) ? 'selected' : '';
            html += `<option value="${b.id}" ${sel}>${b.name}</option>`;
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
                <select name="rows[${i}][expense_category_id]"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 cat-select" required>
                    ${catOptions(defaults.expense_category_id || '')}
                </select>
            </td>
            <td class="px-2 py-2">
                <select name="rows[${i}][bank_account_id]"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
                    ${bankOptions(defaults.bank_account_id || '')}
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
        const catSelect  = tr.querySelector('.cat-select');

        function recalc() {
            const isZero = zeroCheck.checked;
            const amount = parseFloat(amtInput.value) || 0;
            const vat    = isZero ? 0 : Math.round(amount * 0.18 * 100) / 100;
            const total  = amount + vat;
            vatDisplay.value = vat.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totDisplay.value = total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            recalcGrandTotal();
        }

        // Auto-detect zero-rated from category
        catSelect.addEventListener('change', () => {
            const cat = CATEGORIES.find(c => c.id === catSelect.value);
            if (cat && cat.isZeroRated) {
                zeroCheck.checked = true;
            }
            recalc();
        });

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
