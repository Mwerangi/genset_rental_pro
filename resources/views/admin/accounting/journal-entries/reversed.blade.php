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

    <!-- Tabs -->
    <div class="flex gap-1 mb-5 border-b border-gray-200">
        <a href="{{ route('admin.accounting.journal-entries.index') }}"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px border-transparent text-gray-500 hover:text-gray-700">
            Active
        </a>
        <a href="{{ route('admin.accounting.journal-entries.reversed') }}"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px border-red-600 text-red-600">
            Reversed
            @if($stats['reversed'] > 0)
            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs bg-red-50 text-red-600">{{ $stats['reversed'] }}</span>
            @endif
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Active Entries</p>
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
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Reversed</p>
            <p class="text-2xl font-bold text-red-400 mt-1">{{ $stats['reversed'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.accounting.journal-entries.reversed') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="JE number, description..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Source</label>
                <select name="source_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Sources</option>
                    @foreach($sourceTypes as $value => $label)
                    <option value="{{ $value }}" @selected(request('source_type') === $value)>{{ $label }}</option>
                    @endforeach
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
            <a href="{{ route('admin.accounting.journal-entries.reversed') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</a>
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
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Reversed by JE</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($entries as $je)
                <tr class="hover:bg-gray-50 bg-red-50/30">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-500 line-through">
                        {{ $je->entry_number }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $je->entry_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $je->description }}</td>
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium opacity-60 {{ $cls }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $drLines = $je->lines->filter(fn($l) => $l->debit > 0);
                            $crLines = $je->lines->filter(fn($l) => $l->credit > 0);
                        @endphp
                        <div class="space-y-1 opacity-60">
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
                    <td class="px-4 py-3 text-right font-mono font-semibold text-gray-400 line-through">
                        Tsh {{ number_format($je->lines->sum('debit'), 0) }}
                    </td>
                    <td class="px-4 py-3">
                        @if($je->reversedBy)
                        <a href="{{ route('admin.accounting.journal-entries.show', $je->reversedBy) }}"
                           class="font-mono text-xs text-red-600 hover:underline">
                            {{ $je->reversedBy->entry_number }}
                        </a>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.accounting.journal-entries.show', $je) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No reversed journal entries.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-pagination-bar :paginator="$entries" :per-page="$perPage" />
    </div>
</x-admin-layout>
