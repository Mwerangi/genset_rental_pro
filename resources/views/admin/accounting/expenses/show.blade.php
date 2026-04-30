<x-admin-layout>
    <div x-data="{ postOpen: false }">
    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expenses</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $expense->expense_number }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                @php $colors = ['draft'=>'bg-gray-100 text-gray-600','approved'=>'bg-blue-50 text-blue-700','posted'=>'bg-green-50 text-green-700']; @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $colors[$expense->status] ?? 'bg-gray-100' }}">
                    {{ ucfirst($expense->status) }}
                </span>
                @if($expense->is_zero_rated)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">VAT Exempt (0%)</span>
                @endif
                @if($expense->bank_reconciled_at)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bank Verified
                </span>
                @endif
                <span class="text-sm text-gray-400">{{ $expense->expense_date?->format('d M Y') }}</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 flex-shrink-0">
            @if($expense->status === 'draft')
            @permission('create_expenses')
            <a href="{{ route('admin.accounting.expenses.edit', $expense) }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
            @endpermission
            @permission('approve_expenses')
            <div x-data="{ approveOpen: false }">
                <button type="button" x-on:click="approveOpen = true"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Approve
                </button>
                {{-- Approve Confirmation Modal --}}
                <div x-show="approveOpen" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     x-on:keydown.escape.window="approveOpen = false">
                    <div class="absolute inset-0 bg-gray-900/50" x-on:click="approveOpen = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Approve Expense?</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Please review the details before approving.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl border border-gray-100 divide-y divide-gray-100 text-sm mb-5">
                            <div class="flex justify-between px-4 py-2.5">
                                <span class="text-gray-500">Expense #</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $expense->expense_number }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-2.5">
                                <span class="text-gray-500">Description</span>
                                <span class="text-gray-800 font-medium text-right max-w-[60%] truncate">{{ $expense->description }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-2.5">
                                <span class="text-gray-500">Category</span>
                                <span class="text-gray-800">{{ $expense->category?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-2.5">
                                <span class="text-gray-500">Amount (excl. VAT)</span>
                                <span class="font-mono text-gray-800">Tsh {{ number_format($expense->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-2.5">
                                <span class="text-gray-500">VAT</span>
                                @if($expense->is_zero_rated)
                                    <span class="text-green-700 text-xs font-semibold">Zero-rated (0%)</span>
                                @else
                                    <span class="font-mono text-gray-800">Tsh {{ number_format($expense->vat_amount, 2) }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between px-4 py-2.5 bg-white rounded-b-xl">
                                <span class="font-bold text-gray-800">Total</span>
                                <span class="font-bold font-mono text-gray-900">Tsh {{ number_format($expense->total_amount, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" x-on:click="approveOpen = false"
                                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                                Cancel
                            </button>
                            <form method="POST" action="{{ route('admin.accounting.expenses.approve', $expense) }}">
                                @csrf
                                <button type="submit"
                                        class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                                    Confirm Approval
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endpermission
            @elseif($expense->status === 'approved')
            @permission('approve_expenses')
            <form method="POST" action="{{ route('admin.accounting.expenses.reject', $expense) }}"
                  onsubmit="return confirm('Reject and return to Draft?')">
                @csrf<button type="submit" class="px-4 py-2 border border-amber-300 text-amber-700 rounded-lg text-sm font-medium hover:bg-amber-50">Reject (→ Draft)</button>
            </form>
            <button type="button" x-on:click="postOpen = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                Post to Ledger
            </button>
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

        {{-- ── LEFT COLUMN (2/3) ──────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Core Details --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Expense Details</p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Description</dt>
                        <dd class="text-gray-900 font-medium">{{ $expense->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Category</dt>
                        <dd class="text-gray-900">
                            {{ $expense->category?->name ?? '—' }}
                            @if($expense->category?->account)
                            <span class="ml-1 text-xs text-gray-400">({{ $expense->category->account->code }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Bank / Cash Account</dt>
                        <dd class="text-gray-900">{{ $expense->bankAccount?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Expense Date</dt>
                        <dd class="text-gray-900">{{ $expense->expense_date?->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Reference</dt>
                        <dd class="text-gray-900 font-mono text-xs">{{ $expense->reference ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Source</dt>
                        <dd class="text-gray-900">{{ $expense->source_label }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Amount Breakdown --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Amount Breakdown</p>
                <div class="space-y-2 text-sm max-w-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Amount (excl. VAT)</span>
                        <span class="font-semibold font-mono">Tsh {{ number_format($expense->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">VAT</span>
                        @if($expense->is_zero_rated)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">VAT Exempt (0%)</span>
                        @elseif($expense->vat_amount > 0)
                            <span class="font-mono">Tsh {{ number_format($expense->vat_amount, 2) }}</span>
                        @else
                            <span class="text-gray-400">— (not applicable)</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-100 pt-2 mt-2">
                        <span class="font-bold text-gray-800">Total</span>
                        <span class="font-bold text-gray-900 text-base font-mono">Tsh {{ number_format($expense->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Journal Entry --}}
            @if($expense->journalEntry)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    Journal Entry —
                    <a href="{{ route('admin.accounting.journal-entries.show', $expense->journalEntry) }}" class="text-blue-600 hover:underline normal-case font-medium">
                        {{ $expense->journalEntry->entry_number }}
                    </a>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs {{ $expense->journalEntry->status === 'posted' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ ucfirst($expense->journalEntry->status) }}
                    </span>
                </p>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-1.5 font-semibold text-gray-500">Account</th>
                            <th class="text-right py-1.5 font-semibold text-gray-500 w-28">Debit (Tsh)</th>
                            <th class="text-right py-1.5 font-semibold text-gray-500 w-28">Credit (Tsh)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($expense->journalEntry->lines as $line)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-700">
                            <span class="font-mono text-gray-500">{{ $line->account?->code }}</span>
                            <span class="ml-1">{{ $line->account?->name }}</span>
                            @if($line->description)
                            <span class="block text-gray-400 text-xs mt-0.5">{{ $line->description }}</span>
                            @endif
                        </td>
                        <td class="py-1.5 text-right font-mono {{ $line->debit > 0 ? 'text-gray-900 font-semibold' : 'text-gray-300' }}">
                            {{ $line->debit > 0 ? number_format($line->debit, 0) : '—' }}
                        </td>
                        <td class="py-1.5 text-right font-mono {{ $line->credit > 0 ? 'text-gray-900 font-semibold' : 'text-gray-300' }}">
                            {{ $line->credit > 0 ? number_format($line->credit, 0) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Bank Reconciliation --}}
            @if($expense->bankTransaction)
            @php $bt = $expense->bankTransaction; @endphp
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide mb-4 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bank Statement Reconciliation
                </p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Statement</dt>
                        <dd>
                            <a href="{{ route('admin.accounting.bank-statements.show', $bt->bankStatement) }}" class="text-blue-600 hover:underline text-xs font-medium">
                                {{ $bt->bankStatement->reference ?: 'Statement #'.$bt->bankStatement->id }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Transaction Date</dt>
                        <dd class="text-gray-900 font-medium">{{ $bt->transaction_date->format('d M Y') }}</dd>
                    </div>
                    @if($bt->reference)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Bank Reference</dt>
                        <dd class="font-mono text-xs text-gray-900">{{ $bt->reference }}</dd>
                    </div>
                    @endif
                    @if($bt->description)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Transaction Description</dt>
                        <dd class="text-gray-700 text-xs">{{ $bt->description }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Confirmed At</dt>
                        <dd class="text-emerald-700 font-medium text-xs">{{ $expense->bank_reconciled_at?->format('d M Y H:i') }}</dd>
                    </div>
                    @if($expense->bankReconciledBy)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Confirmed By</dt>
                        <dd class="text-gray-900 text-xs">{{ $expense->bankReconciledBy->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @elseif($expense->status === 'posted')
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <div>
                    <p class="text-sm font-semibold text-amber-700">Not yet reconciled</p>
                    <p class="text-xs text-amber-600 mt-0.5">Open the bank statement for <strong>{{ $expense->bankAccount?->name }}</strong> and match the debit line to confirm this payment.</p>
                </div>
            </div>
            @endif

        </div>

        {{-- ── RIGHT COLUMN (1/3) ─────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Attachment --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Receipt / Attachment</p>
                @if($expense->attachment)
                @php
                    $ext  = strtolower(pathinfo($expense->attachment, PATHINFO_EXTENSION));
                    $url  = asset('storage/' . $expense->attachment);
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                @endphp
                @if($isImage)
                <a href="{{ $url }}" target="_blank">
                    <img src="{{ $url }}" alt="Receipt" class="w-full rounded-lg border border-gray-200 object-cover max-h-56 hover:opacity-90 transition">
                </a>
                @endif
                <a href="{{ $url }}" target="_blank"
                   class="mt-3 inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    {{ $isImage ? 'Open full size' : 'Download / View File' }}
                    <span class="text-xs text-gray-400 uppercase">.{{ $ext }}</span>
                </a>
                @else
                <p class="text-sm text-gray-400 italic">No attachment uploaded.</p>
                @endif
            </div>

            {{-- Audit Trail --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Audit Trail</p>
                <ol class="relative border-l border-gray-200 ml-2 space-y-4">

                    {{-- Created --}}
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-gray-300 border-2 border-white"></div>
                        <p class="text-xs font-semibold text-gray-700">Created</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $expense->created_at?->format('d M Y H:i') }}
                            @if($expense->createdBy)
                            — <span class="font-medium text-gray-700">{{ $expense->createdBy->name }}</span>
                            @endif
                        </p>
                    </li>

                    {{-- Approved --}}
                    @if($expense->approvedBy || $expense->approved_at)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-blue-400 border-2 border-white"></div>
                        <p class="text-xs font-semibold text-blue-700">Approved</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $expense->approved_at?->format('d M Y H:i') }}
                            @if($expense->approvedBy)
                            — <span class="font-medium text-gray-700">{{ $expense->approvedBy->name }}</span>
                            @endif
                        </p>
                    </li>
                    @endif

                    {{-- Posted --}}
                    @if($expense->journalEntry)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-green-400 border-2 border-white"></div>
                        <p class="text-xs font-semibold text-green-700">Posted to Ledger</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $expense->journalEntry->created_at?->format('d M Y H:i') }}
                            <a href="{{ route('admin.accounting.journal-entries.show', $expense->journalEntry) }}" class="ml-1 text-blue-600 hover:underline font-medium">
                                {{ $expense->journalEntry->entry_number }}
                            </a>
                        </p>
                    </li>
                    @endif

                    {{-- Bank Reconciled --}}
                    @if($expense->bank_reconciled_at)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white"></div>
                        <p class="text-xs font-semibold text-emerald-700">Bank Verified</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $expense->bank_reconciled_at->format('d M Y H:i') }}
                            @if($expense->bankReconciledBy)
                            — <span class="font-medium text-gray-700">{{ $expense->bankReconciledBy->name }}</span>
                            @endif
                        </p>
                        @if($expense->bankTransaction?->reference)
                        <p class="text-xs font-mono text-gray-500 mt-0.5">Ref: {{ $expense->bankTransaction->reference }}</p>
                        @endif
                    </li>
                    @endif

                    {{-- Pending indicator for draft --}}
                    @if($expense->status === 'draft')
                    <li class="ml-4 opacity-40">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-gray-200 border-2 border-white"></div>
                        <p class="text-xs text-gray-400 italic">Awaiting approval…</p>
                    </li>
                    @elseif($expense->status === 'approved')
                    <li class="ml-4 opacity-40">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-gray-200 border-2 border-white"></div>
                        <p class="text-xs text-gray-400 italic">Awaiting post to ledger…</p>
                    </li>
                    @endif

                </ol>
            </div>

            {{-- Metadata --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">System Info</p>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-400">Expense #</dt>
                        <dd class="font-mono font-semibold text-gray-700">{{ $expense->expense_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-400">Internal ID</dt>
                        <dd class="font-mono text-gray-500">{{ $expense->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-400">Source Type</dt>
                        <dd class="text-gray-700">{{ $expense->source_label }}</dd>
                    </div>
                    @if($expense->source_id)
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-400">Source</dt>
                        <dd class="text-gray-700">
                            @if($expense->source_type === 'cash_request')
                                @php $cr = \App\Models\CashRequest::find($expense->source_id); @endphp
                                @if($cr)
                                <a href="{{ route('admin.accounting.cash-requests.show', $cr) }}" class="text-blue-600 hover:underline font-mono text-xs">{{ $cr->request_number }}</a>
                                @else
                                <span class="font-mono text-gray-500">{{ $expense->source_id }}</span>
                                @endif
                            @else
                            <span class="font-mono text-gray-500">{{ $expense->source_id }}</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-400">Last Updated</dt>
                        <dd class="text-gray-500">{{ $expense->updated_at?->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

        </div>
    </div>

    {{-- ── Post to Ledger Confirmation Modal ──────────────────────────── --}}
    <div x-show="postOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-on:keydown.escape.window="postOpen = false">
        <div class="absolute inset-0 bg-gray-900/50" x-on:click="postOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Post to Ledger?</h3>
                    <p class="text-xs text-gray-500 mt-0.5">This will create a journal entry. This action cannot be undone.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-100 divide-y divide-gray-100 text-sm mb-5">
                <div class="flex justify-between px-4 py-2.5">
                    <span class="text-gray-500">Expense #</span>
                    <span class="font-mono font-semibold text-gray-800">{{ $expense->expense_number }}</span>
                </div>
                <div class="flex justify-between px-4 py-2.5">
                    <span class="text-gray-500">Description</span>
                    <span class="text-gray-800 font-medium text-right max-w-[60%] truncate">{{ $expense->description }}</span>
                </div>
                <div class="flex justify-between px-4 py-2.5">
                    <span class="text-gray-500">Pay From</span>
                    <span class="text-gray-800">{{ $expense->bankAccount?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between px-4 py-2.5 bg-white rounded-b-xl">
                    <span class="font-bold text-gray-800">Total to Post</span>
                    <span class="font-bold font-mono text-gray-900">Tsh {{ number_format($expense->total_amount, 2) }}</span>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="postOpen = false"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.accounting.expenses.post', $expense) }}">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">
                        Confirm &amp; Post
                    </button>
                </form>
            </div>
        </div>
    </div>

    </div>{{-- end x-data wrapper --}}
</x-admin-layout>
