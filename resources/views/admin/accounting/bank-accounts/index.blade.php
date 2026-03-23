<x-admin-layout>
    <div x-data="{ transferOpen: false }" class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bank & Cash Accounts</h1>
            <p class="text-gray-500 mt-1">Registered payment accounts and balances</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="transferOpen = true"
                class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transfer Funds
            </button>
            <a href="{{ route('admin.accounting.bank-accounts.create') }}"
               class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Account
            </a>
        </div>

        {{-- Transfer Modal --}}
        <div x-show="transferOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="transferOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-gray-900">Transfer Funds</h2>
                    <button @click="transferOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.accounting.account-transfers.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Account <span class="text-red-500">*</span></label>
                            <select name="from_bank_account_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">— Select source account —</option>
                                @foreach($accounts as $ba)
                                <option value="{{ $ba->id }}">{{ $ba->name }} ({{ $ba->currency }} {{ number_format($ba->current_balance, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Account <span class="text-red-500">*</span></label>
                            <select name="to_bank_account_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">— Select destination account —</option>
                                @foreach($accounts as $ba)
                                <option value="{{ $ba->id }}">{{ $ba->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                                <input type="number" name="amount" min="1" step="1" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transfer_date" required value="{{ date('Y-m-d') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description / Reference</label>
                            <input type="text" name="description" maxlength="255"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="e.g. Monthly petty cash top-up">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="transferOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Confirm Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($accounts as $ba)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-bold text-gray-900">{{ $ba->name }}</p>
                        @if($ba->bank_name)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $ba->bank_name }}{{ $ba->account_number ? ' — ' . $ba->account_number : '' }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ba->getTypeBadgeStyle() }}">
                        {{ $ba->getTypeLabel() }}
                    </span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Balance</p>
                        <p class="text-2xl font-bold {{ $ba->current_balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            {{ $ba->currency }} {{ number_format($ba->current_balance, 0) }}
                        </p>
                        @if($ba->account)
                        <p class="text-xs text-gray-400 mt-0.5">COA: {{ $ba->account->code }} {{ $ba->account->name }}</p>
                        @endif
                    </div>
                    @if(!$ba->is_active)
                    <span class="text-xs text-gray-400 italic">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="border-t border-gray-100 px-5 py-3 flex justify-between items-center bg-gray-50">
                <a href="{{ route('admin.accounting.bank-accounts.show', $ba) }}" class="text-xs text-blue-600 hover:underline">View Transactions</a>
                <div class="flex gap-3">
                    <a href="{{ route('admin.accounting.bank-accounts.edit', $ba) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.accounting.bank-accounts.destroy', $ba) }}" onsubmit="return confirm('Delete this bank account?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-400">
            No bank accounts set up yet.
            <a href="{{ route('admin.accounting.bank-accounts.create') }}" class="text-red-600 hover:underline ml-1">Add one</a>
        </div>
        @endforelse
    </div>
</x-admin-layout>
