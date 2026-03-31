@php
    $cs = $companySetting;
    $accent = $cs?->primary_color ?: '#8B1A0A';
    $logoLocalPath = $cs?->logo_path ? storage_path('app/public/' . $cs->logo_path) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #3a3a3a; background: #ffffff; }
        .page { background: #ffffff; padding: 32px 36px 28px; }
        .co-name { font-size: 13pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3px; }
        .co-contact { font-size: 8pt; color: #555; line-height: 1.7; margin-top: 4px; }
        .co-contact strong { color: #2a2a2a; }
        .doc-badge { font-size: 22pt; font-weight: bold; color: #1a1a1a; text-align: right; letter-spacing: 2px; }
        .doc-meta-tbl { border-collapse: collapse; margin-left: auto; }
        .doc-meta-tbl td { font-size: 8.5pt; padding: 2px 0 2px 14px; }
        .doc-meta-tbl .lbl { color: #999; white-space: nowrap; }
        .doc-meta-tbl .val { font-weight: bold; color: #1a1a1a; text-align: right; white-space: nowrap; }
        .divider { border: none; border-top: 2px solid #1a1a1a; margin: 14px 0 24px; }
        .divider-soft { border: none; border-top: 1px solid #e8e8e8; margin: 14px 0; }
        .section-lbl { font-size: 9.5pt; font-weight: bold; margin-bottom: 8px; }
        .client-name-big { font-size: 10.5pt; font-weight: bold; color: #1a1a1a; margin-bottom: 6px; }
        .client-field { width: 100%; border-collapse: collapse; }
        .client-field td { font-size: 8.5pt; padding: 2px 0; vertical-align: top; }
        .client-field .fl { color: #999; width: 80px; white-space: nowrap; }
        .client-field .fv { color: #1a1a1a; font-weight: bold; }
        .meta-field { width: 100%; border-collapse: collapse; }
        .meta-field td { font-size: 8.5pt; padding: 3.5px 0; border-bottom: 1px dashed #eeeeee; vertical-align: top; }
        .meta-field .fl { color: #999; width: 90px; white-space: nowrap; }
        .meta-field .fv { font-weight: bold; color: #1a1a1a; }
        .meta-field tr:last-child td { border-bottom: none; }
        .items-tbl { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .items-tbl thead tr { background: #EDEDF3; }
        .items-tbl thead th { font-size: 8.5pt; font-weight: bold; color: #3D3A56; padding: 7px 10px; text-align: left; border: 1px solid #DCDCE8; }
        .items-tbl thead th.r { text-align: right; }
        .items-tbl tbody td { font-size: 9pt; padding: 7px 10px; border: 1px solid #e8e8e8; vertical-align: top; color: #3a3a3a; }
        .items-tbl tbody td.r { text-align: right; }
        .items-tbl tbody tr:nth-child(even) td { background: #fafafa; }
        .item-desc { font-weight: bold; color: #1a1a1a; }
        .item-sub { font-size: 8pt; color: #999; margin-top: 2px; }
        .totals-tbl { border-collapse: collapse; float: right; min-width: 240px; margin-top: 6px; }
        .totals-tbl td { font-size: 9pt; padding: 4px 10px; }
        .totals-tbl .tl { color: #555; text-align: left; white-space: nowrap; }
        .totals-tbl .tv { font-weight: bold; color: #1a1a1a; text-align: right; white-space: nowrap; }
        .totals-tbl tr.gtrow td { font-weight: bold; font-size: 9.5pt; border-top: 2px solid #1a1a1a; padding-top: 6px; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .status-badge { display: inline-block; padding: 2px 9px; border-radius: 10px; font-size: 8pt; font-weight: bold; }
        .validity-notice { border-radius: 3px; padding: 7px 14px; text-align: center; margin: 12px 0 6px; font-size: 9pt; font-weight: bold; }
        .validity-valid { background: #fefce8; border: 1px solid #fde68a; color: #92400e; }
        .validity-expired { background: #fff5f5; border: 1px solid #fca5a5; color: #7f1d1d; }
        .bank-tbl { border-collapse: collapse; }
        .bank-tbl td { font-size: 8.5pt; padding: 2.5px 0; vertical-align: top; }
        .bank-tbl .bl { color: #999; width: 100px; white-space: nowrap; }
        .bank-tbl .bv { font-weight: bold; color: #1a1a1a; }
        .terms-title { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #999; letter-spacing: 0.5px; margin-bottom: 5px; }
        .terms-body { font-size: 8.5pt; color: #555; line-height: 1.65; }
        .terms-list { margin: 0; padding-left: 14px; }
        .terms-list li { margin-bottom: 3px; font-size: 8.5pt; color: #555; line-height: 1.55; }
        .zero-rated { color: #166534; background: #f0fdf4; padding: 2px 6px; border-radius: 3px; font-size: 8pt; }
        .footer { margin-top: 20px; border-top: 1px solid #e0e0e0; padding-top: 9px; text-align: center; font-size: 7.5pt; color: #bbbbbb; line-height: 1.7; }
    </style>
    <style>
        .section-lbl { color: {{ $accent }}; }
        .totals-tbl tr.gtrow .tv { color: {{ $accent }}; }
    </style>
</head>
<body>
<div class="page">

    {{-- QUOTATION TITLE --}}
    <div style="text-align:center; margin-bottom:16px;">
        <div style="font-size:24pt; font-weight:bold; color:#1a1a1a; letter-spacing:4px;">QUOTATION</div>
    </div>

    {{-- HEADER --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top; width:58%;">
                @if($logoLocalPath && file_exists($logoLocalPath))
                    <div style="margin-bottom:7px;">
                        <img src="{{ $logoLocalPath }}" style="height:52px; max-width:160px; object-fit:contain;" alt="logo">
                    </div>
                @endif
                <div class="co-name">{{ $cs?->company_name ?? 'Milele Power' }}</div>
                @if($cs?->tagline)
                    <div style="font-size:8pt; color:#888; margin-bottom:2px;">{{ $cs->tagline }}</div>
                @endif
                <div class="co-contact">
                    @if($cs?->vrn_number)<strong>VRN:</strong> {{ $cs->vrn_number }}<br>@endif
                    @if($cs?->tin_number)<strong>TIN:</strong> {{ $cs->tin_number }}<br>@endif
                    @if($cs?->phone_primary)<strong>Phone:</strong> {{ $cs->phone_primary }}<br>@endif
                    @if($cs?->email_general)<strong>Email:</strong> {{ $cs->email_general }}<br>@endif
                    @if($cs?->address_line1){{ $cs->address_line1 }}@if($cs?->city), {{ $cs->city }}@endif
                    @elseif($cs?->city){{ $cs->city }}, {{ $cs?->country ?? 'Tanzania' }}
                    @else Dar es Salaam, Tanzania
                    @endif
                </div>
            </td>
            <td style="vertical-align:top; text-align:right; width:42%;">
                <table class="doc-meta-tbl" style="margin-top:0;">
                    <tr><td class="lbl">Quote No:</td><td class="val">{{ $quotation->quotation_number }}</td></tr>
                    <tr><td class="lbl">Date Issued:</td><td class="val">{{ $quotation->issue_date?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr>
                        <td class="lbl">Valid Until:</td>
                        <td class="val">
                            @if($quotation->valid_until)
                                {{ $quotation->valid_until->format('d/m/Y') }}
                            @else N/A @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Status:</td>
                        <td class="val">
                            @php
                                $statusColors = [
                                    'draft'    => ['bg'=>'#f3f4f6','fg'=>'#374151'],
                                    'sent'     => ['bg'=>'#dbeafe','fg'=>'#1e40af'],
                                    'approved' => ['bg'=>'#dcfce7','fg'=>'#166534'],
                                    'rejected' => ['bg'=>'#fee2e2','fg'=>'#991b1b'],
                                    'expired'  => ['bg'=>'#fef9c3','fg'=>'#854d0e'],
                                    'converted'=> ['bg'=>'#f3e8ff','fg'=>'#6b21a8'],
                                ];
                                $sc = $statusColors[$quotation->status] ?? ['bg'=>'#f3f4f6','fg'=>'#374151'];
                            @endphp
                            <span class="status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['fg'] }};">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- PREPARED FOR + META --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:18px;">
        <tr>
            <td style="vertical-align:top; width:52%; padding-right:24px;">
                <div class="section-lbl">Prepared For:</div>
                @if($quotation->client)
                    <div class="client-name-big">
                        {{ $quotation->client->company_name ?? $quotation->client->full_name }}
                    </div>
                    <table class="client-field">
                        @if($quotation->client->company_name)
                        <tr><td class="fl">Contact:</td><td class="fv">{{ $quotation->client->full_name }}</td></tr>
                        @endif
                        @if($quotation->client->vrn)
                        <tr><td class="fl">VRN:</td><td class="fv">{{ $quotation->client->vrn }}</td></tr>
                        @endif
                        @if($quotation->client->tin_number)
                        <tr><td class="fl">TIN:</td><td class="fv">{{ $quotation->client->tin_number }}</td></tr>
                        @endif
                        @if($quotation->client->email)
                        <tr><td class="fl">Email:</td><td class="fv">{{ $quotation->client->email }}</td></tr>
                        @endif
                        @if($quotation->client->phone)
                        <tr><td class="fl">Phone:</td><td class="fv">{{ $quotation->client->phone }}</td></tr>
                        @endif
                    </table>
                @elseif($quotation->quoteRequest)
                    <div class="client-name-big">{{ $quotation->quoteRequest->contact_name }}</div>
                    <table class="client-field">
                        @if($quotation->quoteRequest->company_name)
                        <tr><td class="fl">Company:</td><td class="fv">{{ $quotation->quoteRequest->company_name }}</td></tr>
                        @endif
                        @if($quotation->quoteRequest->email)
                        <tr><td class="fl">Email:</td><td class="fv">{{ $quotation->quoteRequest->email }}</td></tr>
                        @endif
                        @if($quotation->quoteRequest->phone)
                        <tr><td class="fl">Phone:</td><td class="fv">{{ $quotation->quoteRequest->phone }}</td></tr>
                        @endif
                    </table>
                @elseif($quotation->customer_name)
                    <div class="client-name-big">{{ $quotation->customer_name }}</div>
                    <table class="client-field">
                        @if($quotation->customer_email)
                        <tr><td class="fl">Email:</td><td class="fv">{{ $quotation->customer_email }}</td></tr>
                        @endif
                        @if($quotation->customer_phone)
                        <tr><td class="fl">Phone:</td><td class="fv">{{ $quotation->customer_phone }}</td></tr>
                        @endif
                    </table>
                @else
                    <div class="client-name-big" style="color:#999;">Direct Quotation</div>
                @endif
            </td>
            <td style="vertical-align:top; width:48%; padding-left:24px; border-left:1px solid #eeeeee;">
                <table class="meta-field">
                    @if($quotation->quoteRequest)
                    <tr><td class="fl">Request Ref:</td><td class="fv">{{ $quotation->quoteRequest->request_number }}</td></tr>
                    @endif
                    @if($quotation->preparedBy ?? $quotation->user)
                    <tr><td class="fl">Prepared By:</td><td class="fv">{{ ($quotation->preparedBy ?? $quotation->user)?->name }}</td></tr>
                    @endif
                    @if($quotation->payment_terms)
                    <tr><td class="fl">Pay. Terms:</td><td class="fv">{{ $quotation->payment_terms }}</td></tr>
                    @endif
                    <tr>
                        <td class="fl">Currency:</td>
                        <td class="fv">
                            {{ $quotation->currency ?? 'TZS' }}
                            @if($quotation->currency === 'USD' && $quotation->exchange_rate_to_tzs)
                                &nbsp;<span style="font-weight:normal;color:#888;">(Rate: {{ number_format($quotation->exchange_rate_to_tzs, 0) }} TZS)</span>
                            @endif
                        </td>
                    </tr>
                    @if($quotation->is_zero_rated ?? false)
                    <tr><td class="fl">VAT Status:</td><td class="fv"><span class="zero-rated">Zero Rated (0%)</span></td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ITEMS TABLE --}}
    <div class="section-lbl" style="margin-bottom:7px;">Quotation Details:</div>
    <table class="items-tbl">
        <thead>
            <tr>
                <th style="width:44%;">Particulars</th>
                <th class="r" style="width:8%;">Qty</th>
                <th class="r" style="width:16%;">Unit Price ({{ $quotation->currencySymbol() }})</th>
                <th class="r" style="width:9%;">Days</th>
                <th class="r" style="width:15%;">Subtotal ({{ $quotation->currencySymbol() }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items as $item)
            <tr>
                <td>
                    <div class="item-desc">{{ $item->description }}</div>
                    @if($item->item_type_label ?? null)
                    <div class="item-sub">{{ $item->item_type_label }}</div>
                    @endif
                </td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">{{ number_format($item->unit_price, 2) }}</td>
                <td class="r">{{ $item->duration_days ?? '&mdash;' }}</td>
                <td class="r">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#aaa;padding:18px;">No items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="clearfix" style="margin-bottom:6px;">
        <table class="totals-tbl">
            <tr>
                <td class="tl">Sub-Total:</td>
                <td class="tv">{{ $quotation->formatAmount($quotation->subtotal ?? 0, 2) }}</td>
            </tr>
            @if($quotation->is_zero_rated ?? false)
            <tr>
                <td class="tl">VAT:</td>
                <td class="tv" style="color:#166534;">Zero Rated (0%)</td>
            </tr>
            @else
            <tr>
                <td class="tl">VAT {{ $quotation->vat_rate ?? 18 }}%:</td>
                <td class="tv">{{ $quotation->formatAmount($quotation->vat_amount ?? 0, 2) }}</td>
            </tr>
            @endif
            <tr class="gtrow">
                <td class="tl">Grand Total:</td>
                <td class="tv">{{ $quotation->formatAmount($quotation->total_amount ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- VALIDITY NOTICE --}}
    @if($quotation->valid_until)
        @if($quotation->valid_until->isPast())
        <div class="validity-notice validity-expired">
            This quotation expired on {{ $quotation->valid_until->format('d M Y') }}. Please request a renewed quotation.
        </div>
        @else
        <div class="validity-notice validity-valid">
            This quotation is valid until {{ $quotation->valid_until->format('d M Y') }}
            ({{ $quotation->valid_until->diffForHumans() }}).
        </div>
        @endif
    @endif

    {{-- BANK DETAILS --}}
    @if($cs?->bank_name || $cs?->bank_account_number || $cs?->bank_swift_code)
    <hr class="divider-soft">
    <div class="section-lbl" style="margin-bottom:7px;">Bank Details</div>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top; width:50%; padding-right:18px;">
                <table class="bank-tbl">
                    @if($cs->bank_swift_code)
                    <tr><td class="bl">Swift Code:</td><td class="bv">{{ $cs->bank_swift_code }}</td></tr>
                    @endif
                    @if($cs->bank_name)
                    <tr><td class="bl">Bank:</td><td class="bv">{{ $cs->bank_name }}@if($cs->bank_branch_name), {{ $cs->bank_branch_name }}@endif</td></tr>
                    @endif
                    @if($cs->bank_account_name)
                    <tr><td class="bl">A/C Name:</td><td class="bv">{{ $cs->bank_account_name }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="vertical-align:top; width:50%; padding-left:18px; border-left:1px dashed #e0e0e0;">
                <table class="bank-tbl">
                    @if($cs->bank_account_number)
                    <tr><td class="bl">Account No.:</td><td class="bv">{{ $cs->bank_account_number }}</td></tr>
                    @endif
                    @if($cs?->payment_instructions)
                    <tr><td colspan="2" style="font-size:8pt;color:#777;padding-top:5px;line-height:1.55;">{{ $cs->payment_instructions }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
    @endif

    {{-- TERMS & CONDITIONS --}}
    <hr class="divider-soft">
    <div class="section-lbl" style="margin-bottom:7px;">Terms &amp; Conditions:</div>
    @if($quotation->terms_conditions)
        <div class="terms-body">{{ $quotation->terms_conditions }}</div>
    @elseif($cs?->quotation_terms)
        <div class="terms-body">{{ $cs->quotation_terms }}</div>
    @else
        <ul class="terms-list">
            <li>The above quoted price is exclusive of VAT unless otherwise stated.</li>
            <li>The rental period begins from the date of generator delivery to the client's site.</li>
            <li>Payment is due within 7 days of invoice date unless otherwise agreed.</li>
            <li>Fuel and consumables are the responsibility of the client unless stated otherwise.</li>
            <li>{{ $cs?->company_name ?? 'Milele Power' }} reserves the right to withdraw this quotation if not accepted within the validity period.</li>
            <li>All prices are subject to change without prior notice after the validity date.</li>
        </ul>
    @endif

    {{-- SIGNATURE --}}
    <table style="width:100%; border-collapse:collapse; margin-top:28px;">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%; text-align:center;">
                <div style="border-top:1px solid #888; width:180px; margin:0 auto 5px;"></div>
                <div style="font-size:8.5pt; color:#666;">Authorized Signature</div>
                <div style="font-size:8pt; color:#aaa; margin-top:2px;">{{ $cs?->company_name ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        This quotation was generated by the {{ $cs?->company_name ?? 'Milele Power' }} Rental System.<br>
        Thank you for your trust and partnership!
    </div>

</div>
</body>
</html>