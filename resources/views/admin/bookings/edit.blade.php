<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-slate-600 hover:text-slate-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Booking</h1>
            <p class="text-slate-600 mt-1">{{ $booking->booking_number }} &mdash; {{ ucfirst($booking->status) }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Rental Details -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Rental Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Generator Type <span class="text-red-500">*</span></label>
                            <select name="genset_type" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Select generator type...</option>
                                @foreach($gensetTypeOptions as $gt)
                                    @php
                                        $val   = $gt->type . ($gt->kva_rating ? ' — ' . $gt->kva_rating . ' KVA' : '');
                                        $label = $gt->type_formatted . ($gt->kva_rating ? ' — ' . $gt->kva_rating . ' KVA' : '');
                                    @endphp
                                    <option value="{{ $val }}" {{ old('genset_type', $booking->genset_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                                {{-- Keep current value selectable even if it doesn't match any fleet genset --}}
                                @if($booking->genset_type && !$gensetTypeOptions->contains(fn($gt) => ($gt->type . ($gt->kva_rating ? ' — ' . $gt->kva_rating . ' KVA' : '')) === $booking->genset_type))
                                    <option value="{{ $booking->genset_type }}" selected>{{ $booking->genset_type }} (current)</option>
                                @endif
                            </select>
                            @error('genset_type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                            <x-input type="date" name="rental_start_date" value="{{ old('rental_start_date', $booking->rental_start_date?->format('Y-m-d')) }}" class="w-full" />
                            @error('rental_start_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Duration (days) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="rental_duration_days" value="{{ old('rental_duration_days', $booking->rental_duration_days) }}" min="1" placeholder="30" class="w-full" />
                            @error('rental_duration_days') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-ON Location <span class="text-red-500">*</span></label>
                            <x-input type="text" name="drop_on_location" value="{{ old('drop_on_location', $booking->drop_on_location) }}" placeholder="e.g. Dar Es Salaam Port, Gate 3" class="w-full" />
                            @error('drop_on_location') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-OFF Location</label>
                            <x-input type="text" name="drop_off_location" value="{{ old('drop_off_location', $booking->drop_off_location) }}" placeholder="Same as Drop-ON or specify..." class="w-full" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Destination</label>
                            <x-input type="text" name="destination" value="{{ old('destination', $booking->destination) }}" placeholder="e.g. Mombasa, Kenya — Kilindini Harbour" class="w-full" />
                            <p class="text-xs text-slate-500 mt-1">Country, region, city or full address of the deployment site.</p>
                        </div>
                    </div>
                </x-card>

                <!-- Notes -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Notes</h2>
                    <textarea name="notes" rows="4" placeholder="Any special instructions or requirements..."
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-slate-700">{{ old('notes', $booking->notes) }}</textarea>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Total Amount -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Total Amount</h2>
                    <div x-data="{
                            currency: '{{ old('currency', $booking->currency ?? 'TZS') }}',
                            isZeroRated: {{ old('is_zero_rated', $booking->is_zero_rated) ? 'true' : 'false' }},
                            subtotal: {{ old('total_amount', $booking->total_amount ?? 0) }},
                            vatRate: 18,
                            get vatAmount() { return this.isZeroRated ? 0 : Math.round(this.subtotal * this.vatRate) / 100; },
                            get total() { return parseFloat(this.subtotal || 0) + this.vatAmount; },
                            fmt(n) { return new Intl.NumberFormat().format(Math.round(n)); }
                        }" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Currency <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="TZS" x-model="currency" class="text-red-600" {{ old('currency', $booking->currency ?? 'TZS') === 'TZS' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">TZS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="USD" x-model="currency" class="text-red-600" {{ old('currency', $booking->currency ?? 'TZS') === 'USD' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">USD</span>
                                </label>
                            </div>
                        </div>
                        <div x-show="currency === 'USD'" x-cloak>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Exchange Rate (1 USD = ? TZS) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="exchange_rate_to_tzs" :value="old('exchange_rate_to_tzs', $booking->exchange_rate_to_tzs > 1 ? $booking->exchange_rate_to_tzs : '')" step="0.0001" min="1" placeholder="e.g. 2700" />
                            <p class="text-xs text-slate-500 mt-1">This rate will be locked on this booking.</p>
                            @error('exchange_rate_to_tzs') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Subtotal (excl. VAT) <span class="text-red-500">*</span>
                                <span class="font-normal text-slate-500" x-text="currency === 'USD' ? '(in USD)' : '(in TZS)'"></span>
                            </label>
                            <x-input type="number" name="total_amount" x-model="subtotal" value="{{ old('total_amount', $booking->total_amount) }}" min="0" step="0.01" placeholder="0.00" class="w-full text-lg font-bold" />
                            @error('total_amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <!-- Zero-rated toggle -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="is_zero_rated" name="is_zero_rated" value="1"
                                x-model="isZeroRated"
                                class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500"
                                {{ old('is_zero_rated', $booking->is_zero_rated) ? 'checked' : '' }}>
                            <label for="is_zero_rated" class="text-sm text-slate-700">Zero-rated VAT (0%) — Exempt sale</label>
                        </div>
                        <!-- VAT summary -->
                        <div class="bg-slate-50 rounded-lg p-4 space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span x-text="currency + ' ' + fmt(subtotal)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span x-text="isZeroRated ? 'VAT (0% — Zero Rated)' : 'VAT (18%)'"></span>
                                <span x-text="isZeroRated ? '0' : currency + ' ' + fmt(vatAmount)" :class="isZeroRated ? 'text-green-700 font-medium' : ''"></span>
                            </div>
                            <div class="flex justify-between font-semibold text-slate-900 border-t border-slate-200 pt-2">
                                <span>Invoice Total</span>
                                <span x-text="currency + ' ' + fmt(total)"></span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Booking Info (read-only) -->
                <x-card>
                    <h2 class="text-sm font-semibold text-slate-700 mb-3">Booking Info</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Number</dt>
                            <dd class="font-mono font-semibold text-slate-800">{{ $booking->booking_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Status</dt>
                            <dd><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-semibold">{{ ucfirst($booking->status) }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Created</dt>
                            <dd class="text-slate-700">{{ $booking->created_at->format('d M Y') }}</dd>
                        </div>
                        @if($booking->client)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Client</dt>
                            <dd class="text-slate-700">{{ $booking->client->company_name ?? $booking->client->contact_person }}</dd>
                        </div>
                        @endif
                    </dl>
                </x-card>

                <!-- Submit -->
                <x-card>
                    <div class="space-y-3">
                        <button type="submit" class="w-full px-5 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            Save Changes
                        </button>
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="block w-full px-5 py-3 border border-slate-300 rounded-lg text-center text-slate-700 hover:bg-slate-50 transition font-medium">
                            Cancel
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</x-admin-layout>
