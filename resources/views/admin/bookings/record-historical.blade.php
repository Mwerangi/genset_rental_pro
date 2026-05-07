<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="text-slate-600 hover:text-slate-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Record Historical Sale</h1>
            <p class="text-slate-500 mt-1 text-sm">Add past rentals that were completed before this system was in use.</p>
        </div>
    </div>

    <!-- Info banner -->
    <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-amber-800">Records created here bypass the normal approval workflow. They are created with status <strong>Paid</strong> and tagged as historical entries, appearing in all revenue reports and accounting.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Mode tabs -->
    <div x-data="{ mode: 'single' }" class="mb-6">
        <div class="flex gap-1 bg-slate-100 p-1 rounded-lg w-fit">
            <button type="button" @click="mode = 'single'"
                :class="mode === 'single' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Single Entry
            </button>
            <button type="button" @click="mode = 'bulk'"
                :class="mode === 'bulk' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
                Bulk Upload (Excel)
            </button>
        </div>

        {{-- ── SINGLE ENTRY ─────────────────────────────────────────── --}}
        <div x-show="mode === 'single'">

    <div x-data="newClientModal()">

    <form method="POST" action="{{ route('admin.bookings.store-historical') }}"
          x-data="{
              currency: '{{ old('currency', 'TZS') }}',
              isZeroRated: {{ old('is_zero_rated') ? 'true' : 'false' }},
              subtotal: {{ old('subtotal', 0) }},
              vatRate: 18,
              get vatAmount() { return this.isZeroRated ? 0 : Math.round(this.subtotal * this.vatRate) / 100; },
              get total() { return parseFloat(this.subtotal || 0) + this.vatAmount; },
              fmt(n) { return new Intl.NumberFormat().format(Math.round(n)); }
          }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT: Rental Details + Client -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Client -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Client</h2>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-slate-700">Select Client <span class="text-red-500">*</span></label>
                            <button type="button" @click="open = true"
                                class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Client
                            </button>
                        </div>
                        <select id="client_id_select" name="client_id" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— choose client —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->company_name ?? $client->full_name }}
                                    ({{ $client->client_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </x-card>

                <!-- Rental Details -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Rental Details</h2>
                    <div class="grid grid-cols-2 gap-4">

                        <!-- Genset -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Genset (optional)</label>
                            <select name="genset_id"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">— leave blank if unknown —</option>
                                @foreach($gensets as $genset)
                                    <option value="{{ $genset->id }}" {{ old('genset_id') == $genset->id ? 'selected' : '' }}>
                                        {{ $genset->asset_number }} — {{ $genset->name }}
                                        @if($genset->kva_rating) ({{ $genset->kva_rating }} KVA) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Genset Type -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Genset Type / Description</label>
                            <x-input type="text" name="genset_type"
                                value="{{ old('genset_type') }}"
                                placeholder="e.g. 45 KVA Clip-on, 100 KVA Underslung..."
                                class="w-full" />
                        </div>

                        <!-- Dates -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Rental Start Date <span class="text-red-500">*</span></label>
                            <x-input type="date" name="rental_start_date"
                                value="{{ old('rental_start_date') }}" required class="w-full" />
                            @error('rental_start_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Rental End Date <span class="text-red-500">*</span></label>
                            <x-input type="date" name="rental_end_date"
                                value="{{ old('rental_end_date') }}" required class="w-full" />
                            @error('rental_end_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Locations -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-ON Location <span class="text-red-500">*</span></label>
                            <x-input type="text" name="drop_on_location"
                                value="{{ old('drop_on_location') }}"
                                placeholder="e.g. Dar Es Salaam Port, Gate 3" required class="w-full" />
                            @error('drop_on_location') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Drop-OFF Location</label>
                            <x-input type="text" name="drop_off_location"
                                value="{{ old('drop_off_location') }}"
                                placeholder="Same as Drop-ON or specify..." class="w-full" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Destination</label>
                            <x-input type="text" name="destination"
                                value="{{ old('destination') }}"
                                placeholder="e.g. Mombasa, Kenya — Kilindini Harbour" class="w-full" />
                            <p class="text-xs text-slate-500 mt-1">Country, region, city or full address of the deployment site.</p>
                        </div>
                    </div>
                </x-card>

                <!-- Invoice / Pricing -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Invoice &amp; Pricing</h2>
                    <div class="space-y-4">

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Service Description <span class="text-red-500">*</span></label>
                            <x-input type="text" name="description"
                                value="{{ old('description', 'Genset Rental Services') }}"
                                placeholder="e.g. Genset Rental — 45 KVA, 30 days" required class="w-full" />
                            @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Currency -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Currency <span class="text-red-500">*</span></label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="TZS" x-model="currency" class="text-red-600">
                                    <span class="text-sm font-medium text-slate-700">TZS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="currency" value="USD" x-model="currency" class="text-red-600">
                                    <span class="text-sm font-medium text-slate-700">USD</span>
                                </label>
                            </div>
                        </div>

                        <!-- Exchange rate (USD only) -->
                        <div x-show="currency === 'USD'" style="display:none">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Exchange Rate (1 USD = ? TZS) <span class="text-red-500">*</span></label>
                            <x-input type="number" name="exchange_rate_to_tzs"
                                value="{{ old('exchange_rate_to_tzs') }}"
                                step="0.0001" min="0.0001" placeholder="e.g. 2550" class="w-full" />
                            @error('exchange_rate_to_tzs') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Subtotal -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Subtotal (excl. VAT) <span class="text-red-500">*</span>
                                <span class="font-normal text-slate-500" x-text="currency === 'USD' ? '(in USD)' : '(in TZS)'"></span>
                            </label>
                            <x-input type="number" name="subtotal"
                                x-model="subtotal"
                                value="{{ old('subtotal') }}"
                                step="0.01" min="0" required placeholder="0.00" class="w-full" />
                            @error('subtotal') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Zero rated toggle -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="is_zero_rated" name="is_zero_rated" value="1"
                                x-model="isZeroRated"
                                class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500"
                                {{ old('is_zero_rated') ? 'checked' : '' }}>
                            <label for="is_zero_rated" class="text-sm text-slate-700">Zero-rated VAT (0%) — Exempt sale</label>
                        </div>

                        <!-- VAT summary -->
                        <div class="bg-slate-50 rounded-lg p-4 space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span x-text="currency + ' ' + fmt(subtotal)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span x-text="isZeroRated ? 'VAT (0%)' : 'VAT (18%)'"></span>
                                <span x-text="currency + ' ' + fmt(vatAmount)"></span>
                            </div>
                            <div class="flex justify-between font-semibold text-slate-900 border-t border-slate-200 pt-2">
                                <span>Total</span>
                                <span x-text="currency + ' ' + fmt(total)"></span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Notes -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Notes</h2>
                    <textarea name="notes" rows="3" placeholder="Any additional context about this historical record..."
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-slate-700">{{ old('notes') }}</textarea>
                </x-card>

            </div>

            <!-- RIGHT: Payment + Submit -->
            <div class="space-y-6">

                <!-- Invoice Date -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Invoice Date</h2>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Invoice / Issue Date <span class="text-red-500">*</span></label>
                        <x-input type="date" name="issue_date"
                            value="{{ old('issue_date') }}" required class="w-full" />
                        @error('issue_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </x-card>

                <!-- Payment -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Payment Record</h2>
                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Received Into <span class="text-red-500">*</span></label>
                            <select name="bank_account_id" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">— select bank account —</option>
                                @foreach($bankAccounts as $ba)
                                    <option value="{{ $ba->id }}" {{ old('bank_account_id') == $ba->id ? 'selected' : '' }}>
                                        {{ $ba->bank_name }} — {{ $ba->name }} ({{ $ba->currency }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_account_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                            <x-input type="date" name="payment_date"
                                value="{{ old('payment_date') }}" required class="w-full" />
                            @error('payment_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Payment Reference</label>
                            <x-input type="text" name="payment_reference"
                                value="{{ old('payment_reference') }}"
                                placeholder="Receipt #, transfer ref, cheque #..." class="w-full" />
                        </div>
                    </div>
                </x-card>

                <!-- Summary & Submit -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-3">Summary</h2>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Invoice</span>
                            <span class="font-semibold text-slate-900" x-text="currency + ' ' + fmt(total)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Status after save</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Paid
                            </span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Record type</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-medium rounded-full">Historical</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition text-sm">
                        Save Historical Sale
                    </button>
                    <a href="{{ route('admin.bookings.index') }}"
                        class="mt-2 w-full inline-block text-center py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition text-sm">
                        Cancel
                    </a>
                </x-card>
            </div>

        </div>
    </form>

    <!-- ── New Client Modal ──────────────────────────────────────────────── -->
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="open = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

        <!-- Panel -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Add New Client</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <!-- Error -->
                <template x-if="errors.length">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 space-y-0.5">
                        <template x-for="e in errors" :key="e">
                            <p x-text="'• ' + e"></p>
                        </template>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.full_name" placeholder="Contact person name"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm text-slate-700" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Company Name</label>
                        <input type="text" x-model="form.company_name" placeholder="Company / organisation"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm text-slate-700" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.phone" placeholder="+255 ..."
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm text-slate-700" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <input type="email" x-model="form.email" placeholder="optional"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm text-slate-700" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">TIN Number</label>
                        <input type="text" x-model="form.tin_number" placeholder="optional"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm text-slate-700" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                    Cancel
                </button>
                <button type="button" @click="submit()" :disabled="saving"
                    class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save Client'"></span>
                </button>
            </div>
        </div>
    </div>

    </div>{{-- end x-data="newClientModal()" --}}

    <script>
    function newClientModal() {
        return {
            open: false,
            saving: false,
            errors: [],
            form: { full_name: '', company_name: '', phone: '', email: '', tin_number: '' },

            async submit() {
                this.errors = [];
                this.saving = true;
                try {
                    const res = await fetch('{{ route('admin.clients.quick-store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.form),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        // Laravel validation errors come as { errors: { field: [msgs] } }
                        if (data.errors) {
                            this.errors = Object.values(data.errors).flat();
                        } else {
                            this.errors = [data.message ?? 'An error occurred.'];
                        }
                        return;
                    }

                    // Add new option to the select and auto-select it
                    const select = document.getElementById('client_id_select');
                    const option = new Option(data.label, data.id, true, true);
                    select.add(option);

                    // Reset & close
                    this.form = { full_name: '', company_name: '', phone: '', email: '', tin_number: '' };
                    this.open = false;
                } catch (e) {
                    this.errors = ['Network error. Please try again.'];
                } finally {
                    this.saving = false;
                }
            }
        };
    }
    </script>

    </div>{{-- end x-show single --}}

    {{-- ── BULK UPLOAD ──────────────────────────────────────────────── --}}
    <div x-show="mode === 'bulk'" style="display:none" class="mt-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <!-- Step 1: Download template -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Step 1 — Download the Template</h2>
                    <p class="text-sm text-slate-500 mb-4">Download the Excel template, fill in your historical sales data (one row per sale), then come back and upload it below. The template includes 2 sample rows and an Instructions sheet.</p>
                    <a href="{{ route('admin.bookings.historical-template') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Template (xlsx)
                    </a>
                </x-card>

                <!-- Step 2: Upload filled file -->
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Step 2 — Upload Completed File</h2>
                    <p class="text-sm text-slate-500 mb-4">Upload your filled spreadsheet. The system will parse the data and show you a preview before anything is saved.</p>

                    <form method="POST" action="{{ route('admin.bookings.bulk-historical-preview') }}"
                          enctype="multipart/form-data"
                          x-data="{
                              dragOver: false,
                              fileName: '',
                              setFile(files) {
                                  if (files.length) {
                                      this.fileName = files[0].name;
                                      this.$refs.fileInput.files = files;
                                  }
                              }
                          }">
                        @csrf

                        <!-- Drop zone -->
                        <div @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="dragOver = false; setFile($event.dataTransfer.files)"
                             @click="$refs.fileInput.click()"
                             :class="dragOver ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                             class="border-2 border-dashed rounded-xl p-10 text-center cursor-pointer transition">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v8"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-700" x-text="fileName || 'Drop your Excel file here, or click to browse'"></p>
                            <p class="text-xs text-slate-400 mt-1">.xlsx or .xls — max 10 MB</p>
                            <input type="file" name="bulk_file" accept=".xlsx,.xls" x-ref="fileInput" class="hidden"
                                @change="fileName = $event.target.files[0]?.name || ''">
                        </div>

                        @error('bulk_file')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror

                        <div class="mt-4 flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Parse &amp; Preview
                            </button>
                            <span class="text-xs text-slate-500">You will see a preview before anything is saved.</span>
                        </div>
                    </form>
                </x-card>

            </div>

            <!-- Tips sidebar -->
            <div class="space-y-4">
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Template Tips</h3>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✓</span>
                            <span><strong>client_identifier</strong>: use the client number (CL-YYYY-####) for an exact match, or type the exact company name. Unknown clients will be created automatically.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✓</span>
                            <span><strong>Dates</strong> must be in <code class="bg-slate-100 px-1 rounded">YYYY-MM-DD</code> format (e.g. 2024-01-15).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✓</span>
                            <span><strong>subtotal</strong>: numeric only, no commas or currency symbols.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✓</span>
                            <span><strong>payment_method</strong>: must be one of <code class="bg-slate-100 px-1 rounded">cash</code>, <code class="bg-slate-100 px-1 rounded">bank_transfer</code>, <code class="bg-slate-100 px-1 rounded">mpesa</code>, <code class="bg-slate-100 px-1 rounded">cheque</code>, or <code class="bg-slate-100 px-1 rounded">other</code>.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 mt-0.5">!</span>
                            <span>Delete the two sample rows before uploading your real data.</span>
                        </li>
                    </ul>
                </x-card>
            </div>
        </div>
    </div>{{-- end x-show bulk --}}

    </div>{{-- end mode tabs x-data --}}
</x-admin-layout>

