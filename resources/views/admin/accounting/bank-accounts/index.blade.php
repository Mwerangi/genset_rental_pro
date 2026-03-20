<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bank & Cash Accounts</h1>
            <p class="text-gray-500 mt-1">Registered payment accounts and balances</p>
        </div>
        <a href="{{ route('admin.accounting.bank-accounts.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Account
        </a>
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
