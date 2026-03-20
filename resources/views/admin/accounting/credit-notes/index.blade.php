<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Credit Notes</h1>
            <p class="text-sm text-gray-500 mt-0.5">Issue credit notes against client invoices</p>
        </div>
        <a href="{{ route('admin.accounting.credit-notes.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Credit Note
        </a>
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-5">
        @php
            $counts = $creditNotes->getCollection()->groupBy('status');
        @endphp
        @foreach([['draft','Draft','bg-gray-100 text-gray-700'],['issued','Issued','bg-blue-50 text-blue-700'],['applied','Applied','bg-green-50 text-green-700'],['voided','Voided','bg-red-50 text-red-700']] as [$s,$label,$cls])
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $cls }}">{{ $totals[$s] ?? 0 }}</span>
            </div>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $totals[$s.'_amount'] ? 'Tsh '.number_format($totals[$s.'_amount'],0) : '—' }}</p>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search CN #, client, invoice…"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach(['draft','issued','applied','voided'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="month" name="month" value="{{ request('month') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filter</button>
            @if(request()->hasAny(['q','status','month']))
            <a href="{{ route('admin.accounting.credit-notes.index') }}" class="px-4 py-2 text-gray-500 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($creditNotes->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <p class="text-base font-medium">No credit notes found</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">CN #</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Client</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($creditNotes as $cn)
                @php
                    $statusStyle = match($cn->status) {
                        'draft'=>'bg-gray-100 text-gray-600', 'issued'=>'bg-blue-50 text-blue-700',
                        'applied'=>'bg-green-50 text-green-700', 'voided'=>'bg-red-50 text-red-700', default=>''
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $cn->cn_number }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $cn->issue_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $cn->client?->name }}</td>
                    <td class="px-4 py-3">
                        @if($cn->invoice)
                        <span class="font-mono text-xs text-blue-600">{{ $cn->invoice->invoice_number }}</span>
                        @else<span class="text-gray-400">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $cn->reason }}</td>
                    <td class="px-4 py-3 text-right font-semibold font-mono">Tsh {{ number_format($cn->total_amount, 0) }}</td>
                    <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusStyle }}">{{ ucfirst($cn->status) }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.accounting.credit-notes.show', $cn) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $creditNotes->withQueryString()->links() }}</div>
        @endif
    </div>
</x-admin-layout>
