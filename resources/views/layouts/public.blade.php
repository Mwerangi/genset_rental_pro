<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Milele Power - Reliable Generator Rental Solutions for Cold Chain Logistics in Tanzania. Powering your cold chain logistics from dock to destination.">
    <meta name="keywords" content="generator rental, genset rental, cold chain, Tanzania, Dar es Salaam, power solutions">
    
    <title>{{ $title ?? 'Milele Power - Reliable Power, Anytime, Anywhere!' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-black text-white fixed w-full top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-xl font-bold text-white">MILELE POWER</span>
                            <span class="text-xs text-gray-400 uppercase tracking-wider">Generator Rentals</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-white hover:text-red-600 transition-colors duration-200">Home</a>
                    <a href="#services" class="text-white hover:text-red-600 transition-colors duration-200">Services</a>
                    <a href="#clients" class="text-white hover:text-red-600 transition-colors duration-200">Clients</a>
                    <a href="#about" class="text-white hover:text-red-600 transition-colors duration-200">About</a>
                    <a href="#contact" class="text-white hover:text-red-600 transition-colors duration-200">Contact</a>
                    <button 
                        onclick="openQuoteModal()" 
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-md transition-colors duration-200 shadow-lg">
                        Get Quote
                    </button>
                    <a href="/login" class="text-white hover:text-red-600 transition-colors duration-200 text-sm">
                        Admin Login
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button 
                        onclick="toggleMobileMenu()" 
                        class="text-gray-300 hover:text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobileMenu" class="hidden md:hidden bg-black">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="#home" class="block text-white hover:text-red-600 py-2">Home</a>
                <a href="#services" class="block text-white hover:text-red-600 py-2">Services</a>
                <a href="#clients" class="block text-white hover:text-red-600 py-2">Clients</a>
                <a href="#about" class="block text-white hover:text-red-600 py-2">About</a>
                <a href="#contact" class="block text-white hover:text-red-600 py-2">Contact</a>
                <button 
                    onclick="openQuoteModal()" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-md transition-colors duration-200 mt-2">
                    Get Quote
                </button>
                <a href="/login" class="block text-gray-300 hover:text-white py-2 text-sm border-t border-gray-700 pt-2 mt-2">
                    Admin Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-black text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-xl font-bold">MILELE POWER LTD</span>
                    </div>
                    <div class="mb-4">
                        <p class="text-gray-400" style="max-width: 450px;">
                            Powering your cold chain logistics — from dock to destination. 
                            Keeping It Cool, Wherever You Go.
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-400">
                        <span class="text-3xl font-bold text-red-600">1000+</span>
                        <span>Successful Rentals</span>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-red-600 transition-colors">Home</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-red-600 transition-colors">Services</a></li>
                        <li><a href="#clients" class="text-gray-400 hover:text-red-600 transition-colors">Our Clients</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-red-600 transition-colors">Contact</a></li>
                        <li><a href="/login" class="text-gray-400 hover:text-red-600 transition-colors">Admin Login</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Plot No. 80, Mikocheni Industrial Area,<br>Coca Cola Road - Dar Es Salaam, Tanzania</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <div>info@milelepower.co.tz</div>
                                <div>accounts@milelepower.co.tz</div>
                            </div>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <div>Mon-Fri: 9 AM - 5 PM</div>
                                <div>Saturday: 9 AM - 1 PM</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Copyright MILELE POWER LTD. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Quote Request Modal (Will be implemented next) -->
    <div id="quoteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-20 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <!-- Quote form will be added here -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-black-900">Request a Quote</h2>
                    <button onclick="closeQuoteModal()" class="text-gray-500 hover:text-black-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 mb-6">Coming soon! Quote request form will be implemented here.</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        function openQuoteModal() {
            const modal = document.getElementById('quoteModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeQuoteModal() {
            const modal = document.getElementById('quoteModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const button = event.target.closest('button');
            
            if (!menu.contains(event.target) && !button) {
                menu.classList.add('hidden');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Close mobile menu if open
                    document.getElementById('mobileMenu').classList.add('hidden');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
