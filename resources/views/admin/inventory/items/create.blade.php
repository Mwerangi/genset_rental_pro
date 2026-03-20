<x-admin-layout>
    @php $isEdit = isset($item); @endphp

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.inventory.items.index') }}" class="hover:text-red-600">Inventory</a>
        <span>/</span>
        <span>{{ $isEdit ? 'Edit '.$item->name : 'Add Item' }}</span>
    </div>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $isEdit ? 'Edit Item' : 'Add Inventory Item' }}</h1>

        @if($errors->any())
        <div class="mb-5 border rounded-xl p-4 text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.inventory.items.update', $item) : route('admin.inventory.items.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" value="{{ old('sku', $item->sku ?? '') }}" required
                               placeholder="e.g. OIL-15W40-1L"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                        <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required
                           placeholder="e.g. Engine Oil 15W-40 (1 Litre)"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description', $item->description ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                        <select name="unit" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            @foreach(['pieces','litres','kg','metres','sets','pairs','boxes'] as $u)
                                <option value="{{ $u }}" {{ old('unit', $item->unit ?? 'pieces') === $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Min Stock Level <span class="text-red-500">*</span></label>
                        <input type="number" name="min_stock_level" value="{{ old('min_stock_level', $item->min_stock_level ?? '0') }}" required min="0" step="0.001"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Cost (Tsh) <span class="text-red-500">*</span></label>
                        <input type="number" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost ?? '0') }}" required min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $item->notes ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

            </div>

            <div class="mt-5 flex gap-3 justify-end">
                <a href="{{ $isEdit ? route('admin.inventory.items.show', $item) : route('admin.inventory.items.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Discard</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                    {{ $isEdit ? 'Save Changes' : 'Add Item' }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
