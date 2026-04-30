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

    <div x-data="{ rejectOpen: false, payOpen: false, reconcileOpen: false }">
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
            @permission('approve_cash_requests')
            <form method="POST" action="{{ route('admin.accounting.cash-requests.approve', $cashRequest) }}">
                @csrf<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Approve</button>
            </form>
            <button x-on:click="rejectOpen = true"
                    class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Reject</button>
            @endpermission
            @elseif($cashRequest->status === 'approved')
            @permission('pay_cash_requests')
            <button x-on:click="payOpen = true"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Disburse Cash</button>
            @endpermission
            @elseif($cashRequest->status === 'paid')
            {{-- Fully disbursed — expense auto-created; reconcile if not yet done --}}
            @if($cashRequest->expense && !$cashRequest->expense->bank_reconciled_at)
            @permission('approve_cash_requests')
            <button x-on:click="reconcileOpen = true"
                    class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700">Reconcile with Bank</button>
            @endpermission
            @endif
            <a href="{{ route('admin.accounting.cash-requests.retire.form', $cashRequest) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Retire (Enter Actuals)</a>
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
                    @if($cashRequest->expense_date)
                    <dt class="text-gray-500">Expense Date</dt><dd class="text-gray-900">{{ $cashRequest->expense_date->format('d M Y') }}</dd>
                    @endif
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
                    @if($cashRequest->attachment)
                    <dt class="text-gray-500">Attachment</dt>
                    <dd><a href="{{ route('admin.accounting.cash-requests.attachment', $cashRequest) }}" target="_blank" class="text-blue-600 hover:underline text-sm">View / Download</a></dd>
                    @endif
                </dl>
            </div>

            <!-- Items -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-semibold text-gray-800">Cost Items</p>
                    @if($cashRequest->status === 'retired')
                    <span class="text-xs text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Actuals recorded</span>
                    @endif
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Description</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Category</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Estimated</th>
                            @if($cashRequest->status === 'retired')
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Actual (Net)</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Variance</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Receipt</th>
                            @else
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">VAT</th>
                            <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Total</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cashRequest->items as $item)
                        <tr>
                            <td class="px-4 py-2">
                                {{ $item->description }}
                                @if($item->vat_justification)
                                <div class="text-xs text-amber-600 mt-0.5">No VAT: {{ $item->vat_justification }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $item->expenseCategory?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($item->estimated_amount, 2) }}</td>
                            @if($cashRequest->status === 'retired')
                            @php
                                $itemVariance = ($item->actual_amount !== null)
                                    ? round((float)$item->actual_amount - (float)$item->estimated_amount, 2)
                                    : 0;
                            @endphp
                            <td class="px-4 py-2 text-right font-mono text-xs font-semibold">
                                {{ $item->actual_amount !== null ? number_format($item->actual_amount, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-xs font-semibold
                                {{ $itemVariance > 0 ? 'text-red-600' : ($itemVariance < 0 ? 'text-green-600' : 'text-gray-400') }}">
                                {{ $itemVariance != 0 ? ($itemVariance > 0 ? '+' : '') . number_format($itemVariance, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-xs">
                                @if($item->receipt_ref)
                                <span class="text-gray-700 font-mono">{{ $item->receipt_ref }}</span>
                                @endif
                                @if($item->receipt_path)
                                <a href="{{ route('admin.accounting.cash-requests.receipt', [$cashRequest, $item]) }}"
                                   class="text-blue-600 hover:underline ml-1">View</a>
                                @endif
                                @if(!$item->receipt_ref && !$item->receipt_path)
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            @else
                            <td class="px-4 py-2 text-right font-mono text-xs {{ $item->is_zero_rated ? 'text-green-600' : 'text-gray-600' }}">
                                {{ $item->is_zero_rated ? 'Exempt' : number_format($item->vat_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-xs font-semibold">{{ number_format($item->total_amount ?? ($item->estimated_amount + $item->vat_amount), 2) }}</td>
                            @endif
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
                    <div class="flex justify-between"><span class="text-gray-500">Net (ex-VAT)</span><span class="font-semibold">Tsh {{ number_format($cashRequest->amount ?? ($cashRequest->total_amount - $cashRequest->vat_amount), 0) }}</span></div>
                    @if((float)$cashRequest->vat_amount > 0)
                    <div class="flex justify-between"><span class="text-gray-500">VAT</span><span>Tsh {{ number_format($cashRequest->vat_amount, 0) }}</span></div>
                    @elseif($cashRequest->is_zero_rated)
                    <div class="flex justify-between"><span class="text-gray-500">VAT</span><span class="text-green-600 text-xs font-medium">Exempt (zero-rated)</span></div>
                    @endif
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-600 font-medium">Total Requested</span><span class="font-bold text-gray-900">Tsh {{ number_format($cashRequest->total_amount, 0) }}</span></div>
                </div>
            </div>

            @if($cashRequest->expense)
            <div class="bg-green-50 border border-green-200 rounded-xl shadow-sm p-4">
                <p class="text-xs font-semibold text-green-700 mb-2">Linked Expense Record</p>
                <a href="{{ route('admin.accounting.expenses.show', $cashRequest->expense) }}" class="text-sm text-green-800 font-semibold hover:underline">{{ $cashRequest->expense->expense_number }}</a>
                <p class="text-xs text-green-600 mt-0.5">{{ $cashRequest->expense->expense_date?->format('d M Y') }} · Tsh {{ number_format($cashRequest->expense->total_amount, 0) }} · <span class="capitalize">{{ $cashRequest->expense->status }}</span></p>
            </div>
            @endif

            {{-- Retirement summary --}}
            @if($cashRequest->status === 'retired')
            <div class="bg-green-50 border border-green-200 rounded-xl shadow-sm p-4">
                <p class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-3">Retirement Summary</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Estimated Total</span>
                        <span class="font-mono text-gray-700">Tsh {{ number_format($cashRequest->total_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Actual Spent</span>
                        <span class="font-mono font-semibold text-gray-900">Tsh {{ number_format($cashRequest->actual_amount, 0) }}</span>
                    </div>
                    @php
                        $retireVariance = round((float)$cashRequest->actual_amount - (float)$cashRequest->total_amount, 2);
                    @endphp
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-500">Variance</span>
                        <span class="font-mono font-semibold {{ $retireVariance > 0 ? 'text-red-600' : ($retireVariance < 0 ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $retireVariance != 0 ? ($retireVariance > 0 ? '+' : '') . 'Tsh ' . number_format($retireVariance, 0) : 'None' }}
                        </span>
                    </div>
                    @if($retireVariance != 0)
                    <div class="text-xs {{ $retireVariance > 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $retireVariance > 0 ? 'Over-spent — additional bank debit posted' : 'Under-spent — surplus return to bank posted' }}
                    </div>
                    @endif
                    @if($cashRequest->retired_at)
                    <div class="flex justify-between text-xs text-gray-400 border-t pt-2 mt-1">
                        <span>Retired on</span>
                        <span>{{ $cashRequest->retired_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Bank Reconciliation Card --}}
            @if($cashRequest->status === 'paid' && $cashRequest->expense)
            <div class="bg-white border rounded-xl shadow-sm p-4 {{ $cashRequest->expense->bank_reconciled_at ? 'border-violet-200 bg-violet-50' : 'border-gray-200' }}">
                <p class="text-xs font-semibold uppercase tracking-wide mb-2 {{ $cashRequest->expense->bank_reconciled_at ? 'text-violet-700' : 'text-gray-500' }}">Bank Reconciliation</p>
                @if($cashRequest->expense->bank_reconciled_at)
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-violet-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-violet-800">Reconciled</p>
                            <p class="text-xs text-violet-600 mt-0.5">{{ $cashRequest->expense->bank_reconciled_at->format('d M Y H:i') }}</p>
                            @if($reconciledTxn)
                            <p class="text-xs text-gray-600 mt-1">
                                Txn: <span class="font-mono">{{ $reconciledTxn->reference ?: $reconciledTxn->description }}</span><br>
                                {{ $reconciledTxn->transaction_date?->format('d M Y') }} · Tsh {{ number_format($reconciledTxn->amount, 0) }}
                            </p>
                            @if($reconciledTxn->bankStatement)
                            <a href="{{ route('admin.accounting.bank-statements.show', $reconciledTxn->bankStatement) }}"
                               class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Bank Statement →</a>
                            @endif
                            @endif
                        </div>
                    </div>
                    @permission('approve_cash_requests')
                    <form method="POST" action="{{ route('admin.accounting.cash-requests.unreconcile', $cashRequest) }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Remove reconciliation? This will reset the bank transaction status.')"
                                class="text-xs text-violet-600 hover:text-violet-800 underline">Un-reconcile</button>
                    </form>
                    @endpermission
                @else
                    <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Not yet reconciled with bank statement
                    </div>
                    @permission('approve_cash_requests')
                    @if($cashRequest->bank_account_id)
                    <button x-on:click="reconcileOpen = true"
                            class="mt-2 w-full px-3 py-1.5 bg-violet-600 text-white rounded-lg text-xs font-medium hover:bg-violet-700">
                        Match Bank Transaction
                    </button>
                    @else
                    <p class="text-xs text-gray-400 mt-2">No payment account recorded — cannot search bank transactions.</p>
                    @endif
                    @endpermission
                @endif
            </div>
            @endif

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
    <div x-show="rejectOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-on:click.self="rejectOpen = false" x-on:keydown.escape.window="rejectOpen = false">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            <h3 class="font-bold text-gray-900 mb-4">Reject Request</h3>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.reject', $cashRequest) }}">
                @csrf
                <textarea name="reason" rows="3" placeholder="Reason for rejection..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="rejectOpen = false" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-medium">Reject</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Pay / Disburse Modal --}}
    <div x-show="payOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-on:click.self="payOpen = false" x-on:keydown.escape.window="payOpen = false">
        <div class="bg-white rounded-xl p-6 shadow-xl w-full max-w-sm">
            <h3 class="font-bold text-gray-900 mb-1">Disburse Cash</h3>
            <p class="text-sm text-gray-500 mb-4">Pay <strong>Tsh {{ number_format($cashRequest->total_amount, 0) }}</strong> to {{ $cashRequest->requestedBy?->name }}. This will post a journal entry and create an expense record automatically.</p>
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
                    <button type="button" x-on:click="payOpen = false" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium">Disburse</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reconcile Modal --}}
    @if($cashRequest->status === 'paid' && $cashRequest->expense && !$cashRequest->expense->bank_reconciled_at && $cashRequest->bank_account_id)
    <div x-show="reconcileOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto py-8"
         x-on:click.self="reconcileOpen = false" x-on:keydown.escape.window="reconcileOpen = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900">Match Bank Transaction</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Select the debit that corresponds to this disbursement of <strong>Tsh {{ number_format($cashRequest->total_amount, 0) }}</strong></p>
                </div>
                <button x-on:click="reconcileOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.accounting.cash-requests.reconcile', $cashRequest) }}">
                @csrf
                <div class="px-6 py-4">
                    @if($unreconciledTxns->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-6">No unreconciled debit transactions found for <strong>{{ $cashRequest->bankAccount?->name }}</strong>.<br>Import a bank statement first.</p>
                    @else
                    <p class="text-xs text-gray-400 mb-3">Showing {{ $unreconciledTxns->count() }} unreconciled debit transactions from {{ $cashRequest->bankAccount?->name }}</p>
                    <div class="overflow-y-auto max-h-80 rounded-lg border border-gray-200">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 w-8"></th>
                                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500">Date</th>
                                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500">Description / Ref</th>
                                    <th class="text-right px-3 py-2 text-xs font-medium text-gray-500">Amount</th>
                                    <th class="text-center px-3 py-2 text-xs font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($unreconciledTxns as $tx)
                                <tr class="hover:bg-violet-50 cursor-pointer" onclick="this.querySelector('input').checked=true">
                                    <td class="px-3 py-2 text-center">
                                        <input type="radio" name="bank_transaction_id" value="{{ $tx->id }}"
                                               class="accent-violet-600"
                                               {{ abs((float)$tx->amount - (float)$cashRequest->total_amount) < 1 ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">{{ $tx->transaction_date?->format('d M Y') }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        {{ $tx->description }}
                                        @if($tx->reference)<span class="text-gray-400 ml-1 font-mono">{{ $tx->reference }}</span>@endif
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-xs font-semibold
                                        {{ abs((float)$tx->amount - (float)$cashRequest->total_amount) < 1 ? 'text-violet-700' : 'text-gray-800' }}">
                                        {{ number_format($tx->amount, 0) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 capitalize">{{ $tx->status }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" x-on:click="reconcileOpen = false"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    @if($unreconciledTxns->isNotEmpty())
                    <button type="submit"
                            class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700">Confirm Reconciliation</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    </div>
</x-admin-layout>
