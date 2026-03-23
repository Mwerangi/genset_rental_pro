<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-500 mt-0.5">Manage staff accounts and access levels</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Users</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Inactive</p>
            <p class="text-2xl font-bold text-gray-500 mt-1">{{ $stats['inactive'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Locked</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['locked'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end shadow-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
            <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                <option value="all">All Roles</option>
                @foreach(\App\Models\User::roles() as $val => $label)
                    <option value="{{ $val }}" {{ request('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                <option value="all">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
        @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">User</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Role</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Department</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Last Login</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $user->role_badge_color }}">
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ $user->department ?? '—' }}
                        @if($user->position)
                            <br><span class="text-xs text-gray-400">{{ $user->position }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($user->isLocked())
                            <span class="inline-flex px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-semibold">Locked</span>
                        @elseif($user->status === 'active')
                            <span class="inline-flex px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-semibold">Active</span>
                        @elseif($user->status === 'suspended')
                            <span class="inline-flex px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs font-semibold">Suspended</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-semibold">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.users.show', $user) }}"
                           class="text-sm text-red-600 hover:text-red-700 font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
