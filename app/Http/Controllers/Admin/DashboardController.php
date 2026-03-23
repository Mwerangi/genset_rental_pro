<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\CashRequest;
use App\Models\Client;
use App\Models\Genset;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        // ── KPI Cards ─────────────────────────────────────────────────
        $activeRentals    = Booking::where('status', 'active')->count();
        $pendingApprovals = Booking::where('status', 'created')->count();

        $monthRevenue = InvoicePayment::whereMonth('payment_date', $month)
                                      ->whereYear('payment_date', $year)
                                      ->sum('amount');

        $lastMonthRevenue = InvoicePayment::whereMonth('payment_date', $now->copy()->subMonth()->month)
                                          ->whereYear('payment_date', $now->copy()->subMonth()->year)
                                          ->sum('amount');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : null;

        $outstandingAmount = Invoice::whereNotIn('status', ['paid', 'void', 'declined'])
                                    ->selectRaw('SUM(total_amount - amount_paid) as total')
                                    ->value('total') ?? 0;

        $overdueCount = Invoice::whereNotIn('status', ['paid', 'void', 'declined'])
                               ->whereDate('due_date', '<', $now)
                               ->count();

        $totalGensets     = Genset::whereNotIn('status', ['retired'])->count();
        $availableGensets = Genset::where('status', 'available')->count();
        $rentedGensets    = Genset::where('status', 'rented')->count();
        $maintenanceGensets = Genset::whereIn('status', ['maintenance', 'repair'])->count();

        // ── Pending Actions ────────────────────────────────────────────
        $pendingCashRequests = CashRequest::where('status', 'pending')->count();
        $newQuoteRequests    = QuoteRequest::where('status', 'new')->count();

        // ── Rentals ending within 3 days ───────────────────────────────
        $endingSoon = Booking::where('status', 'active')
                             ->whereDate('rental_end_date', '<=', $now->copy()->addDays(3))
                             ->whereDate('rental_end_date', '>=', $now->toDateString())
                             ->with('client')
                             ->orderBy('rental_end_date')
                             ->get();

        // ── Recent Invoices ────────────────────────────────────────────
        $recentInvoices = Invoice::with('client')
                                 ->latest('issue_date')
                                 ->take(6)
                                 ->get();

        // ── Account Balances ───────────────────────────────────────────
        $bankAccounts = BankAccount::where('is_active', true)
                                   ->orderBy('name')
                                   ->get(['id', 'name', 'account_type', 'currency', 'current_balance']);

        // ── Recent Bookings needing action ─────────────────────────────
        $actionableBookings = Booking::with('client')
                                     ->whereIn('status', ['created', 'approved', 'returned'])
                                     ->latest()
                                     ->take(5)
                                     ->get();

        // ── Totals for breadcrumb context ─────────────────────────────
        $totalClients = Client::where('status', 'active')->count();

        return view('dashboard', compact(
            'activeRentals', 'pendingApprovals',
            'monthRevenue', 'revenueChange', 'lastMonthRevenue',
            'outstandingAmount', 'overdueCount',
            'totalGensets', 'availableGensets', 'rentedGensets', 'maintenanceGensets',
            'pendingCashRequests', 'newQuoteRequests',
            'endingSoon',
            'recentInvoices',
            'bankAccounts',
            'actionableBookings',
            'totalClients'
        ));
    }
}
