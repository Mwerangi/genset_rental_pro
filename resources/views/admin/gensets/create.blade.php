<x-admin-layout>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                <a href="{{ route('admin.gensets.index') }}" class="hover:text-red-600">Fleet</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">Add Genset</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Genset</h1>
            <p class="text-gray-500 mt-1">Register a generator in the fleet. An asset number will be assigned automatically.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.gensets.store') }}" class="space-y-6">
            @csrf

            <!-- Identity -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Identity</h2>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. Cummins 100KVA Clip-on #1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                        <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Manufacturer serial">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">Select type...</option>
                            <option value="clip-on" @selected(old('type') === 'clip-on')>Clip-on</option>
                            <option value="underslung" @selected(old('type') === 'underslung')>Underslung</option>
                            <option value="open-frame" @selected(old('type') === 'open-frame')>Open Frame</option>
                            <option value="canopy" @selected(old('type') === 'canopy')>Canopy</option>
                            <option value="other" @selected(old('type') === 'other')>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. Cummins, Perkins, Caterpillar">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <input type="text" name="model" value="{{ old('model') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Model number">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="text" name="color" value="{{ old('color') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. Yellow">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="available" @selected(old('status', 'available') === 'available')>Available</option>
                            <option value="maintenance" @selected(old('status') === 'maintenance')>Maintenance</option>
                            <option value="repair" @selected(old('status') === 'repair')>Under Repair</option>
                            <option value="reserved" @selected(old('status') === 'reserved')>Reserved</option>
                            <option value="retired" @selected(old('status') === 'retired')>Retired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. Warehouse A, Dar es Salaam">
                    </div>
                </div>
            </div>

            <!-- Power & Tech -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Power & Technical</h2>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">KVA Rating</label>
                        <input type="number" name="kva_rating" value="{{ old('kva_rating') }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. 100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">KW Rating</label>
                        <input type="number" name="kw_rating" value="{{ old('kw_rating') }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. 80">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                        <input type="text" name="fuel_type" value="{{ old('fuel_type') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. Diesel">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tank Capacity (Litres)</label>
                        <input type="number" name="tank_capacity_litres" value="{{ old('tank_capacity_litres') }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dimensions (L×W×H mm)</label>
                        <input type="text" name="dimensions" value="{{ old('dimensions') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g. 2500×900×1500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Run Hours</label>
                        <input type="number" name="run_hours" value="{{ old('run_hours', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>
            </div>

            <!-- Rates -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Rental Rates (TZS)</h2>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Daily Rate</label>
                        <input type="number" name="daily_rate" value="{{ old('daily_rate') }}" min="0" step="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weekly Rate</label>
                        <input type="number" name="weekly_rate" value="{{ old('weekly_rate') }}" min="0" step="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rate</label>
                        <input type="number" name="monthly_rate" value="{{ old('monthly_rate') }}" min="0" step="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>
            </div>

            <!-- Acquisition -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Acquisition</h2>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (TZS)</label>
                        <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" min="0" step="1000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <input type="text" name="supplier" value="{{ old('supplier') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capitalize via Bank Account</label>
                        <select name="capitalize_bank_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">— Financed / No cash outflow (CR Loans Payable) —</option>
                            @foreach($bankAccounts as $ba)
                                <option value="{{ $ba->id }}" @selected(old('capitalize_bank_account_id') == $ba->id)>{{ $ba->name }} (Tsh {{ number_format($ba->current_balance, 0) }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">If a purchase price is entered: select a bank account to post DR Fleet / CR Bank, or leave blank to post DR Fleet / CR Loans Payable.</p>
                    </div>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Maintenance Schedule</h2>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Service Date</label>
                        <input type="date" name="last_service_date" value="{{ old('last_service_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Next Service Date</label>
                        <input type="date" name="next_service_date" value="{{ old('next_service_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Interval (hrs)</label>
                        <input type="number" name="service_interval_hours" value="{{ old('service_interval_hours', 250) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Notes</h2>
                </div>
                <div class="p-5">
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Any additional notes about this genset...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pb-8">
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">Save Genset</button>
                <a href="{{ route('admin.gensets.index') }}" class="px-6 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
