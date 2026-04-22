<x-admin-layout>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
            <p class="text-gray-500 mt-1">All active invoices across all bookings</p>
        </div>
        <a href="{{ route('admin.invoices.voided') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            Voided / Cancelled
        </a>
    </div>



    <!-- Stats — Count cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">invoices</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Awaiting</p>
            <p class="text-3xl font-bold mt-1" style="color:#2563eb;">{{ $stats['sent'] + $stats['draft'] }}</p>
            <p class="text-xs text-gray-400 mt-1">draft / sent</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Part Paid</p>
            <p class="text-3xl font-bold mt-1" style="color:#d97706;">{{ $stats['partially_paid'] }}</p>
            <p class="text-xs text-gray-400 mt-1">partially paid</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Overdue</p>
            <p class="text-3xl font-bold mt-1" style="color:#dc2626;">{{ $stats['overdue'] }}</p>
            <p class="text-xs text-gray-400 mt-1">past due date</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fully Paid</p>
            <p class="text-3xl font-bold mt-1" style="color:#16a34a;">{{ $stats['paid'] }}</p>
            <p class="text-xs text-gray-400 mt-1">invoices</p>
        </div>
    </div>

    <!-- Stats — Financial summary cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border px-5 py-4 shadow-sm flex items-center gap-4" style="background:#f0fdf4;border-color:#86efac;">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#dcfce7;">
                <svg class="w-5 h-5" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:#166534;">Total Collected</p>
                <p class="text-xl font-bold mt-0.5" style="color:#15803d;">TZS {{ number_format($stats['total_paid'], 0) }}</p>
                <p class="text-xs mt-0.5" style="color:#4ade80;">Payments received (excl. voided)</p>
            </div>
        </div>
        <div class="rounded-xl border px-5 py-4 shadow-sm flex items-center gap-4" style="background:#fff7ed;border-color:#fdba74;">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#ffedd5;">
                <svg class="w-5 h-5" style="color:#ea580c;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:#9a3412;">Outstanding Balance</p>
                <p class="text-xl font-bold mt-0.5" style="color:#ea580c;">TZS {{ number_format($stats['total_outstanding'], 0) }}</p>
                <p class="text-xs mt-0.5" style="color:#fb923c;">Unpaid across open invoices</p>
            </div>
        </div>
        <div class="rounded-xl border px-5 py-4 shadow-sm flex items-center gap-4" style="background:#f8fafc;border-color:#e2e8f0;">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#f1f5f9;">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Invoiced</p>
                <p class="text-xl font-bold text-gray-900 mt-0.5">TZS {{ number_format($stats['total_invoiced'], 0) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Gross value of all active invoices</p>
            </div>
        </div>
    </div>

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
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Paid</th>
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
                            @if($invoice->amount_paid > 0)
                                <span class="font-semibold text-green-700">{{ $invoice->formatAmount($invoice->amount_paid, 0) }}</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
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
            <x-pagination-bar :paginator="$invoices" :per-page="$perPage" />
        @endif
    </div>
</x-admin-layout>
