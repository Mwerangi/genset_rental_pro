<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Overdue Servicing</h1>
        <p class="text-sm text-gray-500 mt-0.5">Generators past their scheduled service date or run-hour threshold</p>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium text-gray-700">As at:</label>
        <input type="date" name="as_at" value="{{ $asAt }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search asset number, name…" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[200px]">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Show</button>
    </form>

    @if($gensets->total() === 0)
        <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
            <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-green-800 font-medium">All generators are within service schedule as at {{ \Carbon\Carbon::parse($asAt)->format('d F Y') }}</p>
        </div>
    @else
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 flex items-center gap-2 text-sm text-red-800">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <strong>{{ $gensets->total() }}</strong> generator{{ $gensets->total() !== 1 ? 's' : '' }} require servicing.
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-end">
            <a href="{{ route('admin.reports.fleet.overdue-service.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">↓ Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Generator</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Run Hours</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Service Interval</th>
                        <th class="text-center px-4 py-2.5 text-xs font-medium text-red-600">Next Service Date</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Days Overdue</th>
                        <th class="px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($gensets as $g)
                    @php
                        $daysOverdue = $g->next_service_date ? \Carbon\Carbon::parse($g->next_service_date)->diffInDays(\Carbon\Carbon::parse($asAt), false) : null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.gensets.show', $g->id) }}" class="font-medium text-gray-900 hover:text-red-600">{{ $g->asset_number }}</a>
                            <p class="text-xs text-gray-400">{{ $g->name }} · {{ $g->kva_rating }} KVA</p>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $g->status==='available' ? 'bg-green-100 text-green-700' : ($g->status==='rented' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ ucfirst($g->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($g->run_hours ?? 0) }} h</td>
                        <td class="px-4 py-2.5 text-right text-gray-500">{{ $g->service_interval_hours ? number_format($g->service_interval_hours) . ' h' : '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-red-600 font-medium text-xs">
                            {{ $g->next_service_date ? \Carbon\Carbon::parse($g->next_service_date)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold text-red-600">
                            {{ $daysOverdue !== null && $daysOverdue > 0 ? '+' . $daysOverdue . ' days' : '—' }}
                        </td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.maintenance.create') }}?genset_id={{ $g->id }}" class="text-xs text-red-600 hover:underline font-medium">Schedule</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $gensets->links() }}</div>
    </div>
    @endif
</x-admin-layout>
