<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Bank Accounts</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $bankAccount->name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $bankAccount->getTypeBadgeStyle() }}">{{ $bankAccount->getTypeLabel() }}</span>
                @if($bankAccount->bank_name)<span class="text-xs text-gray-500">{{ $bankAccount->bank_name }}</span>@endif
                @if($bankAccount->account_number)<span class="text-xs text-gray-500">• {{ $bankAccount->account_number }}</span>@endif
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Current Balance</p>
            <p class="text-3xl font-bold {{ $bankAccount->current_balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ $bankAccount->currency }} {{ number_format($bankAccount->current_balance, 0) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Receipts (Invoice Payments) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-green-50">
                <p class="font-semibold text-green-800">Money In — Receipts</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Invoice / Client</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($receipts as $pmt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $pmt->payment_date?->format('d M') }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.invoices.show', $pmt->invoice) }}" class="text-blue-600 hover:underline text-xs">{{ $pmt->invoice?->invoice_number }}</a>
                            <span class="text-xs text-gray-400 ml-1">{{ $pmt->invoice?->client?->name }}</span>
                        </td>
                        <td class="px-4 py-2 text-right font-semibold text-green-700 font-mono text-xs">+{{ number_format($pmt->amount, 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">No receipts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Payments Out -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-red-50">
                <p class="font-semibold text-red-800">Money Out — Supplier Payments</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Supplier / Ref</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $sp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $sp->payment_date?->format('d M') }}</td>
                        <td class="px-4 py-2">
                            <span class="text-xs font-medium text-gray-700">{{ $sp->supplier?->name }}</span>
                            @if($sp->reference)<span class="text-xs text-gray-400 ml-1">{{ $sp->reference }}</span>@endif
                        </td>
                        <td class="px-4 py-2 text-right font-semibold text-red-700 font-mono text-xs">-{{ number_format($sp->amount, 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cash Disbursements -->
    <div class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-orange-50">
            <p class="font-semibold text-orange-800">Cash Disbursements (Cash Requests)</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Ref</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Purpose</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Requested By</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Status</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($cashDisbursements as $cr)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $cr->paid_at?->format('d M Y') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.accounting.cash-requests.show', $cr) }}" class="text-blue-600 hover:underline text-xs font-mono">{{ $cr->request_number }}</a>
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-700">{{ Str::limit($cr->purpose, 50) }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $cr->requestedBy?->name }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $cr->status_style }}">{{ $cr->status_label }}</span>
                    </td>
                    <td class="px-4 py-2 text-right font-semibold text-orange-700 font-mono text-xs">-{{ number_format($cr->actual_amount ?? $cr->total_amount, 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">No cash disbursements from this account yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Internal Transfers -->
    @if($transfersOut->isNotEmpty() || $transfersIn->isNotEmpty())
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transfers Out -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-slate-50">
                <p class="font-semibold text-slate-700">Transfers Out</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Ref</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">To</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Sent</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Received</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Rate</th>
                        <th class="px-4 py-2 text-xs font-medium text-gray-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transfersOut as $tr)
                    @php $isFx = $tr->from_currency && $tr->to_currency && $tr->from_currency !== $tr->to_currency; @endphp
                    <tr class="hover:bg-gray-50 {{ $tr->isReversed() ? 'opacity-50' : '' }}">
                        <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">{{ $tr->transfer_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-xs font-mono text-gray-600">
                            {{ $tr->reference }}
                            @if($tr->reversal_of_transfer_id)
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-orange-100 text-orange-600 font-semibold">REV</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $tr->toAccount?->name }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-700 font-mono text-xs whitespace-nowrap">
                            -{{ $tr->from_currency ?? $bankAccount->currency }} {{ number_format($tr->amount, 2) }}
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-xs whitespace-nowrap">
                            @if($isFx)
                                <span class="text-indigo-600 font-semibold">{{ $tr->to_currency }} {{ number_format($tr->to_amount, 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-xs whitespace-nowrap">
                            @if($isFx && $tr->exchange_rate)
                                <span class="text-gray-500">{{ number_format($tr->exchange_rate, 4) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($tr->isReversed())
                                <span class="px-2 py-0.5 rounded text-[10px] bg-red-100 text-red-500 font-semibold">Reversed</span>
                            @elseif(!$tr->reversal_of_transfer_id)
                                <form method="POST" action="{{ route('admin.accounting.account-transfers.reverse', $tr) }}"
                                      onsubmit="return confirm('Reverse transfer {{ $tr->reference }}? This will restore balances and create a reversal journal entry.')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 border border-red-200 text-red-500 rounded text-xs hover:bg-red-50">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-4 text-center text-xs text-gray-400">No outgoing transfers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Transfers In -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-slate-50">
                <p class="font-semibold text-slate-700">Transfers In</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Ref</th>
                        <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">From</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Sent</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Received</th>
                        <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Rate</th>
                        <th class="px-4 py-2 text-xs font-medium text-gray-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transfersIn as $tr)
                    @php $isFx = $tr->from_currency && $tr->to_currency && $tr->from_currency !== $tr->to_currency; @endphp
                    <tr class="hover:bg-gray-50 {{ $tr->isReversed() ? 'opacity-50' : '' }}">
                        <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">{{ $tr->transfer_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-xs font-mono text-gray-600">
                            {{ $tr->reference }}
                            @if($tr->reversal_of_transfer_id)
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-orange-100 text-orange-600 font-semibold">REV</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $tr->fromAccount?->name }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs whitespace-nowrap">
                            @if($isFx)
                                <span class="text-gray-500">{{ $tr->from_currency }} {{ number_format($tr->amount, 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right font-semibold text-green-700 font-mono text-xs whitespace-nowrap">
                            +{{ $tr->to_currency ?? $bankAccount->currency }} {{ number_format($tr->to_amount ?? $tr->amount, 2) }}
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-xs whitespace-nowrap">
                            @if($isFx && $tr->exchange_rate)
                                <span class="text-gray-500">{{ number_format($tr->exchange_rate, 4) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($tr->isReversed())
                                <span class="px-2 py-0.5 rounded text-[10px] bg-red-100 text-red-500 font-semibold">Reversed</span>
                            @elseif(!$tr->reversal_of_transfer_id)
                                <form method="POST" action="{{ route('admin.accounting.account-transfers.reverse', $tr) }}"
                                      onsubmit="return confirm('Reverse transfer {{ $tr->reference }}? This will restore balances and create a reversal journal entry.')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 border border-red-200 text-red-500 rounded text-xs hover:bg-red-50">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-4 text-center text-xs text-gray-400">No incoming transfers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-admin-layout>
