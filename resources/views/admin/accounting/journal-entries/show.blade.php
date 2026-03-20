<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.journal-entries.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Journal Entries</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $journalEntry->entry_number }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $journalEntry->status === 'posted' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ ucfirst($journalEntry->status) }}
                </span>
                @if($journalEntry->is_reversed)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-50 text-red-600 font-medium">Reversed</span>
                @endif
                <span class="text-sm text-gray-500">{{ $journalEntry->entry_date?->format('d M Y') }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            @if($journalEntry->status === 'draft')
            <form method="POST" action="{{ route('admin.accounting.journal-entries.post', $journalEntry) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Post Entry</button>
            </form>
            @elseif($journalEntry->status === 'posted' && !$journalEntry->is_reversed)
            <button onclick="document.getElementById('reverseModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">
                Reverse Entry
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <!-- Meta Info -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase font-semibold">Entry Date</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $journalEntry->entry_date?->format('d M Y') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase font-semibold">Source</p>
            <p class="mt-1 font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $journalEntry->source_type ?? 'Manual')) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase font-semibold">Reference</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $journalEntry->reference ?? '—' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase font-semibold">Created By</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $journalEntry->createdBy?->name ?? 'System' }}</p>
        </div>
    </div>

    <!-- Description & Notes -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <p class="text-sm font-medium text-gray-700 mb-1">Description</p>
        <p class="text-gray-800">{{ $journalEntry->description }}</p>
        @if($journalEntry->notes)
        <p class="text-sm text-gray-500 mt-2">{{ $journalEntry->notes }}</p>
        @endif
    </div>

    <!-- Lines -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800">Journal Entry Lines</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Account</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Debit (Tsh)</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Credit (Tsh)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($journalEntry->lines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs text-gray-500">{{ $line->account?->code }}</span>
                        <span class="ml-2 font-medium text-gray-800">{{ $line->account?->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $line->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold {{ $line->debit > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono font-semibold {{ $line->credit > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="2" class="px-4 py-3 font-semibold text-gray-700 text-right">Totals</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 font-mono">{{ number_format($journalEntry->lines->sum('debit'), 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 font-mono">{{ number_format($journalEntry->lines->sum('credit'), 2) }}</td>
                </tr>
                @if(round($journalEntry->lines->sum('debit'), 2) !== round($journalEntry->lines->sum('credit'), 2))
                <tr>
                    <td colspan="4" class="px-4 py-2 text-center text-xs text-red-600 font-semibold">⚠ Entry is NOT balanced — debits do not equal credits</td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    <!-- Reversed by info -->
    @if($journalEntry->reversedBy)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-800">
        Reversed by journal entry <a href="{{ route('admin.accounting.journal-entries.show', $journalEntry->reversedBy) }}" class="font-semibold underline">{{ $journalEntry->reversedBy->entry_number }}</a>
    </div>
    @endif

    <!-- Reverse Modal -->
    <div id="reverseModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-md">
            <h3 class="font-bold text-gray-900 mb-4">Reverse Journal Entry</h3>
            <form method="POST" action="{{ route('admin.accounting.journal-entries.reverse', $journalEntry) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for reversal</label>
                    <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Enter reason..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reverseModal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium">Reverse Entry</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
