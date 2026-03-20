<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Maintenance</h1>
            <p class="text-gray-500 mt-1">Fleet servicing, repairs and inspections</p>
        </div>
        <a href="{{ route('admin.maintenance.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Record
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Scheduled</p>
            <p class="text-3xl font-bold mt-1" style="color:#1e40af;">{{ $stats['scheduled'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">In Progress</p>
            <p class="text-3xl font-bold mt-1" style="color:#b45309;">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Overdue</p>
            <p class="text-3xl font-bold mt-1 {{ $stats['overdue'] > 0 ? '' : 'text-gray-900' }}" style="{{ $stats['overdue'] > 0 ? 'color:#dc2626;' : '' }}">{{ $stats['overdue'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search record #, title, genset #, technician..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled"   {{ request('status') === 'scheduled'   ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled"   {{ request('status') === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Types</option>
                    <option value="scheduled"   {{ request('type') === 'scheduled'   ? 'selected' : '' }}>Scheduled Service</option>
                    <option value="preventive"  {{ request('type') === 'preventive'  ? 'selected' : '' }}>Preventive</option>
                    <option value="repair"      {{ request('type') === 'repair'      ? 'selected' : '' }}>Repair</option>
                    <option value="breakdown"   {{ request('type') === 'breakdown'   ? 'selected' : '' }}>Breakdown</option>
                    <option value="inspection"  {{ request('type') === 'inspection'  ? 'selected' : '' }}>Inspection</option>
                </select>
            </div>
            <div>
                <select name="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Priorities</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>High</option>
                    <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>Medium</option>
                    <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request()->hasAny(['search', 'status', 'type', 'priority']))
                <a href="{{ route('admin.maintenance.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($records->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                <p class="text-sm">No maintenance records found</p>
                <a href="{{ route('admin.maintenance.create') }}" class="mt-3 inline-block text-sm text-red-600 hover:underline">Create first record</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Genset</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $record)
                    @php
                        $isOverdue = $record->status === 'scheduled' && $record->scheduled_date && $record->scheduled_date->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $record->maintenance_number }}</td>
                        <td class="px-5 py-3">
                            @if($record->genset)
                                <a href="{{ route('admin.gensets.show', $record->genset) }}" class="text-red-600 hover:underline font-medium">{{ $record->genset->asset_number }}</a>
                                <p class="text-xs text-gray-400 truncate max-w-[120px]">{{ $record->genset->name }}</p>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-800 max-w-[200px]">
                            <p class="truncate font-medium">{{ $record->title }}</p>
                            @if($record->technician_name)
                                <p class="text-xs text-gray-400">{{ $record->technician_name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $record->type_label }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $record->priority_style }}">
                                {{ $record->priority_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs {{ $isOverdue ? 'font-semibold' : 'text-gray-500' }}" style="{{ $isOverdue ? 'color:#dc2626;' : '' }}">
                            {{ $record->scheduled_date ? $record->scheduled_date->format('d M Y') : '—' }}
                            @if($isOverdue) <span class="ml-1">⚠</span> @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $record->status_style }}">
                                {{ $record->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.maintenance.show', $record) }}" class="text-sm text-red-600 hover:underline font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($records->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $records->links() }}
                </div>
            @endif
        @endif
    </div>
</x-admin-layout>
