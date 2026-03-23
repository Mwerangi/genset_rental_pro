<x-admin-layout>
    @php
        $statusColors = [
            'draft'=>'bg-gray-100 text-gray-600',
            'pending'=>'bg-amber-50 text-amber-700',
            'approved'=>'bg-blue-50 text-blue-700',
            'paid'=>'bg-purple-50 text-purple-700',
            'retired'=>'bg-green-50 text-green-700',
            'rejected'=>'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.cash-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Cash Requests</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $cashRequest->request_number }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$cashRequest->status] ?? 'bg-gray-100' }}">{{ ucfirst($cashRequest->status) }}</span>
                <span class="text-sm text-gray-500">{{ $cashRequest->requestedBy?->name }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            @if($cashRequest->status === 'draft')
            <a href="{{ route('admin.accounting.cash-requests.edit', $cashRequest) }}"
               class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.submit', $cashRequest) }}">
                @csrf<button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600">Submit for Approval</button>
            </form>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.destroy', $cashRequest) }}"
                  onsubmit="return confirm('Delete this draft? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Delete</button>
            </form>
            @elseif($cashRequest->status === 'pending')
            <form method="POST" action="{{ route('admin.accounting.cash-requests.approve', $cashRequest) }}">
                @csrf<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Approve</button>
            </form>
            <button onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Reject</button>
            @elseif($cashRequest->status === 'approved')
            <button onclick="document.getElementById('payModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Disburse Cash</button>
            @elseif($cashRequest->status === 'paid')
            <button onclick="document.getElementById('retireModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Retire / Reconcile</button>
            @elseif($cashRequest->status === 'rejected')
            <form method="POST" action="{{ route('admin.accounting.cash-requests.destroy', $cashRequest) }}"
                  onsubmit="return confirm('Delete this rejected request?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Delete</button>
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
                <p class="text-sm font-semibold text-gray-700 mb-3">Request Details</p>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">Purpose</dt><dd class="text-gray-900 font-medium">{{ $cashRequest->purpose }}</dd>
                    @if($cashRequest->bankAccount)
                    <dt class="text-gray-500">Paid From</dt><dd class="text-gray-900">{{ $cashRequest->bankAccount->name }}</dd>
                    @endif
                    @if($cashRequest->approvedBy)
                    <dt class="text-gray-500">Approved By</dt><dd class="text-gray-900">{{ $cashRequest->approvedBy->name }} — {{ $cashRequest->approved_at?->format('d M Y') }}</dd>
                    @endif
                    @if($cashRequest->paid_at)
                    <dt class="text-gray-500">Disbursed At</dt><dd class="text-gray-900">{{ $cashRequest->paid_at->format('d M Y H:i') }}</dd>
                    @endif
                    @if($cashRequest->retired_at)
                    <dt class="text-gray-500">Retired At</dt><dd class="text-gray-900">{{ $cashRequest->retired_at->format('d M Y H:i') }}</dd>
                    @endif
                    @if($cashRequest->notes)
                    <dt class="text-gray-500">Notes</dt><dd class="text-gray-900 col-span-1">{{ $cashRequest->notes }}</dd>
                    @endif
                    @if($cashRequest->rejection_reason)
                    <dt class="text-red-600 font-medium">Rejection Reason</dt>
                    <dd class="text-red-700 font-medium col-span-1">{{ $cashRequest->rejection_reason }}</dd>
                    @endif
                </dl>
            </div>

            <!-- Items -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="font-semibold text-gray-800">Cost Items</p>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Description</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Category</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Estimated</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Actual</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cashRequest->items as $item)
                        <tr>
                            <td class="px-4 py-2">{{ $item->description }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $item->expenseCategory?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($item->estimated_amount, 0) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $item->actual_amount ? 'text-gray-900' : 'text-gray-300' }}">
                                {{ $item->actual_amount ? number_format($item->actual_amount, 0) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">
                                @if($item->receipt_path)
                                    <a href="{{ route('admin.accounting.cash-requests.receipt', [$cashRequest, $item]) }}"
                                       class="text-blue-600 hover:underline" target="_blank">
                                        {{ $item->receipt_ref ?: 'View file' }}
                                    </a>
                                @else
                                    {{ $item->receipt_ref ?? '—' }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Amounts</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Requested</span><span class="font-semibold">Tsh {{ number_format($cashRequest->total_amount, 0) }}</span></div>
                    @if($cashRequest->actual_amount)
                    <div class="flex justify-between"><span class="text-gray-500">Actual Spent</span><span class="font-semibold">Tsh {{ number_format($cashRequest->actual_amount, 0) }}</span></div>
                    <div class="flex justify-between border-t pt-2 {{ $cashRequest->total_amount > $cashRequest->actual_amount ? 'text-green-700' : 'text-red-700' }}">
                        <span class="font-medium">{{ $cashRequest->total_amount > $cashRequest->actual_amount ? 'Balance to Return' : 'Over-spent' }}</span>
                        <span class="font-bold">Tsh {{ number_format(abs($cashRequest->total_amount - $cashRequest->actual_amount), 0) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($cashRequest->journalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-700 mb-2">Disbursement JE</p>
                <a href="{{ route('admin.accounting.journal-entries.show', $cashRequest->journalEntry) }}" class="text-sm text-blue-600 hover:underline">{{ $cashRequest->journalEntry->entry_number }}</a>
            </div>
            @endif

            @if($cashRequest->retireJournalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-700 mb-2">Retirement JE</p>
                <a href="{{ route('admin.accounting.journal-entries.show', $cashRequest->retireJournalEntry) }}" class="text-sm text-blue-600 hover:underline">{{ $cashRequest->retireJournalEntry->entry_number }}</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            <h3 class="font-bold text-gray-900 mb-4">Reject Request</h3>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.reject', $cashRequest) }}">
                @csrf
                <textarea name="reason" rows="3" placeholder="Reason for rejection..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-medium">Reject</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Pay / Disburse Modal --}}
    <div id="payModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            <h3 class="font-bold text-gray-900 mb-4">Disburse Cash — Tsh {{ number_format($cashRequest->total_amount, 0) }}</h3>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.pay', $cashRequest) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay From Account <span class="text-red-500">*</span></label>
                    <select name="bank_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                        <option value="">Select account</option>
                        @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}">{{ $ba->name }} ({{ number_format($ba->current_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('payModal').classList.add('hidden')" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium">Disburse</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Retire Modal --}}
    @if($cashRequest->status === 'paid')
    <div id="retireModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-4xl my-8">
            <h3 class="font-bold text-gray-900 mb-1">Retire Cash Request</h3>
            <p class="text-xs text-gray-500 mb-4">Confirm actual amounts and verify the expense category for each item — this determines which ledger account will be debited.</p>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.retire', $cashRequest) }}" enctype="multipart/form-data">
                @csrf
                <div class="overflow-x-auto">
                <table class="w-full text-sm mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-2 py-1.5 text-xs font-medium text-gray-500">Item</th>
                            <th class="text-left px-2 py-1.5 text-xs font-medium text-gray-500">Category <span class="text-red-500">*</span></th>
                            <th class="text-right px-2 py-1.5 text-xs font-medium text-gray-500">Est.</th>
                            <th class="text-right px-2 py-1.5 text-xs font-medium text-gray-500">Actual <span class="text-red-500">*</span></th>
                            <th class="text-left px-2 py-1.5 text-xs font-medium text-gray-500">Receipt #</th>
                            <th class="text-left px-2 py-1.5 text-xs font-medium text-gray-500">Upload Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashRequest->items as $i=>$item)
                        <tr class="border-t border-gray-100">
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <td class="px-2 py-1.5 text-xs text-gray-700 max-w-[150px]">{{ $item->description }}</td>
                            <td class="px-2 py-1.5 min-w-[180px]">
                                <select name="items[{{ $i }}][expense_category_id]" class="w-full border border-gray-300 rounded px-2 py-1 text-xs retire-cat-select" required>
                                    <option value="">— Select —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        data-coa="{{ $cat->account ? $cat->account->code . ' - ' . $cat->account->name : '' }}"
                                        {{ $item->expense_category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-blue-600 mt-0.5 retire-coa-hint"></p>
                            </td>
                            <td class="px-2 py-1.5 text-right text-xs text-gray-500">{{ number_format($item->estimated_amount, 0) }}</td>
                            <td class="px-2 py-1.5"><input type="number" name="items[{{ $i }}][actual_amount]" value="{{ $item->actual_amount ?? $item->estimated_amount }}" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1 text-xs text-right" required></td>
                            <td class="px-2 py-1.5"><input type="text" name="items[{{ $i }}][receipt_ref]" value="{{ $item->receipt_ref }}" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="RCP-xxx"></td>
                            <td class="px-2 py-1.5">
                                <input type="file" name="items[{{ $i }}][receipt_file]"
                                       accept=".jpg,.jpeg,.png,.pdf,.heic"
                                       class="text-xs text-gray-600 w-full">
                                @if($item->receipt_path)
                                <p class="text-xs text-green-600 mt-0.5">✓ File already uploaded</p>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('retireModal').classList.add('hidden')" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium">Submit & Post to Ledger</button>
                </div>
            </form>
        </div>
    </div>
    @endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.retire-cat-select').forEach(sel => {
        function updateHint() {
            const opt = sel.options[sel.selectedIndex];
            const hint = sel.closest('td').querySelector('.retire-coa-hint');
            if (hint) hint.textContent = opt?.dataset.coa ? 'Ledger: ' + opt.dataset.coa : '';
        }
        sel.addEventListener('change', updateHint);
        updateHint();
    });
});
</script>
</x-admin-layout>
