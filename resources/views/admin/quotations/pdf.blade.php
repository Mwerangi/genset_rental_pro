<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 40px;
            border-bottom: 3px solid #DC2626;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #DC2626;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }

        /* Document Title */
        .document-title {
            text-align: center;
            margin: 30px 0;
        }

        .quotation-title {
            font-size: 28px;
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 5px;
        }

        .quotation-number {
            font-size: 16px;
            color: #DC2626;
            font-weight: bold;
        }

        /* Two Column Layout */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-box {
            background: #F8FAFC;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            font-size: 14px;
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box p {
            font-size: 12px;
            margin-bottom: 5px;
            color: #475569;
        }

        .info-box .label {
            font-weight: bold;
            color: #1E293B;
        }

        /* Items Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table thead {
            background: #1E293B;
            color: white;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tbody tr {
            border-bottom: 1px solid #E2E8F0;
        }

        table tbody tr:hover {
            background: #F8FAFC;
        }

        table td {
            padding: 12px;
            font-size: 11px;
        }

        table td:last-child,
        table th:last-child {
            text-align: right;
        }

        .item-description {
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 3px;
        }

        .item-type {
            color: #64748B;
            font-size: 10px;
        }

        /* Totals */
        .totals-section {
            width: 50%;
            margin-left: auto;
            margin-bottom: 30px;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #E2E8F0;
        }

        .total-row .label {
            display: table-cell;
            text-align: left;
            font-weight: 600;
            color: #475569;
        }

        .total-row .value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            color: #1E293B;
        }

        .grand-total {
            background: #FEF2F2;
            border: 2px solid #DC2626;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
        }

        .grand-total .label {
            font-size: 16px;
            font-weight: bold;
            color: #1E293B;
        }

        .grand-total .value {
            font-size: 24px;
            font-weight: bold;
            color: #DC2626;
        }

        /* Terms Section */
        .terms-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E2E8F0;
        }

        .terms-section h3 {
            font-size: 14px;
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .terms-section p {
            font-size: 11px;
            color: #475569;
            margin-bottom: 15px;
            line-height: 1.8;
            white-space: pre-line;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #DC2626;
            text-align: center;
            font-size: 10px;
            color: #64748B;
        }

        .footer p {
            margin-bottom: 5px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background: #F1F5F9; color: #475569; }
        .status-sent { background: #DBEAFE; color: #1E40AF; }
        .status-accepted { background: #D1FAE5; color: #065F46; }
        .status-rejected { background: #FEE2E2; color: #991B1B; }
        .status-expired { background: #FEF3C7; color: #92400E; }

        /* Validity Notice */
        .validity-notice {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 12px;
            margin: 20px 0;
            font-size: 11px;
            color: #92400E;
        }

        .validity-notice strong {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Milele Power</div>
            <div class="company-tagline">Reliable Generator Rental Solutions</div>
            <div class="company-info">
                Email: info@milelepower.co.tz | Phone: +255 123 456 789<br>
                Address: Dar es Salaam, Tanzania | Website: www.milelepower.co.tz
            </div>
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <div class="quotation-title">QUOTATION</div>
            <div class="quotation-number">{{ $quotation->quotation_number }}</div>
        </div>

        <!-- Customer & Quotation Info -->
        <div class="two-columns">
            <div class="column" style="padding-right: 15px;">
                <div class="info-box">
                    <h3>Bill To</h3>
                    @if($quotation->quoteRequest)
                        <p><span class="label">Name:</span> {{ $quotation->quoteRequest->full_name }}</p>
                        <p><span class="label">Email:</span> {{ $quotation->quoteRequest->email }}</p>
                        <p><span class="label">Phone:</span> {{ $quotation->quoteRequest->phone }}</p>
                        @if($quotation->quoteRequest->company_name)
                            <p><span class="label">Company:</span> {{ $quotation->quoteRequest->company_name }}</p>
                        @endif
                    @else
                        <p>Direct Quotation</p>
                    @endif
                </div>
            </div>
            <div class="column" style="padding-left: 15px;">
                <div class="info-box">
                    <h3>Quotation Details</h3>
                    <p><span class="label">Date Issued:</span> {{ $quotation->created_at->format('F d, Y') }}</p>
                    <p><span class="label">Valid Until:</span> {{ $quotation->valid_until->format('F d, Y') }}</p>
                    <p><span class="label">Status:</span> <span class="status-badge status-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span></p>
                    @if($quotation->quoteRequest)
                        <p><span class="label">Reference:</span> {{ $quotation->quoteRequest->request_number }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Validity Notice -->
        @if(!$quotation->isExpired())
            <div class="validity-notice">
                <strong>⏰ This quotation is valid until {{ $quotation->valid_until->format('F d, Y') }}</strong><br>
                Please review and respond before the expiration date. Prices and availability are subject to change after this date.
            </div>
        @else
            <div class="validity-notice" style="background: #FEE2E2; border-color: #DC2626; color: #991B1B;">
                <strong>⚠️ This quotation has expired</strong><br>
                This quotation expired on {{ $quotation->valid_until->format('F d, Y') }}. Please contact us for an updated quotation.
            </div>
        @endif

        <!-- Line Items -->
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: center;">Duration</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                    <tr>
                        <td>
                            <div class="item-description">{{ $item->description }}</div>
                            <div class="item-type">{{ $item->item_type_formatted }}</div>
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">TZS {{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: center;">
                            @if($item->item_type === 'genset_rental' && $item->duration_days)
                                {{ $item->duration_days }} days
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: right;">TZS {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <div class="label">Subtotal</div>
                <div class="value">TZS {{ number_format($quotation->subtotal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="label">VAT ({{ $quotation->vat_rate }}%)</div>
                <div class="value">TZS {{ number_format($quotation->vat_amount, 2) }}</div>
            </div>
            <div class="grand-total">
                <div class="total-row" style="border: none;">
                    <div class="label">TOTAL AMOUNT</div>
                    <div class="value">TZS {{ number_format($quotation->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="terms-section">
            @if($quotation->payment_terms)
                <h3>Payment Terms</h3>
                <p>{{ $quotation->payment_terms }}</p>
            @endif

            @if($quotation->terms_conditions)
                <h3>Terms & Conditions</h3>
                <p>{{ $quotation->terms_conditions }}</p>
            @endif

            @if(!$quotation->payment_terms && !$quotation->terms_conditions)
                <h3>Payment Terms</h3>
                <p>Payment due within 30 days of acceptance. All prices are in Tanzanian Shillings (TZS).</p>
                
                <h3>Terms & Conditions</h3>
                <p>
                    1. This quotation is valid for the period specified above.<br>
                    2. Prices are subject to change without notice after the validity period.<br>
                    3. Generator rental includes basic maintenance during the rental period.<br>
                    4. Delivery and pickup charges may apply based on location.<br>
                    5. Full payment is required before equipment delivery.<br>
                    6. Security deposit may be required for rental equipment.
                </p>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>For questions about this quotation, please contact us at info@milelepower.co.tz or +255 123 456 789</p>
            <p>© {{ date('Y') }} Milele Power. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
