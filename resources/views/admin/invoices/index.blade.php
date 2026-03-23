<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
            <p class="text-gray-500 mt-1">All invoices across all bookings</p>
        </div>
    </div>



    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Awaiting</p>
            <p class="text-3xl font-bold mt-1" style="color:#2563eb;">{{ $stats['sent'] + $stats['draft'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Part Paid</p>
            <p class="text-3xl font-bold mt-1" style="color:#d97706;">{{ $stats['partially_paid'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Overdue</p>
            <p class="text-3xl font-bold mt-1" style="color:#dc2626;">{{ $stats['overdue'] }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paid</p>
            <p class="text-3xl font-bold mt-1" style="color:#16a34a;">{{ $stats['paid'] }}</p>
        </div>
    </div>

    @if($stats['total_outstanding'] > 0)
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-900">Outstanding Balance</p>
            <p class="text-xs text-amber-700 mt-0.5">Total unpaid across all open invoices</p>
        </div>
        <p class="text-2xl font-bold text-amber-900">TZS {{ number_format($stats['total_outstanding'], 0) }} <span class="text-sm font-normal text-amber-700">(equiv.)</span></p>
    </div>
    @endif

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.invoices.index') }}" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, client, booking #..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all">All Statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="void" @selected(request('status') === 'void')>Void</option>
                    <option value="declined" @selected(request('status') === 'declined')>Declined</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#dc2626;">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($invoices->isEmpty())
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 font-medium">No invoices found</p>
                <p class="text-gray-400 text-sm mt-1">Generate invoices from approved bookings</p>
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
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Balance Due</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Due Date</th>
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
                            @if($invoice->is_overdue)
                                <p class="text-xs mt-0.5" style="color:#dc2626;">Overdue</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            {{ $invoice->formatAmount($invoice->total_amount, 0) }}
                            @if($invoice->currency === 'USD')
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold" style="background:#eff6ff;color:#1d4ed8;">USD</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($invoice->balance_due > 0)
                                <span class="font-semibold" style="color:#dc2626;">{{ $invoice->formatAmount($invoice->balance_due, 0) }}</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $invoice->due_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($invoices->hasPages())
                <div class="px-4 py-4 border-t border-gray-100">{{ $invoices->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>
</x-admin-layout>
