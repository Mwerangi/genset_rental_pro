<x-admin-layout>
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.maintenance.index') }}" class="hover:text-red-600">Maintenance</a>
                <span>/</span>
                <span class="font-mono">{{ $maintenance->maintenance_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $maintenance->title }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $maintenance->status_style }}">{{ $maintenance->status_label }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $maintenance->priority_style }}">{{ $maintenance->priority_label }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $maintenance->type_label }}</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($maintenance->status === 'scheduled')
                @permission('start_maintenance')
                <button onclick="document.getElementById('startModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Start Work</button>
                @endpermission
                @permission('edit_maintenance')
                <a href="{{ route('admin.maintenance.edit', $maintenance) }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Edit</a>
                @endpermission
                @permission('cancel_maintenance')
                <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-red-300 text-red-600 hover:bg-red-50">Cancel</button>
                @endpermission
            @elseif($maintenance->status === 'in_progress')
                @permission('complete_maintenance')
                <button onclick="document.getElementById('completeModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#16a34a;">Mark Completed</button>
                @endpermission
                @permission('cancel_maintenance')
                <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-red-300 text-red-600 hover:bg-red-50">Cancel</button>
                @endpermission
            @endif
        </div>
    </div>

    {{-- Progress Tracker --}}
    @php
        $steps = ['scheduled' => 0, 'in_progress' => 1, 'completed' => 2];
        $currentStep = $steps[$maintenance->status] ?? ($maintenance->status === 'cancelled' ? -1 : 2);
    @endphp
    @if($maintenance->status !== 'cancelled')
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center">
            @foreach([['Scheduled','scheduled'],['In Progress','in_progress'],['Completed','completed']] as $i => [$label, $key])
                @php $done = ($currentStep > $i); $active = ($currentStep === $i); @endphp
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all
                        {{ $done ? 'text-white border-transparent' : ($active ? 'text-white border-transparent' : 'text-gray-400 border-gray-200 bg-white') }}"
                        style="{{ $done ? 'background:#16a34a;' : ($active ? 'background:#b45309;' : '') }}">
                        @if($done) <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else {{ $i + 1 }} @endif
                    </div>
                    <p class="text-xs font-semibold mt-1.5 {{ $done ? '' : ($active ? '' : 'text-gray-400') }}"
                       style="{{ $done ? 'color:#16a34a;' : ($active ? 'color:#b45309;' : '') }}">{{ $label }}</p>
                </div>
                @if($i < 2)
                    <div class="flex-1 h-0.5 mx-1" style="{{ $done ? 'background:#16a34a;' : 'background:#e5e7eb;' }}"></div>
                @endif
            @endforeach
        </div>
    </div>
    @else
    <div class="border rounded-xl p-4 mb-6 text-sm flex items-center gap-3" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>This maintenance record has been <strong>cancelled</strong>.</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Maintenance Details --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Maintenance Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Record #</dt>
                        <dd class="font-mono font-semibold text-gray-800">{{ $maintenance->maintenance_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Type</dt>
                        <dd class="text-gray-800">{{ $maintenance->type_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Priority</dt>
                        <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $maintenance->priority_style }}">{{ $maintenance->priority_label }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Scheduled Date</dt>
                        <dd class="text-gray-800">{{ $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d M Y') : '—' }}</dd>
                    </div>
                    @if($maintenance->started_at)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Work Started</dt>
                        <dd class="text-gray-800">{{ $maintenance->started_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if($maintenance->completed_at)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Completed At</dt>
                        <dd class="text-gray-800">{{ $maintenance->completed_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if($maintenance->run_hours_at_service)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Run Hours at Service</dt>
                        <dd class="text-gray-800">{{ number_format($maintenance->run_hours_at_service) }} hrs</dd>
                    </div>
                    @endif
                    @if($maintenance->booking)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Linked Booking</dt>
                        <dd><a href="{{ route('admin.bookings.show', $maintenance->booking) }}" class="text-red-600 hover:underline font-mono text-xs">{{ $maintenance->booking->booking_number }}</a></dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 mb-0.5">Reported At</dt>
                        <dd class="text-gray-800">{{ $maintenance->reported_at ? $maintenance->reported_at->format('d M Y H:i') : '—' }}</dd>
                    </div>
                    @if($maintenance->createdBy)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Logged By</dt>
                        <dd class="text-gray-800">{{ $maintenance->createdBy->name }}</dd>
                    </div>
                    @endif
                </dl>
                @if($maintenance->description)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Description</p>
                    <p class="text-sm text-gray-800 leading-relaxed">{{ $maintenance->description }}</p>
                </div>
                @endif
            </div>

            {{-- Technician --}}
            @if($maintenance->technician_name || $maintenance->technician_phone)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Technician</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    @if($maintenance->technician_name)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Name</dt>
                        <dd class="text-gray-800 font-medium">{{ $maintenance->technician_name }}</dd>
                    </div>
                    @endif
                    @if($maintenance->technician_phone)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Phone</dt>
                        <dd class="text-gray-800">{{ $maintenance->technician_phone }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            {{-- Completion Report (only when completed) --}}
            @if($maintenance->status === 'completed')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" style="border-color:#bbf7d0;">
                <h2 class="text-base font-semibold mb-4" style="color:#166534;">Completion Report</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    @if($maintenance->cost)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Total Cost</dt>
                        <dd class="text-gray-800 font-semibold">Tsh {{ number_format($maintenance->cost, 2) }}</dd>
                    </div>
                    @endif
                    @if($maintenance->next_service_date)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Next Service Date</dt>
                        <dd class="text-gray-800">{{ $maintenance->next_service_date->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if($maintenance->next_service_hours)
                    <div>
                        <dt class="text-gray-500 mb-0.5">Next Service (Hours)</dt>
                        <dd class="text-gray-800">{{ number_format($maintenance->next_service_hours) }} hrs</dd>
                    </div>
                    @endif
                </dl>
                @if($maintenance->parts_used)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Parts & Materials Used</p>
                    <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $maintenance->parts_used }}</p>
                </div>
                @endif
                @if($maintenance->internal_notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Internal Notes</p>
                    <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $maintenance->internal_notes }}</p>
                </div>
                @endif
            </div>
            @elseif($maintenance->internal_notes)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Internal Notes</h2>
                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $maintenance->internal_notes }}</p>
            </div>
            @endif

        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Genset Card --}}
            @if($maintenance->genset)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Genset</h2>
                <a href="{{ route('admin.gensets.show', $maintenance->genset) }}" class="text-lg font-bold text-red-600 hover:underline">{{ $maintenance->genset->asset_number }}</a>
                <p class="text-sm text-gray-600 mt-0.5">{{ $maintenance->genset->name }}</p>
                @if($maintenance->genset->make || $maintenance->genset->model)
                    <p class="text-xs text-gray-400 mt-0.5">{{ collect([$maintenance->genset->make, $maintenance->genset->model])->filter()->implode(' ') }}</p>
                @endif
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500">Current Status</p>
                    <p class="text-sm font-semibold text-gray-800 capitalize mt-0.5">{{ str_replace('_', ' ', $maintenance->genset->status) }}</p>
                </div>
                @if($maintenance->genset->capacity_kva)
                <div class="mt-2">
                    <p class="text-xs text-gray-500">Capacity</p>
                    <p class="text-sm text-gray-800 mt-0.5">{{ $maintenance->genset->capacity_kva }} kVA</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Timestamps --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Timeline</h2>
                <ul class="space-y-3 text-xs text-gray-500">
                    <li class="flex justify-between"><span>Reported</span><span class="font-medium text-gray-700">{{ $maintenance->reported_at ? $maintenance->reported_at->format('d M Y H:i') : '—' }}</span></li>
                    <li class="flex justify-between"><span>Scheduled</span><span class="font-medium text-gray-700">{{ $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d M Y') : '—' }}</span></li>
                    @if($maintenance->started_at)
                    <li class="flex justify-between"><span>Started</span><span class="font-medium text-gray-700">{{ $maintenance->started_at->format('d M Y H:i') }}</span></li>
                    @endif
                    @if($maintenance->completed_at)
                    <li class="flex justify-between"><span>Completed</span><span class="font-medium text-gray-700">{{ $maintenance->completed_at->format('d M Y H:i') }}</span></li>
                    @endif
                    <li class="flex justify-between"><span>Last Updated</span><span class="font-medium text-gray-700">{{ $maintenance->updated_at->format('d M Y H:i') }}</span></li>
                </ul>
            </div>

        </div>
    </div>

    {{-- =====================================================
         MODALS
    ====================================================== --}}

    {{-- Start Modal --}}
    @if($maintenance->status === 'scheduled')
    <div id="startModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">Start Maintenance Work</h3>
                <button onclick="document.getElementById('startModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.maintenance.start', $maintenance) }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600">Confirm that work has started on <strong>{{ $maintenance->maintenance_number }}</strong>. The genset status will be updated to <em>Under Maintenance</em>.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Technician Name</label>
                        <input type="text" name="technician_name" value="{{ $maintenance->technician_name }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Technician Phone</label>
                        <input type="text" name="technician_phone" value="{{ $maintenance->technician_phone }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('startModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Start Work</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Complete Modal --}}
    @if($maintenance->status === 'in_progress')
    <div id="completeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 sticky top-0 bg-white">
                <h3 class="text-lg font-bold text-gray-900">Mark as Completed</h3>
                <button onclick="document.getElementById('completeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.maintenance.complete', $maintenance) }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parts & Materials Used</label>
                        <textarea name="parts_used" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="List parts replaced, consumables used...">{{ $maintenance->parts_used }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Cost (Tsh)</label>
                        <input type="number" name="cost" value="{{ $maintenance->cost }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Next Service Date</label>
                            <input type="date" name="next_service_date" value="{{ $maintenance->next_service_date ? $maintenance->next_service_date->format('Y-m-d') : '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Next Service (Hours)</label>
                            <input type="number" name="next_service_hours" value="{{ $maintenance->next_service_hours }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                        <textarea name="internal_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Any findings, recommendations...">{{ $maintenance->internal_notes }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pay From (Bank Account)</label>
                        <select name="bank_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">— Skip journal entry —</option>
                            @foreach($bankAccounts as $ba)
                                <option value="{{ $ba->id }}">{{ $ba->name }} (Tsh {{ number_format($ba->current_balance, 0) }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Selecting a bank account will post a maintenance expense journal entry.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('completeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#16a34a;">Save & Complete</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if(in_array($maintenance->status, ['scheduled','in_progress']))
    <div id="cancelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">Cancel Maintenance Record</h3>
                <button onclick="document.getElementById('cancelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.maintenance.cancel', $maintenance) }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600">Are you sure you want to cancel <strong>{{ $maintenance->maintenance_number }}</strong>? The genset will be restored to available status if no other active maintenance is pending.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Cancel Record</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-admin-layout>
