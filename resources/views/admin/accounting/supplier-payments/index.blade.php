<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supplier Payments</h1>
            <p class="text-sm text-gray-500 mt-0.5">AP payments posted against purchase orders</p>
        </div>
        <a href="{{ route('admin.accounting.supplier-payments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Payment
        </a>
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500">Total Payments</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $payments->total() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500">This Month</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Tsh {{ number_format($monthTotal ?? 0, 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500">All Time</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Tsh {{ number_format($allTimeTotal ?? 0, 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search payment no, supplier…"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <select name="method" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Methods</option>
                <option value="bank_transfer" @selected(request('method')==='bank_transfer')>Bank Transfer</option>
                <option value="cheque" @selected(request('method')==='cheque')>Cheque</option>
                <option value="cash" @selected(request('method')==='cash')>Cash</option>
                <option value="mobile_money" @selected(request('method')==='mobile_money')>Mobile Money</option>
            </select>
            <input type="month" name="month" value="{{ request('month') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filter</button>
            @if(request()->hasAny(['q','method','month']))
            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="px-4 py-2 text-gray-500 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($payments->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <p class="text-base font-medium">No payments found</p>
            <p class="text-sm mt-1">Record a payment against a purchase order to get started</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Payment #</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Supplier</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">PO</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Method</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Tax Invoice / EFD #</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">JE</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $payment->payment_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $payment->supplier?->name }}</td>
                    <td class="px-4 py-3">
                        @if($payment->purchaseOrder)
                        <span class="text-xs font-mono text-blue-600">{{ $payment->purchaseOrder->po_number }}</span>
                        @else<span class="text-gray-400">—</span>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ ucwords(str_replace('_',' ', $payment->payment_method)) }}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $payment->tax_invoice_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($payment->status === 'confirmed')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Confirmed</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Paid</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold font-mono text-sm">Tsh {{ number_format($payment->amount, 0) }}</td>
                    <td class="px-4 py-3">
                        @if($payment->journalEntry)
                        <a href="{{ route('admin.accounting.journal-entries.show', $payment->journalEntry) }}" class="text-xs text-blue-600 hover:underline">{{ $payment->journalEntry->entry_number }}</a>
                        @else<span class="text-xs text-gray-400">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.accounting.supplier-payments.show', $payment) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $payments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>
