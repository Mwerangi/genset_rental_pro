<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Deliveries</h1>
            <p class="text-gray-500 mt-1">Outgoing deliveries and return pickups</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-3xl font-bold mt-1" style="color:#b45309;">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Dispatched</p>
            <p class="text-3xl font-bold mt-1" style="color:#1e40af;">{{ $stats['dispatched'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-3xl font-bold mt-1" style="color:#166534;">{{ $stats['completed'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search delivery #, driver, booking #..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Statuses</option>
                    <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                    <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="failed"     {{ request('status') === 'failed'     ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Types</option>
                    <option value="delivery" {{ request('type') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                    <option value="return"   {{ request('type') === 'return'   ? 'selected' : '' }}>Return Pickup</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request()->hasAny(['search', 'status', 'type']))
                <a href="{{ route('admin.deliveries.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($deliveries->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>
                <p class="text-sm">No delivery orders found</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Booking</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Genset</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Driver</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($deliveries as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $delivery->delivery_number }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                style="{{ $delivery->type === 'delivery' ? 'background:#ede9fe;color:#5b21b6;' : 'background:#fce7f3;color:#9d174d;' }}">
                                {{ $delivery->type_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($delivery->booking)
                                <a href="{{ route('admin.bookings.show', $delivery->booking) }}" class="text-red-600 hover:underline font-medium">{{ $delivery->booking->booking_number }}</a>
                                @if($delivery->booking->client)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $delivery->booking->client->name }}</p>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $delivery->genset->asset_number ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-700">{{ $delivery->driver_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $delivery->status_style }}">
                                {{ $delivery->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-sm text-red-600 hover:underline font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($deliveries->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $deliveries->links() }}
                </div>
            @endif
        @endif
    </div>
</x-admin-layout>
