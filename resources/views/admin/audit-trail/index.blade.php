<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Audit Trail</h1>
        <p class="text-gray-500 mt-0.5">Full record of who did what and when across the system.</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.audit-trail.index') }}" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

            {{-- User --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">User</label>
                <select name="user_id" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Action --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select name="action" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
                    <option value="">All Actions</option>
                    @foreach($distinctActions as $act)
                        <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Module --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Module</label>
                <select name="module" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
                    <option value="">All Modules</option>
                    @foreach($modules as $class => $label)
                        <option value="{{ $class }}" @selected(request('module') === $class)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
            </div>

            {{-- Date To --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
            </div>

            {{-- Search + Submit --}}
            <div class="flex flex-col justify-end gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…"
                       class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:border-red-400">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg hover:bg-red-700 transition">Filter</button>
                    <a href="{{ route('admin.audit-trail.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-2 rounded-lg hover:bg-gray-200 transition">Clear</a>
                </div>
            </div>

        </div>
    </form>

    {{-- Results summary --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-gray-500">
            Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} entries
        </p>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Date / Time</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">User</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Action</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Module</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                @php
                    $moduleLabel = \App\Http\Controllers\Admin\AuditTrailController::resolveModuleLabel($log->model_type);

                    $actionColors = [
                        'login'          => 'bg-blue-100 text-blue-700',
                        'logout'         => 'bg-gray-100 text-gray-600',
                        'created'        => 'bg-green-100 text-green-700',
                        'updated'        => 'bg-yellow-100 text-yellow-700',
                        'deleted'        => 'bg-red-100 text-red-700',
                        'approved'       => 'bg-green-100 text-green-800',
                        'rejected'       => 'bg-red-100 text-red-700',
                        'activated'      => 'bg-teal-100 text-teal-700',
                        'returned'       => 'bg-indigo-100 text-indigo-700',
                        'invoiced'       => 'bg-purple-100 text-purple-700',
                        'paid'           => 'bg-emerald-100 text-emerald-700',
                        'cancelled'      => 'bg-red-100 text-red-600',
                        'voided'         => 'bg-red-100 text-red-600',
                        'issued'         => 'bg-teal-100 text-teal-700',
                        'confirmed'      => 'bg-green-100 text-green-700',
                        'submitted'      => 'bg-blue-100 text-blue-700',
                        'posted'         => 'bg-indigo-100 text-indigo-700',
                        'disbursed'      => 'bg-emerald-100 text-emerald-700',
                        'retired'        => 'bg-gray-100 text-gray-700',
                        'status_changed' => 'bg-orange-100 text-orange-700',
                        'password_reset' => 'bg-orange-100 text-orange-700',
                        'unlocked'       => 'bg-blue-100 text-blue-700',
                        'payment_recorded' => 'bg-emerald-100 text-emerald-700',
                        'payment_reversed' => 'bg-red-100 text-red-600',
                    ];
                    $badgeClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs font-mono">
                        {{ $log->created_at->format('d M Y') }}<br>
                        <span class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->user)
                            <span class="font-medium text-gray-800">{{ $log->user->name }}</span>
                            <br><span class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $log->user->role) }}</span>
                        @else
                            <span class="text-gray-400 italic">System</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $badgeClass }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $moduleLabel }}</td>
                    <td class="px-4 py-3 text-gray-800">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs font-mono whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-16 text-center text-gray-400">
                        <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        No activity found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
