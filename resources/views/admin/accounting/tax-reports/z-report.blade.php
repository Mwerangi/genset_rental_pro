<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Z-Report — Daily Sales Summary</h1>
            <p class="text-sm text-gray-500 mt-0.5">TRA daily sales closure report</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.tax-reports.vat') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">VAT Report</a>
            <a href="{{ route('admin.accounting.tax-reports.wht') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">WHT Report</a>
            <button onclick="window.print()" class="px-3 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-5a1 1 0 00-1-1H9a1 1 0 00-1 1v5a1 1 0 001 1zm0-12V5a1 1 0 011-1h4a1 1 0 011 1v4"/></svg>
                Print
            </button>
        </div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Date:</label>
        <input type="date" name="date" value="{{ $date }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Generate Z-Report</button>
    </form>

    {{-- Printable Z-Report Block --}}
    <div id="zReportPrint" class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 print:shadow-none print:border-0">
        {{-- Header --}}
        <div class="text-center mb-6 pb-4 border-b-2 border-gray-800">
            <h2 class="text-xl font-bold text-gray-900 uppercase tracking-wide">MilelePower Ltd</h2>
            <p class="text-sm text-gray-600 mt-1">Generator Rental Services</p>
            <p class="text-xs text-gray-500">TIN: __________________ | VRN: __________________</p>
            <div class="mt-3">
                <p class="text-2xl font-extrabold text-gray-900">*** Z-REPORT ***</p>
                <p class="text-sm text-gray-600 mt-1">Report Date: {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Generated: {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>

        {{-- Summary Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Invoices Issued</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $summary['invoice_count'] }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Gross Sales</p>
                <p class="text-xl font-bold text-gray-900 mt-0.5">{{ number_format($summary['total_subtotal'], 0) }}</p>
                <p class="text-xs text-gray-400">Tsh (excl. VAT)</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3 text-center">
                <p class="text-xs text-red-600 font-medium">VAT @ 18%</p>
                <p class="text-xl font-bold text-red-800 mt-0.5">{{ number_format($summary['total_vat'], 0) }}</p>
                <p class="text-xs text-red-400">Tsh</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-xs text-green-700 font-medium">Total Incl. VAT</p>
                <p class="text-xl font-bold text-green-900 mt-0.5">{{ number_format($summary['total_amount'], 0) }}</p>
                <p class="text-xs text-green-500">Tsh</p>
            </div>
        </div>

        {{-- Collections --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Cash Collections</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Payment Method</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Transactions</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Amount (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($byMethod as $method => $data)
                        <tr>
                            <td class="px-4 py-2.5 font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $method) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-600">{{ $data['count'] }}</td>
                            <td class="px-4 py-2.5 text-right font-mono font-medium">{{ number_format($data['amount'], 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400 text-sm">No collections recorded this date</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                        <tr>
                            <td colspan="2" class="px-4 py-2.5 font-bold text-gray-900">TOTAL COLLECTED</td>
                            <td class="px-4 py-2.5 text-right font-bold font-mono text-gray-900">{{ number_format($summary['total_received'], 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Invoice detail --}}
        @if($invoices->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Invoices Issued — {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Invoice #</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">VAT</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($invoices as $inv)
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs text-blue-700">{{ $inv->invoice_number }}</td>
                            <td class="px-4 py-2 text-gray-700 text-xs">{{ $inv->client?->name }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($inv->subtotal, 0) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-red-600">{{ number_format($inv->vat_amount, 0) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs font-semibold">{{ number_format($inv->total_amount, 0) }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium
                                    {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' :
                                       ($inv->status === 'sent' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-300 font-semibold text-sm">
                        <tr>
                            <td colspan="2" class="px-4 py-2.5 font-bold">TOTALS</td>
                            <td class="px-4 py-2.5 text-right font-mono">{{ number_format($summary['total_subtotal'], 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-red-700">{{ number_format($summary['total_vat'], 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono">{{ number_format($summary['total_amount'], 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        {{-- Signature block --}}
        <div class="mt-8 pt-6 border-t border-gray-300 grid grid-cols-3 gap-8 text-center text-xs text-gray-500 print:mt-10">
            <div>
                <div class="border-b border-gray-400 mb-1 h-8"></div>
                <p>Prepared by</p>
            </div>
            <div>
                <div class="border-b border-gray-400 mb-1 h-8"></div>
                <p>Approved by</p>
            </div>
            <div>
                <div class="border-b border-gray-400 mb-1 h-8"></div>
                <p>Date</p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">*** END OF Z-REPORT ***</p>
    </div>
</x-admin-layout>

<style>
@media print {
    body * { visibility: hidden; }
    #zReportPrint, #zReportPrint * { visibility: visible; }
    #zReportPrint { position: fixed; top: 0; left: 0; width: 100%; }
}
</style>
