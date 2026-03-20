<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Journal Entries</h1>
            <p class="text-gray-500 mt-1">Double-entry ledger transactions</p>
        </div>
        <a href="{{ route('admin.accounting.journal-entries.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Manual Entry
        </a>
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
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Source</th>
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $je->source_type ?? 'manual')) }}
                        </span>
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
                            <form method="POST" action="{{ route('admin.accounting.journal-entries.post', $je) }}">
                                @csrf
                                <button type="submit" class="text-xs text-green-600 hover:underline">Post</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No journal entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($entries->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $entries->links() }}</div>
        @endif
    </div>
</x-admin-layout>
