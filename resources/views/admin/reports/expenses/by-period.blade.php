<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Expenses by Period</h1>
        <p class="text-sm text-gray-500 mt-0.5">Monthly expenditure trend with optional category filter</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[180px]">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Apply</button>
    </form>

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Expenses (period)</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['total'] ?? 0) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total Entries</p>
            <p class="text-xl font-bold text-gray-700 mt-1">{{ $totals['count'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-end">
            <a href="{{ route('admin.reports.expenses.by-period.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Month</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Entries</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Amount (TZS)</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $maxVal = $monthly->max('total') ?: 1; @endphp
                    @forelse($monthly as $row)
                    @php $barWidth = round($row['total'] / $maxVal * 100); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $row['month'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ $row['count'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-900">{{ number_format($row['total']) }}</td>
                        <td class="px-4 py-2.5 w-40">
                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-orange-400" style="width: {{ $barWidth }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-gray-400">No expenses found for this period.</td></tr>
                    @endforelse
                </tbody>
                @if(!$monthly->isEmpty())
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-gray-700">Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-700">{{ $totals['count'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-gray-900">{{ number_format($totals['total'] ?? 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-admin-layout>
