<x-admin-layout>
    <div x-data="quotationBuilder()">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ $quoteRequest ? route('admin.quote-requests.show', $quoteRequest->id) : route('admin.quote-requests.index') }}" class="text-slate-600 hover:text-slate-900 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Create Quotation</h1>
                    <p class="text-slate-600 mt-1">Build a professional quotation for your customer</p>
                </div>
            </div>
        </div>



        <form method="POST" action="{{ route('admin.quotations.store') }}" class="space-y-6" x-data="{ quoteRequestId: '{{ $quoteRequest?->id ?? '' }}' }">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-red-800 text-sm">Please fix the following errors:</p>
                            <ul class="mt-1 list-disc list-inside text-sm text-red-700 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Information -->
                    @if($quoteRequest)
                        {{-- Linked from a quote request: show read-only info --}}
                        <input type="hidden" name="quote_request_id" value="{{ $quoteRequest->id }}">
                        <x-card>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-slate-900">Customer Information</h2>
                                <x-badge color="blue">From Request: {{ $quoteRequest->request_number }}</x-badge>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-600">Customer Name</p>
                                    <p class="font-medium text-slate-900">{{ $quoteRequest->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-600">Email</p>
                                    <p class="font-medium text-slate-900">{{ $quoteRequest->email }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-600">Phone</p>
                                    <p class="font-medium text-slate-900">{{ $quoteRequest->phone }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-600">Company</p>
                                    <p class="font-medium text-slate-900">{{ $quoteRequest->company_name ?? '-' }}</p>
                                </div>
                            </div>
                        </x-card>
                    @else
                        {{-- No quote request: allow selecting one OR entering customer info directly --}}
                        <x-card>
                            <h2 class="text-lg font-semibold text-slate-900 mb-1">Linked Quote Request</h2>
                            <p class="text-sm text-slate-500 mb-4">Optionally link to an existing quote request, or fill in customer details manually below.</p>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Quote Request (Optional)</label>
                                <select
                                    name="quote_request_id"
                                    x-model="quoteRequestId"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                >
                                    <option value="">— No quote request linked —</option>
                                    @foreach(\App\Models\QuoteRequest::whereNotIn('status', ['converted','rejected'])->orderBy('created_at','desc')->get() as $qr)
                                        <option value="{{ $qr->id }}" {{ old('quote_request_id') == $qr->id ? 'selected' : '' }}>
                                            {{ $qr->request_number }} — {{ $qr->full_name }}{{ $qr->company_name ? ' (' . $qr->company_name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </x-card>

                        {{-- Customer section — visible only when no quote request selected --}}
                        <x-card x-show="!quoteRequestId" x-transition>
                        <div x-data="customerSection()">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-slate-900">Customer Information</h2>
                                {{-- Mode toggle --}}
                                <div class="flex rounded-lg border border-slate-300 overflow-hidden text-sm font-medium">
                                    <button type="button"
                                        @click="mode = 'existing'; clearClient()"
                                        :class="mode === 'existing' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                        class="px-4 py-1.5 transition-colors">
                                        Existing Client
                                    </button>
                                    <button type="button"
                                        @click="mode = 'new'; clearClient()"
                                        :class="mode === 'new' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                        class="px-4 py-1.5 border-l border-slate-300 transition-colors">
                                        New Customer
                                    </button>
                                </div>
                            </div>

                            {{-- EXISTING CLIENT SEARCH --}}
                            <div x-show="mode === 'existing'" x-transition>
                                <input type="hidden" name="client_id" :value="selectedClient ? selectedClient.id : ''" />

                                <div class="relative" @click.outside="dropdownOpen = false">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Search Client <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            x-model="clientSearch"
                                            @focus="dropdownOpen = true"
                                            @input="dropdownOpen = true"
                                            placeholder="Search by name, company, email or client #..."
                                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500 pr-10"
                                            autocomplete="off"
                                        />
                                        <button type="button" x-show="selectedClient" @click="clearClient()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Dropdown results --}}
                                    <div x-show="dropdownOpen && filtered.length > 0" x-transition
                                        class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                        <template x-for="client in filtered" :key="client.id">
                                            <button type="button" @click="selectClient(client)"
                                                class="w-full flex items-start gap-3 px-4 py-3 hover:bg-slate-50 text-left border-b border-slate-100 last:border-0">
                                                <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                                    <span x-text="(client.company_name || client.full_name || '?').charAt(0).toUpperCase()"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-slate-900 text-sm" x-text="client.company_name || client.full_name"></p>
                                                    <p class="text-xs text-slate-500 mt-0.5" x-show="client.company_name && client.full_name" x-text="client.full_name"></p>
                                                    <p class="text-xs text-slate-400" x-text="[client.client_number, client.email].filter(Boolean).join(' · ')"></p>
                                                </div>
                                            </button>
                                        </template>
                                    </div>

                                    <div x-show="dropdownOpen && clientSearch && filtered.length === 0"
                                        class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg px-4 py-3 text-sm text-slate-500">
                                        No clients found matching "<span x-text="clientSearch"></span>"
                                    </div>
                                </div>

                                {{-- Selected client preview --}}
                                <div x-show="selectedClient" x-transition
                                    class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div class="text-sm">
                                        <p class="font-semibold text-green-800" x-text="selectedClient?.company_name || selectedClient?.full_name"></p>
                                        <p class="text-green-700 text-xs mt-0.5" x-show="selectedClient?.company_name" x-text="selectedClient?.full_name"></p>
                                        <p class="text-green-600 text-xs" x-text="[selectedClient?.email, selectedClient?.phone].filter(Boolean).join(' · ')"></p>
                                    </div>
                                </div>

                                @error('client_id') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- NEW CUSTOMER MANUAL FORM --}}
                            <div x-show="mode === 'new'" x-transition>
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
                            </div>
                        </div>{{-- /customerSection --}}
                        </x-card>
                    @endif

                    <!-- Line Items -->
                    <x-card>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Line Items</h2>
                            <button type="button" @click="addItem()" class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="item.id">
                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Item Type -->
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Item Type</label>
                                            <select 
                                                x-model="item.item_type" 
                                                :name="'items[' + index + '][item_type]'"
                                                @change="updateCalculations()"
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                                required
                                            >
                                                <template x-for="type in itemTypes" :key="type.key">
                                                    <option :value="type.key" x-text="type.label"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Description -->
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                                            <input 
                                                type="text" 
                                                x-model="item.description" 
                                                :name="'items[' + index + '][description]'"
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                                placeholder="Enter item description"
                                                required
                                            >
                                        </div>

                                        <!-- Quantity -->
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Quantity</label>
                                            <input 
                                                type="number" 
                                                x-model.number="item.quantity" 
                                                :name="'items[' + index + '][quantity]'"
                                                @input="updateCalculations()"
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                                min="1"
                                                required
                                            >
                                        </div>

                                        <!-- Unit Price -->
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Unit Price (<span x-text="currency"></span>)</label>
                                            <input 
                                                type="number" 
                                                x-model.number="item.unit_price" 
                                                :name="'items[' + index + '][unit_price]'"
                                                @input="updateCalculations()"
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                                min="0"
                                                step="0.01"
                                                required
                                            >
                                        </div>

                                        <!-- Duration (only for rental types) -->
                                        <div x-show="isRentalType(item.item_type)">
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Duration (Days)</label>
                                            <input 
                                                type="number" 
                                                x-model.number="item.duration_days" 
                                                :name="'items[' + index + '][duration_days]'"
                                                @input="updateCalculations()"
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                                min="1"
                                            >
                                        </div>

                                        <!-- Subtotal Display -->
                                        <div class="flex items-end">
                                            <div class="w-full">
                                                <label class="block text-sm font-medium text-slate-700 mb-2">Subtotal</label>
                                                <div class="px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-900 font-semibold">
                                                    <span x-text="currency + ' ' + formatNumber(calculateItemSubtotal(item))"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="mt-4 flex justify-end">
                                        <button 
                                            type="button" 
                                            @click="removeItem(index)"
                                            x-show="items.length > 1"
                                            class="text-red-600 hover:text-red-700 text-sm font-medium flex items-center gap-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remove Item
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div x-show="items.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-600">No items added yet. Click "Add Item" to get started.</p>
                            </div>
                        </div>
                    </x-card>

                    <!-- Additional Details -->
                    <x-card>
                        <h2 class="text-lg font-semibold text-slate-900 mb-4">Additional Details</h2>
                        
                        <div class="space-y-4">
                            <!-- Payment Terms -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Payment Terms</label>
                                <x-textarea 
                                    name="payment_terms" 
                                    rows="2" 
                                    placeholder="e.g., Payment due within 30 days of acceptance"
                                >{{ old('payment_terms', 'Payment due within 30 days of acceptance') }}</x-textarea>
                            </div>

                            <!-- Terms & Conditions -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Terms & Conditions</label>
                                <x-textarea 
                                    name="terms_conditions" 
                                    rows="4" 
                                    placeholder="Enter terms and conditions"
                                >{{ old('terms_conditions') }}</x-textarea>
                            </div>

                            <!-- Internal Notes -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Internal Notes (Not visible to customer)</label>
                                <x-textarea 
                                    name="notes" 
                                    rows="3" 
                                    placeholder="Add any internal notes"
                                >{{ old('notes') }}</x-textarea>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Pricing Summary -->
                    <x-card>
                        <h2 class="text-lg font-semibold text-slate-900 mb-4">Pricing Summary</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Subtotal</span>
                                <span class="font-semibold text-slate-900"><span x-text="currency + ' ' + formatNumber(totals.subtotal)"></span></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">VAT (18%)</span>
                                <span class="font-semibold text-slate-900"><span x-text="currency + ' ' + formatNumber(totals.vat)"></span></span>
                            </div>
                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-900">Total Amount</span>
                                    <span class="text-2xl font-bold text-red-600"><span x-text="currency + ' ' + formatNumber(totals.total)"></span></span>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <!-- Quotation Settings -->
                    <x-card>
                        <h2 class="text-lg font-semibold text-slate-900 mb-4">Quotation Settings</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Valid Until <span class="text-red-600">*</span></label>
                            <x-input 
                                type="date" 
                                name="valid_until" 
                                :value="old('valid_until', now()->addDays(30)->format('Y-m-d'))" 
                                required 
                                :min="now()->addDay()->format('Y-m-d')"
                            />
                            @error('valid_until')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Currency --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Currency <span class="text-red-600">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="TZS" x-model="currency" class="text-red-600" {{ old('currency','TZS') === 'TZS' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">TZS — Tanzanian Shilling</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="USD" x-model="currency" class="text-red-600" {{ old('currency') === 'USD' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700">USD — US Dollar</span>
                                </label>
                            </div>
                            <div x-show="currency === 'USD'" x-cloak class="mt-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Exchange Rate (1 USD = ? TZS) <span class="text-red-600">*</span></label>
                                <x-input type="number" name="exchange_rate_to_tzs" :value="old('exchange_rate_to_tzs')" step="0.0001" min="1" placeholder="e.g. 2650" />
                                <p class="text-xs text-slate-500 mt-1">This rate will be locked on the quotation.</p>
                                @error('exchange_rate_to_tzs')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </x-card>

                    <!-- Actions -->
                    <x-card>
                        <h2 class="text-lg font-semibold text-slate-900 mb-4">Actions</h2>
                        
                        <div class="space-y-3">
                            <button type="submit" name="send" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition font-semibold shadow-lg shadow-red-600/20">
                                Save & Send to Customer
                            </button>
                            <button type="submit" class="w-full bg-slate-600 text-white px-4 py-3 rounded-lg hover:bg-slate-700 transition font-semibold">
                                Save as Draft
                            </button>
                            <a href="{{ $quoteRequest ? route('admin.quote-requests.show', $quoteRequest->id) : route('admin.quote-requests.index') }}" class="block w-full text-center px-4 py-3 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition font-medium">
                                Cancel
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    </div>

    <script>
        const ITEM_TYPES = @json($itemTypes);

        function quotationBuilder() {
            return {
                items: [],
                itemTypes: ITEM_TYPES,
                currency: '{{ old('currency', 'TZS') }}',
                totals: {
                    subtotal: 0,
                    vat: 0,
                    total: 0
                },
                nextId: 1,

                init() {
                    // Add initial item
                    @if($quoteRequest)
                        this.addItem({
                            item_type: 'genset_rental',
                            description: '{{ $quoteRequest->genset_type_formatted }} Rental',
                            quantity: 1,
                            unit_price: 0,
                            duration_days: {{ $quoteRequest->rental_duration_days }}
                        });
                    @else
                        this.addItem();
                    @endif

                    this.updateCalculations();
                },

                addItem(data = {}) {
                    this.items.push({
                        id: this.nextId++,
                        item_type: data.item_type || (ITEM_TYPES[0]?.key ?? 'genset_rental'),
                        description: data.description || '',
                        quantity: data.quantity || 1,
                        unit_price: data.unit_price || 0,
                        duration_days: data.duration_days || null
                    });
                    this.updateCalculations();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.updateCalculations();
                },

                isRentalType(key) {
                    const t = ITEM_TYPES.find(t => t.key === key);
                    return t ? t.is_rental : false;
                },

                calculateItemSubtotal(item) {
                    if (this.isRentalType(item.item_type) && item.duration_days) {
                        return item.quantity * item.unit_price * item.duration_days;
                    }
                    return item.quantity * item.unit_price;
                },

                updateCalculations() {
                    let subtotal = 0;
                    this.items.forEach(item => {
                        subtotal += this.calculateItemSubtotal(item);
                    });

                    const vat = subtotal * 0.18;
                    const total = subtotal + vat;

                    this.totals = {
                        subtotal: subtotal,
                        vat: vat,
                        total: total
                    };
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(num || 0);
                }
            }
        }
        function customerSection() {
            return {
                mode: '{{ old('client_id') ? 'existing' : (old('customer_name') ? 'new' : 'existing') }}',  // default to Existing Client
                clientSearch: '',
                selectedClient: null,
                dropdownOpen: false,
                clients: @json($clients),
                get filtered() {
                    if (!this.clientSearch) return this.clients;
                    const q = this.clientSearch.toLowerCase();
                    return this.clients.filter(c =>
                        (c.full_name || '').toLowerCase().includes(q) ||
                        (c.company_name || '').toLowerCase().includes(q) ||
                        (c.email || '').toLowerCase().includes(q) ||
                        (c.client_number || '').toLowerCase().includes(q) ||
                        (c.phone || '').toLowerCase().includes(q)
                    );
                },
                selectClient(client) {
                    this.selectedClient = client;
                    this.clientSearch = client.company_name || client.full_name;
                    this.dropdownOpen = false;
                },
                clearClient() {
                    this.selectedClient = null;
                    this.clientSearch = '';
                }
            };
        }
    </script>
</x-admin-layout>
