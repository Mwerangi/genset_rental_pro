<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bank Statements</h1>
            <p class="text-gray-500 mt-1">Post unrecorded bank transactions to the journal</p>
        </div>
        <a href="{{ route('admin.accounting.bank-statements.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Statement
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Reference</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Bank Account</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Period</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Transactions</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Pending</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Posted</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Created By</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($statements as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $s->reference ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $s->bankAccount->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        @if($s->period_from || $s->period_to)
                            {{ $s->period_from?->format('d M Y') }} – {{ $s->period_to?->format('d M Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-gray-700">{{ $s->transactions_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($s->pending_count > 0)
                        <span class="bg-yellow-100 text-yellow-700 rounded-full px-2 py-0.5 text-xs font-medium">{{ $s->pending_count }}</span>
                        @else
                        <span class="text-gray-400 text-xs">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">{{ $s->posted_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->createdBy->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $s->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.accounting.bank-statements.show', $s) }}" class="text-red-600 hover:text-red-700 text-xs font-medium">Open →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-gray-400 italic">No bank statements yet. Click "New Statement" to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($statements->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $statements->links() }}</div>
        @endif
    </div>
</x-admin-layout>
