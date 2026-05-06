@php
    $cs = $companySetting;
    $logoLocalPath = $cs?->logo_path ? storage_path('app/public/' . $cs->logo_path) : null;
    $logoBase64 = null;
    if ($logoLocalPath && file_exists($logoLocalPath)) {
        $mime = mime_content_type($logoLocalPath);
        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoLocalPath));
    }
    $clientName = $client->company_name ?: $client->full_name;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Statement of Accounts – {{ $clientName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #2d2d2d; background: #fff; }
        .page { padding: 18px 24px 18px; }

        /* Header */
        .header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-tbl td { vertical-align: top; }
        .logo { max-height: 42px; max-width: 130px; }
        .co-name { font-size: 12pt; font-weight: bold; color: #1a1a1a; }
        .co-sub { font-size: 7.5pt; color: #777; margin-top: 2px; line-height: 1.5; }
        .doc-title { font-size: 15pt; font-weight: bold; color: #1a1a1a; text-align: right; }
        .doc-meta { font-size: 8pt; color: #555; text-align: right; margin-top: 4px; line-height: 1.7; }

        .divider { border: none; border-top: 2px solid #1a1a1a; margin: 8px 0 10px; }
        .divider-soft { border: none; border-top: 1px solid #e0e0e0; margin: 8px 0; }

        /* Client box */
        .client-box { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .client-box td { font-size: 8pt; padding: 2px 0; vertical-align: top; }
        .client-box .lbl { color: #888; width: 80px; }
        .client-box .val { font-weight: bold; color: #1a1a1a; }

        /* Summary cards */
        .summary-tbl { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary-tbl td { border: 1px solid #e8e8e8; padding: 7px 10px; width: 25%; vertical-align: top; }
        .sum-title { font-size: 7pt; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .sum-val { font-size: 9.5pt; font-weight: bold; }
        .sum-val.red { color: #b91c1c; }
        .sum-val.green { color: #15803d; }
        .sum-val.blue { color: #1d4ed8; }
        .sum-val.gray { color: #9ca3af; }

        /* Transaction table */
        .txn-tbl { width: 100%; border-collapse: collapse; }
        .txn-tbl thead tr.group-hdr th { background: #1a1a1a; color: #fff; font-size: 7.5pt; padding: 5px 7px; text-align: center; }
        .txn-tbl thead tr.group-hdr th.left { text-align: left; }
        .txn-tbl thead tr.sub-hdr th { background: #f3f4f6; font-size: 7pt; font-weight: bold; color: #555; padding: 3px 7px; text-align: right; }
        .txn-tbl thead tr.sub-hdr th.left { text-align: left; }
        .txn-tbl tbody td { font-size: 8pt; padding: 5px 7px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .txn-tbl tbody tr.opening td { background: #f9fafb; font-style: italic; }
        .txn-tbl tbody tr.payment td { background: #f0fdf4; }
        .txn-tbl tbody td.r { text-align: right; font-family: "DejaVu Sans Mono", monospace; }
        .txn-tbl tbody td.muted { color: #ccc; text-align: right; font-family: "DejaVu Sans Mono", monospace; }
        .txn-tbl tfoot td { font-size: 8.5pt; padding: 6px 7px; border-top: 2px solid #1a1a1a; font-weight: bold; }
        .txn-tbl tfoot td.r { text-align: right; font-family: "DejaVu Sans Mono", monospace; }
        .txn-tbl tfoot td.blue { color: #1d4ed8; }
        .txn-tbl tfoot td.green { color: #15803d; }
        .txn-tbl tfoot td.red { color: #b91c1c; }
        .txn-tbl tfoot td.gray { color: #6b7280; }

        .status-badge { font-size: 6.5pt; font-weight: bold; padding: 1px 5px; border-radius: 8px; }
        .s-sent           { background: #dbeafe; color: #1e40af; }
        .s-partially_paid { background: #fef9c3; color: #854d0e; }
        .s-paid           { background: #dcfce7; color: #166534; }
        .s-disputed       { background: #fee2e2; color: #991b1b; }
        .s-draft          { background: #f3f4f6; color: #374151; }

        .footer { margin-top: 16px; border-top: 1px solid #e0e0e0; padding-top: 6px; font-size: 7pt; color: #aaa; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <table class="header-tbl">
        <tr>
            <td style="width:50%">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
                @endif
                <div class="co-name" style="{{ $logoBase64 ? 'margin-top:6px' : '' }}">{{ $cs?->company_name ?? 'Company Name' }}</div>
                <div class="co-sub">
                    @if($cs?->address) {{ $cs->address }}<br>@endif
                    @if($cs?->phone) Tel: {{ $cs->phone }}@if($cs?->email) &nbsp;|&nbsp; {{ $cs->email }}@endif<br>@endif
                    @if($cs?->tin_number) TIN: {{ $cs->tin_number }}@endif
                    @if($cs?->vrn) &nbsp;|&nbsp; VRN: {{ $cs->vrn }}@endif
                </div>
            </td>
            <td style="width:50%; text-align:right">
                <div class="doc-title">STATEMENT OF ACCOUNT</div>
                <div class="doc-meta">
                    <strong>Period:</strong> {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}<br>
                    <strong>Printed:</strong> {{ now()->format('d M Y, H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── Client Info ── --}}
    <table class="client-box">
        <tr>
            <td class="lbl">Client:</td>
            <td class="val">{{ $clientName }}</td>
            @if($client->company_name && $client->full_name)
            <td class="lbl" style="padding-left:20px">Contact:</td>
            <td class="val">{{ $client->full_name }}</td>
            @else
            <td></td><td></td>
            @endif
            <td class="lbl" style="padding-left:20px">Client No:</td>
            <td class="val">{{ $client->client_number }}</td>
        </tr>
        <tr>
            <td class="lbl">Email:</td>
            <td class="val">{{ $client->email ?: '—' }}</td>
            <td class="lbl" style="padding-left:20px">Phone:</td>
            <td class="val">{{ $client->phone ?: '—' }}</td>
            @if($client->tin_number)
            <td class="lbl" style="padding-left:20px">TIN:</td>
            <td class="val">{{ $client->tin_number }}</td>
            @else
            <td></td><td></td>
            @endif
        </tr>
    </table>

    <hr class="divider-soft">

    {{-- ── Summary cards ── --}}
    <table class="summary-tbl">
        <tr>
            <td>
                <div class="sum-title">Opening Balance</div>
                @if($opening['USD'] > 0)
                    <div class="sum-val red">USD {{ number_format($opening['USD'], 2) }}</div>
                @endif
                @if($opening['TZS'] > 0)
                    <div class="sum-val red">TZS {{ number_format($opening['TZS'], 0) }}</div>
                @endif
                @if(!$opening['USD'] && !$opening['TZS'])
                    <div class="sum-val gray">—</div>
                @endif
            </td>
            <td style="background:#eff6ff">
                <div class="sum-title">Invoiced This Period</div>
                @if($invoiced['USD'] > 0)
                    <div class="sum-val blue">USD {{ number_format($invoiced['USD'], 2) }}</div>
                @endif
                @if($invoiced['TZS'] > 0)
                    <div class="sum-val blue">TZS {{ number_format($invoiced['TZS'], 0) }}</div>
                @endif
                @if(!$invoiced['USD'] && !$invoiced['TZS'])
                    <div class="sum-val gray">—</div>
                @endif
            </td>
            <td style="background:#f0fdf4">
                <div class="sum-title">Payments Received</div>
                @if($paid['USD'] > 0)
                    <div class="sum-val green">USD {{ number_format($paid['USD'], 2) }}</div>
                @endif
                @if($paid['TZS'] > 0)
                    <div class="sum-val green">TZS {{ number_format($paid['TZS'], 0) }}</div>
                @endif
                @if(!$paid['USD'] && !$paid['TZS'])
                    <div class="sum-val gray">—</div>
                @endif
            </td>
            <td style="background:#fafafa">
                <div class="sum-title">Closing Balance (Outstanding)</div>
                @if($closing['USD'] != 0)
                    <div class="sum-val {{ $closing['USD'] > 0 ? 'red' : 'green' }}">USD {{ number_format($closing['USD'], 2) }}</div>
                @endif
                @if($closing['TZS'] != 0)
                    <div class="sum-val {{ $closing['TZS'] > 0 ? 'red' : 'green' }}">TZS {{ number_format($closing['TZS'], 0) }}</div>
                @endif
                @if(!$closing['USD'] && !$closing['TZS'])
                    <div class="sum-val gray">Nil</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── Transaction table ── --}}
    <table class="txn-tbl">
        <thead>
            <tr class="group-hdr">
                <th class="left" rowspan="2" style="width:70px">Date</th>
                <th class="left" rowspan="2" style="width:90px">Reference</th>
                <th class="left" rowspan="2">Description</th>
                <th colspan="2" style="width:130px">Debit</th>
                <th colspan="2" style="width:130px">Credit</th>
                <th colspan="2" style="width:130px">Balance</th>
            </tr>
            <tr class="sub-hdr">
                <th style="width:65px">USD</th>
                <th style="width:65px">TZS</th>
                <th style="width:65px">USD</th>
                <th style="width:65px">TZS</th>
                <th style="width:65px">USD</th>
                <th style="width:65px">TZS</th>
            </tr>
        </thead>
        <tbody>
            @if($opening['USD'] > 0 || $opening['TZS'] > 0)
            <tr class="opening">
                <td>{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</td>
                <td>—</td>
                <td>Opening Balance (b/f)</td>
                <td class="muted">—</td>
                <td class="muted">—</td>
                <td class="muted">—</td>
                <td class="muted">—</td>
                <td class="r {{ $opening['USD'] > 0 ? 'red' : '' }}" style="{{ $opening['USD'] > 0 ? 'color:#b91c1c' : 'color:#9ca3af' }}">
                    {{ number_format($opening['USD'], 2) }}
                </td>
                <td class="r" style="{{ $opening['TZS'] > 0 ? 'color:#b91c1c' : 'color:#9ca3af' }}">
                    {{ number_format($opening['TZS'], 0) }}
                </td>
            </tr>
            @endif

            @forelse($lines as $line)
            @php $isUsd = $line['currency'] === 'USD'; @endphp
            <tr class="{{ $line['type'] === 'payment' ? 'payment' : '' }}">
                <td>{{ \Carbon\Carbon::parse($line['date'])->format('d M Y') }}</td>
                <td style="font-family:'DejaVu Sans Mono',monospace; font-size:7.5pt">{{ $line['reference'] }}</td>
                <td>
                    {{ $line['description'] }}
                    @if($line['status'])
                        <span class="status-badge s-{{ $line['status'] }}">
                            {{ ucfirst(str_replace('_', ' ', $line['status'])) }}
                        </span>
                    @endif
                </td>
                {{-- Debit USD --}}
                @if($line['debit'] > 0 && $isUsd)
                    <td class="r" style="font-weight:bold">{{ number_format($line['debit'], 2) }}</td>
                @else
                    <td class="muted">—</td>
                @endif
                {{-- Debit TZS --}}
                @if($line['debit'] > 0 && !$isUsd)
                    <td class="r" style="font-weight:bold">{{ number_format($line['debit'], 0) }}</td>
                @else
                    <td class="muted">—</td>
                @endif
                {{-- Credit USD --}}
                @if($line['credit'] > 0 && $isUsd)
                    <td class="r" style="font-weight:bold; color:#15803d">{{ number_format($line['credit'], 2) }}</td>
                @else
                    <td class="muted">—</td>
                @endif
                {{-- Credit TZS --}}
                @if($line['credit'] > 0 && !$isUsd)
                    <td class="r" style="font-weight:bold; color:#15803d">{{ number_format($line['credit'], 0) }}</td>
                @else
                    <td class="muted">—</td>
                @endif
                {{-- Balance USD --}}
                <td class="r" style="font-weight:bold; color:{{ $line['balance_usd'] > 0 ? '#b91c1c' : ($line['balance_usd'] < 0 ? '#15803d' : '#9ca3af') }}">
                    {{ number_format($line['balance_usd'], 2) }}
                </td>
                {{-- Balance TZS --}}
                <td class="r" style="font-weight:bold; color:{{ $line['balance_tzs'] > 0 ? '#b91c1c' : ($line['balance_tzs'] < 0 ? '#15803d' : '#9ca3af') }}">
                    {{ number_format($line['balance_tzs'], 0) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:18px; color:#9ca3af; font-style:italic">No transactions in this period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="font-size:8.5pt; color:#374151">Totals / Closing Balance</td>
                <td class="r blue">{{ number_format($invoiced['USD'], 2) }}</td>
                <td class="r blue">{{ number_format($invoiced['TZS'], 0) }}</td>
                <td class="r green">{{ number_format($paid['USD'], 2) }}</td>
                <td class="r green">{{ number_format($paid['TZS'], 0) }}</td>
                <td class="r {{ $closing['USD'] > 0 ? 'red' : ($closing['USD'] < 0 ? 'green' : 'gray') }}">
                    {{ number_format($closing['USD'], 2) }}
                </td>
                <td class="r {{ $closing['TZS'] > 0 ? 'red' : ($closing['TZS'] < 0 ? 'green' : 'gray') }}">
                    {{ number_format($closing['TZS'], 0) }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Footer ── --}}
    <div class="footer">
        This statement was generated on {{ now()->format('d F Y \a\t H:i') }} and is correct as of the date printed.
        @if($cs?->company_name) &nbsp;·&nbsp; {{ $cs->company_name }} @endif
    </div>

</div>
</body>
</html>
