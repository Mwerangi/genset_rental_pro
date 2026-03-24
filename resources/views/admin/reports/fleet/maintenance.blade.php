<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Maintenance Costs</h1>
        <p class="text-sm text-gray-500 mt-0.5">Completed maintenance costs by type and generator</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Types</option>
            @foreach($types as $t)
            <option value="{{ $t }}" @selected($type === $t)>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search title, generator…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[200px]">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Records</p>
            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Total Cost</p>
            <p class="text-2xl font-bold text-red-900 mt-1">Tsh {{ number_format($totals['total_cost'], 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Avg Cost</p>
            <p class="text-2xl font-bold text-orange-900 mt-1">Tsh {{ number_format($totals['avg_cost'], 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        <!-- By Type -->
        @if($byType->count())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-semibold text-gray-800">By Maintenance Type</p></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Type</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Count</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-red-600">Total Cost</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($byType as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-700">{{ ucwords(str_replace('_', ' ', $row['type'])) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $row['count'] }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($row['total_cost'], 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- By Generator -->
        @if($byGenset->count())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-semibold text-gray-800">By Generator</p></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Generator</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-gray-500">Jobs</th>
                    <th class="text-right px-4 py-2 text-xs font-medium text-red-600">Total Cost</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($byGenset as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-700">{{ $row['name'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $row['count'] }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900">{{ number_format($row['total_cost'], 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @if($records->total() > 0)
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <p class="font-semibold text-gray-800">Maintenance Records</p>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400">{{ $records->total() }} records</span>
                <a href="{{ route('admin.reports.fleet.maintenance.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Type</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Title</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-red-600">Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($records as $rec)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-500">{{ \Carbon\Carbon::parse($rec->completed_at)->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $rec->genset?->asset_number }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ ucwords(str_replace('_', ' ', $rec->type)) }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ $rec->title }}</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($rec->cost, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $records->links() }}</div>
    </div>
    @endif
</x-admin-layout>
