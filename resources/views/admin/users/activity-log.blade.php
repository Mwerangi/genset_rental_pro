<x-admin-layout>
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.users.show', $user) }}" class="text-gray-500 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
            <p class="text-gray-500 mt-0.5">{{ $user->name }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Time</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Action</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-semibold">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">No activity found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
