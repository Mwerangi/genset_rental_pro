@php
    $cs = $companySetting;
    $primaryColor = $cs?->primary_color ?: '#dc2626';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; background: #ffffff; }
        .page { padding: 22px 32px; min-height: 100vh; }

        /* Header */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .company-name { font-size: 15pt; font-weight: bold; color: #c00000; }
        .company-sub { font-size: 8.5pt; color: #555; margin-top: 4px; }
        .company-contact { font-size: 8pt; color: #666; margin-top: 6px; line-height: 1.6; }
        .invoice-label { font-size: 18pt; font-weight: bold; color: #c00000; text-align: right; }
        .invoice-meta td { font-size: 8.5pt; padding: 2px 0; }
        .invoice-meta .label { color: #888; width: 90px; }
        .invoice-meta .value { font-weight: bold; color: #1a1a1a; }

        /* Divider */
        .divider { border: none; border-top: 2px solid #c00000; margin: 10px 0; }
        .divider-thin { border: none; border-top: 1px solid #e5e7eb; margin: 12px 0; }

        /* Bill To */
        .billing-row { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .billing-box { background: #f8f8f8; border-left: 3px solid #c00000; padding: 8px 12px; width: 48%; vertical-align: top; }
        .billing-box .section-title { font-size: 7.5pt; font-weight: bold; color: #c00000; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .billing-box .client-name { font-weight: bold; font-size: 9.5pt; color: #1a1a1a; }
        .billing-box .client-detail { font-size: 8.5pt; color: #555; line-height: 1.7; margin-top: 3px; }
        .billing-box .client-tax { font-size: 8.5pt; color: #1a1a1a; font-weight: bold; line-height: 1.7; margin-top: 3px; }
        .info-box { vertical-align: top; width: 48%; padding-left: 24px; }
        .info-row { border-collapse: collapse; width: 100%; }
        .info-row td { font-size: 9pt; padding: 4px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row .lbl { color: #888; }
        .info-row .val { font-weight: bold; text-align: right; }
        .spacer-col { width: 4%; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }

        /* Zero rated badge */
        .zero-rated { color: #166534; background: #f0fdf4; padding: 2px 8px; border-radius: 4px; font-size: 8pt; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items-table thead tr { background: #c00000; }
        .items-table thead th { color: #ffffff; font-size: 8.5pt; font-weight: bold; padding: 6px 10px; text-align: left; }
        .items-table thead th.right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #f0f0f0; }
        .items-table tbody tr:nth-child(even) { background: #fafafa; }
        .items-table tbody td { padding: 6px 10px; font-size: 9pt; vertical-align: top; }
        .items-table tbody td.right { text-align: right; }
        .item-desc { font-weight: bold; color: #1a1a1a; }
        .item-type { font-size: 8pt; color: #888; margin-top: 2px; }
        .items-table tfoot td { padding: 6px 10px; font-size: 9pt; }
        .items-table tfoot .lbl { text-align: right; color: #555; }
        .items-table tfoot .val { text-align: right; font-weight: bold; }
        .total-row td { border-top: 2px solid #c00000; padding-top: 8px; }
        .total-row .val { font-size: 11pt; font-weight: bold; color: #c00000; }

        /* Amounts Summary (right side) */
        .summary-table { width: 280px; float: right; margin-top: -4px; border-collapse: collapse; }
        .summary-table td { padding: 3px 8px; font-size: 9pt; }
        .summary-table .lbl { color: #555; }
        .summary-table .val { text-align: right; font-weight: bold; }
        .summary-divider { border-top: 1px solid #e5e7eb; }
        .grand-total td { background: #c00000; color: #fff; font-weight: bold; font-size: 9.5pt; }
        .balance-due td { background: #fff7f7; }
        .balance-due .val { color: #c00000; font-size: 11pt; }
        .paid-amount td { background: #f0fdf4; }
        .paid-amount .val { color: #166534; }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* Payment Status Banner */
        .paid-banner { background: #f0fdf4; border: 1px solid #86efac; border-radius: 4px; padding: 7px 12px; text-align: center; margin: 10px 0; }
        .paid-banner-text { font-weight: bold; font-size: 10pt; color: #166534; }
        .overdue-banner { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 4px; padding: 7px 12px; text-align: center; margin: 10px 0; }
        .overdue-banner-text { font-weight: bold; font-size: 9pt; color: #c00000; }

        /* Booking / Reference */
        .reference-section { margin-top: 20px; font-size: 8.5pt; color: #666; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .reference-section strong { color: #1a1a1a; }

        /* Notes */
        .notes-section { margin-top: 8px; font-size: 9pt; }
        .notes-section .notes-title { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 4px; }
        .notes-section .notes-body { color: #555; line-height: 1.6; }

        /* Footer */
        .footer { margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 8pt; color: #aaa; }
        .footer strong { color: #888; }
    </style>
    <style>
        .company-name { color: {{ $primaryColor }}; }
        .invoice-label { color: {{ $primaryColor }}; }
        .billing-box .section-title { color: {{ $primaryColor }}; }
        .total-row .val { color: {{ $primaryColor }}; }
        .balance-due .val { color: {{ $primaryColor }}; }
        .overdue-banner-text { color: {{ $primaryColor }}; }
        .divider { border-top-color: {{ $primaryColor }}; }
        .billing-box { border-left-color: {{ $primaryColor }}; }
        .items-table thead tr { background: {{ $primaryColor }}; }
        .grand-total td { background: {{ $primaryColor }}; }
        .total-row td { border-top-color: {{ $primaryColor }}; }
    </style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align:top; width:55%;">
                @php
                    $logoLocalPath = $cs?->logo_path
                        ? storage_path('app/public/' . $cs->logo_path)
                        : null;
                @endphp
                @if($logoLocalPath && file_exists($logoLocalPath))
                <img src="{{ $logoLocalPath }}" style="height:52px; max-width:160px; object-fit:contain; margin-bottom:5px;" alt="{{ $cs->company_name }}"><br>
                @endif
                <div class="company-name">{{ $cs?->company_name ?? 'Milele Power' }}</div>
                @if($cs?->trading_name && $cs->trading_name !== $cs->company_name)
                <div class="company-sub">t/a {{ $cs->trading_name }}</div>
                @endif
                @if($cs?->tagline)
                <div class="company-sub">{{ $cs->tagline }}</div>
                @else
                <div class="company-sub">Generator Rental &amp; Power Solutions</div>
                @endif
                <div class="company-contact">
                    @if($cs?->address_line1)
                        {{ $cs->address_line1 }}@if($cs?->city), {{ $cs->city }}@endif<br>
                    @elseif($cs?->city)
                        {{ $cs->city }}, {{ $cs?->country ?? 'Tanzania' }}<br>
                    @else
                        Dar es Salaam, Tanzania<br>
                    @endif
                    @if($cs?->phone_primary)
                        Tel: {{ $cs->phone_primary }}
                        @if($cs?->email_general)
                            &bull; {{ $cs->email_general }}
                        @endif
                        <br>
                    @endif
                    @if($cs?->website)
                        {{ $cs->website }}<br>
                    @endif
                    @if($cs?->tin_number)
                        <strong>TIN: {{ $cs->tin_number }}</strong>
                        @if($cs?->vrn_number)
                            &bull; <strong>VRN: {{ $cs->vrn_number }}</strong>
                        @endif
                    @endif
                </div>
            </td>
            <td style="vertical-align:top; text-align:right; width:45%;">
                <div class="invoice-label">INVOICE</div>
                <table class="invoice-meta" style="float:right; margin-top:8px;">
                    <tr>
                        <td class="label">Invoice #:</td>
                        <td class="value">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Issue Date:</td>
                        <td class="value">{{ $invoice->issue_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Due Date:</td>
                        <td class="value">{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">
                            @php
                                $colors = [
                                    'draft'          => ['bg'=>'#f3f4f6','fg'=>'#374151'],
                                    'sent'           => ['bg'=>'#dbeafe','fg'=>'#1e40af'],
                                    'partially_paid' => ['bg'=>'#fef9c3','fg'=>'#854d0e'],
                                    'paid'           => ['bg'=>'#dcfce7','fg'=>'#166534'],
                                    'void'           => ['bg'=>'#fee2e2','fg'=>'#991b1b'],
                                    'declined'       => ['bg'=>'#fee2e2','fg'=>'#7f1d1d'],
                                ];
                                $c = $colors[$invoice->status] ?? ['bg'=>'#f3f4f6','fg'=>'#374151'];
                            @endphp
                            <span class="status-badge" style="background:{{ $c['bg'] }};color:{{ $c['fg'] }};">{{ $invoice->status_label }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="divider">

    <!-- Bill To + Details -->
    <table class="billing-row">
        <tr>
            <td class="billing-box">
                <div class="section-title">Bill To</div>
                <table style="width:100%; border-collapse:collapse;">
                    @if($invoice->client?->company_name)
                    <tr>
                        <td style="font-size:8pt; color:#999; padding:2px 0; width:36%; white-space:nowrap;">Company</td>
                        <td style="font-size:8.5pt; font-weight:bold; color:#1a1a1a; padding:2px 0;">{{ $invoice->client->company_name }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8pt; color:#999; padding:2px 0;">Contact</td>
                        <td style="font-size:8.5pt; color:#333; padding:2px 0;">{{ $invoice->client->full_name }}</td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="2" style="font-size:9pt; font-weight:bold; color:#1a1a1a; padding:2px 0;">{{ $invoice->client?->full_name ?? 'N/A' }}</td>
                    </tr>
                    @endif
                    @if($invoice->client?->phone)
                    <tr>
                        <td style="font-size:8pt; color:#999; padding:2px 0;">Phone</td>
                        <td style="font-size:8.5pt; color:#333; padding:2px 0;">{{ $invoice->client->phone }}</td>
                    </tr>
                    @endif
                    @if($invoice->client?->email)
                    <tr>
                        <td style="font-size:8pt; color:#999; padding:2px 0;">Email</td>
                        <td style="font-size:8.5pt; color:#333; padding:2px 0;">{{ $invoice->client->email }}</td>
                    </tr>
                    @endif
                </table>
                @if($invoice->client?->tin_number || $invoice->client?->vrn)
                <div style="margin-top:7px; border-top:1px dashed #ddd; padding-top:6px;">
                    @if($invoice->client?->tin_number)
                    <div style="font-size:8.5pt; color:#1a1a1a; font-weight:bold;">TIN: {{ $invoice->client->tin_number }}</div>
                    @endif
                    @if($invoice->client?->vrn)
                    <div style="font-size:8.5pt; color:#1a1a1a; font-weight:bold; margin-top:2px;">VRN: {{ $invoice->client->vrn }}</div>
                    @endif
                </div>
                @endif
            </td>
            <td class="spacer-col"></td>
            <td class="info-box">
                <table class="info-row">
                    @if($invoice->booking)
                    <tr>
                        <td class="lbl">Booking Ref:</td>
                        <td class="val">{{ $invoice->booking->booking_number }}</td>
                    </tr>
                    @endif
                    @if($invoice->booking?->genset)
                    <tr>
                        <td class="lbl">Generator:</td>
                        <td class="val">{{ $invoice->booking->genset->name }}</td>
                    </tr>
                    @endif
                    @if($invoice->is_zero_rated)
                    <tr>
                        <td class="lbl">VAT:</td>
                        <td class="val"><span class="zero-rated">Zero Rated</span></td>
                    </tr>
                    @endif
                    @if($invoice->currency === 'USD')
                    <tr>
                        <td class="lbl">Currency:</td>
                        <td class="val">USD &mdash; Rate: {{ number_format($invoice->exchange_rate_to_tzs, 0) }} TZS</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:45%;">Description</th>
                <th class="right" style="width:8%;">Qty</th>
                <th class="right" style="width:12%;">Unit Price</th>
                <th class="right" style="width:10%;">Days</th>
                <th class="right" style="width:15%;">Amount ({{ $invoice->currencySymbol() }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>
                    <div class="item-desc">{{ $item->description }}</div>
                    <div class="item-type">{{ $item->item_type_label }}</div>
                </td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->unit_price, 0) }}</td>
                <td class="right">{{ $item->duration_days ?? '—' }}</td>
                <td class="right">{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#aaa; padding:16px;">No items</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals -->
    <div class="clearfix">
        <table class="summary-table">
            <tr>
                <td class="lbl">Subtotal:</td>
                <td class="val">{{ $invoice->formatAmount($invoice->subtotal, 0) }}</td>
            </tr>
            @if($invoice->is_zero_rated)
            <tr>
                <td class="lbl">VAT:</td>
                <td class="val" style="color:#166534;">Zero Rated (0%)</td>
            </tr>
            @else
            <tr>
                <td class="lbl">VAT ({{ $invoice->vat_rate }}%):</td>
                <td class="val">{{ $invoice->formatAmount($invoice->vat_amount, 0) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td style="padding:8px 8px; font-weight:bold; border-radius:0;">TOTAL:</td>
                <td style="text-align:right; padding:8px 8px; font-size:9.5pt; border-radius:0;">{{ $invoice->formatAmount($invoice->total_amount, 0) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
            <tr class="paid-amount">
                <td class="lbl" style="padding-top:6px;">Amount Paid:</td>
                <td class="val" style="padding-top:6px; color:#166534;">{{ $invoice->formatAmount($invoice->amount_paid, 0) }}</td>
            </tr>
            <tr class="balance-due">
                <td class="lbl" style="font-weight:bold; color:#555;">Balance Due:</td>
                <td class="val">{{ $invoice->formatAmount($invoice->balance_due, 0) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($invoice->status === 'paid')
    <div class="paid-banner" style="margin-top:16px;">
        <div class="paid-banner-text">&#10003; FULLY PAID</div>
    </div>
    @elseif($invoice->is_overdue)
    <div class="overdue-banner" style="margin-top:16px;">
        <div class="overdue-banner-text">&#9888; OVERDUE — Payment was due {{ $invoice->due_date->format('d M Y') }}</div>
    </div>
    @endif

    <!-- Bank Details & Payment Instructions -->
    @if($cs?->bank_name || $cs?->bank_account_number || $cs?->payment_instructions)
    <div style="margin-top:12px; border:1px solid #e5e7eb; border-left:3px solid {{ $primaryColor }}; border-radius:4px; padding:10px 14px; background:#fafafa;">
        <div style="font-size:7.5pt; font-weight:bold; color:{{ $primaryColor }}; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:7px;">Payment Details</div>
        @if($cs->bank_name || $cs->bank_account_number)
        <table style="border-collapse:collapse; width:100%; font-size:8.5pt;">
            @if($cs->bank_name)
            <tr><td style="color:#888; padding:1px 0; width:110px;">Bank:</td><td style="font-weight:bold; color:#1a1a1a;">{{ $cs->bank_name }}</td></tr>
            @endif
            @if($cs->bank_branch_name)
            <tr><td style="color:#888; padding:1px 0;">Branch:</td><td style="font-weight:bold; color:#1a1a1a;">{{ $cs->bank_branch_name }}</td></tr>
            @endif
            @if($cs->bank_account_name)
            <tr><td style="color:#888; padding:1px 0;">Account Name:</td><td style="font-weight:bold; color:#1a1a1a;">{{ $cs->bank_account_name }}</td></tr>
            @endif
            @if($cs->bank_account_number)
            <tr><td style="color:#888; padding:1px 0;">Account No.:</td><td style="font-weight:bold; color:#1a1a1a;">{{ $cs->bank_account_number }}</td></tr>
            @endif
            @if($cs->bank_swift_code)
            <tr><td style="color:#888; padding:1px 0;">SWIFT/BIC:</td><td style="font-weight:bold; color:#1a1a1a;">{{ $cs->bank_swift_code }}</td></tr>
            @endif
        </table>
        @endif
        @if($cs?->payment_instructions)
        <div style="margin-top:6px; font-size:8.5pt; color:#555; line-height:1.5;">{{ $cs->payment_instructions }}</div>
        @endif
    </div>
    @endif

    <!-- Notes & Terms -->
    @if($invoice->payment_terms || $invoice->terms_conditions)
    <div style="margin-top:12px; border-top: 1px solid #e5e7eb; padding-top:8px;">
        @if($invoice->payment_terms)
        <div class="notes-section">
            <div class="notes-title">Payment Terms</div>
            <div class="notes-body">{{ $invoice->payment_terms }}</div>
        </div>
        @endif
        @if($invoice->terms_conditions)
        <div class="notes-section" style="margin-top:6px;">
            <div class="notes-title">Terms &amp; Conditions</div>
            <div class="notes-body">{{ $invoice->terms_conditions }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <strong>
            {{ $cs?->company_name ?? 'Milele Power' }}
            @if($cs?->tagline)
                &bull; {{ $cs->tagline }}
            @endif
        </strong><br>
        @if($cs?->city)
            {{ $cs->city }}, {{ $cs?->country ?? 'Tanzania' }}
        @else
            Dar es Salaam, Tanzania
        @endif
        @if($cs?->phone_primary)
            &bull; Tel: {{ $cs->phone_primary }}
        @endif
        @if($cs?->email_general)
            &bull; {{ $cs->email_general }}
        @endif
        <br>
        @if($cs?->invoice_notes)
            {{ $cs->invoice_notes }}
        @else
            Thank you for your business.
        @endif
    </div>

</div>
</body>
</html>
