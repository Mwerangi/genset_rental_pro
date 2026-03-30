<x-admin-layout>
    <div x-data="{
        showAdd: false,
        editId: null,
        editLabel: '',
        editIsRental: false,
        editIsActive: true,
        editSortOrder: 0,
        openEdit(id, label, isRental, isActive, sortOrder) {
            this.editId = id;
            this.editLabel = label;
            this.editIsRental = isRental;
            this.editIsActive = isActive;
            this.editSortOrder = sortOrder;
        },
        closeEdit() { this.editId = null; }
    }">

        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Line Item Types</h1>
                <p class="text-gray-500 mt-0.5">Define the service types available when building quotation line items</p>
            </div>
            <button @click="showAdd = !showAdd"
                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Item Type
            </button>
        </div>

        @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-800">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Add New Form --}}
        <div x-show="showAdd" x-transition x-cloak class="mb-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h2 class="text-base font-semibold text-slate-900 mb-4">New Item Type</h2>
                <form method="POST" action="{{ route('admin.item-types.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Key <span class="text-red-500">*</span>
                            <span class="text-slate-400 font-normal text-xs ml-1">(lowercase_underscored)</span>
                        </label>
                        <input type="text" name="key" value="{{ old('key') }}"
                            placeholder="e.g. transport_fee"
                            pattern="[a-z0-9_]+"
                            title="Only lowercase letters, numbers and underscores"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                        @error('key') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Label <span class="text-red-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}"
                            placeholder="e.g. Transport Fee"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                        @error('label') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" name="is_rental" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Has Duration (days)
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                Add
                            </button>
                            <button type="button" @click="showAdd = false" class="border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-medium px-4 py-2 rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left bg-slate-50">
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 w-8">#</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Key</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Label</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 text-center">Rental Type</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($itemTypes as $type)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $type->sort_order }}</td>
                            <td class="px-5 py-3">
                                <code class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-mono">{{ $type->key }}</code>
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $type->label }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($type->is_rental)
                                    <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5 font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Has Duration
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($type->is_active)
                                    <span class="inline-flex items-center text-xs bg-green-50 text-green-700 border border-green-200 rounded-full px-2.5 py-0.5 font-medium">Active</span>
                                @else
                                    <span class="inline-flex items-center text-xs bg-slate-100 text-slate-500 border border-slate-200 rounded-full px-2.5 py-0.5 font-medium">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit({{ $type->id }}, {{ Js::from($type->label) }}, {{ $type->is_rental ? 'true' : 'false' }}, {{ $type->is_active ? 'true' : 'false' }}, {{ $type->sort_order }})"
                                        class="text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.item-types.destroy', $type) }}"
                                          onsubmit="return confirm('Delete \'{{ addslashes($type->label) }}\'? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">
                                No item types defined yet.
                                <button @click="showAdd = true" class="text-red-600 hover:underline ml-1">Add one now</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-slate-400">
            Tip: "Has Duration" types display a <em>Duration (Days)</em> field in the quotation builder and include days in the subtotal calculation.
        </p>

        {{-- Edit Modal --}}
        <div x-show="editId !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
             @keydown.escape.window="closeEdit()">
            <div @click.stop class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-semibold text-slate-900">Edit Item Type</h3>
                    <button @click="closeEdit()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-for="type in {{ Js::from($itemTypes) }}" :key="type.id">
                    <form x-show="editId === type.id"
                          :action="'{{ url('admin/settings/item-types') }}/' + type.id"
                          method="POST" class="space-y-4">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Label <span class="text-red-500">*</span></label>
                            <input type="text" name="label" x-model="editLabel"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" x-model.number="editSortOrder" min="0"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="is_rental" value="1"
                                    x-model="editIsRental"
                                    class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                Has Duration (days)
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="is_active" value="1"
                                    x-model="editIsActive"
                                    class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                Active
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeEdit()" class="border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-medium px-4 py-2 rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded-lg">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-admin-layout>
