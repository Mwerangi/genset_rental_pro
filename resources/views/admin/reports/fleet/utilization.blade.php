<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Fleet Utilization</h1>
        <p class="text-sm text-gray-500 mt-0.5">Days rented vs idle per generator over the selected period</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">From:</label>
        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <label class="text-sm font-medium text-gray-700">To:</label>
        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Apply</button>
        <span class="text-xs text-gray-400 ml-auto">Period: {{ $totalDays }} days</span>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Generators</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $summary['total_gensets'] }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Avg Utilization</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $summary['avg_utilization'] }}%</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">≥ 80% Utilized</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $summary['fully_utilized'] }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Idle (0%)</p>
            <p class="text-3xl font-bold text-orange-900 mt-1">{{ $summary['idle'] }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">KVA</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-green-700">Rented Days</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Idle Days</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Bookings</th>
                        <th class="px-4 py-2.5 text-xs font-medium text-gray-500">Utilization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.gensets.show', $row['id']) }}" class="font-medium text-gray-900 hover:text-red-600">{{ $row['asset_number'] }}</a>
                            <p class="text-xs text-gray-400">{{ $row['name'] }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ $row['kva_rating'] }} KVA</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $row['status']==='available' ? 'bg-green-100 text-green-700' : ($row['status']==='rented' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ ucfirst($row['status']) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium text-green-700">{{ $row['rented_days'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $row['idle_days'] }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ $row['booking_count'] }}</td>
                        <td class="px-4 py-2.5 min-w-[140px]">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $row['utilization'] >= 80 ? 'bg-green-500' : ($row['utilization'] >= 40 ? 'bg-yellow-500' : 'bg-red-400') }}"
                                         style="width: {{ min($row['utilization'], 100) }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700 w-10 text-right">{{ $row['utilization'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
