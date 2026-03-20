<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payables Register</h1>
            <p class="text-sm text-gray-500 mt-0.5">Purchase order commitments and outstanding AP balances</p>
        </div>
        <a href="{{ route('admin.purchase-orders.index') }}"
           class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
            All Purchase Orders
        </a>
    </div>

    <!-- Status filter -->
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">Show:</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="open"     {{ $statusFilter === 'open'     ? 'selected' : '' }}>All Open (excl. cancelled)</option>
            <option value="draft"    {{ $statusFilter === 'draft'    ? 'selected' : '' }}>Draft</option>
            <option value="sent"     {{ $statusFilter === 'sent'     ? 'selected' : '' }}>Sent to Supplier</option>
            <option value="partial"  {{ $statusFilter === 'partial'  ? 'selected' : '' }}>Partially Received</option>
            <option value="received" {{ $statusFilter === 'received' ? 'selected' : '' }}>Fully Received</option>
            <option value="all"      {{ $statusFilter === 'all'      ? 'selected' : '' }}>All (incl. cancelled)</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filter</button>
    </form>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Committed (not received)</p>
            <p class="text-xl font-bold text-blue-900 mt-1">Tsh {{ number_format($totals['committed'], 0) }}</p>
            <p class="text-xs text-blue-500 mt-0.5">Draft &amp; Sent POs</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
            <p class="text-xs text-amber-600 font-medium uppercase tracking-wide">Goods Received</p>
            <p class="text-xl font-bold text-amber-900 mt-1">Tsh {{ number_format($totals['received'], 0) }}</p>
            <p class="text-xs text-amber-500 mt-0.5">Partial &amp; Fully received</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium uppercase tracking-wide">AP Balance Due</p>
            <p class="text-xl font-bold text-red-900 mt-1">Tsh {{ number_format($totals['balance_due'], 0) }}</p>
            <p class="text-xs text-red-500 mt-0.5">Received but unpaid</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium uppercase tracking-wide">Total Paid Out</p>
            <p class="text-xl font-bold text-green-900 mt-1">Tsh {{ number_format($totals['total_paid'], 0) }}</p>
            <p class="text-xs text-green-500 mt-0.5">All supplier payments</p>
        </div>
    </div>

    @if($pos->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 font-medium">No purchase orders found for this filter.</p>
        </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-800">Purchase Orders</p>
            <span class="text-xs text-gray-500">{{ $pos->count() }} order{{ $pos->count() !== 1 ? 's' : '' }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase">PO #</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase">Order Date</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase">Expected</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-blue-600 uppercase">Committed</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-amber-600 uppercase">Received</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-green-600 uppercase">Paid</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-red-600 uppercase">Balance Due</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pos as $po)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-medium text-gray-800">{{ $po->po_number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $po->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                  style="{{ $po->status_style }}">
                                {{ $po->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $po->ordered_at ? $po->ordered_at->format('d M Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            @if($po->expected_at)
                                @php
                                    $daysLeft = now()->diffInDays($po->expected_at, false);
                                    $overdue  = $daysLeft < 0 && !in_array($po->status, ['received', 'cancelled']);
                                @endphp
                                <span class="{{ $overdue ? 'text-red-600 font-medium' : '' }}">
                                    {{ $po->expected_at->format('d M Y') }}
                                </span>
                                @if($overdue)
                                    <span class="text-xs text-red-500">({{ abs(intval($daysLeft)) }}d late)</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-blue-700 font-medium tabular-nums">
                            Tsh {{ number_format($po->committed_value, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right text-amber-700 tabular-nums">
                            @if($po->received_value > 0)
                                Tsh {{ number_format($po->received_value, 0) }}
                                @if($po->committed_value > 0)
                                    <span class="block text-xs text-gray-400">
                                        {{ number_format(($po->received_value / $po->committed_value) * 100, 0) }}%
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-green-700 tabular-nums">
                            @if($po->total_paid > 0)
                                Tsh {{ number_format($po->total_paid, 0) }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold
                            {{ $po->balance_due > 0 ? 'text-red-700' : 'text-gray-400' }}">
                            @if($po->balance_due > 0)
                                Tsh {{ number_format($po->balance_due, 0) }}
                            @else
                                <span class="text-green-600 font-normal text-xs">Settled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}"
                                   class="px-2.5 py-1 text-xs text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">
                                    View
                                </a>
                                @if($po->balance_due > 0)
                                <a href="{{ route('admin.accounting.supplier-payments.create', ['po_id' => $po->id]) }}"
                                   class="px-2.5 py-1 text-xs text-white bg-red-600 rounded-md hover:bg-red-700">
                                    Pay
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-sm font-semibold text-gray-700">Totals</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-800 tabular-nums">
                            Tsh {{ number_format($pos->sum('committed_value'), 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-amber-800 tabular-nums">
                            Tsh {{ number_format($pos->sum('received_value'), 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-green-800 tabular-nums">
                            Tsh {{ number_format($pos->sum('total_paid'), 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-800 tabular-nums">
                            Tsh {{ number_format($pos->sum('balance_due'), 0) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Legend -->
    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-500">
        <p class="font-medium text-gray-600 mb-1.5">How this works</p>
        <ul class="space-y-1 list-disc list-inside">
            <li><strong>Committed</strong> — Total value of goods ordered (qty ordered × unit cost). No journal entry yet until goods are received.</li>
            <li><strong>Received</strong> — Value of goods physically received so far (posted to DR Inventory / CR Accounts Payable on each receipt).</li>
            <li><strong>Paid</strong> — Total supplier payments recorded against this PO (posted to DR AP / CR Bank on each payment).</li>
            <li><strong>Balance Due</strong> — Received value minus payments made. This is your outstanding AP for this PO.</li>
        </ul>
    </div>
</x-admin-layout>
