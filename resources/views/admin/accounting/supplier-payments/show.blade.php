<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Supplier Payments</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $supplierPayment->payment_number }}</h1>
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
        </div>
    </div>
</x-admin-layout>
