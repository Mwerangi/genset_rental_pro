<x-admin-layout>
    @php $isEdit = isset($maintenance); @endphp

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.maintenance.index') }}" class="hover:text-red-600">Maintenance</a>
        <span>/</span>
        <span>{{ $isEdit ? 'Edit '.$maintenance->maintenance_number : 'New Record' }}</span>
    </div>

    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $isEdit ? 'Edit Maintenance Record' : 'New Maintenance Record' }}</h1>

        @if($errors->any())
        <div class="mb-5 border rounded-xl p-4 text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.maintenance.update', $maintenance) : route('admin.maintenance.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-6">

                {{-- Genset --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Genset <span class="text-red-500">*</span></label>
                    @if($isEdit)
                        <input type="hidden" name="genset_id" value="{{ $maintenance->genset_id }}">
                        <div class="flex items-center gap-3 border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50">
                            <span class="font-mono font-semibold text-gray-800">{{ $maintenance->genset->asset_number ?? '—' }}</span>
                            <span class="text-gray-500 text-sm">{{ $maintenance->genset->name ?? '' }}</span>
                            <span class="ml-auto text-xs text-gray-400 italic">Cannot change genset on existing record</span>
                        </div>
                    @else
                        <select name="genset_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select Genset —</option>
                            @foreach($gensets as $genset)
                                <option value="{{ $genset->id }}"
                                    {{ old('genset_id', $preselectedGenset?->id) == $genset->id ? 'selected' : '' }}>
                                    {{ $genset->asset_number }} — {{ $genset->name }}
                                    ({{ ucfirst(str_replace('_',' ', $genset->status)) }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $maintenance->title ?? '') }}" required
                           placeholder="e.g. 250hr scheduled service, Oil change, Alternator repair..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                {{-- Type + Priority --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select —</option>
                            <option value="scheduled"  {{ old('type', $maintenance->type ?? '') === 'scheduled'  ? 'selected' : '' }}>Scheduled Service</option>
                            <option value="preventive" {{ old('type', $maintenance->type ?? '') === 'preventive' ? 'selected' : '' }}>Preventive Maintenance</option>
                            <option value="repair"     {{ old('type', $maintenance->type ?? '') === 'repair'     ? 'selected' : '' }}>Repair</option>
                            <option value="breakdown"  {{ old('type', $maintenance->type ?? '') === 'breakdown'  ? 'selected' : '' }}>Breakdown</option>
                            <option value="inspection" {{ old('type', $maintenance->type ?? '') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select —</option>
                            <option value="low"      {{ old('priority', $maintenance->priority ?? 'medium') === 'low'      ? 'selected' : '' }}>Low</option>
                            <option value="medium"   {{ old('priority', $maintenance->priority ?? 'medium') === 'medium'   ? 'selected' : '' }}>Medium</option>
                            <option value="high"     {{ old('priority', $maintenance->priority ?? '') === 'high'     ? 'selected' : '' }}>High — genset set to Maintenance</option>
                            <option value="critical" {{ old('priority', $maintenance->priority ?? '') === 'critical' ? 'selected' : '' }}>Critical — genset set to Maintenance</option>
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Detailed description of the work to be done...">{{ old('description', $maintenance->description ?? '') }}</textarea>
                </div>

                {{-- Scheduled Date + Run Hours --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Scheduled Date</label>
                        <input type="date" name="scheduled_date" value="{{ old('scheduled_date', isset($maintenance->scheduled_date) ? $maintenance->scheduled_date->format('Y-m-d') : '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Run Hours at Service</label>
                        <input type="number" name="run_hours_at_service" value="{{ old('run_hours_at_service', $maintenance->run_hours_at_service ?? '') }}"
                               min="0" placeholder="e.g. 2500"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                {{-- Technician --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Technician Name</label>
                        <input type="text" name="technician_name" value="{{ old('technician_name', $maintenance->technician_name ?? '') }}"
                               placeholder="Assigned technician"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Technician Phone</label>
                        <input type="text" name="technician_phone" value="{{ old('technician_phone', $maintenance->technician_phone ?? '') }}"
                               placeholder="+254 700 000 000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                {{-- Cost (edit only or if you want to capture at creation too) --}}
                @if($isEdit)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated / Actual Cost (Tsh)</label>
                    <input type="number" name="cost" value="{{ old('cost', $maintenance->cost ?? '0') }}"
                           step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                @endif

                {{-- Internal Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Internal Notes</label>
                    <textarea name="internal_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Internal comments, preliminary findings...">{{ old('internal_notes', $maintenance->internal_notes ?? '') }}</textarea>
                </div>

            </div>

            <div class="mt-5 flex gap-3 justify-end">
                <a href="{{ $isEdit ? route('admin.maintenance.show', $maintenance) : route('admin.maintenance.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Discard</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                    {{ $isEdit ? 'Save Changes' : 'Create Record' }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
