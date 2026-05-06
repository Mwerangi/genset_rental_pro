<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Statement of Accounts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Per-client transaction history with running balance</p>
        </div>
        <div class="flex items-center gap-2">
            @if($client)
            <a href="{{ route('admin.accounting.reports.statement.pdf', ['client_id' => $clientId, 'from' => $from, 'to' => $to]) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            @endif
            <a href="{{ route('admin.accounting.reports.aging') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Aging Report</a>
        </div>
    </div>

    <!-- Filter form -->
    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-700 mb-1">Client</label>
                <select name="client_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select a client —</option>
                    @foreach($clientsList as $c)
                        <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>
                            {{ $c->company_name ?: $c->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Show</button>
        </div>
    </form>

    @if(!$client)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            <p class="text-gray-500 font-medium">Select a client above to view their statement</p>
        </div>
    @else
    <!-- Client header -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $client->company_name ?: $client->full_name }}</h2>
                @if($client->company_name && $client->full_name)
                    <p class="text-sm text-gray-500">{{ $client->full_name }}</p>
                @endif
                <div class="flex gap-4 mt-1">
                    @if($client->email)
                        <span class="text-xs text-gray-500">{{ $client->email }}</span>
                    @endif
                    @if($client->phone)
                        <span class="text-xs text-gray-500">{{ $client->phone }}</span>
                    @endif
                </div>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Statement Period</p>
                <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Summary cards — always show both USD and TZS rows -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        @php
            $statusStyles = [
                'draft'          => 'bg-gray-100 text-gray-600',
                'sent'           => 'bg-blue-100 text-blue-700',
                'partially_paid' => 'bg-yellow-100 text-yellow-700',
                'paid'           => 'bg-green-100 text-green-700',
                'disputed'       => 'bg-red-100 text-red-700',
            ];
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 font-medium mb-2">Opening Balance</p>
            <p class="text-base font-bold {{ $opening['USD'] > 0 ? 'text-red-700' : 'text-gray-400' }}">USD {{ number_format($opening['USD'], 0) }}</p>
            <p class="text-base font-bold {{ $opening['TZS'] > 0 ? 'text-red-700' : 'text-gray-400' }} mt-0.5">TZS {{ number_format($opening['TZS'], 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium mb-2">Invoiced This Period</p>
            <p class="text-base font-bold {{ $invoiced['USD'] > 0 ? 'text-blue-900' : 'text-blue-300' }}">USD {{ number_format($invoiced['USD'], 0) }}</p>
            <p class="text-base font-bold {{ $invoiced['TZS'] > 0 ? 'text-blue-900' : 'text-blue-300' }} mt-0.5">TZS {{ number_format($invoiced['TZS'], 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium mb-2">Payments Received</p>
            <p class="text-base font-bold {{ $paid['USD'] > 0 ? 'text-green-800' : 'text-green-300' }}">USD {{ number_format($paid['USD'], 0) }}</p>
            <p class="text-base font-bold {{ $paid['TZS'] > 0 ? 'text-green-800' : 'text-green-300' }} mt-0.5">TZS {{ number_format($paid['TZS'], 0) }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-600 font-medium mb-2">Closing Balance</p>
            <p class="text-base font-bold {{ $closing['USD'] > 0 ? 'text-red-700' : ($closing['USD'] < 0 ? 'text-green-700' : 'text-gray-400') }}">USD {{ number_format($closing['USD'], 0) }}</p>
            <p class="text-base font-bold {{ $closing['TZS'] > 0 ? 'text-red-700' : ($closing['TZS'] < 0 ? 'text-green-700' : 'text-gray-400') }} mt-0.5">TZS {{ number_format($closing['TZS'], 0) }}</p>
        </div>
    </div>

    <!-- Transaction table — dual USD / TZS columns -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800">Transaction History</p>
        </div>
        <table class="w-full text-sm min-w-[860px]">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 w-24" rowspan="2">Date</th>
                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500 w-32" rowspan="2">Reference</th>
                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500" rowspan="2">Description</th>
                    <th colspan="2" class="text-center px-3 py-2 text-xs font-semibold text-blue-700 border-l border-blue-100 bg-blue-50/50">Debit</th>
                    <th colspan="2" class="text-center px-3 py-2 text-xs font-semibold text-green-700 border-l border-green-100 bg-green-50/50">Credit</th>
                    <th colspan="2" class="text-center px-3 py-2 text-xs font-semibold text-gray-700 border-l border-gray-200 bg-gray-100/50">Balance</th>
                </tr>
                <tr class="border-b border-gray-100">
                    <th class="text-right px-3 py-1.5 text-xs font-medium text-blue-500 border-l border-blue-100 bg-blue-50/30 w-24">USD</th>
                    <th class="text-right px-3 py-1.5 text-xs font-medium text-blue-500 bg-blue-50/30 w-28">TZS</th>
                    <th class="text-right px-3 py-1.5 text-xs font-medium text-green-500 border-l border-green-100 bg-green-50/30 w-24">USD</th>
                    <th class="text-right px-3 py-1.5 text-xs font-medium text-green-500 bg-green-50/30 w-28">TZS</th>
                    <th class="text-right px-3 py-1.5 text-xs font-medium text-gray-500 border-l border-gray-200 bg-gray-100/30 w-24">USD</th>
                    <th class="text-right px-4 py-1.5 text-xs font-medium text-gray-500 bg-gray-100/30 w-28">TZS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @if($opening['USD'] > 0 || $opening['TZS'] > 0)
                <tr class="bg-gray-50/70">
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</td>
                    <td class="px-3 py-2.5 text-xs text-gray-500 font-mono">—</td>
                    <td class="px-3 py-2.5 text-xs font-medium text-gray-600 italic">Opening Balance (b/f)</td>
                    {{-- Debit USD --}}
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-300 border-l border-blue-50">—</td>
                    {{-- Debit TZS --}}
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-300">—</td>
                    {{-- Credit USD --}}
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-300 border-l border-green-50">—</td>
                    {{-- Credit TZS --}}
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-300">—</td>
                    {{-- Balance USD --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs font-semibold {{ $opening['USD'] > 0 ? 'text-red-700' : 'text-gray-400' }} border-l border-gray-100">
                        {{ number_format($opening['USD'], 0) }}
                    </td>
                    {{-- Balance TZS --}}
                    <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold {{ $opening['TZS'] > 0 ? 'text-red-700' : 'text-gray-400' }}">
                        {{ number_format($opening['TZS'], 0) }}
                    </td>
                </tr>
                @endif

                @forelse($lines as $line)
                @php $isUsd = $line['currency'] === 'USD'; @endphp
                <tr class="{{ $line['type'] === 'payment' ? 'bg-green-50/20' : '' }} hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-2.5 text-xs text-gray-600">{{ \Carbon\Carbon::parse($line['date'])->format('d M Y') }}</td>
                    <td class="px-3 py-2.5 text-xs font-mono">
                        @if($line['type'] === 'invoice' && $line['id'])
                            <a href="{{ route('admin.invoices.show', $line['id']) }}" class="text-blue-600 hover:underline">{{ $line['reference'] }}</a>
                        @else
                            <span class="text-gray-600">{{ $line['reference'] }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-700">
                        {{ $line['description'] }}
                        @if($line['status'])
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full {{ $statusStyles[$line['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $line['status'])) }}
                            </span>
                        @endif
                    </td>
                    {{-- Debit USD --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs border-l border-blue-50 {{ ($line['debit'] > 0 && $isUsd) ? 'font-semibold text-gray-900' : 'text-gray-200' }}">
                        {{ ($line['debit'] > 0 && $isUsd) ? number_format($line['debit'], 0) : '—' }}
                    </td>
                    {{-- Debit TZS --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ ($line['debit'] > 0 && !$isUsd) ? 'font-semibold text-gray-900' : 'text-gray-200' }}">
                        {{ ($line['debit'] > 0 && !$isUsd) ? number_format($line['debit'], 0) : '—' }}
                    </td>
                    {{-- Credit USD --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs border-l border-green-50 {{ ($line['credit'] > 0 && $isUsd) ? 'font-semibold text-green-700' : 'text-gray-200' }}">
                        {{ ($line['credit'] > 0 && $isUsd) ? number_format($line['credit'], 0) : '—' }}
                    </td>
                    {{-- Credit TZS --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ ($line['credit'] > 0 && !$isUsd) ? 'font-semibold text-green-700' : 'text-gray-200' }}">
                        {{ ($line['credit'] > 0 && !$isUsd) ? number_format($line['credit'], 0) : '—' }}
                    </td>
                    {{-- Balance USD --}}
                    <td class="px-3 py-2.5 text-right font-mono text-xs font-semibold border-l border-gray-100 {{ $line['balance_usd'] > 0 ? 'text-red-700' : ($line['balance_usd'] < 0 ? 'text-green-700' : 'text-gray-400') }}">
                        {{ number_format($line['balance_usd'], 0) }}
                    </td>
                    {{-- Balance TZS --}}
                    <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold {{ $line['balance_tzs'] > 0 ? 'text-red-700' : ($line['balance_tzs'] < 0 ? 'text-green-700' : 'text-gray-400') }}">
                        {{ number_format($line['balance_tzs'], 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400">No transactions in this period</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Totals / Closing Balance</td>
                    {{-- Total Debit USD --}}
                    <td class="px-3 py-3 text-right font-mono text-xs font-bold text-blue-700 border-l border-blue-100">{{ number_format($invoiced['USD'], 0) }}</td>
                    {{-- Total Debit TZS --}}
                    <td class="px-3 py-3 text-right font-mono text-xs font-bold text-blue-700">{{ number_format($invoiced['TZS'], 0) }}</td>
                    {{-- Total Credit USD --}}
                    <td class="px-3 py-3 text-right font-mono text-xs font-bold text-green-700 border-l border-green-100">{{ number_format($paid['USD'], 0) }}</td>
                    {{-- Total Credit TZS --}}
                    <td class="px-3 py-3 text-right font-mono text-xs font-bold text-green-700">{{ number_format($paid['TZS'], 0) }}</td>
                    {{-- Closing Balance USD --}}
                    <td class="px-3 py-3 text-right font-mono text-sm font-bold border-l border-gray-200 {{ $closing['USD'] > 0 ? 'text-red-700' : ($closing['USD'] < 0 ? 'text-green-700' : 'text-gray-500') }}">{{ number_format($closing['USD'], 0) }}</td>
                    {{-- Closing Balance TZS --}}
                    <td class="px-4 py-3 text-right font-mono text-sm font-bold {{ $closing['TZS'] > 0 ? 'text-red-700' : ($closing['TZS'] < 0 ? 'text-green-700' : 'text-gray-500') }}">{{ number_format($closing['TZS'], 0) }}</td>
                </tr>
                <tr class="border-t border-gray-100">
                    <td colspan="3" class="px-4 py-1.5 text-xs text-gray-400 italic">Currency columns: USD | TZS</td>
                    <td colspan="2" class="px-3 py-1.5 text-center text-xs text-blue-400">Debit</td>
                    <td colspan="2" class="px-3 py-1.5 text-center text-xs text-green-400 border-l border-green-50">Credit</td>
                    <td colspan="2" class="px-3 py-1.5 text-center text-xs text-gray-400 border-l border-gray-100">Balance (outstanding)</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</x-admin-layout>
