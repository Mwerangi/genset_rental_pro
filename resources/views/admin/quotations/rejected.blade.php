<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Rejected Quotations</h1>
        <p class="text-slate-600 mt-1">Quotations that were declined by the customer</p>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-6 min-w-max md:min-w-0">
            <x-card class="flex-1 min-w-[200px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Rejected</p>
                        <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="flex-1 min-w-[200px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">This Month</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['this_month'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Search & Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.quotations.rejected') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[220px]">
                <x-input type="text" name="search" placeholder="Search by quotation # or customer..." value="{{ request('search') }}" class="w-full" />
            </div>
            <div>
                <x-input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full" />
            </div>
            <div>
                <x-input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full" />
            </div>
            <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">Filter</button>
            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <a href="{{ route('admin.quotations.rejected') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition font-medium">Reset</a>
            @endif
        </form>
    </x-card>

    <!-- Table -->
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">All Rejected Quotations</h2>
        </div>

        @if($quotations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Quotation #</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Amount</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Rejected On</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Reason</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quotations as $quotation)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-sm font-semibold text-slate-900">{{ $quotation->quotation_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($quotation->client)
                                        <p class="font-medium text-slate-900 text-sm">{{ $quotation->client->company_name ?? $quotation->client->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $quotation->client->client_number }}</p>
                                    @elseif($quotation->quoteRequest)
                                        <p class="font-medium text-slate-900 text-sm">{{ $quotation->quoteRequest->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $quotation->quoteRequest->company_name ?? $quotation->quoteRequest->email }}</p>
                                    @elseif($quotation->customer_name)
                                        <p class="font-medium text-slate-900 text-sm">{{ $quotation->customer_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $quotation->company_name ?? $quotation->customer_email ?? '' }}</p>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-semibold text-slate-700 text-sm">{{ $quotation->formatted_total }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($quotation->rejected_at)
                                        <p class="text-sm text-slate-900">{{ $quotation->rejected_at->format('M d, Y') }}</p>
                                        <p class="text-xs text-slate-500">{{ $quotation->rejected_at->diffForHumans() }}</p>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 max-w-[220px]">
                                    @if($quotation->rejection_reason)
                                        <p class="text-sm text-slate-600 truncate" title="{{ $quotation->rejection_reason }}">
                                            {{ $quotation->rejection_reason }}
                                        </p>
                                    @else
                                        <span class="text-slate-400 text-sm">No reason provided</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                <p>Showing {{ $quotations->firstItem() }} to {{ $quotations->lastItem() }} of {{ $quotations->total() }} results</p>
                {{ $quotations->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-slate-900">No rejected quotations</h3>
                <p class="mt-1 text-sm text-slate-500">Rejected quotations will appear here.</p>
            </div>
        @endif
    </x-card>
</x-admin-layout>
