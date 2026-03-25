<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Supplier Payments</a>
            <div class="flex items-center gap-3 mt-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ $supplierPayment->payment_number }}</h1>
                @if($supplierPayment->status === 'confirmed')
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Confirmed</span>
                @else
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Paid — Awaiting Confirmation</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-0.5">{{ $supplierPayment->payment_date->format('d M Y') }}</p>
        </div>
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <!-- Details -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">Payment Details</p>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-900">{{ $supplierPayment->supplier?->name }}</dd>
                    @if($supplierPayment->purchaseOrder)
                    <dt class="text-gray-500">Purchase Order</dt>
                    <dd><span class="font-mono text-sm text-blue-600">{{ $supplierPayment->purchaseOrder->po_number }}</span></dd>
                    @endif
                    <dt class="text-gray-500">Bank Account</dt><dd class="text-gray-900">{{ $supplierPayment->bankAccount?->name }}</dd>
                    <dt class="text-gray-500">Method</dt><dd>{{ ucwords(str_replace('_',' ',$supplierPayment->payment_method)) }}</dd>
                    @if($supplierPayment->reference)
                    <dt class="text-gray-500">Reference</dt><dd class="font-mono text-xs text-gray-700">{{ $supplierPayment->reference }}</dd>
                    @endif
                    @if($supplierPayment->tax_invoice_number)
                    <dt class="text-gray-500">Tax Invoice / EFD #</dt><dd class="font-mono text-xs text-gray-900 font-semibold">{{ $supplierPayment->tax_invoice_number }}</dd>
                    @endif
                    @if($supplierPayment->notes)
                    <dt class="text-gray-500">Notes</dt><dd class="text-gray-900">{{ $supplierPayment->notes }}</dd>
                    @endif
                </dl>
            </div>

            <!-- JE Lines -->
            @if($supplierPayment->journalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-800">Journal Entry</p>
                    <a href="{{ route('admin.accounting.journal-entries.show', $supplierPayment->journalEntry) }}" class="text-xs text-blue-600 hover:underline">{{ $supplierPayment->journalEntry->entry_number }} →</a>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Account</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Debit</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($supplierPayment->journalEntry->lines as $line)
                        <tr>
                            <td class="px-4 py-2 text-gray-700">
                                <span class="font-mono text-xs text-gray-400 mr-2">{{ $line->account->code }}</span>{{ $line->account->name }}
                                @if($line->description)<div class="text-xs text-gray-400">{{ $line->description }}</div>@endif
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-sm">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-sm">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td class="px-4 py-2 text-xs font-medium text-gray-500">Totals</td>
                            <td class="px-4 py-2 text-right font-bold font-mono text-sm">{{ number_format($supplierPayment->journalEntry->lines->sum('debit'), 2) }}</td>
                            <td class="px-4 py-2 text-right font-bold font-mono text-sm">{{ number_format($supplierPayment->journalEntry->lines->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>

        <!-- Summary -->
        <div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Amount Paid</p>
                <p class="text-3xl font-bold text-gray-900">Tsh {{ number_format($supplierPayment->amount, 0) }}</p>
                @if($supplierPayment->withholding_tax > 0)
                <p class="text-xs text-gray-500 mt-1">WHT: Tsh {{ number_format($supplierPayment->withholding_tax, 0) }}</p>
                <p class="text-xs text-gray-500">Net: Tsh {{ number_format($supplierPayment->amount - $supplierPayment->withholding_tax, 0) }}</p>
                @endif
            </div>

            <!-- Confirmation Card -->
            @permission('confirm_supplier_payments')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mt-4">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Payment Confirmation</p>

                @if($supplierPayment->status === 'confirmed')
                <div class="flex items-start gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-green-700">Confirmed</p>
                        <p class="text-xs text-gray-500 mt-0.5">By {{ $supplierPayment->confirmedBy?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $supplierPayment->confirmed_at?->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                @if($supplierPayment->remittance_path)
                <a href="{{ route('admin.accounting.supplier-payments.remittance', $supplierPayment) }}"
                   class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Remittance Proof
                </a>
                @endif

                {{-- Re-confirm / replace remittance --}}
                <form method="POST" action="{{ route('admin.accounting.supplier-payments.confirm', $supplierPayment) }}"
                      enctype="multipart/form-data" class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                    @csrf
                    <p class="text-xs text-gray-500 font-medium">Update remittance proof (optional)</p>
                    <input type="file" name="remittance_file" accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full text-xs border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="w-full px-3 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200">
                        Replace File
                    </button>
                </form>

                @else
                <p class="text-xs text-gray-500 mb-3">Upload remittance proof (bank advice, SWIFT confirmation, cheque scan) to confirm this payment was executed.</p>
                <form method="POST" action="{{ route('admin.accounting.supplier-payments.confirm', $supplierPayment) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remittance Proof <span class="text-gray-400 font-normal">(JPG, PNG, PDF — max 5 MB)</span></label>
                        <input type="file" name="remittance_file" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full text-xs border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full px-3 py-2.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700">
                        Confirm Payment
                    </button>
                </form>
                @endif
            </div>
            @endpermission
        </div>
    </div>
</x-admin-layout>
