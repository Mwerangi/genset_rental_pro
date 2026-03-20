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
</x-admin-layout>
