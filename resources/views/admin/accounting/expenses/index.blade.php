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
            @permission('create_expenses')
            <div x-data="{ open: false }" class="relative">
                <button x-on:click="open = !open"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Bulk
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-on:click.outside="open = false" x-cloak
                    class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-20 text-sm overflow-hidden">
                    <a href="{{ route('admin.accounting.expenses.bulk-entry') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                        Bulk Entry Form
                    </a>
                    <a href="{{ route('admin.accounting.expenses.bulk-import') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-gray-50 border-t border-gray-100">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import from Excel / CSV
                    </a>
                </div>
            </div>
            @endpermission
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
    <div x-data="bulkExpenses()">

    {{-- Bulk action bar (visible when rows selected) --}}
    @permission('approve_expenses')
    <div x-show="selected.length > 0" x-cloak
         class="mb-3 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
        <span class="text-sm font-medium text-amber-800" x-text="selected.length + ' expense(s) selected'"></span>

        {{-- Approve button — only when draft rows are selected --}}
        <button type="button"
            x-show="draftSelected.length > 0"
            x-on:click="showModal = true"
            class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Approve (<span x-text="draftSelected.length"></span>)
        </button>

        {{-- Post button — only when approved rows are selected --}}
        <button type="button"
            x-show="approvedSelected.length > 0"
            x-on:click="showPostModal = true"
            class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Post to Ledger (<span x-text="approvedSelected.length"></span>)
        </button>

        <button type="button" x-on:click="selected = []"
            class="text-sm text-amber-700 hover:text-amber-900 underline ml-1">Clear selection</button>
    </div>

    {{-- Bulk approve confirmation modal --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         x-on:keydown.escape.window="showModal = false">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" x-on:click="showModal = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Approve expenses?</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        You are about to approve
                        <span class="font-semibold text-gray-800" x-text="selected.length"></span>
                        draft expense(s). They will be moved to <span class="font-medium text-blue-700">Approved</span> status and will be ready to post to the ledger.
                    </p>
                    <p class="mt-2 text-xs text-gray-400">This action can be reversed by rejecting individual expenses.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.accounting.expenses.bulk-approve') }}" x-ref="bulkApproveForm">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    x-on:click="showModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button"
                    x-on:click="$refs.bulkApproveForm.submit()"
                    class="px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg">
                    Yes, Approve
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk post confirmation modal --}}
    <div x-show="showPostModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         x-on:keydown.escape.window="showPostModal = false">
        <div class="absolute inset-0 bg-black/40" x-on:click="showPostModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Post expenses to ledger?</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        You are about to post
                        <span class="font-semibold text-gray-800" x-text="approvedSelected.length"></span>
                        approved expense(s). For each one a journal entry will be created:
                    </p>
                    <ul class="mt-2 text-xs text-gray-500 list-disc list-inside space-y-0.5">
                        <li><span class="font-medium text-red-700">DR</span> Expense account (e.g. Office Supplies)</li>
                        <li><span class="font-medium text-blue-700">CR</span> Payment account (e.g. Petty Cash)</li>
                    </ul>
                    <p class="mt-2 text-xs text-gray-400">This reduces the petty cash / bank balance in the ledger. Cannot be undone without reversing the JE.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.accounting.expenses.bulk-post') }}" x-ref="bulkPostForm">
                @csrf
                <template x-for="id in approvedSelected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    x-on:click="showPostModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button"
                    x-on:click="$refs.bulkPostForm.submit()"
                    class="px-5 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg">
                    Yes, Post to Ledger
                </button>
            </div>
        </div>
    </div>
    @endpermission

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" class="w-4 h-4 accent-red-600 cursor-pointer"
                            x-on:change="toggleAll($event)"
                            x-bind:checked="allChecked"
                            x-bind:indeterminate="someChecked">
                    </th>
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
                <tr class="hover:bg-gray-50" x-bind:class="selected.includes({{ $expense->id }}) ? 'bg-amber-50' : ''">
                    <td class="px-4 py-3">
                        @if(in_array($expense->status, ['draft', 'approved']))
                        <input type="checkbox" class="w-4 h-4 accent-red-600 cursor-pointer"
                            :value="{{ $expense->id }}"
                            x-on:change="toggle({{ $expense->id }})"
                            x-bind:checked="selected.includes({{ $expense->id }})">
                        @else
                        <span class="block w-4"></span>
                        @endif
                    </td>
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
                            <form method="POST" action="{{ route('admin.accounting.expenses.reject', $expense) }}">
                                @csrf<button type="submit" class="text-xs text-amber-600 hover:underline">Reject</button>
                            </form>
                            <form method="POST" action="{{ route('admin.accounting.expenses.post', $expense) }}">
                                @csrf<button type="submit" class="text-xs text-purple-600 hover:underline">Post</button>
                            </form>
                            @endpermission
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="13" class="px-4 py-10 text-center text-gray-400">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-pagination-bar :paginator="$expenses" :per-page="$perPage" />
    </div>
    </div>{{-- end x-data --}}

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bulkExpenses', () => ({
            selected: [],
            showModal: false,
            showPostModal: false,
            draftIds: @json($expenses->where('status', 'draft')->pluck('id')->values()),
            approvedIds: @json($expenses->where('status', 'approved')->pluck('id')->values()),
            get allSelectableIds() { return [...this.draftIds, ...this.approvedIds]; },
            get draftSelected() { return this.selected.filter(id => this.draftIds.includes(id)); },
            get approvedSelected() { return this.selected.filter(id => this.approvedIds.includes(id)); },
            get allChecked() { return this.allSelectableIds.length > 0 && this.selected.length === this.allSelectableIds.length; },
            get someChecked() { return this.selected.length > 0 && this.selected.length < this.allSelectableIds.length; },
            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            },
            toggleAll(e) {
                this.selected = e.target.checked ? [...this.allSelectableIds] : [];
            },
        }));
    });
    </script>
</x-admin-layout>
