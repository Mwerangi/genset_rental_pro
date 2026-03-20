<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-600">Customers</a>
            <span class="text-gray-300">/</span>
            <span>New Client</span>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.clients.store') }}" x-data="{ hasContact: false, hasAddress: false }">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Identity -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Client Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('full_name') border-red-400 @enderror">
                            @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 @error('email') border-red-400 @enderror">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 @error('phone') border-red-400 @enderror">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">TIN Number</label>
                            <input type="text" name="tin_number" value="{{ old('tin_number') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">VRN</label>
                            <input type="text" name="vrn" value="{{ old('vrn') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea name="notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Optional Contact -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <input type="checkbox" id="hasContact" x-model="hasContact" class="rounded text-purple-600">
                        <label for="hasContact" class="font-semibold text-gray-900 cursor-pointer">Add Primary Contact Person</label>
                    </div>
                    <div x-show="hasContact" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Name</label>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Position / Title</label>
                            <input type="text" name="contact_position" value="{{ old('contact_position') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                </div>

                <!-- Optional Address -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <input type="checkbox" id="hasAddress" x-model="hasAddress" class="rounded text-purple-600">
                        <label for="hasAddress" class="font-semibold text-gray-900 cursor-pointer">Add Address</label>
                    </div>
                    <div x-show="hasAddress" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Type</label>
                            <select name="address_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                <option value="service">Service</option>
                                <option value="billing">Billing</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Label</label>
                            <input type="text" name="address_label" value="{{ old('address_label') }}" placeholder="e.g. Main Office"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Street Address</label>
                            <input type="text" name="street_address" value="{{ old('street_address') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Region</label>
                            <input type="text" name="region" value="{{ old('region') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Settings + Submit -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h3 class="font-semibold text-gray-900">Settings</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Risk Level</label>
                        <select name="risk_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="low" {{ old('risk_level', 'low') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('risk_level') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('risk_level') === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Credit Limit (TZS)</label>
                        <input type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0" step="100000"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Terms (days)</label>
                        <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', 30) }}" min="0" max="365"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Source</label>
                        <select name="source" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="manual" {{ old('source', 'manual') === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="referral" {{ old('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                            <option value="website" {{ old('source') === 'website' ? 'selected' : '' }}>Website</option>
                            <option value="phone" {{ old('source') === 'phone' ? 'selected' : '' }}>Phone</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-red-600 text-white py-2.5 rounded-xl font-semibold hover:bg-red-700 transition-colors">
                        Create Client
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="w-full text-center bg-gray-100 text-gray-700 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
