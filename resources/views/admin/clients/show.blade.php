<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-600">Customers</a>
            <span class="text-gray-300">/</span>
            <span>{{ $client->client_number }}</span>
        </div>
    </x-slot>

    <div class="px-2 sm:px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ contactModal: false, addressModal: false }">

        <!-- Left Column: Main Info -->
        <div class="lg:col-span-2 min-w-0 space-y-6">

            <!-- Client Header Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 pt-8 pb-6 border-b border-gray-100">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-mono text-red-500 text-sm font-semibold mb-2">{{ $client->client_number }}</p>
                            <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $client->full_name }}</h1>
                            @if($client->company_name)
                                <p class="text-gray-500 mb-3">{{ $client->company_name }}</p>
                            @endif
                            <div class="flex items-center flex-wrap gap-2 mb-3">
                                @if($client->status === 'active')
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#22c55e;">Active</span>
                                @elseif($client->status === 'inactive')
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#6b7280;">Inactive</span>
                                @else
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#ef4444;">Blacklisted</span>
                                @endif
                                @if($client->risk_level === 'low')
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#16a34a;">Low Risk</span>
                                @elseif($client->risk_level === 'medium')
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#f59e0b;">Medium Risk</span>
                                @else
                                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold text-white whitespace-nowrap" style="background:#dc2626;">High Risk</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ $client->email }}</span>
                                <span>{{ $client->phone }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="text-red-600 border border-red-200 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Edit
                        </a>
                    </div>
                </div>

                <!-- Key Stats Row -->
                <div class="flex divide-x divide-gray-100 border-t border-gray-100">
                    <div class="flex-1 flex flex-col items-center justify-center gap-1 px-5 py-4">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center mb-1">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 leading-none">{{ $client->bookings->count() }}</div>
                        <div class="text-xs text-gray-500">Total Bookings</div>
                    </div>
                    <div class="flex-1 flex flex-col items-center justify-center gap-1 px-5 py-4">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-1">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="text-2xl font-bold text-blue-600 leading-none">{{ $activeBookings }}</div>
                        <div class="text-xs text-gray-500">Active Now</div>
                    </div>
                    <div class="flex-1 flex flex-col items-center justify-center gap-1 px-5 py-4">
                        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-1">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="text-lg font-bold text-green-600 leading-none">TZS {{ number_format($totalSpend, 0) }}</div>
                        <div class="text-xs text-gray-500">Total Spend</div>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Booking History</h2>
                    <a href="{{ route('admin.bookings.create', ['client_id' => $client->id]) }}" class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold hover:bg-red-700 transition-colors">
                        + New Booking
                    </a>
                </div>
                @if($client->bookings->count())
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Booking #</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Genset</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Dates</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Total</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($client->bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700 font-semibold">{{ $booking->booking_number }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ strtoupper($booking->genset_type ?? '—') }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $booking->rental_start_date?->format('M d') ?? '—' }}
                                        @if($booking->rental_end_date) – {{ $booking->rental_end_date->format('M d, Y') }} @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $booking->formatted_total }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $bc = ['created' => 'bg-blue-100 text-blue-700', 'approved' => 'bg-green-100 text-green-700', 'active' => 'bg-teal-100 text-teal-700', 'returned' => 'bg-purple-100 text-purple-700', 'invoiced' => 'bg-orange-100 text-orange-700', 'paid' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', 'rejected' => 'bg-red-100 text-red-700'][$booking->status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $bc }}">{{ $booking->status_label }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-blue-600 hover:underline text-xs">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="py-10 text-center text-gray-400 text-sm">No bookings yet.</div>
                @endif
            </div>
        </div>

        <!-- Right Column: Sidebar Info -->
        <div class="min-w-0 w-full space-y-6">

            <!-- Client Details -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden w-full">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Client Profile</h3>
                    <a href="{{ route('admin.clients.edit', $client) }}" class="text-xs text-red-600 font-semibold hover:underline">Edit</a>
                </div>

                <!-- Contact Info -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Contact Details</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Email</p>
                            <p class="text-sm font-medium text-gray-800 break-all">{{ $client->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Phone</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->phone }}</p>
                        </div>
                        @if($client->company_name)
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Company</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->company_name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Tax & Business Registration -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tax & Registration</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">TIN Number</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->tin_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">VRN (VAT)</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->vrn ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Terms -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Financial Terms</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Credit Limit</p>
                            <p class="text-sm font-semibold text-gray-800">TZS {{ number_format($client->credit_limit, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Payment Terms</p>
                            <p class="text-sm font-medium text-gray-800">Net {{ $client->payment_terms_days }} days</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Risk Level</p>
                            @if($client->risk_level === 'low')
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:#16a34a;">Low Risk</span>
                            @elseif($client->risk_level === 'medium')
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:#f59e0b;">Medium Risk</span>
                            @else
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:#dc2626;">High Risk</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account Info</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Source</p>
                            <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $client->source ?? 'manual')) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Client Since</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($client->createdBy)
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Added By</p>
                            <p class="text-sm font-medium text-gray-800">{{ $client->createdBy->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($client->notes)
                <!-- Notes -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $client->notes }}</p>
                </div>
                @endif

                @if($client->quoteRequest)
                <!-- Origin -->
                <div class="px-5 py-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Originated From</p>
                    <a href="{{ route('admin.quote-requests.show', $client->quoteRequest) }}" class="inline-flex items-center gap-1.5 text-sm text-red-600 font-medium hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $client->quoteRequest->request_number }}
                    </a>
                </div>
                @endif
            </div>

            <!-- Contacts -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-sm">Contacts</h3>
                    <button @click="contactModal = true" class="text-xs bg-gray-800 text-white px-2 py-1 rounded font-medium hover:bg-gray-900">+ Add</button>
                </div>
                @if($client->contacts->count())
                    <div class="divide-y divide-gray-50">
                        @foreach($client->contacts as $contact)
                            <div class="px-4 py-3 flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ $contact->name }}
                                        @if($contact->is_primary) <span class="text-xs text-purple-600 font-semibold">(Primary)</span> @endif
                                    </div>
                                    @if($contact->position)<div class="text-xs text-gray-500">{{ $contact->position }}</div>@endif
                                    @if($contact->email)<div class="text-xs text-gray-600">{{ $contact->email }}</div>@endif
                                    @if($contact->phone)<div class="text-xs text-gray-600">{{ $contact->phone }}</div>@endif
                                </div>
                                <form method="POST" action="{{ route('admin.clients.contacts.destroy', [$client, $contact]) }}" onsubmit="return confirm('Remove contact?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-gray-400">No contacts added.</div>
                @endif
            </div>

            <!-- Addresses -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-sm">Addresses</h3>
                    <button @click="addressModal = true" class="text-xs bg-gray-800 text-white px-2 py-1 rounded font-medium hover:bg-gray-900">+ Add</button>
                </div>
                @if($client->addresses->count())
                    <div class="divide-y divide-gray-50">
                        @foreach($client->addresses as $address)
                            <div class="px-4 py-3 flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        @php $tc = ['billing' => 'text-blue-600 bg-blue-50', 'service' => 'text-green-600 bg-green-50', 'office' => 'text-purple-600 bg-purple-50', 'other' => 'text-gray-600 bg-gray-50'][$address->type] ?? 'text-gray-600 bg-gray-50'; @endphp
                                        <span class="px-1.5 py-0.5 rounded text-xs font-semibold {{ $tc }}">{{ ucfirst($address->type) }}</span>
                                        @if($address->is_default)<span class="text-xs text-gray-400">(Default)</span>@endif
                                    </div>
                                    @if($address->label)<div class="text-xs font-medium text-gray-700">{{ $address->label }}</div>@endif
                                    <div class="text-xs text-gray-600">{{ $address->street_address }}</div>
                                    @if($address->city || $address->region)
                                        <div class="text-xs text-gray-500">{{ collect([$address->city, $address->region])->filter()->implode(', ') }}</div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.clients.addresses.destroy', [$client, $address]) }}" onsubmit="return confirm('Remove address?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-gray-400">No addresses added.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div x-show="contactModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6" @click.away="contactModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Add Contact</h3>
            <form method="POST" action="{{ route('admin.clients.contacts.store', $client) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" name="position" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex gap-4 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_primary" value="1" class="rounded"> Primary contact</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="can_authorize_bookings" value="1" class="rounded"> Can authorize</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="can_receive_invoices" value="1" class="rounded"> Receives invoices</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-gray-900 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black">Save Contact</button>
                    <button type="button" @click="contactModal = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div x-show="addressModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6" @click.away="addressModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Add Address</h3>
            <form method="POST" action="{{ route('admin.clients.addresses.store', $client) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="service">Service</option>
                            <option value="billing">Billing</option>
                            <option value="office">Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                        <input type="text" name="label" placeholder="e.g. Dar Warehouse" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Street Address *</label>
                        <input type="text" name="street_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Region</label>
                        <input type="text" name="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" class="rounded"> Set as default address</label>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-gray-900 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black">Save Address</button>
                    <button type="button" @click="addressModal = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-admin-layout>
