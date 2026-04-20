<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.accounting.accounts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Chart of Accounts</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Account — {{ $account->code }}</h1>
    </div>

    <div class="max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.accounts.update', $account) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                @if(!$account->is_system)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $account->code) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Code</label>
                    <input type="text" value="{{ $account->code }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500" disabled>
                    <p class="text-xs text-gray-400 mt-1">System accounts cannot change code</p>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $account->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                @if(!$account->is_system)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @foreach(['asset','liability','equity','revenue','expense'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $account->type) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Normal Balance</label>
                    <select name="normal_balance" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="debit" @selected(old('normal_balance', $account->normal_balance) === 'debit')>Debit</option>
                        <option value="credit" @selected(old('normal_balance', $account->normal_balance) === 'credit')>Credit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sub-type</label>
                    <input type="text" name="sub_type" value="{{ old('sub_type', $account->sub_type) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Account</label>
                    <select name="parent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">None (top-level)</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $account->parent_id) == $parent->id)>{{ $parent->code }} — {{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        @foreach(['TZS','USD','EUR','KES','GBP'] as $c)
                        <option value="{{ $c }}" @selected(old('currency', $account->currency ?? 'TZS') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description', $account->description) }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active)) class="rounded">
                        Active
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.accounting.accounts.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Save Changes</button>
            </div>
        </form>
    </div>
</x-admin-layout>
