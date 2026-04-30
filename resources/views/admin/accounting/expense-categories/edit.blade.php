<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.expense-categories.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expense Categories</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Category: {{ $expenseCategory->name }}</h1>
    </div>

    <div class="max-w-lg bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.expense-categories.update', $expenseCategory) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linked Ledger Account</label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">None</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(old('account_id', $expenseCategory->account_id) == $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                    @if(!$expenseCategory->account_id)
                    <p class="text-xs text-amber-600 font-medium mt-1">⚠ No ledger account linked — expenses in this category cannot be posted to the ledger until one is set.</p>
                    @else
                    <p class="text-xs text-gray-400 mt-1">The expense account debited when posting. Only expense-type accounts shown.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description', $expenseCategory->description) }}</textarea>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $expenseCategory->is_active)) class="rounded">
                        Active
                    </label>
                </div>
                <div class="pt-1 border-t border-gray-100">
                    <label class="inline-flex items-start gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_zero_rated" value="1" @checked(old('is_zero_rated', $expenseCategory->is_zero_rated)) class="mt-0.5 rounded border-gray-300 text-green-600">
                        <div>
                            <span class="text-sm font-medium text-gray-700">VAT-exempt category (zero-rated)</span>
                            <p class="text-xs text-gray-400 mt-0.5">All expenses in this category will automatically have VAT set to 0. Use for fuel purchases and other tax-exempt items.</p>
                        </div>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.expense-categories.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>
</x-admin-layout>
