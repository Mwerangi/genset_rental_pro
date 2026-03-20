<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $client = null;
        $lines  = collect();
        $openingBalance = 0;
        $totalInvoiced  = 0;
        $totalPaid      = 0;
        $closingBalance = 0;

        if ($clientId) {
            $client = Client::findOrFail($clientId);

            // Opening balance = balance of all unpaid invoices BEFORE $from date
            $openingInvoices = Invoice::where('client_id', $clientId)
                ->whereIn('status', ['sent', 'partially_paid', 'disputed', 'draft'])
                ->where('issue_date', '<', $from)
                ->get();
            $openingBalance = $openingInvoices->sum(fn($i) => max(0, (float)$i->total_amount - (float)$i->amount_paid));

            // All invoices in the period
            $invoices = Invoice::with('payments')
                ->where('client_id', $clientId)
                ->whereBetween('issue_date', [$from, $to])
                ->orderBy('issue_date')
                ->orderBy('invoice_number')
                ->get();

            $runningBalance = $openingBalance;

            foreach ($invoices as $inv) {
                // Invoice line (debit)
                $runningBalance += (float) $inv->total_amount;
                $totalInvoiced  += (float) $inv->total_amount;
                $lines->push([
                    'date'        => $inv->issue_date,
                    'type'        => 'invoice',
                    'reference'   => $inv->invoice_number,
                    'description' => 'Invoice',
                    'debit'       => (float) $inv->total_amount,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                    'status'      => $inv->status,
                    'id'          => $inv->id,
                ]);

                // Payment lines (credit)
                foreach ($inv->payments as $pmt) {
                    if ($pmt->payment_date->between($from, $to)) {
                        $runningBalance -= (float) $pmt->amount;
                        $totalPaid      += (float) $pmt->amount;
                        $lines->push([
                            'date'        => $pmt->payment_date,
                            'type'        => 'payment',
                            'reference'   => $pmt->reference ?? 'PMT-' . $pmt->id,
                            'description' => 'Payment — ' . ucfirst(str_replace('_', ' ', $pmt->payment_method ?? '')),
                            'debit'       => 0,
                            'credit'      => (float) $pmt->amount,
                            'balance'     => $runningBalance,
                            'status'      => null,
                            'id'          => null,
                        ]);
                    }
                }
            }

            // Sort all lines by date
            $lines = $lines->sortBy('date')->values();
            $closingBalance = $openingBalance + $totalInvoiced - $totalPaid;
        }

        return view('admin.accounting.reports.statement', compact(
            'clientsList', 'client', 'lines',
            'openingBalance', 'totalInvoiced', 'totalPaid', 'closingBalance',
            'from', 'to', 'clientId'
        ));
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
}
