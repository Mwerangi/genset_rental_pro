<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-500 mt-0.5 text-sm">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($canApproveBookings && $pendingApprovals > 0)
            <a href="{{ route('admin.bookings.index', ['status' => 'created']) }}"
               class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-100">
                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                {{ $pendingApprovals }} booking{{ $pendingApprovals > 1 ? 's' : '' }} awaiting approval
            </a>
            @endif
            @if($canViewQuoteReqs && $newQuoteRequests > 0)
            <a href="{{ route('admin.quote-requests.index') }}"
               class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-100">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                {{ $newQuoteRequests }} new quote request{{ $newQuoteRequests > 1 ? 's' : '' }}
            </a>
            @endif
            @if($canViewCashReqs && !$canApproveCash && $myCashRequests > 0)
            <a href="{{ route('admin.accounting.cash-requests.index') }}"
               class="inline-flex items-center gap-1.5 bg-orange-50 border border-orange-200 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-orange-100">
                <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                {{ $myCashRequests }} pending cash request{{ $myCashRequests > 1 ? 's' : '' }}
            </a>
            @endif
        </div>
    </div>

    @php
        // Number of visible KPI cards (invoices contributes 2: revenue + outstanding)
        $kpiCount    = (int)$canViewBookings + (int)$canViewInvoices * 2 + (int)$canViewFleet;
        $kpiColClass = match(true) {
            $kpiCount <= 1  => 'grid-cols-1 sm:grid-cols-2',
            $kpiCount === 2 => 'grid-cols-2',
            $kpiCount === 3 => 'grid-cols-2 md:grid-cols-3',
            default          => 'grid-cols-2 lg:grid-cols-4',
        };
        // Whether the main left column (bookings / invoices tables) has any content
        $hasMainLeft     = $canViewBookings || $canViewInvoices;
        // Outer grid: 3-col split when left has content, plain vertical stack otherwise
        $mainGridClass   = $hasMainLeft ? 'grid grid-cols-1 lg:grid-cols-3 gap-6' : 'space-y-6 max-w-2xl';
        // Whether the Quick Actions panel has at least one action to show
        $hasQuickActions = $canViewBookings || $canViewInvoices || $canViewExpenses || $canViewCashReqs;
    @endphp

    {{-- KPI Cards --}}
    @if($canViewBookings || $canViewInvoices || $canViewFleet)
    <div class="grid {{ $kpiColClass }} gap-4 mb-6">

        {{-- Active Rentals --}}
        @if($canViewBookings)
        <a href="{{ route('admin.bookings.index', ['status' => 'active']) }}"
           class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-teal-50 rounded-lg flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                @if($endingSoon->count() > 0)
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $endingSoon->count() }} ending soon</span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $activeRentals }}</p>
            <p class="text-sm text-gray-500 mt-0.5">Active Rentals</p>
        </a>
        @endif

        {{-- Monthly Revenue --}}
        @if($canViewInvoices)
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                @if($revenueChange !== null)
                <span class="text-xs font-semibold {{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs($revenueChange) }}%
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">
                @if($monthRevenue >= 1_000_000)
                    {{ number_format($monthRevenue / 1_000_000, 1) }}M
                @else
                    {{ number_format($monthRevenue / 1_000, 0) }}K
                @endif
            </p>
            <p class="text-sm text-gray-500 mt-0.5">Revenue — {{ now()->format('M Y') }}</p>
        </div>
        @endif

        {{-- Outstanding Invoices --}}
        @if($canViewInvoices)
        <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}"
           class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow group {{ ($overdueCount ?? 0) > 0 ? 'border-red-200' : '' }}">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 {{ ($overdueCount ?? 0) > 0 ? 'bg-red-50' : 'bg-orange-50' }} rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 {{ ($overdueCount ?? 0) > 0 ? 'text-red-600' : 'text-orange-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                @if(($overdueCount ?? 0) > 0)
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ $overdueCount }} overdue</span>
                @endif
            </div>
            <p class="text-3xl font-bold {{ ($overdueCount ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">
                @if($outstandingAmount >= 1_000_000)
                    {{ number_format($outstandingAmount / 1_000_000, 1) }}M
                @else
                    {{ number_format($outstandingAmount / 1_000, 0) }}K
                @endif
            </p>
            <p class="text-sm text-gray-500 mt-0.5">Outstanding (TZS)</p>
        </a>
        @endif

        {{-- Fleet Status --}}
        @if($canViewFleet)
        <a href="{{ route('admin.gensets.index') }}"
           class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                </div>
                @if($totalGensets > 0)
                <span class="text-xs font-semibold text-purple-600">{{ round(($rentedGensets / max($totalGensets, 1)) * 100) }}% utilised</span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $availableGensets }}<span class="text-lg text-gray-400">/{{ $totalGensets }}</span></p>
            <p class="text-sm text-gray-500 mt-0.5">Available Units</p>
        </a>
        @endif

    </div>
    @endif {{-- end KPI grid --}}

    {{-- Fleet breakdown mini-bar --}}
    @if($canViewFleet && $totalGensets > 0)
    <div class="mb-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fleet Breakdown</p>
            <a href="{{ route('admin.gensets.index') }}" class="text-xs text-red-600 hover:underline">View all</a>
        </div>
        @php $total = max($totalGensets, 1); @endphp
        <div class="flex rounded-full overflow-hidden h-3 gap-px bg-gray-100">
            @if($rentedGensets > 0)
            <div class="bg-teal-500 h-3" style="width:{{ round(($rentedGensets/$total)*100) }}%"></div>
            @endif
            @if($availableGensets > 0)
            <div class="bg-green-400 h-3" style="width:{{ round(($availableGensets/$total)*100) }}%"></div>
            @endif
            @if($maintenanceGensets > 0)
            <div class="bg-amber-400 h-3" style="width:{{ round(($maintenanceGensets/$total)*100) }}%"></div>
            @endif
        </div>
        <div class="flex gap-5 mt-2">
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>Rented ({{ $rentedGensets }})</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Available ({{ $availableGensets }})</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Maintenance ({{ $maintenanceGensets }})</span>
        </div>
    </div>
    @endif

    {{-- Main content grid --}}
    <div class="{{ $mainGridClass }}">

        {{-- Left: Bookings needing action + recent invoices --}}
        @if($hasMainLeft)
        <div class="lg:col-span-2 space-y-6">

            {{-- Bookings needing action --}}
            @if($canViewBookings)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-900">Bookings Needing Action</p>
                    <a href="{{ route('admin.bookings.index') }}" class="text-xs text-red-600 hover:underline">View all</a>
                </div>
                @forelse($actionableBookings as $booking)
                @php
                    $statusColors = [
                        'created'  => 'bg-blue-100 text-blue-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'returned' => 'bg-purple-100 text-purple-700',
                    ];
                @endphp
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $booking->status_label }}</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $booking->booking_number }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->client?->display_name ?? $booking->customer_name ?? '—' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 font-medium">
                        View →
                    </a>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400">All caught up — no bookings need action.</div>
                @endforelse
            </div>
            @endif {{-- canViewBookings --}}

            {{-- Recent Invoices --}}
            @if($canViewInvoices)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-900">Recent Invoices</p>
                    <a href="{{ route('admin.invoices.index') }}" class="text-xs text-red-600 hover:underline">View all</a>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-2 text-xs font-medium text-gray-500">Invoice</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Client</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-2 text-xs font-medium text-gray-500">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentInvoices as $inv)
                        @php
                            $statusStyle = [
                                'draft'          => 'bg-gray-100 text-gray-600',
                                'sent'           => 'bg-blue-100 text-blue-700',
                                'partially_paid' => 'bg-amber-100 text-amber-700',
                                'paid'           => 'bg-green-100 text-green-700',
                                'void'           => 'bg-gray-100 text-gray-400',
                                'declined'       => 'bg-red-100 text-red-600',
                            ][$inv->status] ?? 'bg-gray-100 text-gray-600';
                            $isOverdue = !in_array($inv->status, ['paid','void','declined']) && $inv->due_date?->isPast();
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50/40' : '' }}">
                            <td class="px-5 py-2.5">
                                <a href="{{ route('admin.invoices.show', $inv) }}" class="font-mono text-xs text-red-600 hover:underline font-semibold">{{ $inv->invoice_number }}</a>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $inv->issue_date?->format('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-700">{{ $inv->client?->display_name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $statusStyle }}">
                                    {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                                </span>
                                @if($isOverdue)
                                <span class="ml-1 text-xs text-red-600 font-semibold">overdue</span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-right font-mono text-xs font-semibold text-gray-800">
                                {{ number_format($inv->total_amount, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-xs text-gray-400">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif {{-- canViewInvoices --}}

        </div>
        @endif {{-- hasMainLeft --}}

        {{-- Right column --}}
        <div class="space-y-6">

            {{-- Quick Actions --}}
            @if($hasQuickActions)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="font-semibold text-gray-900 mb-4">Quick Actions</p>
                <div class="space-y-2">
                    @if($canViewBookings)
                    <a href="{{ route('admin.bookings.create') }}"
                       class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Booking
                    </a>
                    @endif
                    @if($canViewInvoices)
                    <a href="{{ route('admin.invoices.index') }}"
                       class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        View Invoices
                    </a>
                    @endif
                    @if($canViewExpenses)
                    <a href="{{ route('admin.accounting.expenses.create') }}"
                       class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        Record Expense
                    </a>
                    @endif
                    @if($canViewBookings)
                    <a href="{{ route('admin.clients.create') }}"
                       class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Add Client
                    </a>
                    @endif
                    @if($canViewCashReqs)
                    <a href="{{ route('admin.accounting.cash-requests.create') }}"
                       class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cash Request
                    </a>
                    @endif
                </div>
            </div>
            @endif {{-- hasQuickActions --}}

            {{-- Account Balances --}}
            @if($canViewAccounting)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-900">Account Balances</p>
                    <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="text-xs text-red-600 hover:underline">Manage</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($bankAccounts as $ba)
                    @php
                        $typeBadge = [
                            'bank'         => 'bg-blue-50 text-blue-700',
                            'cash'         => 'bg-green-50 text-green-700',
                            'mobile_money' => 'bg-purple-50 text-purple-700',
                        ][$ba->account_type] ?? 'bg-gray-100 text-gray-500';
                        $typeLabel = ['bank' => 'Bank', 'cash' => 'Cash', 'mobile_money' => 'Mobile'][$ba->account_type] ?? $ba->account_type;
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $ba->name }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $typeBadge }}">{{ $typeLabel }}</span>
                        </div>
                        <p class="font-semibold font-mono text-sm {{ $ba->current_balance < 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($ba->current_balance, 0) }}
                        </p>
                    </div>
                    @empty
                    <div class="px-5 py-4 text-xs text-gray-400 text-center">No accounts set up.</div>
                    @endforelse
                </div>
                @if($bankAccounts->count() > 0)
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500 font-medium">Total (TZS)</p>
                    <p class="text-sm font-bold font-mono text-gray-900">{{ number_format($bankAccounts->sum('current_balance'), 0) }}</p>
                </div>
                @endif
            </div>
            @endif {{-- canViewAccounting --}}

            {{-- Ending Soon --}}
            @if($canViewBookings && $endingSoon->count() > 0)
            <div class="bg-white border border-amber-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-100 bg-amber-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-semibold text-amber-800 text-sm">Rentals Ending Soon</p>
                </div>
                <div class="divide-y divide-amber-50">
                    @foreach($endingSoon as $rental)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $rental->booking_number }}</p>
                            <p class="text-xs text-gray-500">{{ $rental->client?->display_name ?? $rental->customer_name ?? '—' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold {{ $rental->rental_end_date->isToday() ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $rental->rental_end_date->isToday() ? 'Today' : $rental->rental_end_date->format('d M') }}
                            </p>
                            <a href="{{ route('admin.bookings.show', $rental) }}" class="text-xs text-blue-600 hover:underline">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Pending Cash Requests --}}
            @if($canApproveCash && $pendingCashRequests > 0)
            <a href="{{ route('admin.accounting.cash-requests.index') }}"
               class="flex items-center justify-between px-4 py-3.5 bg-orange-50 border border-orange-200 rounded-xl hover:bg-orange-100 transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    <span class="text-sm text-orange-800 font-semibold">Pending Cash Requests</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full bg-orange-200 text-orange-800">{{ $pendingCashRequests }}</span>
            </a>
            @endif

        </div>
    </div>
</x-admin-layout>
