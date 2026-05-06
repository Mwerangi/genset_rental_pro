<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.accounting.expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Expenses</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Import Expenses from Excel / CSV</h1>
            <p class="text-sm text-gray-500 mt-1">Upload the filled Excel template — rows will be parsed into a preview for you to confirm.</p>
        </div>
        <a href="{{ route('admin.accounting.expenses.bulk-entry') }}"
           class="inline-flex items-center gap-2 text-sm border border-gray-300 rounded-lg px-4 py-2 text-gray-600 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
            </svg>
            Manual bulk entry instead
        </a>
    </div>

    {{-- Template download --}}
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
        </svg>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">How to fill the template</p>
            <ul class="list-disc list-inside space-y-0.5 text-blue-700 mb-3">
                <li>Download the Excel template below — column C has <strong>account codes (COA)</strong> pre-filled with your expense accounts; column D has bank accounts; column F has yes/no.</li>
                <li><strong>date</strong>: YYYY-MM-DD format (e.g. 2026-05-01)</li>
                <li><strong>amount</strong>: number only, no commas (e.g. 120000)</li>
                <li><strong>reference</strong>: optional free text</li>
                <li>You can also upload a plain <code class="bg-blue-100 px-1 rounded">.csv</code> file if you prefer.</li>
            </ul>
            <a href="{{ route('admin.accounting.expenses.bulk-import.template') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Excel template (.xlsx)
            </a>
        </div>
    </div>

    {{-- Upload form --}}
    <div class="max-w-lg bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.accounting.expenses.bulk-import.preview') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Excel or CSV File <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" accept=".xlsx,.csv,.txt"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                @error('csv_file')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                Parse & Preview
            </button>
        </form>
    </div>

    {{-- COA & bank account reference --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Available Expense Accounts (COA)</h3>
            <ul class="text-xs text-gray-600 space-y-0.5 max-h-48 overflow-y-auto font-mono">
                @foreach($accounts as $acct)
                <li>{{ $acct->code }} — {{ $acct->name }}</li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Available Bank Accounts</h3>
            <ul class="text-xs text-gray-600 space-y-0.5">
                @foreach($bankAccounts as $b)
                <li>{{ $b->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</x-admin-layout>
