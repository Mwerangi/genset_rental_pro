<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Supplier Payment History</h1>
        <p class="text-sm text-gray-500 mt-0.5">All payments made to suppliers, with withholding tax details</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Supplier</label>
            <select name="supplier_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[180px]">
                <option value="">All Suppliers</option>
                @foreach($suppliersList as $s)
                <option value="{{ $s->id }}" @selected($supplierId == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Supplier or ref…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Payments</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $totals['count'] ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Paid (TZS)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['total_paid'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total WHT Deducted</p>
            <p class="text-xl font-bold text-orange-600 mt-1">{{ number_format($totals['wht'] ?? 0) }}</p>
        </div>
    </div>

    @if($bySupplier->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">By Supplier</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Supplier</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Payments</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Total Paid</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">WHT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($bySupplier as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-900">{{ $row['name'] }}</td>
                        <td class="px-4 py-2 text-right text-gray-600">{{ $row['count'] }}</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($row['total_paid']) }}</td>
                        <td class="px-4 py-2 text-right text-orange-600">{{ number_format($row['wht'] ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $payments->total() }} transactions</span>
            <a href="{{ route('admin.reports.procurement.supplier-payments.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Ref</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Supplier</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Method</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Gross</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">WHT</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Net Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $p->reference_number ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-900">{{ $p->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 capitalize text-gray-500 text-xs">{{ str_replace('_', ' ', $p->payment_method ?? '—') }}</td>
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($p->gross_amount ?? $p->amount) }}</td>
                        <td class="px-4 py-2.5 text-right text-orange-600">{{ number_format($p->withholding_tax ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($p->amount) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">No payments found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $payments->links() }}</div>
    </div>
</x-admin-layout>
