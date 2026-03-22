<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Balance Sheet</h1>
            <p class="text-sm text-gray-500 mt-0.5">Financial position as at a selected date</p>
        </div>
        <a href="{{ route('admin.accounting.reports.profit-loss') }}"
           class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
            ← P&L Statement
        </a>
    </div>

    {{-- Date filter --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">As At</label>
            <input type="date" name="as_at" value="{{ $asAt }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Run Report</button>
        <span class="text-xs text-gray-400 self-center">As at {{ \Carbon\Carbon::parse($asAt)->format('d M Y') }}</span>
    </form>

    {{-- Balance check --}}
    @php $diff = abs($totalAssets - $totalLiabEquity); @endphp
    @if($diff > 0.02)
    <div class="mb-4 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 text-sm text-orange-800">
        ⚠ Balance sheet is out of balance by <strong>Tsh {{ number_format($diff, 2) }}</strong>. Ensure all transactions have been correctly journalised.
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ASSETS --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900 text-base">Assets</h2>
            </div>
            @if($assets->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-gray-400">No asset accounts with balances.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Code</th>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Account</th>
                            <th class="text-right px-6 py-2 text-xs font-semibold text-gray-500">Balance (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($assets->filter(fn($a) => abs($a->balance) > 0.005) as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-xs font-mono text-gray-500">{{ $row->code }}</td>
                            <td class="px-6 py-2.5 text-gray-700">{{ $row->name }}</td>
                            <td class="px-6 py-2.5 text-right font-medium {{ $row->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                                @if($row->balance < 0)({{ number_format(abs($row->balance), 0) }})@else{{ number_format($row->balance, 0) }}@endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50 border-t border-blue-100">
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-sm font-bold text-blue-900">Total Assets</td>
                            <td class="px-6 py-3 text-right font-bold text-blue-900">{{ number_format($totalAssets, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- LIABILITIES + EQUITY --}}
        <div class="space-y-4">
            {{-- Liabilities --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900 text-base">Liabilities</h2>
                </div>
                @if($liabilities->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-gray-400">No liability accounts with balances.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Code</th>
                                <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Account</th>
                                <th class="text-right px-6 py-2 text-xs font-semibold text-gray-500">Balance (Tsh)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($liabilities->filter(fn($a) => abs($a->balance) > 0.005) as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-2.5 text-xs font-mono text-gray-500">{{ $row->code }}</td>
                                <td class="px-6 py-2.5 text-gray-700">{{ $row->name }}</td>
                                <td class="px-6 py-2.5 text-right font-medium text-gray-800">{{ number_format($row->balance, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-6 py-2.5 text-sm font-bold text-gray-800">Total Liabilities</td>
                                <td class="px-6 py-2.5 text-right font-bold text-gray-800">{{ number_format($totalLiabilities, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            {{-- Equity --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900 text-base">Equity</h2>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Code</th>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Account</th>
                            <th class="text-right px-6 py-2 text-xs font-semibold text-gray-500">Balance (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($equity->filter(fn($a) => abs($a->balance) > 0.005) as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-xs font-mono text-gray-500">{{ $row->code }}</td>
                            <td class="px-6 py-2.5 text-gray-700">{{ $row->name }}</td>
                            <td class="px-6 py-2.5 text-right font-medium text-gray-800">{{ number_format($row->balance, 0) }}</td>
                        </tr>
                        @endforeach
                        {{-- Retained Earnings (from P&L) --}}
                        <tr class="bg-blue-50">
                            <td class="px-6 py-2.5 text-xs font-mono text-blue-600">—</td>
                            <td class="px-6 py-2.5 text-blue-800 font-medium">Retained Earnings / Net Profit</td>
                            <td class="px-6 py-2.5 text-right font-semibold {{ $retainedEarnings >= 0 ? 'text-blue-800' : 'text-red-700' }}">
                                @if($retainedEarnings < 0)({{ number_format(abs($retainedEarnings), 0) }})@else{{ number_format($retainedEarnings, 0) }}@endif
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="2" class="px-6 py-2.5 text-sm font-bold text-gray-800">Total Equity</td>
                            <td class="px-6 py-2.5 text-right font-bold text-gray-800">{{ number_format($totalEquity, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Total L + E --}}
            <div class="bg-blue-600 rounded-xl px-6 py-4 flex items-center justify-between text-white">
                <p class="font-bold text-base">Total Liabilities + Equity</p>
                <p class="text-2xl font-bold">Tsh {{ number_format($totalLiabEquity, 0) }}</p>
            </div>
        </div>
    </div>
</x-admin-layout>
