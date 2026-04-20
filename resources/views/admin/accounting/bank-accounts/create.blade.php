<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Bank Accounts</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Bank / Cash Account</h1>
    </div>

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.bank-accounts.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="e.g. CRDB Bank — Operations" required>
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type <span class="text-red-500">*</span></label>
                    <select name="account_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="bank" @selected(old('account_type') === 'bank')>Bank Account</option>
                        <option value="cash" @selected(old('account_type') === 'cash')>Cash / Petty Cash</option>
                        <option value="mobile_money" @selected(old('account_type') === 'mobile_money')>Mobile Money</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', 'TZS') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="e.g. CRDB Bank">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <script>window.__coaAccounts = @json($coaAccounts->map(fn($c) => ['id' => $c->id, 'label' => $c->code . ' — ' . $c->name]));</script>
                <div x-data="coaSelectData({{ old('account_id') ? (int) old('account_id') : 'null' }})" x-on:click.outside="open = false" class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linked COA Account <span class="text-red-500">*</span></label>
                    <input type="hidden" name="account_id" :value="selected.id">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true; selected = { id: '', label: '' }"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                           placeholder="Type to search COA account…" autocomplete="off">
                    <div x-show="open && filtered.length > 0" x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="account in filtered" :key="account.id">
                            <div @click="select(account)"
                                 :class="selected.id == account.id ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-50'"
                                 class="px-3 py-2 text-sm cursor-pointer" x-text="account.label"></div>
                        </template>
                    </div>
                    <div x-show="open && filtered.length === 0" class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-sm px-3 py-2 text-sm text-gray-400">
                        No accounts match
                    </div>
                    @error('account_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">The asset account in your Chart of Accounts that represents this bank account</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance</label>
                    <input type="number" name="current_balance" value="{{ old('current_balance', 0) }}" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.bank-accounts.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Create Account</button>
            </div>
        </form>
    </div>
</x-admin-layout>
