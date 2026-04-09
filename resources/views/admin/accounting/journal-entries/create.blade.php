<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.journal-entries.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Journal Entries</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Manual Journal Entry</h1>
        <p class="text-gray-500 mt-1">Recorded by <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span> &mdash; debits must equal credits</p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.journal-entries.store') }}" id="jeForm">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entry Date <span class="text-red-500">*</span></label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference', $suggestedRef) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono tracking-wide focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-400 mt-1">Auto-generated &mdash; you may edit if needed</p>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Lines -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-700">Entry Lines</p>
                    <button type="button" id="addLine" class="text-sm text-red-600 hover:text-red-700 font-medium">+ Add Line</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600 w-56">Account <span class="text-red-500">*</span></th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600 w-52">Partner</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Description</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Dr (Tsh)</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-32">Cr (Tsh)</th>
                                <th class="px-3 py-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                            @php
                                $oldLines = old('lines', [
                                    ['account_id'=>'','partner'=>'','description'=>'','debit'=>'','credit'=>''],
                                    ['account_id'=>'','partner'=>'','description'=>'','debit'=>'','credit'=>''],
                                ]);
                            @endphp
                            @foreach($oldLines as $i => $line)
                            <tr class="line-row border-t border-gray-100">
                                <td class="px-3 py-2">
                                    <div class="account-wrap relative">
                                        <input type="hidden" name="lines[{{ $i }}][account_id]" class="account-value" value="{{ old('lines.'.$i.'.account_id') }}">
                                        <input type="text" class="account-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-6 truncate" placeholder="Search account&hellip;" autocomplete="off">
                                        <button type="button" class="account-clear hidden absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs leading-none p-0.5">&times;</button>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="partner-wrap relative">
                                        <input type="hidden" name="lines[{{ $i }}][partner]" class="partner-value" value="{{ old('lines.'.$i.'.partner') }}">
                                        <span class="partner-badge hidden"></span>
                                        <input type="text" class="partner-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-6 truncate" placeholder="Search partner…" autocomplete="off">
                                        <button type="button" class="partner-clear hidden absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs leading-none p-0.5">×</button>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="lines[{{ $i }}][description]" value="{{ old("lines.{$i}.description") }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lines[{{ $i }}][debit]" value="{{ old("lines.{$i}.debit", 0) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 debit-input" oninput="updateTotals()">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lines[{{ $i }}][credit]" value="{{ old("lines.{$i}.credit", 0) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-red-400 credit-input" oninput="updateTotals()">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" class="remove-line text-red-400 hover:text-red-600 text-xs" @if($i < 2) disabled @endif>✕</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Totals</td>
                                <td class="px-3 py-2 text-right font-bold font-mono" id="totalDebit">0</td>
                                <td class="px-3 py-2 text-right font-bold font-mono" id="totalCredit">0</td>
                                <td></td>
                            </tr>
                            <tr id="balanceRow" class="hidden">
                                <td colspan="6" class="px-3 py-2 text-center text-xs font-medium text-red-600">⚠ Entry is not balanced</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.accounting.journal-entries.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Draft</button>
            </div>
        </form>
    </div>

    @php
        $partnersJson = $clients->map(fn($c) => ['value' => 'client:'.$c->id, 'label' => $c->company_name ?? $c->full_name, 'type' => 'client'])
            ->concat($suppliers->map(fn($s) => ['value' => 'supplier:'.$s->id, 'label' => $s->name, 'type' => 'supplier']))
            ->values();
        $accountsJson = $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->code.' — '.$a->name, 'code' => $a->code, 'name' => $a->name]);
    @endphp
    <script>
    let lineIndex = {{ count($oldLines) }};

    // ── Data ─────────────────────────────────────────────────────────────────────
    const accountsData = @json($accountsJson);
    const partnersData = @json($partnersJson);

    // ── Single shared floating dropdown ──────────────────────────────────────────
    const sharedDrop = document.createElement('div');
    sharedDrop.className = 'fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto hidden text-sm';
    document.body.appendChild(sharedDrop);
    let activeDrop = null;
    let closeTimer = null;

    function escH(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function openDrop(searchEl, hiddenEl, type) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        activeDrop = { searchEl, hiddenEl, type };
        renderDrop(searchEl.value);
        const r = searchEl.getBoundingClientRect();
        sharedDrop.style.top   = (r.bottom + 4) + 'px';
        sharedDrop.style.left  = r.left + 'px';
        sharedDrop.style.width = Math.max(r.width, 240) + 'px';
        sharedDrop.classList.remove('hidden');
    }

    function renderDrop(q) {
        if (!activeDrop) return;
        q = (q || '').toLowerCase().trim();
        activeDrop.type === 'account' ? renderAccountDrop(q) : renderPartnerDrop(q);
    }

    function renderAccountDrop(q) {
        const list = q ? accountsData.filter(a => a.label.toLowerCase().includes(q)) : accountsData;
        if (!list.length) { sharedDrop.innerHTML = '<div class="px-3 py-2 text-gray-400 italic text-xs">No results</div>'; return; }
        sharedDrop.innerHTML = list.map(a =>
            `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 hover:text-red-700 flex items-baseline gap-2" data-value="${escH(a.value)}" data-label="${escH(a.label)}">` +
            `<span class="font-mono text-xs text-gray-400 shrink-0">${escH(a.code)}</span>` +
            `<span class="truncate">${escH(a.name)}</span></div>`
        ).join('');
    }

    function renderPartnerDrop(q) {
        const list = q ? partnersData.filter(p => p.label.toLowerCase().includes(q)) : partnersData;
        if (!list.length) { sharedDrop.innerHTML = '<div class="px-3 py-2 text-gray-400 italic text-xs">No results</div>'; return; }
        const cl = list.filter(p => p.type === 'client');
        const sl = list.filter(p => p.type === 'supplier');
        let html = `<div class="px-3 py-2 cursor-pointer hover:bg-gray-50 text-gray-400 italic border-b border-gray-100 text-xs" data-value="" data-label="">\u2014 None \u2014</div>`;
        if (cl.length) {
            html += `<div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase tracking-wide bg-gray-50 sticky top-0">Clients</div>`;
            cl.forEach(p => { html += `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 hover:text-red-700 truncate" data-value="${escH(p.value)}" data-label="${escH(p.label)}">${escH(p.label)}</div>`; });
        }
        if (sl.length) {
            html += `<div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase tracking-wide bg-gray-50 sticky top-0">Suppliers</div>`;
            sl.forEach(p => { html += `<div class="pdrop-opt px-3 py-2 cursor-pointer hover:bg-red-50 hover:text-red-700 truncate" data-value="${escH(p.value)}" data-label="${escH(p.label)}">${escH(p.label)}</div>`; });
        }
        sharedDrop.innerHTML = html;
    }

    function closeDrop() { sharedDrop.classList.add('hidden'); activeDrop = null; }
    function scheduleDClose() { closeTimer = setTimeout(closeDrop, 160); }

    sharedDrop.addEventListener('mousedown', e => {
        const opt = e.target.closest('[data-value]');
        if (!opt || !activeDrop) return;
        e.preventDefault();
        if (activeDrop.type === 'account') setAccount(activeDrop.searchEl, activeDrop.hiddenEl, opt.dataset.value, opt.dataset.label);
        else setPartner(activeDrop.searchEl, activeDrop.hiddenEl, opt.dataset.value, opt.dataset.label);
        closeDrop();
    });

    document.addEventListener('mousedown', e => {
        if (!sharedDrop.contains(e.target) &&
            !e.target.classList.contains('account-search') &&
            !e.target.classList.contains('partner-search')) closeDrop();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrop(); });

    // ── Account typeahead ─────────────────────────────────────────────────────────
    function setAccount(searchEl, hiddenEl, value, label) {
        hiddenEl.value = value;
        searchEl.value = label;
        const clearBtn = searchEl.closest('.account-wrap')?.querySelector('.account-clear');
        if (value) {
            searchEl.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
            clearBtn?.classList.remove('hidden');
        } else {
            clearBtn?.classList.add('hidden');
        }
    }

    function initAccountTypeahead(wrap) {
        const searchEl = wrap.querySelector('.account-search');
        const hiddenEl = wrap.querySelector('.account-value');
        const clearBtn = wrap.querySelector('.account-clear');

        searchEl.addEventListener('focus', () => openDrop(searchEl, hiddenEl, 'account'));
        searchEl.addEventListener('input', () => {
            hiddenEl.value = '';
            clearBtn?.classList.add('hidden');
            openDrop(searchEl, hiddenEl, 'account');
        });
        searchEl.addEventListener('blur', scheduleDClose);
        clearBtn?.addEventListener('click', () => { setAccount(searchEl, hiddenEl, '', ''); searchEl.focus(); });

        // Restore from old() on validation failure
        const v = hiddenEl.value;
        if (v) {
            const a = accountsData.find(x => String(x.value) === String(v));
            if (a) setAccount(searchEl, hiddenEl, a.value, a.label);
        }
    }

    document.querySelectorAll('.account-wrap').forEach(initAccountTypeahead);

    // ── Partner typeahead ─────────────────────────────────────────────────────────
    function setPartner(searchEl, hiddenEl, value, label) {
        hiddenEl.value = value;
        searchEl.value = label;
        const wrap     = searchEl.closest('.partner-wrap');
        const badge    = wrap?.querySelector('.partner-badge');
        const clearBtn = wrap?.querySelector('.partner-clear');
        if (value) {
            const type = value.startsWith('client:') ? 'client' : 'supplier';
            badge.textContent = type === 'client' ? 'C' : 'S';
            badge.className   = `partner-badge absolute left-1.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold pointer-events-none ${type === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}`;
            searchEl.classList.add('pl-7');
            clearBtn?.classList.remove('hidden');
        } else {
            badge.className = 'partner-badge hidden';
            searchEl.classList.remove('pl-7');
            clearBtn?.classList.add('hidden');
        }
    }

    function initPartnerTypeahead(wrap) {
        const searchEl = wrap.querySelector('.partner-search');
        const hiddenEl = wrap.querySelector('.partner-value');
        const clearBtn = wrap.querySelector('.partner-clear');

        searchEl.addEventListener('focus', () => openDrop(searchEl, hiddenEl, 'partner'));
        searchEl.addEventListener('input', () => {
            hiddenEl.value = '';
            const b = wrap.querySelector('.partner-badge');
            if (b) b.className = 'partner-badge hidden';
            searchEl.classList.remove('pl-7');
            clearBtn?.classList.add('hidden');
            openDrop(searchEl, hiddenEl, 'partner');
        });
        searchEl.addEventListener('blur', scheduleDClose);
        clearBtn?.addEventListener('click', () => { setPartner(searchEl, hiddenEl, '', ''); searchEl.focus(); });

        // Restore from old() on validation failure
        if (hiddenEl.value) {
            const p = partnersData.find(x => x.value === hiddenEl.value);
            if (p) setPartner(searchEl, hiddenEl, p.value, p.label);
        }
    }

    document.querySelectorAll('.partner-wrap').forEach(initPartnerTypeahead);

    // ── Cell HTML generators for JS-added rows ────────────────────────────────────
    function accountCellHTML(idx) {
        return `<td class="px-3 py-2"><div class="account-wrap relative"><input type="hidden" name="lines[${idx}][account_id]" class="account-value" value=""><input type="text" class="account-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-6 truncate" placeholder="Search account\u2026" autocomplete="off"><button type="button" class="account-clear hidden absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs leading-none p-0.5">&times;</button></div></td>`;
    }

    function partnerCellHTML(idx) {
        return `<td class="px-3 py-2"><div class="partner-wrap relative"><input type="hidden" name="lines[${idx}][partner]" class="partner-value" value=""><span class="partner-badge hidden"></span><input type="text" class="partner-search w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400 pr-6 truncate" placeholder="Search partner\u2026" autocomplete="off"><button type="button" class="partner-clear hidden absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-xs leading-none p-0.5">&times;</button></div></td>`;
    }

    // ── Totals ────────────────────────────────────────────────────────────────────
    function updateTotals() {
        let dr = 0, cr = 0;
        document.querySelectorAll('.debit-input').forEach(i => dr += parseFloat(i.value) || 0);
        document.querySelectorAll('.credit-input').forEach(i => cr += parseFloat(i.value) || 0);
        document.getElementById('totalDebit').textContent  = dr.toLocaleString('en', {minimumFractionDigits:2});
        document.getElementById('totalCredit').textContent = cr.toLocaleString('en', {minimumFractionDigits:2});
        const balanced = Math.abs(dr - cr) < 0.01;
        document.getElementById('balanceRow').classList.toggle('hidden', balanced || (dr === 0 && cr === 0));
        const cls = 'px-3 py-2 text-right font-bold font-mono ' + (balanced ? 'text-green-700' : 'text-red-600');
        document.getElementById('totalDebit').className  = cls;
        document.getElementById('totalCredit').className = cls;
    }

    // ── Form validation: account required ────────────────────────────────────────
    document.getElementById('jeForm').addEventListener('submit', e => {
        let firstEmpty = null;
        document.querySelectorAll('.account-value').forEach(h => {
            const s = h.closest('.account-wrap')?.querySelector('.account-search');
            if (!h.value) {
                s?.classList.add('border-red-400', 'ring-1', 'ring-red-400');
                if (!firstEmpty) firstEmpty = s;
            } else {
                s?.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
            }
        });
        if (firstEmpty) { e.preventDefault(); firstEmpty.focus(); }
    });

    // ── Add line ──────────────────────────────────────────────────────────────────
    document.getElementById('addLine').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'line-row border-t border-gray-100';
        tr.innerHTML = `
        ${accountCellHTML(lineIndex)}
        ${partnerCellHTML(lineIndex)}
        <td class="px-3 py-2"><input type="text" name="lines[${lineIndex}][description]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400"></td>
        <td class="px-3 py-2"><input type="number" name="lines[${lineIndex}][debit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right debit-input" oninput="updateTotals()"></td>
        <td class="px-3 py-2"><input type="number" name="lines[${lineIndex}][credit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right credit-input" oninput="updateTotals()"></td>
        <td class="px-3 py-2 text-center"><button type="button" class="remove-line text-red-400 hover:text-red-600 text-xs">&times;</button></td>`;
        document.getElementById('linesBody').appendChild(tr);
        initAccountTypeahead(tr.querySelector('.account-wrap'));
        initPartnerTypeahead(tr.querySelector('.partner-wrap'));
        tr.querySelector('.remove-line').addEventListener('click', () => { tr.remove(); updateTotals(); });
        lineIndex++;
    });

    document.getElementById('linesBody').addEventListener('click', e => {
        if (e.target.classList.contains('remove-line') && !e.target.disabled) {
            e.target.closest('tr').remove();
            updateTotals();
        }
    });

    updateTotals();
    </script>
</x-admin-layout>
