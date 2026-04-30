<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
            <p class="text-gray-500 mt-1">Operational expenses and cost tracking</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.accounting.expense-categories.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Manage Categories
            </a>
            <a href="{{ route('admin.accounting.expenses.export', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.accounting.expenses.create') }}"
               class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Expense
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Posted This Month</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Tsh {{ number_format($stats['total_this_month'], 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Pending Approval</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_approval'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Approved</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Posted to Ledger</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['posted'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.accounting.expenses.index') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Expense #, description..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all">All Statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="posted" @selected(request('status') === 'posted')>Posted</option>
                </select>
            </div>
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Filter</button>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Expense #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Bank Account</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Amt (excl. VAT)</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">VAT</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Attach.</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Bank Ref</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expenses as $expense)
                @php $colors = ['draft'=>'bg-gray-100 text-gray-600','approved'=>'bg-blue-50 text-blue-700','posted'=>'bg-green-50 text-green-700']; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700 whitespace-nowrap">{{ $expense->expense_number }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $expense->expense_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-800 max-w-xs">
                        <span class="line-clamp-2">{{ $expense->description }}</span>
                        @if($expense->reference)
                        <span class="block text-xs text-gray-400 mt-0.5">Ref: {{ $expense->reference }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $expense->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $expense->bankAccount?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs text-gray-700 whitespace-nowrap">Tsh {{ number_format($expense->amount, 0) }}</td>
                    <td class="px-4 py-3 text-right text-xs whitespace-nowrap">
                        @if($expense->is_zero_rated)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-700 font-medium">0%</span>
                        @elseif($expense->vat_amount > 0)
                            <span class="font-mono text-gray-600">Tsh {{ number_format($expense->vat_amount, 0) }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold font-mono text-gray-900 whitespace-nowrap">Tsh {{ number_format($expense->total_amount, 0) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($expense->attachment)
                        <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank"
                           class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="View attachment">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </a>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($expense->bankTransaction)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-emerald-50 text-emerald-700 font-mono font-medium" title="Bank reconciled — {{ $expense->bankTransaction->reference ?? $expense->bankTransaction->description }}">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $expense->bankTransaction->reference ? \Illuminate\Support\Str::limit($expense->bankTransaction->reference, 12) : '✓' }}
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colors[$expense->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($expense->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.accounting.expenses.show', $expense) }}" class="text-xs text-blue-600 hover:underline">View</a>
                            @if($expense->status === 'draft')
                            @permission('create_expenses')
                            <a href="{{ route('admin.accounting.expenses.edit', $expense) }}" class="text-xs text-gray-600 hover:underline">Edit</a>
                            @endpermission
                            @permission('approve_expenses')
                            <form method="POST" action="{{ route('admin.accounting.expenses.approve', $expense) }}">
                                @csrf<button type="submit" class="text-xs text-green-600 hover:underline">Approve</button>
                            </form>
                            @endpermission
                            @elseif($expense->status === 'approved')
                            @permission('approve_expenses')
                            <form method="POST" action="{{ route('admin.accounting.expenses.post', $expense) }}">
                                @csrf<button type="submit" class="text-xs text-purple-600 hover:underline">Post</button>
                            </form>
                            @endpermission
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="px-4 py-10 text-center text-gray-400">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-pagination-bar :paginator="$expenses" :per-page="$perPage" />
    </div>
</x-admin-layout>
