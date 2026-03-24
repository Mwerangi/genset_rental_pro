<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Outstanding Invoices</h1>
        <p class="text-sm text-gray-500 mt-0.5">Unpaid or partially-paid invoices as at a given date</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">As at</label>
            <input type="date" name="as_at" value="{{ $asAt }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
            <select name="client_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[180px]">
                <option value="">All Clients</option>
                @foreach($clientsList as $c)
                <option value="{{ $c->id }}" @selected(request('client_id') == $c->id)>{{ $c->company_name ?: $c->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Invoice # or client…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Outstanding Invoices</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $totals['count'] ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Balance (TZS)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['balance_tzs'] ?? 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-red-600">Overdue Invoices</p>
            <p class="text-xl font-bold text-red-700 mt-1">{{ $totals['overdue_count'] ?? 0 }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-red-600">Overdue Balance (TZS)</p>
            <p class="text-xl font-bold text-red-700 mt-1">{{ number_format($totals['overdue_balance_tzs'] ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $invoices->total() }} invoices</span>
            <a href="{{ route('admin.reports.invoices.outstanding.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Invoice #</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Client</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Issue Date</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Due Date</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Invoice Total</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Paid</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Balance</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Days Overdue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $inv)
                    <tr class="{{ $inv['is_overdue'] ? 'bg-red-50/40' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.invoices.show', $inv['id']) }}" class="font-medium text-blue-600 hover:underline">{{ $inv['invoice_number'] }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700">{{ $inv['client_name'] }}</td>
                        <td class="px-4 py-2.5 text-center text-gray-500 text-xs">{{ $inv['issue_date'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs {{ $inv['is_overdue'] ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $inv['due_date'] ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($inv['total_amount']) }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ number_format($inv['amount_paid']) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $inv['balance'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($inv['balance']) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($inv['is_overdue'])
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">+{{ $inv['days_overdue'] }}d</span>
                            @elseif($inv['days_overdue'] !== null)
                                <span class="text-xs text-gray-400">Due in {{ abs($inv['days_overdue']) }}d</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No outstanding invoices found.</td></tr>
                    @endforelse
                </tbody>
                @if($invoices->total() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ number_format($totals['total_amount'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-green-700">{{ number_format($totals['amount_paid'] ?? 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-red-600">{{ number_format($totals['balance_tzs'] ?? 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
    </div>
</x-admin-layout>
