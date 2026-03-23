<x-admin-layout>
    <!-- Accept Confirmation Modal -->
    <div
        x-data="{ open: false }"
        @open-accept-modal.window="open = true"
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

    <!-- Rejection Modal -->
    <div
        x-data="{ open: false, reason: '' }"
        @open-reject-modal.window="open = true"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Reject Quotation</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-slate-600 mb-4">
                Please provide a reason for rejecting <span class="font-semibold text-slate-900">{{ $quotation->quotation_number }}</span>. This will be recorded for reference.
            </p>

            <form method="POST" action="{{ route('admin.quotations.reject', $quotation) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Rejection Reason <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        name="rejection_reason"
                        x-model="reason"
                        rows="4"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm resize-none"
                        placeholder="Enter the reason for rejection..."
                        required
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="reason.trim() === ''"
                        class="flex-1 bg-red-600 text-white px-4 py-2.5 rounded-lg hover:bg-red-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirm Rejection
                    </button>
                    <button
                        type="button"
                        @click="open = false"
                        class="flex-1 border border-slate-300 text-slate-700 px-4 py-2.5 rounded-lg hover:bg-slate-50 transition font-medium"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header with Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.quotations.index') }}" class="text-slate-600 hover:text-slate-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $quotation->quotation_number }}</h1>
                <p class="text-slate-600 mt-1">Quotation details</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($quotation->currency === 'USD')
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm font-semibold">
                USD — @ {{ number_format($quotation->exchange_rate_to_tzs, 0) }} TZS
            </span>
            @endif
            <x-badge :color="$quotation->status_color" class="text-base px-4 py-2">
                {{ ucfirst($quotation->status) }}
            </x-badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information -->
            @if($quotation->quoteRequest)
                <x-card>
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Customer Information</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-slate-600 mb-1">Full Name</p>
                            <p class="text-slate-900">{{ $quotation->quoteRequest->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 mb-1">Email</p>
                            <p class="text-slate-900">{{ $quotation->quoteRequest->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 mb-1">Phone Number</p>
                            <p class="text-slate-900">{{ $quotation->quoteRequest->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 mb-1">Company Name</p>
                            <p class="text-slate-900">{{ $quotation->quoteRequest->company_name ?? '-' }}</p>
                        </div>
                    </div>
                </x-card>
            @endif

            <!-- Line Items -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-slate-600">Description</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-slate-600">Qty</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-slate-600">Unit Price</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-slate-600">Duration</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-slate-600">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($quotation->items as $item)
                                <tr>
                                    <td class="py-3 px-4">
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $item->description }}</p>
                                            <p class="text-sm text-slate-600">{{ $item->item_type_formatted }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-900">{{ $item->quantity }}</td>
                                    <td class="py-3 px-4 text-right text-slate-900">{{ $quotation->currencySymbol() }} {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-slate-900">
                                        @if($item->item_type === 'genset_rental' && $item->duration_days)
                                            {{ $item->duration_days }} days
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-slate-900">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-300">
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-right font-medium text-slate-600">Subtotal</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-900">{{ $quotation->formatAmount($quotation->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-right font-medium text-slate-600">VAT ({{ $quotation->vat_rate }}%)</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-900">{{ $quotation->formatAmount($quotation->vat_amount) }}</td>
                            </tr>
                            <tr class="border-t border-slate-200">
                                <td colspan="4" class="py-3 px-4 text-right font-bold text-slate-900">Total Amount</td>
                                <td class="py-3 px-4 text-right font-bold text-2xl text-red-600">{{ $quotation->formatted_total }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <!-- Additional Details -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Terms & Conditions</h2>
                
                @if($quotation->payment_terms)
                    <div class="mb-4">
                        <p class="text-sm font-medium text-slate-600 mb-2">Payment Terms</p>
                        <p class="text-slate-900 whitespace-pre-line">{{ $quotation->payment_terms }}</p>
                    </div>
                @endif

                @if($quotation->terms_conditions)
                    <div class="mb-4">
                        <p class="text-sm font-medium text-slate-600 mb-2">Terms & Conditions</p>
                        <p class="text-slate-900 whitespace-pre-line">{{ $quotation->terms_conditions }}</p>
                    </div>
                @endif

                @if($quotation->notes)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-amber-800 mb-2">Internal Notes</p>
                        <p class="text-amber-900 text-sm whitespace-pre-line">{{ $quotation->notes }}</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    @if($quotation->canBeEdited())
                        <a href="{{ route('admin.quotations.edit', $quotation) }}" class="block w-full bg-slate-600 text-white px-4 py-2.5 rounded-lg hover:bg-slate-700 transition font-medium text-center">
                            Edit Quotation
                        </a>
                    @endif

                    @if($quotation->status === 'draft')
                        <button class="w-full bg-red-600 text-white px-4 py-2.5 rounded-lg hover:bg-red-700 transition font-medium">
                            Send to Customer
                        </button>
                    @endif

                    @if(in_array($quotation->status, ['draft', 'sent', 'viewed']))
                        <!-- Approve -->
                        <button
                            type="button"
                            @click="$dispatch('open-accept-modal')"
                            class="w-full bg-green-600 text-white px-4 py-2.5 rounded-lg hover:bg-green-700 transition font-semibold flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approve Quotation
                        </button>

                        <!-- Reject -->
                        <button
                            type="button"
                            @click="$dispatch('open-reject-modal')"
                            class="w-full bg-red-50 text-red-700 border border-red-300 px-4 py-2.5 rounded-lg hover:bg-red-100 transition font-semibold flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Reject Quotation
                        </button>
                    @endif

                    @if($quotation->status === 'accepted')
                        <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2.5">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-semibold text-green-800">Quotation Approved</span>
                        </div>
                    @endif

                    @if($quotation->status === 'rejected')
                        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-semibold text-red-800">Quotation Rejected</span>
                            </div>
                            @if($quotation->rejection_reason)
                                <p class="text-xs text-red-700 mt-1">{{ $quotation->rejection_reason }}</p>
                            @endif
                        </div>
                    @endif

                    @if($quotation->status !== 'draft')
                        <a href="{{ route('admin.quotations.download-pdf', $quotation) }}" class="block w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                            Download PDF
                        </a>
                    @endif

                    @if($quotation->quoteRequest)
                        <a href="{{ route('admin.quote-requests.show', $quotation->quoteRequest) }}" class="block w-full px-4 py-2.5 border border-slate-300 rounded-lg text-center text-slate-700 hover:bg-slate-50 transition font-medium">
                            View Quote Request
                        </a>
                    @endif
                </div>
            </x-card>

            <!-- Quotation Details -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Quotation Details</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Quotation Number</p>
                        <p class="font-mono text-sm text-slate-900 mt-1">{{ $quotation->quotation_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600">Created</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $quotation->created_at->format('F d, Y \a\t H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600">Valid Until</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $quotation->valid_until->format('F d, Y') }}</p>
                        @if($quotation->isExpired())
                            <x-badge color="red" class="mt-1">Expired</x-badge>
                        @else
                            <p class="text-xs text-slate-600 mt-1">{{ $quotation->valid_until->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600">Created By</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $quotation->createdBy->name }}</p>
                    </div>
                    @if($quotation->sent_at)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Sent At</p>
                            <p class="text-sm text-slate-900 mt-1">{{ $quotation->sent_at->format('F d, Y \a\t H:i') }}</p>
                        </div>
                    @endif
                    @if($quotation->accepted_at)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Approved At</p>
                            <p class="text-sm text-green-700 font-medium mt-1">{{ $quotation->accepted_at->format('F d, Y \a\t H:i') }}</p>
                        </div>
                    @endif
                    @if($quotation->rejected_at)
                        <div>
                            <p class="text-sm font-medium text-slate-600">Rejected At</p>
                            <p class="text-sm text-red-700 font-medium mt-1">{{ $quotation->rejected_at->format('F d, Y \a\t H:i') }}</p>
                        </div>
                        @if($quotation->rejection_reason)
                            <div>
                                <p class="text-sm font-medium text-slate-600">Rejection Reason</p>
                                <p class="text-sm text-red-700 mt-1 italic">{{ $quotation->rejection_reason }}</p>
                            </div>
                        @endif
                    @endif
                    <div>
                        <p class="text-sm font-medium text-slate-600">Status</p>
                        <x-badge :color="$quotation->status_color" class="mt-1">
                            {{ ucfirst($quotation->status) }}
                        </x-badge>
                    </div>
                </div>
            </x-card>

            <!-- Pricing Summary -->
            <x-card>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Pricing Summary</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Subtotal</span>
                        <span class="font-semibold text-slate-900">{{ $quotation->formatAmount($quotation->subtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">VAT ({{ $quotation->vat_rate }}%)</span>
                        <span class="font-semibold text-slate-900">{{ $quotation->formatAmount($quotation->vat_amount) }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-900">Total Amount</span>
                            <span class="text-xl font-bold text-red-600">{{ $quotation->formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-admin-layout>
