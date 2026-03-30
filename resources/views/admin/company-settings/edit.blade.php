<x-admin-layout>
    <div x-data="{
        activeTab: window.location.hash.replace('#','') || 'general',
        switchTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
        }
    }">

        {{-- ── Page Header ──────────────────────────────────────────────────── --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Company Settings</h1>
                <p class="text-gray-500 mt-0.5">Configure your company profile, contact details, tax information and document defaults</p>
            </div>
            <div class="flex items-center gap-2">
                @if($settings->logo_url)
                    <img src="{{ $settings->logo_url }}" alt="Company Logo" class="h-10 w-auto rounded border border-gray-200 object-contain bg-white px-2">
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Tab Navigation ───────────────────────────────────────────────── --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-0 overflow-x-auto">
                @php
                    $tabs = [
                        'general'   => ['label' => 'General',        'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        'contact'   => ['label' => 'Contact & Address','icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                        'tax'       => ['label' => 'Tax & Registration','icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        'banking'   => ['label' => 'Banking',         'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                        'documents' => ['label' => 'Documents',       'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        'branding'  => ['label' => 'Branding',        'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                    ];
                @endphp
                @foreach($tabs as $key => $tab)
                    <button @click="switchTab('{{ $key }}')"
                            :class="activeTab === '{{ $key }}' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent'"
                            class="flex items-center gap-1.5 px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                        </svg>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ── Form ──────────────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('admin.company-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ════════════════════ TAB: GENERAL ════════════════════ --}}
            <div x-show="activeTab === 'general'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Company Identity
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none @error('company_name') border-red-400 @enderror"
                                       placeholder="e.g. Milele Power Ltd">
                                @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Trading Name / DBA</label>
                                <input type="text" name="trading_name" value="{{ old('trading_name', $settings->trading_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none"
                                       placeholder="If different from company name">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Tagline / Slogan</label>
                                <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none"
                                       placeholder="e.g. Powering your cold chain logistics">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                                <textarea name="description" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none resize-none"
                                          placeholder="Brief company description for landing page, emails, etc.">{{ old('description', $settings->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Business Registration
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Business Type</label>
                                <select name="business_type"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none">
                                    <option value="">— Select —</option>
                                    @foreach([
                                        'Limited Company'        => 'Limited Company (Ltd)',
                                        'Public Limited Company' => 'Public Limited Company (PLC)',
                                        'Sole Proprietorship'    => 'Sole Proprietorship',
                                        'Partnership'            => 'Partnership',
                                        'NGO'                    => 'NGO / Non-Profit',
                                        'Other'                  => 'Other',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" {{ old('business_type', $settings->business_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Year Established</label>
                                <input type="text" name="year_established" value="{{ old('year_established', $settings->year_established) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none"
                                       placeholder="e.g. 2018" maxlength="10">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">
                                    Business Registration No.
                                    <span class="font-normal text-gray-400">(BRELA / TZ)</span>
                                </label>
                                <input type="text" name="registration_number" value="{{ old('registration_number', $settings->registration_number) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none font-mono"
                                       placeholder="e.g. 123456789">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Business License Number</label>
                                <input type="text" name="business_license_number" value="{{ old('business_license_number', $settings->business_license_number) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none font-mono"
                                       placeholder="Business operation licence number">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ════════════════════ TAB: CONTACT & ADDRESS ════════════════════ --}}
            <div x-show="activeTab === 'contact'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Contact Details
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Primary Phone</label>
                                    <input type="text" name="phone_primary" value="{{ old('phone_primary', $settings->phone_primary) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="+255 ...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Secondary Phone</label>
                                    <input type="text" name="phone_secondary" value="{{ old('phone_secondary', $settings->phone_secondary) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="+255 ...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">General Email</label>
                                <input type="email" name="email_general" value="{{ old('email_general', $settings->email_general) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="info@milelepower.co.tz">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Billing Email</label>
                                <input type="email" name="email_billing" value="{{ old('email_billing', $settings->email_billing) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="billing@milelepower.co.tz">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Support Email</label>
                                <input type="email" name="email_support" value="{{ old('email_support', $settings->email_support) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="support@milelepower.co.tz">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Website</label>
                                <input type="url" name="website" value="{{ old('website', $settings->website) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="https://www.milelepower.co.tz">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Working Hours</label>
                                <input type="text" name="working_hours" value="{{ old('working_hours', $settings->working_hours) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="e.g. Mon–Fri 8AM–5PM, Sat 9AM–1PM">
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Social Media</p>
                                <div class="space-y-3">
                                    @foreach([
                                        'facebook_url'  => ['label' => 'Facebook',  'icon' => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
                                        'linkedin_url'  => ['label' => 'LinkedIn',  'icon' => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z M2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
                                        'twitter_url'   => ['label' => 'Twitter/X', 'icon' => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                                        'instagram_url' => ['label' => 'Instagram',  'icon' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z M17.5 6.5h.01 M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5z'],
                                    ] as $field => $meta)
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
                                            </svg>
                                            <input type="url" name="{{ $field }}" value="{{ old($field, $settings->{$field}) }}"
                                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                                   placeholder="{{ $meta['label'] }} URL">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Physical Address
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Address Line 1</label>
                                <input type="text" name="address_line1" value="{{ old('address_line1', $settings->address_line1) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="Street address, building name">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Address Line 2</label>
                                <input type="text" name="address_line2" value="{{ old('address_line2', $settings->address_line2) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="Suite, floor, unit (optional)">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">City</label>
                                    <input type="text" name="city" value="{{ old('city', $settings->city) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. Dar es Salaam">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">District</label>
                                    <input type="text" name="district" value="{{ old('district', $settings->district) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. Ilala">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Region</label>
                                    <input type="text" name="region" value="{{ old('region', $settings->region) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. Coast Region">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Country</label>
                                    <input type="text" name="country" value="{{ old('country', $settings->country) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Postal Code</label>
                                    <input type="text" name="postal_code" value="{{ old('postal_code', $settings->postal_code) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. 11101">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">P.O. Box</label>
                                    <input type="text" name="po_box" value="{{ old('po_box', $settings->po_box) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. 12345">
                                </div>
                            </div>

                            @if($settings->full_address)
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Current Full Address</p>
                                    <p class="text-sm text-gray-700">{{ $settings->full_address }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════ TAB: TAX & REGISTRATION ════════════════════ --}}
            <div x-show="activeTab === 'tax'" x-cloak>
                <div class="max-w-2xl">
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Tax Identification &amp; Compliance
                        </h3>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-5 text-sm text-amber-800 flex gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            These numbers will appear on all issued invoices, quotations, and official correspondence.
                        </div>

                        <div class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        TIN Number
                                        <span class="font-normal text-gray-400 ml-1">Tax Identification Number</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="tin_number" value="{{ old('tin_number', $settings->tin_number) }}"
                                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono tracking-wide @error('tin_number') border-red-400 @enderror"
                                               placeholder="e.g. 100-123-456">
                                        <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Issued by Tanzania Revenue Authority (TRA)</p>
                                    @error('tin_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        VRN Number
                                        <span class="font-normal text-gray-400 ml-1">VAT Registration Number</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="vrn_number" value="{{ old('vrn_number', $settings->vrn_number) }}"
                                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono tracking-wide @error('vrn_number') border-red-400 @enderror"
                                               placeholder="e.g. 40-123456-X">
                                        <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">VAT Registration Number (TRA – if VAT registered)</p>
                                    @error('vrn_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Business Registration No.</label>
                                    <input type="text" name="registration_number" value="{{ old('registration_number', $settings->registration_number) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                           placeholder="BRELA / Business Registry No.">
                                    <p class="text-xs text-gray-400 mt-1">Business Registration &amp; Licensing Agency (BRELA)</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Business License Number</label>
                                    <input type="text" name="business_license_number" value="{{ old('business_license_number', $settings->business_license_number) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                           placeholder="Trade licence / operation permit">
                                </div>
                            </div>

                            {{-- Preview --}}
                            @if($settings->tin_number || $settings->vrn_number)
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Invoice Header Preview</p>
                                    <div class="text-sm text-gray-700 space-y-0.5">
                                        <p class="font-semibold text-gray-900">{{ $settings->company_name }}</p>
                                        @if($settings->tin_number) <p>TIN: <span class="font-mono">{{ $settings->tin_number }}</span></p> @endif
                                        @if($settings->vrn_number) <p>VRN: <span class="font-mono">{{ $settings->vrn_number }}</span></p> @endif
                                        @if($settings->registration_number) <p>Reg No.: <span class="font-mono">{{ $settings->registration_number }}</span></p> @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════ TAB: BANKING ════════════════════ --}}
            <div x-show="activeTab === 'banking'" x-cloak>
                <div class="max-w-2xl">
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                            Bank Account Details
                        </h3>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-5 text-sm text-blue-800 flex gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            These banking details will be printed on invoices and remittance advices.
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $settings->bank_name) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. CRDB Bank, NMB Bank">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Name</label>
                                    <input type="text" name="bank_branch_name" value="{{ old('bank_branch_name', $settings->bank_branch_name) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                           placeholder="e.g. Kariakoo Branch">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Account Name</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $settings->bank_account_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                       placeholder="Account holder name as it appears on bank records">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Account Number</label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $settings->bank_account_number) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono tracking-wide"
                                           placeholder="e.g. 0150123456789">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Code</label>
                                    <input type="text" name="bank_branch_code" value="{{ old('bank_branch_code', $settings->bank_branch_code) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                           placeholder="e.g. 015">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">SWIFT / BIC Code</label>
                                <input type="text" name="bank_swift_code" value="{{ old('bank_swift_code', $settings->bank_swift_code) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                       placeholder="e.g. CORUTZTZ">
                                <p class="text-xs text-gray-400 mt-1">Required for international wire transfers</p>
                            </div>

                            @if($settings->bank_name || $settings->bank_account_number)
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Invoice Banking Block Preview</p>
                                    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ $settings->bank_details_block }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════ TAB: DOCUMENTS ════════════════════ --}}
            <div x-show="activeTab === 'documents'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Finance &amp; Numbering
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Default VAT Rate (%)</label>
                                    <input type="number" name="vat_rate" value="{{ old('vat_rate', $settings->vat_rate) }}"
                                           step="0.01" min="0" max="100"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                                    <p class="text-xs text-gray-400 mt-1">TZ standard rate: 18%</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Default Currency</label>
                                    <select name="default_currency"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                                        @foreach(['TZS' => 'TZS – Tanzanian Shilling', 'USD' => 'USD – US Dollar', 'EUR' => 'EUR – Euro', 'KES' => 'KES – Kenyan Shilling', 'GBP' => 'GBP – British Pound'] as $code => $label)
                                            <option value="{{ $code }}" {{ old('default_currency', $settings->default_currency) === $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Invoice Number Prefix</label>
                                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                           placeholder="e.g. INV">
                                    <p class="text-xs text-gray-400 mt-1">e.g. INV-0001</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Quotation Number Prefix</label>
                                    <input type="text" name="quotation_prefix" value="{{ old('quotation_prefix', $settings->quotation_prefix) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-mono"
                                           placeholder="e.g. QT">
                                    <p class="text-xs text-gray-400 mt-1">e.g. QT-0001</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Default Payment Terms (Days)</label>
                                <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $settings->payment_terms_days) }}"
                                       min="0" max="365"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none">
                                <p class="text-xs text-gray-400 mt-1">Payment due X days from invoice date</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Invoice &amp; Quotation Terms
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Invoice Terms &amp; Conditions</label>
                                    <textarea name="invoice_terms" rows="4"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-y"
                                              placeholder="Legal terms, late payment penalties, dispute resolution...">{{ old('invoice_terms', $settings->invoice_terms) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Invoice Footer Notes</label>
                                    <textarea name="invoice_notes" rows="3"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-y"
                                              placeholder="Thank you message, contact note, etc.">{{ old('invoice_notes', $settings->invoice_notes) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Quotation Terms &amp; Conditions</label>
                                    <textarea name="quotation_terms" rows="4"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-y"
                                              placeholder="Validity period, pricing terms, acceptance conditions...">{{ old('quotation_terms', $settings->quotation_terms) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Instructions</label>
                                    <textarea name="payment_instructions" rows="3"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-y"
                                              placeholder="Bank transfer instructions, mobile money details, etc.">{{ old('payment_instructions', $settings->payment_instructions) }}</textarea>
                                    <p class="text-xs text-gray-400 mt-1">Printed at the bottom of invoices as payment instructions</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Default Contract / Booking Terms</label>
                                    <textarea name="contract_terms" rows="5"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-y"
                                              placeholder="Standard rental contract terms and conditions...">{{ old('contract_terms', $settings->contract_terms) }}</textarea>
                                    <p class="text-xs text-gray-400 mt-1">Used as default terms in generated rental contracts</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ════════════════════ TAB: BRANDING ════════════════════ --}}
            <div x-show="activeTab === 'branding'" x-cloak>
                <div class="max-w-2xl">
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            Logo &amp; Brand Colours
                        </h3>

                        {{-- Logo Upload --}}
                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Company Logo</label>
                            @if($settings->logo_url)
                                <div class="flex items-start gap-4 mb-3">
                                    <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                        <img src="{{ $settings->logo_url }}" alt="Current Logo" class="h-16 w-auto object-contain max-w-40">
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-xs text-gray-500">Current logo</p>
                                        <form method="POST" action="{{ route('admin.company-settings.logo.delete') }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Remove company logo?')"
                                                    class="text-xs text-red-600 hover:text-red-800 underline">Remove logo</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-red-400 transition-colors"
                                 x-data="{ dragover: false }" @dragover.prevent="dragover = true" @dragleave="dragover = false"
                                 :class="dragover ? 'border-red-400 bg-red-50' : ''">
                                <input type="file" name="logo" id="logo-upload" accept="image/*" class="sr-only"
                                       @change="
                                            const file = $event.target.files[0];
                                            if (file) {
                                                const reader = new FileReader();
                                                reader.onload = e => $refs.preview.src = e.target.result;
                                                reader.readAsDataURL(file);
                                                $refs.previewWrapper.classList.remove('hidden');
                                            }
                                       ">
                                <label for="logo-upload" class="cursor-pointer">
                                    <svg class="mx-auto w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG or WebP, max 2 MB</p>
                                </label>
                                <div x-ref="previewWrapper" class="hidden mt-4">
                                    <img x-ref="preview" class="mx-auto h-20 w-auto object-contain rounded-lg border border-gray-200" alt="Preview">
                                    <p class="text-xs text-green-600 mt-1 font-medium">New logo selected</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Logo will be used on invoices, quotations, contracts and the admin panel header.</p>
                        </div>

                        {{-- Colours --}}
                        <div class="border-t border-gray-100 pt-5">
                            <p class="text-xs font-semibold text-gray-800 uppercase tracking-wide mb-4">Brand Colours</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Primary Colour</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#dc2626') }}"
                                               class="w-10 h-10 rounded border border-gray-300 cursor-pointer p-0.5">
                                        <input type="text"
                                               value="{{ old('primary_color', $settings->primary_color ?? '#dc2626') }}"
                                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-red-500 focus:outline-none"
                                               placeholder="#dc2626"
                                               @input="document.querySelector('[name=primary_color]').value = $event.target.value">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Used for buttons, accents and PDF highlights</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Secondary Colour</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#1f2937') }}"
                                               class="w-10 h-10 rounded border border-gray-300 cursor-pointer p-0.5">
                                        <input type="text"
                                               value="{{ old('secondary_color', $settings->secondary_color ?? '#1f2937') }}"
                                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-red-500 focus:outline-none"
                                               placeholder="#1f2937"
                                               @input="document.querySelector('[name=secondary_color]').value = $event.target.value">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Used for headings and dark areas</p>
                                </div>
                            </div>

                            {{-- Live colour preview --}}
                            <div class="mt-4 border border-gray-200 rounded-xl overflow-hidden">
                                <div class="p-4 text-white text-sm font-semibold flex items-center justify-between"
                                     :style="'background-color: ' + (document.querySelector('[name=primary_color]')?.value || '#dc2626')">
                                    <span>{{ $settings->company_name }}</span>
                                    <span class="text-xs opacity-80">Brand Preview</span>
                                </div>
                                <div class="p-4 bg-white">
                                    <div class="flex gap-2">
                                        <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                                                :style="'background-color: ' + (document.querySelector('[name=primary_color]')?.value || '#dc2626')">Primary Button</button>
                                        <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                                                :style="'background-color: ' + (document.querySelector('[name=secondary_color]')?.value || '#1f2937')">Secondary Button</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Save Button (sticky footer) ────────────────────────────────── --}}
            <div class="mt-6 flex items-center justify-between bg-white border border-gray-200 rounded-xl px-6 py-4 shadow-sm sticky bottom-4">
                <p class="text-xs text-gray-400">Last saved: {{ $settings->updated_at ? $settings->updated_at->diffForHumans() : 'Never' }}</p>
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</a>
                    <button type="submit"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Settings
                    </button>
                </div>
            </div>

        </form>
    </div>
</x-admin-layout>
