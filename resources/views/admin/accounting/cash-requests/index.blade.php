<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cash Requests</h1>
            <p class="text-gray-500 mt-1">Petty cash advance requests linked to expenses</p>
        </div>
        @permission('create_cash_requests')
        <a href="{{ route('admin.accounting.cash-requests.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Request
        </a>
        @endpermission
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Draft</p>
            <p class="text-2xl font-bold text-gray-500 mt-1">{{ $stats['draft'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending Approval</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Approved (Ready to Pay)</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paid This Month</p>
            <p class="text-2xl font-bold text-green-700 mt-1">Tsh {{ number_format($stats['paid_this_month'], 0) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.accounting.cash-requests.index') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Request #, purpose, requestor…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all">All Statuses</option>
                    @foreach(['draft','pending','approved','paid','rejected'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-44">
                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-28">
                <label class="block text-xs font-medium text-gray-600 mb-1">Per Page</label>
                <select name="per_page" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" @selected(request('per_page', 25) == $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Filter</button>
            <a href="{{ route('admin.accounting.cash-requests.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Request #</th>
                    @if($seeAll)
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Requested By</th>
                    @endif
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Purpose</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Items</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Net</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">VAT</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Expense Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $cr)
                @php
                    $statusColors = [
                        'draft'    => 'bg-gray-100 text-gray-600',
                        'pending'  => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                        'approved' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                        'paid'     => 'bg-green-50 text-green-700 ring-1 ring-green-200',
                        'retired'  => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-50 text-red-700',
                    ];
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="font-mono text-xs font-semibold text-gray-800">{{ $cr->request_number }}</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $cr->created_at->format('d M Y') }}</p>
                    </td>
                    @if($seeAll)
                    <td class="px-4 py-3">
                        <span class="text-gray-800">{{ $cr->requestedBy?->name ?? '—' }}</span>
                        @if($cr->approved_by)
                        <p class="text-xs text-gray-400 mt-0.5">Appr: {{ $cr->approvedBy?->name }}</p>
                        @endif
                    </td>
                    @endif
                    <td class="px-4 py-3 max-w-[200px]">
                        <span class="text-gray-700 leading-snug line-clamp-2">{{ $cr->purpose }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($cr->expenseCategory)
                        <span class="text-xs text-gray-600">{{ $cr->expenseCategory->name }}</span>
                        @if($cr->expenseCategory->is_zero_rated)
                        <span class="ml-1 text-xs text-emerald-600 font-medium">★</span>
                        @endif
                        @else
                        <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $cr->items_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-xs text-gray-700 whitespace-nowrap">
                        {{ number_format((float)$cr->amount ?: ((float)$cr->total_amount - (float)$cr->vat_amount), 0) }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-xs whitespace-nowrap">
                        @if($cr->is_zero_rated)
                        <span class="text-emerald-600 text-xs font-medium">Exempt</span>
                        @else
                        <span class="text-gray-500">{{ number_format((float)$cr->vat_amount, 0) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <span class="font-semibold font-mono text-gray-900">{{ number_format((float)$cr->total_amount, 0) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$cr->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($cr->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $cr->expense_date ? \Carbon\Carbon::parse($cr->expense_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.accounting.cash-requests.show', $cr) }}"
                           class="text-xs text-blue-600 hover:underline font-medium">View</a>
                        @if($cr->status === 'draft')
                        <a href="{{ route('admin.accounting.cash-requests.edit', $cr) }}"
                           class="ml-2 text-xs text-gray-500 hover:underline">Edit</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $seeAll ? 11 : 10 }}" class="px-4 py-12 text-center text-gray-400">No cash requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($requests->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $requests->links() }}</div>
        @endif
    </div>
</x-admin-layout>
