<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">VAT Report</h1>
            <p class="text-sm text-gray-500 mt-0.5">Output VAT charged vs Input VAT claimed — Net VAT payable</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.tax-reports.wht') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">WHT Report</a>
            <a href="{{ route('admin.accounting.tax-reports.trial-balance') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Trial Balance</a>
        </div>
    </div>

    <!-- Month Filter -->
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex gap-3">
        <label class="text-sm font-medium text-gray-700 self-center">Period:</label>
        <input type="month" name="month" value="{{ $month }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Show</button>
    </form>

    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Output VAT (Collected)</p>
            <p class="text-2xl font-bold text-blue-900 mt-1">Tsh {{ number_format($totalOutputVat, 0) }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
            <p class="text-xs text-amber-600 font-medium uppercase tracking-wide">Input VAT (Paid)</p>
            <p class="text-2xl font-bold text-amber-900 mt-1">Tsh {{ number_format($totalInputVat, 0) }}</p>
        </div>
        <div class="bg-{{ $vatPayable >= 0 ? 'red' : 'green' }}-50 border border-{{ $vatPayable >= 0 ? 'red' : 'green' }}-100 rounded-xl p-4">
            <p class="text-xs text-{{ $vatPayable >= 0 ? 'red' : 'green' }}-600 font-medium uppercase tracking-wide">Net VAT {{ $vatPayable >= 0 ? 'Payable' : 'Refundable' }}</p>
            <p class="text-2xl font-bold text-{{ $vatPayable >= 0 ? 'red' : 'green' }}-900 mt-1">Tsh {{ number_format(abs($vatPayable), 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Output VAT -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-blue-50">
                <p class="font-semibold text-blue-800">Output VAT — Invoices Issued</p>
                <p class="text-xs text-blue-600">VAT charged to clients on rental invoices</p>
            </div>
            @if($outputVat->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No invoices this period</p>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Invoice</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Taxable</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">VAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($outputVat as $row)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-blue-600">{{ $row->invoice_number }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $row->client?->company_name ?: ($row->client?->full_name ?? '—') }}</td>
                        <td class="px-4 py-2 text-right text-xs font-mono">{{ number_format($row->subtotal, 0) }}</td>
                        <td class="px-4 py-2 text-right text-xs font-mono font-medium">{{ number_format($row->vat_amount, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-xs font-semibold text-gray-700">Total Output VAT</td>
                        <td class="px-4 py-2 text-right font-bold font-mono text-sm text-blue-700">{{ number_format($totalOutputVat, 0) }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>

        <!-- Input VAT -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-amber-50">
                <p class="font-semibold text-amber-800">Input VAT — Expenses Recorded</p>
                <p class="text-xs text-amber-600">VAT paid on business expenses</p>
            </div>
            @if($inputVat->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No expenses this period</p>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Expense</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Category</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Taxable</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">VAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($inputVat as $row)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-amber-600">{{ $row->expense_number }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $row->category->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-xs font-mono">{{ number_format($row->amount, 0) }}</td>
                        <td class="px-4 py-2 text-right text-xs font-mono font-medium">{{ number_format($row->vat_amount, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-xs font-semibold text-gray-700">Total Input VAT</td>
                        <td class="px-4 py-2 text-right font-bold font-mono text-sm text-amber-700">{{ number_format($totalInputVat, 0) }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>
    </div>
</x-admin-layout>
