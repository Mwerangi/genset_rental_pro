<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Supplier Payments</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Record Supplier Payment</h1>
    </div>

    <form method="POST" action="{{ route('admin.accounting.supplier-payments.store') }}" class="max-w-2xl space-y-5">
        @csrf

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <p class="font-semibold text-gray-800">Payment Details</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order <span class="text-red-500">*</span></label>
                <select name="purchase_order_id" id="poSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a purchase order</option>
                    @foreach($pendingOrders as $po)
                    <option value="{{ $po->id }}"
                            data-supplier="{{ $po->supplier_id }}"
                            data-supplier-name="{{ $po->supplier?->name }}"
                            data-balance="{{ $po->balance_due }}"
                            @selected(old('purchase_order_id', request('po_id')) == $po->id)>
                        {{ $po->po_number }} — {{ $po->supplier?->name }} (Balance: Tsh {{ number_format($po->balance_due, 0) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <input type="text" id="supplierDisplay" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600" readonly placeholder="Auto-filled from PO">
                <input type="hidden" name="supplier_id" id="supplierIdInput" value="{{ old('supplier_id') }}">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gross Amount (Tsh) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amountInput" value="{{ old('amount') }}" step="0.01" min="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required oninput="calcNet()">
                    <p id="balanceHint" class="mt-1 text-xs text-blue-600 hidden">
                        Full outstanding balance: <strong id="balanceDisplay"></strong> — edit above to pay a partial amount.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Withholding Tax (Tsh)</label>
                    <input type="number" name="withholding_tax" id="whtInput" value="{{ old('withholding_tax', 0) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" oninput="calcNet()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Net Payment (Tsh)</label>
                    <input type="text" id="netDisplay" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600" placeholder="Auto-calculated">
                    <p class="text-xs text-gray-400 mt-1">Gross minus withholding — actual bank outflow.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay From <span class="text-red-500">*</span></label>
                    <select name="bank_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select bank account</option>
                        @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" @selected(old('bank_account_id')==$ba->id)>{{ $ba->name }} ({{ number_format($ba->current_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select method</option>
                        <option value="bank_transfer" @selected(old('payment_method')==='bank_transfer')>Bank Transfer</option>
                        <option value="cheque" @selected(old('payment_method')==='cheque')>Cheque</option>
                        <option value="cash" @selected(old('payment_method')==='cash')>Cash</option>
                        <option value="mobile_money" @selected(old('payment_method')==='mobile_money')>Mobile Money</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Cheque No</label>
                <input type="text" name="reference" value="{{ old('reference') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g., CHQ-0001 or wire ref">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Invoice / EFD Receipt No.</label>
                <input type="text" name="tax_invoice_number" value="{{ old('tax_invoice_number') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g. TIN-2026-00123 or EFD-XXXX-YYYY" maxlength="100">
                <p class="text-xs text-gray-400 mt-1">The supplier's tax invoice number or EFD receipt associated with this payment.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-1 text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                A journal entry will be automatically posted: <strong>DR Accounts Payable (gross) → CR Bank (net) + CR WHT Payable (if any)</strong>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Post Payment</button>
            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
        </div>
    </form>

    <script>
    function calcNet() {
        const gross = parseFloat(document.getElementById('amountInput').value) || 0;
        const wht   = parseFloat(document.getElementById('whtInput').value) || 0;
        const net   = Math.max(0, gross - wht);
        document.getElementById('netDisplay').value = net > 0
            ? 'Tsh ' + net.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
            : '';
    }
    // Init on page load
    calcNet();

    const poSelect       = document.getElementById('poSelect');
    const supplierDisplay = document.getElementById('supplierDisplay');
    const supplierIdInput = document.getElementById('supplierIdInput');
    const amountInput    = document.getElementById('amountInput');
    const balanceHint    = document.getElementById('balanceHint');
    const balanceDisplay = document.getElementById('balanceDisplay');

    function syncPO(opt) {
        if (!opt || !opt.value) {
            balanceHint.classList.add('hidden');
            return;
        }
        supplierDisplay.value = opt.dataset.supplierName || '';
        if (opt.dataset.supplier) supplierIdInput.value = opt.dataset.supplier;

        const balance = parseFloat(opt.dataset.balance || 0);
        if (balance > 0) {
            // Always overwrite amount with the outstanding balance (accountant can reduce it)
            amountInput.value = balance.toFixed(2);
            balanceDisplay.textContent = 'Tsh ' + balance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            balanceHint.classList.remove('hidden');
        } else {
            balanceHint.classList.add('hidden');
        }
    }

    poSelect.addEventListener('change', function() { syncPO(this.options[this.selectedIndex]); });
    // On page load (e.g. pre-selected via ?po_id= or after validation error), sync selected option
    if (poSelect.selectedIndex > 0) syncPO(poSelect.options[poSelect.selectedIndex]);
    </script>
</x-admin-layout>
