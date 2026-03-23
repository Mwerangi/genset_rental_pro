<x-admin-layout>
    @php $isEdit = isset($user); @endphp

    <div class="mb-6 flex items-center gap-4">
        <a href="{{ $isEdit ? route('admin.users.show', $user) : route('admin.users.index') }}" class="text-gray-500 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isEdit ? 'Edit User' : 'New User' }}</h1>
            <p class="text-gray-500 mt-0.5">{{ $isEdit ? $user->name : 'Create a new staff account' }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="max-w-2xl">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                               placeholder="+255 7xx xxx xxx"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
                               placeholder="e.g. Operations, Finance..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position / Title</label>
                        <input type="text" name="position" value="{{ old('position', $user->position ?? '') }}"
                               placeholder="e.g. Fleet Manager, Driver..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Access & Role</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            @foreach($roles as $val => $label)
                                <option value="{{ $val }}" {{ old('role', $user->role ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="active"    {{ old('status', $user->status ?? 'active') === 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="inactive"  {{ old('status', $user->status ?? '')        === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                            @if($isEdit)
                            <option value="suspended" {{ old('status', $user->status ?? '')        === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            @if(!$isEdit)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Set Password</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Minimum 8 characters.</p>
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                    {{ $isEdit ? 'Save Changes' : 'Create User' }}
                </button>
                <a href="{{ $isEdit ? route('admin.users.show', $user) : route('admin.users.index') }}"
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</x-admin-layout>
