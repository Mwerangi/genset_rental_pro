@php
    $cs = $companySetting;
    $primaryColor = $cs?->primary_color ?: '#dc2626';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; background: #ffffff; }
        .page { padding: 22px 32px; min-height: 100vh; }

        /* Header */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .company-name { font-size: 15pt; font-weight: bold; color: #c00000; }
        .company-sub { font-size: 8.5pt; color: #555; margin-top: 4px; }
        .company-contact { font-size: 8pt; color: #666; margin-top: 6px; line-height: 1.6; }
        .doc-label { font-size: 18pt; font-weight: bold; color: #c00000; text-align: right; }
        .doc-meta td { font-size: 8.5pt; padding: 2px 0; }
        .doc-meta .label { color: #888; width: 90px; }
        .doc-meta .value { font-weight: bold; color: #1a1a1a; }

        /* Divider */
        .divider { border: none; border-top: 2px solid #c00000; margin: 10px 0; }

        /* Billing */
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

        /* Totals Summary */
        .summary-table { width: 280px; float: right; margin-top: -4px; border-collapse: collapse; }
        .summary-table td { padding: 3px 8px; font-size: 9pt; }
        .summary-table .lbl { color: #555; }
        .summary-table .val { text-align: right; font-weight: bold; }
        .grand-total td { background: #c00000; color: #fff; font-weight: bold; font-size: 9.5pt; }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* Validity Notice */
        .validity-notice { border-radius: 4px; padding: 7px 12px; text-align: center; margin: 10px 0; }
        .validity-valid { background: #fef9c3; border: 1px solid #fbbf24; }
        .validity-valid-text { font-weight: bold; font-size: 8pt; color: #854d0e; }
        .validity-expired { background: #fef2f2; border: 1px solid #fca5a5; }
        .validity-expired-text { font-weight: bold; font-size: 8pt; color: #c00000; }

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
        .doc-label { color: {{ $primaryColor }}; }
        .billing-box .section-title { color: {{ $primaryColor }}; }
        .divider { border-top-color: {{ $primaryColor }}; }
        .billing-box { border-left-color: {{ $primaryColor }}; }
        .items-table thead tr { background: {{ $primaryColor }}; }
        .grand-total td { background: {{ $primaryColor }}; }
        .validity-expired-text { color: {{ $primaryColor }}; }
        .validity-expired { border-color: {{ $primaryColor }}; }
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
                <div class="doc-label">QUOTATION</div>
                <table class="doc-meta" style="float:right; margin-top:8px;">
                    <tr>
                        <td class="label">Quote #:</td>
                        <td class="value">{{ $quotation->quotation_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date Issued:</td>
                        <td class="value">{{ $quotation->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Valid Until:</td>
                        <td class="value">{{ $quotation->valid_until->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">
                            @php
                                $colors = [
                                    'draft'    => ['bg'=>'#f3f4f6','fg'=>'#374151'],
                                    'sent'     => ['bg'=>'#dbeafe','fg'=>'#1e40af'],
                                    'accepted' => ['bg'=>'#dcfce7','fg'=>'#166534'],
                                    'rejected' => ['bg'=>'#fee2e2','fg'=>'#991b1b'],
                                    'expired'  => ['bg'=>'#fef9c3','fg'=>'#854d0e'],
                                ];
                                $c = $colors[$quotation->status] ?? ['bg'=>'#f3f4f6','fg'=>'#374151'];
                            @endphp
                            <span class="status-badge" style="background:{{ $c['bg'] }};color:{{ $c['fg'] }};">{{ ucfirst($quotation->status) }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="divider">

    <!-- Prepared For + Details -->
    <table class="billing-row">
        <tr>
            <td class="billing-box">
                <div class="section-title">Prepared For</div>
                @if($quotation->client)
                    <div class="client-name">{{ $quotation->client->company_name ?? $quotation->client->full_name }}</div>
                    @if($quotation->client->company_name)
                        <div class="client-detail">{{ $quotation->client->full_name }}</div>
                    @endif
                    @if($quotation->client->phone)
                        <div class="client-detail">{{ $quotation->client->phone }}</div>
                    @endif
                    @if($quotation->client->email)
                        <div class="client-detail">{{ $quotation->client->email }}</div>
                    @endif
                    @if($quotation->client->tin_number)
                        <div class="client-tax">TIN: {{ $quotation->client->tin_number }}</div>
                    @endif
                    @if($quotation->client->vrn)
                        <div class="client-tax">VRN: {{ $quotation->client->vrn }}</div>
                    @endif
                @elseif($quotation->quoteRequest)
                    <div class="client-name">{{ $quotation->quoteRequest->full_name }}</div>
                    @if($quotation->quoteRequest->company_name)
                        <div class="client-detail">{{ $quotation->quoteRequest->company_name }}</div>
                    @endif
                    @if($quotation->quoteRequest->phone)
                        <div class="client-detail">{{ $quotation->quoteRequest->phone }}</div>
                    @endif
                    @if($quotation->quoteRequest->email)
                        <div class="client-detail">{{ $quotation->quoteRequest->email }}</div>
                    @endif
                    @php $reqClient = $quotation->quoteRequest->client; @endphp
                    @if($reqClient && $reqClient->tin_number)
                        <div class="client-tax">TIN: {{ $reqClient->tin_number }}</div>
                    @endif
                    @if($reqClient && $reqClient->vrn)
                        <div class="client-tax">VRN: {{ $reqClient->vrn }}</div>
                    @endif
                @elseif($quotation->customer_name)
                    <div class="client-name">{{ $quotation->customer_name }}</div>
                    @if($quotation->company_name)
                        <div class="client-detail">{{ $quotation->company_name }}</div>
                    @endif
                    @if($quotation->customer_phone)
                        <div class="client-detail">{{ $quotation->customer_phone }}</div>
                    @endif
                    @if($quotation->customer_email)
                        <div class="client-detail">{{ $quotation->customer_email }}</div>
                    @endif
                @else
                    <div class="client-name">Direct Quotation</div>
                @endif
            </td>
            <td class="spacer-col"></td>
            <td class="info-box">
                <table class="info-row">
                    @if($quotation->quoteRequest)
                    <tr>
                        <td class="lbl">Request Ref:</td>
                        <td class="val">{{ $quotation->quoteRequest->request_number }}</td>
                    </tr>
                    @endif
                    @if($quotation->createdBy)
                    <tr>
                        <td class="lbl">Prepared By:</td>
                        <td class="val">{{ $quotation->createdBy->name }}</td>
                    </tr>
                    @endif
                    @if($quotation->payment_terms)
                    <tr>
                        <td class="lbl">Pay. Terms:</td>
                        <td class="val">{{ $quotation->payment_terms }}</td>
                    </tr>
                    @endif
                    @if($quotation->currency === 'USD')
                    <tr>
                        <td class="lbl">Currency:</td>
                        <td class="val">USD &mdash; Rate: {{ number_format($quotation->exchange_rate_to_tzs, 0) }} TZS</td>
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
                <th class="right" style="width:13%;">Unit Price</th>
                <th class="right" style="width:10%;">Days</th>
                <th class="right" style="width:14%;">Amount ({{ $quotation->currencySymbol() }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items as $item)
            <tr>
                <td>
                    <div class="item-desc">{{ $item->description }}</div>
                    <div class="item-type">{{ $item->item_type_formatted }}</div>
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
                <td class="val">{{ $quotation->formatAmount($quotation->subtotal, 0) }}</td>
            </tr>
            <tr>
                <td class="lbl">VAT ({{ $quotation->vat_rate }}%):</td>
                <td class="val">{{ $quotation->formatAmount($quotation->vat_amount, 0) }}</td>
            </tr>
            <tr class="grand-total">
                <td style="padding:8px 8px; font-weight:bold;">TOTAL:</td>
                <td style="text-align:right; padding:8px 8px; font-size:9.5pt;">{{ $quotation->formatAmount($quotation->total_amount, 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- Validity Notice -->
    @if(!$quotation->isExpired())
    <div class="validity-notice validity-valid" style="margin-top:16px;">
        <div class="validity-valid-text">&#8987; Valid until {{ $quotation->valid_until->format('d M Y') }} &mdash; Please respond before this date. Prices and availability are subject to change.</div>
    </div>
    @else
    <div class="validity-notice validity-expired" style="margin-top:16px;">
        <div class="validity-expired-text">&#9888; This quotation expired on {{ $quotation->valid_until->format('d M Y') }} &mdash; Please contact us for an updated quotation.</div>
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
    @if($quotation->payment_terms || $quotation->terms_conditions)
    <div style="margin-top:12px; border-top: 1px solid #e5e7eb; padding-top:8px;">
        @if($quotation->payment_terms)
        <div class="notes-section">
            <div class="notes-title">Payment Terms</div>
            <div class="notes-body">{{ $quotation->payment_terms }}</div>
        </div>
        @endif
        @if($quotation->terms_conditions)
        <div class="notes-section" style="margin-top:6px;">
            <div class="notes-title">Terms &amp; Conditions</div>
            <div class="notes-body">{{ $quotation->terms_conditions }}</div>
        </div>
        @endif
    </div>
    @else
    <div style="margin-top:12px; border-top: 1px solid #e5e7eb; padding-top:8px;">
        <div class="notes-section">
            <div class="notes-title">Payment Terms</div>
            <div class="notes-body">Payment due within 30 days of acceptance. All prices are in {{ $quotation->currency === 'USD' ? 'US Dollars (USD)' : 'Tanzanian Shillings (TZS)' }}.</div>
        </div>
        <div class="notes-section" style="margin-top:8px;">
            <div class="notes-title">Terms &amp; Conditions</div>
            <div class="notes-body">
                1. This quotation is valid for the period specified above.<br>
                2. Prices are subject to change without notice after the validity period.<br>
                3. Generator rental includes basic maintenance during the rental period.<br>
                4. Delivery and pickup charges may apply based on location.<br>
                5. Full payment is required before equipment delivery.<br>
                6. Security deposit may be required for rental equipment.
            </div>
        </div>
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
        @if($cs?->quotation_terms)
            {{ $cs->quotation_terms }}
        @else
            Thank you for choosing {{ $cs?->company_name ?? 'Milele Power' }}.
        @endif
    </div>

</div>
</body>
</html>
