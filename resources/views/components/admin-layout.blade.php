<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ mobileMenuOpen: false, invoicesOpen: {{ request()->routeIs('admin.invoices.*') ? 'true' : 'false' }} }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Milele Power') }} - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            /* Compact dropdown panels */
            .nav-dd a, .nav-dd > div > button, .nav-dd > button { font-size: 0.72rem !important; }
            .nav-dd a { padding: 0.2rem 0.75rem !important; }
            .nav-dd > div > button, .nav-dd > button { padding: 0.2rem 0.75rem !important; }
            .nav-dd .nav-section { font-size: 0.6rem !important; padding: 0.25rem 0.75rem 0.1rem !important; }
            .nav-dd .nav-divider { margin: 0.2rem 0 !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col">

            {{-- ═══════════════════════════════════════════════════════════
                 TOP NAVIGATION BAR
            ════════════════════════════════════════════════════════════ --}}
            <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">

                {{-- ── Row 1: Brand + User ───────────────────────────────── --}}
                <div class="flex items-center justify-between h-14 px-4 sm:px-6 lg:px-8 border-b border-gray-100">

                    {{-- Logo --}}
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                        <div class="bg-red-600 p-1.5 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-sm font-bold text-gray-900 leading-tight">Milele Power</h1>
                            <p class="text-[10px] text-gray-400 leading-tight">Admin Panel</p>
                        </div>
                    </a>

                    {{-- Right: Notifications + User --}}
                    <div class="flex items-center gap-3">
                        @php
                            $unreadNotifCount = \App\Models\AppNotification::where('is_read', false)
                                ->where(fn($q) => $q->where('user_id', Auth::id())->orWhereNull('user_id'))
                                ->count();
                        @endphp
                        <a href="{{ route('admin.notifications.index') }}" class="relative text-gray-400 hover:text-red-600 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadNotifCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center h-4 w-4 rounded-full bg-red-600 text-white text-[9px] font-bold">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                            @endif
                        </a>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                                <div class="bg-red-600 rounded-full p-1.5">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:block text-sm font-medium">{{ Auth::user()->name }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-xl border border-gray-200 py-1" style="display:none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Logout</button>
                                </form>
                            </div>
                        </div>

                        {{-- Mobile hamburger --}}
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-500 hover:text-gray-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ── Row 2: Main Nav (desktop) ────────────────────────── --}}
                <nav class="hidden lg:flex items-center justify-center gap-0.5 px-4 h-11">

                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    {{-- Sales Pipeline --}}
                    @permission('view_quote_requests', 'view_quotations')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.quote-requests.*') || request()->routeIs('admin.quotations.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            Sales
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full left-1/2 -translate-x-1/2 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @permission('view_quote_requests')
                            <a href="{{ route('admin.quote-requests.index') }}" class="{{ request()->routeIs('admin.quote-requests.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>Prospectus</span>
                                @php $nrq = \App\Models\QuoteRequest::where('status','new')->count(); @endphp
                                @if($nrq > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $nrq }}</span>@endif
                            </a>
                            @endpermission
                            @permission('view_quotations')
                            <a href="{{ route('admin.quotations.index') }}" class="{{ request()->routeIs('admin.quotations.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Quotations</span>
                                @php $dqc = \App\Models\Quotation::where('status','draft')->count(); @endphp
                                @if($dqc > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-amber-500 text-white">{{ $dqc }}</span>@endif
                            </a>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    {{-- Bookings --}}
                    @permission('view_bookings')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.bookings.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Bookings
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full left-1/2 -translate-x-1/2 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @php
                                $allBookingsActive = request()->routeIs('admin.bookings.index') && !in_array(request('status'), ['approved','rejected']);
                                $totalBookings   = \App\Models\Booking::count();
                                $approvedCount   = \App\Models\Booking::where('status','approved')->count();
                                $rejectedCount   = \App\Models\Booking::where('status','rejected')->count();
                                $activeRentalsCount = \App\Models\Booking::where('status','active')->count();
                            @endphp
                            <a href="{{ route('admin.bookings.index') }}" class="{{ $allBookingsActive ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>All Bookings</span>
                                @if($totalBookings > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $totalBookings }}</span>@endif
                            </a>
                            <a href="{{ route('admin.bookings.index', ['status' => 'approved']) }}" class="{{ request()->routeIs('admin.bookings.index') && request('status') === 'approved' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Approved</span>
                                @if($approvedCount > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $approvedCount }}</span>@endif
                            </a>
                            <a href="{{ route('admin.bookings.active-rentals') }}" class="{{ request()->routeIs('admin.bookings.active-rentals') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Active Rentals</span>
                                @if($activeRentalsCount > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-blue-600 text-white">{{ $activeRentalsCount }}</span>@endif
                            </a>
                            <a href="{{ route('admin.deliveries.index') }}" class="{{ request()->routeIs('admin.deliveries.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>Deliveries</span>
                                @php $pd = \App\Models\Delivery::whereIn('status',['pending','dispatched'])->count(); @endphp
                                @if($pd > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-blue-700 text-white">{{ $pd }}</span>@endif
                            </a>
                        </div>
                    </div>
                    @endpermission

                    {{-- Fleet --}}
                    @permission('view_fleet', 'view_maintenance', 'view_deliveries')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.gensets.*') || request()->routeIs('admin.maintenance.*') || request()->routeIs('admin.deliveries.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Fleet
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full left-1/2 -translate-x-1/2 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @php $totalGensets = \App\Models\Genset::count(); $activeMaintenance = \App\Models\MaintenanceRecord::whereIn('status',['scheduled','in_progress'])->count(); @endphp
                            @permission('view_fleet')
                            <a href="{{ route('admin.gensets.index') }}" class="{{ request()->routeIs('admin.gensets.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Generators</span>
                                @if($totalGensets > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $totalGensets }}</span>@endif
                            </a>
                            @endpermission
                            @permission('view_deliveries')
                            <a href="{{ route('admin.deliveries.index') }}" class="{{ request()->routeIs('admin.deliveries.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>Deliveries
                            </a>
                            @endpermission
                            @permission('view_maintenance')
                            <a href="{{ route('admin.maintenance.index') }}" class="{{ request()->routeIs('admin.maintenance.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Maintenance</span>
                                @if($activeMaintenance > 0)<span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-amber-600 text-white">{{ $activeMaintenance }}</span>@endif
                            </a>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    {{-- Customers --}}
                    @permission('view_clients')
                    <a href="{{ route('admin.clients.index') }}"
                       class="{{ request()->routeIs('admin.clients.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Customers
                    </a>
                    @endpermission

                    {{-- Inventory --}}
                    @permission('view_inventory', 'view_fuel_logs', 'view_suppliers', 'view_purchase_orders')
                    @php
                        $lowStockItems = \App\Models\InventoryItem::where('is_active', true)->whereColumn('current_stock', '<=', 'min_stock_level')->where('min_stock_level', '>', 0)->count();
                        $pendingPOs = \App\Models\PurchaseOrder::whereIn('status', ['draft', 'sent', 'partial'])->count();
                    @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.inventory.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.purchase-orders.*') || request()->routeIs('admin.fuel-logs.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Inventory
                            @if($lowStockItems > 0)<span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-500 text-white">{{ $lowStockItems }}</span>@endif
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full left-1/2 -translate-x-1/2 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @permission('view_inventory')
                            <a href="{{ route('admin.inventory.items.index') }}" class="{{ request()->routeIs('admin.inventory.items.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>Stock Items</span>
                                @if($lowStockItems > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $lowStockItems }} low</span>@endif
                            </a>
                            <a href="{{ route('admin.inventory.categories.index') }}" class="{{ request()->routeIs('admin.inventory.categories.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>Categories
                            </a>
                            @endpermission
                            @permission('view_purchase_orders')
                            <a href="{{ route('admin.purchase-orders.index') }}" class="{{ request()->routeIs('admin.purchase-orders.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Purchase Orders</span>
                                @if($pendingPOs > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-800">{{ $pendingPOs }}</span>@endif
                            </a>
                            @endpermission
                            @permission('view_suppliers')
                            <a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>Suppliers
                            </a>
                            @endpermission
                            @permission('view_fuel_logs')
                            <a href="{{ route('admin.fuel-logs.index') }}" class="{{ request()->routeIs('admin.fuel-logs.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>Fuel Logs
                            </a>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    {{-- Accounting --}}
                    @permission('view_accounting', 'view_invoices', 'view_cash_requests', 'view_expenses', 'view_journal_entries', 'view_supplier_payments', 'view_credit_notes', 'approve_cash_requests')
                    @php
                        $totalInvoices   = \App\Models\Invoice::count();
                        $awaitingPayment = \App\Models\Invoice::whereIn('status', ['draft','sent','partially_paid'])->count();
                        $overdueInvoices = \App\Models\Invoice::whereIn('status', ['draft','sent','partially_paid'])->where('due_date', '<', now())->count();
                    @endphp
                    <div x-data="{ open: false, invoicesOpen: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.accounting.*') || request()->routeIs('admin.invoices.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Accounting
                            @if($overdueInvoices > 0)<span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $overdueInvoices }}</span>@endif
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full left-1/2 -translate-x-1/2 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Operations</p>
                            {{-- Invoices sub-dropdown --}}
                            @permission('view_invoices')
                            <div class="relative">
                                <button @click="invoicesOpen = !invoicesOpen" class="{{ request()->routeIs('admin.invoices.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} w-full flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                    <span class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Invoices</span>
                                    <div class="flex items-center gap-1">
                                        @if($overdueInvoices > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $overdueInvoices }}</span>@elseif($awaitingPayment > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-500 text-white">{{ $awaitingPayment }}</span>@endif
                                        <svg class="w-3 h-3 text-gray-400 transition-transform" :class="invoicesOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </button>
                                <div x-show="invoicesOpen" x-cloak class="nav-dd absolute left-full top-0 ml-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.index') && !request('status') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                        <span>All Invoices</span>
                                        @if($totalInvoices > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $totalInvoices }}</span>@endif
                                    </a>
                                    <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}" class="{{ request()->routeIs('admin.invoices.index') && request('status') === 'sent' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                        <span>Awaiting Payment</span>
                                        @if($awaitingPayment > 0)<span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $awaitingPayment }}</span>@endif
                                    </a>
                                    @if($overdueInvoices > 0)
                                    <a href="{{ route('admin.invoices.index') }}?overdue=1" class="text-gray-700 hover:bg-gray-50 hover:text-red-600 flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                        <span>Overdue</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ $overdueInvoices }}</span>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="{{ request()->routeIs('admin.invoices.index') && request('status') === 'paid' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Paid</a>
                                </div>
                            </div>
                            @endpermission
                            @permission('view_accounting', 'view_expenses')
                            <a href="{{ route('admin.accounting.expenses.index') }}" class="{{ request()->routeIs('admin.accounting.expenses.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Expenses
                            </a>
                            @endpermission
                            @permission('view_accounting', 'view_cash_requests', 'approve_cash_requests')
                            <a href="{{ route('admin.accounting.cash-requests.index') }}" class="{{ request()->routeIs('admin.accounting.cash-requests.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Cash Requests
                            </a>
                            @endpermission
                            @permission('view_accounting', 'view_supplier_payments')
                            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="{{ request()->routeIs('admin.accounting.supplier-payments.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Supplier Payments
                            </a>
                            @endpermission
                            @permission('view_accounting', 'view_credit_notes')
                            <a href="{{ route('admin.accounting.credit-notes.index') }}" class="{{ request()->routeIs('admin.accounting.credit-notes.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>Credit Notes
                            </a>
                            @endpermission
                            @permission('view_accounting', 'view_journal_entries')
                            <a href="{{ route('admin.accounting.journal-entries.index') }}" class="{{ request()->routeIs('admin.accounting.journal-entries.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>Journal Entries
                            </a>
                            @endpermission
                            @permission('view_accounting')
                            <div class="nav-divider my-0.5 border-t border-gray-100"></div>
                            <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Reports &amp; Setup</p>
                            <a href="{{ route('admin.accounting.tax-reports.vat') }}" class="{{ request()->routeIs('admin.accounting.tax-reports.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Tax Reports</a>
                            <a href="{{ route('admin.accounting.reports.aging') }}" class="{{ request()->routeIs('admin.accounting.reports.aging') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Aging Report</a>
                            <a href="{{ route('admin.accounting.reports.statement') }}" class="{{ request()->routeIs('admin.accounting.reports.statement') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Statement of Accounts</a>
                            <a href="{{ route('admin.accounting.reports.payables') }}" class="{{ request()->routeIs('admin.accounting.reports.payables') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Payables Register</a>
                            <a href="{{ route('admin.accounting.accounts.index') }}" class="{{ request()->routeIs('admin.accounting.accounts.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Chart of Accounts</a>
                            <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="{{ request()->routeIs('admin.accounting.bank-accounts.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">Cash &amp; Bank Accounts</a>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    {{-- Reports --}}
                    @permission('view_sales_reports', 'view_fleet_reports', 'view_financial_reports', 'view_expense_reports', 'view_inventory_reports', 'view_executive_reports')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.reports.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/></svg>
                            Reports
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full right-0 mt-1 w-96 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50 grid grid-cols-2 gap-x-2">
                            <div>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Overview</p>
                                <a href="{{ route('admin.reports.executive-summary') }}" class="{{ request()->routeIs('admin.reports.executive-summary') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-1.5 text-xs">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Executive Summary
                                </a>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Sales</p>
                                <a href="{{ route('admin.reports.sales.funnel') }}" class="{{ request()->routeIs('admin.reports.sales.funnel') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Sales Funnel</a>
                                <a href="{{ route('admin.reports.sales.revenue-by-client') }}" class="{{ request()->routeIs('admin.reports.sales.revenue-by-client') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Revenue by Client</a>
                                <a href="{{ route('admin.reports.sales.pipeline') }}" class="{{ request()->routeIs('admin.reports.sales.pipeline') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Sales Pipeline</a>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Fleet</p>
                                <a href="{{ route('admin.reports.fleet.utilization') }}" class="{{ request()->routeIs('admin.reports.fleet.utilization') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Utilization</a>
                                <a href="{{ route('admin.reports.fleet.revenue-by-genset') }}" class="{{ request()->routeIs('admin.reports.fleet.revenue-by-genset') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Revenue/Generator</a>
                                <a href="{{ route('admin.reports.fleet.bookings') }}" class="{{ request()->routeIs('admin.reports.fleet.bookings') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Booking Summary</a>
                                <a href="{{ route('admin.reports.fleet.fuel') }}" class="{{ request()->routeIs('admin.reports.fleet.fuel') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Fuel Consumption</a>
                                <a href="{{ route('admin.reports.fleet.maintenance') }}" class="{{ request()->routeIs('admin.reports.fleet.maintenance') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Maintenance Costs</a>
                                <a href="{{ route('admin.reports.fleet.overdue-service') }}" class="{{ request()->routeIs('admin.reports.fleet.overdue-service') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Overdue Servicing</a>
                            </div>
                            <div>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Invoicing</p>
                                <a href="{{ route('admin.reports.invoices.revenue-by-period') }}" class="{{ request()->routeIs('admin.reports.invoices.revenue-by-period') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Revenue by Period</a>
                                <a href="{{ route('admin.reports.invoices.payment-methods') }}" class="{{ request()->routeIs('admin.reports.invoices.payment-methods') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Payment Methods</a>
                                <a href="{{ route('admin.reports.invoices.outstanding') }}" class="{{ request()->routeIs('admin.reports.invoices.outstanding') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Outstanding Invoices</a>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Expenses</p>
                                <a href="{{ route('admin.reports.expenses.by-category') }}" class="{{ request()->routeIs('admin.reports.expenses.by-category') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">By Category</a>
                                <a href="{{ route('admin.reports.expenses.by-period') }}" class="{{ request()->routeIs('admin.reports.expenses.by-period') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">By Period</a>
                                <a href="{{ route('admin.reports.expenses.petty-cash') }}" class="{{ request()->routeIs('admin.reports.expenses.petty-cash') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Petty Cash</a>
                                <a href="{{ route('admin.reports.expenses.gross-margin') }}" class="{{ request()->routeIs('admin.reports.expenses.gross-margin') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Gross Margin</a>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Procurement</p>
                                <a href="{{ route('admin.reports.procurement.supplier-payments') }}" class="{{ request()->routeIs('admin.reports.procurement.supplier-payments') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Supplier Payments</a>
                                <a href="{{ route('admin.reports.procurement.purchase-orders') }}" class="{{ request()->routeIs('admin.reports.procurement.purchase-orders') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Purchase Orders</a>
                                <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Inventory / Ledger</p>
                                <a href="{{ route('admin.reports.inventory.stock-levels') }}" class="{{ request()->routeIs('admin.reports.inventory.stock-levels') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Stock Levels</a>
                                <a href="{{ route('admin.reports.inventory.movements') }}" class="{{ request()->routeIs('admin.reports.inventory.movements') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Stock Movements</a>
                                <a href="{{ route('admin.reports.inventory.valuation') }}" class="{{ request()->routeIs('admin.reports.inventory.valuation') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">Inventory Valuation</a>
                                <a href="{{ route('admin.reports.accounting.general-ledger') }}" class="{{ request()->routeIs('admin.reports.accounting.general-ledger') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} block px-4 py-1.5 text-xs">General Ledger</a>
                            </div>
                        </div>
                    </div>
                    @endpermission

                    {{-- Settings --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.audit-trail.*') || request()->routeIs('admin.company-settings.*') ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50' }} flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                            <svg class="w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="nav-dd absolute top-full right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @permission('manage_company_settings')
                            <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Company</p>
                            <a href="{{ route('admin.company-settings.edit') }}" class="{{ request()->routeIs('admin.company-settings.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>Company Settings
                            </a>
                            <div class="nav-divider my-0.5 border-t border-gray-100"></div>
                            <p class="nav-section text-[10px] font-bold uppercase tracking-widest text-gray-400">Users &amp; Access</p>
                            @endpermission
                            @permission('view_users')
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>Users
                            </a>
                            @endpermission
                            @permission('manage_permissions')
                            <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Roles
                            </a>
                            <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Permissions
                            </a>
                            @endpermission
                            @permission('view_audit_trail')
                            <a href="{{ route('admin.audit-trail.index') }}" class="{{ request()->routeIs('admin.audit-trail.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-4 py-2 text-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Audit Trail
                            </a>
                            @endpermission
                        </div>
                    </div>

                </nav>

                {{-- ── Mobile Menu ──────────────────────────────────────── --}}
                <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="lg:hidden border-t border-gray-100 bg-white pb-3 px-4 space-y-1 max-h-[75vh] overflow-y-auto" style="display:none;">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium">Dashboard</a>

                    @permission('view_quote_requests')
                    <a href="{{ route('admin.quote-requests.index') }}" class="{{ request()->routeIs('admin.quote-requests.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Prospectus</a>
                    @endpermission
                    @permission('view_quotations')
                    <a href="{{ route('admin.quotations.index') }}" class="{{ request()->routeIs('admin.quotations.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Quotations</a>
                    @endpermission
                    @permission('view_bookings')
                    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.index') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Bookings</a>
                    <a href="{{ route('admin.bookings.active-rentals') }}" class="{{ request()->routeIs('admin.bookings.active-rentals') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Active Rentals</a>
                    @endpermission
                    @permission('view_deliveries')
                    <a href="{{ route('admin.deliveries.index') }}" class="{{ request()->routeIs('admin.deliveries.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Deliveries</a>
                    @endpermission
                    @permission('view_fleet')
                    <a href="{{ route('admin.gensets.index') }}" class="{{ request()->routeIs('admin.gensets.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Generators</a>
                    @endpermission
                    @permission('view_maintenance')
                    <a href="{{ route('admin.maintenance.index') }}" class="{{ request()->routeIs('admin.maintenance.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Maintenance</a>
                    @endpermission
                    @permission('view_clients')
                    <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Customers</a>
                    @endpermission
                    @permission('view_inventory')
                    <a href="{{ route('admin.inventory.items.index') }}" class="{{ request()->routeIs('admin.inventory.items.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Stock Items</a>
                    @endpermission
                    @permission('view_purchase_orders')
                    <a href="{{ route('admin.purchase-orders.index') }}" class="text-gray-700 hover:bg-gray-50 hover:text-red-600 flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Purchase Orders</a>
                    @endpermission
                    @permission('view_accounting', 'view_invoices')
                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Invoices</a>
                    <a href="{{ route('admin.accounting.expenses.index') }}" class="text-gray-700 hover:bg-gray-50 hover:text-red-600 flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Expenses</a>
                    <a href="{{ route('admin.accounting.cash-requests.index') }}" class="text-gray-700 hover:bg-gray-50 hover:text-red-600 flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Cash Requests</a>
                    @endpermission
                    @permission('view_sales_reports', 'view_fleet_reports', 'view_financial_reports', 'view_expense_reports', 'view_inventory_reports', 'view_executive_reports')
                    <a href="{{ route('admin.reports.executive-summary') }}" class="{{ request()->routeIs('admin.reports.executive-summary') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Executive Summary</a>
                    @endpermission
                    @permission('view_users')
                    <a href="{{ route('admin.users.index') }}" class="text-gray-700 hover:bg-gray-50 hover:text-red-600 flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Users</a>
                    @endpermission
                    @permission('manage_company_settings')
                    <a href="{{ route('admin.company-settings.edit') }}" class="{{ request()->routeIs('admin.company-settings.*') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-red-600' }} flex items-center gap-2 px-3 py-2 rounded-lg text-sm">Company Settings</a>
                    @endpermission
                </div>

            </header>

            {{-- ═══════════════════════════════════════════════════════════
                 MAIN CONTENT
            ════════════════════════════════════════════════════════════ --}}
            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

        </div>

        <!-- Toast Notifications -->
        <x-toast />

        <!-- Auto-fire session flash messages as toasts -->
        @if(session('success'))
            <script>document.addEventListener('DOMContentLoaded', () => toast.success(@js(session('success'))))</script>
        @endif
        @if(session('error'))
            <script>document.addEventListener('DOMContentLoaded', () => toast.error(@js(session('error'))))</script>
        @endif
        @if(session('warning'))
            <script>document.addEventListener('DOMContentLoaded', () => toast.warning(@js(session('warning'))))</script>
        @endif
        @if(session('info'))
            <script>document.addEventListener('DOMContentLoaded', () => toast.info(@js(session('info'))))</script>
        @endif
        @stack('scripts')
    </body>
</html>
