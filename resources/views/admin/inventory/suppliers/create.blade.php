<x-admin-layout>
    @php $isEdit = isset($supplier); @endphp

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.suppliers.index') }}" class="hover:text-red-600">Suppliers</a>
        <span>/</span>
        <span>{{ $isEdit ? 'Edit '.$supplier->name : 'New Supplier' }}</span>
    </div>

    <div class="max-w-xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $isEdit ? 'Edit Supplier' : 'New Supplier' }}</h1>

        @if($errors->any())
        <div class="mb-5 border rounded-xl p-4 text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-5">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" value="{{ old('address', $supplier->address ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                </div>

                @if($isEdit)
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Active supplier</label>
                </div>
                @endif

            </div>

            <div class="mt-5 flex gap-3 justify-end">
                <a href="{{ route('admin.suppliers.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50">Discard</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">
                    {{ $isEdit ? 'Save Changes' : 'Add Supplier' }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
