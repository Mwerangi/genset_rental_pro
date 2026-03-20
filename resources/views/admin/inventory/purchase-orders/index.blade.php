<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
            <p class="text-gray-500 mt-1">Procurement from suppliers</p>
        </div>
        <a href="{{ route('admin.purchase-orders.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New PO
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Draft</p>
            <p class="text-3xl font-bold mt-1 text-gray-900">{{ $stats['draft'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-3xl font-bold mt-1" style="color:#b45309;">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Received</p>
            <p class="text-3xl font-bold mt-1" style="color:#166534;">{{ $stats['received'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO #, supplier..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">All Statuses</option>
                <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
                <option value="sent"     {{ request('status') === 'sent'     ? 'selected' : '' }}>Sent</option>
                <option value="partial"  {{ request('status') === 'partial'  ? 'selected' : '' }}>Partial</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                <option value="cancelled"{{ request('status') === 'cancelled'? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.purchase-orders.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400 text-sm">No purchase orders found.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">PO #</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Expected</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Created</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $order->po_number }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $order->supplier?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $order->expected_at ? $order->expected_at->format('d M Y') : '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $order->status_style }}">{{ $order->status_label }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.purchase-orders.show', $order) }}" class="text-sm text-red-600 hover:underline font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($orders->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
</x-admin-layout>
