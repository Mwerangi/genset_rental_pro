<x-admin-layout>
    <div class="mb-6 flex items-start justify-between">
        <div>
            <a href="{{ route('admin.accounting.bank-statements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Bank Statements</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">
                {{ $bankStatement->reference ?: 'Bank Statement #'.$bankStatement->id }}
            </h1>
            <p class="text-gray-500 mt-1">
                {{ $bankStatement->bankAccount->name }}
                @if($bankStatement->period_from || $bankStatement->period_to)
                    &mdash; {{ $bankStatement->period_from?->format('d M Y') }} to {{ $bankStatement->period_to?->format('d M Y') }}
                @endif
                &mdash; Added by {{ $bankStatement->createdBy->name }} on {{ $bankStatement->created_at->format('d M Y') }}
            </p>
        </div>
        <div class="flex gap-2 mt-1">
            {{-- CSV Import --}}
            <button type="button" onclick="document.getElementById('csvModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </button>
            {{-- Post All --}}
            @php $pendingWithContra = $transactions->where('status','pending')->whereNotNull('contra_account_id')->count(); @endphp
            @if($pendingWithContra > 0)
            <form id="postAllForm" method="POST" action="{{ route('admin.accounting.bank-statements.post-all', $bankStatement) }}">
                @csrf
                <button type="button" onclick="openPostAllModal({{ $pendingWithContra }})" class="inline-flex items-center gap-2 bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Post All ({{ $pendingWithContra }})
                </button>
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

    {{-- Summary strip --}}
    @php
        $totalIn       = $transactions->where('type','credit')->sum('amount');
        $totalOut      = $transactions->where('type','debit')->sum('amount');
        $pendingCnt    = $transactions->where('status','pending')->count();
        $postedCnt     = $transactions->where('status','posted')->count();
        $reconciledCnt = $transactions->where('status','reconciled')->count();
        $ignoredCnt    = $transactions->where('status','ignored')->count();
        $net           = $totalIn - $totalOut;
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 flex divide-x divide-gray-100 overflow-hidden">
        {{-- Total In --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Total In</p>
                <p class="text-base font-bold text-emerald-600 font-mono leading-tight">{{ number_format($totalIn, 2) }}</p>
            </div>
        </div>
        {{-- Total Out --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Total Out</p>
                <p class="text-base font-bold text-red-500 font-mono leading-tight">{{ number_format($totalOut, 2) }}</p>
            </div>
        </div>
        {{-- Net --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full {{ $net >= 0 ? 'bg-blue-50' : 'bg-orange-50' }} flex items-center justify-center">
                <svg class="w-4 h-4 {{ $net >= 0 ? 'text-blue-600' : 'text-orange-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Net</p>
                <p class="text-base font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-orange-500' }} font-mono leading-tight">{{ number_format($net, 2) }}</p>
            </div>
        </div>
        {{-- Pending --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-yellow-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Pending</p>
                <p class="text-base font-bold text-yellow-600 leading-tight">{{ $pendingCnt }}</p>
            </div>
        </div>
        {{-- Posted --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-green-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Posted</p>
                <p class="text-base font-bold text-green-700 leading-tight">{{ $postedCnt }}</p>
            </div>
        </div>
        {{-- Reconciled --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Reconciled</p>
                <p class="text-base font-bold text-purple-700 leading-tight">{{ $reconciledCnt }}</p>
            </div>
        </div>
        {{-- Ignored --}}
        <div class="flex-1 flex items-center gap-3 px-5 py-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Ignored</p>
                <p class="text-base font-bold text-gray-500 leading-tight">{{ $ignoredCnt }}</p>
            </div>
        </div>
    </div>

    {{-- Transactions table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-24">Date</th>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-24">Ref</th>
                    <th class="text-right px-3 py-2 font-semibold text-gray-600 w-28">Amount</th>
                    <th class="text-center px-3 py-2 font-semibold text-gray-600 w-20">Type</th>
                    <th class="text-center px-3 py-2 font-semibold text-gray-600 w-20">Status</th>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-44">Contra Account</th>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-32">Partner</th>
                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-32">JE</th>
                    <th class="px-3 py-2 w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($transactions as $tx)
                <tr class="hover:bg-gray-50 @if($tx->status === 'ignored') opacity-50 @endif" id="tx-{{ $tx->id }}">
                    <td class="px-3 py-2 text-gray-600 text-xs">{{ $tx->transaction_date->format('d M Y') }}</td>
                    <td class="px-3 py-2 text-gray-900">
                        {{ $tx->description }}
                        @if($tx->status === 'reconciled')
                            <span class="inline-flex items-center gap-1 text-xs text-purple-700 bg-purple-50 border border-purple-200 rounded-full px-2 py-0.5 ml-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Reconciled {{ $tx->reconciled_at?->format('d M Y') }}
                            </span>
                        @endif
                        @if($tx->notes) <span class="text-xs text-gray-400 block">{{ $tx->notes }}</span> @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500 text-xs font-mono">{{ $tx->reference ?: '—' }}</td>
                    <td class="px-3 py-2 text-right font-mono font-semibold {{ $tx->type === 'credit' ? 'text-blue-700' : 'text-red-600' }}">
                        {{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $tx->type_badge }}">
                            {{ $tx->type === 'credit' ? 'In' : 'Out' }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $tx->status_badge }}">
                            {{ ucfirst($tx->status) }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-gray-600">
                        @if($tx->contraAccount)
                            <span class="font-mono text-gray-400">{{ $tx->contraAccount->code }}</span>
                            {{ $tx->contraAccount->name }}
                        @elseif($tx->status === 'pending')
                            <span class="text-gray-300 italic">not set</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2 text-xs text-gray-600">{{ $tx->partner_name ?: '—' }}</td>
                    <td class="px-3 py-2">
                        @if($tx->journalEntry)
                        <a href="{{ route('admin.accounting.journal-entries.show', $tx->journalEntry) }}" class="text-xs text-blue-600 hover:underline font-mono">{{ $tx->journalEntry->entry_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($tx->status === 'posted')
                            <span class="text-xs text-gray-400 italic">Posted</span>
                        @elseif($tx->status === 'reconciled')
                            <div class="flex items-center gap-1 flex-wrap">
                                <span class="text-xs text-purple-600 italic">Reconciled</span>
                                <form method="POST" action="{{ route('admin.accounting.bank-statements.transactions.unreconcile', [$bankStatement, $tx]) }}"
                                      onsubmit="return confirm('Remove reconciliation and reset this transaction to pending?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 border border-red-200 text-red-500 rounded text-xs hover:bg-red-50">
                                        Un-reconcile
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center gap-1 flex-wrap">
                                <button type="button"
                                    onclick="openPostModal({{ $tx->id }}, '{{ addslashes($tx->description) }}', '{{ $tx->type }}', {{ $tx->amount }}, '{{ $tx->contra_account_id }}', '{{ $tx->partner_type ? $tx->partner_type.':'.$tx->partner_id : '' }}')"
                                    class="px-2 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">Post</button>
                                <button type="button"
                                    onclick="openReconcileModal({{ $tx->id }}, '{{ addslashes($tx->description) }}', '{{ $tx->type }}', {{ $tx->amount }})"
                                    class="px-2 py-1 bg-purple-600 text-white rounded text-xs hover:bg-purple-700">Reconcile</button>
                                <form method="POST" action="{{ route('admin.accounting.bank-statements.transactions.ignore', [$bankStatement, $tx]) }}">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 border border-gray-200 text-gray-500 rounded text-xs hover:bg-gray-50">
                                        {{ $tx->status === 'ignored' ? 'Restore' : 'Ignore' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Post modal ─────────────────────────────────────────────── --}}
    @php
        $accountsJson = $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->code.' — '.$a->name, 'code' => $a->code, 'name' => $a->name]);
        $partnersJson = $clients->map(fn($c) => ['value' => 'client:'.$c->id, 'label' => $c->company_name ?? $c->full_name, 'type' => 'client'])
            ->concat($suppliers->map(fn($s) => ['value' => 'supplier:'.$s->id, 'label' => $s->name, 'type' => 'supplier']))->values();
    @endphp

    <div id="postModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Post Transaction</h3>
            <p id="postModalDesc" class="text-sm text-gray-500 mb-4"></p>

            <form id="postForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contra Account <span class="text-red-500">*</span></label>
                    <div class="account-wrap relative">
                        <input type="hidden" name="contra_account_id" id="modalAccountValue" class="account-value">
                        <input type="text" id="modalAccountSearch" class="account-search w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 pr-7" placeholder="Search account…" autocomplete="off">
                        <button type="button" id="modalAccountClear" class="account-clear hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-sm">&times;</button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Partner <span class="text-xs text-gray-400">(optional)</span></label>
                    <div class="partner-wrap relative">
                        <input type="hidden" name="partner" id="modalPartnerValue" class="partner-value">
                        <span id="modalPartnerBadge" class="partner-badge hidden"></span>
                        <input type="text" id="modalPartnerSearch" class="partner-search w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 pr-7" placeholder="Search partner…" autocomplete="off">
                        <button type="button" id="modalPartnerClear" class="partner-clear hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-sm">&times;</button>
                    </div>
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-xs text-gray-400">(appended to JE)</span></label>
                    <input type="text" name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closePostModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Post → Create JE</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CSV Import modal ───────────────────────────────────────── --}}
    <div id="csvModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Import CSV</h3>
            <p class="text-sm text-gray-500 mb-4">Columns needed: <span class="font-mono text-xs">date, description</span> + either (<span class="font-mono text-xs">debit, credit</span>) or (<span class="font-mono text-xs">amount, type</span>)</p>
            <form method="POST" action="{{ route('admin.accounting.bank-statements.import-csv', $bankStatement) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">CSV File <span class="text-red-500">*</span></label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('csvModal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Import</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reconcile modal ───────────────────────────────────────────── --}}
    <div id="reconcileModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start justify-between mb-1">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Reconcile Transaction</h3>
                        <p id="reconcileModalDesc" class="text-sm text-gray-500 mt-0.5"></p>
                    </div>
                    <button type="button" onclick="closeReconcileModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                {{-- Explanation banner --}}
                <div class="mt-3 bg-purple-50 border border-purple-100 rounded-lg px-4 py-3 text-xs text-purple-700">
                    <strong>What is Reconcile?</strong> Use this when a payment was <em>already recorded</em> in the system
                    (e.g. from an invoice or supplier payment) and this bank line confirms the same money moved.
                    Reconciling links the two records — <strong>no new journal entry is created</strong>, preventing double-counting.
                </div>

                {{-- Candidate matches section --}}
                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Matching payments found</p>
                    <div id="reconcileMatchList" class="space-y-2 max-h-56 overflow-y-auto">
                        <div class="flex items-center justify-center py-6 text-sm text-gray-400" id="reconcileLoadingState">
                            <svg class="animate-spin w-4 h-4 mr-2 text-purple-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                            Searching for matches…
                        </div>
                    </div>
                </div>

                {{-- Manual search fallback --}}
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold text-gray-500 mb-2">Not in the list above? Search by invoice number, reference, or client / supplier name:</p>
                    <div class="relative">
                        <input type="text" id="reconcileSearchInput"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm pr-8 focus:outline-none focus:ring-2 focus:ring-purple-400"
                            placeholder="e.g. INV-2026-0042, REF-001, Kilimanjaro…"
                            autocomplete="off">
                        <svg id="reconcileSearchSpinner" class="hidden absolute right-2 top-2.5 w-4 h-4 animate-spin text-purple-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button type="button" onclick="closeReconcileModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="button" id="reconcileConfirmBtn" onclick="submitReconcile()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Reconcile (no new JE)
                </button>
            </div>
            <form id="reconcileForm" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="payment_type" id="reconcilePaymentType">
                <input type="hidden" name="payment_id"   id="reconcilePaymentId">
            </form>
        </div>
    </div>

    {{-- ── Post All confirmation modal ──────────────────────────────── --}}
    <div id="postAllModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Confirm Post All</h3>
                        <p id="postAllModalDesc" class="text-sm text-gray-500 mt-0.5"></p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">
                    Each transaction will create a Journal Entry. This action cannot be undone.
                </p>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button type="button" onclick="closePostAllModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="document.getElementById('postAllForm').submit()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Yes, Post All
                </button>
            </div>
        </div>
    </div>

    <script>
    const accountsData = @json($accountsJson);
    const partnersData = @json($partnersJson);

    // Reuse the same shared dropdown (already defined above on JE pages, but defined inline here)
    const sharedDrop = document.createElement('div');
    sharedDrop.className = 'fixed bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto hidden text-sm';
    sharedDrop.style.zIndex = '9999';
    document.body.appendChild(sharedDrop);
    let activeDrop = null, closeTimer = null;

    function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function openDrop(searchEl, hiddenEl, type) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        activeDrop = { searchEl, hiddenEl, type };
        renderDrop(searchEl.value);
        const r = searchEl.getBoundingClientRect();
        sharedDrop.style.top   = (r.bottom + 4) + 'px';
        sharedDrop.style.left  = r.left + 'px';
        sharedDrop.style.width = Math.max(r.width, 260) + 'px';
        sharedDrop.classList.remove('hidden');
    }

    function renderDrop(q) {
        if (!activeDrop) return;
        q = (q || '').toLowerCase().trim();
        if (activeDrop.type === 'account') {
            const list = q ? accountsData.filter(a => a.label.toLowerCase().includes(q)) : accountsData;
            sharedDrop.innerHTML = list.length
                ? list.map(a => `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 flex gap-2 items-baseline" data-value="${escH(a.value)}" data-label="${escH(a.label)}"><span class="font-mono text-xs text-gray-400 shrink-0">${escH(a.code)}</span><span class="truncate">${escH(a.name)}</span></div>`).join('')
                : '<div class="px-3 py-2 text-gray-400 italic text-xs">No results</div>';
        } else {
            const list = q ? partnersData.filter(p => p.label.toLowerCase().includes(q)) : partnersData;
            const cl = list.filter(p => p.type==='client'), sl = list.filter(p => p.type==='supplier');
            let html = `<div class="px-3 py-2 cursor-pointer hover:bg-gray-50 text-gray-400 italic text-xs border-b border-gray-100" data-value="" data-label="">\u2014 None \u2014</div>`;
            if (cl.length) { html += `<div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase bg-gray-50 sticky top-0">Clients</div>`; cl.forEach(p => { html += `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 truncate" data-value="${escH(p.value)}" data-label="${escH(p.label)}">${escH(p.label)}</div>`; }); }
            if (sl.length) { html += `<div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase bg-gray-50 sticky top-0">Suppliers</div>`; sl.forEach(p => { html += `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 truncate" data-value="${escH(p.value)}" data-label="${escH(p.label)}">${escH(p.label)}</div>`; }); }
            sharedDrop.innerHTML = list.length ? html : '<div class="px-3 py-2 text-gray-400 italic text-xs">No results</div>';
        }
    }

    function closeDrop() { sharedDrop.classList.add('hidden'); activeDrop = null; }
    function scheduleDClose() { closeTimer = setTimeout(closeDrop, 180); }

    sharedDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('[data-value]');
        if (!opt || !activeDrop) return;
        e.preventDefault();
        activeDrop.type === 'account'
            ? setAccount(activeDrop.searchEl, activeDrop.hiddenEl, opt.dataset.value, opt.dataset.label)
            : setPartner(activeDrop.searchEl, activeDrop.hiddenEl, opt.dataset.value, opt.dataset.label);
        closeDrop();
    });

    document.addEventListener('mousedown', e => {
        if (!sharedDrop.contains(e.target) && !e.target.classList.contains('account-search') && !e.target.classList.contains('partner-search')) closeDrop();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrop(); });

    function setAccount(searchEl, hiddenEl, value, label) {
        hiddenEl.value = value;
        searchEl.value = label;
        searchEl.closest('.account-wrap')?.querySelector('.account-clear')?.classList.toggle('hidden', !value);
    }

    function setPartner(searchEl, hiddenEl, value, label) {
        hiddenEl.value = value;
        searchEl.value = label;
        const wrap = searchEl.closest('.partner-wrap');
        const badge = wrap?.querySelector('.partner-badge');
        const clearBtn = wrap?.querySelector('.partner-clear');
        if (value) {
            const type = value.startsWith('client:') ? 'client' : 'supplier';
            badge.textContent = type === 'client' ? 'C' : 'S';
            badge.className = `partner-badge absolute left-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold pointer-events-none ${type==='client' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}`;
            searchEl.classList.add('pl-8');
            clearBtn?.classList.remove('hidden');
        } else {
            badge.className = 'partner-badge hidden';
            searchEl.classList.remove('pl-8');
            clearBtn?.classList.add('hidden');
        }
    }

    // ── Modal account typeahead ───────────────────────────────────────
    const mAS = document.getElementById('modalAccountSearch');
    const mAH = document.getElementById('modalAccountValue');
    const mAC = document.getElementById('modalAccountClear');
    mAS.addEventListener('focus', () => openDrop(mAS, mAH, 'account'));
    mAS.addEventListener('input', () => { mAH.value = ''; mAC.classList.add('hidden'); openDrop(mAS, mAH, 'account'); });
    mAS.addEventListener('blur', scheduleDClose);
    mAC.addEventListener('click', () => { setAccount(mAS, mAH, '', ''); mAS.focus(); });

    const mPS = document.getElementById('modalPartnerSearch');
    const mPH = document.getElementById('modalPartnerValue');
    const mPC = document.getElementById('modalPartnerClear');
    mPS.addEventListener('focus', () => openDrop(mPS, mPH, 'partner'));
    mPS.addEventListener('input', () => {
        mPH.value = '';
        document.getElementById('modalPartnerBadge').className = 'partner-badge hidden';
        mPS.classList.remove('pl-8');
        mPC.classList.add('hidden');
        openDrop(mPS, mPH, 'partner');
    });
    mPS.addEventListener('blur', scheduleDClose);
    mPC.addEventListener('click', () => { setPartner(mPS, mPH, '', ''); mPS.focus(); });

    // ── Open post modal ───────────────────────────────────────────────
    function openPostModal(txId, desc, type, amount, contraAccountId, partnerValue) {
        const amtDir = type === 'credit' ? 'Money IN' : 'Money OUT';
        document.getElementById('postModalDesc').textContent = desc + ' — ' + amtDir + ' ' + Number(amount).toLocaleString('en', {minimumFractionDigits:2});
        document.getElementById('postForm').action = `/admin/accounting/bank-statements/{{ $bankStatement->id }}/transactions/${txId}/post`;
        // Reset fields
        setAccount(mAS, mAH, '', '');
        setPartner(mPS, mPH, '', '');
        document.querySelector('#postForm [name="notes"]').value = '';
        // Restore saved contra account if any
        if (contraAccountId) {
            const a = accountsData.find(x => String(x.value) === String(contraAccountId));
            if (a) setAccount(mAS, mAH, a.value, a.label);
        }
        if (partnerValue) {
            const p = partnersData.find(x => x.value === partnerValue);
            if (p) setPartner(mPS, mPH, p.value, p.label);
        }
        document.getElementById('postModal').classList.remove('hidden');
        setTimeout(() => mAS.focus(), 80);
    }

    function closePostModal() {
        document.getElementById('postModal').classList.add('hidden');
    }

    document.getElementById('postModal').addEventListener('click', e => {
        if (e.target === document.getElementById('postModal')) closePostModal();
    });

    // ── Post All modal ────────────────────────────────────────────────
    function openPostAllModal(count) {
        document.getElementById('postAllModalDesc').textContent =
            count + ' transaction' + (count !== 1 ? 's' : '') + ' will be posted to Journal Entries.';
        document.getElementById('postAllModal').classList.remove('hidden');
    }
    function closePostAllModal() {
        document.getElementById('postAllModal').classList.add('hidden');
    }
    document.getElementById('postAllModal').addEventListener('click', e => {
        if (e.target === document.getElementById('postAllModal')) closePostAllModal();
    });

    // ── Reconcile modal ───────────────────────────────────────────────
    let reconcileCurrentTxId = null;
    let selectedPaymentType  = null;
    let selectedPaymentId    = null;
    let reconcileSearchTimer = null;

    function openReconcileModal(txId, desc, type, amount) {
        reconcileCurrentTxId = txId;
        selectedPaymentType  = null;
        selectedPaymentId    = null;

        const amtDir = type === 'credit' ? 'Money IN' : 'Money OUT';
        document.getElementById('reconcileModalDesc').textContent =
            desc + ' — ' + amtDir + ' ' + Number(amount).toLocaleString('en', {minimumFractionDigits: 2});

        document.getElementById('reconcileSearchInput').value = '';
        showReconcileLoading();
        document.getElementById('reconcileModal').classList.remove('hidden');

        // Auto-fetch date+amount matches immediately
        fetchReconcileMatches(txId, '');
    }

    function showReconcileLoading() {
        document.getElementById('reconcileMatchList').innerHTML =
            '<div class="flex items-center justify-center py-6 text-sm text-gray-400">' +
            '<svg class="animate-spin w-4 h-4 mr-2 text-purple-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>' +
            'Searching for matches…</div>';
    }

    function fetchReconcileMatches(txId, q) {
        if (q) document.getElementById('reconcileSearchSpinner').classList.remove('hidden');
        const url = `/admin/accounting/bank-statements/{{ $bankStatement->id }}/transactions/${txId}/suggest-matches` +
                    (q ? `?q=${encodeURIComponent(q)}` : '');
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { renderReconcileMatches(data.matches); })
            .catch(() => {
                document.getElementById('reconcileMatchList').innerHTML =
                    '<p class="text-xs text-red-500 py-3 px-2">Failed to load. Please try again.</p>';
            })
            .finally(() => document.getElementById('reconcileSearchSpinner').classList.add('hidden'));
    }

    // Debounced search input
    document.getElementById('reconcileSearchInput').addEventListener('input', function () {
        clearTimeout(reconcileSearchTimer);
        const q = this.value.trim();
        if (q.length > 0 && q.length < 2) return; // wait for at least 2 chars
        selectedPaymentType = null;
        selectedPaymentId   = null;
        if (q === '') {
            showReconcileLoading();
            reconcileSearchTimer = setTimeout(() => fetchReconcileMatches(reconcileCurrentTxId, ''), 300);
        } else {
            reconcileSearchTimer = setTimeout(() => fetchReconcileMatches(reconcileCurrentTxId, q), 400);
        }
    });

    function renderReconcileMatches(matches) {
        const list = document.getElementById('reconcileMatchList');
        if (!matches || !matches.length) {
            list.innerHTML = '<p class="text-xs text-gray-400 italic py-3 px-2">No payments found. Try searching by invoice number, reference, or client/supplier name in the box below.</p>';
            return;
        }

        // Group by type for clarity
        const inv = matches.filter(m => m.type === 'invoice_payment');
        const sup = matches.filter(m => m.type === 'supplier_payment');
        let html = '';

        if (inv.length) {
            html += `<p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide px-1 mb-1">Invoice Payments</p>`;
            html += inv.map(m => matchCard(m)).join('');
        }
        if (sup.length) {
            if (inv.length) html += `<p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide px-1 mt-3 mb-1">Supplier Payments</p>`;
            html += sup.map(m => matchCard(m)).join('');
        }
        list.innerHTML = html;
    }

    function matchCard(m) {
        const label = m.type === 'invoice_payment' ? 'Invoice' : 'Payment';
        const ref   = m.invoice_number ? `${label} ${escH(m.invoice_number)} · Ref: ${escH(m.reference)}` : `Ref: ${escH(m.reference)}`;
        return `<button type="button"
            onclick="selectReconcilePayment('${m.type}', ${m.id}, this)"
            data-type="${m.type}" data-id="${m.id}"
            class="reconcile-match-btn w-full text-left border border-gray-200 rounded-lg px-4 py-3 mb-1.5 hover:border-purple-400 hover:bg-purple-50 transition-colors">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${escH(m.description)}</p>
                    <p class="text-xs text-gray-500 mt-0.5">${m.date} &nbsp;·&nbsp; ${escH(m.method)} &nbsp;·&nbsp; ${ref}</p>
                </div>
                <span class="text-sm font-bold font-mono text-gray-800 shrink-0 ml-4">${Number(m.amount).toLocaleString('en', {minimumFractionDigits:2})}</span>
            </div>
        </button>`;
    }

    function selectReconcilePayment(type, id, btnEl) {
        document.querySelectorAll('.reconcile-match-btn').forEach(b =>
            b.classList.remove('border-purple-500', 'ring-2', 'ring-purple-300', '!bg-purple-50'));
        btnEl.classList.add('border-purple-500', 'ring-2', 'ring-purple-300', '!bg-purple-50');
        selectedPaymentType = type;
        selectedPaymentId   = id;
    }

    function closeReconcileModal() {
        document.getElementById('reconcileModal').classList.add('hidden');
        reconcileCurrentTxId = null;
        selectedPaymentType  = null;
        selectedPaymentId    = null;
    }
    document.getElementById('reconcileModal').addEventListener('click', e => {
        if (e.target === document.getElementById('reconcileModal')) closeReconcileModal();
    });

    function submitReconcile() {
        if (!selectedPaymentType || !selectedPaymentId) {
            alert('Please select a payment from the list above to reconcile against.\n\nCan\'t find it? Use the search box to search by invoice number, reference, or client/supplier name.');
            return;
        }
        if (!reconcileCurrentTxId) return;

        document.getElementById('reconcileForm').action =
            `/admin/accounting/bank-statements/{{ $bankStatement->id }}/transactions/${reconcileCurrentTxId}/reconcile`;
        document.getElementById('reconcilePaymentType').value = selectedPaymentType;
        document.getElementById('reconcilePaymentId').value   = selectedPaymentId;
        document.getElementById('reconcileForm').submit();
    }
    </script>
</x-admin-layout>
