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
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        $canViewFleet       = PermissionService::can($user, 'view_fleet');
        $canViewBookings    = PermissionService::can($user, 'view_bookings');
        $canViewInvoices    = PermissionService::can($user, 'view_invoices');
        $canViewAccounting  = PermissionService::can($user, 'view_accounting');
        $canViewExpenses    = PermissionService::can($user, 'view_expenses');
        $canViewCashReqs    = PermissionService::can($user, 'view_cash_requests');
        $canViewQuoteReqs   = PermissionService::can($user, 'view_quote_requests');
        $canApproveBookings = PermissionService::can($user, 'approve_bookings');
        $canApproveExpenses = PermissionService::can($user, 'approve_expenses');
        $canApproveCash     = PermissionService::can($user, 'approve_cash_requests');

        // ── KPI Cards — only for roles that can see them ──────────────
        $activeRentals      = $canViewBookings  ? Booking::where('status', 'active')->count() : null;
        $pendingApprovals   = $canApproveBookings ? Booking::where('status', 'created')->count() : null;

        $monthRevenue = $canViewInvoices
            ? InvoicePayment::whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount')
            : null;

        $lastMonthRevenue = $canViewInvoices
            ? InvoicePayment::whereMonth('payment_date', $now->copy()->subMonth()->month)
                             ->whereYear('payment_date', $now->copy()->subMonth()->year)
                             ->sum('amount')
            : null;

        $revenueChange = ($canViewInvoices && $lastMonthRevenue > 0)
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : null;

        $outstandingAmount = $canViewInvoices
            ? Invoice::whereNotIn('status', ['paid', 'void', 'declined'])
                     ->selectRaw('SUM(total_amount - amount_paid) as total')->value('total') ?? 0
            : null;

        $overdueCount = $canViewInvoices
            ? Invoice::whereNotIn('status', ['paid', 'void', 'declined'])
                     ->whereDate('due_date', '<', $now)->count()
            : null;

        $totalGensets       = $canViewFleet ? Genset::whereNotIn('status', ['retired'])->count() : null;
        $availableGensets   = $canViewFleet ? Genset::where('status', 'available')->count() : null;
        $rentedGensets      = $canViewFleet ? Genset::where('status', 'rented')->count() : null;
        $maintenanceGensets = $canViewFleet ? Genset::whereIn('status', ['maintenance', 'repair'])->count() : null;

        // ── Pending Actions ────────────────────────────────────────────
        $pendingCashRequests = $canApproveCash    ? CashRequest::where('status', 'pending')->count() : null;
        $newQuoteRequests    = $canViewQuoteReqs  ? QuoteRequest::where('status', 'new')->count() : null;

        // ── My cash requests (for staff/field roles) ──────────────────
        $myCashRequests = ($canViewCashReqs && !$canApproveCash)
            ? CashRequest::where('created_by', $user->id)->whereIn('status', ['draft', 'pending'])->count()
            : null;

        // ── Rentals ending within 3 days ──────────────────────────────
        $endingSoon = $canViewBookings
            ? Booking::where('status', 'active')
                     ->whereDate('rental_end_date', '<=', $now->copy()->addDays(3))
                     ->whereDate('rental_end_date', '>=', $now->toDateString())
                     ->with('client')->orderBy('rental_end_date')->get()
            : collect();

        // ── Recent Invoices ────────────────────────────────────────────
        $recentInvoices = $canViewInvoices
            ? Invoice::with('client')->latest('issue_date')->take(6)->get()
            : collect();

        // ── Account Balances (finance/accounting roles only) ──────────
        $bankAccounts = $canViewAccounting
            ? BankAccount::where('is_active', true)->orderBy('name')
                         ->get(['id', 'name', 'account_type', 'currency', 'current_balance'])
            : collect();

        // ── Bookings needing action ────────────────────────────────────
        $actionableBookings = $canViewBookings
            ? Booking::with('client')->whereIn('status', ['created', 'approved', 'returned'])->latest()->take(5)->get()
            : collect();

        $totalClients = $canViewBookings ? Client::where('status', 'active')->count() : null;

        return view('dashboard', compact(
            'user',
            'canViewFleet', 'canViewBookings', 'canViewInvoices',
            'canViewAccounting', 'canViewExpenses', 'canViewCashReqs',
            'canViewQuoteReqs', 'canApproveBookings', 'canApproveExpenses', 'canApproveCash',
            'activeRentals', 'pendingApprovals',
            'monthRevenue', 'revenueChange', 'lastMonthRevenue',
            'outstandingAmount', 'overdueCount',
            'totalGensets', 'availableGensets', 'rentedGensets', 'maintenanceGensets',
            'pendingCashRequests', 'newQuoteRequests', 'myCashRequests',
            'endingSoon',
            'recentInvoices',
            'bankAccounts',
            'actionableBookings',
            'totalClients'
        ));
    }
}
