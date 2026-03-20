<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Statement of Accounts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Per-client transaction history with running balance</p>
        </div>
        <a href="{{ route('admin.accounting.reports.aging') }}" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Aging Report</a>
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

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500">Opening Balance</p>
            <p class="text-xl font-bold {{ $openingBalance > 0 ? 'text-red-700' : 'text-gray-900' }} mt-0.5">Tsh {{ number_format($openingBalance, 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Invoiced This Period</p>
            <p class="text-xl font-bold text-blue-900 mt-0.5">Tsh {{ number_format($totalInvoiced, 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Payments Received</p>
            <p class="text-xl font-bold text-green-800 mt-0.5">Tsh {{ number_format($totalPaid, 0) }}</p>
        </div>
        <div class="bg-{{ $closingBalance > 0 ? 'red' : 'gray' }}-50 border border-{{ $closingBalance > 0 ? 'red' : 'gray' }}-100 rounded-xl p-4">
            <p class="text-xs text-{{ $closingBalance > 0 ? 'red' : 'gray' }}-600 font-medium">Closing Balance</p>
            <p class="text-xl font-bold text-{{ $closingBalance > 0 ? 'red' : 'gray' }}-900 mt-0.5">Tsh {{ number_format($closingBalance, 0) }}</p>
        </div>
    </div>

    <!-- Transaction table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800">Transaction History</p>
        </div>

        @if($openingBalance > 0)
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Date</th>
                    <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500 w-36">Reference</th>
                    <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Description</th>
                    <th class="text-right px-3 py-2.5 text-xs font-medium text-blue-600">Debit</th>
                    <th class="text-right px-3 py-2.5 text-xs font-medium text-green-600">Credit</th>
                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-700">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <!-- Opening balance row -->
                <tr class="bg-gray-50/70">
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</td>
                    <td class="px-3 py-2.5 text-xs text-gray-500 font-mono">—</td>
                    <td class="px-3 py-2.5 text-xs font-medium text-gray-600 italic">Opening Balance (b/f)</td>
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-500">—</td>
                    <td class="px-3 py-2.5 text-right text-xs font-mono text-gray-500">—</td>
                    <td class="px-4 py-2.5 text-right font-mono text-sm font-semibold {{ $openingBalance > 0 ? 'text-red-700' : 'text-gray-700' }}">
                        {{ number_format($openingBalance, 0) }}
                    </td>
                </tr>
                @foreach($lines as $line)
                <tr class="{{ $line['type'] === 'payment' ? 'bg-green-50/30' : '' }} hover:bg-gray-50 transition-colors">
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
                            @php
                                $statusStyles = [
                                    'draft'         => 'bg-gray-100 text-gray-600',
                                    'sent'          => 'bg-blue-100 text-blue-700',
                                    'partially_paid'=> 'bg-yellow-100 text-yellow-700',
                                    'paid'          => 'bg-green-100 text-green-700',
                                    'disputed'      => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full {{ $statusStyles[$line['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $line['status'])) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ $line['debit'] > 0 ? 'font-medium text-gray-900' : 'text-gray-300' }}">
                        {{ $line['debit'] > 0 ? number_format($line['debit'], 0) : '—' }}
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ $line['credit'] > 0 ? 'font-medium text-green-700' : 'text-gray-300' }}">
                        {{ $line['credit'] > 0 ? number_format($line['credit'], 0) : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold {{ $line['balance'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($line['balance'], 0) }}
                    </td>
                </tr>
                @endforeach
                @if($lines->isEmpty())
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">No transactions in this period</td>
                </tr>
                @endif
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Closing Balance</td>
                    <td class="px-3 py-3 text-right font-mono text-sm font-semibold text-blue-700">{{ number_format($totalInvoiced, 0) }}</td>
                    <td class="px-3 py-3 text-right font-mono text-sm font-semibold text-green-700">{{ number_format($totalPaid, 0) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-base font-bold {{ $closingBalance > 0 ? 'text-red-700' : 'text-green-700' }}">
                        Tsh {{ number_format($closingBalance, 0) }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Date</th>
                    <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500 w-36">Reference</th>
                    <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500">Description</th>
                    <th class="text-right px-3 py-2.5 text-xs font-medium text-blue-600">Debit</th>
                    <th class="text-right px-3 py-2.5 text-xs font-medium text-green-600">Credit</th>
                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-700">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($lines as $line)
                <tr class="{{ $line['type'] === 'payment' ? 'bg-green-50/30' : '' }} hover:bg-gray-50 transition-colors">
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
                            @php
                                $statusStyles = [
                                    'draft'         => 'bg-gray-100 text-gray-600',
                                    'sent'          => 'bg-blue-100 text-blue-700',
                                    'partially_paid'=> 'bg-yellow-100 text-yellow-700',
                                    'paid'          => 'bg-green-100 text-green-700',
                                    'disputed'      => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full {{ $statusStyles[$line['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $line['status'])) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ $line['debit'] > 0 ? 'font-medium text-gray-900' : 'text-gray-300' }}">
                        {{ $line['debit'] > 0 ? number_format($line['debit'], 0) : '—' }}
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-xs {{ $line['credit'] > 0 ? 'font-medium text-green-700' : 'text-gray-300' }}">
                        {{ $line['credit'] > 0 ? number_format($line['credit'], 0) : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold {{ $line['balance'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($line['balance'], 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">No transactions in this period</td>
                </tr>
                @endforelse
            </tbody>
            @if($lines->isNotEmpty())
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Closing Balance</td>
                    <td class="px-3 py-3 text-right font-mono text-sm font-semibold text-blue-700">{{ number_format($totalInvoiced, 0) }}</td>
                    <td class="px-3 py-3 text-right font-mono text-sm font-semibold text-green-700">{{ number_format($totalPaid, 0) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-base font-bold {{ $closingBalance > 0 ? 'text-red-700' : 'text-green-700' }}">
                        Tsh {{ number_format($closingBalance, 0) }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
        @endif
    </div>
    @endif
</x-admin-layout>
