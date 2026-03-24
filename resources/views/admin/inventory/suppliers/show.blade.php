<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.suppliers.index') }}" class="hover:text-red-600">Suppliers</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">{{ $supplier->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                @if($supplier->supplier_number)
                    <span class="text-xs font-mono font-semibold bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ $supplier->supplier_number }}</span>
                @endif
                @if($supplier->category)
                    @php
                        $catClass = match($supplier->category) {
                            'fuel'      => 'bg-orange-100 text-orange-700',
                            'parts'     => 'bg-blue-100 text-blue-700',
                            'services'  => 'bg-purple-100 text-purple-700',
                            'equipment' => 'bg-cyan-100 text-cyan-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="text-xs font-semibold px-2 py-1 rounded {{ $catClass }}">{{ ucfirst($supplier->category) }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New PO
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: supplier details --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Status + Contact --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Contact & Location</h2>
                    @if($supplier->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Inactive</span>
                    @endif
                </div>
                <dl class="space-y-3 text-sm">
                    @if($supplier->contact_person)
                    <div class="flex gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <div><dt class="text-gray-400 text-xs">Contact Person</dt><dd class="text-gray-800 font-medium">{{ $supplier->contact_person }}</dd></div>
                    </div>
                    @endif
                    @if($supplier->phone)
                    <div class="flex gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <div>
                            <dt class="text-gray-400 text-xs">Phone</dt>
                            <dd class="text-gray-800">{{ $supplier->phone }}{{ $supplier->phone_alt ? ' / '.$supplier->phone_alt : '' }}</dd>
                        </div>
                    </div>
                    @endif
                    @if($supplier->email)
                    <div class="flex gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <div><dt class="text-gray-400 text-xs">Email</dt><dd><a href="mailto:{{ $supplier->email }}" class="text-red-600 hover:underline">{{ $supplier->email }}</a></dd></div>
                    </div>
                    @endif
                    @if($supplier->website)
                    <div class="flex gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                        <div><dt class="text-gray-400 text-xs">Website</dt><dd><a href="{{ $supplier->website }}" target="_blank" class="text-red-600 hover:underline truncate block max-w-[180px]">{{ $supplier->website }}</a></dd></div>
                    </div>
                    @endif
                    @if($supplier->address || $supplier->city || $supplier->country)
                    <div class="flex gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <div>
                            <dt class="text-gray-400 text-xs">Location</dt>
                            <dd class="text-gray-800">{{ implode(', ', array_filter([$supplier->address, $supplier->city, $supplier->country])) }}</dd>
                        </div>
                    </div>
                    @endif
                </dl>
                @if($supplier->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-medium text-gray-500 mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $supplier->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Tax & Compliance --}}
            @if($supplier->tin_number || $supplier->vrn_number)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Tax & Compliance</h2>
                <dl class="space-y-3 text-sm">
                    @if($supplier->tin_number)
                    <div>
                        <dt class="text-xs text-gray-400">TIN Number</dt>
                        <dd class="text-gray-800 font-mono font-medium mt-0.5">{{ $supplier->tin_number }}</dd>
                    </div>
                    @endif
                    @if($supplier->vrn_number)
                    <div>
                        <dt class="text-xs text-gray-400">VRN Number <span class="font-sans font-normal">(VAT Registration)</span></dt>
                        <dd class="text-gray-800 font-mono font-medium mt-0.5">{{ $supplier->vrn_number }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            {{-- Banking --}}
            @if($supplier->bank_name || $supplier->bank_account_number)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Banking Details</h2>
                <dl class="space-y-3 text-sm">
                    @if($supplier->bank_name)
                    <div><dt class="text-xs text-gray-400">Bank</dt><dd class="text-gray-800 font-medium mt-0.5">{{ $supplier->bank_name }}</dd></div>
                    @endif
                    @if($supplier->bank_account_name)
                    <div><dt class="text-xs text-gray-400">Account Name</dt><dd class="text-gray-800 mt-0.5">{{ $supplier->bank_account_name }}</dd></div>
                    @endif
                    @if($supplier->bank_account_number)
                    <div><dt class="text-xs text-gray-400">Account Number</dt><dd class="text-gray-800 font-mono mt-0.5">{{ $supplier->bank_account_number }}</dd></div>
                    @endif
                    @if($supplier->payment_terms || $supplier->currency)
                    <div class="pt-2 border-t border-gray-100 flex gap-4">
                        @if($supplier->payment_terms)
                        <div><dt class="text-xs text-gray-400">Terms</dt><dd class="text-gray-800 text-sm mt-0.5">{{ $supplier->payment_terms }}</dd></div>
                        @endif
                        @if($supplier->currency)
                        <div><dt class="text-xs text-gray-400">Currency</dt><dd class="text-gray-800 font-semibold text-sm mt-0.5">{{ $supplier->currency }}</dd></div>
                        @endif
                    </div>
                    @endif
                </dl>
            </div>
            @endif

        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Recent Purchase Orders --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Purchase Orders</h2>
                    <a href="{{ route('admin.purchase-orders.index') }}?supplier_id={{ $supplier->id }}"
                       class="text-xs text-red-600 hover:underline">View all</a>
                </div>

                @if($supplier->purchaseOrders->isEmpty())
                    <div class="px-5 py-12 text-center text-gray-400 text-sm">
                        No purchase orders yet.
                        <a href="{{ route('admin.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="block mt-2 text-red-600 hover:underline">Create first PO</a>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">PO Number</th>
                                <th class="px-5 py-3 text-left font-medium">Status</th>
                                <th class="px-5 py-3 text-left font-medium">Items</th>
                                <th class="px-5 py-3 text-left font-medium">Ordered</th>
                                <th class="px-5 py-3 text-left font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->purchaseOrders as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $po->po_number }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $statusColors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$po->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $po->items->count() }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $po->ordered_at ? $po->ordered_at->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.purchase-orders.show', $po) }}" class="text-red-600 hover:underline text-xs font-medium">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Recent Payments --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Payments</h2>
                    <span class="text-xs text-gray-400">Total paid: <strong class="text-gray-700">{{ number_format($stats['total_paid'], 2) }}</strong></span>
                </div>

                @if($supplier->payments->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">No payments recorded yet.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">Date</th>
                                <th class="px-5 py-3 text-left font-medium">Reference</th>
                                <th class="px-5 py-3 text-left font-medium">Method</th>
                                <th class="px-5 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-600">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-3 text-gray-800 font-medium">{{ $payment->reference ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ ucfirst($payment->payment_method ?? '—') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Summary --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Summary</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Total Orders</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Pending / Sent</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['received_orders'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Received</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_paid'], 2) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Total Paid</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
