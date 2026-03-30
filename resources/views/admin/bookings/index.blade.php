<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bookings</h1>
            <p class="text-slate-600 mt-1">All confirmed rental bookings</p>
        </div>
        @permission('view_quotations')
        <a href="{{ route('admin.quotations.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Quotation
        </a>
        @endpermission
    </div>

    <!-- Stats -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-6 min-w-max md:min-w-0">
            <x-card class="flex-1 min-w-[160px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="flex-1 min-w-[160px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Pending Approval</p>
                        <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['created'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="flex-1 min-w-[160px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Approved</p>
                        <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['approved'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="flex-1 min-w-[160px]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active</p>
                        <p class="text-3xl font-bold text-teal-600 mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[220px]">
                <x-input type="text" name="search" placeholder="Search by booking #, customer..." value="{{ request('search') }}" class="w-full" />
            </div>
            <div>
                <select name="status" class="px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="created" {{ request('status') === 'created' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="invoiced" {{ request('status') === 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.bookings.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition font-medium">Reset</a>
            @endif
        </form>
    </x-card>

    <!-- Table -->
    <x-card>
        @if($bookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Booking #</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Genset</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Rental Period</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bookings as $booking)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-sm font-bold text-slate-900">{{ $booking->booking_number }}</span>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $booking->created_at->format('M d, Y') }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    @if($booking->quoteRequest)
                                        <p class="font-medium text-slate-900 text-sm">{{ $booking->quoteRequest->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $booking->quoteRequest->company_name ?? $booking->quoteRequest->email }}</p>
                                    @elseif($booking->client)
                                        <p class="font-medium text-slate-900 text-sm">{{ $booking->client->company_name ?? $booking->client->name }}</p>
                                    @elseif($booking->customer_name)
                                        <p class="font-medium text-slate-900 text-sm">{{ $booking->customer_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $booking->company_name ?? $booking->customer_email ?? '' }}</p>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-slate-700">
                                    {{ $booking->quoteRequest?->genset_type_formatted ?? $booking->genset_type ?? '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($booking->rental_start_date)
                                        <p class="text-sm text-slate-900">{{ $booking->rental_start_date->format('M d') }} – {{ $booking->rental_end_date?->format('M d, Y') ?? '?' }}</p>
                                        <p class="text-xs text-slate-500">{{ $booking->rental_duration_days }} days</p>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-semibold text-sm text-slate-900">{{ $booking->formatted_total }}</span>
                                    @if($booking->currency === 'USD')
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold" style="background:#eff6ff;color:#1d4ed8;">USD</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <x-badge :color="$booking->status_color">{{ $booking->status_label }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                <p>Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results</p>
                {{ $bookings->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-slate-900">No bookings yet</h3>
                <p class="mt-1 text-sm text-slate-500">Bookings will appear here once a quotation is accepted.</p>
            </div>
        @endif
    </x-card>
</x-admin-layout>
