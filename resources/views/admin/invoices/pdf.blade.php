@php
    $cs = $companySetting;
    $accent = $cs?->primary_color ?: '#8B1A0A';
    $logoLocalPath  = $cs?->logo_path  ? storage_path('app/public/' . $cs->logo_path)  : null;
    $stampLocalPath = $cs?->stamp_path ? storage_path('app/public/' . $cs->stamp_path) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #3a3a3a; background: #ffffff; }
        .page { background: #ffffff; padding: 20px 28px 20px; }
        .co-name { font-size: 13pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3px; }
        .co-contact { font-size: 8pt; color: #555; line-height: 1.5; margin-top: 2px; }
        .co-contact strong { color: #2a2a2a; }
        .doc-badge { font-size: 22pt; font-weight: bold; color: #1a1a1a; text-align: right; letter-spacing: 2px; }
        .doc-meta-tbl { border-collapse: collapse; margin-left: auto; }
        .doc-meta-tbl td { font-size: 8.5pt; padding: 2px 0 2px 14px; }
        .doc-meta-tbl .lbl { color: #999; white-space: nowrap; }
        .doc-meta-tbl .val { font-weight: bold; color: #1a1a1a; text-align: right; white-space: nowrap; }
        .divider { border: none; border-top: 2px solid #1a1a1a; margin: 10px 0 12px; }
        .divider-soft { border: none; border-top: 1px solid #e8e8e8; margin: 14px 0; }
        .section-lbl { font-size: 9.5pt; font-weight: bold; margin-bottom: 5px; }
        .client-name-big { font-size: 10.5pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3px; }
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
        .totals-tbl tr.sep td { border-top: 1px solid #ddd; padding-top: 6px; }
        .totals-tbl tr.gtrow td { font-weight: bold; font-size: 9.5pt; border-top: 2px solid #1a1a1a; padding-top: 6px; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .status-badge { display: inline-block; padding: 2px 9px; border-radius: 10px; font-size: 8pt; font-weight: bold; }
        .banner { border-radius: 3px; padding: 7px 14px; text-align: center; margin: 12px 0 6px; font-weight: bold; font-size: 9pt; }
        .banner-paid { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .banner-overdue { background: #fff5f5; border: 1px solid #fca5a5; color: #7f1d1d; }
        .bank-tbl { border-collapse: collapse; }
        .bank-tbl td { font-size: 8.5pt; padding: 2.5px 0; vertical-align: top; }
        .bank-tbl .bl { color: #999; width: 100px; white-space: nowrap; }
        .bank-tbl .bv { font-weight: bold; color: #1a1a1a; }
        .terms-title { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #999; letter-spacing: 0.5px; margin-bottom: 4px; }
        .terms-body { font-size: 8.5pt; color: #555; line-height: 1.65; }
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

    {{-- INVOICE TITLE --}}
    <div style="text-align:center; margin-bottom:8px;">
        <div style="font-size:24pt; font-weight:bold; color:#1a1a1a; letter-spacing:4px;">INVOICE</div>
    </div>

    {{-- HEADER --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:top; width:58%;">
                @if($logoLocalPath && file_exists($logoLocalPath))
                    <div style="margin-bottom:4px;">
                        <img src="{{ $logoLocalPath }}" style="height:40px; max-width:140px; object-fit:contain;" alt="logo">
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
                    <tr><td class="lbl">Invoice No:</td><td class="val">{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="lbl">Date Issued:</td><td class="val">{{ $invoice->issue_date->format('d/m/Y') }}</td></tr>
                    <tr><td class="lbl">Due Date:</td><td class="val">{{ $invoice->due_date->format('d/m/Y') }}</td></tr>
                    <tr>
                        <td class="lbl">Status:</td>
                        <td class="val">
                            @php
                                $statusColors = [
                                    'draft'          => ['bg'=>'#f3f4f6','fg'=>'#374151'],
                                    'sent'           => ['bg'=>'#dbeafe','fg'=>'#1e40af'],
                                    'partially_paid' => ['bg'=>'#fef9c3','fg'=>'#854d0e'],
                                    'paid'           => ['bg'=>'#dcfce7','fg'=>'#166534'],
                                    'void'           => ['bg'=>'#fee2e2','fg'=>'#991b1b'],
                                    'overdue'        => ['bg'=>'#fee2e2','fg'=>'#991b1b'],
                                ];
                                $sc = $statusColors[$invoice->status] ?? ['bg'=>'#f3f4f6','fg'=>'#374151'];
                            @endphp
                            <span class="status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['fg'] }};">{{ $invoice->status_label }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ATTENTION TO + META --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="vertical-align:top; width:52%; padding-right:24px;">
                <div class="section-lbl">Attention To:</div>
                <div class="client-name-big">
                    {{ $invoice->client?->company_name ?? $invoice->client?->full_name ?? 'N/A' }}
                </div>
                <table class="client-field">
                    @if($invoice->client?->company_name)
                    <tr><td class="fl">Contact:</td><td class="fv">{{ $invoice->client->full_name }}</td></tr>
                    @endif
                    @if($invoice->client?->vrn)
                    <tr><td class="fl">VRN:</td><td class="fv">{{ $invoice->client->vrn }}</td></tr>
                    @endif
                    @if($invoice->client?->tin_number)
                    <tr><td class="fl">TIN:</td><td class="fv">{{ $invoice->client->tin_number }}</td></tr>
                    @endif
                    @if($invoice->client?->email)
                    <tr><td class="fl">Email:</td><td class="fv">{{ $invoice->client->email }}</td></tr>
                    @endif
                    @if($invoice->client?->phone)
                    <tr><td class="fl">Phone:</td><td class="fv">{{ $invoice->client->phone }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="vertical-align:top; width:48%; padding-left:24px; border-left:1px solid #eeeeee;">
                <table class="meta-field">
                    @if($invoice->booking)
                    <tr><td class="fl">Booking Ref:</td><td class="fv">{{ $invoice->booking->booking_number }}</td></tr>
                    @endif
                    @if($invoice->booking?->gensets?->isNotEmpty())
                    <tr>
                        <td class="fl">Generator(s):</td>
                        <td class="fv">
                            @foreach($invoice->booking->gensets as $g)
                                {{ $g->name }} ({{ $g->kva_rating }} kVA)@if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                    @elseif($invoice->booking?->genset)
                    <tr><td class="fl">Generator:</td><td class="fv">{{ $invoice->booking->genset->name }}</td></tr>
                    @endif
                    @if($invoice->payment_terms)
                    <tr><td class="fl">Pay. Terms:</td><td class="fv">{{ $invoice->payment_terms }}</td></tr>
                    @endif
                    <tr>
                        <td class="fl">Currency:</td>
                        <td class="fv">
                            {{ $invoice->currency ?? 'TZS' }}
                            @if($invoice->currency === 'USD' && $invoice->exchange_rate_to_tzs)
                                &nbsp;<span style="font-weight:normal;color:#888;">(Rate: {{ number_format($invoice->exchange_rate_to_tzs, 0) }} TZS)</span>
                            @endif
                        </td>
                    </tr>
                    @if($invoice->is_zero_rated)
                    <tr><td class="fl">VAT Status:</td><td class="fv"><span class="zero-rated">Zero Rated (0%)</span></td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ITEMS TABLE --}}
    <div class="section-lbl" style="margin-bottom:7px;">Tax Invoice:</div>
    <table class="items-tbl">
        <thead>
            <tr>
                <th style="width:42%;">Particulars</th>
                <th class="r" style="width:8%;">Qty</th>
                <th class="r" style="width:16%;">Unit Price ({{ $invoice->currencySymbol() }})</th>
                <th class="r" style="width:9%;">Days</th>
                <th class="r" style="width:15%;">Subtotal ({{ $invoice->currencySymbol() }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>
                    <div class="item-desc">{{ $item->description }}</div>
                    <div class="item-sub">{{ $item->item_type_label }}</div>
                </td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">{{ number_format($item->unit_price, 2) }}</td>
                <td class="r">{{ $item->duration_days ?? '—' }}</td>
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
                <td class="tv">{{ $invoice->formatAmount($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->is_zero_rated)
            <tr>
                <td class="tl">VAT:</td>
                <td class="tv" style="color:#166534;">Zero Rated (0%)</td>
            </tr>
            @else
            <tr>
                <td class="tl">VAT {{ $invoice->vat_rate }}%:</td>
                <td class="tv">{{ $invoice->formatAmount($invoice->vat_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="gtrow">
                <td class="tl">Grand Total:</td>
                <td class="tv">{{ $invoice->formatAmount($invoice->total_amount, 2) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
            <tr>
                <td class="tl" style="padding-top:5px;color:#166534;">Amount Paid:</td>
                <td class="tv" style="padding-top:5px;color:#166534;">{{ $invoice->formatAmount($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr class="sep">
                <td class="tl" style="font-weight:bold;">Balance Due:</td>
                <td class="tv" style="color:{{ $accent }};">{{ $invoice->formatAmount($invoice->balance_due, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- STATUS BANNERS --}}
    @if($invoice->status === 'paid')
    <div class="banner banner-paid">FULLY PAID &#10004;</div>
    @elseif($invoice->is_overdue)
    <div class="banner banner-overdue">OVERDUE &#9655; Payment was due {{ $invoice->due_date->format('d M Y') }}</div>
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

    {{-- NOTES & TERMS --}}
    @if($invoice->terms_conditions || $invoice->payment_terms || $cs?->invoice_notes)
    <hr class="divider-soft">
    @if($invoice->terms_conditions)
    <div style="margin-bottom:7px;">
        <div class="terms-title">Terms &amp; Conditions</div>
        <div class="terms-body">{{ $invoice->terms_conditions }}</div>
    </div>
    @endif
    @if($invoice->payment_terms)
    <div style="margin-bottom:7px;">
        <div class="terms-title">Payment Terms</div>
        <div class="terms-body">{{ $invoice->payment_terms }}</div>
    </div>
    @endif
    @if(!$invoice->terms_conditions && !$invoice->payment_terms && $cs?->invoice_notes)
    <div>
        <div class="terms-title">Notes</div>
        <div class="terms-body">{{ $cs->invoice_notes }}</div>
    </div>
    @endif
    @endif

    {{-- SIGNATURE --}}
    <table style="width:100%; border-collapse:collapse; margin-top:28px;">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%; text-align:center;">
                @if($stampLocalPath && file_exists($stampLocalPath))
                    <div style="margin-bottom:6px;">
                        <img src="{{ $stampLocalPath }}" style="height:160px; max-width:240px; object-fit:contain; opacity:0.85;" alt="Company Stamp">
                    </div>
                @endif
                <div style="border-top:1px solid #888; width:180px; margin:0 auto 5px;"></div>
                <div style="font-size:8.5pt; color:#666;">Authorized Signature</div>
                <div style="font-size:8pt; color:#aaa; margin-top:2px;">{{ $cs?->company_name ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        This invoice was generated by the {{ $cs?->company_name ?? 'Milele Power' }} Rental System.<br>
        Thank you for your trust and partnership!
    </div>

</div>
</body>
</html>