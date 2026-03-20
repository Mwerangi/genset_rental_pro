<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Milele Power') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- Hero Section with Red Dot Pattern -->
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-white via-gray-50 to-gray-100 relative overflow-hidden">
            <!-- Red Dot Pattern Background -->
            <div class="absolute inset-0 z-0" style="background-image: radial-gradient(#DC2626 0.5px, transparent 0.5px); background-size: 20px 20px; opacity: 0.08;"></div>
            
            <div class="relative z-10 w-full max-w-md px-6 py-8">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <a href="/" class="inline-block">
                        <div class="flex items-center justify-center gap-3">
                            <div class="bg-gradient-to-br from-red-600 to-red-700 p-3 rounded-xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h1 class="text-2xl font-bold text-gray-900">Milele Power</h1>
                                <p class="text-sm text-gray-600">Generator Rentals</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Auth Card -->
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-8 py-6">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Back to Home Link -->
                <div class="text-center mt-6">
                    <a href="/" class="text-sm text-gray-600 hover:text-red-600 transition-colors inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
