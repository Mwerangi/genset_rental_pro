<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.credit-notes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Credit Notes</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Credit Note</h1>
    </div>

    <form method="POST" action="{{ route('admin.accounting.credit-notes.store') }}" class="max-w-2xl space-y-5">
        @csrf

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <p class="font-semibold text-gray-800">Credit Note Details</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Invoice (optional)</label>
                <select name="invoice_id" id="invoiceSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— Standalone credit note —</option>
                    @foreach($invoices as $inv)
                    <option value="{{ $inv->id }}"
                            data-client="{{ $inv->client_id }}"
                            data-client-name="{{ $inv->client?->company_name ?: $inv->client?->full_name }}"
                            data-amount="{{ $inv->total_amount }}"
                            @selected(old('invoice_id', request('invoice_id'))==$inv->id)>
                        {{ $inv->invoice_number }} — {{ $inv->client?->company_name ?: $inv->client?->full_name }} (Tsh {{ number_format($inv->total_amount,0) }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Client: locked to invoice's client when invoice is chosen; free picker for standalone CNs --}}
            <div id="clientLockedBlock" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <div class="flex items-center gap-2 border border-gray-200 bg-gray-50 rounded-lg px-3 py-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span id="clientLockedName" class="text-sm text-gray-700 font-medium flex-1"></span>
                    <span class="text-xs text-gray-400">Auto-set from invoice</span>
                </div>
            </div>

            <div id="clientPickerBlock">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                <select name="client_id" id="clientSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Select client</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id')==$client->id)>{{ $client->company_name ?: $client->full_name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Hidden input used when invoice locks the client --}}
            <input type="hidden" name="client_id" id="clientHidden">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date <span class="text-red-500">*</span></label>
                <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>{{ old('reason') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (ex-VAT, Tsh) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amountInput" value="{{ old('amount') }}" step="0.01" min="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required onchange="calcTotal()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT Amount (Tsh)</label>
                    <input type="number" name="vat_amount" id="vatInput" value="{{ old('vat_amount', 0) }}" step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" onchange="calcTotal()">
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2.5 flex items-center justify-between">
                <span class="text-sm text-blue-800 font-medium">Total Credit Note Value</span>
                <span id="totalDisplay" class="font-bold text-blue-900 text-sm">Tsh 0</span>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Save as Draft</button>
            <a href="{{ route('admin.accounting.credit-notes.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
        </div>
    </form>

    <script>
    const invoiceSelect    = document.getElementById('invoiceSelect');
    const clientLockedBlock = document.getElementById('clientLockedBlock');
    const clientLockedName  = document.getElementById('clientLockedName');
    const clientPickerBlock = document.getElementById('clientPickerBlock');
    const clientSelect     = document.getElementById('clientSelect');
    const clientHidden     = document.getElementById('clientHidden');

    function syncInvoice(opt) {
        if (opt && opt.value) {
            // Invoice selected — lock client
            clientLockedBlock.classList.remove('hidden');
            clientPickerBlock.classList.add('hidden');
            clientLockedName.textContent = opt.dataset.clientName || '';
            clientHidden.value           = opt.dataset.client || '';
            clientSelect.removeAttribute('required');
            clientSelect.value = '';
        } else {
            // No invoice — show free picker
            clientLockedBlock.classList.add('hidden');
            clientPickerBlock.classList.remove('hidden');
            clientHidden.value           = '';
            clientSelect.setAttribute('required', 'required');
        }
    }

    invoiceSelect.addEventListener('change', function() {
        syncInvoice(this.options[this.selectedIndex]);
    });

    // On page load (invoice pre-selected or old() value)
    if (invoiceSelect.selectedIndex > 0) {
        syncInvoice(invoiceSelect.options[invoiceSelect.selectedIndex]);
    }

    function calcTotal() {
        const amount = parseFloat(document.getElementById('amountInput').value) || 0;
        const vat    = parseFloat(document.getElementById('vatInput').value) || 0;
        document.getElementById('totalDisplay').textContent = 'Tsh ' + (amount + vat).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }
    calcTotal();
    </script>
</x-admin-layout>
