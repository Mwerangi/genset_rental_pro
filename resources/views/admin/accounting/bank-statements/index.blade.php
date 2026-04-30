<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bank Statements</h1>
            <p class="text-gray-500 mt-1">Post unrecorded bank transactions to the journal</p>
        </div>
        <a href="{{ route('admin.accounting.bank-statements.create') }}"
           class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Statement
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Reference</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Bank Account</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Period</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Transactions</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Pending</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Posted</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Created By</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($statements as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $s->reference ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $s->bankAccount->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        @if($s->period_from || $s->period_to)
                            {{ $s->period_from?->format('d M Y') }} – {{ $s->period_to?->format('d M Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-gray-700">{{ $s->transactions_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($s->pending_count > 0)
                        <span class="bg-yellow-100 text-yellow-700 rounded-full px-2 py-0.5 text-xs font-medium">{{ $s->pending_count }}</span>
                        @else
                        <span class="text-gray-400 text-xs">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">{{ $s->posted_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->createdBy->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $s->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.accounting.bank-statements.show', $s) }}" class="text-red-600 hover:text-red-700 text-xs font-medium">Open →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-gray-400 italic">No bank statements yet. Click "New Statement" to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- Pagination bar --}}
    <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">

        {{-- Left: showing X–Y of Z + per-page selector --}}
        <div class="flex items-center gap-3 text-sm text-gray-600">
            <span>
                Showing
                <strong>{{ $statements->firstItem() ?? 0 }}</strong>–<strong>{{ $statements->lastItem() ?? 0 }}</strong>
                of <strong>{{ $statements->total() }}</strong> statements
            </span>

            <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
                <label class="text-xs text-gray-500">Show</label>
                <select name="per_page" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @foreach([10, 20, 50, 100] as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-500">per page</span>
            </form>
        </div>

        {{-- Right: jump-to + prev/next --}}
        <div class="flex items-center gap-2">
            @if($statements->lastPage() > 1)
            <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <label class="text-xs text-gray-500 whitespace-nowrap">Jump to</label>
                <select name="page" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @for($p = 1; $p <= $statements->lastPage(); $p++)
                        @php
                            $from = (($p - 1) * $perPage) + 1;
                            $to   = min($p * $perPage, $statements->total());
                        @endphp
                        <option value="{{ $p }}" @selected($statements->currentPage() === $p)>
                            {{ number_format($from) }}–{{ number_format($to) }}
                        </option>
                    @endfor
                </select>
            </form>

            @if($statements->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $statements->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">← Prev</a>
            @endif

            @if($statements->hasMorePages())
                <a href="{{ $statements->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Next →</a>
            @else
                <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">Next →</span>
            @endif
            @endif
        </div>
    </div>
</x-admin-layout>
