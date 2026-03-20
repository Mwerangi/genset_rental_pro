<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Trial Balance</h1>
            <p class="text-sm text-gray-500 mt-0.5">All accounts with posted balances as at selected date</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.tax-reports.vat') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">VAT Report</a>
            <a href="{{ route('admin.accounting.tax-reports.wht') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">WHT Report</a>
        </div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex gap-3">
        <label class="text-sm font-medium text-gray-700 self-center">As at:</label>
        <input type="date" name="as_at" value="{{ $asAt }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Show</button>
    </form>

    @if(!$balanced)
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700 flex items-center gap-2">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        Trial balance is <strong>out of balance</strong> — difference: Tsh {{ number_format(abs($totalDebits - $totalCredits), 2) }}. Investigate unposted or reversed entries.
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-800">Trial Balance as at {{ \Carbon\Carbon::parse($asAt)->format('d F Y') }}</p>
            <span class="{{ $balanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} inline-flex px-2 py-0.5 rounded text-xs font-medium">
                {{ $balanced ? 'Balanced' : 'Out of Balance' }}
            </span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 w-20">Code</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Account Name</th>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Type</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Debit</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php $currentType = null; @endphp
                @foreach($accounts as $account)
                @if($currentType !== $account->type)
                @php $currentType = $account->type; @endphp
                <tr class="bg-gray-50">
                    <td colspan="5" class="px-4 py-1.5 text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ str_replace('_',' ', $account->type) }}</td>
                </tr>
                @endif
                <tr class="border-t border-gray-50 hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-xs text-gray-400">{{ $account->code }}</td>
                    <td class="px-4 py-2 text-gray-800">{{ $account->name }}
                        @if($account->parent)<span class="text-xs text-gray-400 ml-1">({{ $account->parent->name }})</span>@endif
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ ucwords(str_replace('_',' ',$account->sub_type ?? $account->type)) }}</td>
                    <td class="px-4 py-2 text-right font-mono text-sm text-gray-900">
                        {{ $account->normal_balance === 'debit' && $account->balance > 0 ? number_format($account->balance, 2) : ($account->normal_balance === 'credit' && $account->balance < 0 ? number_format(abs($account->balance), 2) : '') }}
                    </td>
                    <td class="px-4 py-2 text-right font-mono text-sm text-gray-900">
                        {{ $account->normal_balance === 'credit' && $account->balance > 0 ? number_format($account->balance, 2) : ($account->normal_balance === 'debit' && $account->balance < 0 ? number_format(abs($account->balance), 2) : '') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-800">TOTALS</td>
                    <td class="px-4 py-3 text-right font-bold font-mono text-sm {{ $balanced ? 'text-gray-900' : 'text-red-700' }}">{{ number_format($totalDebits, 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold font-mono text-sm {{ $balanced ? 'text-gray-900' : 'text-red-700' }}">{{ number_format($totalCredits, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-admin-layout>
