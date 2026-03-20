<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Welcome Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-600 mt-1">Here's what's happening with your generator rental business today.</p>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- New Quote Requests -->
        <x-card class="hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">New Requests</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ \App\Models\QuoteRequest::where('status', 'new')->count() }}</h3>
                    <p class="text-sm text-green-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        +12% from last week
                    </p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Active Rentals -->
        <x-card class="hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Active Rentals</p>
                    <h3 class="text-3xl font-bold text-gray-900">24</h3>
                    <p class="text-sm text-gray-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        8 ending this week
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Total Revenue -->
        <x-card class="hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Revenue (Month)</p>
                    <h3 class="text-3xl font-bold text-gray-900">TZS 45.2M</h3>
                    <p class="text-sm text-green-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        +23% from last month
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Available Generators -->
        <x-card class="hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Available Units</p>
                    <h3 class="text-3xl font-bold text-gray-900">18/42</h3>
                    <p class="text-sm text-yellow-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        43% utilization
                    </p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Quote Requests -->
        <div class="lg:col-span-2">
            <x-card title="Recent Quote Requests" class="h-full">
                <x-table>
                    <x-table-head>
                        <x-table-row>
                            <x-table-header>Request #</x-table-header>
                            <x-table-header>Customer</x-table-header>
                            <x-table-header>Generator Type</x-table-header>
                            <x-table-header>Status</x-table-header>
                            <x-table-header>Date</x-table-header>
                        </x-table-row>
                    </x-table-head>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse(\App\Models\QuoteRequest::latest()->take(5)->get() as $request)
                            <x-table-row striped>
                                <x-table-cell class="font-medium text-red-600">{{ $request->request_number }}</x-table-cell>
                                <x-table-cell>{{ $request->full_name }}</x-table-cell>
                                <x-table-cell>{{ $request->genset_type_formatted }}</x-table-cell>
                                <x-table-cell>
                                    <x-badge :variant="$request->status">
                                        {{ ucfirst($request->status) }}
                                    </x-badge>
                                </x-table-cell>
                                <x-table-cell class="text-gray-600">{{ $request->created_at->format('M d, Y') }}</x-table-cell>
                            </x-table-row>
                        @empty
                            <x-table-row>
                                <x-table-cell colspan="5" class="text-center text-gray-500 py-8">
                                    No quote requests yet
                                </x-table-cell>
                            </x-table-row>
                        @endforelse
                    </tbody>
                </x-table>
                
                @if(\App\Models\QuoteRequest::count() > 0)
                    <div class="mt-4">
                        <x-button href="#" variant="ghost" size="sm">
                            View All Requests
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </x-button>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Quick Actions -->
        <div>
            <x-card title="Quick Actions" class="h-full">
                <div class="space-y-3">
                    <x-button href="#" variant="primary" class="w-full justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Rental
                    </x-button>
                    
                    <x-button href="#" variant="secondary" class="w-full justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Add Customer
                    </x-button>
                    
                    <x-button href="#" variant="ghost" class="w-full justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        View Reports
                    </x-button>
                </div>

                <!-- System Status -->
                <div class="mt-6 pt-6 border-t">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">System Status</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Database</span>
                            <span class="flex items-center gap-1 text-green-600 font-medium">
                                <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Email Service</span>
                            <span class="flex items-center gap-1 text-green-600 font-medium">
                                <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">API Status</span>
                            <span class="flex items-center gap-1 text-green-600 font-medium">
                                <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                Online
                            </span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-admin-layout>
