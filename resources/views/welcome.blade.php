<x-layouts.public>
    <!-- Hero Section -->
    <section id="home" class="relative bg-gradient-to-br from-white via-gray-50 to-white py-20 md:py-32 overflow-hidden">
        <!-- Red Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-[0.08]">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #DC2626 2px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        
        <!-- Decorative Blurred Circles -->
        <div class="absolute top-20 right-20 w-96 h-96 bg-red-100/40 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-20 w-80 h-80 bg-red-50/60 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content Card -->
                <div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 md:p-10 shadow-xl border border-gray-200/50">
                        <!-- Eyebrow -->
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-200 rounded-full text-red-600 text-sm font-semibold mb-6">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                            <span>Trusted by 1000+ Businesses</span>
                        </div>
                        
                        <!-- Main Heading -->
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight text-gray-900">
                            Build Reliable Power Solutions That Feel <span class="text-red-600">Effortless</span>
                        </h1>
                        
                        <!-- Lead Paragraph -->
                        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                            Tanzania's leading generator rental service for refrigerated transport. Keeping your cold chain logistics powered — from dock to destination.
                        </p>
                        
                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap gap-4 mb-8">
                            <button onclick="openQuoteModal()" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-4 rounded-lg transition-all shadow-lg hover:shadow-xl">
                                <span>Get Free Quote</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                            <a href="#services" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-900 font-semibold px-8 py-4 rounded-lg transition-all border-2 border-gray-300 hover:border-gray-400">
                                <span>Explore Services</span>
                            </a>
                        </div>
                        
                        <!-- Mini Stats -->
                        <div class="flex flex-wrap gap-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                                </svg>
                                <span class="text-gray-700 font-medium">Premium Equipment</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 font-medium">24/7 Support</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                <span class="text-gray-700 font-medium">Trusted Nationwide</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Illustration -->
                <div class="hidden lg:flex items-center justify-center">
                    <div class="relative w-full max-w-lg">
                        <!-- Floating Badge -->
                        <div class="absolute top-8 right-8 bg-white/90 backdrop-blur-md border border-gray-200 rounded-xl p-4 shadow-xl z-20 animate-float">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-gray-900 font-bold text-sm">99.9% Uptime</div>
                                    <div class="text-gray-600 text-xs">Guaranteed</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Main Illustration -->
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-200 to-red-100 blur-3xl rounded-full opacity-70"></div>
                            <svg class="w-full h-auto relative z-10" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- White background circle for contrast -->
                                <circle cx="200" cy="200" r="150" fill="white" opacity="0.9"/>
                                
                                <!-- Background circles with solid colors -->
                                <circle cx="200" cy="200" r="140" fill="#FEE2E2" opacity="0.8"/>
                                <circle cx="200" cy="200" r="120" fill="#FECACA" opacity="0.6"/>
                                
                                <!-- Main lightning bolt - SOLID and PROMINENT -->
                                <path d="M200 60 L130 200 L180 200 L140 340 L270 160 L220 160 Z" 
                                      fill="#DC2626" 
                                      stroke="#991B1B" 
                                      stroke-width="4" 
                                      stroke-linejoin="round"/>
                                
                                <!-- Animated rotating circles with SOLID colors -->
                                <circle cx="200" cy="200" r="160" stroke="#DC2626" stroke-width="4" stroke-dasharray="12 8" opacity="0.9" class="animate-spin-slow"/>
                                <circle cx="200" cy="200" r="180" stroke="#EF4444" stroke-width="3" stroke-dasharray="8 8" opacity="0.7"/>
                                <circle cx="200" cy="200" r="200" stroke="#FECACA" stroke-width="2" stroke-dasharray="5 5" opacity="0.5"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="services" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Title -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-100 rounded-full text-red-600 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                    </svg>
                    <span>OUR VALUES</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Why Choose <span class="text-red-600">Milele Power?</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Committed to delivering exceptional service that keeps your business running smoothly
                </p>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Quality -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Quality First</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Premium generators maintained to the highest standards, ensuring optimal performance for your refrigerated transport needs.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Reliability -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Reliability</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Dependable power solutions with 99.9% uptime guarantee. Your cargo stays protected, no matter what.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Customer Satisfaction -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Customer Satisfaction</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        24/7 dedicated support team available to address your concerns and ensure seamless operations.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Innovation -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Innovation</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Latest technology and fuel-efficient generators that reduce costs while maximizing performance.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Safety -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Safety</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Rigorous safety checks and compliance with international standards for secure operation.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Excellence -->
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-100 hover:border-red-500 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Excellence</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Continuous improvement and training ensure exceptional service that exceeds expectations.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:gap-3 transition-all">
                        <span>Learn More</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Target Clients Section -->
    <section id="clients" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Title -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-100 rounded-full text-red-600 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>OUR CLIENTS</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Industries We <span class="text-red-600">Serve</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Specialized power solutions for diverse industries across Tanzania
                </p>
            </div>

            <!-- Clients Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Meat & Poultry Distributors</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Preserve freshness and quality during transport with reliable refrigeration power.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Dairy Products</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Maintain optimal temperatures for milk, yogurt, and cheese throughout the cold chain.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Seafood & Fish</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Critical power backup ensuring seafood stays frozen from port to market.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Fruits & Vegetables</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Extend shelf life and reduce spoilage with precise temperature control.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Pharmaceuticals</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Ensure temperature-sensitive medications remain effective with uninterrupted power.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border-l-4 border-red-600 hover:shadow-xl transition-all duration-300 group">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors">Frozen Food Chains</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Comprehensive power solutions for supermarkets and restaurant supply chains.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Title -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-100 rounded-full text-red-600 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"/>
                    </svg>
                    <span>OUR EQUIPMENT</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Generator <span class="text-red-600">Solutions</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    High-performance generators designed specifically for refrigerated transport
                </p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <!-- Clip-on Gensets -->
                <div class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <!-- Clean Header with Icon -->
                    <div class="bg-gradient-to-br from-red-50 to-white p-8 border-b-4 border-red-600">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Clip-on Generator Sets</h3>
                                <p class="text-gray-600">External mounting solution for maximum versatility</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-8">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Key Features</h4>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Diesel powered, self-contained units</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Easy installation on any reefer unit</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Fuel-efficient operation</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">24/7 monitoring and support</span>
                            </li>
                        </ul>
                        <button onclick="openQuoteModal()" class="group/btn w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-4 px-6 rounded-xl shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center gap-2">
                            <span>Request Clip-on Genset</span>
                            <svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Underslung Gensets -->
                <div class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                    <!-- Clean Header with Icon -->
                    <div class="bg-gradient-to-br from-gray-50 to-white p-8 border-b-4 border-gray-900">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-gray-900 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Underslung Generator Sets</h3>
                                <p class="text-gray-600">Chassis-mounted solution with space-saving design</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-8">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Key Features</h4>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Maximizes cargo space</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Protected from elements and damage</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Reduced noise levels</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700 text-base">Improved aerodynamics</span>
                            </li>
                        </ul>
                        <button onclick="openQuoteModal()" class="group/btn w-full bg-gray-900 hover:bg-black text-white font-semibold py-4 px-6 rounded-xl shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center gap-2">
                            <span>Request Underslung Genset</span>
                            <svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-24 bg-gradient-to-br from-white via-gray-50 to-white overflow-hidden">
        <!-- Red Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-[0.08]">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #DC2626 2px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="bg-white rounded-2xl p-12 md:p-16 shadow-xl border border-gray-200">
                <!-- Content -->
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-full text-gray-600 text-xs font-medium mb-6">
                        <span>GET STARTED TODAY</span>
                    </div>
                    
                    <h2 class="text-3xl md:text-5xl font-bold mb-6 text-gray-900">
                        Ready to Power Your Business?
                    </h2>
                    
                    <div class="mb-10">
                        <p class="text-base md:text-lg text-gray-600 mx-auto" style="max-width: 600px;">
                            Get a free consultation and discover the perfect generator solution for your refrigerated transport needs.
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                        <button onclick="openQuoteModal()" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-4 rounded-xl transition-all shadow-lg hover:shadow-xl group">
                            <span>Get Your Free Quote</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                        <a href="#contact" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-900 font-semibold px-8 py-4 rounded-xl transition-all border border-gray-300 hover:border-gray-400">
                            <span>Contact Us</span>
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap justify-center gap-12 pt-8 border-t border-gray-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 mb-1">1000+</div>
                            <div class="text-sm text-gray-600">Successful Rentals</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 mb-1">24/7</div>
                            <div class="text-sm text-gray-600">Support Available</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 mb-1">99.9%</div>
                            <div class="text-sm text-gray-600">Uptime Guarantee</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Title -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-100 rounded-full text-red-600 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <span>GET IN TOUCH</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                    Contact <span class="text-red-600">Milele Power</span>
                </h2>
                <div class="mb-4">
                    <p class="text-lg text-gray-600 mx-auto" style="max-width: 600px;">
                        We're here to help you keep your cold chain running smoothly
                    </p>
                </div>
            </div>

            <!-- Contact Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-200 hover:border-red-600 hover:shadow-xl transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transition-all duration-300 group-hover:scale-110">
                        <svg class="w-8 h-8 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-3 text-gray-900 group-hover:text-red-600 transition-colors">Our Location</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Plot No. 80, Mikocheni<br>
                        Dar es Salaam, Tanzania
                    </p>
                </div>

                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-200 hover:border-red-600 hover:shadow-xl transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transition-all duration-300 group-hover:scale-110">
                        <svg class="w-8 h-8 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-3 text-gray-900 group-hover:text-red-600 transition-colors">Email Us</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        info@milelepower.co.tz<br>
                        support@milelepower.co.tz
                    </p>
                </div>

                <div class="group bg-white rounded-2xl p-8 border-2 border-gray-200 hover:border-red-600 hover:shadow-xl transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transition-all duration-300 group-hover:scale-110">
                        <svg class="w-8 h-8 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-3 text-gray-900 group-hover:text-red-600 transition-colors">Business Hours</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        24/7 Emergency Support<br>
                        Office: Mon-Fri, 8AM-6PM
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
