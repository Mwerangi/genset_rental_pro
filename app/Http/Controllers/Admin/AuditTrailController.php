<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /** Friendly module labels mapped from model_type class names. */
    private const MODULE_LABELS = [
        'App\\Models\\User'            => 'Users',
        'App\\Models\\Booking'         => 'Bookings',
        'App\\Models\\Invoice'         => 'Invoices',
        'App\\Models\\InvoicePayment'  => 'Invoice Payments',
        'App\\Models\\Quotation'       => 'Quotations',
        'App\\Models\\QuoteRequest'    => 'Quote Requests',
        'App\\Models\\CashRequest'     => 'Cash Requests',
        'App\\Models\\Expense'         => 'Expenses',
        'App\\Models\\SupplierPayment' => 'Supplier Payments',
        'App\\Models\\CreditNote'      => 'Credit Notes',
        'App\\Models\\Client'          => 'Clients',
        'App\\Models\\Genset'          => 'Gensets',
        'App\\Models\\MaintenanceRecord' => 'Maintenance',
        'App\\Models\\FuelLog'         => 'Fuel Logs',
        'App\\Models\\PurchaseOrder'   => 'Purchase Orders',
        'App\\Models\\InventoryItem'   => 'Inventory',
        'App\\Models\\JournalEntry'    => 'Journal Entries',
    ];

    /** Resolve a human-readable module label from a model class name. */
    public static function resolveModuleLabel(?string $modelType): string
    {
        if (!$modelType) {
            return '—';
        }
        return self::MODULE_LABELS[$modelType] ?? class_basename($modelType);
    }

    public function index(Request $request)
    {        $query = UserActivityLog::with('user')->latest('created_at');

        // Filter: user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter: action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter: module (model_type)
        if ($request->filled('module')) {
            $query->where('model_type', $request->module);
        }

        // Filter: date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: keyword search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $logs   = $query->paginate(50)->withQueryString();
        $users  = User::orderBy('name')->get(['id', 'name', 'role']);
        $modules = self::MODULE_LABELS;

        $distinctActions = UserActivityLog::distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-trail.index', compact('logs', 'users', 'modules', 'distinctActions'));
    }
}
