<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.bank-statements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Bank Statements</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Bank Statement</h1>
        <p class="text-gray-500 mt-1">Enter transactions manually, or upload an Excel / CSV file to import them.</p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- ── Statement header (shared by both modes) ─────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-5">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Statement ID</label>
                <input type="text" name="reference" id="sharedReference" value="{{ old('reference') }}" placeholder="e.g. March 2026 Statement" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                <p class="text-xs text-gray-400 mt-1">e.g. March 2026 Statement</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account <span class="text-red-500">*</span></label>
                <select name="bank_account_id" id="sharedBankAccount" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Select bank account…</option>
                    @foreach($bankAccounts as $ba)
                    <option value="{{ $ba->id }}" @selected(old('bank_account_id') == $ba->id)>{{ $ba->name }} ({{ $ba->bank_name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period From</label>
                <input type="date" name="period_from" id="sharedPeriodFrom" value="{{ old('period_from') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period To</label>
                <input type="date" name="period_to" id="sharedPeriodTo" value="{{ old('period_to') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="sharedNotes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── Mode tabs ─────────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-4" id="modeTabs">
        <button type="button" onclick="switchMode('upload')" id="tabUpload"
            class="mode-tab px-5 py-2 rounded-lg text-sm font-medium border transition-colors bg-red-600 text-white border-red-600">
            Upload File (Excel / CSV)
        </button>
        <button type="button" onclick="switchMode('manual')" id="tabManual"
            class="mode-tab px-5 py-2 rounded-lg text-sm font-medium border transition-colors text-gray-600 border-gray-200 hover:bg-gray-50">
            Enter Manually
        </button>
    </div>

    {{-- ── Upload mode ───────────────────────────────────────────────── --}}
    <div id="modeUpload">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.accounting.bank-statements.preview-import') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                {{-- Hidden copies of statement header --}}
                <input type="hidden" name="bank_account_id" id="uploadBankAccount">
                <input type="hidden" name="reference"       id="uploadReference">
                <input type="hidden" name="period_from"     id="uploadPeriodFrom">
                <input type="hidden" name="period_to"       id="uploadPeriodTo">
                <input type="hidden" name="notes"           id="uploadNotes">

                <label for="importFile"
                    class="flex flex-col items-center justify-center gap-3 border-2 border-dashed border-gray-300 rounded-xl px-6 py-10 cursor-pointer hover:border-red-400 hover:bg-red-50 transition-colors group"
                    id="dropZone">
                    <svg class="w-10 h-10 text-gray-300 group-hover:text-red-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V3m0 0L8 7m4-4l4 4"/>
                    </svg>
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-700 group-hover:text-red-600">Drop your file here, or <span class="text-red-600 underline">browse</span></p>
                        <p class="text-xs text-gray-400 mt-1">Supports Excel (.xlsx, .xls) and CSV (.csv, .txt) — max 10 MB</p>
                    </div>
                    <div id="fileNameDisplay" class="hidden text-sm font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full border border-red-200"></div>
                    <input type="file" name="import_file" id="importFile" accept=".csv,.txt,.xlsx,.xls,.ods" required class="hidden">
                </label>

                <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-xs text-blue-700">
                    <strong>Supported formats:</strong> Your file needs at minimum a <code>date</code> column and a <code>description</code> column.
                    Amounts can be in separate <code>debit</code> / <code>credit</code> columns, or a single <code>amount</code> column with a <code>type</code> column (cr/dr).
                    After upload, you'll see a preview table to review, adjust COA, and remove unwanted rows before anything is saved.
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <a href="{{ route('admin.accounting.bank-statements.template') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M8 12l4 4 4-4M12 4v12"/>
                        </svg>
                        Download Excel Template
                    </a>
                    <span class="text-xs text-gray-400">— fill in your transactions and upload the file above</span>
                </div>

                {{-- Inline error shown when bank account is not selected --}}
                <div id="uploadBankError" class="hidden mt-4 bg-red-50 border border-red-300 rounded-lg px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span>Please select a <strong>Bank Account</strong> in the Statement Details section above before uploading.</span>
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <a href="{{ route('admin.accounting.bank-statements.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit" id="uploadSubmitBtn" class="px-5 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        <svg id="uploadSubmitIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="uploadSubmitSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        <span id="uploadSubmitLabel">Parse & Preview</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Manual mode ───────────────────────────────────────────────── --}}
    <div id="modeManual" class="hidden">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.accounting.bank-statements.store') }}" id="bsForm">
                @csrf
                <input type="hidden" name="bank_account_id" id="manualBankAccount">
                <input type="hidden" name="reference"       id="manualReference">
                <input type="hidden" name="period_from"     id="manualPeriodFrom">
                <input type="hidden" name="period_to"       id="manualPeriodTo">
                <input type="hidden" name="notes"           id="manualNotes">

                {{-- Transactions table --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-700">Transactions</p>
                        <button type="button" id="addRow" class="text-sm text-red-600 hover:text-red-700 font-medium">+ Add Row</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-32">Date <span class="text-red-500">*</span></th>
                                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-44">Account</th>
                                    <th class="text-left px-3 py-2 font-semibold text-gray-600 w-40">Partner</th>
                                    <th class="text-left px-3 py-2 font-semibold text-gray-600">Description <span class="text-red-500">*</span></th>
                                    <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Dr (Tsh)</th>
                                    <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Cr (Tsh)</th>
                                    <th class="px-3 py-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="txBody">
                                @php
                                    $oldTx = old('transactions', [
                                        ['date'=>'','contra_account_id'=>'','partner'=>'','description'=>'','debit'=>'','credit'=>''],
                                        ['date'=>'','contra_account_id'=>'','partner'=>'','description'=>'','debit'=>'','credit'=>''],
                                    ]);
                                @endphp
                                @foreach($oldTx as $i => $tx)
                                <tr class="tx-row border-t border-gray-100">
                                    <td class="px-2 py-1.5">
                                        <input type="date" name="transactions[{{ $i }}][date]" value="{{ $tx['date'] ?? '' }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <div class="account-wrap relative">
                                            <input type="hidden" name="transactions[{{ $i }}][contra_account_id]" class="account-value" value="{{ $tx['contra_account_id'] ?? '' }}">
                                            <input type="text" class="account-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search account…" autocomplete="off">
                                            <button type="button" class="account-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs p-0.5">&times;</button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <div class="partner-wrap relative">
                                            <input type="hidden" name="transactions[{{ $i }}][partner]" class="partner-value" value="{{ $tx['partner'] ?? '' }}">
                                            <span class="partner-badge hidden"></span>
                                            <input type="text" class="partner-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search partner…" autocomplete="off">
                                            <button type="button" class="partner-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs p-0.5">&times;</button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" name="transactions[{{ $i }}][description]" value="{{ $tx['description'] ?? '' }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" name="transactions[{{ $i }}][debit]" value="{{ $tx['debit'] ?? 0 }}" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 debit-input" oninput="updateTotals()">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" name="transactions[{{ $i }}][credit]" value="{{ $tx['credit'] ?? 0 }}" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 credit-input" oninput="updateTotals()">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <button type="button" class="remove-row text-red-400 hover:text-red-600 text-xs" @if($i < 2) disabled @endif>&times;</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <td colspan="4" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Totals</td>
                                    <td class="px-3 py-2 text-right font-bold font-mono" id="totalDebit">0</td>
                                    <td class="px-3 py-2 text-right font-bold font-mono" id="totalCredit">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.accounting.bank-statements.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Statement</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $accountsJson = $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->code.' — '.$a->name, 'code' => $a->code, 'name' => $a->name]);
        $partnersJson = $clients->map(fn($c) => ['value' => 'client:'.$c->id, 'label' => $c->company_name ?? $c->full_name, 'type' => 'client'])
            ->concat($suppliers->map(fn($s) => ['value' => 'supplier:'.$s->id, 'label' => $s->name, 'type' => 'supplier']))->values();
    @endphp

    <script>
    let rowIndex = {{ count($oldTx) }};
    const accountsData = @json($accountsJson);
    const partnersData = @json($partnersJson);

    // ── Shared floating dropdown ──────────────────────────────────────
    const sharedDrop = document.createElement('div');
    sharedDrop.className = 'fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-xl max-h-48 overflow-y-auto hidden text-sm';
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
        sharedDrop.style.width = Math.max(r.width, 220) + 'px';
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
            const cl = list.filter(p => p.type === 'client'), sl = list.filter(p => p.type === 'supplier');
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
            badge.className = `partner-badge absolute left-1.5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full flex items-center justify-center text-xs font-bold pointer-events-none ${type === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}`;
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
        s.addEventListener('input', () => { h.value = ''; c?.classList.add('hidden'); openDrop(s, h, 'account'); });
        s.addEventListener('blur', scheduleDClose);
        c?.addEventListener('click', () => { setAccount(s, h, '', ''); s.focus(); });
        if (h.value) { const a = accountsData.find(x => String(x.value) === String(h.value)); if (a) setAccount(s, h, a.value, a.label); }
    }

    function initPartnerTypeahead(wrap) {
        const s = wrap.querySelector('.partner-search'), h = wrap.querySelector('.partner-value'), c = wrap.querySelector('.partner-clear');
        s.addEventListener('focus', () => openDrop(s, h, 'partner'));
        s.addEventListener('input', () => { h.value = ''; wrap.querySelector('.partner-badge').className = 'partner-badge hidden'; s.classList.remove('pl-6'); c?.classList.add('hidden'); openDrop(s, h, 'partner'); });
        s.addEventListener('blur', scheduleDClose);
        c?.addEventListener('click', () => { setPartner(s, h, '', ''); s.focus(); });
        if (h.value) { const p = partnersData.find(x => x.value === h.value); if (p) setPartner(s, h, p.value, p.label); }
    }

    document.querySelectorAll('.account-wrap').forEach(initAccountTypeahead);
    document.querySelectorAll('.partner-wrap').forEach(initPartnerTypeahead);

    function newRowHTML(idx) {
        return `<tr class="tx-row border-t border-gray-100">
        <td class="px-2 py-1.5"><input type="date" name="transactions[${idx}][date]" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400"></td>
        <td class="px-2 py-1.5"><div class="account-wrap relative"><input type="hidden" name="transactions[${idx}][contra_account_id]" class="account-value" value=""><input type="text" class="account-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search account\u2026" autocomplete="off"><button type="button" class="account-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs p-0.5">&times;</button></div></td>
        <td class="px-2 py-1.5"><div class="partner-wrap relative"><input type="hidden" name="transactions[${idx}][partner]" class="partner-value" value=""><span class="partner-badge hidden"></span><input type="text" class="partner-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-5 truncate" placeholder="Search partner\u2026" autocomplete="off"><button type="button" class="partner-clear hidden absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs p-0.5">&times;</button></div></td>
        <td class="px-2 py-1.5"><input type="text" name="transactions[${idx}][description]" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400"></td>
        <td class="px-2 py-1.5"><input type="number" name="transactions[${idx}][debit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 debit-input" oninput="updateTotals()"></td>
        <td class="px-2 py-1.5"><input type="number" name="transactions[${idx}][credit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 credit-input" oninput="updateTotals()"></td>
        <td class="px-2 py-1.5 text-center"><button type="button" class="remove-row text-red-400 hover:text-red-600 text-xs">&times;</button></td>
        </tr>`;
    }

    function updateTotals() {
        let dr = 0, cr = 0;
        document.querySelectorAll('.debit-input').forEach(i => dr += parseFloat(i.value) || 0);
        document.querySelectorAll('.credit-input').forEach(i => cr += parseFloat(i.value) || 0);
        document.getElementById('totalDebit').textContent  = dr.toLocaleString('en', {minimumFractionDigits: 2});
        document.getElementById('totalCredit').textContent = cr.toLocaleString('en', {minimumFractionDigits: 2});
    }

    document.getElementById('addRow').addEventListener('click', () => {
        const tmp = document.createElement('tbody');
        tmp.innerHTML = newRowHTML(rowIndex);
        const tr = tmp.firstElementChild;
        document.getElementById('txBody').appendChild(tr);
        initAccountTypeahead(tr.querySelector('.account-wrap'));
        initPartnerTypeahead(tr.querySelector('.partner-wrap'));
        tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
        rowIndex++;
    });

    document.getElementById('txBody').addEventListener('click', e => {
        if (e.target.classList.contains('remove-row') && !e.target.disabled) e.target.closest('tr').remove();
    });

    // ── Mode tabs ─────────────────────────────────────────────────────
    function switchMode(mode) {
        const isUpload = mode === 'upload';
        document.getElementById('modeUpload').classList.toggle('hidden', !isUpload);
        document.getElementById('modeManual').classList.toggle('hidden', isUpload);
        document.getElementById('tabUpload').className = `mode-tab px-5 py-2 rounded-lg text-sm font-medium border transition-colors ${isUpload ? 'bg-red-600 text-white border-red-600' : 'text-gray-600 border-gray-200 hover:bg-gray-50'}`;
        document.getElementById('tabManual').className = `mode-tab px-5 py-2 rounded-lg text-sm font-medium border transition-colors ${!isUpload ? 'bg-red-600 text-white border-red-600' : 'text-gray-600 border-gray-200 hover:bg-gray-50'}`;
    }

    // ── Sync shared header fields into hidden form fields on submit ───
    function syncHeader(prefix) {
        document.getElementById(prefix + 'BankAccount').value = document.getElementById('sharedBankAccount').value;
        document.getElementById(prefix + 'Reference').value   = document.getElementById('sharedReference').value;
        document.getElementById(prefix + 'PeriodFrom').value  = document.getElementById('sharedPeriodFrom').value;
        document.getElementById(prefix + 'PeriodTo').value    = document.getElementById('sharedPeriodTo').value;
        document.getElementById(prefix + 'Notes').value       = document.getElementById('sharedNotes').value;
    }

    document.getElementById('uploadForm').addEventListener('submit', e => {
        if (!document.getElementById('sharedBankAccount').value) {
            e.preventDefault();
            // Show inline error near the button
            document.getElementById('uploadBankError').classList.remove('hidden');
            // Scroll to and highlight the bank account select
            const sel = document.getElementById('sharedBankAccount');
            sel.classList.add('ring-2', 'ring-red-500', 'border-red-400');
            sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => sel.focus(), 400);
            return;
        }
        // Clear any previous error state
        document.getElementById('uploadBankError').classList.add('hidden');
        document.getElementById('sharedBankAccount').classList.remove('ring-2', 'ring-red-500', 'border-red-400');
        // Show loading state on button
        const btn = document.getElementById('uploadSubmitBtn');
        btn.disabled = true;
        document.getElementById('uploadSubmitIcon').classList.add('hidden');
        document.getElementById('uploadSubmitSpinner').classList.remove('hidden');
        document.getElementById('uploadSubmitLabel').textContent = 'Processing…';
        syncHeader('upload');
    });

    // Clear bank error highlight when user selects an account
    document.getElementById('sharedBankAccount').addEventListener('change', () => {
        if (document.getElementById('sharedBankAccount').value) {
            document.getElementById('uploadBankError').classList.add('hidden');
            document.getElementById('sharedBankAccount').classList.remove('ring-2', 'ring-red-500', 'border-red-400');
        }
    });

    document.getElementById('bsForm').addEventListener('submit', () => syncHeader('manual'));

    // ── File drop zone ────────────────────────────────────────────────
    const fileInput = document.getElementById('importFile');
    const dropZone  = document.getElementById('dropZone');
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            fileNameDisplay.textContent = fileInput.files[0].name;
            fileNameDisplay.classList.remove('hidden');
        }
    });

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-red-400', 'bg-red-50'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-red-400', 'bg-red-50'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-red-400', 'bg-red-50');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileNameDisplay.textContent = e.dataTransfer.files[0].name;
            fileNameDisplay.classList.remove('hidden');
        }
    });
    </script>
</x-admin-layout>
