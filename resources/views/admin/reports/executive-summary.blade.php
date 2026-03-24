<x-admin-layout>

{{-- ════════════════════════════════════════════════════════════════════
     PRINT STYLES
═════════════════════════════════════════════════════════════════════ --}}
<style>
    @media print {
        .no-print { display: none !important; }
        .print-full { width: 100% !important; max-width: 100% !important; }
        body { font-size: 11px; }
        .chart-container { page-break-inside: avoid; }
    }
</style>

<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Executive Summary</h1>
            <p class="mt-1 text-sm text-gray-500">High‑level business intelligence across the entire system.</p>
        </div>
        <div class="flex flex-wrap gap-2 no-print">
            <a href="{{ route('admin.reports.executive-summary.export', request()->query()) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export CSV
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print / PDF
            </button>
        </div>
    </div>

    {{-- ── FILTER FORM ───────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm no-print">
        <form method="GET" action="{{ route('admin.reports.executive-summary') }}" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Compare From</label>
                <input type="date" name="compare_from" value="{{ $compareFrom }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Compare To</label>
                <input type="date" name="compare_to" value="{{ $compareTo }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700">
                    Apply
                </button>
            </div>
        </form>
        <p class="mt-2 text-xs text-gray-400">
            Current period: <strong>{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</strong> – <strong>{{ \Carbon\Carbon::parse($to)->format('d M Y') }}</strong>
            &nbsp;|&nbsp;
            Comparison period: <strong>{{ \Carbon\Carbon::parse($compareFrom)->format('d M Y') }}</strong> – <strong>{{ \Carbon\Carbon::parse($compareTo)->format('d M Y') }}</strong>
        </p>
    </div>

    {{-- ── PRIMARY KPI CARDS ─────────────────────────────────────────── --}}
    @php
        $fmt  = fn($v) => 'TZS ' . number_format($v, 0);
        $pct  = fn($c, $p) => $p != 0 ? round(($c - $p) / abs($p) * 100, 1) : null;
        $arrow = fn($v) => $v === null ? '' : ($v >= 0 ? '▲' : '▼');
        $deltaClass = fn($v) => $v === null ? 'text-gray-400' : ($v >= 0 ? 'text-emerald-600' : 'text-red-500');
    @endphp

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        {{-- Sales Value --}}
        @php $d = $pct($current['salesValue'], $previous['salesValue']); @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Sales Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $fmt($current['salesValue']) }}</p>
            <p class="mt-1 text-xs {{ $deltaClass($d) }}">
                {{ $arrow($d) }} {{ $d !== null ? abs($d).'%' : '—' }} vs comparison &nbsp;·&nbsp; {{ $current['salesCount'] }} bookings
            </p>
            <p class="mt-0.5 text-xs text-gray-400">Comparison: {{ $fmt($previous['salesValue']) }}</p>
        </div>

        {{-- Revenue Collected --}}
        @php $d = $pct($current['revenue'], $previous['revenue']); @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Revenue Collected</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $fmt($current['revenue']) }}</p>
            <p class="mt-1 text-xs {{ $deltaClass($d) }}">
                {{ $arrow($d) }} {{ $d !== null ? abs($d).'%' : '—' }} vs comparison
            </p>
            <p class="mt-0.5 text-xs text-gray-400">Comparison: {{ $fmt($previous['revenue']) }}</p>
        </div>

        {{-- Total Expenditure --}}
        @php $d = $pct($current['expenditure'], $previous['expenditure']); $dinv = $d !== null ? -$d : null; @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Expenditure</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $fmt($current['expenditure']) }}</p>
            <p class="mt-1 text-xs {{ $deltaClass($dinv) }}">
                {{ $arrow($d) }} {{ $d !== null ? abs($d).'%' : '—' }} vs comparison
            </p>
            <p class="mt-0.5 text-xs text-gray-400">Expenses + Cash + PO</p>
        </div>

        {{-- Net Balance --}}
        @php $d = $pct($current['netBalance'], $previous['netBalance']); $isPos = $current['netBalance'] >= 0; @endphp
        <div class="rounded-xl border {{ $isPos ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $isPos ? 'text-emerald-700' : 'text-red-700' }}">Net Balance</p>
            <p class="mt-2 text-2xl font-bold {{ $isPos ? 'text-emerald-700' : 'text-red-700' }}">{{ $fmt($current['netBalance']) }}</p>
            <p class="mt-1 text-xs {{ $deltaClass($d) }}">
                {{ $arrow($d) }} {{ $d !== null ? abs($d).'%' : '—' }} vs comparison
            </p>
            <p class="mt-0.5 text-xs {{ $isPos ? 'text-emerald-600' : 'text-red-500' }}">Revenue − Expenditure</p>
        </div>
    </div>

    {{-- ── SECONDARY CARDS ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Fleet Utilisation</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $utilizationPct }}%</p>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-blue-500" style="width: {{ min($utilizationPct, 100) }}%"></div>
            </div>
            <p class="mt-1 text-xs text-gray-400">{{ $activeRentals }} active / {{ $totalFleet }} total gensets</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">New Clients</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $newClients }}</p>
            <p class="mt-2 text-xs text-gray-400">Added in current period</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Collection Rate</p>
            <p class="mt-1 text-3xl font-bold {{ $collectionRate >= 80 ? 'text-emerald-600' : ($collectionRate >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $collectionRate }}%</p>
            <p class="mt-2 text-xs text-gray-400">Invoiced: {{ $fmt($invoicedInPeriod) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Outstanding AR</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $fmt($outstandingBalance) }}</p>
            <p class="mt-2 text-xs text-gray-400">Sent / partial / disputed invoices</p>
        </div>
    </div>

    {{-- ── EXPENDITURE BREAKDOWN ─────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-700">Expenditure Breakdown</h2>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="rounded-lg bg-orange-50 p-3">
                <p class="text-xs text-orange-700 font-medium">Direct Expenses</p>
                <p class="mt-1 text-lg font-bold text-orange-700">{{ $fmt($current['expenses']) }}</p>
                @php $ep = $current['expenditure'] > 0 ? round($current['expenses'] / $current['expenditure'] * 100, 1) : 0; @endphp
                <p class="text-xs text-orange-500">{{ $ep }}% of total</p>
            </div>
            <div class="rounded-lg bg-purple-50 p-3">
                <p class="text-xs text-purple-700 font-medium">Cash Requests</p>
                <p class="mt-1 text-lg font-bold text-purple-700">{{ $fmt($current['cashSpend']) }}</p>
                @php $cp = $current['expenditure'] > 0 ? round($current['cashSpend'] / $current['expenditure'] * 100, 1) : 0; @endphp
                <p class="text-xs text-purple-500">{{ $cp }}% of total</p>
            </div>
            <div class="rounded-lg bg-indigo-50 p-3">
                <p class="text-xs text-indigo-700 font-medium">Purchase Orders</p>
                <p class="mt-1 text-lg font-bold text-indigo-700">{{ $fmt($current['poSpend']) }}</p>
                @php $pp = $current['expenditure'] > 0 ? round($current['poSpend'] / $current['expenditure'] * 100, 1) : 0; @endphp
                <p class="text-xs text-indigo-500">{{ $pp }}% of total</p>
            </div>
        </div>
    </div>

    {{-- ── CHARTS ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Revenue vs Expenses Trend --}}
        <div class="chart-container rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-xs font-semibold text-gray-700">Monthly Revenue vs Expenditure</h2>
            <canvas id="revenueExpenseChart" height="160"></canvas>
        </div>

        {{-- Year-over-Year Comparison --}}
        <div class="chart-container rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-xs font-semibold text-gray-700">Period Comparison — Revenue</h2>
            <canvas id="yoyChart" height="160"></canvas>
        </div>

        {{-- Monthly Bookings --}}
        <div class="chart-container rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-xs font-semibold text-gray-700">Monthly Booking Volume</h2>
            <canvas id="bookingsChart" height="160"></canvas>
        </div>
    </div>

    {{-- ── INTELLIGENCE GRID ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Top Clients --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Top 5 Clients by Sales Value</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Client</th>
                            <th class="px-4 py-2 text-right">Bookings</th>
                            <th class="px-4 py-2 text-right">Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topClients as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-bold text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 font-medium text-gray-800">
                                    {{ $row->client?->company_name ?: $row->client?->full_name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->bookings_count }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format($row->total_value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Gensets --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Top 5 Generators by Booking Count</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Generator</th>
                            <th class="px-4 py-2 text-right">KVA</th>
                            <th class="px-4 py-2 text-right">Bookings</th>
                            <th class="px-4 py-2 text-right">Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topGensets as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-bold text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $row->genset?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->genset?->kva_rating ?? '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->bookings_count }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format($row->total_value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lead Sales Users --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Lead Sales Users</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-right">Bookings</th>
                            <th class="px-4 py-2 text-right">Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topSalesUsers as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-bold text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $row->createdBy?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->bookings_count }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format($row->total_value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Expense Categories --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Top Expense Categories</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @php $maxCat = $topExpenseCategories->max('total') ?: 1; @endphp
                @forelse($topExpenseCategories as $row)
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-800">{{ $row->category?->name ?? 'Uncategorised' }}</span>
                            <span class="text-gray-600">TZS {{ number_format($row->total, 0) }}</span>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-orange-400" style="width: {{ round($row->total / $maxCat * 100) }}%"></div>
                            </div>
                            <span class="w-10 text-right text-xs text-gray-400">{{ $row->cnt }}×</span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-400">No expense data for this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── OPERATIONAL SECTION ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- Genset KVA Breakdown --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Generator KVA Demand</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">KVA</th>
                            <th class="px-4 py-2 text-right">Bookings</th>
                            <th class="px-4 py-2 text-right">Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($gensetTypeBreakdown as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $row->kva_rating ?? '—' }} KVA</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->cnt }}</td>
                                <td class="px-4 py-2 text-right text-gray-800">{{ number_format($row->total_value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Maintenance Types --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Maintenance Service Types</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-right">Count</th>
                            <th class="px-4 py-2 text-right">Cost (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($maintenanceTypes as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium capitalize text-gray-800">{{ str_replace('_', ' ', $row->type) }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $row->cnt }}</td>
                                <td class="px-4 py-2 text-right text-gray-800">{{ number_format($row->total_cost, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">No maintenance records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Fleet Snapshot --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Fleet Snapshot (Live)</h2>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Available</span>
                    <span class="font-semibold text-emerald-600">{{ $fleetAvailable }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">On Rental / Deployed</span>
                    <span class="font-semibold text-blue-600">{{ $fleetDeployed }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Under Maintenance</span>
                    <span class="font-semibold text-amber-600">{{ $fleetMaintenance }}</span>
                </div>
                <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-2">
                    <span class="font-medium text-gray-800">Total Active Fleet</span>
                    <span class="font-bold text-gray-900">{{ $totalFleet }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Active Bookings</span>
                    <span class="font-semibold text-gray-900">{{ $activeRentals }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── QUICK LINKS ────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm no-print">
        <h2 class="mb-3 text-sm font-semibold text-gray-700">Drill Down to Detailed Reports</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ([
                ['admin.reports.sales.funnel',                          'Sales Funnel'],
                ['admin.reports.sales.revenue-by-client',               'Revenue by Client'],
                ['admin.reports.sales.pipeline',                        'Sales Pipeline'],
                ['admin.reports.invoices.outstanding',                  'Outstanding Invoices'],
                ['admin.reports.invoices.revenue-by-period',            'Revenue by Period'],
                ['admin.reports.invoices.payment-methods',              'Payment Methods'],
                ['admin.reports.expenses.by-period',                    'Expenses by Period'],
                ['admin.reports.expenses.by-category',                  'Expenses by Category'],
                ['admin.reports.expenses.petty-cash',                   'Petty Cash'],
                ['admin.reports.expenses.gross-margin',                 'Gross Margin'],
                ['admin.reports.fleet.bookings',                        'Fleet Bookings'],
                ['admin.reports.fleet.utilization',                     'Fleet Utilisation'],
                ['admin.reports.fleet.maintenance',                     'Maintenance'],
                ['admin.reports.fleet.fuel',                            'Fuel Log'],
                ['admin.reports.procurement.purchase-orders',           'Purchase Orders'],
                ['admin.reports.procurement.supplier-payments',         'Supplier Payments'],
                ['admin.reports.accounting.general-ledger',             'General Ledger'],
                ['admin.reports.inventory.stock-levels',                'Stock Levels'],
            ] as [$route, $label])
                <a href="{{ route($route) }}"
                   class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-red-300 hover:bg-red-50 hover:text-red-600 transition-colors">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels      = @json($chartLabels);
    const revenue     = @json($chartRevenue);
    const expenses    = @json($chartExpenses);
    const bookings    = @json($chartBookings);
    const prevRevenue  = @json($chartPrevRevenue);
    const prevExpenses = @json($chartPrevExpenses);

    // Pad compare arrays to match current period length
    const padTo = (arr, len) => { const a = [...arr]; while (a.length < len) a.push(0); return a.slice(0, len); };
    const pRev  = padTo(prevRevenue,  labels.length);
    const pExp  = padTo(prevExpenses, labels.length);

    const gridColor = 'rgba(0,0,0,0.05)';
    const currYear  = @json(\Carbon\Carbon::parse($from)->format('Y'));
    const prevYear  = @json(\Carbon\Carbon::parse($compareFrom)->format('Y'));

    // ── Chart 1: Revenue vs Expenses combo ──────────────────────────
    new Chart(document.getElementById('revenueExpenseChart'), {
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Revenue (TZS)',
                    data: revenue,
                    backgroundColor: 'rgba(16,185,129,0.75)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Expenditure (TZS)',
                    data: expenses,
                    backgroundColor: 'rgba(239,68,68,0.65)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Revenue Trend',
                    data: revenue,
                    borderColor: 'rgba(5,150,105,1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.4,
                    fill: false,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                x: { grid: { color: gridColor } },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        callback: v => 'TZS ' + Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(v),
                        font: { size: 10 },
                    }
                }
            }
        }
    });

    // ── Chart 2: Year-over-Year Revenue Comparison ──────────────────
    new Chart(document.getElementById('yoyChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: currYear + ' Revenue',
                    data: revenue,
                    backgroundColor: 'rgba(59,130,246,0.75)',
                    borderRadius: 4,
                },
                {
                    label: prevYear + ' Revenue',
                    data: pRev,
                    backgroundColor: 'rgba(156,163,175,0.6)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                x: { grid: { color: gridColor } },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        callback: v => 'TZS ' + Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(v),
                        font: { size: 10 },
                    }
                }
            }
        }
    });

    // ── Chart 3: Monthly Bookings Line ───────────────────────────────
    new Chart(document.getElementById('bookingsChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Bookings',
                data: bookings,
                borderColor: 'rgba(139,92,246,1)',
                backgroundColor: 'rgba(139,92,246,0.1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor } },
                y: {
                    grid: { color: gridColor },
                    ticks: { stepSize: 1, font: { size: 10 } },
                    beginAtZero: true,
                }
            }
        }
    });
})();
</script>
@endpush
</x-admin-layout>
