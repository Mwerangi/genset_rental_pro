<x-admin-layout>
    <x-slot name="title">Role Permissions</x-slot>

    <div class="p-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Role Permissions</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Select a role on the left to view and modify its permissions. Changes take effect immediately.
                    <span class="text-amber-600 font-medium">Super Admin always has all permissions and cannot be modified.</span>
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex gap-6" x-data="{ selectedRole: '{{ $selectedRole }}' }">
            {{-- Left — Role List --}}
            <div class="w-56 shrink-0 space-y-1">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 mb-3">Roles</p>

                @foreach($roles as $roleKey => $roleLabel)
                    @php
                        $colors = [
                            'super_admin'        => 'bg-red-100 text-red-800',
                            'admin'              => 'bg-orange-100 text-orange-800',
                            'finance_manager'    => 'bg-green-100 text-green-800',
                            'operations_manager' => 'bg-blue-100 text-blue-800',
                            'sales_manager'      => 'bg-purple-100 text-purple-800',
                            'dispatcher'         => 'bg-yellow-100 text-yellow-800',
                            'driver'             => 'bg-cyan-100 text-cyan-800',
                            'technician'         => 'bg-teal-100 text-teal-800',
                            'accountant'         => 'bg-indigo-100 text-indigo-800',
                        ];
                        $badgeColor = $colors[$roleKey] ?? 'bg-gray-100 text-gray-700';
                        $permCount  = count($rolePermissions[$roleKey] ?? []);
                    @endphp
                    <button
                        type="button"
                        @click="selectedRole = '{{ $roleKey }}'"
                        :class="selectedRole === '{{ $roleKey }}'
                            ? 'bg-red-600 text-white shadow-sm'
                            : 'text-gray-700 hover:bg-gray-100'"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors duration-150 text-sm font-medium"
                    >
                        <span>{{ $roleLabel }}</span>
                        <span
                            :class="selectedRole === '{{ $roleKey }}' ? 'bg-white/20 text-white' : '{{ $badgeColor }}'"
                            class="text-xs font-bold px-2 py-0.5 rounded-full"
                        >{{ $permCount }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Right — Permissions Panel --}}
            <div class="flex-1 min-w-0">
                @foreach($roles as $roleKey => $roleLabel)
                    <div x-show="selectedRole === '{{ $roleKey }}'" x-cloak>

                        {{-- Super Admin: read-only notice --}}
                        @if($roleKey === 'super_admin')
                            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center text-sm text-red-700">
                                <svg class="w-10 h-10 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="font-semibold text-red-800 mb-1">Super Admin — Unrestricted Access</p>
                                <p>Super Admin bypasses all permission checks and always has access to everything. Their permissions cannot be modified here.</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.permissions.update', $roleKey) }}">
                                @csrf
                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                    {{-- Panel header --}}
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                                        <div>
                                            <h2 class="text-base font-semibold text-gray-900">{{ $roleLabel }}</h2>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ count($rolePermissions[$roleKey] ?? []) }} of {{ $permissions->flatten()->count() }} permissions granted
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button"
                                                onclick="toggleAll('{{ $roleKey }}', true)"
                                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors">
                                                Grant All
                                            </button>
                                            <button type="button"
                                                onclick="toggleAll('{{ $roleKey }}', false)"
                                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition-colors">
                                                Revoke All
                                            </button>
                                            <button type="submit"
                                                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Permission modules --}}
                                    <div class="divide-y divide-gray-100">
                                        @foreach($permissions as $module => $modulePerms)
                                            @php
                                                $grantedInModule  = $modulePerms->filter(fn($p) => in_array($p->name, $rolePermissions[$roleKey] ?? []))->count();
                                                $totalInModule    = $modulePerms->count();
                                            @endphp
                                            <div x-data="{ open: true }" class="group">
                                                {{-- Module header (collapsible) --}}
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition-colors"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                        <span class="text-sm font-semibold text-gray-700">{{ $module }}</span>
                                                    </div>
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                                        {{ $grantedInModule === $totalInModule ? 'bg-green-100 text-green-700' : ($grantedInModule === 0 ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700') }}">
                                                        {{ $grantedInModule }} / {{ $totalInModule }}
                                                    </span>
                                                </button>

                                                {{-- Permission rows --}}
                                                <div x-show="open" class="px-6 pb-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    @foreach($modulePerms as $permission)
                                                        @php $granted = in_array($permission->name, $rolePermissions[$roleKey] ?? []); @endphp
                                                        <label
                                                            class="flex items-start gap-3 px-3 py-2.5 rounded-lg border cursor-pointer transition-colors
                                                                {{ $granted ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50' }}"
                                                            data-role="{{ $roleKey }}"
                                                            data-perm="{{ $permission->name }}"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $permission->name }}"
                                                                {{ $granted ? 'checked' : '' }}
                                                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer perm-checkbox-{{ $roleKey }}"
                                                                onchange="updateLabelStyle(this)"
                                                            >
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $permission->label }}</p>
                                                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $permission->name }}</p>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Footer save button --}}
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                                        <button type="submit"
                                            class="flex items-center gap-2 px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Save {{ $roleLabel }} Permissions
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleAll(roleKey, grant) {
            const checkboxes = document.querySelectorAll('.perm-checkbox-' + roleKey);
            checkboxes.forEach(cb => {
                cb.checked = grant;
                updateLabelStyle(cb);
            });
        }

        function updateLabelStyle(checkbox) {
            const label = checkbox.closest('label');
            if (checkbox.checked) {
                label.classList.remove('border-gray-200', 'bg-white', 'hover:border-gray-300', 'hover:bg-gray-50');
                label.classList.add('border-green-200', 'bg-green-50');
            } else {
                label.classList.remove('border-green-200', 'bg-green-50');
                label.classList.add('border-gray-200', 'bg-white', 'hover:border-gray-300', 'hover:bg-gray-50');
            }
        }
    </script>
    @endpush
</x-admin-layout>
