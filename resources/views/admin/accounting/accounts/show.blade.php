<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.accounts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Chart of Accounts</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $account->code }} — {{ $account->name }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $account->getTypeBadgeStyle() }}">{{ ucfirst($account->type) }}</span>
                <span class="text-sm text-gray-500">Normal balance: {{ ucfirst($account->normal_balance) }}</span>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Running Balance</p>
            <p class="text-3xl font-bold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                Tsh {{ number_format(abs($account->balance), 0) }}
            </p>
        </div>
    </div>

    <!-- Sub-accounts -->
    @if($account->children->count())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <p class="text-sm font-semibold text-gray-700 mb-3">Sub-accounts</p>
        <div class="flex flex-wrap gap-2">
            @foreach($account->children as $child)
            <a href="{{ route('admin.accounting.accounts.show', $child) }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-200">
                {{ $child->code }} — {{ $child->name }}
                <span class="text-gray-500">Tsh {{ number_format($child->balance, 0) }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Ledger Entries -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-800">Ledger Entries</p>
            <p class="text-xs text-gray-400">Posted transactions only</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Entry#</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Debit</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Credit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($lines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $line->journalEntry->entry_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.accounting.journal-entries.show', $line->journalEntry) }}" class="text-blue-600 hover:underline font-mono text-xs">
                            {{ $line->journalEntry->entry_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $line->description ?? $line->journalEntry->description }}</td>
                    <td class="px-4 py-3 text-right font-mono {{ $line->debit > 0 ? 'text-gray-900 font-semibold' : 'text-gray-300' }}">
                        {{ $line->debit > 0 ? number_format($line->debit, 0) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono {{ $line->credit > 0 ? 'text-gray-900 font-semibold' : 'text-gray-300' }}">
                        {{ $line->credit > 0 ? number_format($line->credit, 0) : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No posted transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($lines->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $lines->links() }}</div>
        @endif
    </div>
</x-admin-layout>
