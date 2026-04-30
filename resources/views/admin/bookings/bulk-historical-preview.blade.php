<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.bookings.record-historical') }}" class="text-slate-600 hover:text-slate-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bulk Import Preview</h1>
            <p class="text-slate-500 mt-1 text-sm">Review the parsed rows before saving. Rows with errors will be skipped.</p>
        </div>
    </div>

    <!-- Summary strip -->
    <div class="mb-6 bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="flex divide-x divide-slate-200">
            <div class="flex-1 px-6 py-4 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Total Rows</p>
                <p class="text-2xl font-bold text-slate-900">{{ count($rows) }}</p>
            </div>
            <div class="flex-1 px-6 py-4 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Will Save</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $validCount }}</p>
            </div>
            <div class="flex-1 px-6 py-4 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Will Skip (errors)</p>
                <p class="text-2xl font-bold text-red-500">{{ count($rows) - $validCount }}</p>
            </div>
            <div class="flex-1 px-6 py-4 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">New Clients</p>
                <p class="text-2xl font-bold text-amber-600">{{ count(array_filter($rows, fn($r) => $r['client_status'] === 'new' && empty($r['errors']))) }}</p>
            </div>
        </div>
    </div>

    @if($validCount === 0)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
            <strong>No valid rows found.</strong> Please fix the errors in your spreadsheet and upload again.
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-24">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-10">#</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Client</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Genset / Description</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Rental Period</th>
                        <th class="py-3 px-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Subtotal</th>
                        <th class="py-3 px-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">VAT</th>
                        <th class="py-3 px-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Payment</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $row)
                        @php $hasErrors = !empty($row['errors']); @endphp
                        <tr class="{{ $hasErrors ? 'bg-red-50' : 'hover:bg-slate-50' }} transition">
                            <td class="py-3 px-4 text-slate-400 text-xs">{{ $row['row'] }}</td>

                            <!-- Client -->
                            <td class="py-3 px-4">
                                @if($row['client_label'])
                                    <p class="font-medium text-slate-900">{{ $row['client_label'] }}</p>
                                @else
                                    <p class="font-medium text-slate-900">{{ $row['client_identifier'] }}</p>
                                    @if($row['client_status'] === 'new')
                                        <span class="inline-flex items-center px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-medium rounded mt-0.5">New client</span>
                                    @endif
                                @endif
                                @if($row['client_phone'])
                                    <p class="text-xs text-slate-400">{{ $row['client_phone'] }}</p>
                                @endif
                            </td>

                            <!-- Genset / Description -->
                            <td class="py-3 px-4">
                                @if($row['genset_type'])
                                    <p class="text-slate-700">{{ $row['genset_type'] }}</p>
                                @endif
                                <p class="text-xs text-slate-500">{{ Str::limit($row['description'], 50) }}</p>
                            </td>

                            <!-- Dates -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <p class="text-slate-700">{{ $row['rental_start_date'] }}</p>
                                <p class="text-xs text-slate-400">→ {{ $row['rental_end_date'] }}</p>
                            </td>

                            <!-- Subtotal -->
                            <td class="py-3 px-4 text-right whitespace-nowrap text-slate-700">
                                {{ $row['currency'] }} {{ number_format($row['subtotal'], 0) }}
                            </td>

                            <!-- VAT -->
                            <td class="py-3 px-4 text-right whitespace-nowrap text-slate-500 text-xs">
                                @if($row['zero_rated'])
                                    <span class="text-slate-400">0%</span>
                                @else
                                    {{ $row['currency'] }} {{ number_format($row['vat_amount'], 0) }}
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="py-3 px-4 text-right whitespace-nowrap font-semibold text-slate-900">
                                {{ $row['currency'] }} {{ number_format($row['total'], 0) }}
                            </td>

                            <!-- Payment -->
                            <td class="py-3 px-4">
                                <p class="text-slate-700 capitalize">{{ str_replace('_', ' ', $row['payment_method']) }}</p>
                                <p class="text-xs text-slate-400">{{ $row['payment_date'] }}</p>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                @if($hasErrors)
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">Skip</span>
                                        <ul class="mt-1 space-y-0.5">
                                            @foreach($row['errors'] as $err)
                                                <li class="text-xs text-red-600">• {{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        OK
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sticky bottom bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-lg z-40">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <div class="text-sm text-slate-600">
                <span class="font-semibold text-slate-900">{{ $validCount }}</span> of <span class="font-semibold">{{ count($rows) }}</span> rows will be saved.
                @if(count($rows) - $validCount > 0)
                    <span class="text-red-600 ml-2">{{ count($rows) - $validCount }} row(s) with errors will be skipped.</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.bookings.record-historical') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    ← Start Over
                </a>
                @if($validCount > 0)
                    <form method="POST" action="{{ route('admin.bookings.bulk-historical-confirm') }}" class="flex items-center gap-3">
                        @csrf
                        <select name="bank_account_id" required
                            class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Select bank account —</option>
                            @foreach($bankAccounts as $ba)
                            <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->name }} ({{ $ba->currency }})</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save {{ $validCount }} Sale{{ $validCount !== 1 ? 's' : '' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
