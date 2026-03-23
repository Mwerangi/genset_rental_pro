<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Created Quotations</h1>
        <p class="text-slate-600 mt-1">Manage all quotations generated in the system</p>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-6 min-w-max md:min-w-0">
            <x-card class="flex-1 min-w-[200px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Quotations</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['total_quotations'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="flex-1 min-w-[200px]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Draft</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['draft'] }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="flex-1 min-w-[200px]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Sent</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['sent'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="flex-1 min-w-[200px]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Accepted</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['accepted'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="flex-1 min-w-[200px]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Total Value</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats['total_value'] / 1000000, 1) }}M</p>
                    <p class="text-xs text-slate-500 mt-1">TZS equiv.</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-card>
        </div>
    </div>

    <!-- Filters and Search -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.quotations.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-input 
                        type="text" 
                        name="search" 
                        placeholder="Search quotations..." 
                        value="{{ request('search') }}"
                        class="w-full"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-select name="status" class="w-full">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="viewed" {{ request('status') === 'viewed' ? 'selected' : '' }}>Viewed</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </x-select>
                </div>

                <!-- Date From -->
                <div>
                    <x-input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full"
                    />
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <x-button type="submit" class="flex-1">
                        Filter
                    </x-button>
                    <a href="{{ route('admin.quotations.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Quotations Table -->
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">All Quotations</h2>
            <a href="{{ route('admin.quotations.create') }}" class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Quotation
            </a>
        </div>

        @if($quotations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Quotation #</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Valid Until</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Created</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($quotations as $quotation)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-sm font-medium text-slate-900">{{ $quotation->quotation_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($quotation->quoteRequest)
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $quotation->quoteRequest->full_name }}</p>
                                            <p class="text-sm text-slate-600">{{ $quotation->quoteRequest->email }}</p>
                                        </div>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-900">{{ $quotation->formatted_total }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="text-slate-900">{{ $quotation->valid_until->format('M d, Y') }}</p>
                                        @if($quotation->isExpired())
                                            <p class="text-xs text-red-600 font-medium">Expired</p>
                                        @else
                                            <p class="text-xs text-slate-600">{{ $quotation->valid_until->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <x-badge :color="$quotation->status_color">
                                        {{ ucfirst($quotation->status) }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-4 text-sm text-slate-600">
                                    {{ $quotation->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                            View
                                        </a>
                                        <a href="{{ route('admin.quotations.download-pdf', $quotation) }}" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                            PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing {{ $quotations->firstItem() }} to {{ $quotations->lastItem() }} of {{ $quotations->total() }} results
                </div>
                <div>
                    {{ $quotations->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No quotations</h3>
                <p class="mt-1 text-sm text-slate-500">Get started by creating your first quotation.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.quotations.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create Quotation
                    </a>
                </div>
            </div>
        @endif
    </x-card>
</x-admin-layout>
