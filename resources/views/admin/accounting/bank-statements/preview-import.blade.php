<x-admin-layout>
    <div class="mb-5 flex items-start justify-between">
        <div>
            <a href="{{ route('admin.accounting.bank-statements.create') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Create</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Review Import</h1>
            <p class="text-gray-500 mt-1">
                {{ $bankAccounts->find($importData['bank_account_id'])?->name }}
                @if($importData['reference']) &mdash; {{ $importData['reference'] }} @endif
            </p>
        </div>
        <div class="mt-1 text-right">
            <p class="text-xs text-gray-400">Nothing has been saved yet.</p>
            <p class="text-xs text-gray-400">Edit or remove rows below, then click <strong>Save</strong>.</p>
        </div>
    </div>

    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Summary bar --}}
    @php
        $rows    = $importData['rows'];
        $totalIn  = collect($rows)->where('type','credit')->sum('amount');
        $totalOut = collect($rows)->where('type','debit')->sum('amount');
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-5 flex divide-x divide-gray-100 overflow-hidden">
        <div class="flex-1 flex items-center gap-3 px-5 py-3">
            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Rows Found</p>
                <p class="text-base font-bold text-gray-800 leading-tight" id="rowCount">{{ count($rows) }}</p>
            </div>
        </div>
        <div class="flex-1 flex items-center gap-3 px-5 py-3">
            <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Total Credits</p>
                <p class="text-base font-bold text-emerald-600 font-mono leading-tight">{{ number_format($totalIn, 2) }}</p>
            </div>
        </div>
        <div class="flex-1 flex items-center gap-3 px-5 py-3">
            <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Total Debits</p>
                <p class="text-base font-bold text-red-500 font-mono leading-tight">{{ number_format($totalOut, 2) }}</p>
            </div>
        </div>
        <div class="flex-1 flex items-center gap-3 px-5 py-3">
            <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Without COA</p>
                <p class="text-base font-bold text-yellow-600 leading-tight" id="noCOACount">{{ count($rows) }}</p>
            </div>
        </div>
    </div>

    @php
        $accountsJson = $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->code.' — '.$a->name, 'code' => $a->code, 'name' => $a->name]);
        $partnersJson = $clients->map(fn($c) => ['value' => 'client:'.$c->id, 'label' => $c->company_name ?? $c->full_name, 'type' => 'client'])
            ->concat($suppliers->map(fn($s) => ['value' => 'supplier:'.$s->id, 'label' => $s->name, 'type' => 'supplier']))->values();
    @endphp

    <form method="POST" action="{{ route('admin.accounting.bank-statements.confirm-import') }}" id="confirmForm">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-semibold text-gray-700">Parsed Transactions</p>
                <p class="text-xs text-gray-400">You can edit any cell, change the COA, remove rows you don't need, then save.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600 w-28">Date</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">Description</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600 w-28">Reference</th>
                            <th class="text-right px-3 py-2 font-semibold text-gray-600 w-28">Amount</th>
                            <th class="text-center px-3 py-2 font-semibold text-gray-600 w-24">Type</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600 w-52">Contra Account (COA)</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600 w-40">Partner</th>
                            <th class="px-2 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="previewBody">
                        @foreach($rows as $i => $row)
                        <tr class="preview-row border-t border-gray-100 hover:bg-gray-50/50 transition-colors" data-idx="{{ $i }}">
                            <td class="px-2 py-1.5">
                                <input type="date" name="transactions[{{ $i }}][date]" value="{{ $row['date'] }}" required
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="text" name="transactions[{{ $i }}][description]" value="{{ $row['description'] }}" required
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="text" name="transactions[{{ $i }}][reference]" value="{{ $row['reference'] ?? '' }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="number" name="transactions[{{ $i }}][amount]" value="{{ $row['amount'] }}" step="0.01" min="0.01" required
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-red-400">
                            </td>
                            <td class="px-2 py-1.5">
                                <select name="transactions[{{ $i }}][type]"
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 type-select">
                                    <option value="credit" @selected($row['type']==='credit')>Credit (In)</option>
                                    <option value="debit"  @selected($row['type']==='debit')>Debit (Out)</option>
                                </select>
                            </td>
                            <td class="px-2 py-1.5">
                                <div class="account-wrap relative">
                                    <input type="hidden" name="transactions[{{ $i }}][contra_account_id]" class="account-value" value="">
                                    <input type="text" class="account-search w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search account…" autocomplete="off">
                                    <button type="button" class="account-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs">&times;</button>
                                </div>
                            </td>
                            <td class="px-2 py-1.5">
                                <div class="partner-wrap relative">
                                    <input type="hidden" name="transactions[{{ $i }}][partner]" class="partner-value" value="">
                                    <span class="partner-badge hidden"></span>
                                    <input type="text" class="partner-search w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search…" autocomplete="off">
                                    <button type="button" class="partner-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs">&times;</button>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <button type="button" class="remove-preview-row text-red-400 hover:text-red-600 hover:bg-red-50 rounded p-0.5 transition-colors" title="Remove row">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-4 flex justify-between items-center bg-white border border-gray-200 rounded-xl shadow-lg px-5 py-3">
            <div class="text-sm text-gray-500">
                <span id="remainingCount" class="font-semibold text-gray-800">{{ count($rows) }}</span> transaction(s) will be saved
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.accounting.bank-statements.create') }}"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                    ← Start Over
                </a>
                <button type="submit" id="saveBtn"
                    class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save <span id="saveBtnCount">{{ count($rows) }}</span> Transaction(s)
                </button>
            </div>
        </div>
    </form>

    <script>
    const accountsData = @json($accountsJson);
    const partnersData = @json($partnersJson);

    // ── Shared floating dropdown ──────────────────────────────────────
    const sharedDrop = document.createElement('div');
    sharedDrop.className = 'fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-xl max-h-52 overflow-y-auto hidden text-sm';
    document.body.appendChild(sharedDrop);
    let activeDrop = null, closeTimer = null;

    function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function openDrop(searchEl, hiddenEl, type) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        activeDrop = { searchEl, hiddenEl, type };
        renderDrop(searchEl.value);
        const r = searchEl.getBoundingClientRect();
        sharedDrop.style.top   = (r.bottom + 2) + 'px';
        sharedDrop.style.left  = r.left + 'px';
        sharedDrop.style.width = Math.max(r.width, 240) + 'px';
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
        updateCOACount();
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
            badge.className = `partner-badge absolute left-1.5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full flex items-center justify-center text-xs font-bold pointer-events-none ${type==='client' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}`;
            searchEl.classList.add('pl-6');
            clearBtn?.classList.remove('hidden');
        } else {
            badge.className = 'partner-badge hidden';
            searchEl.classList.remove('pl-6');
            clearBtn?.classList.add('hidden');
        }
    }

    function initAccountTypeahead(wrap) {
        const s = wrap.querySelector('.account-search'), h = wrap.querySelector('.account-value'), c = wrap.querySelector('.account-clear');
        s.addEventListener('focus', () => openDrop(s, h, 'account'));
        s.addEventListener('input', () => { h.value = ''; c?.classList.add('hidden'); openDrop(s, h, 'account'); updateCOACount(); });
        s.addEventListener('blur', scheduleDClose);
        c?.addEventListener('click', () => { setAccount(s, h, '', ''); s.focus(); updateCOACount(); });
    }

    function initPartnerTypeahead(wrap) {
        const s = wrap.querySelector('.partner-search'), h = wrap.querySelector('.partner-value'), c = wrap.querySelector('.partner-clear');
        s.addEventListener('focus', () => openDrop(s, h, 'partner'));
        s.addEventListener('input', () => { h.value = ''; wrap.querySelector('.partner-badge').className = 'partner-badge hidden'; s.classList.remove('pl-6'); c?.classList.add('hidden'); openDrop(s, h, 'partner'); });
        s.addEventListener('blur', scheduleDClose);
        c?.addEventListener('click', () => { setPartner(s, h, '', ''); s.focus(); });
    }

    document.querySelectorAll('.account-wrap').forEach(initAccountTypeahead);
    document.querySelectorAll('.partner-wrap').forEach(initPartnerTypeahead);

    // ── Row removal ───────────────────────────────────────────────────
    document.getElementById('previewBody').addEventListener('click', e => {
        const btn = e.target.closest('.remove-preview-row');
        if (!btn) return;
        btn.closest('tr').remove();
        reindexRows();
        updateCOACount();
    });

    function reindexRows() {
        const rows = document.querySelectorAll('#previewBody .preview-row');
        rows.forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/transactions\[\d+\]/, `transactions[${i}]`);
            });
        });
        const count = rows.length;
        document.getElementById('remainingCount').textContent = count;
        document.getElementById('saveBtnCount').textContent   = count;
        document.getElementById('rowCount').textContent       = count;
    }

    // ── COA counter ───────────────────────────────────────────────────
    function updateCOACount() {
        const total   = document.querySelectorAll('#previewBody .preview-row').length;
        const withCOA = document.querySelectorAll('#previewBody .account-value').length;
        let noCOA = 0;
        document.querySelectorAll('#previewBody .account-value').forEach(h => { if (!h.value) noCOA++; });
        document.getElementById('noCOACount').textContent = noCOA;
    }

    updateCOACount();
    </script>
</x-admin-layout>
