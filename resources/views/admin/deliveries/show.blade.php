<x-admin-layout>
    @php
        $statusTimeline = ['pending', 'dispatched', 'completed'];
        $currentIdx     = array_search($delivery->status, $statusTimeline);
        $isFailed       = $delivery->status === 'failed';
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.deliveries.index') }}" class="text-gray-400 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $delivery->delivery_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" style="{{ $delivery->status_style }}">{{ $delivery->status_label }}</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                        style="{{ $delivery->type === 'delivery' ? 'background:#ede9fe;color:#5b21b6;' : 'background:#fce7f3;color:#9d174d;' }}">
                        {{ $delivery->type_label }}
                    </span>
                </div>
                <p class="text-gray-500 mt-1 text-sm">Created {{ $delivery->created_at->format('M d, Y') }}{{ $delivery->createdBy ? ' by ' . $delivery->createdBy->name : '' }}</p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2 flex-wrap">
            @permission('dispatch_deliveries')
            @if($delivery->status === 'pending')
                <button onclick="document.getElementById('dispatch-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1e40af;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3"/></svg>
                    Dispatch
                </button>
            @endif
            @endpermission
            @permission('complete_deliveries')
            @if(in_array($delivery->status, ['pending', 'dispatched']))
                <button onclick="document.getElementById('complete-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#166534;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark Completed
                </button>
                <button onclick="document.getElementById('fail-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-red-300 text-red-600 hover:bg-red-50">
                    Mark Failed
                </button>
            @endif
            @endpermission
        </div>
    </div>

    {{-- Progress tracker --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
        @if($isFailed)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#fee2e2;">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Failed</p>
                    @if($delivery->notes)<p class="text-sm text-gray-500 mt-0.5">{{ $delivery->notes }}</p>@endif
                </div>
            </div>
        @else
            <div class="flex items-center">
                @php $stepLabels = ['pending' => 'Pending', 'dispatched' => 'Dispatched', 'completed' => 'Completed']; @endphp
                @foreach($statusTimeline as $idx => $step)
                    @php
                        $isDone    = $currentIdx !== false && $idx < $currentIdx;
                        $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                    @endphp
                    <div class="flex flex-col items-center flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2
                            @if($isDone) border-green-500 bg-green-500 text-white
                            @elseif($isCurrent) border-red-600 bg-red-600 text-white
                            @else border-gray-200 bg-white text-gray-400 @endif">
                            @if($isDone)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <p class="text-xs mt-1.5 font-medium truncate w-full text-center
                            @if($isDone) text-green-600 @elseif($isCurrent) text-red-600 @else text-gray-400 @endif">
                            {{ $stepLabels[$step] }}
                        </p>
                    </div>
                    @if($idx < count($statusTimeline) - 1)
                        <div class="h-0.5 flex-1 mx-1 mb-5 rounded {{ ($currentIdx !== false && $idx < $currentIdx) ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Delivery Info --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Delivery Details</h2>
                </div>
                <div class="p-5 grid grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Booking</p>
                        @if($delivery->booking)
                            <a href="{{ route('admin.bookings.show', $delivery->booking) }}" class="mt-1 font-medium text-red-600 hover:underline block">
                                {{ $delivery->booking->booking_number }}
                            </a>
                            @if($delivery->booking->client)
                                <p class="text-xs text-gray-400">{{ $delivery->booking->client->name }}</p>
                            @endif
                        @else
                            <p class="mt-1 text-gray-400">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Genset</p>
                        @if($delivery->genset)
                            <a href="{{ route('admin.gensets.show', $delivery->genset) }}" class="mt-1 font-medium text-red-600 hover:underline block">
                                {{ $delivery->genset->asset_number }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $delivery->genset->name }}</p>
                        @else
                            <p class="mt-1 text-gray-400">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Origin</p>
                        <p class="mt-1 text-gray-800 text-sm">{{ $delivery->origin_address ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Destination</p>
                        <p class="mt-1 text-gray-800 text-sm">{{ $delivery->destination_address ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Scheduled At</p>
                        <p class="mt-1 text-gray-800 text-sm">{{ $delivery->scheduled_at ? $delivery->scheduled_at->format('d M Y H:i') : '—' }}</p>
                    </div>
                    @if($delivery->dispatched_at)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Dispatched At</p>
                        <p class="mt-1 text-gray-800 text-sm">{{ $delivery->dispatched_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                    @if($delivery->completed_at)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Completed At</p>
                        <p class="mt-1 font-semibold text-green-700 text-sm">{{ $delivery->completed_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                </div>
                @if($delivery->notes)
                <div class="px-5 pb-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Notes</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $delivery->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Driver Info --}}
            @if($delivery->driver_name || $delivery->driver_phone || $delivery->vehicle_details)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Driver & Vehicle</h2>
                </div>
                <div class="p-5 grid grid-cols-3 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Driver Name</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $delivery->driver_name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Driver Phone</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $delivery->driver_phone ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Vehicle</p>
                        <p class="mt-1 text-gray-800">{{ $delivery->vehicle_details ?: '—' }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Proof of Delivery --}}
            @if($delivery->status === 'completed')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100" style="background:#f0fdf4;">
                    <h2 class="font-semibold text-green-800">Proof of Delivery</h2>
                </div>
                <div class="p-5 grid grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Signed By</p>
                        <p class="mt-1 font-medium text-gray-800">{{ $delivery->pod_signed_by ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Completed At</p>
                        <p class="mt-1 font-medium text-green-700">{{ $delivery->completed_at->format('d M Y H:i') }}</p>
                    </div>
                    @if($delivery->pod_notes)
                    <div class="col-span-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">POD Notes</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $delivery->pod_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs" style="background:#dc2626;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Created</p>
                            <p class="text-xs text-gray-400">{{ $delivery->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @if($delivery->dispatched_at)
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs" style="background:#1e40af;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Dispatched</p>
                            <p class="text-xs text-gray-400">{{ $delivery->dispatched_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($delivery->completed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs" style="background:#166534;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Completed</p>
                            <p class="text-xs text-gray-400">{{ $delivery->completed_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Modals ──────────────────────────────────────────────────────────── --}}

    {{-- Dispatch Modal --}}
    @if($delivery->status === 'pending')
    <div id="dispatch-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b" style="background:#eff6ff;">
                <h3 class="font-bold text-gray-900 text-lg">Dispatch Delivery</h3>
                <button onclick="document.getElementById('dispatch-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.deliveries.dispatch', $delivery) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driver Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="driver_name" value="{{ $delivery->driver_name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driver Phone</label>
                    <input type="text" name="driver_phone" value="{{ $delivery->driver_phone }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Details</label>
                    <input type="text" name="vehicle_details" value="{{ $delivery->vehicle_details }}" placeholder="e.g. TZ 123 ABC — Toyota Hilux" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Time</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ $delivery->scheduled_at ? $delivery->scheduled_at->format('Y-m-d\TH:i') : '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('dispatch-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1e40af;">Dispatch</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Complete Modal --}}
    @if(in_array($delivery->status, ['pending', 'dispatched']))
    <div id="complete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b" style="background:#f0fdf4;">
                <h3 class="font-bold text-gray-900 text-lg">Mark as Completed</h3>
                <button onclick="document.getElementById('complete-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.deliveries.complete', $delivery) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signed / Received By</label>
                    <input type="text" name="pod_signed_by" placeholder="Name of person who received" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">POD Notes</label>
                    <textarea name="pod_notes" rows="3" placeholder="Any notes about the delivery condition..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#166534;">Confirm Complete</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Fail Modal --}}
    <div id="fail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b" style="background:#fff7f7;">
                <h3 class="font-bold text-gray-900 text-lg">Mark as Failed</h3>
                <button onclick="document.getElementById('fail-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.deliveries.fail', $delivery) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for failure</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Client not available, access denied..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('fail-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Confirm Failed</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-admin-layout>
