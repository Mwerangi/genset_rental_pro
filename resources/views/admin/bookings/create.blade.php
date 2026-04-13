<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="text-slate-600 hover:text-slate-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">New Booking</h1>
            <p class="text-slate-600 mt-1">Create a rental booking directly</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.bookings.store') }}" x-data="bookingForm()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Link to Prospect (optional) -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Linked Prospect</h2>
                    <p class="text-sm text-slate-500 mb-4">Optionally link this booking to an existing quote request. If selected, customer info is pulled from there.</p>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Quote Request (Optional)</label>
                        <select
                            name="quote_request_id"
                            x-model="quoteRequestId"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            <option value="">— No prospect linked —</option>
                            @foreach($quoteRequests as $qr)
                                <option value="{{ $qr->id }}" {{ (old('quote_request_id', $preselected?->id) == $qr->id) ? 'selected' : '' }}>
                                    {{ $qr->request_number }} — {{ $qr->full_name }}{{ $qr->company_name ? ' (' . $qr->company_name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-card>

                <!-- Customer Info (shown only when no prospect linked) -->
                <x-card x-show="!quoteRequestId" x-transition>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Customer Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <x-input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="John Mwangi" class="w-full" />
                            @error('customer_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email <span class="text-red-500">*</span></label>
                            <x-input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="john@company.co.tz" class="w-full" />
                            @error('customer_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                            <x-input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+255 7xx xxx xxx" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Company</label>
                            <x-input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Company Ltd." class="w-full" />
                        </div>
                    </div>
                </x-card>

                <!-- Rental Details -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Rental Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Generator Type <span class="text-red-500">*</span></label>
                            <select name="genset_type" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Select generator type...</option>
                                <option value="clip-on" {{ old('genset_type') === 'clip-on' ? 'selected' : '' }}>Clip-on Generator (20ESX)</option>
                                <option value="underslung" {{ old('genset_type') === 'underslung' ? 'selected' : '' }}>Underslung Generator</option>
                                <option value="10kva" {{ old('genset_type') === '10kva' ? 'selected' : '' }}>10 KVA</option>
                                <option value="20kva" {{ old('genset_type') === '20kva' ? 'selected' : '' }}>20 KVA</option>
                                <option value="30kva" {{ old('genset_type') === '30kva' ? 'selected' : '' }}>30 KVA</option>
                                <option value="45kva" {{ old('genset_type') === '45kva' ? 'selected' : '' }}>45 KVA</option>
                                <option value="60kva" {{ old('genset_type') === '60kva' ? 'selected' : '' }}>60 KVA</option>
                                <option value="100kva" {{ old('genset_type') === '100kva' ? 'selected' : '' }}>100+ KVA</option>
                            </select>
                            @error('genset_type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                            <x-input type="date" name="rental_start_date" value="{{ old('rental_start_date') }}" class="w-full" />
                            @error('rental_start_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Duration (days) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="rental_duration_days" value="{{ old('rental_duration_days') }}" min="1" placeholder="30" class="w-full" />
                            @error('rental_duration_days') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-ON Location <span class="text-red-500">*</span></label>
                            <x-input type="text" name="drop_on_location" value="{{ old('drop_on_location') }}" placeholder="e.g. Dar Es Salaam Port, Gate 3" class="w-full" />
                            @error('drop_on_location') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-OFF Location</label>
                            <x-input type="text" name="drop_off_location" value="{{ old('drop_off_location') }}" placeholder="Same as Drop-ON or specify..." class="w-full" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Destination</label>
                            <x-input type="text" name="destination" value="{{ old('destination') }}" placeholder="e.g. Mombasa, Kenya — Kilindini Harbour" class="w-full" />
                            <p class="text-xs text-slate-500 mt-1">Country, region, city or full address of the deployment site.</p>
                        </div>
                    </div>
                </x-card>

                <!-- Notes -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Notes</h2>
                    <textarea name="notes" rows="4" placeholder="Any special instructions or requirements..." class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-slate-700">{{ old('notes') }}</textarea>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Total Amount -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Total Amount</h2>
                    <div x-data="{ currency: '{{ old('currency', 'TZS') }}' }" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Currency <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="TZS" x-model="currency" class="text-red-600" {{ old('currency', 'TZS') === 'TZS' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">TZS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="USD" x-model="currency" class="text-red-600" {{ old('currency') === 'USD' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">USD</span>
                                </label>
                            </div>
                        </div>
                        <div x-show="currency === 'USD'" x-cloak>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Exchange Rate (1 USD = ? TZS) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="exchange_rate_to_tzs" :value="old('exchange_rate_to_tzs')" step="0.0001" min="1" placeholder="e.g. 2700" />
                            <p class="text-xs text-slate-500 mt-1">This rate will be locked on this booking.</p>
                            @error('exchange_rate_to_tzs') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Total (<span x-text="currency"></span>) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="total_amount" value="{{ old('total_amount') }}" min="0" step="0.01" placeholder="0.00" class="w-full text-lg font-bold" />
                            @error('total_amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-500 mt-2">Enter the agreed total for this booking. If coming from a quotation, this should match the quotation total.</p>
                        </div>
                    </div>
                </x-card>

                <!-- Submit -->
                <x-card>
                    <div class="space-y-3">
                        <button type="submit" class="w-full px-5 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            Create Booking
                        </button>
                        <a href="{{ route('admin.bookings.index') }}" class="block w-full px-5 py-3 border border-slate-300 rounded-lg text-center text-slate-700 hover:bg-slate-50 transition font-medium">
                            Cancel
                        </a>
                    </div>
                </x-card>

                @if($errors->any())
                    <x-card>
                        <div class="text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <p>• {{ $error }}</p>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </form>

    <script>
        function bookingForm() {
            return {
                quoteRequestId: '{{ old('quote_request_id', $preselected?->id ?? '') }}',
            }
        }
    </script>
</x-admin-layout>
