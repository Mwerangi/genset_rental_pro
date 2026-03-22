<x-admin-layout>
    <!-- Header with Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.quote-requests.index') }}" class="text-slate-600 hover:text-slate-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $quoteRequest->request_number }}</h1>
                <p class="text-slate-600 mt-1">Quote request details</p>
            </div>
        </div>
        <x-badge :color="$quoteRequest->status_color" class="text-base px-4 py-2">
            {{ ucfirst($quoteRequest->status) }}
        </x-badge>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Customer Information</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Full Name</p>
                        <p class="text-slate-900">{{ $quoteRequest->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Email</p>
                        <p class="text-slate-900">{{ $quoteRequest->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Phone Number</p>
                        <p class="text-slate-900">{{ $quoteRequest->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Company Name</p>
                        <p class="text-slate-900">{{ $quoteRequest->company_name ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Rental Requirements -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Rental Requirements</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Generator Type</p>
                        <p class="text-slate-900 font-medium">{{ $quoteRequest->genset_type_formatted }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Rental Duration</p>
                        <p class="text-slate-900">{{ $quoteRequest->rental_duration_days }} days</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">Start Date</p>
                        <p class="text-slate-900">{{ $quoteRequest->rental_start_date->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 mb-1">End Date</p>
                        <p class="text-slate-900">{{ $quoteRequest->rental_end_date->format('F d, Y') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm font-medium text-slate-600 mb-1">Delivery Location</p>
                        <p class="text-slate-900">{{ $quoteRequest->delivery_location }}</p>
                    </div>
                    @if($quoteRequest->site_location)
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-slate-600 mb-1">Site Location</p>
                            <p class="text-slate-900">{{ $quoteRequest->site_location }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Additional Requirements -->
            @if($quoteRequest->additional_requirements)
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Additional Requirements</h2>
                    <p class="text-slate-700 whitespace-pre-line">{{ $quoteRequest->additional_requirements }}</p>
                </x-card>
            @endif

            <!-- Quotation -->
            @if($quotation)
                @php $booking = $quotation->booking; @endphp
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Quotation</h2>
                        <x-badge :color="$quotation->status_color">{{ ucfirst($quotation->status) }}</x-badge>
                    </div>

                    @if($booking)
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-green-800">Booking Created</p>
                                <p class="text-xs text-green-700">This quotation was accepted and converted to booking <span class="font-mono font-bold">{{ $booking->booking_number }}</span></p>
                            </div>
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="ml-auto text-sm font-medium text-green-700 hover:text-green-900 whitespace-nowrap">View Booking →</a>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Quotation Number</p>
                            <p class="font-mono text-sm font-bold text-slate-900 mt-1">{{ $quotation->quotation_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Amount</p>
                            <p class="text-lg font-bold text-slate-900 mt-1">{{ $quotation->formatted_total }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Created</p>
                            <p class="text-sm text-slate-900 mt-1">{{ $quotation->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">Valid Until</p>
                            <p class="text-sm {{ $quotation->valid_until->isPast() && $quotation->status !== 'accepted' ? 'text-red-600 font-medium' : 'text-slate-900' }} mt-1">
                                {{ $quotation->valid_until->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">View Details</a>

                        @if($quotation->canBeEdited())
                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-sm font-medium transition">Edit &amp; Resend</a>
                        @endif

                        <a href="{{ route('admin.quotations.download-pdf', $quotation) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">Download PDF</a>

                        @if(in_array($quotation->status, ['draft', 'sent', 'viewed']))
                            <div x-data="{ open: false }">
                                <!-- Trigger -->
                                <button
                                    type="button"
                                    @click="open = true"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                                    Accept &amp; Create Booking
                                </button>

                                <!-- Confirmation Modal -->
                                <div
                                    x-show="open"
                                    x-cloak
                                    class="fixed inset-0 z-50 flex items-center justify-center"
                                >
                                    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
                                    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6" @click.stop>
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-slate-900">Accept Quotation</h3>
                                            <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex gap-3 mb-5">
                                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">Accept {{ $quotation->quotation_number }}?</p>
                                                <p class="text-sm text-slate-500 mt-1">This will mark the quotation as accepted and automatically create a booking. This action cannot be undone.</p>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.quotations.approve', $quotation) }}">
                                            @csrf
                                            <div class="flex gap-3">
                                                <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2.5 rounded-lg hover:bg-green-700 transition font-semibold">
                                                    Yes, Accept &amp; Create Booking
                                                </button>
                                                <button type="button" @click="open = false" class="flex-1 border border-slate-300 text-slate-700 px-4 py-2.5 rounded-lg hover:bg-slate-50 transition font-medium">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <button
                                onclick="document.getElementById('quotation-reject-modal').classList.remove('hidden')"
                                class="px-4 py-2 border border-red-300 hover:bg-red-50 text-red-700 rounded-lg text-sm font-medium transition"
                            >Reject Quotation</button>
                        @endif
                    </div>
                </x-card>
            @else
                <x-card>
                    <div class="text-center py-8">
                        <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-900">No quotation yet</h3>
                        <p class="mt-1 text-sm text-slate-500 mb-4">Create a quotation to share pricing with this prospect.</p>
                        <a href="{{ route('admin.quotations.create', ['quote_request_id' => $quoteRequest->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Quotation
                        </a>
                    </div>
                </x-card>
            @endif

            <!-- Activity Log -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Activity Log</h2>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">Quote request submitted</p>
                            <p class="text-sm text-slate-600">{{ $quoteRequest->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($quoteRequest->status === 'reviewed' && $quoteRequest->reviewedBy)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900">Marked as reviewed by {{ $quoteRequest->reviewedBy->name }}</p>
                                <p class="text-sm text-slate-600">{{ $quoteRequest->reviewed_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endif

                    @if($quoteRequest->status === 'rejected')
                        <div class="flex gap-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900">Request rejected</p>
                                @if($quoteRequest->rejection_reason)
                                    <p class="text-sm text-slate-700 mt-1">{{ $quoteRequest->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    @if($quoteRequest->status !== 'converted' && $quoteRequest->status !== 'rejected')
                        @if(!$quotation)
                            <x-button href="{{ route('admin.quotations.create', ['quote_request_id' => $quoteRequest->id]) }}" class="w-full justify-center">
                                Create Quotation
                            </x-button>
                        @elseif($quotation->canBeEdited())
                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="block w-full px-4 py-2 bg-red-600 text-white rounded-lg text-center hover:bg-red-700 transition font-medium">
                                Edit &amp; Resend Quotation
                            </a>
                        @endif

                        @if($quotation && $quotation->booking)
                            <a href="{{ route('admin.bookings.show', $quotation->booking) }}" class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg text-center hover:bg-green-700 transition font-medium">
                                View Booking
                            </a>
                        @endif

                        <a href="mailto:{{ $quoteRequest->email }}" class="block w-full px-4 py-2 border border-slate-300 rounded-lg text-center text-slate-700 hover:bg-slate-50 transition font-medium">
                            Send Email
                        </a>

                        @if($quoteRequest->status === 'new')
                            <form method="POST" action="{{ route('admin.quote-requests.mark-as-reviewed', $quoteRequest->id) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-center text-slate-700 hover:bg-slate-50 transition font-medium">
                                    Mark as Reviewed
                                </button>
                            </form>
                        @endif

                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="w-full px-4 py-2 border border-red-300 rounded-lg text-center text-red-700 hover:bg-red-50 transition font-medium">
                            Reject Request
                        </button>
                    @endif
                </div>
            </x-card>

            <!-- Request Details -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Request Details</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Request Number</p>
                        <p class="font-mono text-sm text-slate-900 mt-1">{{ $quoteRequest->request_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600">Submitted</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $quoteRequest->created_at->format('F d, Y \a\t H:i') }}</p>
                    </div>
                    @if($quoteRequest->reviewedBy)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Reviewed By</p>
                            <p class="text-sm text-slate-900 mt-1">{{ $quoteRequest->reviewedBy->name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-slate-600">Status</p>
                        <x-badge :color="$quoteRequest->status_color" class="mt-1">
                            {{ ucfirst($quoteRequest->status) }}
                        </x-badge>
                    </div>
                </div>
            </x-card>

            <!-- Contact Information -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Contact</h2>
                <div class="space-y-3">
                    <a href="tel:{{ $quoteRequest->phone }}" class="flex items-center gap-3 text-slate-700 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="font-medium">{{ $quoteRequest->phone }}</span>
                    </a>
                    <a href="mailto:{{ $quoteRequest->email }}" class="flex items-center gap-3 text-slate-700 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium text-sm break-all">{{ $quoteRequest->email }}</span>
                    </a>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Reject Quote Request Modal -->
    <div id="reject-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Reject Quote Request</h3>
            <form method="POST" action="{{ route('admin.quote-requests.reject', $quoteRequest->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Rejection Reason (Optional)</label>
                    <textarea name="reason" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Enter reason for rejection..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($quotation && in_array($quotation->status, ['draft', 'sent', 'viewed']))
    <!-- Reject Quotation Modal -->
    <div id="quotation-reject-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Reject Quotation</h3>
            <form method="POST" action="{{ route('admin.quotations.reject', $quotation) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" rows="4" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Enter reason for rejection..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('quotation-reject-modal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        Reject Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-admin-layout>
