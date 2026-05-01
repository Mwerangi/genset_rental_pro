<x-admin-layout>
    <x-slot name="header">Snapshot History</x-slot>

    {{-- ── Filter bar ──────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.accounting.reports.snapshots') }}"
              class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                Filter
            </button>
            <a href="{{ route('admin.accounting.reports.snapshots') }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                Reset
            </a>
        </form>
    </div>

    {{-- ── Summary ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">Days with Snapshots</p>
            <p class="text-2xl font-bold text-gray-900">{{ $byDate->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">Account Records</p>
            <p class="text-2xl font-bold text-gray-900">{{ $closings->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">Latest Snapshot</p>
            <p class="text-lg font-bold text-gray-900">
                {{ $closings->first()?->closing_date->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">Earliest Snapshot</p>
            <p class="text-lg font-bold text-gray-900">
                {{ $closings->last()?->closing_date->format('d M Y') ?? '—' }}
            </p>
        </div>
    </div>

    {{-- ── No data ──────────────────────────────────────────────────────── --}}
    @if($byDate->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-500 font-medium">No snapshots found for this period</p>
            <p class="text-xs text-gray-400 mt-1">Snapshots are created automatically at the configured close time, or manually via the Daily Cash-Up report.</p>
            <a href="{{ route('admin.accounting.reports.daily-cashup') }}"
               class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                Go to Daily Cash-Up
            </a>
        </div>
    @else

    {{-- ── Snapshot list grouped by date ───────────────────────────────── --}}
    <div class="space-y-4">
        @foreach($byDate as $dateStr => $dayClosings)
            @php
                $totalIn  = $dayClosings->sum('total_in');
                $totalOut = $dayClosings->sum('total_out');
                $net      = $totalIn - $totalOut;
                $isAuto   = $dayClosings->contains('is_auto', true);
                $closedAt = $dayClosings->sortByDesc('updated_at')->first()?->updated_at;
            @endphp
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                {{-- Date header row --}}
                <div class="flex items-center justify-between gap-4 px-5 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="text-sm font-bold text-gray-900">
                            {{ \Carbon\Carbon::parse($dateStr)->format('l, d M Y') }}
                        </div>
                        @if(\Carbon\Carbon::parse($dateStr)->isToday())
                            <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">Today</span>
                        @endif
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $isAuto ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $isAuto ? 'Auto' : 'Manual' }} · {{ $closedAt?->format('H:i') }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $dayClosings->count() }} {{ Str::plural('account', $dayClosings->count()) }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex items-center gap-4 text-xs">
                            <span class="text-gray-500">In: <span class="font-semibold text-emerald-700">{{ number_format($totalIn, 2) }}</span></span>
                            <span class="text-gray-500">Out: <span class="font-semibold text-red-600">{{ number_format($totalOut, 2) }}</span></span>
                            <span class="text-gray-500">Net: <span class="font-semibold {{ $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">{{ number_format($net, 2) }}</span></span>
                        </div>
                        <a href="{{ route('admin.accounting.reports.daily-cashup', ['date' => $dateStr]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            View Cash-Up
                        </a>
                    </div>
                </div>

                {{-- Per-account rows --}}
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-gray-400 uppercase tracking-wide border-b border-gray-100">
                            <th class="px-5 py-2 text-left font-semibold">Account</th>
                            <th class="px-3 py-2 text-left font-semibold">Type</th>
                            <th class="px-3 py-2 text-left font-semibold">Currency</th>
                            <th class="px-3 py-2 text-right font-semibold">Opening</th>
                            <th class="px-3 py-2 text-right font-semibold text-emerald-700">Total In</th>
                            <th class="px-3 py-2 text-right font-semibold text-red-600">Total Out</th>
                            <th class="px-3 py-2 text-right font-semibold">Closing</th>
                            <th class="px-3 py-2 text-center font-semibold">Txns</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($dayClosings as $closing)
                            @php
                                $snap = $closing->snapshot ?? [];
                                $txnCount = count($snap['payments'] ?? []) + count($snap['expenses'] ?? []) + count($snap['cash_requests'] ?? []);
                                $typeColors = ['cash' => 'bg-green-100 text-green-700', 'mobile_money' => 'bg-blue-100 text-blue-700', 'bank' => 'bg-purple-100 text-purple-700'];
                                $typeLabels = ['cash' => 'Cash', 'mobile_money' => 'Mobile', 'bank' => 'Bank'];
                                $acctType   = $closing->bankAccount?->account_type ?? 'bank';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-2.5 font-medium text-gray-800">
                                    {{ $closing->bankAccount?->name ?? 'Account #'.$closing->bank_account_id }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $typeColors[$acctType] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $typeLabels[$acctType] ?? $acctType }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-gray-500">{{ $closing->bankAccount?->currency ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700 font-mono">{{ number_format($closing->opening_balance, 2) }}</td>
                                <td class="px-3 py-2.5 text-right text-emerald-700 font-mono">
                                    @if($closing->total_in > 0) {{ number_format($closing->total_in, 2) }} @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-3 py-2.5 text-right text-red-600 font-mono">
                                    @if($closing->total_out > 0) {{ number_format($closing->total_out, 2) }} @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold font-mono
                                    {{ $closing->closing_balance >= $closing->opening_balance ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ number_format($closing->closing_balance, 2) }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold
                                        {{ $txnCount > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $txnCount }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
    @endif

</x-admin-layout>
