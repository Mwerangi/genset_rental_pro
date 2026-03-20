<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
            <p class="text-gray-500 mt-1">Vendors for parts, consumables and fuel</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Supplier
        </a>
    </div>

    {{-- Search --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, contact, email..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($suppliers->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400 text-sm">
                No suppliers found.
                <a href="{{ route('admin.suppliers.create') }}" class="block mt-2 text-red-600 hover:underline">Add first supplier</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Phone / Email</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">City</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">POs</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($suppliers as $sup)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-semibold text-gray-800">{{ $sup->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $sup->contact_person ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($sup->phone) <p>{{ $sup->phone }}</p> @endif
                            @if($sup->email) <p>{{ $sup->email }}</p> @endif
                            @if(!$sup->phone && !$sup->email) — @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $sup->city ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-700">{{ $sup->purchase_orders_count }}</td>
                        <td class="px-5 py-3">
                            @if($sup->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#dcfce7;color:#166534;">Active</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.suppliers.edit', $sup) }}" class="text-sm text-red-600 hover:underline font-medium">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($suppliers->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $suppliers->links() }}</div>
            @endif
        @endif
    </div>
</x-admin-layout>
