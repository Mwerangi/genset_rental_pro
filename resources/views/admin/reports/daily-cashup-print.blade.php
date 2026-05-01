<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Empty title so browser prints nothing in the header --}}
    <title></title>
    @vite(['resources/css/app.css'])
    <style>
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            color: #111827;
            background: white;
            margin: 0;
            padding: 16px 20px;
        }

        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }

        table { border-collapse: collapse; width: 100%; }
        th, td { vertical-align: top; }

        .section-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .account-header {
            background: #f3f4f6;
            padding: 6px 12px;
            border-bottom: 1px solid #d1d5db;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .badge {
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 9999px;
            font-weight: 600;
        }
        .badge-bank   { background: #dbeafe; color: #1d4ed8; }
        .badge-cash   { background: #dcfce7; color: #15803d; }
        .badge-mobile { background: #f3e8ff; color: #7e22ce; }

        .balance-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-bottom: 1px solid #e5e7eb;
        }
        .balance-cell {
            padding: 8px 12px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
        }
        .balance-cell:last-child { border-right: none; background: #eff6ff; }
        .balance-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 2px; }
        .balance-value { font-size: 12px; font-weight: 600; }

        .txn-section { padding: 8px 12px; border-top: 1px solid #f3f4f6; }
        .txn-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
        .txn-title-green  { color: #15803d; }
        .txn-title-red    { color: #b91c1c; }
        .txn-title-orange { color: #c2410c; }

        .data-table { font-size: 9.5px; }
        .data-table th {
            text-align: left;
            color: #6b7280;
            font-weight: 500;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table th.r, .data-table td.r { text-align: right; }
        .data-table td { padding: 2px 4px 2px 0; color: #374151; border-bottom: 1px solid #f9fafb; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tfoot td { border-top: 1px solid #d1d5db; padding-top: 3px; font-weight: 700; }
        .mono { font-family: ui-monospace, monospace; }
        .muted { color: #9ca3af; }
        .italic { font-style: italic; }

        /* Grand totals */
        .grand-totals {
            border: 2px solid #1f2937;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 8px;
        }
        .grand-header { background: #1f2937; color: white; padding: 6px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .grand-row { display: grid; grid-template-columns: 60px 1fr; border-top: 1px solid #e5e7eb; }
        .grand-row:first-child { border-top: none; }
        .grand-currency { padding: 8px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6b7280; display: flex; align-items: center; }
        .grand-cells { display: grid; grid-template-columns: repeat(3, 1fr); divide-x: 1px solid #e5e7eb; }
        .grand-cell { padding: 8px 12px; text-align: center; border-left: 1px solid #e5e7eb; }
        .grand-cell-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
        .grand-cell-value { font-size: 13px; font-weight: 700; }

        /* Signature block */
        .sig-block { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-top: 48px; text-align: center; font-size: 9px; }
        .sig-line { border-top: 1px solid #1f2937; padding-top: 3px; margin-top: 40px; font-weight: 600; }
        .sig-sub { color: #9ca3af; }

        .no-activity { padding: 8px 12px; font-size: 9px; color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>

    {{-- ── Report header ──────────────────────────────────────────── --}}
    <div style="text-align:center; border-bottom: 2px solid #1f2937; padding-bottom: 10px; margin-bottom: 14px;">
        <p style="font-size:9px; text-transform:uppercase; letter-spacing:0.1em; color:#6b7280; margin:0;">{{ config('app.name') }}</p>
        <h1 style="font-size:16px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin:4px 0 2px;">Daily Cash-Up Report</h1>
        <p style="font-size:11px; margin:0;">
            Date: <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
        </p>
        <p style="font-size:9px; color:#9ca3af; margin:2px 0 0;">Generated: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- ── Account cards ──────────────────────────────────────────── --}}
    @foreach($accounts as $acct)
        @php
            $badgeClass = match($acct['account_type']) {
                'bank'         => 'badge-bank',
                'cash'         => 'badge-cash',
                'mobile_money' => 'badge-mobile',
                default        => '',
            };
            $typeLabel = match($acct['account_type']) {
                'bank'         => 'Bank',
                'cash'         => 'Cash',
                'mobile_money' => 'Mobile Money',
                default        => $acct['account_type'],
            };
        @endphp

        <div class="section-card">
            <div class="account-header">
                <span style="font-weight:700; font-size:11px;">{{ $acct['name'] }}</span>
                <span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                <span style="margin-left:auto; font-size:9px; color:#6b7280;">{{ $acct['currency'] ?? 'TZS' }}</span>
            </div>

            <div class="balance-grid">
                <div class="balance-cell">
                    <div class="balance-label">Opening</div>
                    <div class="balance-value" style="color:#374151;">{{ number_format($acct['opening_balance'], 2) }}</div>
                </div>
                <div class="balance-cell">
                    <div class="balance-label">+ Inflows</div>
                    <div class="balance-value" style="color:#15803d;">{{ number_format($acct['total_in'], 2) }}</div>
                </div>
                <div class="balance-cell">
                    <div class="balance-label">− Outflows</div>
                    <div class="balance-value" style="color:#b91c1c;">{{ number_format($acct['total_out'], 2) }}</div>
                </div>
                <div class="balance-cell">
                    <div class="balance-label" style="color:#1d4ed8;">Closing</div>
                    <div class="balance-value" style="color:{{ $acct['closing_balance'] >= 0 ? '#1d4ed8' : '#b91c1c' }};">
                        {{ number_format($acct['closing_balance'], 2) }}
                    </div>
                </div>
            </div>

            @if(!$acct['has_activity'])
                <div class="no-activity">No transactions recorded for this date.</div>
            @else

                {{-- Collections --}}
                @if($acct['payments']->isNotEmpty())
                    <div class="txn-section">
                        <div class="txn-title txn-title-green">
                            Collections — {{ $acct['payments']->count() }} payment(s)
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:34px;">Time</th>
                                    <th style="width:70px;">Invoice</th>
                                    <th>Client</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                    <th>Notes</th>
                                    <th class="r">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acct['payments'] as $pay)
                                    <tr>
                                        <td class="mono muted">{{ $pay->created_at?->format('H:i') ?? '—' }}</td>
                                        <td class="mono">{{ $pay->invoice->invoice_number ?? 'N/A' }}</td>
                                        <td>{{ $pay->invoice->client->name ?? '—' }}</td>
                                        <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $pay->payment_method ?? '—') }}</td>
                                        <td>{{ $pay->reference ?? '—' }}</td>
                                        <td>{{ $pay->recordedBy->name ?? '—' }}</td>
                                        <td class="italic muted">{{ $pay->notes ?? '—' }}</td>
                                        <td class="r" style="color:#15803d; font-weight:600;">{{ number_format($pay->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" style="color:#374151;">Subtotal</td>
                                    <td class="r" style="color:#15803d;">{{ number_format($acct['payments']->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- Expenses --}}
                @if($acct['expenses']->isNotEmpty())
                    <div class="txn-section">
                        <div class="txn-title txn-title-red">
                            Expenses — {{ $acct['expenses']->count() }} item(s)
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:34px;">Time</th>
                                    <th style="width:70px;">Ref</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Receipt Ref</th>
                                    <th>Posted By</th>
                                    <th class="r">Net</th>
                                    <th class="r">VAT</th>
                                    <th class="r">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acct['expenses'] as $exp)
                                    <tr>
                                        <td class="mono muted">{{ $exp->created_at?->format('H:i') ?? '—' }}</td>
                                        <td class="mono">{{ $exp->expense_number }}</td>
                                        <td>{{ $exp->category->name ?? '—' }}</td>
                                        <td>{{ $exp->description }}</td>
                                        <td>{{ $exp->reference ?? '—' }}</td>
                                        <td>{{ $exp->createdBy->name ?? '—' }}</td>
                                        <td class="r">{{ number_format($exp->amount, 2) }}</td>
                                        <td class="r muted">{{ number_format($exp->vat_amount, 2) }}</td>
                                        <td class="r" style="color:#b91c1c; font-weight:600;">{{ number_format($exp->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="color:#374151;">Subtotal</td>
                                    <td class="r">{{ number_format($acct['expenses']->sum('amount'), 2) }}</td>
                                    <td class="r muted">{{ number_format($acct['expenses']->sum('vat_amount'), 2) }}</td>
                                    <td class="r" style="color:#b91c1c;">{{ number_format($acct['expenses']->sum('total_amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- Cash Disbursements --}}
                @if($acct['cash_reqs']->isNotEmpty())
                    <div class="txn-section">
                        <div class="txn-title txn-title-orange">
                            Cash Disbursements — {{ $acct['cash_reqs']->count() }} request(s)
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:34px;">Time</th>
                                    <th style="width:70px;">Ref</th>
                                    <th>Purpose</th>
                                    <th>Requested By</th>
                                    <th class="r">Requested</th>
                                    <th class="r">Actual Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acct['cash_reqs'] as $cr)
                                    <tr>
                                        <td class="mono muted">{{ $cr->paid_at?->format('H:i') ?? '—' }}</td>
                                        <td class="mono">{{ $cr->request_number }}</td>
                                        <td>{{ $cr->purpose }}</td>
                                        <td>{{ $cr->requestedBy->name ?? '—' }}</td>
                                        <td class="r muted">{{ number_format($cr->total_amount, 2) }}</td>
                                        <td class="r" style="color:#c2410c; font-weight:600;">{{ number_format($cr->actual_amount ?? $cr->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="color:#374151;">Subtotal</td>
                                    <td class="r muted">{{ number_format($acct['cash_reqs']->sum('total_amount'), 2) }}</td>
                                    <td class="r" style="color:#c2410c;">{{ number_format($acct['cash_reqs']->sum(fn($cr) => $cr->actual_amount ?? $cr->total_amount), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

            @endif
        </div>
    @endforeach

    {{-- ── Grand totals ────────────────────────────────────────────── --}}
    @if($accounts->isNotEmpty())
        <div class="grand-totals">
            <div class="grand-header">Grand Totals</div>
            @foreach($currencyTotals as $currency => $totals)
                <div class="grand-row">
                    <div class="grand-currency">{{ $currency }}</div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr);">
                        <div class="grand-cell">
                            <div class="grand-cell-label" style="color:#15803d;">Total In</div>
                            <div class="grand-cell-value" style="color:#15803d;">{{ number_format($totals['total_in'], 2) }}</div>
                        </div>
                        <div class="grand-cell">
                            <div class="grand-cell-label" style="color:#b91c1c;">Total Out</div>
                            <div class="grand-cell-value" style="color:#b91c1c;">{{ number_format($totals['total_out'], 2) }}</div>
                        </div>
                        <div class="grand-cell" style="background:#f8fafc;">
                            <div class="grand-cell-label" style="color:#374151;">Net Movement</div>
                            <div class="grand-cell-value" style="color:{{ $totals['net'] >= 0 ? '#1d4ed8' : '#b91c1c' }};">
                                {{ ($totals['net'] >= 0 ? '+' : '') . number_format($totals['net'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Signature block --}}
        <div class="sig-block">
            <div>
                <div class="sig-line">Prepared By</div>
                <div class="sig-sub">Name &amp; Signature / Date</div>
            </div>
            <div>
                <div class="sig-line">Reviewed By</div>
                <div class="sig-sub">Name &amp; Signature / Date</div>
            </div>
            <div>
                <div class="sig-line">Approved By</div>
                <div class="sig-sub">Name &amp; Signature / Date</div>
            </div>
        </div>
    @endif

    <script>
        // Set title to empty string — removes page title from browser print header
        document.title = ' ';
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
                // Close the tab after printing (works in most browsers)
                window.addEventListener('afterprint', function () { window.close(); });
            }, 300);
        });
    </script>
</body>
</html>
