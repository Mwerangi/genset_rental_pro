<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.journal-entries.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Journal Entries</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Manual Journal Entry</h1>
        <p class="text-gray-500 mt-1">Debits must equal credits</p>
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
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Invoice #, receipt #..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
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
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Account <span class="text-red-500">*</span></th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-600">Description</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-36">Debit (Tsh)</th>
                                <th class="text-right px-3 py-2 font-semibold text-gray-600 w-36">Credit (Tsh)</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                            @php $lines = old('lines', [['account_id'=>'','description'=>'','debit'=>'','credit'=>''],['account_id'=>'','description'=>'','debit'=>'','credit'=>'']]); @endphp
                            @foreach($lines as $i=>$line)
                            <tr class="line-row border-t border-gray-100">
                                <td class="px-3 py-2">
                                    <select name="lines[{{ $i }}][account_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required>
                                        <option value="">Select account</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old("lines.{$i}.account_id") == $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
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
                                    <button type="button" class="remove-line text-red-400 hover:text-red-600" @if($i < 2) disabled @endif>✕</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Totals</td>
                                <td class="px-3 py-2 text-right font-bold font-mono" id="totalDebit">0</td>
                                <td class="px-3 py-2 text-right font-bold font-mono" id="totalCredit">0</td>
                                <td></td>
                            </tr>
                            <tr id="balanceRow" class="hidden">
                                <td colspan="5" class="px-3 py-2 text-center text-xs font-medium text-red-600">⚠ Entry is not balanced</td>
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

    <script>
    let lineIndex = {{ count($lines) }};
    const accountOptions = `{!! collect($accounts)->map(fn($a) => '<option value="'.$a->id.'">'.$a->code.' — '.htmlspecialchars($a->name, ENT_QUOTES).'</option>')->implode('') !!}`;

    function updateTotals() {
        let dr = 0, cr = 0;
        document.querySelectorAll('.debit-input').forEach(i => dr += parseFloat(i.value) || 0);
        document.querySelectorAll('.credit-input').forEach(i => cr += parseFloat(i.value) || 0);
        document.getElementById('totalDebit').textContent = dr.toLocaleString('en', {minimumFractionDigits:2});
        document.getElementById('totalCredit').textContent = cr.toLocaleString('en', {minimumFractionDigits:2});
        const balanced = Math.abs(dr - cr) < 0.01;
        document.getElementById('balanceRow').classList.toggle('hidden', balanced);
        document.getElementById('totalDebit').className = 'px-3 py-2 text-right font-bold font-mono ' + (balanced ? 'text-green-700' : 'text-red-600');
        document.getElementById('totalCredit').className = 'px-3 py-2 text-right font-bold font-mono ' + (balanced ? 'text-green-700' : 'text-red-600');
    }

    document.getElementById('addLine').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'line-row border-t border-gray-100';
        tr.innerHTML = `<td class="px-3 py-2"><select name="lines[${lineIndex}][account_id]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" required><option value="">Select account</option>${accountOptions}</select></td>
        <td class="px-3 py-2"><input type="text" name="lines[${lineIndex}][description]" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-red-400"></td>
        <td class="px-3 py-2"><input type="number" name="lines[${lineIndex}][debit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right debit-input" oninput="updateTotals()"></td>
        <td class="px-3 py-2"><input type="number" name="lines[${lineIndex}][credit]" value="0" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right credit-input" oninput="updateTotals()"></td>
        <td class="px-3 py-2 text-center"><button type="button" class="remove-line text-red-400 hover:text-red-600">✕</button></td>`;
        document.getElementById('linesBody').appendChild(tr);
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
