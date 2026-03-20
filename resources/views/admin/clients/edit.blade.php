<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-600">Customers</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('admin.clients.show', $client) }}" class="text-gray-400 hover:text-gray-600">{{ $client->client_number }}</a>
            <span class="text-gray-300">/</span>
            <span>Edit</span>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.clients.update', $client) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Identity -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Client Information
                        <span class="text-gray-400 font-normal text-sm ml-2">{{ $client->client_number }}</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $client->full_name) }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 @error('full_name') border-red-400 @enderror">
                            @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 @error('email') border-red-400 @enderror">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone *</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 @error('phone') border-red-400 @enderror">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">TIN Number</label>
                            <input type="text" name="tin_number" value="{{ old('tin_number', $client->tin_number) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">VRN</label>
                            <input type="text" name="vrn" value="{{ old('vrn', $client->vrn) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea name="notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">{{ old('notes', $client->notes) }}</textarea>
                    </div>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar: Settings + Submit -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h3 class="font-semibold text-gray-900">Settings</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status', $client->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $client->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blacklisted" {{ old('status', $client->status) === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Risk Level</label>
                        <select name="risk_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="low" {{ old('risk_level', $client->risk_level) === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('risk_level', $client->risk_level) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('risk_level', $client->risk_level) === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Credit Limit (TZS)</label>
                        <input type="number" name="credit_limit" value="{{ old('credit_limit', $client->credit_limit) }}" min="0" step="100000"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Terms (days)</label>
                        <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $client->payment_terms_days) }}" min="0" max="365"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Source</label>
                        <select name="source" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="manual" {{ old('source', $client->source) === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="referral" {{ old('source', $client->source) === 'referral' ? 'selected' : '' }}>Referral</option>
                            <option value="website" {{ old('source', $client->source) === 'website' ? 'selected' : '' }}>Website</option>
                            <option value="phone" {{ old('source', $client->source) === 'phone' ? 'selected' : '' }}>Phone</option>
                            <option value="quote_request" {{ old('source', $client->source) === 'quote_request' ? 'selected' : '' }}>Quote Request</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-red-600 text-white py-2.5 rounded-xl font-semibold hover:bg-red-700 transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.clients.show', $client) }}" class="w-full text-center bg-gray-100 text-gray-700 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
