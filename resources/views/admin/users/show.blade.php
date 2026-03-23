<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-gray-500 mt-0.5">{{ $user->email }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <!-- Avatar -->
                <div class="flex flex-col items-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-red-600 text-white flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h2 class="mt-3 text-lg font-semibold text-gray-900">{{ $user->name }}</h2>
                    <span class="mt-1 inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $user->role_badge_color }}">
                        {{ $user->role_label }}
                    </span>
                </div>

                <dl class="space-y-3 text-sm border-t border-gray-100 pt-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</dt>
                        <dd class="mt-0.5">
                            @if($user->isLocked())
                                <span class="inline-flex px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-semibold">Locked until {{ $user->locked_until->format('d M Y H:i') }}</span>
                            @elseif($user->status === 'active')
                                <span class="inline-flex px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-semibold">Active</span>
                            @elseif($user->status === 'suspended')
                                <span class="inline-flex px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs font-semibold">Suspended</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-semibold">Inactive</span>
                            @endif
                        </dd>
                    </div>
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
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Login</dt>
                        <dd class="text-gray-800 mt-0.5">
                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}
                            @if($user->last_login_ip)
                                <span class="text-xs text-gray-400 ml-1">{{ $user->last_login_ip }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Member Since</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Login Attempts</dt>
                        <dd class="mt-0.5 {{ $user->login_attempts >= 3 ? 'text-red-600 font-semibold' : 'text-gray-800' }}">{{ $user->login_attempts }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 space-y-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Actions</h3>

                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition border border-gray-200">
                        {{ $user->status === 'active' ? '⏸ Deactivate Account' : '▶ Activate Account' }}
                    </button>
                </form>
                @endif

                @if($user->isLocked())
                <form method="POST" action="{{ route('admin.users.unlock', $user) }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm text-orange-700 hover:bg-orange-50 transition border border-orange-200">
                        🔓 Unlock Account
                    </button>
                </form>
                @endif

                <a href="{{ route('admin.users.activity-log', $user) }}"
                   class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition border border-gray-200">
                    📋 Full Activity Log
                </a>
            </div>

            <!-- Reset Password -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4" x-data="{ open: false }">
                <button @click="open = !open" class="w-full text-left text-sm font-semibold text-gray-700 flex items-center justify-between">
                    <span>🔑 Reset Password</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-4">
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">New Password</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                            @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <button type="submit" class="w-full px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Activity</h2>
                    <a href="{{ route('admin.users.activity-log', $user) }}" class="text-xs text-red-600 hover:text-red-700 font-medium">View all</a>
                </div>
                @if($recentActivity->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No activity recorded yet.</p>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach($recentActivity as $log)
                        <li class="px-5 py-3 flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800">{{ $log->description }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                    @if($log->ip_address) &middot; {{ $log->ip_address }} @endif
                                </p>
                            </div>
                            <span class="inline-flex px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs flex-shrink-0">{{ $log->action }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
