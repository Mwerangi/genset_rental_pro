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
        <div class="container-page">
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
    <footer class="bg-black-900 text-white">
        <div class="container-page py-12">
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

    <!-- Quote Request Modal -->
    <div id="quoteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-20 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-gray-200 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <!-- Modal Header with subtle red accent -->
            <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-t-2xl border-b-2 border-red-600/10">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 rounded-full mb-3">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                            <span class="text-xs font-semibold text-red-600">GET YOUR QUOTE</span>
                        </div>
                        <h2 class="text-3xl font-bold mb-2 text-gray-900">Request a Quote</h2>
                        <p class="text-gray-600">Fill out the form below and we'll get back to you within 24 hours</p>
                    </div>
                    <button onclick="closeQuoteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Success Message (Initially Hidden) -->
            <div id="quoteSuccessMessage" class="hidden p-6 bg-green-50 border-l-4 border-green-500">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800 mb-1">Quote Request Submitted!</h3>
                        <p class="text-green-700">Your request number is <span id="requestNumber" class="font-bold"></span>. We'll contact you shortly.</p>
                    </div>
                </div>
            </div>

            <!-- Error Message (Initially Hidden) -->
            <div id="quoteErrorMessage" class="hidden p-6 bg-red-50 border-l-4 border-red-500">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800 mb-1">Error</h3>
                        <p id="errorMessageText" class="text-red-700"></p>
                    </div>
                </div>
            </div>

            <!-- Quote Request Form -->
            <form id="quoteRequestForm" class="p-6">
                @csrf
                
                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b-2 border-red-600/20 pb-2">Personal Information</h3>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Full Name -->
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Full Name <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" id="full_name" name="full_name" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                        placeholder="John Doe">
                                    <p class="text-red-600 text-sm mt-1 hidden" id="full_name_error"></p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-600">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                        placeholder="john@example.com">
                                    <p class="text-red-600 text-sm mt-1 hidden" id="email_error"></p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number <span class="text-red-600">*</span>
                                    </label>
                                    <input type="tel" id="phone" name="phone" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                        placeholder="+255 XXX XXX XXX">
                                    <p class="text-red-600 text-sm mt-1 hidden" id="phone_error"></p>
                                </div>

                                <!-- Company Name -->
                                <div>
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Company Name <span class="text-gray-400">(Optional)</span>
                                    </label>
                                    <input type="text" id="company_name" name="company_name"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                        placeholder="Your Company Ltd">
                                </div>
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b-2 border-red-600/20 pb-2">Rental Requirements</h3>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Genset Type -->
                                <div>
                                    <label for="genset_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Generator Type <span class="text-red-600">*</span>
                                    </label>
                                    <select id="genset_type" name="genset_type" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all">
                                        <option value="">Select genset type...</option>
                                        <option value="clip-on">Clip-on Generator (20ESX)</option>
                                        <option value="underslung">Underslung Generator</option>
                                        <option value="not_sure">Not Sure / Need Advice</option>
                                    </select>
                                    <p class="text-red-600 text-sm mt-1 hidden" id="genset_type_error"></p>
                                </div>

                                <!-- Rental Duration -->
                                <div>
                                    <label for="rental_duration_days" class="block text-sm font-medium text-gray-700 mb-2">
                                        Rental Duration (Days) <span class="text-red-600">*</span>
                                    </label>
                                    <input type="number" id="rental_duration_days" name="rental_duration_days" required min="1"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                        placeholder="7">
                                    <p class="text-red-600 text-sm mt-1 hidden" id="rental_duration_days_error"></p>
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="rental_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Rental Start Date <span class="text-red-600">*</span>
                                    </label>
                                    <input type="date" id="rental_start_date" name="rental_start_date" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all">
                                    <p class="text-red-600 text-sm mt-1 hidden" id="rental_start_date_error"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Location Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b-2 border-red-600/20 pb-2">Location Details</h3>
                            
                            <!-- Delivery Location -->
                            <div>
                                <label for="delivery_location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Delivery Location <span class="text-red-600">*</span>
                                </label>
                                <textarea id="delivery_location" name="delivery_location" required rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                    placeholder="Full address including area, street, building name..."></textarea>
                                <p class="text-red-600 text-sm mt-1 hidden" id="delivery_location_error"></p>
                            </div>

                            <!-- Pickup Location -->
                            <div>
                                <label for="pickup_location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pickup Location <span class="text-gray-400">(Leave blank if same as delivery)</span>
                                </label>
                                <textarea id="pickup_location" name="pickup_location" rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                    placeholder="Full address (optional)"></textarea>
                            </div>
                        </div>

                        <!-- Additional Requirements -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b-2 border-red-600/20 pb-2">Additional Information</h3>
                            
                            <label for="additional_requirements" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Requirements or Questions <span class="text-gray-400">(Optional)</span>
                            </label>
                            <textarea id="additional_requirements" name="additional_requirements" rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all"
                                placeholder="Any special requirements, questions, or comments..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button - Full Width -->
                <div class="flex items-center justify-between pt-6 border-t mt-6">
                    <p class="text-sm text-gray-500">
                        <span class="text-red-600">*</span> Required fields
                    </p>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeQuoteModal()"
                            class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="submitQuoteBtn"
                            class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center gap-2">
                            <span id="submitBtnText">Submit Request</span>
                            <svg id="submitSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
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
            
            // Reset form and messages
            document.getElementById('quoteRequestForm').reset();
            document.getElementById('quoteSuccessMessage').classList.add('hidden');
            document.getElementById('quoteErrorMessage').classList.add('hidden');
            
            // Clear all error messages
            document.querySelectorAll('[id$="_error"]').forEach(el => el.classList.add('hidden'));
        }

        // Set minimum date to today for rental start date
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('rental_start_date');
            if (startDateInput) {
                const today = new Date().toISOString().split('T')[0];
                startDateInput.setAttribute('min', today);
            }
        });

        // Quote Request Form Submission
        document.getElementById('quoteRequestForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Hide previous messages
            document.getElementById('quoteSuccessMessage').classList.add('hidden');
            document.getElementById('quoteErrorMessage').classList.add('hidden');
            
            // Clear previous field errors
            document.querySelectorAll('[id$="_error"]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            
            // Show loading state
            const submitBtn = document.getElementById('submitQuoteBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Submitting...';
            submitSpinner.classList.remove('hidden');
            
            // Get form data
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/quote-request', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Success
                    document.getElementById('requestNumber').textContent = data.request_number || 'N/A';
                    document.getElementById('quoteSuccessMessage').classList.remove('hidden');
                    document.getElementById('quoteRequestForm').classList.add('hidden');
                    
                    // Reset form
                    this.reset();
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => {
                        closeQuoteModal();
                        document.getElementById('quoteRequestForm').classList.remove('hidden');
                    }, 5000);
                    
                } else {
                    // Validation errors
                    if (data.errors) {
                        // Show field-specific errors
                        Object.keys(data.errors).forEach(field => {
                            const errorElement = document.getElementById(field + '_error');
                            if (errorElement) {
                                errorElement.textContent = data.errors[field][0];
                                errorElement.classList.remove('hidden');
                            }
                        });
                        
                        // Scroll to first error
                        const firstError = document.querySelector('[id$="_error"]:not(.hidden)');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } else {
                        // General error
                        document.getElementById('errorMessageText').textContent = data.message || 'An error occurred. Please try again.';
                        document.getElementById('quoteErrorMessage').classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('errorMessageText').textContent = 'Network error. Please check your connection and try again.';
                document.getElementById('quoteErrorMessage').classList.remove('hidden');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Submit Request';
                submitSpinner.classList.add('hidden');
            }
        });

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
