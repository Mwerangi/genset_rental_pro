<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Journal Entries</h1>
            <p class="text-gray-500 mt-1">Double-entry ledger transactions</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.accounting.journal-entries.export', request()->query()) }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel
            </a>
            @permission('create_journal_entries')
            <a href="{{ route('admin.accounting.journal-entries.create') }}"
               class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Manual Entry
            </a>
            @endpermission
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Total Entries</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Draft</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['draft'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Posted</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['posted']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.accounting.journal-entries.index') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="JE number, description..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-32">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="posted" @selected(request('status') === 'posted')>Posted</option>
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Source</label>
                <select name="source_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Sources</option>
                    <option value="manual" @selected(request('source_type') === 'manual')>Manual</option>
                    <option value="invoice" @selected(request('source_type') === 'invoice')>Invoice</option>
                    <option value="payment" @selected(request('source_type') === 'payment')>Payment</option>
                    <option value="purchase_order" @selected(request('source_type') === 'purchase_order')>Purchase Order</option>
                    <option value="supplier_payment" @selected(request('source_type') === 'supplier_payment')>Supplier Payment</option>
                    <option value="expense" @selected(request('source_type') === 'expense')>Expense</option>
                    <option value="cash_request" @selected(request('source_type') === 'cash_request')>Cash Request</option>
                    <option value="credit_note" @selected(request('source_type') === 'credit_note')>Credit Note</option>
                </select>
            </div>
            <div class="min-w-32">
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-32">
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Filter</button>
            <a href="{{ route('admin.accounting.journal-entries.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Entry #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Nature / Source</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Accounts (COA)</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total Dr</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($entries as $je)
                <tr class="hover:bg-gray-50 {{ $je->is_reversed ? 'opacity-60' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">
                        {{ $je->entry_number }}
                        @if($je->is_reversed)<span class="ml-1 text-red-400">[R]</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $je->entry_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-800 max-w-xs truncate">{{ $je->description }}</td>
                    <td class="px-4 py-3">
                        @php
                            $sourceLabels = [
                                'manual'           => ['Manual Entry',      'bg-purple-50 text-purple-700'],
                                'invoice'          => ['Invoice',           'bg-blue-50 text-blue-700'],
                                'payment'          => ['Client Payment',    'bg-teal-50 text-teal-700'],
                                'purchase_order'   => ['Purchase Order',    'bg-orange-50 text-orange-700'],
                                'supplier_payment' => ['Supplier Payment',  'bg-amber-50 text-amber-700'],
                                'expense'          => ['Expense',           'bg-red-50 text-red-700'],
                                'cash_request'     => ['Cash Request',      'bg-cyan-50 text-cyan-700'],
                                'credit_note'      => ['Credit Note',       'bg-pink-50 text-pink-700'],
                                'maintenance'      => ['Maintenance',       'bg-lime-50 text-lime-700'],
                                'genset'           => ['Asset / Genset',    'bg-indigo-50 text-indigo-700'],
                            ];
                            [$label, $cls] = $sourceLabels[$je->source_type] ?? [ucfirst(str_replace('_', ' ', $je->source_type ?? 'manual')), 'bg-gray-100 text-gray-600'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $cls }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $drLines = $je->lines->filter(fn($l) => $l->debit > 0);
                            $crLines = $je->lines->filter(fn($l) => $l->credit > 0);
                        @endphp
                        <div class="space-y-1">
                            @foreach($drLines as $line)
                            <div class="flex items-center gap-1">
                                <span class="inline-block w-5 text-center text-xs font-bold text-blue-600 shrink-0">DR</span>
                                <span class="font-mono text-xs text-gray-500">{{ $line->account?->code }}</span>
                                <span class="text-xs text-gray-700 truncate max-w-[140px]" title="{{ $line->account?->name }}">{{ $line->account?->name }}</span>
                            </div>
                            @endforeach
                            @foreach($crLines as $line)
                            <div class="flex items-center gap-1">
                                <span class="inline-block w-5 text-center text-xs font-bold text-green-600 shrink-0">CR</span>
                                <span class="font-mono text-xs text-gray-500">{{ $line->account?->code }}</span>
                                <span class="text-xs text-gray-700 truncate max-w-[140px]" title="{{ $line->account?->name }}">{{ $line->account?->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">
                        Tsh {{ number_format($je->lines->sum('debit'), 0) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $je->status === 'posted' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ ucfirst($je->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.accounting.journal-entries.show', $je) }}" class="text-xs text-blue-600 hover:underline">View</a>
                            @if($je->status === 'draft')
                            @permission('edit_journal_entries')
                            <a href="{{ route('admin.accounting.journal-entries.edit', $je) }}" class="text-xs text-amber-600 hover:underline">Edit</a>
                            @endpermission
                            <form method="POST" action="{{ route('admin.accounting.journal-entries.post', $je) }}">
                                @csrf
                                <button type="submit" class="text-xs text-green-600 hover:underline">Post</button>
                            </form>
                            @permission('delete_journal_entries')
                            <button type="button"
                                    onclick="openDeleteModal('{{ $je->id }}', '{{ $je->entry_number }}', false)"
                                    class="text-xs text-red-500 hover:underline">Delete</button>
                            @endpermission
                            @elseif($je->status === 'posted')
                            @permission('force_delete_journal_entries')
                            <button type="button"
                                    onclick="openDeleteModal('{{ $je->id }}', '{{ $je->entry_number }}', true)"
                                    class="text-xs text-red-600 font-semibold hover:underline">Force Delete</button>
                            @endpermission
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No journal entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-pagination-bar :paginator="$entries" :per-page="$perPage" />
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this)closeDeleteModal()">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            <div id="deleteModalDraftHead">
                <h3 class="font-bold text-gray-900 mb-2">Delete Draft Entry</h3>
                <p class="text-sm text-gray-600 mb-1">Are you sure you want to delete</p>
                <p class="text-sm font-semibold text-gray-900 mb-4" id="deleteModalEntryNumber"></p>
                <p class="text-xs text-gray-400 mb-5">This action cannot be undone.</p>
            </div>
            <div id="deleteModalPostedHead" class="hidden">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-red-600 text-xl">⚠</span>
                    <h3 class="font-bold text-gray-900">Force-Delete Posted Entry</h3>
                </div>
                <p class="text-sm text-gray-600 mb-1">You are about to permanently delete posted entry</p>
                <p class="text-sm font-semibold text-gray-900 mb-3" id="deleteModalEntryNumberPosted"></p>
                <p class="text-xs text-red-600 font-semibold mb-5">Warning: This will not reverse account balances. Use Reverse Entry unless you are certain this entry was posted in error.</p>
            </div>
            <form id="deleteModalForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openDeleteModal(id, entryNumber, isPosted) {
        document.getElementById('deleteModalEntryNumber').textContent = entryNumber;
        document.getElementById('deleteModalEntryNumberPosted').textContent = entryNumber;
        document.getElementById('deleteModalForm').action = '/admin/accounting/journal-entries/' + id;
        document.getElementById('deleteModalDraftHead').classList.toggle('hidden', isPosted);
        document.getElementById('deleteModalPostedHead').classList.toggle('hidden', !isPosted);
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
    </script>
</x-admin-layout>
