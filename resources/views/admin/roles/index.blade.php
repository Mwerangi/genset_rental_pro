<x-admin-layout>
    <x-slot name="title">Role Management</x-slot>

    {{--
        All badge color classes must appear as literals here so Tailwind keeps them in the build.
        bg-red-100 text-red-800
        bg-orange-100 text-orange-800
        bg-yellow-100 text-yellow-800
        bg-green-100 text-green-800
        bg-teal-100 text-teal-800
        bg-cyan-100 text-cyan-800
        bg-blue-100 text-blue-800
        bg-indigo-100 text-indigo-800
        bg-purple-100 text-purple-800
        bg-gray-100 text-gray-700
    --}}

    <div class="p-6 space-y-6" x-data="{ createOpen: false, editRole: null }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                <p class="mt-1 text-sm text-gray-500">Create and manage roles. Assign permissions to each role from the <a href="{{ route('admin.permissions.index') }}" class="text-red-600 underline hover:text-red-700">Permissions</a> page.</p>
            </div>
            <button @click="createOpen = true"
                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Role
            </button>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->has('delete'))
            <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $errors->first('delete') }}
            </div>
        @endif
        @if($errors->has('key'))
            <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $errors->first('key') }}
            </div>
        @endif

        {{-- Roles table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $role->badge_color }}">
                                        {{ $role->label }}
                                    </span>
                                    @if($role->is_system)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            System
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono">{{ $role->key }}</code>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $role->description ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-semibold {{ ($userCounts[$role->key] ?? 0) > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $userCounts[$role->key] ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($role->key === 'super_admin')
                                    <span class="text-xs font-medium text-red-600">All</span>
                                @else
                                    <a href="{{ route('admin.permissions.index', ['role' => $role->key]) }}"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                                        {{ $permCounts[$role->key] ?? 0 }}
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit button --}}
                                    <button
                                        @click="editRole = {
                                            id: {{ $role->id }},
                                            key: '{{ $role->key }}',
                                            label: '{{ addslashes($role->label) }}',
                                            description: '{{ addslashes($role->description ?? '') }}',
                                            badge_color: '{{ $role->badge_color }}',
                                            sort_order: {{ $role->sort_order }}
                                        }"
                                        class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Edit role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Delete button (non-system, no users) --}}
                                    @unless($role->is_system)
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                            onsubmit="return confirm('Delete role \'{{ $role->label }}\'? This cannot be undone.');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors
                                                    {{ ($userCounts[$role->key] ?? 0) > 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                                @if(($userCounts[$role->key] ?? 0) > 0) disabled title="Users are assigned to this role" @else title="Delete role" @endif>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── CREATE ROLE MODAL ────────────────────────────────────────────── --}}
        <div x-show="createOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="createOpen = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl z-10 flex flex-col max-h-[90vh]"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">
                    {{-- Fixed header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Add New Role</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Fill in the details below to create a new role.</p>
                        </div>
                        <button @click="createOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    {{-- Scrollable body --}}
                    <form method="POST" action="{{ route('admin.roles.store') }}" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">
                            {{-- Row 1: Name + Sort Order --}}
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="label" id="create_label" required
                                        oninput="autoKey(this.value)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500"
                                        placeholder="e.g. Senior Technician"
                                        value="{{ old('label') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 99) }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                                </div>
                            </div>
                            {{-- Row 2: Key + Description --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Role Key <span class="text-gray-400 font-normal">(auto-generated)</span>
                                    </label>
                                    <input type="text" name="key" id="create_key"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-red-500 focus:border-red-500"
                                        placeholder="senior_technician"
                                        value="{{ old('key') }}">
                                    <p class="mt-1 text-xs text-gray-400">Lowercase, numbers, underscores. Cannot be changed later.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <input type="text" name="description"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500"
                                        placeholder="Optional description"
                                        value="{{ old('description') }}">
                                </div>
                            </div>
                            {{-- Row 3: Badge Color --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Badge Color <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-9 gap-2">
                                    @foreach($colors as $colorClass => $colorName)
                                        <label class="relative cursor-pointer" title="{{ $colorName }}">
                                            <input type="radio" name="badge_color" value="{{ $colorClass }}"
                                                class="sr-only peer"
                                                {{ old('badge_color', 'bg-gray-100 text-gray-700') === $colorClass ? 'checked' : '' }}>
                                            <span class="flex items-center justify-center h-9 rounded-lg text-xs font-bold peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-gray-600 transition-all {{ $colorClass }}">
                                                Aa
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        {{-- Fixed footer --}}
                        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 shrink-0 bg-gray-50 rounded-b-2xl">
                            <button type="button" @click="createOpen = false"
                                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                Create Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── EDIT ROLE MODAL ──────────────────────────────────────────────── --}}
        <div x-show="editRole !== null" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="editRole = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl z-10 flex flex-col max-h-[90vh]"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">
                    {{-- Fixed header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Edit Role — <span x-text="editRole?.label" class="text-red-600"></span></h3>
                            <p class="text-xs text-gray-400 mt-0.5">Update the role details below.</p>
                        </div>
                        <button @click="editRole = null" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <template x-if="editRole">
                        <form :action="'{{ url('admin/settings/roles') }}/' + editRole.id" method="POST" class="flex flex-col flex-1 overflow-hidden">
                            @csrf @method('PUT')
                            {{-- Scrollable body --}}
                            <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">
                                {{-- Row 1: Name + Sort Order --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="label" required
                                            :value="editRole.label"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                        <input type="number" name="sort_order" min="0" :value="editRole.sort_order"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                                    </div>
                                </div>
                                {{-- Row 2: Key + Description --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Key <span class="text-gray-400 font-normal">(read-only)</span></label>
                                        <input type="text" readonly :value="editRole.key"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono bg-gray-50 text-gray-500 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input type="text" name="description" :value="editRole.description"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                                    </div>
                                </div>
                                {{-- Row 3: Badge Color --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Badge Color <span class="text-red-500">*</span></label>
                                    <div class="grid grid-cols-9 gap-2">
                                        @foreach($colors as $colorClass => $colorName)
                                            <label class="relative cursor-pointer" title="{{ $colorName }}">
                                                <input type="radio" name="badge_color" value="{{ $colorClass }}"
                                                    class="sr-only peer"
                                                    :checked="editRole.badge_color === '{{ $colorClass }}'">
                                                <span class="flex items-center justify-center h-9 rounded-lg text-xs font-bold peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-gray-600 transition-all {{ $colorClass }}">
                                                    Aa
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            {{-- Fixed footer --}}
                            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 shrink-0 bg-gray-50 rounded-b-2xl">
                                <button type="button" @click="editRole = null"
                                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function autoKey(label) {
            const key = label
                .toLowerCase()
                .replace(/[^a-z0-9\s_]/g, '')
                .trim()
                .replace(/[\s-]+/g, '_');
            document.getElementById('create_key').value = key;
        }
    </script>
    @endpush
</x-admin-layout>
