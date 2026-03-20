<x-guest-layout>
    <!-- Page Title -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
        <p class="text-sm text-gray-600 mt-1">Sign up for admin access</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <x-input 
            label="Full Name"
            name="name" 
            type="text" 
            :value="old('name')"
            placeholder="John Doe"
            required 
            autofocus 
        />

        <!-- Email Address -->
        <x-input 
            label="Email Address"
            name="email" 
            type="email" 
            :value="old('email')"
            placeholder="john@milelepower.co.tz"
            required 
        />

        <!-- Password -->
        <x-input 
            label="Password"
            name="password" 
            type="password" 
            placeholder="Minimum 8 characters"
            required 
        />

        <!-- Confirm Password -->
        <x-input 
            label="Confirm Password"
            name="password_confirmation" 
            type="password" 
            placeholder="Re-enter your password"
            required 
        />

        <!-- Register Button -->
        <x-button 
            type="submit" 
            variant="primary" 
            class="w-full gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Create Account
        </x-button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t">
            <p class="text-sm text-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-red-600 hover:text-red-700 font-medium">
                    Sign in here
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
