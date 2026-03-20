<x-guest-layout>
    <!-- Page Title -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
        <p class="text-sm text-gray-600 mt-2">
            Forgot your password? No problem. Enter your email address and we'll send you a password reset link.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <x-input 
            label="Email Address"
            name="email" 
            type="email" 
            :value="old('email')"
            placeholder="admin@milelepower.co.tz"
            required 
            autofocus 
        />

        <!-- Submit Button -->
        <x-button 
            type="submit" 
            variant="primary" 
            class="w-full gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Email Password Reset Link
        </x-button>

        <!-- Back to Login -->
        <div class="text-center pt-4 border-t">
            <p class="text-sm text-gray-600">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-red-600 hover:text-red-700 font-medium">
                    Back to login
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
