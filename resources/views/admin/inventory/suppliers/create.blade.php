<x-admin-layout>
    @php $isEdit = isset($supplier); @endphp

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.suppliers.index') }}" class="hover:text-red-600">Suppliers</a>
        <span>/</span>
        <span>{{ $isEdit ? 'Edit '.$supplier->name : 'New Supplier' }}</span>
    </div>

    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $isEdit ? 'Edit Supplier' : 'New Supplier' }}</h1>

        @if($errors->any())
        <div class="mb-5 border rounded-xl p-4 text-sm bg-red-50 border-red-200 text-red-700">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- Basic Info --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Basic Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select —</option>
                            @foreach(['fuel' => 'Fuel', 'parts' => 'Parts & Spares', 'services' => 'Services', 'equipment' => 'Equipment', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}" {{ old('category', $supplier->category ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($isEdit)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Supplier Number</label>
                        <input type="text" value="{{ $supplier->supplier_number ?? '—' }}" disabled
                               class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2.5 text-sm text-gray-500">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Contact Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone (Alt)</label>
                        <input type="text" name="phone_alt" value="{{ old('phone_alt', $supplier->phone_alt ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Website</label>
                        <input type="url" name="website" placeholder="https://" value="{{ old('website', $supplier->website ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Location</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" value="{{ old('address', $supplier->address ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', $supplier->country ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            {{-- Tax & Compliance --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Tax & Compliance</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">TIN Number</label>
                        <input type="text" name="tin_number" value="{{ old('tin_number', $supplier->tin_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">VRN Number <span class="text-xs font-normal text-gray-400">(VAT Registration)</span></label>
                        <input type="text" name="vrn_number" value="{{ old('vrn_number', $supplier->vrn_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            {{-- Payment & Banking --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Payment & Banking</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" placeholder="e.g. Net 30, Cash on delivery"
                               value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="TZS" {{ old('currency', $supplier->currency ?? 'TZS') === 'TZS' ? 'selected' : '' }}>TZS – Tanzanian Shilling</option>
                            <option value="USD" {{ old('currency', $supplier->currency ?? '') === 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $supplier->bank_name ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $supplier->bank_account_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Account Name</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $supplier->bank_account_name ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
            </div>

            {{-- Notes & Status --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Notes & Status</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300">
                        <label for="is_active" class="text-sm text-gray-700">Active supplier</label>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('admin.suppliers.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Discard</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                    {{ $isEdit ? 'Save Changes' : 'Add Supplier' }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
