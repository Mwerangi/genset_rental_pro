<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\CashRequest;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FuelLog;
use App\Models\Genset;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntryLine;
use App\Models\MaintenanceRecord;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Accounts Receivable Aging Report.
     * Groups all outstanding invoices by client and overdue bucket.
     */
    public function aging(Request $request)
    {
        $asAt = $request->get('as_at', now()->toDateString());
        $asAtDate = Carbon::parse($asAt)->endOfDay();

        // All unpaid/partially-paid invoices as at the selected date
        $invoices = Invoice::with('client')
            ->whereIn('status', ['sent', 'partially_paid', 'disputed', 'draft'])
            ->where('issue_date', '<=', $asAtDate)
            ->orderBy('due_date')
            ->get();

        // Build per-client aging rows
        $clients = [];

        foreach ($invoices as $inv) {
            $balance = max(0, (float) $inv->total_amount - (float) $inv->amount_paid);
            if ($balance <= 0) continue;

            $clientId = $inv->client_id ?? 0;
            $clientName = $inv->client?->company_name ?: ($inv->client?->full_name ?? 'Unknown');

            if (!isset($clients[$clientId])) {
                $clients[$clientId] = [
                    'id'       => $clientId,
                    'name'     => $clientName,
                    'email'    => $inv->client?->email ?? '',
                    'current'  => 0,  // not yet due
                    'days_1_30'  => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'days_90plus' => 0,
                    'total'    => 0,
                    'invoices' => [],
                ];
            }

            // Days overdue as at the selected date
            $daysOverdue = $inv->due_date
                ? (int) $inv->due_date->diffInDays($asAtDate, false)
                : null;

            $bucket = 'current';
            if ($daysOverdue === null || $daysOverdue <= 0) {
                $bucket = 'current';
            } elseif ($daysOverdue <= 30) {
                $bucket = 'days_1_30';
            } elseif ($daysOverdue <= 60) {
                $bucket = 'days_31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = 'days_61_90';
            } else {
                $bucket = 'days_90plus';
            }

            $clients[$clientId][$bucket]  += $balance;
            $clients[$clientId]['total']  += $balance;
            $clients[$clientId]['invoices'][] = [
                'invoice_number' => $inv->invoice_number,
                'issue_date'     => $inv->issue_date,
                'due_date'       => $inv->due_date,
                'total_amount'   => $inv->total_amount,
                'amount_paid'    => $inv->amount_paid,
                'balance'        => $balance,
                'days_overdue'   => $daysOverdue,
                'bucket'         => $bucket,
                'status'         => $inv->status,
                'invoice_id'     => $inv->id,
            ];
        }

        // Sort clients by total outstanding descending
        usort($clients, fn($a, $b) => $b['total'] <=> $a['total']);

        // Grand totals
        $totals = [
            'current'     => array_sum(array_column($clients, 'current')),
            'days_1_30'   => array_sum(array_column($clients, 'days_1_30')),
            'days_31_60'  => array_sum(array_column($clients, 'days_31_60')),
            'days_61_90'  => array_sum(array_column($clients, 'days_61_90')),
            'days_90plus' => array_sum(array_column($clients, 'days_90plus')),
            'total'       => array_sum(array_column($clients, 'total')),
        ];

        return view('admin.accounting.reports.aging', compact('clients', 'totals', 'asAt'));
    }

    /**
     * Statement of Accounts for a specific client.
     */
    public function statement(Request $request)
    {
        $clientId = $request->get('client_id');
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $clientsList = Client::orderBy('company_name')->orderBy('full_name')->get(['id', 'company_name', 'full_name', 'email', 'phone']);

        $client  = null;
        $lines   = collect();

        // Per-currency opening balances, running totals, closing balances
        $opening  = ['USD' => 0.0, 'TZS' => 0.0];
        $invoiced = ['USD' => 0.0, 'TZS' => 0.0];
        $paid     = ['USD' => 0.0, 'TZS' => 0.0];
        $closing  = ['USD' => 0.0, 'TZS' => 0.0];

        if ($clientId) {
            $client = Client::findOrFail($clientId);

            // Opening balance per currency — unpaid invoices before the period
            $openingInvoices = Invoice::where('client_id', $clientId)
                ->whereIn('status', ['sent', 'partially_paid', 'disputed', 'draft'])
                ->where('issue_date', '<', $from)
                ->get();

            foreach ($openingInvoices as $i) {
                $ccy = $i->currency === 'USD' ? 'USD' : 'TZS';
                $opening[$ccy] += max(0, (float)$i->total_amount - (float)$i->amount_paid);
            }

            // Period invoices
            $invoices = Invoice::with('payments')
                ->where('client_id', $clientId)
                ->whereBetween('issue_date', [$from, $to])
                ->orderBy('issue_date')
                ->orderBy('invoice_number')
                ->get();

            // Running balances start at opening balances
            $running = ['USD' => $opening['USD'], 'TZS' => $opening['TZS']];

            foreach ($invoices as $inv) {
                $ccy    = $inv->currency === 'USD' ? 'USD' : 'TZS';
                $amount = (float)$inv->total_amount;

                $running[$ccy]  += $amount;
                $invoiced[$ccy] += $amount;

                $lines->push([
                    'date'        => $inv->issue_date,
                    'type'        => 'invoice',
                    'reference'   => $inv->invoice_number,
                    'description' => 'Invoice',
                    'currency'    => $ccy,
                    'debit'       => $amount,
                    'credit'      => 0.0,
                    'balance_usd' => $running['USD'],
                    'balance_tzs' => $running['TZS'],
                    'status'      => $inv->status,
                    'id'          => $inv->id,
                ]);

                foreach ($inv->payments as $pmt) {
                    if ($pmt->payment_date->between($from, $to)) {
                        $pmtAmt         = (float)$pmt->amount;
                        $running[$ccy] -= $pmtAmt;
                        $paid[$ccy]    += $pmtAmt;

                        $lines->push([
                            'date'        => $pmt->payment_date,
                            'type'        => 'payment',
                            'reference'   => $pmt->reference ?? 'PMT-' . $pmt->id,
                            'description' => 'Payment — ' . ucfirst(str_replace('_', ' ', $pmt->payment_method ?? '')),
                            'currency'    => $ccy,
                            'debit'       => 0.0,
                            'credit'      => $pmtAmt,
                            'balance_usd' => $running['USD'],
                            'balance_tzs' => $running['TZS'],
                            'status'      => null,
                            'id'          => null,
                        ]);
                    }
                }
            }

            $lines = $lines->sortBy('date')->values();

            $closing['USD'] = $opening['USD'] + $invoiced['USD'] - $paid['USD'];
            $closing['TZS'] = $opening['TZS'] + $invoiced['TZS'] - $paid['TZS'];
        }

        return view('admin.accounting.reports.statement', compact(
            'clientsList', 'client', 'lines',
            'opening', 'invoiced', 'paid', 'closing',
            'from', 'to', 'clientId'
        ));
    }

    /**
     * Download Statement of Accounts as PDF.
     */
    public function statementPdf(Request $request)
    {
        $clientId = $request->get('client_id');
        $from     = $request->get('from', now()->startOfYear()->toDateString());
        $to       = $request->get('to', now()->toDateString());

        abort_if(!$clientId, 400, 'Client required.');

        $client = Client::findOrFail($clientId);

        $opening  = ['USD' => 0.0, 'TZS' => 0.0];
        $invoiced = ['USD' => 0.0, 'TZS' => 0.0];
        $paid     = ['USD' => 0.0, 'TZS' => 0.0];
        $closing  = ['USD' => 0.0, 'TZS' => 0.0];
        $lines    = collect();

        $openingInvoices = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'partially_paid', 'disputed', 'draft'])
            ->where('issue_date', '<', $from)
            ->get();

        foreach ($openingInvoices as $i) {
            $ccy = $i->currency === 'USD' ? 'USD' : 'TZS';
            $opening[$ccy] += max(0, (float)$i->total_amount - (float)$i->amount_paid);
        }

        $invoices = Invoice::with('payments')
            ->where('client_id', $clientId)
            ->whereBetween('issue_date', [$from, $to])
            ->orderBy('issue_date')
            ->orderBy('invoice_number')
            ->get();

        $running = ['USD' => $opening['USD'], 'TZS' => $opening['TZS']];

        foreach ($invoices as $inv) {
            $ccy    = $inv->currency === 'USD' ? 'USD' : 'TZS';
            $amount = (float)$inv->total_amount;

            $running[$ccy]  += $amount;
            $invoiced[$ccy] += $amount;

            $lines->push([
                'date'        => $inv->issue_date,
                'type'        => 'invoice',
                'reference'   => $inv->invoice_number,
                'description' => 'Invoice',
                'currency'    => $ccy,
                'debit'       => $amount,
                'credit'      => 0.0,
                'balance_usd' => $running['USD'],
                'balance_tzs' => $running['TZS'],
                'status'      => $inv->status,
            ]);

            foreach ($inv->payments as $pmt) {
                if ($pmt->payment_date->between($from, $to)) {
                    $pmtAmt         = (float)$pmt->amount;
                    $running[$ccy] -= $pmtAmt;
                    $paid[$ccy]    += $pmtAmt;

                    $lines->push([
                        'date'        => $pmt->payment_date,
                        'type'        => 'payment',
                        'reference'   => $pmt->reference ?? 'PMT-' . $pmt->id,
                        'description' => 'Payment — ' . ucfirst(str_replace('_', ' ', $pmt->payment_method ?? '')),
                        'currency'    => $ccy,
                        'debit'       => 0.0,
                        'credit'      => $pmtAmt,
                        'balance_usd' => $running['USD'],
                        'balance_tzs' => $running['TZS'],
                        'status'      => null,
                    ]);
                }
            }
        }

        $lines = $lines->sortBy('date')->values();

        $closing['USD'] = $opening['USD'] + $invoiced['USD'] - $paid['USD'];
        $closing['TZS'] = $opening['TZS'] + $invoiced['TZS'] - $paid['TZS'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.accounting.reports.statement-pdf', compact(
            'client', 'lines', 'opening', 'invoiced', 'paid', 'closing', 'from', 'to'
        ));
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Statement_' . str_replace([' ', '/'], '_', $client->company_name ?: $client->full_name)
            . '_' . $from . '_to_' . $to . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Payables Register — all open Purchase Orders with AP exposure.
     * Gives the accounts team visibility of committed spend and outstanding balances.
     */
    public function payables(Request $request)
    {
        $statusFilter = $request->get('status', 'open');

        $query = PurchaseOrder::with(['supplier', 'items', 'supplierPayments'])
            ->orderByDesc('created_at');

        if ($statusFilter === 'open') {
            $query->whereNotIn('status', ['cancelled']);
        } elseif ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $pos = $query->get()->map(function (PurchaseOrder $po) {
            $committedValue = round(
                $po->items->sum(fn ($i) => $i->quantity_ordered * $i->unit_cost), 2
            );
            $receivedValue = round(
                $po->items->sum(fn ($i) => $i->quantity_received * $i->unit_cost), 2
            );
            $totalPaid  = round((float) $po->supplierPayments->sum('amount'), 2);
            $balanceDue = max(0, $receivedValue - $totalPaid);

            return (object) [
                'id'              => $po->id,
                'po_number'       => $po->po_number,
                'supplier'        => $po->supplier,
                'status'          => $po->status,
                'status_label'    => $po->status_label,
                'status_style'    => $po->status_style,
                'ordered_at'      => $po->ordered_at,
                'expected_at'     => $po->expected_at,
                'committed_value' => $committedValue,
                'received_value'  => $receivedValue,
                'total_paid'      => $totalPaid,
                'balance_due'     => $balanceDue,
            ];
        });

        $totals = [
            'committed'   => $pos->whereIn('status', ['draft', 'sent'])->sum('committed_value'),
            'received'    => $pos->whereIn('status', ['partial', 'received'])->sum('received_value'),
            'balance_due' => $pos->sum('balance_due'),
            'total_paid'  => $pos->sum('total_paid'),
        ];

        return view('admin.accounting.reports.payables', compact('pos', 'totals', 'statusFilter'));
    }

    /**
     * Profit & Loss Statement.
     * Revenue and expenses from posted JE lines within a date range.
     */
    public function profitLoss(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $base = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$from, $to]);

        // Revenue: credit-normal — net = SUM(credit) - SUM(debit)
        $revenueRows = (clone $base)
            ->where('accounts.type', 'revenue')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->selectRaw('accounts.id, accounts.code, accounts.name,
                SUM(journal_entry_lines.credit) - SUM(journal_entry_lines.debit) AS net')
            ->orderBy('accounts.code')
            ->get();

        // Expenses: debit-normal — net = SUM(debit) - SUM(credit)
        $expenseRows = (clone $base)
            ->where('accounts.type', 'expense')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->selectRaw('accounts.id, accounts.code, accounts.name,
                SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit) AS net')
            ->orderBy('accounts.code')
            ->get();

        $totalRevenue  = $revenueRows->sum('net');
        $totalExpenses = $expenseRows->sum('net');
        $netProfit     = $totalRevenue - $totalExpenses;

        return view('admin.accounting.reports.profit-loss', compact(
            'revenueRows', 'expenseRows',
            'totalRevenue', 'totalExpenses', 'netProfit',
            'from', 'to'
        ));
    }

    /**
     * Balance Sheet.
     * Account balances as at a given date, computed from posted JE lines.
     */
    public function balanceSheet(Request $request)
    {
        $asAt = $request->get('as_at', now()->toDateString());

        // Aggregate posted JE lines up to $asAt per account
        $aggregates = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asAt)
            ->whereIn('accounts.type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->selectRaw('accounts.id, accounts.code, accounts.name, accounts.type, accounts.normal_balance,
                SUM(journal_entry_lines.debit)  AS total_debit,
                SUM(journal_entry_lines.credit) AS total_credit')
            ->orderBy('accounts.code')
            ->get();

        $assets      = collect();
        $liabilities = collect();
        $equity      = collect();

        // Retained earnings = revenue - expenses up to $asAt
        $retainedEarnings = 0;

        foreach ($aggregates as $row) {
            // Compute signed balance based on normal_balance convention
            $balance = $row->normal_balance === 'debit'
                ? (float) $row->total_debit - (float) $row->total_credit
                : (float) $row->total_credit - (float) $row->total_debit;

            if ($row->type === 'asset') {
                $assets->push((object) ['code' => $row->code, 'name' => $row->name, 'balance' => $balance]);
            } elseif ($row->type === 'liability') {
                $liabilities->push((object) ['code' => $row->code, 'name' => $row->name, 'balance' => $balance]);
            } elseif ($row->type === 'equity') {
                $equity->push((object) ['code' => $row->code, 'name' => $row->name, 'balance' => $balance]);
            } elseif ($row->type === 'revenue') {
                // Revenue credits increase retained earnings
                $retainedEarnings += (float) $row->total_credit - (float) $row->total_debit;
            } elseif ($row->type === 'expense') {
                // Expense debits reduce retained earnings
                $retainedEarnings -= (float) $row->total_debit - (float) $row->total_credit;
            }
        }

        $totalAssets      = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity      = $equity->sum('balance') + $retainedEarnings;
        $totalLiabEquity  = $totalLiabilities + $totalEquity;

        return view('admin.accounting.reports.balance-sheet', compact(
            'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'totalLiabEquity',
            'retainedEarnings', 'asAt'
        ));
    }

    // =========================================================================
    // SALES REPORTS
    // =========================================================================

    public function salesFunnel(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $stats = [
            'quote_requests'      => QuoteRequest::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count(),
            'quotations_created'  => Quotation::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count(),
            'quotations_accepted' => Quotation::where('status', 'accepted')->whereNotNull('accepted_at')->whereBetween('accepted_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count(),
            'bookings_created'    => Booking::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count(),
            'invoices_issued'     => Invoice::whereNotIn('status', ['voided'])->whereBetween('issue_date', [$from, $to])->count(),
            'revenue_tzs'         => Invoice::whereNotIn('status', ['voided', 'draft'])->whereBetween('issue_date', [$from, $to])->get()->sum(fn($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1)),
        ];

        $monthly = collect();
        $current = Carbon::parse($from)->startOfMonth();
        $end     = Carbon::parse($to)->endOfMonth();
        while ($current->lessThanOrEqualTo($end)) {
            $ms = $current->copy()->startOfMonth()->toDateString();
            $me = $current->copy()->endOfMonth()->toDateString();
            $monthly->push([
                'month'      => $current->format('M Y'),
                'requests'   => QuoteRequest::whereBetween('created_at', [$ms . ' 00:00:00', $me . ' 23:59:59'])->count(),
                'quotations' => Quotation::whereBetween('created_at', [$ms . ' 00:00:00', $me . ' 23:59:59'])->count(),
                'accepted'   => Quotation::where('status', 'accepted')->whereNotNull('accepted_at')->whereBetween('accepted_at', [$ms . ' 00:00:00', $me . ' 23:59:59'])->count(),
                'bookings'   => Booking::whereBetween('created_at', [$ms . ' 00:00:00', $me . ' 23:59:59'])->count(),
                'invoices'   => Invoice::whereNotIn('status', ['voided'])->whereBetween('issue_date', [$ms, $me])->count(),
            ]);
            $current->addMonth();
        }

        return view('admin.reports.sales.funnel', compact('stats', 'monthly', 'from', 'to'));
    }

    public function revenueByClient(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $sort   = $request->get('sort', 'invoiced_desc');
        $search = trim($request->get('search', ''));

        $clients = Client::with(['invoices' => function ($q) use ($from, $to) {
            $q->whereNotIn('status', ['voided'])->whereBetween('issue_date', [$from, $to]);
        }])->get()->map(function ($client) {
            $invoiced  = $client->invoices->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1));
            $collected = $client->invoices->sum(fn ($i) => $i->amount_paid * ($i->exchange_rate_to_tzs ?? 1));
            return ['id' => $client->id, 'name' => $client->company_name ?: $client->full_name, 'email' => $client->email, 'invoice_count' => $client->invoices->count(), 'invoiced' => $invoiced, 'collected' => $collected, 'outstanding' => max(0, $invoiced - $collected)];
        })->filter(fn ($c) => $c['invoiced'] > 0);

        if ($search !== '') {
            $clients = $clients->filter(fn ($c) =>
                str_contains(strtolower($c['name']), strtolower($search)) ||
                str_contains(strtolower($c['email'] ?? ''), strtolower($search))
            );
        }

        $clients = match ($sort) {
            'collected_desc'   => $clients->sortByDesc('collected'),
            'outstanding_desc' => $clients->sortByDesc('outstanding'),
            'name'             => $clients->sortBy('name'),
            default            => $clients->sortByDesc('invoiced'),
        };
        $clients = $clients->values();

        $totals = ['invoiced' => $clients->sum('invoiced'), 'collected' => $clients->sum('collected'), 'outstanding' => $clients->sum('outstanding')];

        $clients = $this->paginateCollection($clients);

        return view('admin.reports.sales.revenue-by-client', compact('clients', 'totals', 'from', 'to', 'sort', 'search'));
    }

    public function revenueByClientExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $sort   = $request->get('sort', 'invoiced_desc');
        $search = trim($request->get('search', ''));

        $rows = Client::with(['invoices' => function ($q) use ($from, $to) {
            $q->whereNotIn('status', ['voided'])->whereBetween('issue_date', [$from, $to]);
        }])->get()->map(function ($client) {
            $invoiced  = $client->invoices->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1));
            $collected = $client->invoices->sum(fn ($i) => $i->amount_paid * ($i->exchange_rate_to_tzs ?? 1));
            return ['name' => $client->company_name ?: $client->full_name, 'email' => $client->email, 'invoice_count' => $client->invoices->count(), 'invoiced' => $invoiced, 'collected' => $collected, 'outstanding' => max(0, $invoiced - $collected)];
        })->filter(fn ($c) => $c['invoiced'] > 0);

        if ($search !== '') {
            $rows = $rows->filter(fn ($c) => str_contains(strtolower($c['name']), strtolower($search)) || str_contains(strtolower($c['email'] ?? ''), strtolower($search)));
        }
        $rows = match ($sort) {
            'collected_desc'   => $rows->sortByDesc('collected'),
            'outstanding_desc' => $rows->sortByDesc('outstanding'),
            'name'             => $rows->sortBy('name'),
            default            => $rows->sortByDesc('invoiced'),
        };
        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['#', 'Client', 'Email', 'Invoices', 'Invoiced (TZS)', 'Collected (TZS)', 'Outstanding (TZS)']);
            foreach ($rows->values() as $i => $c) {
                fputcsv($h, [$i + 1, $c['name'], $c['email'] ?? '', $c['invoice_count'], $c['invoiced'], $c['collected'], $c['outstanding']]);
            }
            fclose($h);
        }, 'revenue-by-client-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function salesPipeline(Request $request)
    {
        $search = trim($request->get('search', ''));

        $allQ = Quotation::with('client')->whereIn('status', ['draft', 'sent', 'viewed'])->get();

        $stats = [
            'total_count'   => $allQ->count(),
            'total_value'   => $allQ->sum(fn ($q) => $q->total_amount * ($q->exchange_rate_to_tzs ?? 1)),
            'draft_count'   => $allQ->where('status', 'draft')->count(),
            'sent_count'    => $allQ->where('status', 'sent')->count(),
            'viewed_count'  => $allQ->where('status', 'viewed')->count(),
            'expiring_soon' => $allQ->whereNotNull('valid_until')->filter(fn ($q) => Carbon::parse($q->valid_until)->between(now(), now()->addDays(7)))->count(),
            'expired'       => $allQ->whereNotNull('valid_until')->filter(fn ($q) => Carbon::parse($q->valid_until)->lt(now()))->count(),
        ];

        $detailQ = Quotation::with('client')
            ->whereIn('status', ['draft', 'sent', 'viewed'])
            ->orderByDesc('created_at');
        if ($search !== '') {
            $detailQ->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) =>
                      $cq->where('company_name', 'like', '%' . $search . '%')
                         ->orWhere('full_name', 'like', '%' . $search . '%'));
            });
        }
        $quotations = $detailQ->paginate(10)->withQueryString();

        return view('admin.reports.sales.pipeline', compact('quotations', 'stats', 'search'));
    }

    public function salesPipelineExport(Request $request)
    {
        $search = trim($request->get('search', ''));

        $query = Quotation::with('client')
            ->whereIn('status', ['draft', 'sent', 'viewed'])
            ->orderByDesc('created_at');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) =>
                      $cq->where('company_name', 'like', '%' . $search . '%')
                         ->orWhere('full_name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Quotation No.', 'Client', 'Status', 'Value (TZS)', 'Valid Until', 'Created At']);
            foreach ($query->get() as $q) {
                fputcsv($h, [$q->quotation_number, $q->client?->company_name ?: ($q->client?->full_name ?? ''), ucfirst($q->status), number_format($q->total_amount * ($q->exchange_rate_to_tzs ?? 1), 2, '.', ''), $q->valid_until ?? '', $q->created_at->format('d M Y')]);
            }
            fclose($h);
        }, 'sales-pipeline-' . now()->toDateString() . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // FLEET REPORTS
    // =========================================================================

    public function fleetUtilization(Request $request)
    {
        $from      = $request->get('from', now()->startOfMonth()->toDateString());
        $to        = $request->get('to', now()->toDateString());
        $totalDays = max(1, Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);

        $gensets = Genset::with(['bookings' => function ($q) use ($from, $to) {
            $q->whereIn('status', ['active', 'returned', 'invoiced', 'paid'])
              ->where('rental_start_date', '<=', $to)
              ->where(function ($q2) use ($from) {
                  $q2->whereNull('rental_end_date')->orWhere('rental_end_date', '>=', $from);
              });
        }])->get();

        $rows = $gensets->map(function ($g) use ($from, $to, $totalDays) {
            $rentedDays = 0;
            foreach ($g->bookings as $b) {
                $start = Carbon::parse(max($b->rental_start_date, $from));
                $end   = $b->rental_end_date ? Carbon::parse(min($b->rental_end_date, $to)) : Carbon::parse($to);
                if ($end >= $start) $rentedDays += $start->diffInDays($end) + 1;
            }
            $rentedDays = min($rentedDays, $totalDays);
            return [
                'id'            => $g->id,
                'asset_number'  => $g->asset_number,
                'name'          => $g->name,
                'kva_rating'    => $g->kva_rating,
                'status'        => $g->status,
                'rented_days'   => $rentedDays,
                'idle_days'     => $totalDays - $rentedDays,
                'utilization'   => round($rentedDays / $totalDays * 100, 1),
                'booking_count' => $g->bookings->count(),
            ];
        })->sortByDesc('utilization')->values();

        $summary = [
            'total_gensets'   => $gensets->count(),
            'avg_utilization' => $rows->count() ? round($rows->avg('utilization'), 1) : 0,
            'fully_utilized'  => $rows->where('utilization', '>=', 80)->count(),
            'idle'            => $rows->where('utilization', 0)->count(),
        ];

        return view('admin.reports.fleet.utilization', compact('rows', 'summary', 'from', 'to', 'totalDays'));
    }

    public function revenueByGenset(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $gensets = Genset::orderBy('asset_number')->get();

        $rows = $gensets->map(function ($g) use ($from, $to) {
            $revenue = Invoice::whereHas('booking', fn ($q) => $q->where('genset_id', $g->id))
                ->whereNotIn('status', ['voided', 'draft'])
                ->whereBetween('issue_date', [$from, $to])
                ->get()->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1));

            $bookingCount = Booking::where('genset_id', $g->id)
                ->whereIn('status', ['active', 'returned', 'invoiced', 'paid'])
                ->whereBetween('rental_start_date', [$from, $to])->count();

            $fuelCost        = (float) FuelLog::where('genset_id', $g->id)->whereBetween('fuelled_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->sum('total_cost');
            $maintenanceCost = (float) MaintenanceRecord::where('genset_id', $g->id)->whereNotNull('completed_at')->whereBetween('completed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->sum('cost');

            return [
                'id'               => $g->id,
                'asset_number'     => $g->asset_number,
                'name'             => $g->name,
                'kva_rating'       => $g->kva_rating,
                'status'           => $g->status,
                'revenue'          => $revenue,
                'fuel_cost'        => $fuelCost,
                'maintenance_cost' => $maintenanceCost,
                'gross_profit'     => $revenue - $fuelCost - $maintenanceCost,
                'booking_count'    => $bookingCount,
            ];
        })->sortByDesc('revenue')->values();

        $totals = [
            'revenue'          => $rows->sum('revenue'),
            'fuel_cost'        => $rows->sum('fuel_cost'),
            'maintenance_cost' => $rows->sum('maintenance_cost'),
            'gross_profit'     => $rows->sum('gross_profit'),
        ];

        return view('admin.reports.fleet.revenue-by-genset', compact('rows', 'totals', 'from', 'to'));
    }

    public function bookingSummary(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $search = trim($request->get('search', ''));

        // Full collection (unfiltered by search) for stats + monthly chart
        $allBookings = Booking::with(['client', 'genset'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->get();

        $stats = [
            'total'        => $allBookings->count(),
            'pending'      => $allBookings->where('status', 'pending')->count(),
            'approved'     => $allBookings->where('status', 'approved')->count(),
            'active'       => $allBookings->where('status', 'active')->count(),
            'returned'     => $allBookings->whereIn('status', ['returned', 'invoiced', 'paid'])->count(),
            'cancelled'    => $allBookings->where('status', 'cancelled')->count(),
            'total_value'  => $allBookings->sum(fn ($b) => $b->total_amount * ($b->exchange_rate_to_tzs ?? 1)),
            'avg_duration' => round($allBookings->avg('rental_duration_days') ?? 0, 1),
        ];

        $monthly = $allBookings->groupBy(fn ($b) => Carbon::parse($b->created_at)->format('Y-m'))
            ->map(fn ($group, $key) => [
                'month'     => Carbon::parse($key . '-01')->format('M Y'),
                'count'     => $group->count(),
                'value'     => $group->sum(fn ($b) => $b->total_amount * ($b->exchange_rate_to_tzs ?? 1)),
                'cancelled' => $group->where('status', 'cancelled')->count(),
            ])->sortKeys()->values();

        // Paginated individual bookings (with optional search)
        $bookingsQuery = Booking::with(['client', 'genset', 'createdBy'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $bookingsQuery->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) => $cq
                      ->where('company_name', 'like', '%' . $search . '%')
                      ->orWhere('full_name', 'like', '%' . $search . '%'))
                  ->orWhereHas('createdBy', fn ($uq) => $uq
                      ->where('name', 'like', '%' . $search . '%'));
            });
        }

        $bookings = $bookingsQuery->paginate(10)->withQueryString();

        return view('admin.reports.fleet.bookings', compact('stats', 'monthly', 'bookings', 'from', 'to', 'search'));
    }

    public function bookingSummaryExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $search = trim($request->get('search', ''));

        $query = Booking::with(['client', 'genset', 'createdBy'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) => $cq
                      ->where('company_name', 'like', '%' . $search . '%')
                      ->orWhere('full_name', 'like', '%' . $search . '%'))
                  ->orWhereHas('createdBy', fn ($uq) => $uq
                      ->where('name', 'like', '%' . $search . '%'));
            });
        }

        $rows = $query->get();
        $filename = 'bookings-' . $from . '-to-' . $to . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#', 'Booking No.', 'Client', 'Genset', 'Start Date', 'Duration (Days)', 'Value (TZS)', 'Currency', 'Status', 'Opened By', 'Created At']);
            foreach ($rows as $i => $b) {
                $client = $b->client?->company_name ?: ($b->client?->full_name ?? $b->customer_name ?? '');
                $genset = $b->genset ? $b->genset->asset_number . ' - ' . $b->genset->name : '';
                fputcsv($handle, [
                    $i + 1,
                    $b->booking_number,
                    $client,
                    $genset,
                    $b->rental_start_date?->format('d M Y') ?? '',
                    $b->rental_duration_days ?? '',
                    number_format($b->total_amount * ($b->exchange_rate_to_tzs ?? 1), 2, '.', ''),
                    $b->currency ?? 'TZS',
                    ucfirst($b->status),
                    $b->createdBy?->name ?? '',
                    Carbon::parse($b->created_at)->format('d M Y H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function fuelConsumption(Request $request)
    {
        $from     = $request->get('from', now()->startOfMonth()->toDateString());
        $to       = $request->get('to', now()->toDateString());
        $gensetId = $request->get('genset_id');
        $search   = trim($request->get('search', ''));

        // Full query for summary stats
        $baseQ = FuelLog::with(['genset'])
            ->whereBetween('fuelled_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if ($gensetId) $baseQ->where('genset_id', $gensetId);
        $allLogs = $baseQ->get();

        $byGenset = $allLogs->groupBy('genset_id')->map(function ($group) {
            $g = $group->first()->genset;
            return [
                'id'            => $g?->id,
                'name'          => $g ? $g->asset_number . ' — ' . $g->name : 'Unknown',
                'litres'        => $group->sum('litres'),
                'cost'          => $group->sum('total_cost'),
                'avg_per_litre' => $group->count() ? round($group->avg('cost_per_litre'), 0) : 0,
                'log_count'     => $group->count(),
            ];
        })->sortByDesc('cost')->values();

        $totals = [
            'litres'        => $allLogs->sum('litres'),
            'cost'          => $allLogs->sum('total_cost'),
            'avg_per_litre' => $allLogs->count() ? round($allLogs->avg('cost_per_litre'), 0) : 0,
            'log_count'     => $allLogs->count(),
        ];

        // Paginated + searchable detail
        $logQ = FuelLog::with(['genset'])
            ->whereBetween('fuelled_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('fuelled_at');
        if ($gensetId) $logQ->where('genset_id', $gensetId);
        if ($search !== '') {
            $logQ->where(function ($q) use ($search) {
                $q->where('fuelled_by', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('genset', fn ($gq) =>
                      $gq->where('asset_number', 'like', '%' . $search . '%')
                         ->orWhere('name', 'like', '%' . $search . '%'));
            });
        }
        $logs = $logQ->paginate(10)->withQueryString();

        $gensetList = Genset::orderBy('name')->get(['id', 'asset_number', 'name', 'kva_rating']);

        return view('admin.reports.fleet.fuel', compact('logs', 'byGenset', 'totals', 'gensetList', 'from', 'to', 'gensetId', 'search'));
    }

    public function fuelConsumptionExport(Request $request)
    {
        $from     = $request->get('from', now()->startOfMonth()->toDateString());
        $to       = $request->get('to', now()->toDateString());
        $gensetId = $request->get('genset_id');
        $search   = trim($request->get('search', ''));

        $query = FuelLog::with(['genset'])
            ->whereBetween('fuelled_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('fuelled_at');
        if ($gensetId) $query->where('genset_id', $gensetId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('fuelled_by', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('genset', fn ($gq) =>
                      $gq->where('asset_number', 'like', '%' . $search . '%')
                         ->orWhere('name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Date', 'Generator', 'Litres', 'Cost/L (TZS)', 'Total Cost (TZS)', 'Fuelled By', 'Notes']);
            foreach ($query->get() as $log) {
                fputcsv($h, [Carbon::parse($log->fuelled_at)->format('d M Y'), trim(($log->genset?->asset_number ?? '') . ' ' . ($log->genset?->name ?? '')), $log->litres, $log->cost_per_litre ?? 0, $log->total_cost ?? 0, $log->fuelled_by ?? '', $log->notes ?? '']);
            }
            fclose($h);
        }, 'fuel-consumption-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function maintenanceCosts(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $type   = $request->get('type');
        $search = trim($request->get('search', ''));

        $baseQ = MaintenanceRecord::with('genset')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if ($type) $baseQ->where('type', $type);
        $allRecords = $baseQ->get();

        $byType = $allRecords->groupBy('type')->map(fn ($g, $t) => [
            'type'       => $t,
            'count'      => $g->count(),
            'total_cost' => $g->sum('cost'),
        ])->sortByDesc('total_cost')->values();

        $byGenset = $allRecords->groupBy('genset_id')->map(function ($g) {
            $gen = $g->first()->genset;
            return ['name' => $gen ? $gen->asset_number . ' — ' . $gen->name : 'Unknown', 'count' => $g->count(), 'total_cost' => $g->sum('cost')];
        })->sortByDesc('total_cost')->values();

        $totals = [
            'count'      => $allRecords->count(),
            'total_cost' => $allRecords->sum('cost'),
            'avg_cost'   => $allRecords->count() ? round($allRecords->avg('cost'), 0) : 0,
        ];

        $recQ = MaintenanceRecord::with('genset')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('completed_at');
        if ($type) $recQ->where('type', $type);
        if ($search !== '') {
            $recQ->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('genset', fn ($gq) =>
                      $gq->where('asset_number', 'like', '%' . $search . '%')
                         ->orWhere('name', 'like', '%' . $search . '%'));
            });
        }
        $records = $recQ->paginate(10)->withQueryString();

        $types = MaintenanceRecord::distinct()->pluck('type')->sort()->values();

        return view('admin.reports.fleet.maintenance', compact('records', 'byType', 'byGenset', 'totals', 'types', 'from', 'to', 'type', 'search'));
    }

    public function maintenanceCostsExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $type   = $request->get('type');
        $search = trim($request->get('search', ''));

        $query = MaintenanceRecord::with('genset')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('completed_at');
        if ($type) $query->where('type', $type);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('genset', fn ($gq) =>
                      $gq->where('asset_number', 'like', '%' . $search . '%')
                         ->orWhere('name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Date', 'Generator', 'Type', 'Title', 'Cost (TZS)', 'Notes']);
            foreach ($query->get() as $rec) {
                fputcsv($h, [$rec->completed_at ? Carbon::parse($rec->completed_at)->format('d M Y') : '', trim(($rec->genset?->asset_number ?? '') . ' ' . ($rec->genset?->name ?? '')), ucwords(str_replace('_', ' ', $rec->type)), $rec->title ?? '', $rec->cost ?? 0, $rec->notes ?? '']);
            }
            fclose($h);
        }, 'maintenance-costs-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function overdueServicing(Request $request)
    {
        $asAt   = $request->get('as_at', now()->toDateString());
        $search = trim($request->get('search', ''));

        $query = Genset::whereNotIn('status', ['retired'])
            ->where(function ($q) use ($asAt) {
                $q->where(function ($q2) use ($asAt) {
                    $q2->whereNotNull('next_service_date')->where('next_service_date', '<', $asAt);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('service_interval_hours')
                       ->whereNotNull('run_hours')
                       ->whereColumn('run_hours', '>=', 'service_interval_hours');
                });
            })
            ->orderBy('next_service_date');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        $gensets = $query->paginate(10)->withQueryString();

        return view('admin.reports.fleet.overdue-service', compact('gensets', 'asAt', 'search'));
    }

    public function overdueServicingExport(Request $request)
    {
        $asAt   = $request->get('as_at', now()->toDateString());
        $search = trim($request->get('search', ''));

        $query = Genset::whereNotIn('status', ['retired'])
            ->where(function ($q) use ($asAt) {
                $q->where(function ($q2) use ($asAt) {
                    $q2->whereNotNull('next_service_date')->where('next_service_date', '<', $asAt);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('service_interval_hours')
                       ->whereNotNull('run_hours')
                       ->whereColumn('run_hours', '>=', 'service_interval_hours');
                });
            })
            ->orderBy('next_service_date');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        return response()->streamDownload(function () use ($query, $asAt) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Asset No.', 'Name', 'KVA', 'Status', 'Run Hours', 'Service Interval (h)', 'Next Service Date', 'Days Overdue']);
            foreach ($query->get() as $g) {
                $days = $g->next_service_date ? max(0, (int) Carbon::parse($g->next_service_date)->diffInDays(Carbon::parse($asAt), false)) : 0;
                fputcsv($h, [$g->asset_number, $g->name, $g->kva_rating, ucfirst($g->status), $g->run_hours ?? 0, $g->service_interval_hours ?? '', $g->next_service_date ?? '', $days]);
            }
            fclose($h);
        }, 'overdue-service-' . $asAt . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // INVOICING REPORTS
    // =========================================================================

    public function revenueByPeriod(Request $request)
    {
        $from    = $request->get('from', now()->subYear()->toDateString());
        $to      = $request->get('to', now()->toDateString());
        $groupBy = $request->get('group_by', 'month');

        $invoices = Invoice::whereNotIn('status', ['voided', 'draft'])
            ->whereBetween('issue_date', [$from, $to])->get();

        $grouped = match ($groupBy) {
            'quarter' => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y') . '-Q' . Carbon::parse($i->issue_date)->quarter),
            'year'    => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y')),
            default   => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y-m')),
        };

        $periods = $grouped->map(function ($group, $key) use ($groupBy) {
            $label = match ($groupBy) {
                'quarter' => $key,
                'year'    => $key,
                default   => Carbon::parse($key . '-01')->format('M Y'),
            };
            return [
                'period'    => $label,
                'sort_key'  => $key,
                'count'     => $group->count(),
                'total_tzs' => $group->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1)),
                'paid_tzs'  => $group->sum(fn ($i) => $i->amount_paid * ($i->exchange_rate_to_tzs ?? 1)),
                'usd_total' => $group->where('currency', 'USD')->sum('total_amount'),
                'tzs_total' => $group->where('currency', 'TZS')->sum('total_amount'),
            ];
        })->sortKeys()->values();

        $totals = [
            'count'     => $invoices->count(),
            'total_tzs' => $invoices->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1)),
            'paid_tzs'  => $invoices->sum(fn ($i) => $i->amount_paid * ($i->exchange_rate_to_tzs ?? 1)),
        ];

        return view('admin.reports.invoices.revenue-by-period', compact('periods', 'totals', 'from', 'to', 'groupBy'));
    }

    public function revenueByPeriodExport(Request $request)
    {
        $from    = $request->get('from', now()->subYear()->toDateString());
        $to      = $request->get('to', now()->toDateString());
        $groupBy = $request->get('group_by', 'month');

        $invoices = Invoice::whereNotIn('status', ['voided', 'draft'])->whereBetween('issue_date', [$from, $to])->get();
        $grouped  = match ($groupBy) {
            'quarter' => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y') . '-Q' . Carbon::parse($i->issue_date)->quarter),
            'year'    => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y')),
            default   => $invoices->groupBy(fn ($i) => Carbon::parse($i->issue_date)->format('Y-m')),
        };
        $periods = $grouped->map(function ($group, $key) use ($groupBy) {
            $label = match ($groupBy) { 'quarter' => $key, 'year' => $key, default => Carbon::parse($key . '-01')->format('M Y') };
            return ['period' => $label, 'count' => $group->count(), 'total_tzs' => $group->sum(fn ($i) => $i->total_amount * ($i->exchange_rate_to_tzs ?? 1)), 'paid_tzs' => $group->sum(fn ($i) => $i->amount_paid * ($i->exchange_rate_to_tzs ?? 1))];
        })->sortKeys()->values();

        return response()->streamDownload(function () use ($periods) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Period', 'Invoices', 'Total Revenue (TZS)', 'Collected (TZS)', 'Outstanding (TZS)']);
            foreach ($periods as $row) {
                fputcsv($h, [$row['period'], $row['count'], $row['total_tzs'], $row['paid_tzs'], $row['total_tzs'] - $row['paid_tzs']]);
            }
            fclose($h);
        }, 'revenue-by-period-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function paymentMethods(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $payments = InvoicePayment::with('invoice')
            ->whereBetween('payment_date', [$from, $to])
            ->where('is_reversed', false)->get();

        $byMethod = $payments->groupBy('payment_method')->map(fn ($g, $m) => [
            'method' => ucwords(str_replace('_', ' ', $m ?: 'unknown')),
            'count'  => $g->count(),
            'total'  => $g->sum('amount'),
        ])->sortByDesc('total')->values();

        $monthly = $payments->groupBy(fn ($p) => Carbon::parse($p->payment_date)->format('Y-m'))
            ->map(fn ($g, $key) => [
                'month' => Carbon::parse($key . '-01')->format('M Y'),
                'count' => $g->count(),
                'total' => $g->sum('amount'),
            ])->sortKeys()->values();

        $totals = [
            'count' => $payments->count(),
            'total' => $payments->sum('amount'),
        ];

        return view('admin.reports.invoices.payment-methods', compact('byMethod', 'totals', 'monthly', 'from', 'to'));
    }

    public function paymentMethodsExport(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $byMethod = InvoicePayment::with('invoice')
            ->whereBetween('payment_date', [$from, $to])
            ->where('is_reversed', false)->get()
            ->groupBy('payment_method')
            ->map(fn ($g, $m) => ['method' => ucwords(str_replace('_', ' ', $m ?: 'unknown')), 'count' => $g->count(), 'total' => $g->sum('amount')])
            ->sortByDesc('total')->values();

        return response()->streamDownload(function () use ($byMethod) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Payment Method', 'Count', 'Total (TZS)']);
            foreach ($byMethod as $row) {
                fputcsv($h, [$row['method'], $row['count'], $row['total']]);
            }
            fclose($h);
        }, 'payment-methods-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function outstandingInvoices(Request $request)
    {
        $asAt     = $request->get('as_at', now()->toDateString());
        $clientId = $request->get('client_id');
        $search   = trim($request->get('search', ''));

        $query = Invoice::with('client')
            ->whereIn('status', ['sent', 'partially_paid', 'disputed'])
            ->where('issue_date', '<=', $asAt);
        if ($clientId) $query->where('client_id', $clientId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) =>
                      $cq->where('company_name', 'like', '%' . $search . '%')
                         ->orWhere('full_name', 'like', '%' . $search . '%'));
            });
        }

        $allInvoices = $query->orderBy('due_date')->get()->map(function ($inv) use ($asAt) {
            $balance     = max(0, (float) $inv->total_amount - (float) $inv->amount_paid);
            $daysOverdue = ($inv->due_date && Carbon::parse($inv->due_date)->lt(Carbon::parse($asAt)))
                ? (int) Carbon::parse($inv->due_date)->diffInDays(Carbon::parse($asAt)) : 0;
            return ['id' => $inv->id, 'invoice_number' => $inv->invoice_number, 'client_name' => $inv->client?->company_name ?: ($inv->client?->full_name ?? 'Unknown'), 'client_id' => $inv->client_id, 'issue_date' => $inv->issue_date, 'due_date' => $inv->due_date, 'total_amount' => $inv->total_amount, 'amount_paid' => $inv->amount_paid, 'balance' => $balance, 'currency' => $inv->currency, 'exchange_rate' => $inv->exchange_rate_to_tzs ?? 1, 'status' => $inv->status, 'days_overdue' => $daysOverdue, 'is_overdue' => $daysOverdue > 0];
        })->filter(fn ($i) => $i['balance'] > 0)->values();

        $clientsList = Client::orderBy('company_name')->get(['id', 'company_name', 'full_name']);

        $totals = [
            'count'               => $allInvoices->count(),
            'total_amount'        => $allInvoices->sum('total_amount'),
            'amount_paid'         => $allInvoices->sum('amount_paid'),
            'balance_tzs'         => $allInvoices->sum(fn ($i) => $i['balance'] * $i['exchange_rate']),
            'overdue_count'       => $allInvoices->where('is_overdue', true)->count(),
            'overdue_balance_tzs' => $allInvoices->where('is_overdue', true)->sum(fn ($i) => $i['balance'] * $i['exchange_rate']),
        ];

        $invoices = $this->paginateCollection($allInvoices);

        return view('admin.reports.invoices.outstanding', compact('invoices', 'totals', 'clientsList', 'clientId', 'asAt', 'search'));
    }

    public function outstandingInvoicesExport(Request $request)
    {
        $asAt     = $request->get('as_at', now()->toDateString());
        $clientId = $request->get('client_id');
        $search   = trim($request->get('search', ''));

        $query = Invoice::with('client')
            ->whereIn('status', ['sent', 'partially_paid', 'disputed'])
            ->where('issue_date', '<=', $asAt);
        if ($clientId) $query->where('client_id', $clientId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($cq) =>
                      $cq->where('company_name', 'like', '%' . $search . '%')
                         ->orWhere('full_name', 'like', '%' . $search . '%'));
            });
        }
        $rows = $query->orderBy('due_date')->get()->map(function ($inv) use ($asAt) {
            $balance = max(0, (float) $inv->total_amount - (float) $inv->amount_paid);
            $days    = ($inv->due_date && Carbon::parse($inv->due_date)->lt(Carbon::parse($asAt))) ? (int) Carbon::parse($inv->due_date)->diffInDays(Carbon::parse($asAt)) : 0;
            return ['invoice_number' => $inv->invoice_number, 'client_name' => $inv->client?->company_name ?: ($inv->client?->full_name ?? ''), 'issue_date' => $inv->issue_date, 'due_date' => $inv->due_date ?? '', 'total_amount' => $inv->total_amount, 'amount_paid' => $inv->amount_paid, 'balance' => $balance, 'currency' => $inv->currency, 'days_overdue' => $days, 'status' => $inv->status];
        })->filter(fn ($i) => $i['balance'] > 0)->values();

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Invoice No.', 'Client', 'Issue Date', 'Due Date', 'Total', 'Paid', 'Balance', 'Currency', 'Days Overdue', 'Status']);
            foreach ($rows as $row) {
                fputcsv($h, array_values($row));
            }
            fclose($h);
        }, 'outstanding-invoices-as-at-' . $asAt . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // EXPENSE REPORTS
    // =========================================================================

    public function expensesByCategory(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $expenses = Expense::with('category')
            ->whereIn('status', ['approved', 'posted'])
            ->whereBetween('expense_date', [$from, $to])->get();

        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($g) {
            $cat = $g->first()->category;
            return [
                'name'  => $cat?->name ?? 'Uncategorized',
                'count' => $g->count(),
                'total' => $g->sum('total_amount'),
                'vat'   => $g->sum('vat_amount'),
            ];
        })->sortByDesc('total')->values();

        $totals = [
            'count' => $expenses->count(),
            'total' => $expenses->sum('total_amount'),
            'vat'   => $expenses->sum('vat_amount'),
        ];

        return view('admin.reports.expenses.by-category', compact('byCategory', 'totals', 'from', 'to'));
    }

    public function expensesByCategoryExport(Request $request)
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $expenses = Expense::with('category')
            ->whereIn('status', ['approved', 'posted'])
            ->whereBetween('expense_date', [$from, $to])->get();

        $grandTotal = $expenses->sum('total_amount');
        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($g) {
            $cat = $g->first()->category;
            return ['name' => $cat?->name ?? 'Uncategorized', 'count' => $g->count(), 'total' => $g->sum('total_amount'), 'vat' => $g->sum('vat_amount')];
        })->sortByDesc('total')->values();

        return response()->streamDownload(function () use ($byCategory, $grandTotal) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Category', 'Entries', 'Amount (TZS)', 'VAT (TZS)', '% of Total']);
            foreach ($byCategory as $row) {
                $pct = $grandTotal > 0 ? round($row['total'] / $grandTotal * 100, 1) : 0;
                fputcsv($h, [$row['name'], $row['count'], $row['total'], $row['vat'] ?? 0, $pct . '%']);
            }
            fclose($h);
        }, 'expenses-by-category-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function expensesByPeriod(Request $request)
    {
        $from       = $request->get('from', now()->subYear()->toDateString());
        $to         = $request->get('to', now()->toDateString());
        $categoryId = $request->get('category_id');

        $query = Expense::with('category')
            ->whereIn('status', ['approved', 'posted'])
            ->whereBetween('expense_date', [$from, $to]);
        if ($categoryId) $query->where('expense_category_id', $categoryId);
        $expenses = $query->get();

        $monthly = $expenses->groupBy(fn ($e) => Carbon::parse($e->expense_date)->format('Y-m'))
            ->map(fn ($g, $key) => [
                'month'    => Carbon::parse($key . '-01')->format('M Y'),
                'sort_key' => $key,
                'count'    => $g->count(),
                'total'    => $g->sum('total_amount'),
            ])->sortKeys()->values();

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $totals = [
            'count' => $expenses->count(),
            'total' => $expenses->sum('total_amount'),
        ];

        return view('admin.reports.expenses.by-period', compact('monthly', 'totals', 'categories', 'from', 'to', 'categoryId'));
    }

    public function expensesByPeriodExport(Request $request)
    {
        $from       = $request->get('from', now()->subYear()->toDateString());
        $to         = $request->get('to', now()->toDateString());
        $categoryId = $request->get('category_id');

        $query = Expense::whereIn('status', ['approved', 'posted'])->whereBetween('expense_date', [$from, $to]);
        if ($categoryId) $query->where('expense_category_id', $categoryId);

        $monthly = $query->get()->groupBy(fn ($e) => Carbon::parse($e->expense_date)->format('Y-m'))
            ->map(fn ($g, $key) => ['month' => Carbon::parse($key . '-01')->format('M Y'), 'count' => $g->count(), 'total' => $g->sum('total_amount')])
            ->sortKeys()->values();

        return response()->streamDownload(function () use ($monthly) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Month', 'Entries', 'Amount (TZS)']);
            foreach ($monthly as $row) {
                fputcsv($h, [$row['month'], $row['count'], $row['total']]);
            }
            fclose($h);
        }, 'expenses-by-period-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function pettyCashSummary(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $status = $request->get('status');
        $search = trim($request->get('search', ''));

        // Full query for stats (no search filter)
        $baseQ = CashRequest::with(['requestedBy', 'items.expenseCategory'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if ($status) $baseQ->where('status', $status);
        $allRequests = $baseQ->get();
        $retired = $allRequests->where('status', 'retired');
        $stats = [
            'total_count'     => $allRequests->count(),
            'total_estimated' => $allRequests->sum('total_amount'),
            'total_actual'    => $retired->sum('actual_amount'),
            'variance'        => $retired->sum('actual_amount') - $retired->sum('total_amount'),
            'by_status'       => $allRequests->groupBy('status')->map(fn ($g) => ['count' => $g->count(), 'total' => $g->sum('total_amount')]),

        ];

        // Paginated + searchable detail
        $reqQ = CashRequest::with(['requestedBy', 'items.expenseCategory'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');
        if ($status) $reqQ->where('status', $status);
        if ($search !== '') {
            $reqQ->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('reference_number', 'like', '%' . $search . '%')
                  ->orWhereHas('requestedBy', fn ($uq) =>
                      $uq->where('name', 'like', '%' . $search . '%'));
            });
        }
        $requests = $reqQ->paginate(10)->withQueryString();

        return view('admin.reports.expenses.petty-cash', compact('requests', 'stats', 'from', 'to', 'status', 'search'));
    }

    public function pettyCashSummaryExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $status = $request->get('status');
        $search = trim($request->get('search', ''));

        $query = CashRequest::with(['requestedBy'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');
        if ($status) $query->where('status', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('reference_number', 'like', '%' . $search . '%')
                  ->orWhereHas('requestedBy', fn ($uq) =>
                      $uq->where('name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Ref', 'Title', 'Requested By', 'Status', 'Estimated (TZS)', 'Actual (TZS)', 'Created At']);
            foreach ($query->get() as $r) {
                fputcsv($h, [$r->reference_number ?? '', $r->title ?? '', $r->requestedBy?->name ?? '', ucfirst($r->status), $r->total_amount ?? 0, $r->actual_amount ?? 0, Carbon::parse($r->created_at)->format('d M Y')]);
            }
            fclose($h);
        }, 'petty-cash-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function grossMargin(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $search = trim($request->get('search', ''));

        $bookings = Booking::with(['invoice', 'genset', 'client'])
            ->whereIn('status', ['invoiced', 'paid', 'returned'])
            ->whereBetween('rental_start_date', [$from, $to])->get();

        $bookingIds     = $bookings->pluck('id');
        $fuelByBooking  = FuelLog::whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')->selectRaw('booking_id, SUM(total_cost) as total')
            ->pluck('total', 'booking_id');
        $maintByBooking = MaintenanceRecord::whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')->selectRaw('booking_id, SUM(cost) as total')
            ->pluck('total', 'booking_id');

        $rows = $bookings->map(function ($b) use ($fuelByBooking, $maintByBooking) {
                $revenue = $b->invoice
                    ? (float) $b->invoice->total_amount * ($b->invoice->exchange_rate_to_tzs ?? 1)
                    : (float) $b->total_amount * ($b->exchange_rate_to_tzs ?? 1);
                $fuelCost        = (float) ($fuelByBooking[$b->id] ?? 0);
                $maintenanceCost = (float) ($maintByBooking[$b->id] ?? 0);
                $directCost      = $fuelCost + $maintenanceCost;
                $grossProfit     = $revenue - $directCost;
                return ['id' => $b->id, 'booking_number' => $b->booking_number, 'client_name' => $b->client?->company_name ?: ($b->client?->full_name ?? $b->customer_name), 'genset_name' => $b->genset ? $b->genset->asset_number . ' — ' . $b->genset->name : '—', 'start_date' => $b->rental_start_date, 'duration' => $b->rental_duration_days, 'revenue' => $revenue, 'fuel_cost' => $fuelCost, 'maintenance_cost' => $maintenanceCost, 'direct_cost' => $directCost, 'gross_profit' => $grossProfit, 'margin_pct' => $revenue > 0 ? round($grossProfit / $revenue * 100, 1) : 0];
            })->sortByDesc('gross_profit')->values();

        if ($search !== '') {
            $rows = $rows->filter(fn ($r) =>
                str_contains(strtolower($r['booking_number'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($r['client_name'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($r['genset_name'] ?? ''), strtolower($search))
            )->values();
        }

        $totalRevenue = $rows->sum('revenue');
        $totals = [
            'revenue'          => $totalRevenue,
            'fuel_cost'        => $rows->sum('fuel_cost'),
            'maintenance_cost' => $rows->sum('maintenance_cost'),
            'direct_cost'      => $rows->sum('direct_cost'),
            'gross_profit'     => $rows->sum('gross_profit'),
            'avg_margin'       => $totalRevenue > 0 ? round($rows->sum('gross_profit') / $totalRevenue * 100, 1) : 0,
        ];

        $rows = $this->paginateCollection($rows);

        return view('admin.reports.expenses.gross-margin', compact('rows', 'totals', 'from', 'to', 'search'));
    }

    public function grossMarginExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $search = trim($request->get('search', ''));

        $bookings = Booking::with(['invoice', 'genset', 'client'])
            ->whereIn('status', ['invoiced', 'paid', 'returned'])
            ->whereBetween('rental_start_date', [$from, $to])->get();

        $bookingIds     = $bookings->pluck('id');
        $fuelByBooking  = FuelLog::whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')->selectRaw('booking_id, SUM(total_cost) as total')
            ->pluck('total', 'booking_id');
        $maintByBooking = MaintenanceRecord::whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')->selectRaw('booking_id, SUM(cost) as total')
            ->pluck('total', 'booking_id');

        $rows = $bookings->map(function ($b) use ($fuelByBooking, $maintByBooking) {
                $revenue  = $b->invoice ? (float) $b->invoice->total_amount * ($b->invoice->exchange_rate_to_tzs ?? 1) : (float) $b->total_amount * ($b->exchange_rate_to_tzs ?? 1);
                $fuelCost = (float) ($fuelByBooking[$b->id] ?? 0);
                $mCost    = (float) ($maintByBooking[$b->id] ?? 0);
                $profit   = $revenue - $fuelCost - $mCost;
                return ['booking_number' => $b->booking_number, 'client_name' => $b->client?->company_name ?: ($b->client?->full_name ?? $b->customer_name), 'genset_name' => $b->genset ? $b->genset->asset_number . ' — ' . $b->genset->name : '', 'start_date' => $b->rental_start_date, 'duration' => $b->rental_duration_days, 'revenue' => $revenue, 'fuel_cost' => $fuelCost, 'maintenance_cost' => $mCost, 'direct_cost' => $fuelCost + $mCost, 'gross_profit' => $profit, 'margin_pct' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0];
            })->sortByDesc('gross_profit');

        if ($search !== '') {
            $rows = $rows->filter(fn ($r) =>
                str_contains(strtolower($r['booking_number'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($r['client_name'] ?? ''), strtolower($search))
            );
        }

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Booking No.', 'Client', 'Generator', 'Start Date', 'Days', 'Revenue (TZS)', 'Fuel Cost', 'Maintenance Cost', 'Direct Cost', 'Gross Profit', 'Margin %']);
            foreach ($rows as $r) {
                fputcsv($h, [$r['booking_number'], $r['client_name'], $r['genset_name'], $r['start_date'], $r['duration'], $r['revenue'], $r['fuel_cost'], $r['maintenance_cost'], $r['direct_cost'], $r['gross_profit'], $r['margin_pct'] . '%']);
            }
            fclose($h);
        }, 'gross-margin-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // PROCUREMENT REPORTS
    // =========================================================================

    public function supplierPaymentHistory(Request $request)
    {
        $from       = $request->get('from', now()->startOfYear()->toDateString());
        $to         = $request->get('to', now()->toDateString());
        $supplierId = $request->get('supplier_id');
        $search     = trim($request->get('search', ''));

        // Full query for summary stats
        $baseQ = SupplierPayment::with(['supplier', 'purchaseOrder'])
            ->whereBetween('payment_date', [$from, $to]);
        if ($supplierId) $baseQ->where('supplier_id', $supplierId);
        $allPayments = $baseQ->get();

        $bySupplier = $allPayments->groupBy('supplier_id')->map(function ($g) {
            $s = $g->first()->supplier;
            return ['name' => $s?->name ?? 'Unknown', 'count' => $g->count(), 'total_paid' => $g->sum('amount'), 'wht' => $g->sum('withholding_tax')];
        })->sortByDesc('total_paid')->values();

        $suppliersList = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $totals = [
            'count'      => $allPayments->count(),
            'total_paid' => $allPayments->sum('amount'),
            'wht'        => $allPayments->sum('withholding_tax'),
        ];

        // Paginated + searchable detail
        $payQ = SupplierPayment::with(['supplier', 'purchaseOrder'])
            ->whereBetween('payment_date', [$from, $to])
            ->orderByDesc('payment_date');
        if ($supplierId) $payQ->where('supplier_id', $supplierId);
        if ($search !== '') {
            $payQ->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', '%' . $search . '%')
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', '%' . $search . '%'));
            });
        }
        $payments = $payQ->paginate(10)->withQueryString();

        return view('admin.reports.procurement.supplier-payments', compact('payments', 'bySupplier', 'suppliersList', 'totals', 'from', 'to', 'supplierId', 'search'));
    }

    public function supplierPaymentHistoryExport(Request $request)
    {
        $from       = $request->get('from', now()->startOfYear()->toDateString());
        $to         = $request->get('to', now()->toDateString());
        $supplierId = $request->get('supplier_id');
        $search     = trim($request->get('search', ''));

        $query = SupplierPayment::with(['supplier'])
            ->whereBetween('payment_date', [$from, $to])
            ->orderByDesc('payment_date');
        if ($supplierId) $query->where('supplier_id', $supplierId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', '%' . $search . '%')
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Reference', 'Supplier', 'Payment Method', 'Date', 'Gross Amount', 'WHT', 'Net Paid (TZS)']);
            foreach ($query->get() as $p) {
                fputcsv($h, [$p->reference_number ?? '', $p->supplier?->name ?? '', $p->payment_method ?? '', $p->payment_date, $p->gross_amount ?? $p->amount, $p->withholding_tax ?? 0, $p->amount]);
            }
            fclose($h);
        }, 'supplier-payments-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function purchaseOrderSummary(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $status = $request->get('status');
        $search = trim($request->get('search', ''));

        $query = PurchaseOrder::with(['supplier', 'items', 'supplierPayments'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if ($status) $query->where('status', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', '%' . $search . '%')
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', '%' . $search . '%'));
            });
        }

        $allPos = $query->get()->map(function ($po) {
            $committed = (float) $po->items->sum(fn ($i) => $i->quantity_ordered * $i->unit_cost);
            $received  = (float) $po->items->sum(fn ($i) => $i->quantity_received * $i->unit_cost);
            $paid      = (float) $po->supplierPayments->sum('amount');
            return ['id' => $po->id, 'po_number' => $po->po_number, 'supplier_name' => $po->supplier?->name ?? 'Unknown', 'status' => $po->status, 'ordered_at' => $po->ordered_at, 'committed' => $committed, 'received' => $received, 'paid' => $paid, 'balance' => max(0, $received - $paid)];
        });

        $byStatus = $allPos->groupBy('status')->map(fn ($g) => ['count' => $g->count(), 'committed' => $g->sum('committed')]);
        $totals   = ['count' => $allPos->count(), 'committed' => $allPos->sum('committed'), 'received' => $allPos->sum('received'), 'paid' => $allPos->sum('paid'), 'balance' => $allPos->sum('balance')];

        $pos = $this->paginateCollection($allPos->values());

        return view('admin.reports.procurement.purchase-orders', compact('pos', 'byStatus', 'totals', 'from', 'to', 'status', 'search'));
    }

    public function purchaseOrderSummaryExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfYear()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $status = $request->get('status');
        $search = trim($request->get('search', ''));

        $query = PurchaseOrder::with(['supplier', 'items', 'supplierPayments'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');
        if ($status) $query->where('status', $status);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', '%' . $search . '%')
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['PO Number', 'Supplier', 'Status', 'Date', 'Committed (TZS)', 'Received', 'Paid', 'Balance']);
            foreach ($query->get() as $po) {
                $committed = (float) $po->items->sum(fn ($i) => $i->quantity_ordered * $i->unit_cost);
                $received  = (float) $po->items->sum(fn ($i) => $i->quantity_received * $i->unit_cost);
                $paid      = (float) $po->supplierPayments->sum('amount');
                fputcsv($h, [$po->po_number, $po->supplier?->name ?? '', ucfirst($po->status), $po->ordered_at ?? '', $committed, $received, $paid, max(0, $received - $paid)]);
            }
            fclose($h);
        }, 'purchase-orders-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // INVENTORY REPORTS
    // =========================================================================

    public function stockLevels(Request $request)
    {
        $filter     = $request->get('filter', 'all');
        $categoryId = $request->get('category_id');
        $search     = trim($request->get('search', ''));

        $query = InventoryItem::with('category')->where('is_active', true)->orderBy('name');
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $allItems = $query->get()->map(function ($item) {
            $stockStatus = 'ok';
            if ($item->current_stock <= 0) $stockStatus = 'out';
            elseif ($item->min_stock_level > 0 && $item->current_stock <= $item->min_stock_level) $stockStatus = 'low';
            return ['id' => $item->id, 'sku' => $item->sku, 'name' => $item->name, 'category' => $item->category?->name ?? '—', 'unit' => $item->unit, 'current_stock' => $item->current_stock, 'min_stock_level' => $item->min_stock_level, 'unit_cost' => (float) $item->unit_cost, 'total_value' => (float) $item->current_stock * (float) $item->unit_cost, 'stock_status' => $stockStatus];
        });

        if ($filter === 'low') $allItems = $allItems->filter(fn ($i) => $i['stock_status'] === 'low');
        elseif ($filter === 'out') $allItems = $allItems->filter(fn ($i) => $i['stock_status'] === 'out');
        $allItems = $allItems->values();

        $categories = InventoryCategory::orderBy('name')->get(['id', 'name']);
        $summary = [
            'total'       => $allItems->count(),
            'low'         => $allItems->where('stock_status', 'low')->count(),
            'out'         => $allItems->where('stock_status', 'out')->count(),
            'total_value' => $allItems->sum('total_value'),
        ];

        $items = $this->paginateCollection($allItems);

        return view('admin.reports.inventory.stock-levels', compact('items', 'categories', 'summary', 'filter', 'categoryId', 'search'));
    }

    public function stockLevelsExport(Request $request)
    {
        $filter     = $request->get('filter', 'all');
        $categoryId = $request->get('category_id');
        $search     = trim($request->get('search', ''));

        $query = InventoryItem::with('category')->where('is_active', true)->orderBy('name');
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')->orWhere('sku', 'like', '%' . $search . '%');
            });
        }
        $rows = $query->get()->map(function ($item) {
            $s = 'ok';
            if ($item->current_stock <= 0) $s = 'out';
            elseif ($item->min_stock_level > 0 && $item->current_stock <= $item->min_stock_level) $s = 'low';
            return ['sku' => $item->sku, 'name' => $item->name, 'category' => $item->category?->name ?? '', 'unit' => $item->unit, 'current_stock' => $item->current_stock, 'min_stock_level' => $item->min_stock_level, 'unit_cost' => $item->unit_cost ?? 0, 'total_value' => (float) $item->current_stock * (float) ($item->unit_cost ?? 0), 'stock_status' => $s];
        });
        if ($filter === 'low') $rows = $rows->filter(fn ($i) => $i['stock_status'] === 'low');
        elseif ($filter === 'out') $rows = $rows->filter(fn ($i) => $i['stock_status'] === 'out');

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['SKU', 'Item Name', 'Category', 'Unit', 'Current Stock', 'Min Level', 'Unit Cost', 'Total Value (TZS)', 'Status']);
            foreach ($rows as $row) {
                fputcsv($h, [$row['sku'], $row['name'], $row['category'], $row['unit'], $row['current_stock'], $row['min_stock_level'], $row['unit_cost'], $row['total_value'], ucwords(str_replace('_', ' ', $row['stock_status']))]);
            }
            fclose($h);
        }, 'stock-levels-' . now()->toDateString() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function stockMovements(Request $request)
    {
        $from   = $request->get('from', now()->startOfMonth()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $type   = $request->get('type');
        $itemId = $request->get('item_id');
        $search = trim($request->get('search', ''));

        // Full query for totals (no search)
        $baseQ = StockMovement::with('item.category')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        if ($type) $baseQ->where('type', $type);
        if ($itemId) $baseQ->where('inventory_item_id', $itemId);
        $allMovements = $baseQ->get();
        $totals = [
            'in'         => $allMovements->where('type', 'in')->sum('quantity'),
            'out'        => $allMovements->where('type', 'out')->sum('quantity'),
            'adjustment' => $allMovements->where('type', 'adjustment')->count(),
        ];

        // Paginated + searchable detail
        $movQ = StockMovement::with('item.category')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');
        if ($type) $movQ->where('type', $type);
        if ($itemId) $movQ->where('inventory_item_id', $itemId);
        if ($search !== '') {
            $movQ->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('item', fn ($iq) =>
                      $iq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('sku', 'like', '%' . $search . '%'));
            });
        }
        $movements = $movQ->paginate(10)->withQueryString();

        $itemsList = InventoryItem::where('is_active', true)->orderBy('name')->get(['id', 'sku', 'name']);

        return view('admin.reports.inventory.movements', compact('movements', 'totals', 'itemsList', 'from', 'to', 'type', 'itemId', 'search'));
    }

    public function stockMovementsExport(Request $request)
    {
        $from   = $request->get('from', now()->startOfMonth()->toDateString());
        $to     = $request->get('to', now()->toDateString());
        $type   = $request->get('type');
        $itemId = $request->get('item_id');
        $search = trim($request->get('search', ''));

        $query = StockMovement::with('item')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderByDesc('created_at');
        if ($type) $query->where('type', $type);
        if ($itemId) $query->where('inventory_item_id', $itemId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('item', fn ($iq) =>
                      $iq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('sku', 'like', '%' . $search . '%'));
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Date', 'Item', 'Type', 'Quantity', 'Unit Cost', 'Reference', 'Notes']);
            foreach ($query->get() as $m) {
                fputcsv($h, [Carbon::parse($m->created_at)->format('d M Y'), $m->item?->name ?? '', ucfirst($m->type), $m->quantity, $m->unit_cost ?? 0, $m->reference ?? '', $m->notes ?? '']);
            }
            fclose($h);
        }, 'stock-movements-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function inventoryValuation(Request $request)
    {
        $categoryId = $request->get('category_id');
        $search     = trim($request->get('search', ''));

        $query = InventoryItem::with('category')->where('is_active', true)->orderBy('name');
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $allItems = $query->get()->map(fn ($item) => ['id' => $item->id, 'sku' => $item->sku, 'name' => $item->name, 'category' => $item->category?->name ?? '—', 'unit' => $item->unit, 'stock' => $item->current_stock ?? 0, 'unit_cost' => $item->unit_cost ?? 0, 'total_value' => ($item->current_stock ?? 0) * ($item->unit_cost ?? 0)]);

        $byCategory = $allItems->groupBy('category')->map(function ($g, $catName) {
            return ['name' => $catName, 'item_count' => $g->count(), 'total_units' => $g->sum('stock'), 'total_value' => $g->sum('total_value')];
        })->sortByDesc('total_value')->values();

        $categories = InventoryCategory::orderBy('name')->get(['id', 'name']);
        $totals = ['item_count' => $allItems->count(), 'total_units' => $allItems->sum('stock'), 'total_value' => $allItems->sum('total_value')];

        $items = $this->paginateCollection($allItems);

        return view('admin.reports.inventory.valuation', compact('items', 'byCategory', 'categories', 'totals', 'categoryId', 'search'));
    }

    public function inventoryValuationExport(Request $request)
    {
        $categoryId = $request->get('category_id');
        $search     = trim($request->get('search', ''));

        $query = InventoryItem::with('category')->where('is_active', true)->orderBy('name');
        if ($categoryId) $query->where('category_id', $categoryId);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')->orWhere('sku', 'like', '%' . $search . '%');
            });
        }
        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['SKU', 'Name', 'Category', 'Unit', 'Stock', 'Unit Cost (TZS)', 'Total Value (TZS)']);
            foreach ($query->get() as $item) {
                fputcsv($h, [$item->sku, $item->name, $item->category?->name ?? '', $item->unit, $item->current_stock ?? 0, $item->unit_cost ?? 0, ($item->current_stock ?? 0) * ($item->unit_cost ?? 0)]);
            }
            fclose($h);
        }, 'inventory-valuation-' . now()->toDateString() . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // ACCOUNTING REPORTS
    // =========================================================================

    public function generalLedger(Request $request)
    {
        $from      = $request->get('from', now()->startOfMonth()->toDateString());
        $to        = $request->get('to', now()->toDateString());
        $accountId = $request->get('account_id');
        $search    = trim($request->get('search', ''));

        $accountsList = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);

        $allLines       = collect();
        $account        = null;
        $openingBalance = 0;
        $closingBalance = 0;

        if ($accountId) {
            $account = Account::findOrFail($accountId);

            $before = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->where('journal_entry_lines.account_id', $accountId)
                ->where('journal_entries.entry_date', '<', $from)
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $openingBalance = $account->normal_balance === 'debit'
                ? (float) $before->total_debit - (float) $before->total_credit
                : (float) $before->total_credit - (float) $before->total_debit;

            $jeLines = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->where('journal_entry_lines.account_id', $accountId)
                ->whereBetween('journal_entries.entry_date', [$from, $to])
                ->select(
                    'journal_entries.id as journal_entry_id',
                    'journal_entries.entry_number',
                    'journal_entries.entry_date',
                    'journal_entries.description as je_description',
                    'journal_entries.reference',
                    'journal_entry_lines.description as line_description',
                    'journal_entry_lines.debit',
                    'journal_entry_lines.credit'
                )
                ->orderBy('journal_entries.entry_date')
                ->orderBy('journal_entries.entry_number')
                ->get();

            $running = $openingBalance;
            foreach ($jeLines as $jeLine) {
                $running += $account->normal_balance === 'debit'
                    ? (float) $jeLine->debit - (float) $jeLine->credit
                    : (float) $jeLine->credit - (float) $jeLine->debit;
                $allLines->push([
                    'journal_entry_id' => $jeLine->journal_entry_id,
                    'entry_number'     => $jeLine->entry_number,
                    'date'             => $jeLine->entry_date,
                    'description'      => $jeLine->line_description ?: $jeLine->je_description,
                    'reference'        => $jeLine->reference,
                    'debit'            => (float) $jeLine->debit,
                    'credit'           => (float) $jeLine->credit,
                    'balance'          => $running,
                ]);
            }
            $closingBalance = $running;
        }

        $filteredLines = $search !== ''
            ? $allLines->filter(fn ($l) =>
                str_contains(strtolower($l['description'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($l['reference'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($l['entry_number'] ?? ''), strtolower($search))
              )->values()
            : $allLines;

        $periodDebit  = $filteredLines->sum('debit');
        $periodCredit = $filteredLines->sum('credit');
        $lines = $this->paginateCollection($filteredLines);

        return view('admin.reports.accounting.general-ledger', compact(
            'accountsList', 'account', 'lines',
            'openingBalance', 'closingBalance', 'periodDebit', 'periodCredit',
            'from', 'to', 'accountId', 'search'
        ));
    }

    public function generalLedgerExport(Request $request)
    {
        $from      = $request->get('from', now()->startOfMonth()->toDateString());
        $to        = $request->get('to', now()->toDateString());
        $accountId = $request->get('account_id');
        $search    = trim($request->get('search', ''));

        $lines   = collect();
        $account = null;
        $opening = 0;

        if ($accountId) {
            $account = Account::findOrFail($accountId);
            $before  = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->where('journal_entry_lines.account_id', $accountId)
                ->where('journal_entries.entry_date', '<', $from)
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();
            $opening = $account->normal_balance === 'debit'
                ? (float) $before->total_debit - (float) $before->total_credit
                : (float) $before->total_credit - (float) $before->total_debit;

            $jeLines = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->where('journal_entry_lines.account_id', $accountId)
                ->whereBetween('journal_entries.entry_date', [$from, $to])
                ->select('journal_entries.entry_number', 'journal_entries.entry_date', 'journal_entries.reference', 'journal_entry_lines.description as line_description', 'journal_entries.description as je_description', 'journal_entry_lines.debit', 'journal_entry_lines.credit')
                ->orderBy('journal_entries.entry_date')->orderBy('journal_entries.entry_number')->get();

            $running = $opening;
            foreach ($jeLines as $jeLine) {
                $running += $account->normal_balance === 'debit'
                    ? (float) $jeLine->debit - (float) $jeLine->credit
                    : (float) $jeLine->credit - (float) $jeLine->debit;
                $lines->push(['entry_number' => $jeLine->entry_number, 'date' => $jeLine->entry_date, 'description' => $jeLine->line_description ?: $jeLine->je_description, 'reference' => $jeLine->reference, 'debit' => (float) $jeLine->debit, 'credit' => (float) $jeLine->credit, 'balance' => $running]);
            }
        }

        if ($search !== '') {
            $lines = $lines->filter(fn ($l) =>
                str_contains(strtolower($l['description'] ?? ''), strtolower($search)) ||
                str_contains(strtolower($l['reference'] ?? ''), strtolower($search))
            )->values();
        }

        return response()->streamDownload(function () use ($lines) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Entry No.', 'Date', 'Description', 'Reference', 'Debit', 'Credit', 'Running Balance']);
            foreach ($lines as $l) {
                fputcsv($h, [$l['entry_number'], $l['date'], $l['description'], $l['reference'] ?? '', $l['debit'], $l['credit'], $l['balance']]);
            }
            fclose($h);
        }, 'general-ledger-' . $from . '-to-' . $to . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // EXECUTIVE SUMMARY
    // =========================================================================

    public function executiveSummary(Request $request)
    {
        $from        = $request->get('from',         now()->startOfYear()->toDateString());
        $to          = $request->get('to',           now()->toDateString());
        $compareFrom = $request->get('compare_from', Carbon::parse($from)->subYear()->toDateString());
        $compareTo   = $request->get('compare_to',   Carbon::parse($to)->subYear()->toDateString());

        $fromDate  = Carbon::parse($from)->startOfDay();
        $toDate    = Carbon::parse($to)->endOfDay();
        $cFromDate = Carbon::parse($compareFrom)->startOfDay();
        $cToDate   = Carbon::parse($compareTo)->endOfDay();

        $buildKpis = function (Carbon $f, Carbon $t) {
            $salesValue  = Booking::whereBetween('created_at', [$f, $t])->whereNotIn('status', ['cancelled'])->sum('total_amount');
            $salesCount  = Booking::whereBetween('created_at', [$f, $t])->whereNotIn('status', ['cancelled'])->count();
            $revenue     = InvoicePayment::whereBetween('payment_date', [$f, $t])->where('is_reversed', false)->sum('amount');
            $expenses    = Expense::where('status', 'posted')->whereBetween('expense_date', [$f, $t])->sum('total_amount');
            $cashSpend   = CashRequest::whereBetween('created_at', [$f, $t])->whereIn('status', ['retired', 'issued'])->sum('actual_amount');
            $poSpend     = (float) DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->whereBetween('purchase_orders.ordered_at', [$f, $t])
                ->whereNotIn('purchase_orders.status', ['cancelled'])
                ->sum(DB::raw('purchase_order_items.quantity_ordered * purchase_order_items.unit_cost'));
            $expenditure = $expenses + $cashSpend + $poSpend;
            $netBalance  = $revenue - $expenditure;
            return compact('salesCount', 'salesValue', 'revenue', 'expenses', 'cashSpend', 'poSpend', 'expenditure', 'netBalance');
        };

        $current  = $buildKpis($fromDate, $toDate);
        $previous = $buildKpis($cFromDate, $cToDate);

        // ── MONTHLY TREND ───────────────────────────────────────────────────
        $months = [];
        $cursor = $fromDate->copy()->startOfMonth();
        while ($cursor->lte($toDate)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $mRevenue  = InvoicePayment::whereBetween('payment_date', [$fromDate, $toDate])
            ->where('is_reversed', false)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as m, SUM(amount) as total")
            ->groupBy('m')->orderBy('m')->pluck('total', 'm');

        $mExpenses = Expense::where('status', 'posted')->whereBetween('expense_date', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as m, SUM(total_amount) as total")
            ->groupBy('m')->orderBy('m')->pluck('total', 'm');

        $mBookings = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as cnt")
            ->groupBy('m')->orderBy('m')->pluck('cnt', 'm');

        $mPrevRevenue = InvoicePayment::whereBetween('payment_date', [$cFromDate, $cToDate])
            ->where('is_reversed', false)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as m, SUM(amount) as total")
            ->groupBy('m')->orderBy('m')->pluck('total', 'm');

        $mPrevExpenses = Expense::where('status', 'posted')->whereBetween('expense_date', [$cFromDate, $cToDate])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as m, SUM(total_amount) as total")
            ->groupBy('m')->orderBy('m')->pluck('total', 'm');

        // Align compare data by positional index (month 1 of compare → month 1 of current)
        $compareMonths = [];
        $cCursor = $cFromDate->copy()->startOfMonth();
        while ($cCursor->lte($cToDate)) {
            $compareMonths[] = $cCursor->format('Y-m');
            $cCursor->addMonth();
        }

        $chartLabels       = array_map(fn($m) => Carbon::parse($m . '-01')->format('M Y'), $months);
        $chartRevenue      = array_map(fn($m) => (float) ($mRevenue[$m]   ?? 0), $months);
        $chartExpenses     = array_map(fn($m) => (float) ($mExpenses[$m]  ?? 0), $months);
        $chartBookings     = array_map(fn($m) => (int)   ($mBookings[$m]  ?? 0), $months);
        $chartPrevRevenue  = array_values(array_map(fn($m) => (float) ($mPrevRevenue[$m]  ?? 0), $compareMonths));
        $chartPrevExpenses = array_values(array_map(fn($m) => (float) ($mPrevExpenses[$m] ?? 0), $compareMonths));

        // ── TOP INTELLIGENCE ────────────────────────────────────────────────
        $topClients = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('client_id')
            ->with('client:id,company_name,full_name')
            ->selectRaw('client_id, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('client_id')->orderByDesc('total_value')->limit(5)->get();

        $topGensets = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('genset_id')
            ->with('genset:id,name,kva_rating,type')
            ->selectRaw('genset_id, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('genset_id')->orderByDesc('bookings_count')->limit(5)->get();

        $topSalesUsers = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('created_by')
            ->with('createdBy:id,name')
            ->selectRaw('created_by, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('created_by')->orderByDesc('bookings_count')->limit(5)->get();

        $topExpenseCategories = Expense::where('status', 'posted')->whereBetween('expense_date', [$fromDate, $toDate])
            ->with('category:id,name')
            ->selectRaw('expense_category_id, SUM(total_amount) as total, COUNT(*) as cnt')
            ->groupBy('expense_category_id')->orderByDesc('total')->limit(6)->get();

        $gensetTypeBreakdown = Booking::whereBetween('bookings.created_at', [$fromDate, $toDate])
            ->whereNotIn('bookings.status', ['cancelled'])->whereNotNull('bookings.genset_id')
            ->join('gensets', 'gensets.id', '=', 'bookings.genset_id')
            ->selectRaw('gensets.kva_rating, COUNT(*) as cnt, SUM(bookings.total_amount) as total_value')
            ->groupBy('gensets.kva_rating')->orderByDesc('cnt')->limit(8)->get();

        $maintenanceTypes = MaintenanceRecord::whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('type, COUNT(*) as cnt, SUM(cost) as total_cost')
            ->groupBy('type')->orderByDesc('cnt')->get();

        // ── FLEET & MISC ────────────────────────────────────────────────────
        $totalFleet       = Genset::whereNotIn('status', ['sold', 'disposed'])->count();
        $activeRentals    = Booking::where('status', 'active')->count();
        $fleetAvailable   = Genset::where('status', 'available')->count();
        $fleetMaintenance = Genset::where('status', 'maintenance')->count();
        $fleetDeployed    = Genset::where('status', 'rented')->count();
        $utilizationPct   = $totalFleet > 0 ? round($activeRentals / $totalFleet * 100, 1) : 0;
        $newClients       = Client::whereBetween('created_at', [$fromDate, $toDate])->count();
        $invoicedInPeriod = Invoice::whereBetween('issue_date', [$fromDate, $toDate])->sum('total_amount');
        $collectionRate   = $invoicedInPeriod > 0 ? round($current['revenue'] / $invoicedInPeriod * 100, 1) : 0;
        $outstandingBalance = Invoice::whereIn('status', ['sent', 'partially_paid', 'disputed'])
            ->sum(DB::raw('total_amount - amount_paid'));

        return view('admin.reports.executive-summary', compact(
            'from', 'to', 'compareFrom', 'compareTo',
            'current', 'previous',
            'chartLabels', 'chartRevenue', 'chartExpenses', 'chartBookings',
            'chartPrevRevenue', 'chartPrevExpenses',
            'topClients', 'topGensets', 'topSalesUsers', 'topExpenseCategories',
            'gensetTypeBreakdown', 'maintenanceTypes',
            'totalFleet', 'activeRentals', 'utilizationPct',
            'fleetAvailable', 'fleetMaintenance', 'fleetDeployed',
            'newClients', 'collectionRate', 'invoicedInPeriod', 'outstandingBalance'
        ));
    }

    public function executiveSummaryExport(Request $request)
    {
        $from        = $request->get('from',         now()->startOfYear()->toDateString());
        $to          = $request->get('to',           now()->toDateString());
        $compareFrom = $request->get('compare_from', Carbon::parse($from)->subYear()->toDateString());
        $compareTo   = $request->get('compare_to',   Carbon::parse($to)->subYear()->toDateString());

        $fromDate  = Carbon::parse($from)->startOfDay();
        $toDate    = Carbon::parse($to)->endOfDay();
        $cFromDate = Carbon::parse($compareFrom)->startOfDay();
        $cToDate   = Carbon::parse($compareTo)->endOfDay();

        $buildKpis = function (Carbon $f, Carbon $t) {
            $salesValue  = Booking::whereBetween('created_at', [$f, $t])->whereNotIn('status', ['cancelled'])->sum('total_amount');
            $salesCount  = Booking::whereBetween('created_at', [$f, $t])->whereNotIn('status', ['cancelled'])->count();
            $revenue     = InvoicePayment::whereBetween('payment_date', [$f, $t])->where('is_reversed', false)->sum('amount');
            $expenses    = Expense::where('status', 'posted')->whereBetween('expense_date', [$f, $t])->sum('total_amount');
            $cashSpend   = CashRequest::whereBetween('created_at', [$f, $t])->whereIn('status', ['retired', 'issued'])->sum('actual_amount');
            $poSpend     = (float) DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->whereBetween('purchase_orders.ordered_at', [$f, $t])
                ->whereNotIn('purchase_orders.status', ['cancelled'])
                ->sum(DB::raw('purchase_order_items.quantity_ordered * purchase_order_items.unit_cost'));
            $expenditure = $expenses + $cashSpend + $poSpend;
            $netBalance  = $revenue - $expenditure;
            return compact('salesCount', 'salesValue', 'revenue', 'expenses', 'cashSpend', 'poSpend', 'expenditure', 'netBalance');
        };

        $cur = $buildKpis($fromDate, $toDate);
        $prv = $buildKpis($cFromDate, $cToDate);

        $topClients = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('client_id')
            ->with('client:id,company_name,full_name')
            ->selectRaw('client_id, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('client_id')->orderByDesc('total_value')->limit(10)->get();

        $topGensets = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('genset_id')
            ->with('genset:id,name,kva_rating')
            ->selectRaw('genset_id, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('genset_id')->orderByDesc('bookings_count')->limit(10)->get();

        $topUsers = Booking::whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])->whereNotNull('created_by')
            ->with('createdBy:id,name')
            ->selectRaw('created_by, COUNT(*) as bookings_count, SUM(total_amount) as total_value')
            ->groupBy('created_by')->orderByDesc('bookings_count')->limit(10)->get();

        $topCats = Expense::where('status', 'posted')->whereBetween('expense_date', [$fromDate, $toDate])
            ->with('category:id,name')
            ->selectRaw('expense_category_id, SUM(total_amount) as total, COUNT(*) as cnt')
            ->groupBy('expense_category_id')->orderByDesc('total')->limit(10)->get();

        $filename = 'executive-summary-' . $from . '-to-' . $to . '.csv';
        return response()->streamDownload(function () use ($from, $to, $compareFrom, $compareTo, $cur, $prv, $topClients, $topGensets, $topUsers, $topCats) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['EXECUTIVE SUMMARY REPORT']);
            fputcsv($h, ['Generated', now()->format('d M Y H:i')]);
            fputcsv($h, ['Current Period', $from . ' to ' . $to]);
            fputcsv($h, ['Comparison Period', $compareFrom . ' to ' . $compareTo]);
            fputcsv($h, []);
            fputcsv($h, ['KPI', 'Current Period', 'Comparison Period', 'Change']);
            $delta = fn($c, $p) => $p > 0 ? round(($c - $p) / $p * 100, 1) . '%' : 'N/A';
            fputcsv($h, ['Total Bookings', $cur['salesCount'], $prv['salesCount'], $delta($cur['salesCount'], $prv['salesCount'])]);
            fputcsv($h, ['Sales Value (TZS)', number_format($cur['salesValue'], 2), number_format($prv['salesValue'], 2), $delta($cur['salesValue'], $prv['salesValue'])]);
            fputcsv($h, ['Revenue Collected (TZS)', number_format($cur['revenue'], 2), number_format($prv['revenue'], 2), $delta($cur['revenue'], $prv['revenue'])]);
            fputcsv($h, ['Direct Expenses (TZS)', number_format($cur['expenses'], 2), number_format($prv['expenses'], 2), $delta($cur['expenses'], $prv['expenses'])]);
            fputcsv($h, ['Cash Requests Spent (TZS)', number_format($cur['cashSpend'], 2), number_format($prv['cashSpend'], 2), $delta($cur['cashSpend'], $prv['cashSpend'])]);
            fputcsv($h, ['PO Committed (TZS)', number_format($cur['poSpend'], 2), number_format($prv['poSpend'], 2), $delta($cur['poSpend'], $prv['poSpend'])]);
            fputcsv($h, ['Total Expenditure (TZS)', number_format($cur['expenditure'], 2), number_format($prv['expenditure'], 2), $delta($cur['expenditure'], $prv['expenditure'])]);
            fputcsv($h, ['Net Balance (TZS)', number_format($cur['netBalance'], 2), number_format($prv['netBalance'], 2), $delta($cur['netBalance'], $prv['netBalance'])]);
            fputcsv($h, []);
            fputcsv($h, ['TOP CLIENTS BY SALES VALUE']);
            fputcsv($h, ['Client', 'Bookings', 'Total Value (TZS)']);
            foreach ($topClients as $r) {
                fputcsv($h, [$r->client?->company_name ?: $r->client?->full_name ?? 'Unknown', $r->bookings_count, number_format($r->total_value, 2)]);
            }
            fputcsv($h, []);
            fputcsv($h, ['TOP GENERATORS BY USAGE']);
            fputcsv($h, ['Generator', 'KVA', 'Bookings', 'Total Value (TZS)']);
            foreach ($topGensets as $r) {
                fputcsv($h, [$r->genset?->name ?? 'Unknown', $r->genset?->kva_rating ?? '—', $r->bookings_count, number_format($r->total_value, 2)]);
            }
            fputcsv($h, []);
            fputcsv($h, ['LEAD SALES USERS']);
            fputcsv($h, ['User', 'Bookings Created', 'Total Value (TZS)']);
            foreach ($topUsers as $r) {
                fputcsv($h, [$r->createdBy?->name ?? 'Unknown', $r->bookings_count, number_format($r->total_value, 2)]);
            }
            fputcsv($h, []);
            fputcsv($h, ['TOP EXPENSE CATEGORIES']);
            fputcsv($h, ['Category', 'Entries', 'Total (TZS)']);
            foreach ($topCats as $r) {
                fputcsv($h, [$r->category?->name ?? 'Uncategorised', $r->cnt, number_format($r->total, 2)]);
            }
            fclose($h);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // =========================================================================
    // DAILY CASH-UP
    // =========================================================================

    public function dailyCashup(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        // Pre-load all closings for this date keyed by bank_account_id
        $closings = \App\Models\DailyClosing::where('closing_date', $date)
            ->get()
            ->keyBy('bank_account_id');

        $bankAccounts = BankAccount::with('account')
            ->where('is_active', true)
            ->orderByRaw("FIELD(account_type, 'cash', 'mobile_money', 'bank')")
            ->orderBy('name')
            ->get();

        $accounts = $bankAccounts->map(function (BankAccount $ba) use ($date, $closings) {

            // ── Use saved snapshot if available ───────────────────────────────
            $closing = $closings->get($ba->id);
            if ($closing) {
                $snap = $closing->snapshot ?? [];
                // Rebuild collection-like arrays from snapshot so the view works unchanged
                $payments = collect($snap['payments'] ?? []);
                $expenses = collect($snap['expenses'] ?? []);
                $cashReqs = collect($snap['cash_requests'] ?? []);

                return [
                    'id'              => $ba->id,
                    'name'            => $ba->name,
                    'account_type'    => $ba->account_type,
                    'currency'        => $ba->currency,
                    'opening_balance' => (float) $closing->opening_balance,
                    'closing_balance' => (float) $closing->closing_balance,
                    'total_in'        => (float) $closing->total_in,
                    'total_out'       => (float) $closing->total_out,
                    'payments'        => $payments,
                    'expenses'        => $expenses,
                    'cash_reqs'       => $cashReqs,
                    'has_activity'    => $closing->total_in > 0 || $closing->total_out > 0,
                    'is_snapshot'     => true,
                    'closed_at'       => $closing->updated_at,
                ];
            }

            // ── Live computation (no snapshot yet) ────────────────────────────

            $openingBalance = null;
            if ($ba->account_id) {
                $before = DB::table('journal_entry_lines')
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entries.status', 'posted')
                    ->where('journal_entry_lines.account_id', $ba->account_id)
                    ->where('journal_entries.entry_date', '<', $date)
                    ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                    ->first();
                $openingBalance = round((float) $before->total_debit - (float) $before->total_credit, 2);
            }

            $payments = InvoicePayment::with(['invoice.client', 'recordedBy'])
                ->where('bank_account_id', $ba->id)
                ->whereDate('payment_date', $date)
                ->where('is_reversed', false)
                ->orderBy('payment_date')
                ->get();

            $expenses = Expense::with(['category', 'createdBy'])
                ->where('bank_account_id', $ba->id)
                ->where('status', 'posted')
                ->whereDate('expense_date', $date)
                ->orderBy('expense_date')
                ->get();

            $cashReqs = CashRequest::with(['requestedBy'])
                ->where('bank_account_id', $ba->id)
                ->whereNotNull('paid_at')
                ->whereDate('paid_at', $date)
                ->orderBy('paid_at')
                ->get();

            $totalIn  = round((float) $payments->sum('amount'), 2);
            $totalOut = round(
                (float) $expenses->sum('total_amount') +
                (float) $cashReqs->sum(fn ($cr) => $cr->actual_amount ?? $cr->total_amount),
                2
            );

            if ($openingBalance === null) {
                $openingBalance = round((float) $ba->current_balance - $totalIn + $totalOut, 2);
            }

            $closingBalance = round($openingBalance + $totalIn - $totalOut, 2);

            return [
                'id'              => $ba->id,
                'name'            => $ba->name,
                'account_type'    => $ba->account_type,
                'currency'        => $ba->currency,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'total_in'        => $totalIn,
                'total_out'       => $totalOut,
                'payments'        => $payments,
                'expenses'        => $expenses,
                'cash_reqs'       => $cashReqs,
                'has_activity'    => $totalIn > 0 || $totalOut > 0,
                'is_snapshot'     => false,
                'closed_at'       => null,
            ];
        });

        $currencyTotals = $accounts->groupBy('currency')->map(fn ($group) => [
            'total_in'  => round((float) $group->sum('total_in'), 2),
            'total_out' => round((float) $group->sum('total_out'), 2),
            'net'       => round((float) ($group->sum('total_in') - $group->sum('total_out')), 2),
        ]);

        if ($request->boolean('print')) {
            return view('admin.reports.daily-cashup-print', compact('accounts', 'currencyTotals', 'date'));
        }

        return view('admin.reports.daily-cashup', compact('accounts', 'currencyTotals', 'date'));
    }

    // =========================================================================
    // SNAPSHOT HISTORY
    // =========================================================================

    public function snapshotHistory(Request $request)
    {
        $from = $request->get('from', now()->subDays(29)->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $closings = \App\Models\DailyClosing::with('bankAccount')
            ->whereBetween('closing_date', [$from, $to])
            ->orderBy('closing_date', 'desc')
            ->orderBy('bank_account_id')
            ->get();

        // Group by date for summary rows
        $byDate = $closings->groupBy(fn ($c) => $c->closing_date->toDateString());

        return view('admin.reports.snapshot-history', compact('closings', 'byDate', 'from', 'to'));
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function paginateCollection(\Illuminate\Support\Collection $collection, int $perPage = 10): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        return new LengthAwarePaginator(
            $collection->slice(($page - 1) * $perPage, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }
}
