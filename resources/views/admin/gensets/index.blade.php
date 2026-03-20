<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fleet Management</h1>
            <p class="text-gray-500 mt-1">All generators in the Milele Power fleet</p>
        </div>
        <a href="{{ route('admin.gensets.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition" style="background:#dc2626;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Genset
        </a>
    </div>



    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Fleet</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Available</p>
            <p class="text-3xl font-bold mt-1" style="color:#16a34a;">{{ $stats['available'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">On Rent</p>
            <p class="text-3xl font-bold mt-1" style="color:#2563eb;">{{ $stats['rented'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Maintenance</p>
            <p class="text-3xl font-bold mt-1" style="color:#d97706;">{{ $stats['maintenance'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.gensets.index') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Asset#, name, brand, serial..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="all">All Statuses</option>
                    <option value="available" @selected(request('status') === 'available')>Available</option>
                    <option value="rented" @selected(request('status') === 'rented')>On Rent</option>
                    <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
                    <option value="repair" @selected(request('status') === 'repair')>Under Repair</option>
                    <option value="reserved" @selected(request('status') === 'reserved')>Reserved</option>
                    <option value="retired" @selected(request('status') === 'retired')>Retired</option>
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="all">All Types</option>
                    <option value="clip-on" @selected(request('type') === 'clip-on')>Clip-on</option>
                    <option value="underslung" @selected(request('type') === 'underslung')>Underslung</option>
                    <option value="open-frame" @selected(request('type') === 'open-frame')>Open Frame</option>
                    <option value="canopy" @selected(request('type') === 'canopy')>Canopy</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request('search') || request('status') || request('type'))
                <a href="{{ route('admin.gensets.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($gensets->isEmpty())
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <p class="text-gray-500 font-medium">No gensets found</p>
                <a href="{{ route('admin.gensets.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Add your first genset</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Asset</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Type / Power</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Location</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Rates (TZS/day)</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Bookings</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($gensets as $genset)
                        @php
                            $statusStyles = [
                                'available'   => 'background:#dcfce7;color:#166534;',
                                'rented'      => 'background:#dbeafe;color:#1e40af;',
                                'maintenance' => 'background:#fef9c3;color:#854d0e;',
                                'repair'      => 'background:#ffedd5;color:#9a3412;',
                                'retired'     => 'background:#f3f4f6;color:#6b7280;',
                                'reserved'    => 'background:#f3e8ff;color:#6b21a8;',
                            ];
                            $ss = $statusStyles[$genset->status] ?? 'background:#f3f4f6;color:#374151;';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.gensets.show', $genset) }}" class="font-bold text-red-600 hover:underline">{{ $genset->asset_number }}</a>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $genset->name }}</p>
                                @if($genset->brand)
                                    <p class="text-xs text-gray-400">{{ $genset->brand }}{{ $genset->model ? ' ' . $genset->model : '' }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $genset->type_formatted }}</p>
                                <p class="text-xs text-gray-500">{{ $genset->power_rating }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap" style="{{ $ss }}">
                                    {{ $genset->status_label }}
                                </span>
                                @if($genset->service_due)
                                    <p class="text-xs mt-1" style="color:#dc2626;">⚠ Service overdue</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $genset->location ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                @if($genset->daily_rate)
                                    {{ number_format($genset->daily_rate, 0) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $genset->bookings_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.gensets.show', $genset) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium">View</a>
                                    <a href="{{ route('admin.gensets.edit', $genset) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($gensets->hasPages())
                <div class="px-4 py-4 border-t border-gray-100">
                    {{ $gensets->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</x-admin-layout>
