<x-admin-layout>

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p class="text-gray-500 mt-0.5">Manage your account information and password</p>
    </div>

    @if(session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Profile updated successfully.
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Password updated successfully.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Sidebar: Identity card --}}
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-red-600 text-white flex items-center justify-center text-2xl font-bold select-none">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h2 class="mt-3 text-base font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <span class="mt-2 inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->role_badge_color }}">
                    {{ $user->role_label }}
                </span>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <dl class="space-y-3 text-sm">
                    @if($user->phone)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $user->phone }}</dd>
                    </div>
                    @endif
                    @if($user->department)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Department</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $user->department }}</dd>
                    </div>
                    @endif
                    @if($user->position)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Position</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $user->position }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Member Since</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Login</dt>
                        <dd class="text-gray-800 mt-0.5">
                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Main: Forms --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Profile Information --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-0.5">Profile Information</h2>
                <p class="text-sm text-gray-500 mb-5">Update your name and email address.</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

                <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                Your email address is unverified.
                                <button form="send-verification" class="underline font-medium hover:text-amber-900 ml-1">
                                    Resend verification email.
                                </button>
                                @if (session('status') === 'verification-link-sent')
                                    <span class="block mt-1 font-medium text-green-700">Verification link sent!</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Update Password --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-0.5">Update Password</h2>
                <p class="text-sm text-gray-500 mb-5">Use a long, random password to keep your account secure.</p>

                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('current_password', 'updatePassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('password', 'updatePassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @error('password_confirmation', 'updatePassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-white border border-red-200 rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-red-700 mb-0.5">Delete Account</h2>
                <p class="text-sm text-gray-500 mb-5">
                    Once deleted, all your data will be permanently removed and cannot be recovered.
                </p>
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        class="px-5 py-2.5 border border-red-300 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
                    Delete Account
                </button>
            </div>

        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">Are you sure you want to delete your account?</h2>
            <p class="mt-1 text-sm text-gray-500">
                This action is permanent. All your data will be lost. Enter your password to confirm.
            </p>

            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700 mb-1 sr-only">Password</label>
                <input type="password" name="password" placeholder="Enter your password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                @error('password', 'userDeletion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                    Delete Account
                </button>
            </div>
        </form>
    </x-modal>

</x-admin-layout>
