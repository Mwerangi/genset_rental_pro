<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Only full accounting managers see all supplier payments; others see only their own
        $seeAll = PermissionService::can($user, 'manage_accounting');

        $query = SupplierPayment::with(['supplier', 'bankAccount', 'purchaseOrder', 'createdBy'])
                                 ->latest('payment_date');

        if (!$seeAll) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from')) {
            $query->where('payment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('payment_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }

        $payments  = $query->paginate(25)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $base         = $seeAll ? SupplierPayment::query() : SupplierPayment::where('created_by', $user->id);
        $monthTotal   = (clone $base)->whereMonth('payment_date', now()->month)
                                     ->whereYear('payment_date', now()->year)
                                     ->sum('amount');
        $allTimeTotal = (clone $base)->sum('amount');

        return view('admin.accounting.supplier-payments.index', compact('payments', 'suppliers', 'monthTotal', 'allTimeTotal', 'seeAll'));
    }

    public function create(Request $request)
    {
        $suppliers    = Supplier::orderBy('name')->get(['id', 'name']);
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        // Pre-select PO if provided
        $purchaseOrder = $request->filled('po') ? PurchaseOrder::find($request->po) : null;

        // POs that have outstanding balance (partially or fully received, not yet paid)
        $pendingOrders = PurchaseOrder::with(['supplier', 'items', 'supplierPayments'])
                                      ->whereIn('status', ['partial', 'received'])
                                      ->orderBy('created_at', 'desc')
                                      ->get()
                                      ->map(function ($po) {
                                          $receivedValue = round(
                                              $po->items->sum(fn ($i) => $i->quantity_received * $i->unit_cost), 2
                                          );
                                          $totalPaid  = round((float) $po->supplierPayments->sum('amount'), 2);
                                          $po->balance_due = max(0, $receivedValue - $totalPaid);
                                          return $po;
                                      })
                                      ->filter(fn ($po) => $po->balance_due > 0)
                                      ->values();

        return view('admin.accounting.supplier-payments.create', compact(
            'suppliers', 'bankAccounts', 'purchaseOrder', 'pendingOrders'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id'  => 'nullable|exists:purchase_orders,id',
            'supplier_id'        => 'required|exists:suppliers,id',
            'bank_account_id'    => 'required|exists:bank_accounts,id',
            'amount'             => 'required|numeric|min:0.01',
            'withholding_tax'    => 'nullable|numeric|min:0',
            'payment_date'       => 'required|date',
            'payment_method'     => 'required|in:bank_transfer,mobile_money,cheque,cash',
            'reference'          => 'nullable|string|max:200',
            'tax_invoice_number' => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $data['withholding_tax'] = $data['withholding_tax'] ?? 0;

        $data['created_by'] = auth()->id();

        $payment = SupplierPayment::create($data);

        // Auto-post journal entry
        $je = app(JournalEntryService::class)->onSupplierPayment($payment);
        if ($je) {
            $payment->update(['journal_entry_id' => $je->id]);
        }

        return redirect()->route('admin.accounting.supplier-payments.show', $payment)
                         ->with('success', 'Supplier payment recorded' . ($je ? " and posted (JE: {$je->entry_number})." : '.'));
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $user = auth()->user();
        if (!PermissionService::can($user, 'manage_accounting') && $supplierPayment->created_by !== $user->id) {
            abort(403, 'You do not have permission to view this supplier payment.');
        }
        $supplierPayment->load(['supplier', 'bankAccount', 'purchaseOrder', 'journalEntry.lines.account', 'createdBy', 'confirmedBy']);
        return view('admin.accounting.supplier-payments.show', compact('supplierPayment'));
    }

    public function confirm(Request $request, SupplierPayment $supplierPayment)
    {
        $request->validate([
            'remittance_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $remittancePath = $supplierPayment->remittance_path;

        if ($request->hasFile('remittance_file')) {
            if ($remittancePath) {
                Storage::disk('private')->delete($remittancePath);
            }
            $remittancePath = $request->file('remittance_file')
                ->store('remittances/' . $supplierPayment->id, 'private');
        }

        $supplierPayment->update([
            'status'          => 'confirmed',
            'confirmed_by'    => auth()->id(),
            'confirmed_at'    => now(),
            'remittance_path' => $remittancePath,
        ]);

        return back()->with('success', 'Payment confirmed successfully.');
    }

    public function serveRemittance(SupplierPayment $supplierPayment)
    {
        abort_unless($supplierPayment->remittance_path, 404);
        abort_unless(Storage::disk('private')->exists($supplierPayment->remittance_path), 404);

        return Storage::disk('private')->download($supplierPayment->remittance_path);
    }
}
