<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
            <p class="text-gray-500 mt-1">Double-entry ledger accounts</p>
        </div>
        <a href="{{ route('admin.accounting.accounts.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Account
        </a>
    </div>

    <!-- Balance Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @foreach(['asset'=>['Assets','text-blue-600'],'liability'=>['Liabilities','text-red-600'],'equity'=>['Equity','text-purple-600'],'revenue'=>['Revenue','text-green-600'],'expense'=>['Expenses','text-orange-600']] as $t=>[$label,$color])
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $label }}</p>
            <p class="text-xl font-bold {{ $color }} mt-1">Tsh {{ number_format($totals[$t], 0) }}</p>
            <a href="?type={{ $t }}" class="text-xs text-gray-400 hover:text-gray-600 mt-1">filter</a>
        </div>
        @endforeach
    </div>

    <!-- Type Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
        <a href="{{ route('admin.accounting.accounts.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !$type ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">All</a>
        @foreach(['asset'=>'Assets','liability'=>'Liabilities','equity'=>'Equity','revenue'=>'Revenue','expense'=>'Expenses'] as $val=>$label)
        <a href="?type={{ $val }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $type === $val ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <!-- Accounts Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Code</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Type</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Parent</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Balance</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($accounts as $account)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $account->code }}</td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-900">{{ $account->name }}</span>
                        @if($account->is_system)
                        <span class="ml-1 text-xs text-gray-400">(system)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $account->getTypeBadgeStyle() }}">
                            {{ ucfirst($account->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $account->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold">
                        @php
                            $displayBal  = $rolledBalances[$account->id] ?? (float) $account->balance;
                            $isRolledUp  = isset($rolledBalances[$account->id]) && $displayBal !== (float) $account->balance;
                        @endphp
                        @if($account->isForeignCurrency())
                            @php $fbal = $account->foreignBalance(); @endphp
                            <span class="block {{ ($fbal ?? 0) >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                                {{ $account->currency }} {{ number_format(abs($fbal ?? 0), 2) }}
                                @if(($fbal ?? 0) < 0) <span class="text-xs">(Cr)</span>@endif
                            </span>
                            <span class="block text-xs text-gray-400 mt-0.5">
                                Tsh {{ number_format(abs($displayBal), 0) }}
                                @if($displayBal < 0) (Cr)@endif
                            </span>
                        @else
                            @php $isAbnormal = $displayBal < 0; @endphp
                            <span class="{{ $isAbnormal ? 'text-red-600' : 'text-gray-900' }}">
                                Tsh {{ number_format(abs($displayBal), 0) }}
                                @if($isAbnormal)
                                    <span class="text-xs">(Cr)</span>
                                @endif
                            </span>
                            @if($isRolledUp)
                                <span class="block text-xs text-gray-400 font-normal mt-0.5" title="Includes balances of all sub-accounts">∑ subtotal</span>
                            @endif
                            @if($isAbnormal && !$isRolledUp)
                                <span class="block text-xs text-orange-500 font-normal mt-0.5" title="Balance is in the abnormal direction — check for reversed entries">⚠ Abnormal</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($account->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-50 text-green-700">Active</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.accounting.accounts.show', $account) }}" class="text-xs text-blue-600 hover:underline">Ledger</a>
                            <a href="{{ route('admin.accounting.accounts.edit', $account) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                            @if(!$account->is_system)
                            <form method="POST" action="{{ route('admin.accounting.accounts.destroy', $account) }}" onsubmit="return confirm('Delete this account?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No accounts found. <a href="{{ route('admin.accounting.accounts.create') }}" class="text-red-600 hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination bar --}}
    <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">

        {{-- Left: showing X–Y of Z + per-page selector --}}
        <div class="flex items-center gap-3 text-sm text-gray-600">
            <span>
                Showing
                <strong>{{ $accounts->firstItem() ?? 0 }}</strong>–<strong>{{ $accounts->lastItem() ?? 0 }}</strong>
                of <strong>{{ $accounts->total() }}</strong> accounts
            </span>

            {{-- Per-page dropdown --}}
            <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
                @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                <label class="text-xs text-gray-500">Show</label>
                <select name="per_page" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @foreach([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-500">per page</span>
            </form>
        </div>

        {{-- Right: group-jump dropdown + prev/next --}}
        <div class="flex items-center gap-2">
            @if($accounts->lastPage() > 1)
            {{-- Jump to page --}}
            <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
                @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <label class="text-xs text-gray-500 whitespace-nowrap">Jump to</label>
                <select name="page" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @for($p = 1; $p <= $accounts->lastPage(); $p++)
                        @php
                            $from = (($p - 1) * $perPage) + 1;
                            $to   = min($p * $perPage, $accounts->total());
                        @endphp
                        <option value="{{ $p }}" @selected($accounts->currentPage() === $p)>
                            {{ number_format($from) }}–{{ number_format($to) }}
                        </option>
                    @endfor
                </select>
            </form>

            {{-- Prev / Next buttons --}}
            @if($accounts->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $accounts->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">← Prev</a>
            @endif

            @if($accounts->hasMorePages())
                <a href="{{ $accounts->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Next →</a>
            @else
                <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">Next →</span>
            @endif
            @endif
        </div>
    </div>
</x-admin-layout>
