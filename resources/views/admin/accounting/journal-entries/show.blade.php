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
            @permission('edit_journal_entries')
            <a href="{{ route('admin.accounting.journal-entries.edit', $journalEntry) }}"
               class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
            @endpermission
            <form method="POST" action="{{ route('admin.accounting.journal-entries.post', $journalEntry) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Post Entry</button>
            </form>
            @permission('delete_journal_entries')
            <button onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Delete</button>
            @endpermission
            @elseif($journalEntry->status === 'posted' && !$journalEntry->is_reversed)
            <button onclick="document.getElementById('reverseModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">
                Reverse Entry
            </button>
            @permission('force_delete_journal_entries')
            <button onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-300 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100">Force Delete</button>
            @endpermission
            @elseif($journalEntry->status === 'posted' && $journalEntry->is_reversed)
            @permission('force_delete_journal_entries')
            <button onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-300 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100">Force Delete</button>
            @endpermission
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
        <p class="text-sm font-medium text-gray-700 mb-1">Recorded By</p>
        <p class="text-gray-800 font-semibold">{{ $journalEntry->createdBy?->name ?? $journalEntry->description }}</p>
        @if($journalEntry->notes)
        <p class="text-sm text-gray-500 mt-2 border-t border-gray-100 pt-2">{{ $journalEntry->notes }}</p>
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
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Partner</th>
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
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if($line->partner_type && $line->partner_id)
                            <span class="inline-flex items-center gap-1">
                                <span class="text-xs px-1.5 py-0.5 rounded font-semibold {{ $line->partner_type === 'client' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($line->partner_type) }}</span>
                                {{ $line->partner_name ?? '—' }}
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
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
                    <td colspan="3" class="px-4 py-3 font-semibold text-gray-700 text-right">Totals</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 font-mono">{{ number_format($journalEntry->lines->sum('debit'), 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 font-mono">{{ number_format($journalEntry->lines->sum('credit'), 2) }}</td>
                </tr>
                @if(round($journalEntry->lines->sum('debit'), 2) !== round($journalEntry->lines->sum('credit'), 2))
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-xs text-red-600 font-semibold">⚠ Entry is NOT balanced — debits do not equal credits</td>
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

    <!-- Delete Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            @if($journalEntry->status === 'posted')
            <div class="flex items-center gap-2 mb-3">
                <span class="text-red-600 text-xl">⚠</span>
                <h3 class="font-bold text-gray-900">Force-Delete Posted Entry</h3>
            </div>
            <p class="text-sm text-gray-600 mb-2">You are about to permanently delete the posted entry <strong>{{ $journalEntry->entry_number }}</strong>.</p>
            <p class="text-xs text-red-600 font-semibold mb-5">Warning: This will not reverse account balances. Use Reverse Entry unless you are certain this entry was posted in error.</p>
            @else
            <h3 class="font-bold text-gray-900 mb-2">Delete Draft Entry</h3>
            <p class="text-sm text-gray-600 mb-5">Are you sure you want to delete <strong>{{ $journalEntry->entry_number }}</strong>? This cannot be undone.</p>
            @endif
            <form method="POST" action="{{ route('admin.accounting.journal-entries.destroy', $journalEntry) }}">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

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
