<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false, salesOpen: {{ request()->routeIs('admin.quote-requests.*') || request()->routeIs('admin.quotations.*') ? 'true' : 'false' }}, bookingsOpen: {{ request()->routeIs('admin.bookings.*') || request()->routeIs('admin.quotations.*') ? 'true' : 'false' }}, inventoryOpen: {{ request()->routeIs('admin.inventory.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.purchase-orders.*') || request()->routeIs('admin.fuel-logs.*') ? 'true' : 'false' }}, accountingOpen: {{ request()->routeIs('admin.accounting.*') || request()->routeIs('admin.invoices.*') ? 'true' : 'false' }}, invoicesOpen: {{ request()->routeIs('admin.invoices.*') ? 'true' : 'false' }}, settingsOpen: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.roles.*') ? 'true' : 'false' }} }">
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
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside 
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 shadow-sm transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
                :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
            >
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="bg-red-600 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-gray-900">Milele Power</h1>
                            <p class="text-xs text-gray-500">Admin Panel</p>
                        </div>
                    </a>
                    <!-- Mobile Close Button -->
                    <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="px-4 py-6 space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="group {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Sales Pipeline (dropdown) -->
                    @permission('view_quote_requests', 'view_quotations')
                    <div class="space-y-1">
                        <button
                            @click="salesOpen = !salesOpen"
                            class="group {{ request()->routeIs('admin.quote-requests.*') || request()->routeIs('admin.quotations.*') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between w-full px-3 py-2.5 rounded-lg transition-colors duration-150"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                                </svg>
                                <span class="font-medium">Sales Pipeline</span>
                            </div>
                            <svg
                                class="w-4 h-4 transition-all duration-300"
                                :class="{ 'rotate-180': salesOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div
                            x-show="salesOpen"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="mt-1 space-y-1 border-l-2 border-red-100 ml-5 pl-2"
                        >
                            <!-- Prospectus (Quote Requests) -->
                            @permission('view_quote_requests')
                            <a href="{{ route('admin.quote-requests.index') }}" class="group {{ request()->routeIs('admin.quote-requests.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <span class="text-sm font-medium">Prospectus</span>
                                </div>
                                @php
                                    $newRequestsCount = \App\Models\QuoteRequest::where('status', 'new')->count();
                                @endphp
                                @if($newRequestsCount > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $newRequestsCount }}</span>
                                @endif
                            </a>
                            @endpermission

                            <!-- Quotations -->
                            @permission('view_quotations')
                            <a href="{{ route('admin.quotations.index') }}" class="group {{ request()->routeIs('admin.quotations.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Quotations</span>
                                </div>
                                @php
                                    $draftQuotationsCount = \App\Models\Quotation::where('status', 'draft')->count();
                                @endphp
                                @if($draftQuotationsCount > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#f59e0b;color:#fff;">{{ $draftQuotationsCount }}</span>
                                @endif
                            </a>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    <!-- Bookings (dropdown) -->
                    @permission('view_bookings')
                    <div class="space-y-1">
                        <button
                            @click="bookingsOpen = !bookingsOpen"
                            class="group {{ request()->routeIs('admin.bookings.*') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between w-full px-3 py-2.5 rounded-lg transition-colors duration-150"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-medium">Bookings</span>
                            </div>
                            <svg
                                class="w-4 h-4 transition-all duration-300"
                                :class="{ 'rotate-180': bookingsOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div
                            x-show="bookingsOpen"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="mt-1 space-y-1 border-l-2 border-red-100 ml-5 pl-2"
                        >
                            <!-- All Bookings -->
                            @php $allBookingsActive = request()->routeIs('admin.bookings.index') && !in_array(request('status'), ['approved', 'rejected']); @endphp
                            <a href="{{ route('admin.bookings.index') }}" class="{{ $allBookingsActive ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    <span class="text-sm font-medium">All Bookings</span>
                                </div>
                                @php $totalBookings = \App\Models\Booking::count(); @endphp
                                @if($totalBookings > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $totalBookings }}</span>
                                @endif
                            </a>

                            <!-- Approved Bookings -->
                            @php $approvedBookingsActive = request()->routeIs('admin.bookings.index') && request('status') === 'approved'; @endphp
                            <a href="{{ route('admin.bookings.index', ['status' => 'approved']) }}" class="{{ $approvedBookingsActive ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-medium">Approved</span>
                                </div>
                                @php $approvedBookings = \App\Models\Booking::where('status', 'approved')->count(); @endphp
                                @if($approvedBookings > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $approvedBookings }}</span>
                                @endif
                            </a>

                            <!-- Rejected Bookings -->
                            @php $rejectedBookingsActive = request()->routeIs('admin.bookings.index') && request('status') === 'rejected'; @endphp
                            <a href="{{ route('admin.bookings.index', ['status' => 'rejected']) }}" class="{{ $rejectedBookingsActive ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-medium">Rejected</span>
                                </div>
                                @php $rejectedBookings = \App\Models\Booking::where('status', 'rejected')->count(); @endphp
                                @if($rejectedBookings > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $rejectedBookings }}</span>
                                @endif
                            </a>

                            <!-- Quotations shortcut -->
                            @permission('view_quotations')
                            <div class="pt-1 mt-1 border-t border-red-100">
                                <a href="{{ route('admin.quotations.index') }}" class="group {{ request()->routeIs('admin.quotations.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-150">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-sm font-medium">Quotations</span>
                                    </div>
                                    @php $draftCount = \App\Models\Quotation::where('status', 'draft')->count(); @endphp
                                    @if($draftCount > 0)
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#f59e0b;color:#fff;">{{ $draftCount }}</span>
                                    @endif
                                </a>
                            </div>
                            @endpermission
                        </div>
                    </div>
                    @endpermission

                    <!-- Fleet -->
                    @permission('view_fleet')
                    <a href="{{ route('admin.gensets.index') }}" class="{{ request()->routeIs('admin.gensets.*') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="font-medium">Generators</span>
                        </div>
                        @php $totalGensets = \App\Models\Genset::count(); @endphp
                        @if($totalGensets > 0)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $totalGensets }}</span>
                        @endif
                    </a>

                    <!-- Rentals -->
                    @permission('view_bookings')
                    @php $activeRentalsCount = \App\Models\Booking::where('status', 'active')->count(); @endphp
                    <a href="{{ route('admin.bookings.active-rentals') }}" class="{{ request()->routeIs('admin.bookings.active-rentals') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="font-medium">Active Rentals</span>
                        </div>
                        @if($activeRentalsCount > 0)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#2563eb;color:#fff;">{{ $activeRentalsCount }}</span>
                        @endif
                    </a>
                    @endpermission

                    <!-- Deliveries -->
                    @php $pendingDeliveries = \App\Models\Delivery::whereIn('status', ['pending', 'dispatched'])->count(); @endphp
                    <a href="{{ route('admin.deliveries.index') }}" class="{{ request()->routeIs('admin.deliveries.*') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>
                            <span class="font-medium">Deliveries</span>
                        </div>
                        @if($pendingDeliveries > 0)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#1e40af;color:#fff;">{{ $pendingDeliveries }}</span>
                        @endif
                    </a>

                    <!-- Maintenance -->  {{-- still inside @permission('view_fleet') --}}
                    @php $activeMaintenance = \App\Models\MaintenanceRecord::whereIn('status', ['scheduled', 'in_progress'])->count(); @endphp
                    <a href="{{ route('admin.maintenance.index') }}" class="{{ request()->routeIs('admin.maintenance.*') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="font-medium">Maintenance</span>
                        </div>
                        @if($activeMaintenance > 0)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#b45309;color:#fff;">{{ $activeMaintenance }}</span>
                        @endif
                    </a>
                    @endpermission {{-- end view_fleet --}}

                    <!-- Inventory -->
                    @permission('view_inventory', 'view_fuel_logs')
                    @php
                        $lowStockItems = \App\Models\InventoryItem::where('is_active', true)->whereColumn('current_stock', '<=', 'min_stock_level')->where('min_stock_level', '>', 0)->count();
                        $pendingPOs = \App\Models\PurchaseOrder::whereIn('status', ['draft', 'sent', 'partial'])->count();
                    @endphp
                    <div class="space-y-1">
                        <button
                            @click="inventoryOpen = !inventoryOpen"
                            class="group {{ request()->routeIs('admin.inventory.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.purchase-orders.*') || request()->routeIs('admin.fuel-logs.*') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between w-full px-3 py-2.5 rounded-lg transition-colors duration-150"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="font-medium">Inventory</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                @if($lowStockItems > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#d97706;color:#fff;">{{ $lowStockItems }}</span>
                                @endif
                                <svg class="w-4 h-4 transition-all duration-300" :class="{ 'rotate-180': inventoryOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>

                        <div
                            x-show="inventoryOpen"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="mt-1 space-y-1 border-l-2 border-red-100 ml-5 pl-2"
                        >
                            <a href="{{ route('admin.inventory.items.index') }}" class="{{ request()->routeIs('admin.inventory.items.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span class="font-medium">Stock Items</span>
                                </div>
                                @if($lowStockItems > 0)
                                    <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#fef9c3;color:#92400e;">{{ $lowStockItems }} low</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="{{ request()->routeIs('admin.purchase-orders.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span class="font-medium">Purchase Orders</span>
                                </div>
                                @if($pendingPOs > 0)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#dbeafe;color:#1e40af;">{{ $pendingPOs }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="font-medium">Suppliers</span>
                            </a>
                            <a href="{{ route('admin.fuel-logs.index') }}" class="{{ request()->routeIs('admin.fuel-logs.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="font-medium">Fuel Logs</span>
                            </a>
                            <a href="{{ route('admin.inventory.categories.index') }}" class="{{ request()->routeIs('admin.inventory.categories.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span class="font-medium">Categories</span>
                            </a>
                        </div>
                    </div>
                    @endpermission

                    <!-- Customers -->
                    @permission('view_clients')
                    <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="font-medium">Customers</span>
                        </div>
                        @php $totalClients = \App\Models\Client::count(); @endphp
                        @if($totalClients > 0)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ef4444;color:#fff;">{{ $totalClients }}</span>
                        @endif
                    </a>
                    @endpermission

                    <!-- Accounting -->
                    @permission('view_accounting', 'view_invoices', 'view_reports')
                    <div class="space-y-1">
                        <button
                            @click="accountingOpen = !accountingOpen"
                            class="group {{ request()->routeIs('admin.accounting.*') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between w-full px-3 py-2.5 rounded-lg transition-colors duration-150"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span class="font-medium">Accounting</span>
                            </div>
                            <svg class="w-4 h-4 transition-all duration-300" :class="{ 'rotate-180': accountingOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div
                            x-show="accountingOpen"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="mt-1 space-y-1 border-l-2 border-red-100 ml-5 pl-2"
                        >
                            @php
                                $totalInvoices   = \App\Models\Invoice::count();
                                $awaitingPayment = \App\Models\Invoice::whereIn('status', ['draft','sent','partially_paid'])->count();
                                $overdueInvoices = \App\Models\Invoice::whereIn('status', ['draft','sent','partially_paid'])->where('due_date', '<', now())->count();
                            @endphp

                            {{-- ── OPERATIONS ──────────────────────────────── --}}
                            <p class="px-3 pt-1 pb-0.5 text-xs font-bold uppercase tracking-widest text-gray-400">Operations</p>

                            <!-- Invoices sub-menu -->
                            <div>
                                <button @click="invoicesOpen = !invoicesOpen"
                                    class="w-full {{ request()->routeIs('admin.invoices.*') ? 'bg-red-100 text-red-700' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="font-medium">Invoices</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($overdueInvoices > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#dc2626;color:#fff;">{{ $overdueInvoices }}</span>
                                        @elseif($awaitingPayment > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#d97706;color:#fff;">{{ $awaitingPayment }}</span>
                                        @endif
                                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': invoicesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </button>
                                <div x-show="invoicesOpen" x-cloak class="ml-6 mt-1 space-y-0.5">
                                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.index') && !request('status') ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-red-600' }} flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors duration-150 hover:bg-red-50">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/></svg>
                                            All Invoices
                                        </span>
                                        @if($totalInvoices > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $totalInvoices }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}" class="{{ request()->routeIs('admin.invoices.index') && request('status') === 'sent' ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-red-600' }} flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors duration-150 hover:bg-red-50">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Awaiting Payment
                                        </span>
                                        @if($awaitingPayment > 0)
                                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#fef9c3;color:#854d0e;">{{ $awaitingPayment }}</span>
                                        @endif
                                    </a>
                                    @if($overdueInvoices > 0)
                                    <a href="{{ route('admin.invoices.index') }}?overdue=1" class="text-gray-500 hover:text-red-600 flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors duration-150 hover:bg-red-50">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                            Overdue
                                        </span>
                                        <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#fee2e2;color:#dc2626;">{{ $overdueInvoices }}</span>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="{{ request()->routeIs('admin.invoices.index') && request('status') === 'paid' ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-red-600' }} flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-colors duration-150 hover:bg-red-50">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Paid
                                    </a>
                                </div>
                            </div>

                            <a href="{{ route('admin.accounting.expenses.index') }}" class="{{ request()->routeIs('admin.accounting.expenses.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="font-medium">Expenses</span>
                            </a>
                            <a href="{{ route('admin.accounting.cash-requests.index') }}" class="{{ request()->routeIs('admin.accounting.cash-requests.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="font-medium">Cash Requests</span>
                            </a>
                            <a href="{{ route('admin.accounting.supplier-payments.index') }}" class="{{ request()->routeIs('admin.accounting.supplier-payments.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-medium">Supplier Payments</span>
                            </a>
                            <a href="{{ route('admin.accounting.credit-notes.index') }}" class="{{ request()->routeIs('admin.accounting.credit-notes.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                <span class="font-medium">Credit Notes</span>
                            </a>
                            <a href="{{ route('admin.accounting.journal-entries.index') }}" class="{{ request()->routeIs('admin.accounting.journal-entries.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span class="font-medium">Journal Entries</span>
                            </a>

                            {{-- ── REPORTS ──────────────────────────────────── --}}
                            <p class="px-3 pt-2 pb-0.5 text-xs font-bold uppercase tracking-widest text-gray-400">Reports</p>

                            <a href="{{ route('admin.accounting.tax-reports.vat') }}" class="{{ request()->routeIs('admin.accounting.tax-reports.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span class="font-medium">Tax Reports</span>
                            </a>
                            <a href="{{ route('admin.accounting.reports.aging') }}" class="{{ request()->routeIs('admin.accounting.reports.aging') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-medium">Aging Report</span>
                            </a>
                            <a href="{{ route('admin.accounting.reports.statement') }}" class="{{ request()->routeIs('admin.accounting.reports.statement') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-medium">Statement of Accounts</span>
                            </a>
                            <a href="{{ route('admin.accounting.reports.payables') }}" class="{{ request()->routeIs('admin.accounting.reports.payables') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h12M3 18h8"/></svg>
                                <span class="font-medium">Payables Register</span>
                            </a>

                            {{-- ── SETUP ──────────────────────────────────── --}}
                            <p class="px-3 pt-2 pb-0.5 text-xs font-bold uppercase tracking-widest text-gray-400">Setup</p>

                            <a href="{{ route('admin.accounting.accounts.index') }}" class="{{ request()->routeIs('admin.accounting.accounts.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span class="font-medium">Chart of Accounts</span>
                            </a>
                            <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="{{ request()->routeIs('admin.accounting.bank-accounts.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span class="font-medium">Cash &amp; Bank Accounts</span>
                            </a>
                        </div>
                    </div>
                    @endpermission

                    <!-- Settings (with Users submenu) -->
                    <div>
                        <button @click="settingsOpen = !settingsOpen"
                            class="{{ request()->routeIs('admin.users.*') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors duration-150">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="font-medium">Settings</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="settingsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="settingsOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="mt-1 ml-4 pl-3 border-l-2 border-gray-100 space-y-0.5">
                            @permission('manage_users')
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span class="font-medium">Users</span>
                            </a>
                            @endpermission
                            @permission('manage_permissions')
                            <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="font-medium">Roles</span>
                            </a>
                            <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'bg-red-600 text-white' : 'text-gray-600 hover:bg-red-50 hover:text-red-600' }} flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors duration-150">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span class="font-medium">Permissions</span>
                            </a>
                            @endpermission
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Overlay for Mobile -->
            <div 
                x-show="sidebarOpen" 
                @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
                style="display: none;"
            ></div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-h-screen">
                <!-- Top Navbar -->
                <header class="bg-white border-b border-gray-200 shadow-sm relative z-30">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Page Header Slot -->
                        @isset($header)
                            <div class="text-sm text-gray-600 font-medium">{{ $header }}</div>
                        @endisset
                        @isset($title)
                            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
                        @endisset

                        <div class="flex items-center gap-4 ml-auto">
                            <!-- Notifications -->
                            @php
                                $unreadNotifCount = \App\Models\AppNotification::where('is_read', false)
                                    ->where(fn($q) => $q->where('user_id', Auth::id())->orWhereNull('user_id'))
                                    ->count();
                            @endphp
                            <a href="{{ route('admin.notifications.index') }}" class="relative text-gray-400 hover:text-red-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($unreadNotifCount > 0)
                                <span class="absolute -top-1 -right-1 flex items-center justify-center h-4 w-4 rounded-full bg-red-600 text-white text-[10px] font-bold leading-none">
                                    {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                                </span>
                                @else
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-gray-300"></span>
                                @endif
                            </a>

                            <!-- User Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center gap-3 text-gray-700 hover:text-gray-900 transition-colors">
                                    <div class="hidden sm:block text-right">
                                        <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-400">Administrator</p>
                                    </div>
                                    <div class="bg-red-600 rounded-full p-2">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Dropdown Menu -->
                                <div 
                                    x-show="open" 
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1"
                                    style="display: none;"
                                >
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profile Settings
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
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
