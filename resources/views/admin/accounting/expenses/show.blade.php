<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expenses</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $expense->expense_number }}</h1>
            <div class="flex items-center gap-3 mt-1">
                @php $colors = ['draft'=>'bg-gray-100 text-gray-600','approved'=>'bg-blue-50 text-blue-700','posted'=>'bg-green-50 text-green-700']; @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colors[$expense->status] ?? 'bg-gray-100' }}">{{ ucfirst($expense->status) }}</span>
                <span class="text-sm text-gray-500">{{ $expense->expense_date?->format('d M Y') }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            @if($expense->status === 'draft')
            @permission('approve_payments')
            <form method="POST" action="{{ route('admin.accounting.expenses.approve', $expense) }}">
                @csrf<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Approve</button>
            </form>
            @endpermission
            @elseif($expense->status === 'approved')
            @permission('approve_payments')
            <form method="POST" action="{{ route('admin.accounting.expenses.post', $expense) }}">
                @csrf<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Post to Ledger</button>
            </form>
            @endpermission
            @endif
            @if(in_array($expense->status, ['draft','approved']))
            <form method="POST" action="{{ route('admin.accounting.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Delete</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Details -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">Expense Details</p>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <dt class="text-gray-500">Description</dt>
                    <dd class="text-gray-900 font-medium">{{ $expense->description }}</dd>
                    <dt class="text-gray-500">Category</dt>
                    <dd class="text-gray-900">{{ $expense->category?->name ?? '—' }}</dd>
                    <dt class="text-gray-500">Bank / Cash Account</dt>
                    <dd class="text-gray-900">{{ $expense->bankAccount?->name ?? '—' }}</dd>
                    <dt class="text-gray-500">Reference</dt>
                    <dd class="text-gray-900">{{ $expense->reference ?? '—' }}</dd>
                    <dt class="text-gray-500">Source</dt>
                    <dd class="text-gray-900">{{ ucfirst($expense->source_type ?? 'manual') }}</dd>
                    @if($expense->createdBy)
                    <dt class="text-gray-500">Created By</dt>
                    <dd class="text-gray-900">{{ $expense->createdBy->name }}</dd>
                    @endif
                    @if($expense->approvedBy)
                    <dt class="text-gray-500">Approved By</dt>
                    <dd class="text-gray-900">{{ $expense->approvedBy->name }} on {{ $expense->approved_at?->format('d M Y') }}</dd>
                    @endif
                </dl>
            </div>

            @if($expense->journalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">Journal Entry —
                    <a href="{{ route('admin.accounting.journal-entries.show', $expense->journalEntry) }}" class="text-blue-600 hover:underline">
                        {{ $expense->journalEntry->entry_number }}
                    </a>
                </p>
                <table class="w-full text-xs">
                    <thead><tr class="border-b border-gray-100">
                        <th class="text-left py-1 font-medium text-gray-500">Account</th>
                        <th class="text-right py-1 font-medium text-gray-500">Debit</th>
                        <th class="text-right py-1 font-medium text-gray-500">Credit</th>
                    </tr></thead>
                    <tbody>
                    @foreach($expense->journalEntry->lines as $line)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-700">{{ $line->account?->code }} {{ $line->account?->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $line->debit > 0 ? number_format($line->debit, 0) : '—' }}</td>
                        <td class="py-1.5 text-right font-mono">{{ $line->credit > 0 ? number_format($line->credit, 0) : '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Amount Summary -->
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Amount</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">Tsh {{ number_format($expense->amount, 0) }}</span></div>
                    @if($expense->vat_amount > 0)
                    <div class="flex justify-between"><span class="text-gray-500">VAT</span><span>Tsh {{ number_format($expense->vat_amount, 0) }}</span></div>
                    @endif
                    <div class="flex justify-between border-t border-gray-100 pt-2"><span class="font-semibold text-gray-800">Total</span><span class="font-bold text-gray-900 text-base">Tsh {{ number_format($expense->total_amount, 0) }}</span></div>
                </div>
            </div>

            @if($expense->attachment)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Attachment</p>
                <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    View Receipt
                </a>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
