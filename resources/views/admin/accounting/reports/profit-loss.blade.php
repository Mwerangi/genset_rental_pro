<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profit & Loss Statement</h1>
            <p class="text-sm text-gray-500 mt-0.5">Revenue and expenses from posted journal entries</p>
        </div>
        <a href="{{ route('admin.accounting.reports.balance-sheet') }}"
           class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
            Balance Sheet →
        </a>
    </div>

    {{-- Date range filter --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Run Report</button>
        <span class="text-xs text-gray-400 self-center">Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium uppercase tracking-wide">Total Revenue</p>
            <p class="text-2xl font-bold text-green-800 mt-1">Tsh {{ number_format($totalRevenue, 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium uppercase tracking-wide">Total Expenses</p>
            <p class="text-2xl font-bold text-red-800 mt-1">Tsh {{ number_format($totalExpenses, 0) }}</p>
        </div>
        <div class="{{ $netProfit >= 0 ? 'bg-blue-50 border-blue-100' : 'bg-orange-50 border-orange-100' }} border rounded-xl p-4">
            <p class="text-xs font-medium uppercase tracking-wide {{ $netProfit >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
            </p>
            <p class="text-2xl font-bold mt-1 {{ $netProfit >= 0 ? 'text-blue-800' : 'text-orange-800' }}">
                Tsh {{ number_format(abs($netProfit), 0) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Revenue</h2>
                <span class="text-sm font-semibold text-green-700">Tsh {{ number_format($totalRevenue, 0) }}</span>
            </div>
            @if($revenueRows->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-gray-400">No revenue posted in this period.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Code</th>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Account</th>
                            <th class="text-right px-6 py-2 text-xs font-semibold text-gray-500">Amount (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($revenueRows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-xs font-mono text-gray-500">{{ $row->code }}</td>
                            <td class="px-6 py-2.5 text-gray-700">{{ $row->name }}</td>
                            <td class="px-6 py-2.5 text-right font-semibold text-green-700">{{ number_format($row->net, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-green-50 border-t border-green-100">
                        <tr>
                            <td colspan="2" class="px-6 py-2.5 text-sm font-bold text-green-800">Total Revenue</td>
                            <td class="px-6 py-2.5 text-right font-bold text-green-800">{{ number_format($totalRevenue, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- Expenses --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Expenses</h2>
                <span class="text-sm font-semibold text-red-700">Tsh {{ number_format($totalExpenses, 0) }}</span>
            </div>
            @if($expenseRows->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-gray-400">No expenses posted in this period.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Code</th>
                            <th class="text-left px-6 py-2 text-xs font-semibold text-gray-500">Account</th>
                            <th class="text-right px-6 py-2 text-xs font-semibold text-gray-500">Amount (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($expenseRows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-xs font-mono text-gray-500">{{ $row->code }}</td>
                            <td class="px-6 py-2.5 text-gray-700">{{ $row->name }}</td>
                            <td class="px-6 py-2.5 text-right font-semibold text-red-700">{{ number_format($row->net, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-red-50 border-t border-red-100">
                        <tr>
                            <td colspan="2" class="px-6 py-2.5 text-sm font-bold text-red-800">Total Expenses</td>
                            <td class="px-6 py-2.5 text-right font-bold text-red-800">{{ number_format($totalExpenses, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- Net Profit line --}}
    <div class="mt-4 bg-white border-2 {{ $netProfit >= 0 ? 'border-blue-200' : 'border-orange-200' }} rounded-xl px-6 py-4 flex items-center justify-between">
        <div>
            <p class="text-base font-bold {{ $netProfit >= 0 ? 'text-blue-900' : 'text-orange-900' }}">
                Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }} for the Period
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-blue-800' : 'text-orange-800' }}">
            @if($netProfit < 0)({{ number_format(abs($netProfit), 0) }})@else{{ number_format($netProfit, 0) }}@endif Tsh
        </p>
    </div>
</x-admin-layout>
