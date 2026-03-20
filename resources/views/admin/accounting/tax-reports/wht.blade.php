<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Withholding Tax Register</h1>
            <p class="text-sm text-gray-500 mt-0.5">WHT deducted on supplier payments</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.tax-reports.vat') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">VAT Report</a>
            <a href="{{ route('admin.accounting.tax-reports.trial-balance') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Trial Balance</a>
        </div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex gap-3">
        <label class="text-sm font-medium text-gray-700 self-center">Period:</label>
        <input type="month" name="month" value="{{ $month }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Show</button>
    </form>

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500">Total Gross Payments</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Tsh {{ number_format($totalGross, 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium">WHT Withheld</p>
            <p class="text-xl font-bold text-red-800 mt-1">Tsh {{ number_format($totalWht, 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500">Net Paid to Suppliers</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Tsh {{ number_format($totalNet, 0) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800">WHT Deduction Register — {{ \Carbon\Carbon::parse($month)->format('F Y') }}</p>
        </div>
        @if($payments->isEmpty())
        <p class="text-sm text-gray-400 text-center py-8">No supplier payments with WHT this period</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Payment #</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Supplier</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">PO</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Gross</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">WHT Rate</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">WHT</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Net</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($payments as $p)
                <tr>
                    <td class="px-4 py-2 text-gray-600">{{ $p->payment_date->format('d M') }}</td>
                    <td class="px-4 py-2 font-mono text-xs text-blue-600">{{ $p->payment_number }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $p->supplier?->name }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $p->purchaseOrder?->po_number ?? '—' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($p->amount, 0) }}</td>
                    <td class="px-4 py-2 text-right text-xs text-gray-500">{{ $p->withholding_tax > 0 ? number_format(($p->withholding_tax/$p->amount)*100, 1).'%' : '—' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs font-medium text-red-600">{{ $p->withholding_tax > 0 ? number_format($p->withholding_tax, 0) : '—' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($p->amount - $p->withholding_tax, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200 font-semibold">
                <tr>
                    <td colspan="4" class="px-4 py-2 text-xs text-gray-700">Totals</td>
                    <td class="px-4 py-2 text-right font-mono text-sm">{{ number_format($totalGross, 0) }}</td>
                    <td></td>
                    <td class="px-4 py-2 text-right font-mono text-sm text-red-700">{{ number_format($totalWht, 0) }}</td>
                    <td class="px-4 py-2 text-right font-mono text-sm">{{ number_format($totalNet, 0) }}</td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>
</x-admin-layout>
