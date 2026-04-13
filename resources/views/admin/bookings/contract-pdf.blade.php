<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rental Agreement — {{ $booking->booking_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; background: #fff; line-height: 1.55; }
        .page { padding: 48px 44px 36px 44px; }

        /* ── Header ─────────────────────────────────────────── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .company-name  { font-size: 14pt; font-weight: bold; color: #c00000; }
        .company-sub   { font-size: 8.5pt; color: #555; margin-top: 3px; }
        .company-contact { font-size: 8pt; color: #666; margin-top: 6px; line-height: 1.7; }
        .doc-title     { font-size: 16pt; font-weight: bold; color: #1a1a1a; text-align: right; line-height: 1.2; }
        .doc-ref       { font-size: 8.5pt; color: #888; text-align: right; margin-top: 5px; }
        .doc-date      { font-size: 8.5pt; color: #555; text-align: right; margin-top: 3px; }

        /* ── Dividers ────────────────────────────────────────── */
        .divider      { border: none; border-top: 2.5px solid #c00000; margin: 10px 0 14px; }
        .divider-thin { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }

        /* ── Parties ─────────────────────────────────────────── */
        .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .party-box { width: 48%; vertical-align: top; padding: 10px 14px; background: #f9f9f9; border-left: 3px solid #c00000; }
        .party-spacer { width: 4%; }
        .party-label  { font-size: 7.5pt; font-weight: bold; color: #c00000; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; }
        .party-name   { font-size: 10pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3px; }
        .party-detail { font-size: 8.5pt; color: #444; line-height: 1.75; }

        /* ── Section headings ───────────────────────────────── */
        .clause-heading { font-size: 9.5pt; font-weight: bold; color: #c00000; text-transform: uppercase;
                          letter-spacing: 0.5px; margin: 14px 0 5px; }
        .clause-body    { font-size: 9pt; color: #333; margin-bottom: 4px; }
        .clause-sub     { font-size: 9pt; color: #333; margin: 3px 0 3px 14px; }

        /* ── Fee table ──────────────────────────────────────── */
        .fee-table { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        .fee-table th { font-size: 8.5pt; font-weight: bold; background: #c00000; color: #fff; padding: 5px 10px; text-align: left; }
        .fee-table td { font-size: 9pt; padding: 5px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .fee-table tr:nth-child(even) td { background: #fafafa; }
        .fee-table .val { text-align: right; font-weight: bold; }
        .fee-total td { border-top: 2px solid #c00000; font-weight: bold; }
        .fee-total .val { color: #c00000; font-size: 10pt; }

        /* ── Payment info box ───────────────────────────────── */
        .bank-box { background: #f9f9f9; border-left: 3px solid #c00000; padding: 9px 12px; margin: 7px 0 12px; font-size: 9pt; }
        .bank-box .bank-label { font-size: 8pt; color: #888; }
        .bank-box .bank-val   { font-weight: bold; color: #1a1a1a; }

        /* ── Signature section ──────────────────────────────── */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .sig-box   { width: 48%; vertical-align: top; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 4px; }
        .sig-spacer { width: 4%; }
        .sig-party  { font-size: 8.5pt; font-weight: bold; color: #c00000; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .sig-field  { font-size: 8.5pt; color: #444; padding: 3px 0; border-bottom: 1px solid #ccc; margin-bottom: 8px; }
        .sig-label  { font-size: 7.5pt; color: #888; margin-top: 2px; }
        .sig-line   { height: 24px; border-bottom: 1px solid #999; margin-bottom: 2px; }

        /* ── Footer ─────────────────────────────────────────── */
        .footer { text-align: center; font-size: 7.5pt; color: #999; border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 18px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ─────────────────────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td style="width:55%; vertical-align:top;">
                <div class="company-name">Milele Power Limited</div>
                <div class="company-sub">Generator (Genset) Rental Services</div>
                <div class="company-contact">
                    P.O. Box: 75941, Dar es Salaam, Tanzania<br>
                    TIN: 181-458-437 &nbsp;|&nbsp; Tel: +255 746 322 916<br>
                    Email: info@milelepower.co.tz
                </div>
            </td>
            <td style="width:45%; vertical-align:top;">
                <div class="doc-title">REEFER GENERATOR (GENSET)<br>RENTAL AGREEMENT</div>
                <div class="doc-ref">Ref: {{ $booking->booking_number }}</div>
                <div class="doc-date">Date: {{ now()->format('d F Y') }}</div>
            </td>
        </tr>
    </table>
    <hr class="divider">

    {{-- ── PREAMBLE ─────────────────────────────────────────── --}}
    <p class="clause-body">
        This Reefer Generator (Genset) Rental Agreement ("Agreement") is made and entered into on
        <strong>{{ $booking->approved_at?->format('d F Y') ?? now()->format('d F Y') }}</strong>, by and between:
    </p>

    {{-- ── PARTIES ──────────────────────────────────────────── --}}
    <table class="parties-table" style="margin-top:12px;">
        <tr>
            {{-- Supplier --}}
            <td class="party-box">
                <div class="party-label">Supplier</div>
                <div class="party-name">Milele Power Limited</div>
                <div class="party-detail">
                    P.O. Box: 75941<br>
                    Location: Dar es Salaam, Tanzania<br>
                    TIN: 181-458-437<br>
                    Tel: +255 746 322 916<br>
                    Email: info@milelepower.co.tz
                </div>
            </td>
            <td class="party-spacer"></td>
            {{-- Client --}}
            <td class="party-box">
                <div class="party-label">Client</div>
                <div class="party-name">{{ $clientName }}</div>
                <div class="party-detail">
                    @if($clientAddress) P.O. Box / Address: {{ $clientAddress }}<br>@endif
                    @if($clientLocation) Location: {{ $clientLocation }}<br>@endif
                    @if($clientTin) TIN: {{ $clientTin }}<br>@endif
                    @if($clientVrn) VRN: {{ $clientVrn }}<br>@endif
                    @if($clientPhone) Tel: {{ $clientPhone }}<br>@endif
                    @if($clientEmail) Email: {{ $clientEmail }}@endif
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider-thin">

    {{-- ── 1. SCOPE ─────────────────────────────────────────── --}}
    <div class="clause-heading">1. Scope of Agreement</div>
    <p class="clause-body">
        1.1 Milele Power Limited agrees to provide a genset(s) to the Client for a rental duration of
        <strong>{{ $booking->rental_duration_days }} day(s)</strong>,
        Model: <strong>{{ $booking->genset?->model ?? $booking->genset_type }}</strong>,
        Genset No: <strong>{{ $booking->genset?->asset_number ?? '—' }}</strong>
        @if($booking->genset?->serial_number), Serial No: <strong>{{ $booking->genset->serial_number }}</strong>@endif.
    </p>
    <p class="clause-body" style="margin-top:4px;">
        1.2 The Client agrees to rent the genset(s) from Milele Power Limited for
        <strong>{{ $booking->rental_duration_days }} day(s)</strong>,
        Model: <strong>{{ $booking->genset?->model ?? $booking->genset_type }}</strong>,
        Genset No: <strong>{{ $booking->genset?->asset_number ?? '—' }}</strong>.
    </p>

    {{-- ── 2. TERM ───────────────────────────────────────────── --}}
    <div class="clause-heading">2. Term of Agreement</div>
    <p class="clause-body">
        2.1 This Agreement commences on <strong>{{ $booking->rental_start_date?->format('d F Y') }}</strong>
        and remains in effect until <strong>{{ $booking->rental_end_date?->format('d F Y') }}</strong>,
        unless terminated earlier in accordance with this Agreement.
    </p>
    <p class="clause-body" style="margin-top:4px;">
        2.2 In the event of an overstay, the rental term will extend accordingly, subject to mutually agreed terms.
    </p>

    {{-- ── 3. RENTAL FEES ────────────────────────────────────── --}}
    <div class="clause-heading">3. Rental Fees</div>
    <table class="fee-table">
        <thead>
            <tr>
                <th style="width:55%;">Description</th>
                <th style="width:20%; text-align:right;">Rate</th>
                <th style="width:25%; text-align:right;">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @if($quotationItems->isNotEmpty())
                @foreach($quotationItems as $item)
                <tr>
                    <td>{{ $item->description }}@if($item->duration_days) <br><span style="font-size:8pt;color:#888;">{{ $item->duration_days }} day(s) × {{ $item->unit_price }}</span>@endif</td>
                    <td class="val">{{ $currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="val">{{ $currency }} {{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>Genset Rental — {{ $booking->rental_duration_days }} day(s)<br>
                        <span style="font-size:8pt;color:#888;">{{ $booking->genset?->model ?? $booking->genset_type }}</span></td>
                    <td class="val">—</td>
                    <td class="val">{{ $currency }} {{ number_format($booking->total_amount, 2) }}</td>
                </tr>
            @endif
            @if($lifting_fee)
            <tr>
                <td>Lift On / Off Fee</td>
                <td class="val">—</td>
                <td class="val">{{ $currency }} {{ number_format($lifting_fee, 2) }}</td>
            </tr>
            @endif
            @if($transport_fee)
            <tr>
                <td>Transportation Fee — {{ $booking->drop_on_location }}</td>
                <td class="val">—</td>
                <td class="val">{{ $currency }} {{ number_format($transport_fee, 2) }}</td>
            </tr>
            @else
            <tr>
                <td>Transportation Fee</td>
                <td class="val">—</td>
                <td class="val" style="color:#888;">To be agreed</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            @if($vatAmount > 0)
            <tr>
                <td colspan="2" style="text-align:right;color:#555;">Subtotal</td>
                <td class="val">{{ $currency }} {{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:right;color:#555;">VAT ({{ $vatRate }}%)</td>
                <td class="val">{{ $currency }} {{ number_format($vatAmount, 2) }}</td>
            </tr>
            @endif
            <tr class="fee-total">
                <td colspan="2" style="text-align:right;">Total Contract Value</td>
                <td class="val">{{ $currency }} {{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <p class="clause-body" style="color:#555;">
        3.4 An additional invoice will be issued for any days beyond the agreed rental term.
    </p>

    {{-- ── 4. PAYMENT TERMS ──────────────────────────────────── --}}
    <div class="clause-heading">4. Payment Terms</div>
    <p class="clause-body">4.1 Total Rental Cost: The total cost is
        <strong>{{ $currency }} {{ number_format($totalAmount, 2) }}</strong>
        @if($booking->currency === 'USD') (approximately TZS {{ number_format($totalAmount * ($booking->exchange_rate_to_tzs ?? 1), 0) }} at rate {{ number_format($booking->exchange_rate_to_tzs ?? 1, 2) }}) @endif
        payable according to the following terms:</p>
    <p class="clause-sub">• 100% payment in advance for short-term rentals (daily / weekly / monthly).</p>
    <p class="clause-sub">• Security Deposit: 10%–30% of the genset value (refundable upon safe return).</p>
    <p class="clause-sub">• Late Payment Penalty: 5%–10% surcharge on overdue amounts.</p>
    <p class="clause-body" style="margin-top:6px;">4.2 Payment Methods — Payments should be made via cash or bank transfer:</p>
    <div class="bank-box">
        <table style="width:100%; border-collapse:collapse; font-size:9pt;">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    <span class="bank-label">Bank Name</span><br><span class="bank-val">CRDB Bank, Water Front Branch, Dar es Salaam</span><br><br>
                    <span class="bank-label">Account Name</span><br><span class="bank-val">Milele Power Limited</span>
                </td>
                <td style="width:50%; vertical-align:top; padding-left:16px;">
                    <span class="bank-label">TZS Account No.</span><br><span class="bank-val">015000002BP00</span><br><br>
                    <span class="bank-label">USD Account No.</span><br><span class="bank-val">025000002BP00</span><br><br>
                    <span class="bank-label">SWIFT Code</span><br><span class="bank-val">CORUTZTZ</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── 5. DELIVERY & INSTALLATION ───────────────────────── --}}
    <div class="clause-heading">5. Delivery &amp; Installation</div>
    <p class="clause-body">5.1 The Supplier will deliver the genset to the Client's site at
        <strong>{{ $booking->drop_on_location }}</strong>
        @if($booking->rental_start_date) by <strong>{{ $booking->rental_start_date->format('d F Y') }}</strong>@endif.
    </p>
    <p class="clause-body">5.2 The Client is responsible for delivery and installation charges, unless otherwise agreed.</p>
    <p class="clause-body">5.3 The Supplier shall provide operational training to the Client's personnel upon request.</p>

    {{-- ── 6. WARRANTIES & MAINTENANCE ─────────────────────── --}}
    <div class="clause-heading">6. Warranties &amp; Maintenance</div>
    <p class="clause-body">6.1 The Supplier warrants that the genset is free from defects and operates per manufacturer specifications for the duration of the rental period from the delivery date.</p>
    <p class="clause-body">6.2 The Client shall cover daily operational costs but not routine maintenance.</p>
    <p class="clause-body">6.3 For leased units, the Client is responsible for scheduled maintenance as specified in the service agreement.</p>

    {{-- ── 7. OWNERSHIP & LIABILITY ─────────────────────────── --}}
    <div class="clause-heading">7. Ownership &amp; Liability</div>
    <p class="clause-body">7.1 The Client assumes responsibility for any damage, loss, or theft of the genset during the rental period.</p>

    {{-- ── 8. TERMINATION ────────────────────────────────────── --}}
    <div class="clause-heading">8. Termination</div>
    <p class="clause-body">8.1 This Agreement may be terminated:</p>
    <p class="clause-sub">• By mutual written consent of both parties.</p>
    <p class="clause-sub">• By the Supplier, if the Client fails to make timely payments.</p>

    {{-- ── 9. DISPUTE RESOLUTION ────────────────────────────── --}}
    <div class="clause-heading">9. Dispute Resolution</div>
    <p class="clause-body">9.1 Any disputes will first be addressed through good-faith negotiations. If unresolved, disputes shall be resolved via arbitration in Dar es Salaam, Tanzania under the laws of Tanzania.</p>

    {{-- ── 10. GOVERNING LAW ────────────────────────────────── --}}
    <div class="clause-heading">10. Governing Law</div>
    <p class="clause-body">10.1 This Agreement shall be governed by the laws of the United Republic of Tanzania.</p>

    {{-- ── 11. ENTIRE AGREEMENT ─────────────────────────────── --}}
    <div class="clause-heading">11. Entire Agreement</div>
    <p class="clause-body">11.1 This document represents the full understanding between the parties and supersedes any prior agreements or communications.</p>

    {{-- ── 12. ADDITIONAL CLAUSES ───────────────────────────── --}}
    <div class="clause-heading">12. Additional Clauses</div>
    <p class="clause-body"><strong>12.1 Insurance:</strong> The Client shall maintain insurance coverage for the rented Genset against theft, loss, and damage for the duration of the rental period. Proof of insurance must be provided upon request.</p>
    <p class="clause-body"><strong>12.2 Inspection &amp; Handover:</strong> An inspection report will be completed and signed by both parties at the time of delivery and return, documenting the condition of the Genset.</p>
    <p class="clause-body"><strong>12.3 Use Restrictions:</strong> The Genset shall not be used for unlawful activities or beyond its rated power capacity. The Client agrees to use the Genset in accordance with manufacturer guidelines.</p>
    <p class="clause-body"><strong>12.4 Damage Responsibility:</strong> The Client shall be liable for all repair costs resulting from misuse, negligence, or unauthorized alterations.</p>
    <p class="clause-body"><strong>12.5 Emergency Support:</strong> The Supplier shall provide 24/7 emergency support. The Client must immediately report any malfunction or damage.</p>
    <p class="clause-body"><strong>12.6 Return Procedure:</strong> The Client must return the Genset to the Supplier's designated location, or arrange for pickup, as agreed. Any delays may incur extra charges.
        @if($booking->drop_off_location) Agreed drop-OFF location: <strong>{{ $booking->drop_off_location }}</strong>.@endif
    </p>
    <p class="clause-body"><strong>12.7 Force Majeure:</strong> Neither party shall be liable for failure to perform obligations due to events beyond reasonable control, including but not limited to natural disasters, war, strikes, or government orders.</p>
    <p class="clause-body"><strong>12.8 Amendment Clause:</strong> No amendments to this Agreement shall be valid unless made in writing and signed by authorized representatives of both parties.</p>
    @if($booking->notes)
    <p class="clause-body"><strong>12.9 Special Notes:</strong> {{ $booking->notes }}</p>
    @endif

    {{-- ── IN WITNESS WHEREOF ───────────────────────────────── --}}
    <hr class="divider" style="margin-top:18px;">
    <p class="clause-body" style="font-style:italic; margin-bottom:10px;">
        IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above.
    </p>
    <table class="sig-table">
        <tr>
            <td class="sig-box">
                <div class="sig-party">Supplier</div>
                <div class="sig-field">Company: <strong>Milele Power Limited</strong></div>
                <div style="margin-top:8px;">
                    <div class="sig-label">Name</div>
                    <div style="padding:3px 0; font-size:9pt; font-weight:bold; color:#1a1a1a;">Obadia Mpyambala</div>
                </div>
                <div style="margin-top:8px;">
                    <div class="sig-label">Position / Title</div>
                    <div style="padding:3px 0; font-size:9pt; font-weight:bold; color:#1a1a1a;">Director</div>
                </div>
                <div style="margin-top:10px;">
                    <div class="sig-label">Signature &amp; Stamp</div>
                    @php
                        $sigPath = public_path('img/signature-stamp.png');
                        $sigSrc  = null;
                        if (file_exists($sigPath)) {
                            $mime   = mime_content_type($sigPath);
                            $sigSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($sigPath));
                        }
                    @endphp
                    @if($sigSrc)
                        <img src="{{ $sigSrc }}" alt="Signature & Stamp"
                             style="display:block; width:200px; height:auto; margin-top:6px;">
                    @else
                        <div style="height:80px; border-bottom:1px solid #999; margin-top:6px;"></div>
                    @endif
                </div>
                <div style="margin-top:8px;">
                    <div class="sig-label">Date</div>
                    <div style="padding:3px 0; font-size:9pt; font-weight:bold; color:#1a1a1a;">{{ now()->format('d F Y') }}</div>
                </div>
            </td>
            <td class="sig-spacer"></td>
            <td class="sig-box">
                <div class="sig-party">Client</div>
                <div class="sig-field">Company: <strong>{{ $clientName }}</strong></div>
                <div style="margin-top:10px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Name</div>
                </div>
                <div style="margin-top:10px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Position / Title</div>
                </div>
                <div style="margin-top:10px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Signature</div>
                </div>
                <div style="margin-top:10px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Date</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── FOOTER ───────────────────────────────────────────── --}}
    <div class="footer">
        Milele Power Limited &nbsp;|&nbsp; P.O. Box 75941, Dar es Salaam, Tanzania &nbsp;|&nbsp;
        Tel: +255 746 322 916 &nbsp;|&nbsp; info@milelepower.co.tz &nbsp;|&nbsp;
        Agreement Ref: {{ $booking->booking_number }}
    </div>

</div>
</body>
</html>
