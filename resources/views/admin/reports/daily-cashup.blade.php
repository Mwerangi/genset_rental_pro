<x-admin-layout>
    <x-slot name="header">Daily Cash-Up Report</x-slot>

    {{-- ── Filter bar ──────────────────────────────────────────────────── --}}
    <div class="mb-6 print:hidden">
        <form method="GET" action="{{ route('admin.accounting.reports.daily-cashup') }}"
              class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                Generate
            </button>
            <button type="button" onclick="printReport()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </form>
    </div>

    {{-- ── Print header (visible only when printing) ───────────────────── --}}
    <div class="hidden print:block mb-6 text-center border-b-2 border-gray-800 pb-4">
        <p class="text-xs text-gray-600 uppercase tracking-widest">{{ config('app.name') }}</p>
        <h1 class="text-xl font-bold uppercase tracking-wide mt-1">Daily Cash-Up Report</h1>
        <p class="text-sm mt-1">
            Date: <span class="font-semibold">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
        </p>
        <p class="text-xs text-gray-500 mt-0.5">Generated: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- ── Screen heading ───────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4 print:hidden">
        <h2 class="text-lg font-semibold text-gray-800">
            {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
        </h2>
        <span class="text-sm text-gray-500">{{ $accounts->count() }} account(s)</span>
    </div>

    {{-- ── No accounts fallback ─────────────────────────────────────────── --}}
    @if($accounts->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center text-yellow-800 text-sm">
            No active bank / cash accounts found.
        </div>
    @endif

    {{-- ── Account cards ────────────────────────────────────────────────── --}}
    @foreach($accounts as $acct)
        @php
            $typeColors = [
                'bank'         => 'bg-blue-100 text-blue-700',
                'cash'         => 'bg-green-100 text-green-700',
                'mobile_money' => 'bg-purple-100 text-purple-700',
            ];
            $typeLabel = [
                'bank'         => 'Bank',
                'cash'         => 'Cash',
                'mobile_money' => 'Mobile Money',
            ];
            $currencyFmt = fn($val) => number_format(abs($val), 2) . ' ' . ($acct['currency'] ?? 'TZS');
        @endphp

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-5 overflow-hidden
                    print:shadow-none print:border print:border-gray-400 print:mb-4 print:break-inside-avoid">

            {{-- Account header --}}
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center gap-2
                        print:bg-gray-100 print:py-2">
                <span class="font-semibold text-gray-900 text-sm">{{ $acct['name'] }}</span>
                <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $typeColors[$acct['account_type']] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $typeLabel[$acct['account_type']] ?? $acct['account_type'] }}
                </span>
                @if($acct['is_snapshot'])
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-emerald-100 text-emerald-700 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Snapshot · {{ $acct['closed_at']?->format('H:i') }}
                    </span>
                @else
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-amber-100 text-amber-700">Live</span>
                @endif
                <span class="text-xs text-gray-500 ml-auto">{{ $acct['currency'] ?? 'TZS' }}</span>
            </div>

            {{-- Balance summary row --}}
            <div class="grid grid-cols-4 divide-x divide-gray-100 print:divide-gray-300">
                <div class="px-4 py-3 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Opening</p>
                    <p class="text-sm font-semibold text-gray-700">
                        {{ number_format($acct['opening_balance'], 2) }}
                    </p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">+ Inflows</p>
                    <p class="text-sm font-semibold text-green-600">
                        {{ number_format($acct['total_in'], 2) }}
                    </p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">− Outflows</p>
                    <p class="text-sm font-semibold text-red-600">
                        {{ number_format($acct['total_out'], 2) }}
                    </p>
                </div>
                <div class="px-4 py-3 text-center bg-blue-50 print:bg-blue-50">
                    <p class="text-xs text-blue-600 uppercase tracking-wide mb-0.5 font-medium">Closing</p>
                    <p class="text-sm font-bold {{ $acct['closing_balance'] >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                        {{ number_format($acct['closing_balance'], 2) }}
                    </p>
                </div>
            </div>

            @if(!$acct['has_activity'])
                <div class="px-4 py-3 text-xs text-gray-400 italic border-t border-gray-100">
                    No transactions recorded for this date.
                </div>
            @else
                <div class="divide-y divide-gray-100 print:divide-gray-200">

                    {{-- Collections ---------------------------------------- --}}
                    @if($acct['payments']->isNotEmpty())
                        <div class="px-4 pt-3 pb-3">
                            <h4 class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-2">
                                Collections &mdash; {{ $acct['payments']->count() }} payment(s)
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="text-gray-500 border-b border-gray-100">
                                            <th class="text-left pb-1 font-medium w-14">Time</th>
                                            <th class="text-left pb-1 font-medium w-24">Invoice</th>
                                            <th class="text-left pb-1 font-medium">Client</th>
                                            <th class="text-left pb-1 font-medium">Method</th>
                                            <th class="text-left pb-1 font-medium">Reference</th>
                                            <th class="text-left pb-1 font-medium">Recorded By</th>
                                            <th class="text-left pb-1 font-medium">Notes</th>
                                            <th class="text-right pb-1 font-medium">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($acct['payments'] as $pay)
                                            <tr class="border-b border-gray-50 last:border-0">
                                                <td class="py-1 text-gray-500 font-mono tabular-nums whitespace-nowrap">
                                                    {{ $pay->created_at?->format('H:i') ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-700 font-mono">
                                                    {{ $pay->invoice->invoice_number ?? 'N/A' }}
                                                </td>
                                                <td class="py-1 text-gray-700">
                                                    {{ $pay->invoice->client->name ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-600 capitalize whitespace-nowrap">
                                                    {{ str_replace('_', ' ', $pay->payment_method ?? '—') }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $pay->reference ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $pay->recordedBy->name ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-500 italic">
                                                    {{ $pay->notes ?? '—' }}
                                                </td>
                                                <td class="py-1 text-right font-medium text-green-700 whitespace-nowrap">
                                                    {{ number_format($pay->amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-green-200">
                                            <td colspan="7" class="pt-1 text-xs font-semibold text-gray-600">Subtotal</td>
                                            <td class="pt-1 text-right text-xs font-bold text-green-700">
                                                {{ number_format($acct['payments']->sum('amount'), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Expenses -------------------------------------------- --}}
                    @if($acct['expenses']->isNotEmpty())
                        <div class="px-4 pt-3 pb-3">
                            <h4 class="text-xs font-semibold text-red-700 uppercase tracking-wide mb-2">
                                Expenses &mdash; {{ $acct['expenses']->count() }} item(s)
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="text-gray-500 border-b border-gray-100">
                                            <th class="text-left pb-1 font-medium w-14">Time</th>
                                            <th class="text-left pb-1 font-medium w-24">Ref</th>
                                            <th class="text-left pb-1 font-medium">Category</th>
                                            <th class="text-left pb-1 font-medium">Description</th>
                                            <th class="text-left pb-1 font-medium">Receipt Ref</th>
                                            <th class="text-left pb-1 font-medium">Posted By</th>
                                            <th class="text-right pb-1 font-medium">Net</th>
                                            <th class="text-right pb-1 font-medium">VAT</th>
                                            <th class="text-right pb-1 font-medium">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($acct['expenses'] as $exp)
                                            <tr class="border-b border-gray-50 last:border-0">
                                                <td class="py-1 text-gray-500 font-mono tabular-nums whitespace-nowrap">
                                                    {{ $exp->created_at?->format('H:i') ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-700 font-mono">
                                                    {{ $exp->expense_number }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $exp->category->name ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-700">
                                                    {{ $exp->description }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $exp->reference ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $exp->createdBy->name ?? '—' }}
                                                </td>
                                                <td class="py-1 text-right text-gray-700 tabular-nums">
                                                    {{ number_format($exp->amount, 2) }}
                                                </td>
                                                <td class="py-1 text-right text-gray-500 tabular-nums">
                                                    {{ number_format($exp->vat_amount, 2) }}
                                                </td>
                                                <td class="py-1 text-right font-medium text-red-700 tabular-nums whitespace-nowrap">
                                                    {{ number_format($exp->total_amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-red-200">
                                            <td colspan="6" class="pt-1 text-xs font-semibold text-gray-600">Subtotal</td>
                                            <td class="pt-1 text-right text-xs text-gray-600 tabular-nums">{{ number_format($acct['expenses']->sum('amount'), 2) }}</td>
                                            <td class="pt-1 text-right text-xs text-gray-500 tabular-nums">{{ number_format($acct['expenses']->sum('vat_amount'), 2) }}</td>
                                            <td class="pt-1 text-right text-xs font-bold text-red-700 tabular-nums">{{ number_format($acct['expenses']->sum('total_amount'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Cash Disbursements ---------------------------------- --}}
                    @if($acct['cash_reqs']->isNotEmpty())
                        <div class="px-4 pt-3 pb-3">
                            <h4 class="text-xs font-semibold text-orange-700 uppercase tracking-wide mb-2">
                                Cash Disbursements &mdash; {{ $acct['cash_reqs']->count() }} request(s)
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="text-gray-500 border-b border-gray-100">
                                            <th class="text-left pb-1 font-medium w-14">Time</th>
                                            <th class="text-left pb-1 font-medium w-24">Ref</th>
                                            <th class="text-left pb-1 font-medium">Purpose</th>
                                            <th class="text-left pb-1 font-medium">Requested By</th>
                                            <th class="text-right pb-1 font-medium">Requested</th>
                                            <th class="text-right pb-1 font-medium">Actual Paid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($acct['cash_reqs'] as $cr)
                                            <tr class="border-b border-gray-50 last:border-0">
                                                <td class="py-1 text-gray-500 font-mono tabular-nums whitespace-nowrap">
                                                    {{ $cr->paid_at?->format('H:i') ?? '—' }}
                                                </td>
                                                <td class="py-1 text-gray-700 font-mono">
                                                    {{ $cr->request_number }}
                                                </td>
                                                <td class="py-1 text-gray-700">
                                                    {{ $cr->purpose }}
                                                </td>
                                                <td class="py-1 text-gray-600">
                                                    {{ $cr->requestedBy->name ?? '—' }}
                                                </td>
                                                <td class="py-1 text-right text-gray-500 tabular-nums">
                                                    {{ number_format($cr->total_amount, 2) }}
                                                </td>
                                                <td class="py-1 text-right font-medium text-orange-700 tabular-nums whitespace-nowrap">
                                                    {{ number_format($cr->actual_amount ?? $cr->total_amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-orange-200">
                                            <td colspan="4" class="pt-1 text-xs font-semibold text-gray-600">Subtotal</td>
                                            <td class="pt-1 text-right text-xs text-gray-500 tabular-nums">{{ number_format($acct['cash_reqs']->sum('total_amount'), 2) }}</td>
                                            <td class="pt-1 text-right text-xs font-bold text-orange-700 tabular-nums">
                                                {{ number_format($acct['cash_reqs']->sum(fn($cr) => $cr->actual_amount ?? $cr->total_amount), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>
            @endif
        </div>
    @endforeach

    {{-- ── Grand totals ────────────────────────────────────────────────── --}}
    @if($accounts->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-2 print:border-gray-800 print:shadow-none">
            <div class="px-4 py-2.5 bg-gray-800 text-white print:bg-gray-800">
                <h3 class="text-sm font-semibold uppercase tracking-wide">Grand Totals</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($currencyTotals as $currency => $totals)
                    <div class="grid grid-cols-3 px-4 py-3 text-sm">
                        <div class="text-gray-500 text-xs font-medium uppercase tracking-wide self-center">
                            {{ $currency }}
                        </div>
                        <div class="grid grid-cols-3 col-span-2 divide-x divide-gray-100 text-center">
                            <div>
                                <p class="text-xs text-green-600 uppercase tracking-wide">Total In</p>
                                <p class="font-semibold text-green-700">{{ number_format($totals['total_in'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-red-600 uppercase tracking-wide">Total Out</p>
                                <p class="font-semibold text-red-700">{{ number_format($totals['total_out'], 2) }}</p>
                            </div>
                            <div class="bg-gray-50">
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Net Movement</p>
                                <p class="font-bold {{ $totals['net'] >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                                    {{ ($totals['net'] >= 0 ? '+' : '') . number_format($totals['net'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Signature block (print only) --}}
        <div class="hidden print:block mt-12">
            <div class="grid grid-cols-3 gap-8 text-center text-xs">
                <div>
                    <div class="border-t border-gray-800 pt-1 mt-12">Prepared By</div>
                    <p class="text-gray-500">Name &amp; Signature / Date</p>
                </div>
                <div>
                    <div class="border-t border-gray-800 pt-1 mt-12">Reviewed By</div>
                    <p class="text-gray-500">Name &amp; Signature / Date</p>
                </div>
                <div>
                    <div class="border-t border-gray-800 pt-1 mt-12">Approved By</div>
                    <p class="text-gray-500">Name &amp; Signature / Date</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Print CSS + script --}}
    <style>
        @media print {
            body { font-size: 11px; }
            .print\:hidden { display: none !important; }
            .hidden.print\:block { display: block !important; }
            .print\:break-inside-avoid { break-inside: avoid; }
        }
        @page { size: A4; margin: 12mm 10mm; }
    </style>
    <script>
        function printReport() {
            var prev = document.title;
            document.title = ' ';
            window.print();
            window.addEventListener('afterprint', function () {
                document.title = prev;
            }, { once: true });
        }
    </script>

</x-admin-layout>
