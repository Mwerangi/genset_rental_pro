<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Active Rentals</h1>
            <p class="text-gray-500 mt-1">All gensets currently deployed on active bookings</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
            All Bookings
        </a>
    </div>



    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Rentals</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $rentals->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gensets on Rent</p>
            <p class="text-3xl font-bold mt-1" style="color:#2563eb;">{{ $rentals->filter(fn($r) => $r->genset)->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Value (Active)</p>
            @php $totalValue = $rentals->sum('total_amount'); @endphp
            <p class="text-2xl font-bold text-gray-900 mt-1">TZS {{ number_format($totalValue, 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Days Active</p>
            @php
                $avgDays = $rentals->where('activated_at', '!=', null)->avg(fn($r) => $r->activated_at->diffInDays(now()));
            @endphp
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $avgDays ? round($avgDays) : 0 }}</p>
        </div>
    </div>

    @if($rentals->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <p class="text-gray-500 font-medium">No active rentals right now</p>
                <p class="text-gray-400 text-sm mt-1">Approved bookings will appear here once a genset is deployed</p>
                <a href="{{ route('admin.bookings.index', ['status' => 'approved']) }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">View Approved Bookings</a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($rentals as $rental)
            @php
                $daysActive = $rental->activated_at ? $rental->activated_at->diffInDays(now()) : 0;
                $eventEnd = $rental->event_end_date;
                $isOverdue = $eventEnd && now()->isAfter($eventEnd);
                $daysUntilEnd = $eventEnd ? now()->diffInDays($eventEnd, false) : null;
            @endphp
            <div class="bg-white border {{ $isOverdue ? 'border-red-200' : 'border-gray-200' }} rounded-xl shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-b {{ $isOverdue ? 'border-red-100 bg-red-50' : 'border-gray-100' }}">
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.bookings.show', $rental) }}" class="font-bold text-red-600 hover:underline text-base">{{ $rental->booking_number }}</a>
                                @if($isOverdue)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fee2e2;color:#991b1b;">⚠ OVERDUE</span>
                                @elseif($daysUntilEnd !== null && $daysUntilEnd <= 2)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fef9c3;color:#854d0e;">Due Soon</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $rental->client?->company_name ?? $rental->client?->name ?? $rental->quoteRequest?->full_name ?? '—' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">Active {{ $daysActive }} day{{ $daysActive != 1 ? 's' : '' }}</span>
                        <a href="{{ route('admin.bookings.show', $rental) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium">View Booking</a>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100">
                    {{-- Genset --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Genset</p>
                        @if($rental->genset)
                            <a href="{{ route('admin.gensets.show', $rental->genset) }}" class="font-bold font-mono text-red-600 hover:underline text-sm">{{ $rental->genset->asset_number }}</a>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $rental->genset->name }}</p>
                            @if($rental->genset->power_rating)
                                <p class="text-xs text-gray-400">{{ $rental->genset->power_rating }}</p>
                            @endif
                        @else
                            <span class="text-sm text-gray-400 italic">Not assigned</span>
                        @endif
                    </div>

                    {{-- Deployment dates --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Deployed</p>
                        <p class="text-sm font-medium text-gray-800">{{ $rental->activated_at ? $rental->activated_at->format('d M Y') : '—' }}</p>
                        @if($rental->activatedBy)
                            <p class="text-xs text-gray-400 mt-0.5">by {{ $rental->activatedBy->name }}</p>
                        @endif
                    </div>

                    {{-- Event period --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Event Period</p>
                        <p class="text-sm font-medium {{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-800' }}">
                            {{ $rental->event_start_date ? $rental->event_start_date->format('d M') : '—' }}
                            @if($rental->event_end_date) – {{ $rental->event_end_date->format('d M Y') }} @endif
                        </p>
                        @if($daysUntilEnd !== null)
                            @if($isOverdue)
                                <p class="text-xs mt-0.5" style="color:#dc2626;">{{ abs($daysUntilEnd) }} day{{ abs($daysUntilEnd) != 1 ? 's' : '' }} overdue</p>
                            @else
                                <p class="text-xs text-gray-400 mt-0.5">{{ $daysUntilEnd }} day{{ $daysUntilEnd != 1 ? 's' : '' }} remaining</p>
                            @endif
                        @endif
                    </div>

                    {{-- Value + Return action --}}
                    <div class="px-5 py-4 flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Value</p>
                            <p class="text-sm font-bold text-gray-900">{{ $rental->formatted_total ?? 'TZS —' }}</p>
                        </div>
                        @if($rental->canBeMarkedReturned())
                        <form method="POST" action="{{ route('admin.bookings.return', $rental) }}" onsubmit="return confirm('Mark {{ $rental->booking_number }} as returned? This will free the genset.')">
                            @csrf
                            <button type="submit" class="mt-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold border border-purple-200 text-purple-700 hover:bg-purple-50 transition">
                                ↩ Mark Returned
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
