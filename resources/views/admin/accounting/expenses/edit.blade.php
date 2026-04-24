<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.expenses.show', $expense) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $expense->expense_number }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Expense</h1>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.expenses.update', $expense) }}" enctype="multipart/form-data"
              x-data="{
                  zeroRated: {{ $expense->is_zero_rated ? 'true' : 'false' }},
                  vatAmount: '{{ old('vat_amount', $expense->vat_amount) }}'
              }">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-data="{
                        selectedCat: '{{ old('expense_category_id', $expense->expense_category_id) }}',
                        cats: {{ $categories->map(fn($c) => ['id' => (string)$c->id, 'hasAccount' => (bool)$c->account_id])->toJson() }}
                    }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="expense_category_id" x-model="selectedCat"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('expense_category_id', $expense->expense_category_id) == $cat->id)>
                            {{ $cat->name }}{{ $cat->account ? ' — ' . $cat->account->code . ' ' . $cat->account->name : ' ⚠ No ledger account' }}
                        </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    <template x-if="selectedCat && cats.find(c => c.id === selectedCat && !c.hasAccount)">
                        <p class="mt-1 text-xs text-amber-600 font-medium">⚠ This category has no ledger account — posting will fail. <a href="{{ route('admin.accounting.expense-categories.index') }}" class="underline">Link one in Categories</a>.</p>
                    </template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay From Account <span class="text-red-500">*</span></label>
                    <select name="bank_account_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="">Select account</option>
                        @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" @selected(old('bank_account_id', $expense->bank_account_id) == $ba->id)>
                            {{ $ba->name }} ({{ $ba->currency }} {{ number_format($ba->current_balance, 0) }})
                        </option>
                        @endforeach
                    </select>
                    @error('bank_account_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (excl. VAT) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}"
                           step="0.01" min="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Zero-rated toggle --}}
                <div class="col-span-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_zero_rated" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-red-600"
                               x-model="zeroRated"
                               @change="if(zeroRated) vatAmount = '0'">
                        <span class="text-sm font-medium text-gray-700">Zero-rated expense (no VAT applicable)</span>
                    </label>
                </div>

                {{-- VAT field --}}
                <div x-show="!zeroRated" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT Amount</label>
                    <input type="number" name="vat_amount" :value="zeroRated ? 0 : vatAmount"
                           @input="vatAmount = $el.value"
                           step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div x-show="zeroRated" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT</label>
                    <div class="w-full border border-green-200 bg-green-50 rounded-lg px-3 py-2 text-sm text-green-700 font-semibold">
                        Zero-rated — VAT: 0.00
                    </div>
                    <input type="hidden" name="vat_amount" value="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date?->toDateString()) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Receipt #</label>
                    <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Attachment (PDF/Image)
                        @if($expense->attachment)
                        — <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="text-blue-600 hover:underline text-xs">View current</a>
                        @endif
                    </label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing attachment.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.expenses.show', $expense) }}"
                   class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>
</x-admin-layout>
