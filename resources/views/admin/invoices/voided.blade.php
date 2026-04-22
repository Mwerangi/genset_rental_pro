<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Invoices</a>
                <span class="text-gray-400">/</span>
                <span class="text-sm font-semibold text-gray-700">Voided / Cancelled</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Voided &amp; Cancelled Invoices</h1>
            <p class="text-gray-500 mt-1">Invoices that have been voided, declined, or written off</p>
        </div>
        <a href="{{ route('admin.invoices.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Active Invoices
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Void</p>
            <p class="text-3xl font-bold mt-1" style="color:#991b1b;">{{ $stats['void'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Declined</p>
            <p class="text-3xl font-bold mt-1" style="color:#7f1d1d;">{{ $stats['declined'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Written Off</p>
            <p class="text-3xl font-bold mt-1" style="color:#6b7280;">{{ $stats['written_off'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.invoices.voided') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, client, booking #..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all">All</option>
                    <option value="void" @selected(request('status') === 'void')>Void</option>
                    <option value="declined" @selected(request('status') === 'declined')>Declined</option>
                    <option value="written_off" @selected(request('status') === 'written_off')>Written Off</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.invoices.voided') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($invoices->isEmpty())
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 font-medium">No voided invoices found</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Invoice</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Client</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Booking</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Total</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Issue Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-bold font-mono text-red-600 hover:underline">{{ $invoice->invoice_number }}</a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $invoice->issue_date->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $invoice->client?->company_name ?? $invoice->client?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($invoice->booking)
                                <a href="{{ route('admin.bookings.show', $invoice->booking) }}" class="text-xs font-mono text-blue-600 hover:underline">{{ $invoice->booking->booking_number }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap" style="{{ $invoice->status_style }}">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-700">
                            {{ $invoice->formatAmount($invoice->total_amount, 0) }}
                            @if($invoice->currency === 'USD')
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold" style="background:#eff6ff;color:#1d4ed8;">USD</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <x-pagination-bar :paginator="$invoices" :per-page="$perPage" />
        @endif
    </div>
</x-admin-layout>
