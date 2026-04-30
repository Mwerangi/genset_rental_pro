<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.cash-requests.show', $cashRequest) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $cashRequest->request_number }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Retire Cash Request</h1>
        <p class="text-sm text-gray-500 mt-0.5">Enter actual amounts spent and upload receipts for each item. A variance journal entry will be posted automatically.</p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @php
        $itemsJson = $cashRequest->items->map(fn($i) => [
            'id'            => $i->id,
            'description'   => $i->description,
            'category'      => $i->expenseCategory?->name ?? '—',
            'estimated'     => (float) $i->estimated_amount,
            'vat_estimated' => (float) ($i->vat_amount ?? 0),
            'is_zero_rated' => (bool) $i->is_zero_rated,
            'actual'        => (float) ($i->actual_amount ?? $i->estimated_amount),
            'receipt_ref'   => $i->receipt_ref ?? '',
        ])->values();
    @endphp

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('retireCr', () => ({
            items: @json($itemsJson),
            vatRate: 0.18,
            itemActualVat(item) {
                if (item.is_zero_rated) return 0;
                return Math.round(item.actual * this.vatRate * 100) / 100;
            },
            itemActualTotal(item) {
                return item.actual + this.itemActualVat(item);
            },
            get estimatedNetTotal() {
                return this.items.reduce((s, i) => s + i.estimated, 0);
            },
            get estimatedVatTotal() {
                return this.items.reduce((s, i) => s + i.vat_estimated, 0);
            },
            get estimatedGrossTotal() {
                return this.estimatedNetTotal + this.estimatedVatTotal;
            },
            get actualNetTotal() {
                return this.items.reduce((s, i) => s + i.actual, 0);
            },
            get actualVatTotal() {
                return this.items.reduce((s, i) => s + this.itemActualVat(i), 0);
            },
            get actualGrossTotal() {
                return this.actualNetTotal + this.actualVatTotal;
            },
            get variance() {
                return this.actualGrossTotal - this.estimatedGrossTotal;
            },
            fmt(n) {
                return new Intl.NumberFormat('en-UG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
            }
        }));
    });
    </script>

    <div x-data="retireCr()">

        {{-- Request summary --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Request #</span>
                    <p class="font-semibold text-gray-900 mt-0.5">{{ $cashRequest->request_number }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Requested By</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $cashRequest->requestedBy->name ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Expense Date</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $cashRequest->expense_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Disbursed From</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $cashRequest->bankAccount?->name ?? '—' }}</p>
                </div>
                <div class="col-span-2 md:col-span-4">
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Purpose</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $cashRequest->purpose }}</p>
                </div>
            </div>
        </div>

        {{-- Retirement form --}}
        <form method="POST" action="{{ route('admin.accounting.cash-requests.retire', $cashRequest) }}" enctype="multipart/form-data">
            @csrf

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-700">Cost Items — Enter Actual Amounts</p>
                    <p class="text-xs text-gray-500">★ = zero-rated (no VAT)</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide w-6">#</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Description</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Category</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Estimated</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Actual (Net)</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Actual VAT</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Receipt Ref</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">Receipt File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cashRequest->items as $index => $item)
                            <tr class="border-b border-gray-100 hover:bg-gray-50"
                                x-data="{ idx: {{ $index }} }">
                                <td class="px-3 py-2.5 text-gray-400 text-xs">{{ $index + 1 }}</td>
                                <td class="px-3 py-2.5 text-gray-800 text-sm">
                                    {{ $item->description }}
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td class="px-3 py-2.5 text-gray-600 text-xs whitespace-nowrap">
                                    {{ $item->expenseCategory?->name ?? '—' }}
                                    @if($item->is_zero_rated)<span class="text-yellow-600 ml-1">★</span>@endif
                                </td>
                                <td class="px-3 py-2.5 text-right text-gray-700 font-mono text-xs whitespace-nowrap">
                                    {{ number_format($item->estimated_amount, 2) }}
                                    @if(!$item->is_zero_rated)
                                    <div class="text-gray-400">+{{ number_format($item->vat_amount ?? 0, 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" step="0.01" min="0"
                                           name="items[{{ $index }}][actual_amount]"
                                           x-model.number="items[{{ $index }}].actual"
                                           value="{{ old("items.{$index}.actual_amount", $item->actual_amount ?? $item->estimated_amount) }}"
                                           class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-sm font-mono focus:outline-none focus:ring-2 focus:ring-red-500"
                                           required>
                                </td>
                                <td class="px-3 py-2.5 text-right text-gray-600 font-mono text-xs whitespace-nowrap"
                                    x-text="fmt(itemActualVat(items[{{ $index }}]))">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="text"
                                           name="items[{{ $index }}][receipt_ref]"
                                           value="{{ old("items.{$index}.receipt_ref", $item->receipt_ref) }}"
                                           placeholder="RCT-001"
                                           class="w-24 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </td>
                                <td class="px-3 py-2.5">
                                    @if($item->receipt_path)
                                        <div class="text-xs text-green-600 mb-1">✓ on file</div>
                                    @endif
                                    <input type="file" name="items[{{ $index }}][receipt_file]"
                                           accept=".jpg,.jpeg,.png,.pdf,.heic"
                                           class="text-xs text-gray-600 w-36 file:mr-1.5 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-red-50 file:text-red-700 cursor-pointer">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="3" class="px-3 py-2.5 text-xs font-semibold text-gray-600 uppercase tracking-wide">Totals</td>
                                <td class="px-3 py-2.5 text-right font-semibold font-mono text-xs text-gray-700">
                                    {{ number_format($cashRequest->amount, 2) }}
                                    <div class="text-gray-400 font-normal">+{{ number_format($cashRequest->vat_amount, 2) }}</div>
                                    <div class="font-semibold text-gray-800">= {{ number_format($cashRequest->total_amount, 2) }}</div>
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold font-mono text-xs"
                                    :class="variance > 0 ? 'text-red-600' : variance < 0 ? 'text-green-600' : 'text-gray-700'"
                                    x-text="fmt(actualNetTotal)"></td>
                                <td class="px-3 py-2.5 text-right text-xs text-gray-600 font-mono"
                                    x-text="fmt(actualVatTotal)"></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="bg-gray-100 border-t border-gray-200">
                                <td colspan="3" class="px-3 py-2 text-xs font-semibold text-gray-700">
                                    Gross Total &amp; Variance
                                </td>
                                <td class="px-3 py-2 text-right font-bold font-mono text-xs text-gray-800 whitespace-nowrap">
                                    Tsh {{ number_format($cashRequest->total_amount, 2) }}
                                </td>
                                <td colspan="2" class="px-3 py-2 text-right">
                                    <span class="font-bold font-mono text-xs"
                                          :class="variance > 0 ? 'text-red-700' : variance < 0 ? 'text-green-700' : 'text-gray-700'"
                                          x-text="'Tsh ' + fmt(actualGrossTotal)"></span>
                                </td>
                                <td colspan="2" class="px-3 py-2 text-right">
                                    <div class="text-xs text-gray-500 mb-0.5">Variance</div>
                                    <span class="font-bold font-mono text-xs"
                                          :class="variance > 0 ? 'text-red-700' : variance < 0 ? 'text-green-700' : 'text-gray-500'"
                                          x-text="(variance > 0 ? '+' : '') + fmt(variance)"></span>
                                    <div class="text-xs mt-0.5"
                                         :class="variance > 0 ? 'text-red-500' : variance < 0 ? 'text-green-500' : 'text-gray-400'"
                                         x-text="variance > 0.009 ? 'Over-spent' : variance < -0.009 ? 'Under-spent (surplus)' : 'No variance'">
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Notes & submit --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Retirement Notes <span class="text-xs text-gray-400">(optional)</span></label>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Any notes about the actual expenditure, missing receipts, etc.">{{ old('notes', $cashRequest->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.accounting.cash-requests.show', $cashRequest) }}"
                       class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>

                    <div class="flex items-center gap-3">
                        {{-- Variance badge --}}
                        <div class="text-sm">
                            <span class="text-gray-500">Variance:</span>
                            <span class="font-semibold font-mono ml-1"
                                  :class="variance > 0 ? 'text-red-700' : variance < 0 ? 'text-green-700' : 'text-gray-500'"
                                  x-text="(variance > 0 ? '+' : '') + 'Tsh ' + fmt(variance)"></span>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                            Submit Retirement
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</x-admin-layout>
