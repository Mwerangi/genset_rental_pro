<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">General Ledger</h1>
        <p class="text-sm text-gray-500 mt-0.5">Full transaction history for a selected account with running balance</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Account <span class="text-red-500">*</span></label>
            <select name="account_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[260px]" required>
                <option value="">— Select Account —</option>
                @foreach($accountsList as $acc)
                <option value="{{ $acc->id }}" @selected($accountId == $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Description or JE #…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Run</button>
    </form>

    @if(!$account)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-8 text-center text-blue-700">
        <svg class="w-10 h-10 mx-auto mb-2 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/></svg>
        <p class="font-medium">Select an account to view its ledger.</p>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex flex-wrap gap-6">
        <div>
            <p class="text-xs text-gray-500">Account</p>
            <p class="font-bold text-gray-900">{{ $account->code }} — {{ $account->name }}</p>
            <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $account->type ?? '')) }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Opening Balance</p>
            <p class="font-bold text-gray-900">{{ number_format($openingBalance, 2) }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Period Debits</p>
            <p class="font-bold text-gray-900">{{ number_format($periodDebit, 2) }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Period Credits</p>
            <p class="font-bold text-gray-900">{{ number_format($periodCredit, 2) }}</p>
        </div>
        <div class="ml-auto">
            <p class="text-xs text-gray-500">Closing Balance</p>
            <p class="text-xl font-bold {{ $closingBalance >= 0 ? 'text-gray-900' : 'text-red-600' }}">{{ number_format($closingBalance, 2) }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">{{ $lines->total() }} entries</span>
            <a href="{{ route('admin.reports.accounting.general-ledger.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">JE #</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Description</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Debit</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Credit</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr class="bg-blue-50/50">
                        <td colspan="5" class="px-4 py-2 text-xs font-medium text-blue-700">Opening Balance</td>
                        <td class="px-4 py-2 text-right text-xs font-bold text-blue-700">{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    @forelse($lines as $line)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($line['date'])->format('d M Y') }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs">
                            <a href="{{ route('admin.accounting.journal-entries.show', $line['journal_entry_id']) }}" class="text-blue-600 hover:underline">{{ $line['entry_number'] }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700 max-w-xs">{{ $line['description'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $line['balance'] < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($line['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">No transactions found for this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-4 py-2.5 font-bold text-gray-700">Closing Balance</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($periodDebit, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($periodCredit, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-lg {{ $closingBalance < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $lines->links() }}</div>
    </div>
    @endif
</x-admin-layout>
