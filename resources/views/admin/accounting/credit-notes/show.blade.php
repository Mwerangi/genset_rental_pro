<x-admin-layout>
    @php
        $statusStyle = match($creditNote->status) {
            'draft'=>'bg-gray-100 text-gray-600', 'issued'=>'bg-blue-50 text-blue-700',
            'applied'=>'bg-green-50 text-green-700', 'voided'=>'bg-red-50 text-red-700', default=>''
        };
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.credit-notes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Credit Notes</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $creditNote->cn_number }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusStyle }}">{{ ucfirst($creditNote->status) }}</span>
                <span class="text-sm text-gray-500">{{ $creditNote->client?->name }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            @if($creditNote->status === 'draft')
            <form method="POST" action="{{ route('admin.accounting.credit-notes.issue', $creditNote) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                        onclick="return confirm('Issue this credit note? A journal entry will be created and posted.')">
                    Issue Credit Note
                </button>
            </form>
            @elseif($creditNote->status === 'issued')
            <form method="POST" action="{{ route('admin.accounting.credit-notes.void', $creditNote) }}">
                @csrf
                <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50"
                        onclick="return confirm('Void this credit note? The journal entry will be reversed.')">
                    Void
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <!-- Details -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">Credit Note Details</p>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <dt class="text-gray-500">Issue Date</dt><dd class="font-medium text-gray-900">{{ $creditNote->issue_date->format('d M Y') }}</dd>
                    <dt class="text-gray-500">Client</dt><dd class="text-gray-900">{{ $creditNote->client?->name }}</dd>
                    @if($creditNote->invoice)
                    <dt class="text-gray-500">Invoice</dt>
                    <dd><span class="font-mono text-sm text-blue-600">{{ $creditNote->invoice->invoice_number }}</span></dd>
                    @endif
                    <dt class="text-gray-500">Reason</dt><dd class="text-gray-900">{{ $creditNote->reason }}</dd>
                </dl>
            </div>

            <!-- Journal Entry Lines -->
            @if($creditNote->journalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-800">Journal Entry</p>
                    <a href="{{ route('admin.accounting.journal-entries.show', $creditNote->journalEntry) }}" class="text-xs text-blue-600 hover:underline">{{ $creditNote->journalEntry->entry_number }} →</a>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Account</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Debit</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($creditNote->journalEntry->lines as $line)
                        <tr>
                            <td class="px-4 py-2 text-gray-700">
                                <span class="font-mono text-xs text-gray-400 mr-2">{{ $line->account->code }}</span>{{ $line->account->name }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-sm">{{ $line->debit > 0 ? number_format($line->debit,2) : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-sm">{{ $line->credit > 0 ? number_format($line->credit,2) : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td class="px-4 py-2 text-xs font-medium text-gray-500">Totals</td>
                            <td class="px-4 py-2 text-right font-bold font-mono text-sm">{{ number_format($creditNote->journalEntry->lines->sum('debit'),2) }}</td>
                            <td class="px-4 py-2 text-right font-bold font-mono text-sm">{{ number_format($creditNote->journalEntry->lines->sum('credit'),2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @elseif($creditNote->status==='draft')
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700">
                No journal entry yet. Issue this credit note to post to the ledger.
            </div>
            @endif
        </div>

        <!-- Side summary -->
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Amounts</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Tsh {{ number_format($creditNote->amount,0) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">VAT</span><span>Tsh {{ number_format($creditNote->vat_amount,0) }}</span></div>
                    <div class="flex justify-between font-bold border-t pt-2"><span>Total Credit</span><span>Tsh {{ number_format($creditNote->total_amount,0) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
