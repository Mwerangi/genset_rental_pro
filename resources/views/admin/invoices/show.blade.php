<x-admin-layout>
@php
    $isEditable = $invoice->is_editable;
@endphp

    {{-- Proforma Banner --}}
    @if($invoice->isProforma())
    <div class="mb-4 bg-amber-50 border border-amber-300 rounded-xl px-5 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-semibold text-amber-800">PROFORMA INVOICE — This is not a tax invoice and has no accounting impact.</span>
        </div>
        @if($invoice->status !== 'void')
        <form method="POST" action="{{ route('admin.invoices.convert-proforma', $invoice) }}" onsubmit="return confirm('Convert this proforma to a Tax Invoice? This cannot be undone.')">
            @csrf
            <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-amber-600 text-white hover:bg-amber-700">
                Convert to Tax Invoice
            </button>
        </form>
        @endif
    </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('admin.invoices.index') }}" class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All Invoices
        </a>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $invoice->invoice_number }}</h1>
                    @if($invoice->isProforma())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">PROFORMA</span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" style="{{ $invoice->status_style }}">{{ $invoice->status_label }}</span>
                    @if($invoice->is_overdue)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" style="background:#fee2e2;color:#dc2626;">OVERDUE</span>
                    @endif
                    @if($invoice->is_zero_rated)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#f0fdf4;color:#15803d;">Zero Rated</span>
                    @endif
                    @if($invoice->currency === 'USD')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" style="background:#eff6ff;color:#1d4ed8;">USD &mdash; @ {{ number_format($invoice->exchange_rate_to_tzs, 0) }} TZS</span>
                    @endif
                </div>
                <p class="text-gray-500 mt-1 text-sm">
                    {{ $invoice->client?->company_name ?? $invoice->client?->name ?? '—' }} &bull;
                    Issued {{ $invoice->issue_date->format('d M Y') }} &bull;
                    Due {{ $invoice->due_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @permission('manage_invoices')
                @if($invoice->status === 'draft')
                    <button onclick="document.getElementById('mark-sent-modal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border" style="border-color:#2563eb;color:#2563eb;">Mark as Sent</button>
                @endif
                @if(!in_array($invoice->status, ['void','paid','written_off','declined']))
                    <button onclick="document.getElementById('dispute-modal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-amber-400 text-amber-700 hover:bg-amber-50">Dispute</button>
                @endif
                @if($invoice->status === 'disputed')
                    <button onclick="document.getElementById('writeoff-modal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-400 text-gray-600 hover:bg-gray-50">Write Off</button>
                @endif
                @if(!in_array($invoice->status, ['void','paid','written_off']))
                    <button onclick="document.getElementById('void-modal').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50">Void</button>
                @endif
                @endpermission
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="px-4 py-2 rounded-lg text-sm font-semibold text-white flex items-center gap-2" style="background:#dc2626;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Items + Payments -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Line Items -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Invoice Items</h2>
                    <span class="text-xs text-gray-400">{{ $invoice->items->count() }} {{ Str::plural('item', $invoice->items->count()) }}</span>
                </div>
                @if($invoice->items->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">No line items</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Description</th>
                                <th class="text-right px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Qty</th>
                                <th class="text-right px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Unit Price</th>
                                <th class="text-right px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Days</th>
                                <th class="text-right px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Subtotal</th>
                                @if($isEditable)<th class="px-3 py-3"></th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->items as $item)
                            <tr class="{{ $item->item_type === 'credit' ? 'bg-green-50' : '' }}">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900">{{ $item->description }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $item->item_type_label }}
                                        @if($item->item_type === 'credit')
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-xs font-semibold" style="background:#dcfce7;color:#166534;">CREDIT</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $invoice->currencySymbol() }} {{ number_format($item->unit_price, 0) }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $item->duration_days ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $item->subtotal < 0 ? 'text-green-700' : 'text-gray-900' }}">
                                    {{ $item->subtotal < 0 ? '&minus;' : '' }}{{ $invoice->currencySymbol() }} {{ number_format(abs($item->subtotal), 0) }}
                                </td>
                                @if($isEditable)
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <button
                                        onclick="openEditItemModal({{ $item->id }}, {{ json_encode($item->item_type) }}, {{ json_encode($item->description) }}, {{ $item->quantity }}, {{ $item->unit_price }}, {{ $item->duration_days ?? 'null' }})"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded border border-blue-200 text-blue-600 hover:bg-blue-50 mr-1" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.invoices.items.delete', [$invoice, $item]) }}" class="inline" onsubmit="return confirm('Remove this line item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded border border-red-200 text-red-500 hover:bg-red-50" title="Delete">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="{{ $isEditable ? 5 : 4 }}" class="px-5 py-2 text-right text-xs text-gray-500 font-medium">Items Subtotal</td>
                                <td class="px-5 py-2 text-right font-semibold text-gray-900">{{ $invoice->formatAmount($invoice->subtotal, 0) }}</td>
                                @if($isEditable)<td></td>@endif
                            </tr>
                            @if($isEditable || $invoice->discount_amount > 0)
                            <tr>
                                <td colspan="{{ $isEditable ? 5 : 4 }}" class="px-5 py-2 text-right text-xs text-gray-500 font-medium">
                                    Discount
                                    @if($invoice->discount_reason) <span class="italic text-gray-400">({{ $invoice->discount_reason }})</span> @endif
                                    @if($isEditable)
                                        <button onclick="document.getElementById('discount-modal').classList.remove('hidden')" class="ml-2 text-xs px-1.5 py-0.5 rounded border border-blue-200 text-blue-600 hover:bg-blue-50">Edit</button>
                                    @endif
                                </td>
                                <td class="px-5 py-2 text-right font-semibold {{ $invoice->discount_amount > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                    @if($invoice->discount_amount > 0)&minus; @endif {{ $invoice->formatAmount($invoice->discount_amount, 0) }}
                                </td>
                                @if($isEditable)<td></td>@endif
                            </tr>
                            @endif
                            @if($invoice->is_zero_rated)
                            <tr>
                                <td colspan="{{ $isEditable ? 5 : 4 }}" class="px-5 py-2 text-right text-xs text-gray-500 font-medium">VAT (Zero Rated)</td>
                                <td class="px-5 py-2 text-right text-gray-400 text-sm">— 0%</td>
                                @if($isEditable)<td></td>@endif
                            </tr>
                            @else
                            <tr>
                                <td colspan="{{ $isEditable ? 5 : 4 }}" class="px-5 py-2 text-right text-xs text-gray-500 font-medium">VAT ({{ $invoice->vat_rate }}%)</td>
                                <td class="px-5 py-2 text-right font-semibold text-gray-700">{{ $invoice->formatAmount($invoice->vat_amount, 0) }}</td>
                                @if($isEditable)<td></td>@endif
                            </tr>
                            @endif
                            <tr class="border-t border-gray-200">
                                <td colspan="{{ $isEditable ? 5 : 4 }}" class="px-5 py-3 text-right text-sm font-bold text-gray-800">Total</td>
                                <td class="px-5 py-3 text-right font-bold text-gray-900 text-base">{{ $invoice->formatAmount($invoice->total_amount, 0) }}</td>
                                @if($isEditable)<td></td>@endif
                            </tr>
                        </tfoot>
                    </table>
                @endif

                @if($isEditable)
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <button onclick="document.getElementById('add-item-modal').classList.remove('hidden')" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Line Item
                    </button>
                </div>
                @endif
            </div>

            <!-- Payment History -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Payment History</h2>
                    @if(!in_array($invoice->status, ['void','declined','written_off','paid']))
                        @permission('manage_invoices')
                        <button onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="text-sm px-3 py-1.5 rounded-lg font-semibold text-white" style="background:#dc2626;">
                            + Record Payment
                        </button>
                        @endpermission
                    @endif
                </div>
                @if($invoice->payments->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">No payments recorded yet</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Date</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Method</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Reference</th>
                                <th class="text-right px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Amount</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->payments as $payment)
                            <tr class="{{ $payment->is_reversed ? 'bg-gray-50 opacity-60' : '' }}">
                                <td class="px-5 py-3 {{ $payment->is_reversed ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $payment->is_reversed ? 'bg-gray-100 text-gray-400' : 'bg-gray-100 text-gray-700' }}">{{ $payment->method_label }}</span>
                                    @if($payment->is_reversed)
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-xs font-bold" style="background:#fef2f2;color:#991b1b;">REVERSED</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs {{ $payment->is_reversed ? 'text-gray-400 line-through' : 'text-gray-500' }}">{{ $payment->reference ?: '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $payment->is_reversed ? 'line-through text-gray-400' : 'text-gray-900' }}">{{ $invoice->currencySymbol() }} {{ number_format($payment->amount, 0) }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if(!$payment->is_reversed && !in_array($invoice->status, ['void','declined']))
                                        @permission('manage_invoices')
                                        <button
                                            onclick="openReverseModal({{ $payment->id }})"
                                            class="text-xs px-2 py-1 rounded border border-amber-300 text-amber-700 hover:bg-amber-50" title="Reverse this payment">
                                            Reverse
                                        </button>
                                        @endpermission
                                    @elseif($payment->is_reversed && $payment->reversal_note)
                                        <span class="text-xs text-gray-400 italic" title="{{ $payment->reversal_note }}">note</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-xs font-semibold text-gray-600">Total Paid (active)</td>
                                <td class="px-5 py-2 text-right font-bold text-green-700">{{ $invoice->formatAmount($invoice->amount_paid, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            <!-- Notes -->
            @if($invoice->notes || $invoice->payment_terms || $invoice->terms_conditions)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
                @if($invoice->payment_terms)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Payment Terms</p>
                        <p class="text-sm text-gray-700">{{ $invoice->payment_terms }}</p>
                    </div>
                @endif
                @if($invoice->terms_conditions)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Terms & Conditions</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->terms_conditions }}</p>
                    </div>
                @endif
                @if($invoice->notes)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
            @endif

        </div>

        <!-- RIGHT: Sidebar -->
        <div class="space-y-5">

            <!-- Amount Summary -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b" style="background:#fafafa;">
                    <h3 class="font-semibold text-gray-800 text-sm">Payment Summary</h3>
                </div>
                <div class="p-5 space-y-3">
                    <!-- Progress Bar -->
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ number_format($invoice->payment_progress, 1) }}%</span>
                        </div>
                        <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all" style="width:{{ min(100, $invoice->payment_progress) }}%;background:#{{ $invoice->payment_progress >= 100 ? '16a34a' : 'd97706' }};"></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Items Subtotal</span>
                        <span class="font-medium text-gray-800">{{ $invoice->formatAmount($invoice->subtotal, 0) }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Discount</span>
                        <span class="font-medium text-green-700">&minus; {{ $invoice->formatAmount($invoice->discount_amount, 0) }}</span>
                    </div>
                    @endif
                    @if(!$invoice->is_zero_rated && $invoice->vat_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">VAT ({{ $invoice->vat_rate }}%)</span>
                        <span class="font-medium text-gray-800">{{ $invoice->formatAmount($invoice->vat_amount, 0) }}</span>
                    </div>
                    @elseif($invoice->is_zero_rated)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">VAT</span>
                        <span class="text-green-700 font-medium text-xs">Zero Rated (0%)</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                        <span class="font-semibold text-gray-700">Total</span>
                        <span class="font-bold text-gray-900">{{ $invoice->formatAmount($invoice->total_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Amount Paid</span>
                        <span class="font-semibold text-green-700">{{ $invoice->formatAmount($invoice->amount_paid, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-gray-200 pt-3 mt-2">
                        <span class="font-bold text-gray-800">Balance Due</span>
                        @if($invoice->balance_due > 0)
                            <span class="font-bold text-xl" style="color:#dc2626;">{{ $invoice->formatAmount($invoice->balance_due, 0) }}</span>
                        @else
                            <span class="font-bold text-xl text-green-700">PAID</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Meta -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b" style="background:#fafafa;">
                    <h3 class="font-semibold text-gray-800 text-sm">Invoice Details</h3>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Client</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $invoice->client?->company_name ?? $invoice->client?->name ?? '—' }}</p>
                        @if($invoice->client?->contact_person || $invoice->client?->name)
                            <p class="text-gray-500 text-xs">{{ $invoice->client?->contact_person ?? $invoice->client?->name }}</p>
                        @endif
                        @if($invoice->client?->phone)
                            <p class="text-gray-500 text-xs">{{ $invoice->client->phone }}</p>
                        @endif
                    </div>
                    @if($invoice->booking)
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Booking</p>
                        <a href="{{ route('admin.bookings.show', $invoice->booking) }}" class="font-mono font-semibold text-blue-600 hover:underline mt-0.5 block">{{ $invoice->booking->booking_number }}</a>
                        @if($invoice->booking->genset)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $invoice->booking->genset->name }} ({{ $invoice->booking->genset->kva_rating }} kVA)</p>
                        @endif
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Issue Date</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ $invoice->issue_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Due Date</p>
                            <p class="font-medium mt-0.5 @if($invoice->is_overdue) text-red-600 @else text-gray-700 @endif">{{ $invoice->due_date->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 font-medium">Created By</p>
                        <p class="font-medium text-gray-700 mt-0.5">{{ $invoice->createdBy?->name ?? '—' }}</p>
                    </div>
                    @if($invoice->sent_at)
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 font-medium">Sent At</p>
                        <p class="font-medium text-gray-700 mt-0.5">{{ $invoice->sent_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                    @if($invoice->void_at)
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 font-medium">Voided At</p>
                        <p class="font-medium mt-0.5" style="color:#dc2626;">{{ $invoice->void_at->format('d M Y H:i') }}</p>
                        @if($invoice->void_reason)
                            <p class="text-xs text-gray-500 mt-1">{{ $invoice->void_reason }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Record Payment Modal -->
    @if(!in_array($invoice->status, ['void','declined','paid']))
    <div id="payment-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <h3 class="font-bold text-gray-900 text-lg">Record Payment</h3>
                <button onclick="document.getElementById('payment-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount ({{ $invoice->currencySymbol() }}) <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balance_due }}" value="{{ $invoice->balance_due }}" required placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-400 mt-1">Balance due: {{ $invoice->formatAmount($invoice->balance_due, 0) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span style="color:#dc2626;">*</span></label>
                    <select name="payment_method" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Transaction ID</label>
                    <input type="text" name="reference" placeholder="e.g. TXN123456" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Mark as Sent Modal -->
    @if($invoice->status === 'draft')
    <div id="mark-sent-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b" style="background:#eff6ff;">
                <h3 class="font-bold text-gray-900 text-lg">Mark Invoice as Sent</h3>
                <button onclick="document.getElementById('mark-sent-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">This will mark <strong>{{ $invoice->invoice_number }}</strong> as sent to the client. The invoice status will change from <em>Draft</em> to <em>Sent</em>.</p>
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Client</span>
                        <span class="font-medium text-gray-800">{{ $invoice->client?->company_name ?? $invoice->client?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Invoice Total</span>
                        <span class="font-semibold text-gray-900">{{ $invoice->formatAmount($invoice->total_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Due Date</span>
                        <span class="font-medium text-gray-800">{{ $invoice->due_date->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('mark-sent-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="{{ route('admin.invoices.mark-sent', $invoice) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#2563eb;">Confirm — Mark as Sent</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Void Modal -->
    @if(!in_array($invoice->status, ['void','paid','written_off']))
    <div id="void-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b border-red-100" style="background:#fff7f7;">
                <h3 class="font-bold text-gray-900 text-lg">Void Invoice</h3>
                <button onclick="document.getElementById('void-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.invoices.void', $invoice) }}" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600">This action cannot be undone. Voiding will permanently cancel the invoice.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for voiding <span style="color:#dc2626;">*</span></label>
                    <textarea name="void_reason" rows="3" required placeholder="Enter reason..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('void-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#7f1d1d;">Void Invoice</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Add Line Item Modal -->
    @if($isEditable)
    <div id="add-item-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <h3 class="font-bold text-gray-900 text-lg">Add Line Item</h3>
                <button onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.invoices.items.store', $invoice) }}" class="p-6 space-y-4" id="add-item-form">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Type <span style="color:#dc2626;">*</span></label>
                    <select name="item_type" required onchange="onAddItemTypeChange(this)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="genset_rental">Genset Rental</option>
                        <option value="delivery">Delivery / Transport</option>
                        <option value="fuel">Fuel</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="extra_days">Extra Days (Overstay)</option>
                        <option value="damage">Damage Charge</option>
                        <option value="penalty">Penalty / Late Fee</option>
                        <option value="credit">Credit / Deduction</option>
                        <option value="other">Other</option>
                    </select>
                    <p id="add-credit-hint" class="hidden mt-1 text-xs text-green-700 font-medium">Credit items will be subtracted from the invoice total.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="description" required placeholder="e.g. Extra 5 days overstay charge" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="quantity" step="0.01" min="0.01" value="1" required oninput="calcAddSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price ({{ $invoice->currencySymbol() }}) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="unit_price" step="0.01" min="0" value="0" required oninput="calcAddSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days, optional)</label>
                    <input type="number" name="duration_days" min="0" placeholder="e.g. 5" oninput="calcAddSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="rounded-lg px-4 py-3 text-sm font-semibold text-right" style="background:#f9fafb;border:1px solid #e5e7eb;">
                    Line Subtotal: {{ $invoice->currencySymbol() }} <span id="add-subtotal-preview">0</span>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Line Item Modal -->
    <div id="edit-item-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <h3 class="font-bold text-gray-900 text-lg">Edit Line Item</h3>
                <button onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" id="edit-item-form" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Type <span style="color:#dc2626;">*</span></label>
                    <select name="item_type" id="edit-item-type" required onchange="onEditItemTypeChange(this)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="genset_rental">Genset Rental</option>
                        <option value="delivery">Delivery / Transport</option>
                        <option value="fuel">Fuel</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="extra_days">Extra Days (Overstay)</option>
                        <option value="damage">Damage Charge</option>
                        <option value="penalty">Penalty / Late Fee</option>
                        <option value="credit">Credit / Deduction</option>
                        <option value="other">Other</option>
                    </select>
                    <p id="edit-credit-hint" class="hidden mt-1 text-xs text-green-700 font-medium">Credit items will be subtracted from the invoice total.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="description" id="edit-item-description" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="quantity" id="edit-item-quantity" step="0.01" min="0.01" required oninput="calcEditSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price ({{ $invoice->currencySymbol() }}) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="unit_price" id="edit-item-unit-price" step="0.01" min="0" required oninput="calcEditSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days, optional)</label>
                    <input type="number" name="duration_days" id="edit-item-duration" min="0" placeholder="e.g. 5" oninput="calcEditSubtotal()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="rounded-lg px-4 py-3 text-sm font-semibold text-right" style="background:#f9fafb;border:1px solid #e5e7eb;">
                    Line Subtotal: {{ $invoice->currencySymbol() }} <span id="edit-subtotal-preview">0</span>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Discount Modal -->
    <div id="discount-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b">
                <h3 class="font-bold text-gray-900 text-lg">Set Discount</h3>
                <button onclick="document.getElementById('discount-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.invoices.discount.update', $invoice) }}" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Amount ({{ $invoice->currencySymbol() }}) <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="discount_amount" step="0.01" min="0" value="{{ $invoice->discount_amount ?? 0 }}" required placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-400 mt-1">Set to 0 to remove discount. Applied before VAT.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <input type="text" name="discount_reason" value="{{ $invoice->discount_reason }}" placeholder="e.g. Loyalty discount, Early return credit" maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('discount-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Apply Discount</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Dispute Modal -->
    @if(!in_array($invoice->status, ['void','paid','written_off','declined']))
    <div id="dispute-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b border-amber-100" style="background:#fffbeb;">
                <h3 class="font-bold text-gray-900 text-lg">Mark as Disputed</h3>
                <button onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.invoices.dispute', $invoice) }}" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600">The invoice will be placed in <strong>Disputed</strong> status. It remains editable so you can add credits, remove charges, or adjust the total while resolving the dispute.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason / description of dispute <span style="color:#dc2626;">*</span></label>
                    <textarea name="dispute_reason" rows="3" required placeholder="e.g. Client disputes overstay charges — under review..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Mark as Disputed</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Write-Off Modal -->
    @if($invoice->status === 'disputed')
    <div id="writeoff-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100" style="background:#f8fafc;">
                <h3 class="font-bold text-gray-900 text-lg">Write Off Invoice</h3>
                <button onclick="document.getElementById('writeoff-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">Writing off this invoice marks the dispute as unrecoverable. Status will be set to <strong>Written Off</strong>. This cannot be undone.</p>
                <div class="rounded-lg p-3 text-sm" style="background:#f1f5f9;border:1px solid #e2e8f0;">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Invoice</span><span class="font-mono font-semibold">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-gray-500">Balance Due</span><span class="font-semibold text-red-600">{{ $invoice->formatAmount($invoice->balance_due, 0) }}</span>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('writeoff-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="{{ route('admin.invoices.write-off', $invoice) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#475569;">Confirm Write-Off</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Reverse Payment Modal (shared, filled via JS) -->
    <div id="reverse-payment-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-5 border-b border-amber-100" style="background:#fffbeb;">
                <h3 class="font-bold text-gray-900 text-lg">Reverse Payment</h3>
                <button onclick="document.getElementById('reverse-payment-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" id="reverse-payment-form" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600">This payment will be marked as reversed. The record is kept for audit purposes but will no longer count toward the invoice balance.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reversal Reason</label>
                    <textarea name="reversal_note" rows="2" placeholder="e.g. Bounced cheque, payment recalled..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('reverse-payment-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#b45309;">Reverse Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── JavaScript ──────────────────────────────────────────────────────── --}}
    <script>
    function calcAddSubtotal() {
        var type  = document.querySelector('#add-item-form [name=item_type]').value;
        var qty   = parseFloat(document.querySelector('#add-item-form [name=quantity]').value) || 0;
        var price = parseFloat(document.querySelector('#add-item-form [name=unit_price]').value) || 0;
        var days  = parseFloat(document.querySelector('#add-item-form [name=duration_days]').value) || 0;
        var sub   = (type === 'genset_rental' || type === 'extra_days') && days > 0
                    ? qty * price * days
                    : qty * price;
        document.getElementById('add-subtotal-preview').textContent = Math.round(sub).toLocaleString();
    }

    function onAddItemTypeChange(sel) {
        document.getElementById('add-credit-hint').classList.toggle('hidden', sel.value !== 'credit');
        calcAddSubtotal();
    }

    function openEditItemModal(id, type, description, qty, unitPrice, durationDays) {
        var baseUrl = '{{ url('admin/invoices/' . $invoice->id . '/items') }}/' + id;
        document.getElementById('edit-item-form').action = baseUrl;
        document.getElementById('edit-item-type').value        = type;
        document.getElementById('edit-item-description').value = description;
        document.getElementById('edit-item-quantity').value    = qty;
        document.getElementById('edit-item-unit-price').value  = unitPrice;
        document.getElementById('edit-item-duration').value    = (durationDays !== null && durationDays !== undefined) ? durationDays : '';
        onEditItemTypeChange(document.getElementById('edit-item-type'));
        calcEditSubtotal();
        document.getElementById('edit-item-modal').classList.remove('hidden');
    }

    function calcEditSubtotal() {
        var type  = document.getElementById('edit-item-type').value;
        var qty   = parseFloat(document.getElementById('edit-item-quantity').value) || 0;
        var price = parseFloat(document.getElementById('edit-item-unit-price').value) || 0;
        var days  = parseFloat(document.getElementById('edit-item-duration').value) || 0;
        var sub   = (type === 'genset_rental' || type === 'extra_days') && days > 0
                    ? qty * price * days
                    : qty * price;
        document.getElementById('edit-subtotal-preview').textContent = Math.round(sub).toLocaleString();
    }

    function onEditItemTypeChange(sel) {
        document.getElementById('edit-credit-hint').classList.toggle('hidden', sel.value !== 'credit');
        calcEditSubtotal();
    }

    function openReverseModal(paymentId) {
        var url = '{{ url('admin/invoices/' . $invoice->id . '/payments') }}/' + paymentId + '/reverse';
        document.getElementById('reverse-payment-form').action = url;
        document.getElementById('reverse-payment-modal').classList.remove('hidden');
    }
    </script>

</x-admin-layout>
