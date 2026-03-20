<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Expense Categories</h1>
            <p class="text-gray-500 mt-1">Link categories to ledger accounts</p>
        </div>
        <a href="{{ route('admin.accounting.expense-categories.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Category
        </a>
    </div>

    @if(session('success'))<div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Linked Account</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Expenses</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        @if($cat->account)
                        <span class="font-mono">{{ $cat->account->code }}</span> {{ $cat->account->name }}
                        @else
                        <span class="text-gray-400 italic">Not mapped</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $cat->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $cat->expenses_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($cat->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-50 text-green-700">Active</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.accounting.expense-categories.edit', $cat) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.accounting.expense-categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No categories yet. <a href="{{ route('admin.accounting.expense-categories.create') }}" class="text-red-600 hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
