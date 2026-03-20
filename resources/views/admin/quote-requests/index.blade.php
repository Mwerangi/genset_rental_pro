<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Quote Requests</h1>
        <p class="text-slate-600 mt-1">Manage and review incoming quote requests</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">New Requests</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['total_new'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">This Week</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['this_week'] }}</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Pending Quotes</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['pending_quotes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Conversion Rate</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['conversion_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters and Search -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.quote-requests.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-input 
                        type="text" 
                        name="search" 
                        placeholder="Search requests..." 
                        value="{{ request('search') }}"
                        class="w-full"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-select name="status" class="w-full">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="quoted" {{ request('status') === 'quoted' ? 'selected' : '' }}>Quoted</option>
                    </x-select>
                </div>

                <!-- Generator Type Filter -->
                <div>
                    <x-select name="genset_type" class="w-full">
                        <option value="all" {{ request('genset_type') === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="10kva" {{ request('genset_type') === '10kva' ? 'selected' : '' }}>10 KVA</option>
                        <option value="20kva" {{ request('genset_type') === '20kva' ? 'selected' : '' }}>20 KVA</option>
                        <option value="30kva" {{ request('genset_type') === '30kva' ? 'selected' : '' }}>30 KVA</option>
                        <option value="45kva" {{ request('genset_type') === '45kva' ? 'selected' : '' }}>45 KVA</option>
                        <option value="60kva" {{ request('genset_type') === '60kva' ? 'selected' : '' }}>60 KVA</option>
                        <option value="100kva" {{ request('genset_type') === '100kva' ? 'selected' : '' }}>100+ KVA</option>
                    </x-select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <x-button type="submit" class="flex-1">
                        Filter
                    </x-button>
                    <a href="{{ route('admin.quote-requests.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </div>

            <!-- Date Range -->
            <div class="flex gap-4 items-center">
                <div class="flex-1">
                    <x-input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full"
                    />
                </div>
                <span class="text-slate-500">to</span>
                <div class="flex-1">
                    <x-input 
                        type="date" 
                        name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full"
                    />
                </div>
                <div class="flex-1"></div>
                <div class="flex-1"></div>
            </div>
        </form>
    </x-card>

    <!-- Quote Requests Table -->
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">All Quote Requests</h2>
            <form method="GET" action="{{ route('admin.quote-requests.export') }}" class="inline">
                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                    Export CSV
                </button>
            </form>
        </div>

        @if($quoteRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Request #</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Company</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Generator</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Start Date</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Duration</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Created</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($quoteRequests as $request)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-sm font-medium text-slate-900">{{ $request->request_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $request->full_name }}</p>
                                        <p class="text-sm text-slate-600">{{ $request->email }}</p>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-900">
                                    {{ $request->company_name ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm font-medium text-slate-900">{{ $request->genset_type_formatted }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-900">
                                    {{ $request->rental_start_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-slate-900">{{ $request->rental_duration_days }} days</span>
                                </td>
                                <td class="py-3 px-4">
                                    <x-badge :color="$request->status_color">
                                        {{ ucfirst($request->status) }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-4 text-sm text-slate-600">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('admin.quote-requests.show', $request->id) }}" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing {{ $quoteRequests->firstItem() }} to {{ $quoteRequests->lastItem() }} of {{ $quoteRequests->total() }} results
                </div>
                <div>
                    {{ $quoteRequests->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No quote requests</h3>
                <p class="mt-1 text-sm text-slate-500">Get started by receiving your first quote request.</p>
            </div>
        @endif
    </x-card>
</x-admin-layout>
