<x-admin-layout>
    <x-slot name="header">Customers</x-slot>

    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Clients</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</div>
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                </div>
                <div class="text-sm text-gray-500 mt-1">Active</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="text-2xl font-bold text-gray-400">{{ number_format($stats['inactive']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Inactive</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="text-2xl font-bold text-red-600">{{ number_format($stats['blacklisted']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Blacklisted</div>
            </div>
        </div>

        <!-- Filters + New Button -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search name, company, email, phone..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blacklisted" {{ request('status') === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.clients.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-2 py-2">Clear</a>
                @endif
                <div class="ml-auto">
                    <a href="{{ route('admin.clients.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Client
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if($clients->count())
                <table class="w-full text-sm">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Client #</th>
                            <th class="px-4 py-3 text-left font-semibold">Name / Company</th>
                            <th class="px-4 py-3 text-left font-semibold">Contact</th>
                            <th class="px-4 py-3 text-left font-semibold">Bookings</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Risk</th>
                            <th class="px-4 py-3 text-left font-semibold">Since</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($clients as $client)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-purple-700 font-semibold text-xs">{{ $client->client_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $client->full_name }}</div>
                                    @if($client->company_name)
                                        <div class="text-xs text-gray-500">{{ $client->company_name }}</div>
                                    @endif
                                    @if($client->source === 'quote_request')
                                        <span class="text-xs text-blue-600">via Quote Request</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-700">{{ $client->email }}</div>
                                    <div class="text-xs text-gray-500">{{ $client->phone }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $client->bookings_count }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $sc = ['active' => 'green', 'inactive' => 'gray', 'blacklisted' => 'red'][$client->status] ?? 'gray';
                                        $sl = ['active' => 'bg-green-100 text-green-800', 'inactive' => 'bg-gray-100 text-gray-600', 'blacklisted' => 'bg-red-100 text-red-800'][$client->status] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sl }}">{{ ucfirst($client->status) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $rl = ['low' => 'bg-green-100 text-green-700', 'medium' => 'bg-amber-100 text-amber-700', 'high' => 'bg-red-100 text-red-700'][$client->risk_level] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $rl }}">{{ ucfirst($client->risk_level) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $client->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">View →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <x-pagination-bar :paginator="$clients" :per-page="$perPage" />
            @else
                <div class="text-center py-16 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="font-medium">No clients found</p>
                    <p class="text-sm mt-1">Clients are auto-created when a quotation is accepted, or you can add one manually.</p>
                    <a href="{{ route('admin.clients.create') }}" class="mt-4 inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors">
                        Add First Client
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
