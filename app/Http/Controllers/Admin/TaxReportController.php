<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class TaxReportController extends Controller
{
    /**
     * VAT Report: monthly summary of output VAT (from invoices) and input VAT (from expenses).
     */
    public function vatReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        // Output VAT — invoices issued in the period
        $outputVat = Invoice::whereIn('status', ['sent', 'partially_paid', 'paid'])
                            ->whereMonth('issue_date', $mon)
                            ->whereYear('issue_date', $year)
                            ->select('invoice_number', 'client_id', 'issue_date', 'subtotal', 'vat_amount', 'total_amount')
                            ->with('client')
                            ->get();

        $totalOutputVat = $outputVat->sum('vat_amount');

        // Input VAT — expenses in the period (vat_amount > 0)
        $inputVat = \App\Models\Expense::where('vat_amount', '>', 0)
                                        ->whereMonth('expense_date', $mon)
                                        ->whereYear('expense_date', $year)
                                        ->with('category')
                                        ->get();

        $totalInputVat = $inputVat->sum('vat_amount');

        $vatPayable = $totalOutputVat - $totalInputVat;

        // VAT Payable account balance (Account 2120)
        $vatAccount = Account::where('code', '2120')->first();

        return view('admin.accounting.tax-reports.vat', compact(
            'outputVat', 'inputVat', 'totalOutputVat', 'totalInputVat',
            'vatPayable', 'vatAccount', 'month', 'year', 'mon'
        ));
    }

    /**
     * WHT Report: withholding tax deducted on supplier payments.
     */
    public function whtReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        // WHT is typically 2-5% deducted on service payments
        // We capture it via wht_amount on supplier_payments (if we add the column)
        // For now, show supplier payments in the period as a WHT register
        $payments = \App\Models\SupplierPayment::with(['supplier', 'purchaseOrder'])
                                                ->whereMonth('payment_date', $mon)
                                                ->whereYear('payment_date', $year)
                                                ->orderBy('payment_date')
                                                ->get();

        $totalGross = $payments->sum('amount');
        $totalWht   = $payments->sum('withholding_tax');
        $totalNet   = $totalGross - $totalWht;

        // WHT Payable account balance (Account 2130)
        $whtAccount = Account::where('code', '2130')->first();

        return view('admin.accounting.tax-reports.wht', compact(
            'payments', 'totalGross', 'totalWht', 'totalNet', 'whtAccount', 'month', 'year', 'mon'
        ));
    }

    /**
     * Z-Report: daily sales summary for TRA compliance.
     */
    public function zReport(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $invoices = Invoice::whereDate('issue_date', $date)
            ->with('client', 'items')
            ->orderBy('invoice_number')
            ->get();

        $payments = InvoicePayment::with(['invoice.client'])
            ->whereDate('payment_date', $date)
            ->orderBy('payment_date')
            ->get();

        $summary = [
            'invoice_count'  => $invoices->count(),
            'total_subtotal' => $invoices->sum('subtotal'),
            'total_vat'      => $invoices->sum('vat_amount'),
            'total_amount'   => $invoices->sum('total_amount'),
            'cash_received'  => $payments->where('payment_method', 'cash')->sum('amount'),
            'bank_received'  => $payments->whereIn('payment_method', ['bank_transfer', 'cheque', 'mobile_money'])->sum('amount'),
            'total_received' => $payments->sum('amount'),
        ];

        // Payment method breakdown
        $byMethod = $payments->groupBy('payment_method')->map(fn($g) => [
            'count'  => $g->count(),
            'amount' => $g->sum('amount'),
        ]);

        return view('admin.accounting.tax-reports.z-report', compact(
            'invoices', 'payments', 'summary', 'byMethod', 'date'
        ));
    }

    /**
     * Trial Balance — all accounts with their debit/credit totals.
     */
    public function trialBalance(Request $request)
    {
        $asAt = $request->get('as_at', now()->toDateString());

        $accounts = Account::where('is_active', true)
                           ->where('balance', '!=', 0)
                           ->orderBy('code')
                           ->get();

        $totalDebits  = $accounts->filter(fn($a) => $a->balance >= 0)->sum('balance');
        $totalCredits = $accounts->filter(fn($a) => $a->balance < 0)->sum(fn($a) => abs($a->balance));
        $balanced     = round($totalDebits - $totalCredits, 2) === 0.0;

        return view('admin.accounting.tax-reports.trial-balance', compact(
            'accounts', 'totalDebits', 'totalCredits', 'balanced', 'asAt'
        ));
    }
}
