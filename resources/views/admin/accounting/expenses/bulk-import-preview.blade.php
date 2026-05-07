<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.expenses.bulk-import') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Import</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Preview Import</h1>
        <p class="text-sm text-gray-500 mt-1">Review each row. Rows with errors (shown in red) will be skipped. Correct your CSV and re-upload to fix them.</p>
    </div>

    @php
        $validRows   = collect($rows)->filter(fn($r) => empty($r['errors']));
        $invalidRows = collect($rows)->filter(fn($r) => !empty($r['errors']));
        $totalAmount = $validRows->sum(fn($r) => $r['is_zero_rated'] ? $r['amount'] : $r['amount'] * 1.18);
    @endphp

    {{-- Summary bar --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm">
            <span class="font-semibold text-green-700">{{ $validRows->count() }}</span>
            <span class="text-green-600"> valid row(s) will be imported</span>
        </div>
        @if($invalidRows->count())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm">
            <span class="font-semibold text-red-700">{{ $invalidRows->count() }}</span>
            <span class="text-red-600"> row(s) with errors — will be skipped</span>
        </div>
        @endif
        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm">
            <span class="text-gray-500">Total (incl. VAT):</span>
            <span class="font-semibold text-gray-800 ml-1">TZS {{ number_format($totalAmount, 2) }}</span>
        </div>
    </div>

    @if($validRows->isEmpty())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-5 text-sm">
            <p class="font-semibold">No valid rows found.</p>
            <p class="mt-1">Please fix your CSV file and <a href="{{ route('admin.accounting.expenses.bulk-import') }}" class="underline">upload again</a>.</p>
        </div>
    @else
    <form method="POST" action="{{ route('admin.accounting.expenses.bulk-import.confirm') }}">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-3 text-center w-12">Row</th>
                            <th class="px-3 py-3 text-left w-28">Date</th>
                            <th class="px-3 py-3 text-left min-w-48">Description</th>
                            <th class="px-3 py-3 text-left w-40">Account (COA)</th>
                            <th class="px-3 py-3 text-left w-44">Pay From Account</th>
                            <th class="px-3 py-3 text-left w-36">Supplier</th>
                            <th class="px-3 py-3 text-right w-28">Amount</th>
                            <th class="px-3 py-3 text-center w-16">No VAT</th>
                            <th class="px-3 py-3 text-right w-24">VAT</th>
                            <th class="px-3 py-3 text-right w-28">Total</th>
                            <th class="px-3 py-3 text-left w-24">Reference</th>
                            <th class="px-3 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $i => $row)
                            @php
                                $hasError = !empty($row['errors']);
                                $isZero   = $row['is_zero_rated'];
                                $amount   = (float) $row['amount'];
                                $vat      = $isZero ? 0 : round($amount * 0.18, 2);
                                $total    = $amount + $vat;
                            @endphp
                            <tr class="{{ $hasError ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                                <td class="px-3 py-2 text-center text-xs text-gray-400">{{ $row['line'] }}</td>
                                <td class="px-3 py-2 {{ $hasError ? 'text-red-700' : 'text-gray-700' }}">{{ $row['expense_date'] }}</td>
                                <td class="px-3 py-2 {{ $hasError ? 'text-red-700' : 'text-gray-700' }}">{{ $row['description'] }}</td>
                                <td class="px-3 py-2 font-mono {{ $hasError ? 'text-red-600' : 'text-gray-700' }}">{{ $row['account_code'] }}</td>
                                <td class="px-3 py-2">
                                    @if(!$hasError)
                                        <select name="bank_accounts[{{ $row['valid_index'] }}]" required
                                            class="w-44 border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                            <option value="">— select account —</option>
                                            @foreach($bankAccounts as $ba)
                                                <option value="{{ $ba->id }}" @selected($row['bank_account_id'] == $ba->id)>
                                                    {{ $ba->name }} ({{ $ba->currency }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="text-xs text-gray-400">{{ $row['bank_account_name'] ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 {{ $hasError ? 'text-red-600' : 'text-gray-500' }}">{{ $row['supplier_name'] ?: '—' }}</td>
                                <td class="px-3 py-2 text-right {{ $hasError ? 'text-red-700' : 'text-gray-700' }}">{{ number_format($amount, 2) }}</td>
                                <td class="px-3 py-2 text-center">
                                    @if($isZero)<span class="text-amber-600 text-xs font-medium">Yes</span>@else<span class="text-gray-400 text-xs">No</span>@endif
                                </td>
                                <td class="px-3 py-2 text-right text-gray-500">{{ number_format($vat, 2) }}</td>
                                <td class="px-3 py-2 text-right font-medium {{ $hasError ? 'text-red-700' : 'text-gray-800' }}">{{ number_format($total, 2) }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $row['reference'] ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    @if($hasError)
                                        <span class="inline-flex items-center gap-1 text-xs text-red-600 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ implode('; ', $row['errors']) }}
                                        </span>
                                    @else
                                        <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Hidden inputs for valid rows only --}}
        @foreach($validRows->values() as $j => $row)
            <input type="hidden" name="rows[{{ $j }}][expense_date]"    value="{{ $row['expense_date'] }}">
            <input type="hidden" name="rows[{{ $j }}][description]"     value="{{ $row['description'] }}">
            <input type="hidden" name="rows[{{ $j }}][account_id]"      value="{{ $row['account_id'] }}">
            <input type="hidden" name="rows[{{ $j }}][supplier_id]"         value="{{ $row['supplier_id'] ?? '' }}">
            <input type="hidden" name="rows[{{ $j }}][amount]"              value="{{ $row['amount'] }}">
            <input type="hidden" name="rows[{{ $j }}][is_zero_rated]"       value="{{ $row['is_zero_rated'] ? 1 : 0 }}">
            <input type="hidden" name="rows[{{ $j }}][reference]"           value="{{ $row['reference'] }}">
        @endforeach

        <div class="mt-4 flex items-center gap-3">
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                Confirm &amp; Import {{ $validRows->count() }} Expense(s)
            </button>
            <a href="{{ route('admin.accounting.expenses.bulk-import') }}"
               class="text-sm text-gray-500 hover:text-gray-700">Re-upload CSV</a>
        </div>
    </form>
    @endif
</x-admin-layout>
