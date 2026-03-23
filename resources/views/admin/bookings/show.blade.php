<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.bookings.index') }}" class="text-gray-500 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $booking->booking_number }}</h1>
                <p class="text-gray-500 mt-1">Booking details &mdash; created {{ $booking->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        @php
            $statusStyles = [
                'created'   => 'background:#dbeafe;color:#1e40af;',
                'approved'  => 'background:#dcfce7;color:#166534;',
                'active'    => 'background:#fef9c3;color:#854d0e;',
                'returned'  => 'background:#f3e8ff;color:#6b21a8;',
                'invoiced'  => 'background:#ffedd5;color:#9a3412;',
                'paid'      => 'background:#dcfce7;color:#14532d;',
                'cancelled' => 'background:#fee2e2;color:#991b1b;',
                'rejected'  => 'background:#fee2e2;color:#991b1b;',
            ];
            $statusStyle = $statusStyles[$booking->status] ?? 'background:#f3f4f6;color:#374151;';
        @endphp
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold" style="{{ $statusStyle }}">
            {{ $booking->status_label }}
        </span>
        @if($booking->currency === 'USD')
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" style="background:#eff6ff;color:#1d4ed8;">USD &mdash; @ {{ number_format($booking->exchange_rate_to_tzs, 0) }} TZS</span>
        @endif
    </div>

    {{-- ================================================================
         LIFECYCLE PROGRESS TRACKER
    ================================================================ --}}
    @php
        $lifecycle = ['created','approved','active','returned','paid'];
        $currentIdx = array_search($booking->status, $lifecycle);
        $isCancelled = in_array($booking->status, ['cancelled','rejected']);
    @endphp

    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
        @if($isCancelled)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#fee2e2;">
                    <svg class="w-5 h-5" style="color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Booking {{ $booking->status_label }}</p>
                    @if($booking->cancellation_reason)
                        <p class="text-sm text-gray-500">Reason: {{ $booking->cancellation_reason }}</p>
                    @endif
                    @if($booking->cancelled_at)
                        <p class="text-xs text-gray-400">{{ $booking->cancelled_at->format('M d, Y \a\t H:i') }}{{ $booking->cancelledBy ? ' by ' . $booking->cancelledBy->name : '' }}</p>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center">
                @foreach($lifecycle as $idx => $step)
                    @php
                        $stepLabels = [
                            'created'  => 'Created',
                            'approved' => 'Approved',
                            'active'   => 'Active',
                            'returned' => 'Returned',
                            'paid'     => 'Paid',
                        ];
                        $isDone    = $currentIdx !== false && $idx < $currentIdx;
                        $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                        $isPending = $currentIdx !== false && $idx > $currentIdx;
                    @endphp

                    {{-- Step circle --}}
                    <div class="flex flex-col items-center flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all
                            @if($isDone) border-green-500 bg-green-500 text-white
                            @elseif($isCurrent) border-red-600 bg-red-600 text-white
                            @else border-gray-200 bg-white text-gray-400
                            @endif">
                            @if($isDone)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <p class="text-xs mt-1.5 font-medium truncate w-full text-center
                            @if($isDone) text-green-600
                            @elseif($isCurrent) text-red-600
                            @else text-gray-400
                            @endif">
                            {{ $stepLabels[$step] }}
                        </p>
                    </div>

                    {{-- Connector line (not after last step) --}}
                    @if($idx < count($lifecycle) - 1)
                        <div class="h-0.5 flex-1 mx-1 mb-5 rounded
                            @if($currentIdx !== false && $idx < $currentIdx) bg-green-400
                            @else bg-gray-200
                            @endif">
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Customer & Rental Info -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Rental Details</h2>
                <div class="grid grid-cols-2 gap-6">
                    @if($booking->quoteRequest)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Customer</p>
                            <p class="text-slate-900 mt-1">{{ $booking->quoteRequest->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Company</p>
                            <p class="text-slate-900 mt-1">{{ $booking->quoteRequest->company_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Email</p>
                            <p class="text-slate-900 mt-1">{{ $booking->quoteRequest->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Phone</p>
                            <p class="text-slate-900 mt-1">{{ $booking->quoteRequest->phone }}</p>
                        </div>
                    @elseif($booking->client)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Client</p>
                            <p class="text-slate-900 mt-1">
                                <a href="{{ route('admin.clients.show', $booking->client) }}" class="text-red-600 hover:underline font-medium">{{ $booking->client->company_name ?? $booking->client->name }}</a>
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-medium text-slate-600">Generator Type</p>
                        <p class="text-slate-900 font-medium mt-1">{{ $booking->quoteRequest?->genset_type_formatted ?? $booking->genset_type ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-600">Duration</p>
                        <p class="text-slate-900 mt-1">{{ $booking->rental_duration_days ?? '—' }} days</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-600">Start Date</p>
                        <p class="text-slate-900 mt-1">{{ $booking->rental_start_date?->format('F d, Y') ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-600">End Date</p>
                        <p class="text-slate-900 mt-1">{{ $booking->rental_end_date?->format('F d, Y') ?? '—' }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-sm font-medium text-slate-600">Delivery Location</p>
                        <p class="text-slate-900 mt-1">{{ $booking->delivery_location ?? '—' }}</p>
                    </div>

                    @if($booking->pickup_location)
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-slate-600">Pickup Location</p>
                            <p class="text-slate-900 mt-1">{{ $booking->pickup_location }}</p>
                        </div>
                    @endif

                    @if($booking->invoice_number)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Invoice Number</p>
                            <p class="font-mono font-bold text-slate-900 mt-1">{{ $booking->invoice_number }}</p>
                        </div>
                    @endif

                    @if($booking->payment_reference)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Payment Reference</p>
                            <p class="font-mono text-slate-900 mt-1">{{ $booking->payment_reference }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Linked Quotation -->
            @if($booking->quotation)
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Quotation</h2>
                        <a href="{{ route('admin.quotations.show', $booking->quotation) }}" class="text-sm font-medium text-red-600 hover:text-red-800">View Full Quotation</a>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Quotation #</p>
                            <p class="font-mono text-sm font-bold text-slate-900 mt-1">{{ $booking->quotation->quotation_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Subtotal</p>
                            <p class="text-sm text-slate-900 mt-1">{{ $booking->quotation->formatAmount($booking->quotation->subtotal, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">VAT ({{ $booking->quotation->vat_rate }}%)</p>
                            <p class="text-sm text-slate-900 mt-1">{{ $booking->quotation->formatAmount($booking->quotation->vat_amount, 0) }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-semibold text-slate-900">Total Amount</span>
                        <span class="text-xl font-bold text-slate-900">{{ $booking->formatted_total }}</span>
                    </div>

                    @if($booking->quotation->items->count() > 0)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-slate-600 mb-3">Line Items</p>
                            <div class="space-y-2">
                                @foreach($booking->quotation->items as $item)
                                    <div class="flex items-center justify-between text-sm py-2 border-b border-slate-50">
                                        <div>
                                            <span class="text-slate-900">{{ $item->description }}</span>
                                            <span class="text-slate-500 ml-2 text-xs">× {{ $item->quantity }}{{ $item->duration_days ? ' × ' . $item->duration_days . ' days' : '' }}</span>
                                        </div>
                                        <span class="font-medium text-slate-900">{{ $booking->quotation->formatAmount($item->subtotal, 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-card>
            @endif

            <!-- Notes -->
            @if($booking->notes)
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-3">Notes</h2>
                    <p class="text-slate-700 whitespace-pre-line">{{ $booking->notes }}</p>
                </x-card>
            @endif

            {{-- ─── Deliveries Section ─────────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Deliveries & Returns</h2>
                    @if(in_array($booking->status, ['approved', 'active', 'returned']))
                        <button onclick="document.getElementById('add-delivery-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white" style="background:#dc2626;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Delivery Order
                        </button>
                    @endif
                </div>
                @if($booking->deliveries->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        No delivery orders created yet.
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Driver</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($booking->deliveries as $dlv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $dlv->delivery_number }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        style="{{ $dlv->type === 'delivery' ? 'background:#ede9fe;color:#5b21b6;' : 'background:#fce7f3;color:#9d174d;' }}">
                                        {{ $dlv->type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-700">{{ $dlv->driver_name ?: '—' }}</td>
                                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $dlv->scheduled_at ? $dlv->scheduled_at->format('d M Y H:i') : '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $dlv->status_style }}">
                                        {{ $dlv->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="{{ route('admin.deliveries.show', $dlv) }}" class="text-xs text-red-600 hover:underline font-medium">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Activity Log -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-5">Activity Log</h2>
                <div class="space-y-0">
                    @php
                        $events = collect();

                        $events->push(['icon' => 'plus', 'color' => 'blue', 'title' => 'Booking created', 'at' => $booking->created_at, 'by' => $booking->createdBy?->name]);

                        if ($booking->approved_at)
                            $events->push(['icon' => 'check', 'color' => 'green', 'title' => 'Booking approved', 'at' => $booking->approved_at, 'by' => $booking->approvedBy?->name]);

                        if (in_array($booking->status, ['rejected']) && $booking->cancelled_at && !$booking->activated_at)
                            $events->push(['icon' => 'x', 'color' => 'red', 'title' => 'Booking rejected' . ($booking->cancellation_reason ? ': ' . $booking->cancellation_reason : ''), 'at' => $booking->cancelled_at, 'by' => $booking->cancelledBy?->name]);

                        if ($booking->activated_at)
                            $events->push(['icon' => 'bolt', 'color' => 'yellow', 'title' => 'Genset deployed — booking activated', 'at' => $booking->activated_at, 'by' => $booking->activatedBy?->name]);

                        if ($booking->returned_at)
                            $events->push(['icon' => 'return', 'color' => 'purple', 'title' => 'Genset returned', 'at' => $booking->returned_at, 'by' => $booking->returnedBy?->name]);

                        if ($booking->invoiced_at)
                            $events->push(['icon' => 'doc', 'color' => 'orange', 'title' => 'Invoice issued' . ($booking->invoice_number ? ' — ' . $booking->invoice_number : ''), 'at' => $booking->invoiced_at, 'by' => $booking->invoicedBy?->name]);

                        if ($booking->paid_at)
                            $events->push(['icon' => 'cash', 'color' => 'green', 'title' => 'Payment confirmed' . ($booking->payment_reference ? ' — Ref: ' . $booking->payment_reference : ''), 'at' => $booking->paid_at, 'by' => $booking->paidBy?->name]);

                        if ($booking->status === 'cancelled' && $booking->cancelled_at)
                            $events->push(['icon' => 'x', 'color' => 'red', 'title' => 'Booking cancelled' . ($booking->cancellation_reason ? ': ' . $booking->cancellation_reason : ''), 'at' => $booking->cancelled_at, 'by' => $booking->cancelledBy?->name]);

                        $events = $events->sortBy('at');
                    @endphp

                    @foreach($events as $i => $event)
                        <div class="flex gap-4 {{ !$loop->last ? 'pb-5' : '' }}">
                            <div class="flex flex-col items-center">
                                @php
                                    $iconBg = match($event['color']) {
                                        'green'  => 'background:#dcfce7;',
                                        'blue'   => 'background:#dbeafe;',
                                        'red'    => 'background:#fee2e2;',
                                        'yellow' => 'background:#fef9c3;',
                                        'purple' => 'background:#f3e8ff;',
                                        'orange' => 'background:#ffedd5;',
                                        default  => 'background:#f3f4f6;',
                                    };
                                    $iconColor = match($event['color']) {
                                        'green'  => 'color:#16a34a;',
                                        'blue'   => 'color:#2563eb;',
                                        'red'    => 'color:#dc2626;',
                                        'yellow' => 'color:#ca8a04;',
                                        'purple' => 'color:#9333ea;',
                                        'orange' => 'color:#ea580c;',
                                        default  => 'color:#6b7280;',
                                    };
                                @endphp
                                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="{{ $iconBg }}">
                                    @if($event['icon'] === 'check')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($event['icon'] === 'x')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($event['icon'] === 'bolt')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                                    @elseif($event['icon'] === 'return')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    @elseif($event['icon'] === 'doc')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($event['icon'] === 'cash')
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" style="{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                    <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                                @endif
                            </div>
                            <div class="pt-1.5 pb-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $event['title'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $event['at']->format('M d, Y \a\t H:i') }}
                                    @if($event['by']) &mdash; {{ $event['by'] }} @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Actions</h2>
                </div>
                <div class="px-5 py-4 space-y-3">

                    {{-- CREATED: Approve or Reject --}}
                    @if($booking->canBeApproved())
                        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#16a34a;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                                ✓ Approve Booking
                            </button>
                        </form>
                        <button type="button" onclick="document.getElementById('modal-reject').classList.remove('hidden')"
                            class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm border border-red-300 text-red-600 hover:bg-red-50 transition">
                            ✕ Reject Booking
                        </button>
                    @endif

                    {{-- APPROVED: Generate Invoice + Deploy --}}
                    @if($booking->canBeActivated())
                        @if(!$booking->invoice_id)
                            <button type="button" onclick="document.getElementById('modal-generate-invoice').classList.remove('hidden')" class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#ea580c;" onmouseover="this.style.background='#c2410c'" onmouseout="this.style.background='#ea580c'">
                                📄 Generate Invoice
                            </button>
                        @else
                            <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="block w-full px-4 py-2.5 rounded-lg text-center font-semibold text-sm" style="background:#fef9c3;color:#854d0e;border:1px solid #fcd34d;">
                                📄 View Invoice
                            </a>
                        @endif
                        <button type="button" onclick="document.getElementById('modal-deploy').classList.remove('hidden')"
                            class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#dc2626;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                            ⚡ Deploy Genset — Mark Active
                        </button>
                    @endif

                    {{-- ACTIVE: Generate or View Invoice + Mark Returned --}}
                    @if($booking->canBeMarkedReturned())
                        @if($booking->invoice_id)
                            <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="block w-full px-4 py-2.5 rounded-lg text-center font-semibold text-sm" style="background:#fef9c3;color:#854d0e;border:1px solid #fcd34d;">
                                📄 View Invoice
                            </a>
                        @else
                            <button type="button" onclick="document.getElementById('modal-generate-invoice').classList.remove('hidden')" class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#ea580c;" onmouseover="this.style.background='#c2410c'" onmouseout="this.style.background='#ea580c'">
                                📄 Generate Invoice
                            </button>
                        @endif
                        <form method="POST" action="{{ route('admin.bookings.return', $booking) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#9333ea;" onmouseover="this.style.background='#7e22ce'" onmouseout="this.style.background='#9333ea'">
                                ↩ Mark as Returned
                            </button>
                        </form>
                    @endif

                    {{-- RETURNED: View or Generate Invoice --}}
                    @if($booking->status === 'returned')
                        @if($booking->invoice_id)
                            <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="block w-full px-4 py-2.5 rounded-lg text-center font-semibold text-sm" style="background:#fef9c3;color:#854d0e;border:1px solid #fcd34d;">
                                📄 View Invoice
                            </a>
                        @else
                            <button type="button" onclick="document.getElementById('modal-generate-invoice').classList.remove('hidden')" class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#ea580c;" onmouseover="this.style.background='#c2410c'" onmouseout="this.style.background='#ea580c'">
                                📄 Generate Invoice
                            </button>
                        @endif
                    @endif

                    {{-- INVOICED: Mark Paid --}}
                    @if($booking->canBeMarkedPaid())
                        <button type="button" onclick="document.getElementById('modal-paid').classList.remove('hidden')"
                            class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm text-white transition" style="background:#16a34a;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                            💰 Mark as Paid
                        </button>
                    @endif

                    {{-- PAID: View Invoice --}}
                    @if($booking->status === 'paid')
                        @if($booking->invoice_id)
                            <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="block w-full px-4 py-2.5 rounded-lg text-center font-semibold text-sm" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;">
                                📄 View Invoice
                            </a>
                        @endif
                        <div class="text-center py-2 text-sm text-green-700 font-semibold">
                            ✓ Booking complete — fully paid
                        </div>
                    @endif

                    {{-- Cancel (available for created/approved/active) --}}
                    @if($booking->canBeCancelled())
                        <button type="button" onclick="document.getElementById('modal-cancel').classList.remove('hidden')"
                            class="w-full px-4 py-2.5 rounded-lg font-semibold text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                            Cancel Booking
                        </button>
                    @endif

                    {{-- Static links --}}
                    @if($booking->quoteRequest)
                        <a href="{{ route('admin.quote-requests.show', $booking->quoteRequest) }}" class="block w-full px-4 py-2.5 rounded-lg text-center text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            View Prospect
                        </a>
                    @endif

                    @if($booking->quotation)
                        <a href="{{ route('admin.quotations.download-pdf', $booking->quotation) }}" class="block w-full px-4 py-2.5 rounded-lg text-center text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            Download Quotation PDF
                        </a>
                    @endif
                </div>
            </div>

            {{-- Assigned Genset Card --}}
            @if($booking->genset)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">⚡ Assigned Genset</h2>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#dbeafe;color:#1e40af;">Rented</span>
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Asset #</span>
                        <a href="{{ route('admin.gensets.show', $booking->genset) }}" class="font-bold font-mono text-red-600 hover:underline">{{ $booking->genset->asset_number }}</a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Name</span>
                        <span class="font-medium text-gray-800 text-right max-w-36 truncate">{{ $booking->genset->name }}</span>
                    </div>
                    @if($booking->genset->power_rating)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Power</span>
                        <span class="text-gray-800">{{ $booking->genset->power_rating }}</span>
                    </div>
                    @endif
                    @if($booking->genset->brand)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Brand</span>
                        <span class="text-gray-800">{{ $booking->genset->brand }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($booking->status === 'approved')
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                <p class="font-semibold mb-1">⚠ No genset assigned</p>
                <p class="text-xs">Click "Deploy Genset" above to assign an available genset and activate this booking.</p>
            </div>
            @endif

            {{-- Linked Invoice Card --}}
            @if($booking->invoice)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">📄 Invoice</h2>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="{{ $booking->invoice->status_style }}">{{ $booking->invoice->status_label }}</span>
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Invoice #</span>
                        <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="font-bold font-mono text-red-600 hover:underline">{{ $booking->invoice->invoice_number }}</a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Due Date</span>
                        <span class="text-gray-700">{{ $booking->invoice->due_date->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="font-medium text-gray-800">{{ $booking->invoice->formatAmount($booking->invoice->total_amount, 0) }}</span>
                    </div>
                    @if($booking->invoice->amount_paid > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Paid</span>
                        <span class="font-semibold text-green-700">{{ $booking->invoice->formatAmount($booking->invoice->amount_paid, 0) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-100 pt-2 mt-1">
                        <span class="font-semibold text-gray-700">Balance Due</span>
                        @if($booking->invoice->balance_due > 0)
                            <span class="font-bold" style="color:#dc2626;">{{ $booking->invoice->formatAmount($booking->invoice->balance_due, 0) }}</span>
                        @else
                            <span class="font-bold text-green-700">Paid ✓</span>
                        @endif
                    </div>
                    @if($booking->invoice->is_overdue)
                    <p class="text-xs font-semibold mt-1" style="color:#dc2626;">⚠ Overdue</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Booking Info -->
            <x-card>
                <h2 class="text-base font-semibold text-slate-900 mb-4">Booking Info</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-medium text-slate-500">Booking Number</p>
                        <p class="font-mono font-bold text-slate-900 mt-1">{{ $booking->booking_number }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-500">Status</p>
                        <span class="inline-flex mt-1 items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $statusStyle }}">
                            {{ $booking->status_label }}
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-slate-500">Total Value</p>
                        <p class="font-bold text-slate-900 mt-1">{{ $booking->formatted_total }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-500">Created</p>
                        <p class="text-slate-900 mt-1">{{ $booking->created_at->format('M d, Y') }}</p>
                    </div>
                    @if($booking->createdBy)
                        <div>
                            <p class="font-medium text-slate-500">Created By</p>
                            <p class="text-slate-900 mt-1">{{ $booking->createdBy->name }}</p>
                        </div>
                    @endif
                    @if($booking->client)
                        <div>
                            <p class="font-medium text-slate-500">Client</p>
                            <a href="{{ route('admin.clients.show', $booking->client) }}" class="text-red-600 hover:underline font-medium mt-1 block">
                                {{ $booking->client->company_name ?? $booking->client->name }}
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    {{-- ================================================================
         MODALS
    ================================================================ --}}

    {{-- Reject Modal --}}
    <div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Reject Booking</h3>
            <p class="text-sm text-gray-500 mb-4">{{ $booking->booking_number }} will be rejected. You may provide a reason below.</p>
            <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                @csrf
                <textarea name="reason" rows="3" placeholder="Reason for rejection (optional)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4"></textarea>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Reject Booking</button>
                </div>
            </form>
        </div>
    </div>



    {{-- Mark Paid Modal --}}
    <div id="modal-paid" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Mark as Paid</h3>
            <p class="text-sm text-gray-500 mb-4">Confirm payment receipt for <strong>{{ $booking->booking_number }}</strong>. Total: <strong>{{ $booking->formatted_total }}</strong></p>
            <form method="POST" action="{{ route('admin.bookings.mark-paid', $booking) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Reference <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="payment_reference" placeholder="e.g. M-Pesa TXN ID, bank ref..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4">
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-paid').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#16a34a;">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div id="modal-cancel" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Cancel Booking</h3>
            <p class="text-sm text-gray-500 mb-4">This will cancel <strong>{{ $booking->booking_number }}</strong>. Please provide a reason.</p>
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason</label>
                <textarea name="reason" rows="3" placeholder="Reason for cancellation..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4"></textarea>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-cancel').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Keep Booking</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Deploy Genset Modal --}}
    @if($booking->canBeActivated())
    <div id="modal-deploy" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">⚡ Deploy Genset</h3>
            <p class="text-sm text-gray-500 mb-4">Select an available genset to assign to <strong>{{ $booking->booking_number }}</strong>. The booking will become active and the genset will be marked as rented.</p>
            <form method="POST" action="{{ route('admin.bookings.activate', $booking) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Genset <span class="text-red-500">*</span></label>
                @if($availableGensets->isEmpty())
                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 mb-4">
                        ⚠ No gensets are currently available. Please update fleet status before deploying.
                    </div>
                    <button type="button" onclick="document.getElementById('modal-deploy').classList.add('hidden')" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
                @else
                    <select name="genset_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4">
                        <option value="">— Choose a genset —</option>
                        @foreach($availableGensets as $g)
                            <option value="{{ $g->id }}">{{ $g->asset_number }} — {{ $g->name }} ({{ $g->power_rating }})@if($g->location) · {{ $g->location }}@endif</option>
                        @endforeach
                    </select>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-deploy').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">⚡ Deploy &amp; Activate</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
    @endif

    {{-- Generate Invoice Confirmation Modal --}}
    @if(!$booking->invoice_id && in_array($booking->status, ['approved','active','returned']))
    <div id="modal-generate-invoice" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b" style="background:#fff7ed;">
                <h3 class="font-bold text-gray-900 text-lg">📄 Generate Invoice</h3>
                <button onclick="document.getElementById('modal-generate-invoice').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 mb-4">The following invoice will be generated from this booking. Please review the details before confirming.</p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2.5 text-sm mb-5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Booking</span>
                        <span class="font-mono font-bold text-gray-800">{{ $booking->booking_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Client</span>
                        <span class="font-medium text-gray-800">{{ $booking->client?->company_name ?? $booking->client?->name ?? $booking->quoteRequest?->full_name ?? '—' }}</span>
                    </div>
                    @if($booking->quotation)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Quotation</span>
                        <span class="font-mono text-gray-700">{{ $booking->quotation->quotation_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="text-gray-700">{{ $booking->quotation->formatAmount($booking->quotation->subtotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">VAT ({{ $booking->quotation->vat_rate }}%)</span>
                        @if($booking->quotation->vat_rate == 0)
                            <span class="font-medium" style="color:#15803d;">Zero Rated</span>
                        @else
                            <span class="text-gray-700">{{ $booking->quotation->formatAmount($booking->quotation->vat_amount, 0) }}</span>
                        @endif
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-1">
                        <span class="font-semibold text-gray-700">Invoice Total</span>
                        <span class="font-bold text-gray-900 text-base">{{ $booking->formatted_total }}</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mb-5">An invoice number (INV-YYYY-XXXX) will be auto-generated. Line items will be copied from the linked quotation.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-generate-invoice').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="{{ route('admin.bookings.generate-proforma', $booking) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-amber-800 border border-amber-400 hover:bg-amber-50">Generate Proforma</button>
                    </form>
                    <form method="POST" action="{{ route('admin.bookings.generate-invoice', $booking) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#ea580c;">Confirm — Generate Tax Invoice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Delivery Modal --}}
    @if(in_array($booking->status, ['approved', 'active', 'returned']))
    <div id="add-delivery-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <h3 class="font-bold text-gray-900 text-lg">Create Delivery Order</h3>
                <button onclick="document.getElementById('add-delivery-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.deliveries.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <input type="hidden" name="genset_id" value="{{ $booking->genset_id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span style="color:#dc2626;">*</span></label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="delivery">Delivery (Genset → Client)</option>
                        <option value="return">Return Pickup (Genset ← Client)</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driver Name</label>
                        <input type="text" name="driver_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driver Phone</label>
                        <input type="text" name="driver_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Details</label>
                    <input type="text" name="vehicle_details" placeholder="e.g. TZ 123 ABC — Toyota Hilux" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destination Address</label>
                    <input type="text" name="destination_address" value="{{ $booking->delivery_location }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Any delivery instructions..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('add-delivery-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Create Order</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-admin-layout>
