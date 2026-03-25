<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Item Categories</h1>
            <p class="text-gray-500 mt-1">Organise your inventory into groups</p>
        </div>
        @permission('manage_inventory_categories')
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Category
        </button>
        @endpermission
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($categories->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <p class="text-sm">No categories yet. Create one to start organising your inventory.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">COA Account</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold text-gray-800">{{ $cat->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $cat->description ?: '—' }}</td>
                        <td class="px-6 py-3 text-xs text-gray-500">
                            @if($cat->account)
                                <span class="font-mono">{{ $cat->account->code }}</span> {{ $cat->account->name }}
                            @else
                                <span class="text-gray-300">1150 (default)</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.inventory.items.index', ['category_id' => $cat->id]) }}" class="text-red-600 hover:underline">{{ $cat->items_count }} item{{ $cat->items_count !== 1 ? 's' : '' }}</a>
                        </td>
                        <td class="px-6 py-3 text-right flex gap-3 justify-end">
                            @permission('manage_inventory_categories')
                            <button onclick='openEditModal({{ $cat->id }}, {{ json_encode($cat->name) }}, {{ json_encode($cat->description) }}, {{ $cat->account_id ?? "null" }})' class="text-xs text-gray-600 hover:text-gray-900 underline">Edit</button>
                            <form method="POST" action="{{ route('admin.inventory.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 underline">Delete</button>
                            </form>
                            @endpermission
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Create Modal --}}
    <div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">New Category</h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.categories.store') }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="e.g. Lubricants, Filters, Electrical...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">COA Account (for PO receipts)</label>
                        <select name="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Default: 1150 Inventory —</option>
                            @foreach($coaAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Determines which ledger account is debited when items from this category are received on a PO.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">Edit Category</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="editName" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" id="editDesc" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">COA Account (for PO receipts)</label>
                        <select id="editAccountId" name="account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— Default: 1150 Inventory —</option>
                            @foreach($coaAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 pb-5">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(id, name, desc, accountId) {
        document.getElementById('editForm').action = '/admin/inventory/categories/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editDesc').value = desc || '';
        const sel = document.getElementById('editAccountId');
        sel.value = accountId !== null ? String(accountId) : '';
        document.getElementById('editModal').classList.remove('hidden');
    }
    </script>
</x-admin-layout>
