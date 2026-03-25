<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\GensetController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\InventoryCategoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\FuelLogController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AccountTransferController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\JournalEntryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\CashRequestController;
use App\Http\Controllers\Admin\CreditNoteController;
use App\Http\Controllers\Admin\TaxReportController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\QuoteRequestController as PublicQuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public quote request submission (from landing page)
Route::post('/quote-request', [PublicQuoteRequestController::class, 'store'])->name('quote-request.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // ── Notifications (always accessible to any authenticated user) ──────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // ── Sales Pipeline — Quote Requests ──────────────────────────────────────
    Route::middleware('permission:view_quote_requests')->group(function () {
        Route::get('/quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
        Route::get('/quote-requests/export', [QuoteRequestController::class, 'export'])->name('quote-requests.export');
        Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
        Route::middleware('permission:review_quote_requests')->group(function () {
            Route::post('/quote-requests/{quoteRequest}/mark-as-reviewed', [QuoteRequestController::class, 'markAsReviewed'])->name('quote-requests.mark-as-reviewed');
            Route::post('/quote-requests/{quoteRequest}/reject', [QuoteRequestController::class, 'reject'])->name('quote-requests.reject');
        });
    });

    // ── Sales Pipeline — Quotations ───────────────────────────────────────────
    Route::middleware('permission:view_quotations')->group(function () {
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/approved', [QuotationController::class, 'approved'])->name('quotations.approved');
        Route::get('/quotations/rejected', [QuotationController::class, 'rejected'])->name('quotations.rejected');
        Route::middleware('permission:create_quotations')->group(function () {
            Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
            Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        });
        Route::middleware('permission:edit_quotations')->group(function () {
            Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
            Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        });
        Route::middleware('permission:approve_quotations')->group(function () {
            Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
            Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
        });
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.download-pdf');
    });

    // ── Bookings ──────────────────────────────────────────────────────────────
    Route::middleware('permission:view_bookings')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/active-rentals', [BookingController::class, 'activeRentals'])->name('bookings.active-rentals');
        Route::middleware('permission:create_bookings')->group(function () {
            Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        });
        Route::middleware('permission:edit_bookings')->group(function () {
            Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
            Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        });
        Route::middleware('permission:activate_bookings')->group(function () {
            Route::post('/bookings/{booking}/activate', [BookingController::class, 'activate'])->name('bookings.activate');
            Route::post('/bookings/{booking}/generate-invoice', [InvoiceController::class, 'generate'])->name('bookings.generate-invoice');
            Route::post('/bookings/{booking}/generate-proforma', [InvoiceController::class, 'generateProforma'])->name('bookings.generate-proforma');
        });
        Route::middleware('permission:return_bookings')->group(function () {
            Route::post('/bookings/{booking}/return', [BookingController::class, 'markReturned'])->name('bookings.return');
            Route::post('/bookings/{booking}/invoice', [BookingController::class, 'markInvoiced'])->name('bookings.invoice');
            Route::post('/bookings/{booking}/mark-paid', [BookingController::class, 'markPaid'])->name('bookings.mark-paid');
        });
        Route::middleware('permission:cancel_bookings')->group(function () {
            Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        });
        Route::middleware('permission:approve_bookings')->group(function () {
            Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
            Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
        });
        Route::get('/bookings/{booking}/contract', [BookingController::class, 'contractPdf'])->name('bookings.contract');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });

    // ── Invoices ──────────────────────────────────────────────────────────────
    Route::middleware('permission:view_invoices')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::middleware('permission:edit_invoices')->group(function () {
            Route::post('/invoices/{invoice}/convert-proforma', [InvoiceController::class, 'convertProforma'])->name('invoices.convert-proforma');
            Route::post('/invoices/{invoice}/items', [InvoiceController::class, 'storeItem'])->name('invoices.items.store');
            Route::put('/invoices/{invoice}/items/{item}', [InvoiceController::class, 'updateItem'])->name('invoices.items.update');
            Route::delete('/invoices/{invoice}/items/{item}', [InvoiceController::class, 'deleteItem'])->name('invoices.items.delete');
            Route::patch('/invoices/{invoice}/discount', [InvoiceController::class, 'updateDiscount'])->name('invoices.discount.update');
        });
        Route::middleware('permission:send_invoices')->group(function () {
            Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.mark-sent');
        });
        Route::middleware('permission:record_invoice_payment')->group(function () {
            Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
            Route::delete('/invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'deletePayment'])->name('invoices.payments.delete');
            Route::post('/invoices/{invoice}/payments/{payment}/reverse', [InvoiceController::class, 'reversePayment'])->name('invoices.payments.reverse');
        });
        Route::middleware('permission:void_invoices')->group(function () {
            Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
        });
        Route::middleware('permission:write_off_invoices')->group(function () {
            Route::post('/invoices/{invoice}/dispute', [InvoiceController::class, 'dispute'])->name('invoices.dispute');
            Route::post('/invoices/{invoice}/write-off', [InvoiceController::class, 'writeOff'])->name('invoices.write-off');
        });
    });

    // ── Clients (CRM) ────────────────────────────────────────────────────────
    Route::middleware('permission:view_clients')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::middleware('permission:create_clients')->group(function () {
            Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
            Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        });
        Route::middleware('permission:edit_clients')->group(function () {
            Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
            Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        });
        Route::middleware('permission:manage_client_contacts')->group(function () {
            Route::post('/clients/{client}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
            Route::delete('/clients/{client}/contacts/{contact}', [ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');
            Route::post('/clients/{client}/addresses', [ClientController::class, 'storeAddress'])->name('clients.addresses.store');
            Route::delete('/clients/{client}/addresses/{address}', [ClientController::class, 'destroyAddress'])->name('clients.addresses.destroy');
        });
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });

    // ── Fleet — Gensets ───────────────────────────────────────────────────────
    Route::middleware('permission:view_fleet')->group(function () {
        Route::get('/gensets', [GensetController::class, 'index'])->name('gensets.index');
        Route::middleware('permission:create_gensets')->group(function () {
            Route::get('/gensets/create', [GensetController::class, 'create'])->name('gensets.create');
            Route::post('/gensets', [GensetController::class, 'store'])->name('gensets.store');
        });
        Route::middleware('permission:edit_gensets')->group(function () {
            Route::get('/gensets/{genset}/edit', [GensetController::class, 'edit'])->name('gensets.edit');
            Route::put('/gensets/{genset}', [GensetController::class, 'update'])->name('gensets.update');
        });
        Route::middleware('permission:delete_gensets')->group(function () {
            Route::delete('/gensets/{genset}', [GensetController::class, 'destroy'])->name('gensets.destroy');
        });
        Route::middleware('permission:update_genset_status')->group(function () {
            Route::post('/gensets/{genset}/status', [GensetController::class, 'updateStatus'])->name('gensets.status');
        });
        Route::get('/gensets/{genset}', [GensetController::class, 'show'])->name('gensets.show');
    });

    // ── Fleet — Deliveries ────────────────────────────────────────────────────
    Route::middleware('permission:view_deliveries')->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
        Route::middleware('permission:create_deliveries')->group(function () {
            Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
        });
        Route::middleware('permission:dispatch_deliveries')->group(function () {
            Route::post('/deliveries/{delivery}/dispatch', [DeliveryController::class, 'dispatch'])->name('deliveries.dispatch');
        });
        Route::middleware('permission:complete_deliveries')->group(function () {
            Route::post('/deliveries/{delivery}/complete', [DeliveryController::class, 'complete'])->name('deliveries.complete');
            Route::post('/deliveries/{delivery}/fail', [DeliveryController::class, 'fail'])->name('deliveries.fail');
        });
    });

    // ── Fleet — Maintenance ───────────────────────────────────────────────────
    Route::middleware('permission:view_maintenance')->group(function () {
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::middleware('permission:create_maintenance')->group(function () {
            Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
            Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        });
        Route::middleware('permission:edit_maintenance')->group(function () {
            Route::get('/maintenance/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
            Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
        });
        Route::middleware('permission:delete_maintenance')->group(function () {
            Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
        });
        Route::middleware('permission:start_maintenance')->group(function () {
            Route::post('/maintenance/{maintenance}/start', [MaintenanceController::class, 'start'])->name('maintenance.start');
        });
        Route::middleware('permission:complete_maintenance')->group(function () {
            Route::post('/maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
        });
        Route::middleware('permission:cancel_maintenance')->group(function () {
            Route::post('/maintenance/{maintenance}/cancel', [MaintenanceController::class, 'cancel'])->name('maintenance.cancel');
        });
        Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    });
    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::middleware('permission:view_inventory')->group(function () {
        Route::get('/inventory/categories', [InventoryCategoryController::class, 'index'])->name('inventory.categories.index');
        Route::get('/inventory/items', [InventoryItemController::class, 'index'])->name('inventory.items.index');
        Route::middleware('permission:manage_inventory_categories')->group(function () {
            Route::post('/inventory/categories', [InventoryCategoryController::class, 'store'])->name('inventory.categories.store');
            Route::put('/inventory/categories/{category}', [InventoryCategoryController::class, 'update'])->name('inventory.categories.update');
            Route::delete('/inventory/categories/{category}', [InventoryCategoryController::class, 'destroy'])->name('inventory.categories.destroy');
        });
        Route::middleware('permission:create_inventory_items')->group(function () {
            Route::get('/inventory/items/create', [InventoryItemController::class, 'create'])->name('inventory.items.create');
            Route::post('/inventory/items', [InventoryItemController::class, 'store'])->name('inventory.items.store');
        });
        Route::middleware('permission:edit_inventory_items')->group(function () {
            Route::get('/inventory/items/{item}/edit', [InventoryItemController::class, 'edit'])->name('inventory.items.edit');
            Route::put('/inventory/items/{item}', [InventoryItemController::class, 'update'])->name('inventory.items.update');
        });
        Route::middleware('permission:adjust_inventory_stock')->group(function () {
            Route::post('/inventory/items/{item}/adjust', [InventoryItemController::class, 'adjust'])->name('inventory.items.adjust');
        });
        Route::get('/inventory/items/{item}', [InventoryItemController::class, 'show'])->name('inventory.items.show');
    });

    // ── Suppliers ─────────────────────────────────────────────────────────────
    Route::middleware('permission:view_suppliers')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::middleware('permission:create_suppliers')->group(function () {
            Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        });
        Route::middleware('permission:edit_suppliers')->group(function () {
            Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        });
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });

    // ── Purchase Orders ───────────────────────────────────────────────────────
    Route::middleware('permission:view_purchase_orders')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::middleware('permission:create_purchase_orders')->group(function () {
            Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
            Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        });
        Route::middleware('permission:send_purchase_orders')->group(function () {
            Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        });
        Route::middleware('permission:receive_purchase_orders')->group(function () {
            Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        });
        Route::middleware('permission:cancel_purchase_orders')->group(function () {
            Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        });
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    });

    // ── Fuel Logs ─────────────────────────────────────────────────────────────
    Route::middleware('permission:view_fuel_logs')->group(function () {
        Route::get('/fuel-logs', [FuelLogController::class, 'index'])->name('fuel-logs.index');
        Route::get('/fuel-logs/genset/{genset}', [FuelLogController::class, 'gensetLogs'])->name('fuel-logs.genset');
        Route::middleware('permission:create_fuel_logs')->group(function () {
            Route::post('/fuel-logs', [FuelLogController::class, 'store'])->name('fuel-logs.store');
        });
    });

    // ─── ACCOUNTING MODULE ────────────────────────────────────────────────────

    // Chart of Accounts & Bank Accounts
    Route::middleware('permission:view_accounting')->group(function () {
        Route::get('/accounting/accounts', [AccountController::class, 'index'])->name('accounting.accounts.index');
        Route::get('/accounting/bank-accounts', [BankAccountController::class, 'index'])->name('accounting.bank-accounts.index');
        Route::middleware('permission:manage_accounts')->group(function () {
            Route::get('/accounting/accounts/create', [AccountController::class, 'create'])->name('accounting.accounts.create');
            Route::post('/accounting/accounts', [AccountController::class, 'store'])->name('accounting.accounts.store');
            Route::get('/accounting/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounting.accounts.edit');
            Route::put('/accounting/accounts/{account}', [AccountController::class, 'update'])->name('accounting.accounts.update');
            Route::delete('/accounting/accounts/{account}', [AccountController::class, 'destroy'])->name('accounting.accounts.destroy');
        });
        Route::middleware('permission:manage_bank_accounts')->group(function () {
            Route::get('/accounting/bank-accounts/create', [BankAccountController::class, 'create'])->name('accounting.bank-accounts.create');
            Route::post('/accounting/bank-accounts', [BankAccountController::class, 'store'])->name('accounting.bank-accounts.store');
            Route::get('/accounting/bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('accounting.bank-accounts.edit');
            Route::put('/accounting/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('accounting.bank-accounts.update');
            Route::delete('/accounting/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('accounting.bank-accounts.destroy');
            Route::post('/accounting/account-transfers', [AccountTransferController::class, 'store'])->name('accounting.account-transfers.store');
        });
        Route::middleware('permission:manage_expense_categories')->group(function () {
            Route::get('/accounting/expense-categories', [ExpenseCategoryController::class, 'index'])->name('accounting.expense-categories.index');
            Route::get('/accounting/expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('accounting.expense-categories.create');
            Route::post('/accounting/expense-categories', [ExpenseCategoryController::class, 'store'])->name('accounting.expense-categories.store');
            Route::get('/accounting/expense-categories/{expenseCategory}/edit', [ExpenseCategoryController::class, 'edit'])->name('accounting.expense-categories.edit');
            Route::put('/accounting/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('accounting.expense-categories.update');
            Route::delete('/accounting/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('accounting.expense-categories.destroy');
        });
        Route::get('/accounting/accounts/{account}', [AccountController::class, 'show'])->name('accounting.accounts.show');
        Route::get('/accounting/bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->name('accounting.bank-accounts.show');
    });

    // Journal Entries
    Route::middleware('permission:view_accounting|view_journal_entries')->group(function () {
        Route::get('/accounting/journal-entries', [JournalEntryController::class, 'index'])->name('accounting.journal-entries.index');
        Route::get('/accounting/journal-entries/export', [JournalEntryController::class, 'export'])->name('accounting.journal-entries.export');
        Route::get('/accounting/journal-entries/{journalEntry}', [JournalEntryController::class, 'show'])->name('accounting.journal-entries.show');
        Route::middleware('permission:create_journal_entries')->group(function () {
            Route::get('/accounting/journal-entries/create', [JournalEntryController::class, 'create'])->name('accounting.journal-entries.create');
            Route::post('/accounting/journal-entries', [JournalEntryController::class, 'store'])->name('accounting.journal-entries.store');
        });
        Route::middleware('permission:post_journal_entries')->group(function () {
            Route::post('/accounting/journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])->name('accounting.journal-entries.post');
        });
        Route::middleware('permission:reverse_journal_entries')->group(function () {
            Route::post('/accounting/journal-entries/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('accounting.journal-entries.reverse');
        });
    });

    // Expenses
    Route::middleware('permission:view_expenses')->group(function () {
        Route::get('/accounting/expenses', [ExpenseController::class, 'index'])->name('accounting.expenses.index');
        Route::middleware('permission:create_expenses')->group(function () {
            Route::get('/accounting/expenses/create', [ExpenseController::class, 'create'])->name('accounting.expenses.create');
            Route::post('/accounting/expenses', [ExpenseController::class, 'store'])->name('accounting.expenses.store');
        });
        Route::get('/accounting/expenses/{expense}', [ExpenseController::class, 'show'])->name('accounting.expenses.show');
        Route::middleware('permission:delete_expenses')->group(function () {
            Route::delete('/accounting/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('accounting.expenses.destroy');
        });
        Route::middleware('permission:approve_expenses')->group(function () {
            Route::post('/accounting/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('accounting.expenses.approve');
            Route::post('/accounting/expenses/{expense}/post', [ExpenseController::class, 'post'])->name('accounting.expenses.post');
        });
    });

    // Supplier Payments
    Route::middleware('permission:view_supplier_payments')->group(function () {
        Route::get('/accounting/supplier-payments', [SupplierPaymentController::class, 'index'])->name('accounting.supplier-payments.index');
        Route::get('/accounting/supplier-payments/{supplierPayment}', [SupplierPaymentController::class, 'show'])->name('accounting.supplier-payments.show');
        Route::get('/accounting/supplier-payments/{supplierPayment}/remittance', [SupplierPaymentController::class, 'serveRemittance'])->name('accounting.supplier-payments.remittance');
        Route::middleware('permission:create_supplier_payments')->group(function () {
            Route::get('/accounting/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('accounting.supplier-payments.create');
            Route::post('/accounting/supplier-payments', [SupplierPaymentController::class, 'store'])->name('accounting.supplier-payments.store');
        });
        Route::middleware('permission:confirm_supplier_payments')->group(function () {
            Route::post('/accounting/supplier-payments/{supplierPayment}/confirm', [SupplierPaymentController::class, 'confirm'])->name('accounting.supplier-payments.confirm');
        });
    });

    // Cash Requests (Petty Cash) — self-service: view_cash_requests lets staff submit requests
    Route::middleware('permission:view_accounting|view_cash_requests')->group(function () {
        Route::get('/accounting/cash-requests', [CashRequestController::class, 'index'])->name('accounting.cash-requests.index');
        Route::get('/accounting/cash-requests/create', [CashRequestController::class, 'create'])->name('accounting.cash-requests.create');
        Route::post('/accounting/cash-requests', [CashRequestController::class, 'store'])->name('accounting.cash-requests.store');
        Route::get('/accounting/cash-requests/{cashRequest}', [CashRequestController::class, 'show'])->name('accounting.cash-requests.show');
        Route::get('/accounting/cash-requests/{cashRequest}/edit', [CashRequestController::class, 'edit'])->name('accounting.cash-requests.edit');
        Route::put('/accounting/cash-requests/{cashRequest}', [CashRequestController::class, 'update'])->name('accounting.cash-requests.update');
        Route::delete('/accounting/cash-requests/{cashRequest}', [CashRequestController::class, 'destroy'])->name('accounting.cash-requests.destroy');
        Route::post('/accounting/cash-requests/{cashRequest}/submit', [CashRequestController::class, 'submit'])->name('accounting.cash-requests.submit');
        Route::get('/accounting/cash-requests/{cashRequest}/receipt/{item}', [CashRequestController::class, 'downloadReceipt'])->name('accounting.cash-requests.receipt');
        // Approval actions require approve_cash_requests (or full view_accounting)
        Route::middleware('permission:view_accounting|approve_cash_requests')->group(function () {
            Route::post('/accounting/cash-requests/{cashRequest}/approve', [CashRequestController::class, 'approve'])->name('accounting.cash-requests.approve');
            Route::post('/accounting/cash-requests/{cashRequest}/reject', [CashRequestController::class, 'reject'])->name('accounting.cash-requests.reject');
            Route::post('/accounting/cash-requests/{cashRequest}/pay', [CashRequestController::class, 'pay'])->name('accounting.cash-requests.pay');
            Route::post('/accounting/cash-requests/{cashRequest}/retire', [CashRequestController::class, 'retire'])->name('accounting.cash-requests.retire');
        });
    });

    // Credit Notes
    Route::middleware('permission:view_credit_notes')->group(function () {
        Route::get('/accounting/credit-notes', [CreditNoteController::class, 'index'])->name('accounting.credit-notes.index');
        Route::get('/accounting/credit-notes/{creditNote}', [CreditNoteController::class, 'show'])->name('accounting.credit-notes.show');
        Route::middleware('permission:create_credit_notes')->group(function () {
            Route::get('/accounting/credit-notes/create', [CreditNoteController::class, 'create'])->name('accounting.credit-notes.create');
            Route::post('/accounting/credit-notes', [CreditNoteController::class, 'store'])->name('accounting.credit-notes.store');
        });
        Route::middleware('permission:issue_credit_notes')->group(function () {
            Route::post('/accounting/credit-notes/{creditNote}/issue', [CreditNoteController::class, 'issue'])->name('accounting.credit-notes.issue');
        });
        Route::middleware('permission:void_credit_notes')->group(function () {
            Route::post('/accounting/credit-notes/{creditNote}/void', [CreditNoteController::class, 'void'])->name('accounting.credit-notes.void');
        });
    });

    // ── Reports ───────────────────────────────────────────────────────────────

    // Sales Reports
    Route::middleware('permission:view_sales_reports')->group(function () {
        Route::get('/reports/sales/funnel', [ReportsController::class, 'salesFunnel'])->name('reports.sales.funnel');
        Route::get('/reports/sales/revenue-by-client', [ReportsController::class, 'revenueByClient'])->name('reports.sales.revenue-by-client');
        Route::get('/reports/sales/revenue-by-client/export', [ReportsController::class, 'revenueByClientExport'])->name('reports.sales.revenue-by-client.export');
        Route::get('/reports/sales/pipeline', [ReportsController::class, 'salesPipeline'])->name('reports.sales.pipeline');
        Route::get('/reports/sales/pipeline/export', [ReportsController::class, 'salesPipelineExport'])->name('reports.sales.pipeline.export');
    });

    // Fleet & Operations Reports
    Route::middleware('permission:view_fleet_reports')->group(function () {
        Route::get('/reports/fleet/utilization', [ReportsController::class, 'fleetUtilization'])->name('reports.fleet.utilization');
        Route::get('/reports/fleet/revenue-by-genset', [ReportsController::class, 'revenueByGenset'])->name('reports.fleet.revenue-by-genset');
        Route::get('/reports/fleet/bookings', [ReportsController::class, 'bookingSummary'])->name('reports.fleet.bookings');
        Route::get('/reports/fleet/bookings/export', [ReportsController::class, 'bookingSummaryExport'])->name('reports.fleet.bookings.export');
        Route::get('/reports/fleet/fuel', [ReportsController::class, 'fuelConsumption'])->name('reports.fleet.fuel');
        Route::get('/reports/fleet/fuel/export', [ReportsController::class, 'fuelConsumptionExport'])->name('reports.fleet.fuel.export');
        Route::get('/reports/fleet/maintenance', [ReportsController::class, 'maintenanceCosts'])->name('reports.fleet.maintenance');
        Route::get('/reports/fleet/maintenance/export', [ReportsController::class, 'maintenanceCostsExport'])->name('reports.fleet.maintenance.export');
        Route::get('/reports/fleet/overdue-service', [ReportsController::class, 'overdueServicing'])->name('reports.fleet.overdue-service');
        Route::get('/reports/fleet/overdue-service/export', [ReportsController::class, 'overdueServicingExport'])->name('reports.fleet.overdue-service.export');
    });

    // Financial Reports (P&L, Balance Sheet, Tax, Ledger, Invoices)
    Route::middleware('permission:view_financial_reports')->group(function () {
        Route::get('/accounting/tax-reports/vat', [TaxReportController::class, 'vatReport'])->name('accounting.tax-reports.vat');
        Route::get('/accounting/tax-reports/wht', [TaxReportController::class, 'whtReport'])->name('accounting.tax-reports.wht');
        Route::get('/accounting/tax-reports/z-report', [TaxReportController::class, 'zReport'])->name('accounting.tax-reports.z-report');
        Route::get('/accounting/tax-reports/trial-balance', [TaxReportController::class, 'trialBalance'])->name('accounting.tax-reports.trial-balance');
        Route::get('/accounting/reports/profit-loss', [ReportsController::class, 'profitLoss'])->name('accounting.reports.profit-loss');
        Route::get('/accounting/reports/balance-sheet', [ReportsController::class, 'balanceSheet'])->name('accounting.reports.balance-sheet');
        Route::get('/accounting/reports/aging', [ReportsController::class, 'aging'])->name('accounting.reports.aging');
        Route::get('/accounting/reports/statement', [ReportsController::class, 'statement'])->name('accounting.reports.statement');
        Route::get('/accounting/reports/payables', [ReportsController::class, 'payables'])->name('accounting.reports.payables');
        Route::get('/reports/invoices/revenue-by-period', [ReportsController::class, 'revenueByPeriod'])->name('reports.invoices.revenue-by-period');
        Route::get('/reports/invoices/revenue-by-period/export', [ReportsController::class, 'revenueByPeriodExport'])->name('reports.invoices.revenue-by-period.export');
        Route::get('/reports/invoices/payment-methods', [ReportsController::class, 'paymentMethods'])->name('reports.invoices.payment-methods');
        Route::get('/reports/invoices/payment-methods/export', [ReportsController::class, 'paymentMethodsExport'])->name('reports.invoices.payment-methods.export');
        Route::get('/reports/invoices/outstanding', [ReportsController::class, 'outstandingInvoices'])->name('reports.invoices.outstanding');
        Route::get('/reports/invoices/outstanding/export', [ReportsController::class, 'outstandingInvoicesExport'])->name('reports.invoices.outstanding.export');
        Route::get('/reports/accounting/general-ledger', [ReportsController::class, 'generalLedger'])->name('reports.accounting.general-ledger');
        Route::get('/reports/accounting/general-ledger/export', [ReportsController::class, 'generalLedgerExport'])->name('reports.accounting.general-ledger.export');
    });

    // Expense Reports
    Route::middleware('permission:view_expense_reports')->group(function () {
        Route::get('/reports/expenses/by-category', [ReportsController::class, 'expensesByCategory'])->name('reports.expenses.by-category');
        Route::get('/reports/expenses/by-category/export', [ReportsController::class, 'expensesByCategoryExport'])->name('reports.expenses.by-category.export');
        Route::get('/reports/expenses/by-period', [ReportsController::class, 'expensesByPeriod'])->name('reports.expenses.by-period');
        Route::get('/reports/expenses/by-period/export', [ReportsController::class, 'expensesByPeriodExport'])->name('reports.expenses.by-period.export');
        Route::get('/reports/expenses/petty-cash', [ReportsController::class, 'pettyCashSummary'])->name('reports.expenses.petty-cash');
        Route::get('/reports/expenses/petty-cash/export', [ReportsController::class, 'pettyCashSummaryExport'])->name('reports.expenses.petty-cash.export');
        Route::get('/reports/expenses/gross-margin', [ReportsController::class, 'grossMargin'])->name('reports.expenses.gross-margin');
        Route::get('/reports/expenses/gross-margin/export', [ReportsController::class, 'grossMarginExport'])->name('reports.expenses.gross-margin.export');
    });

    // Inventory & Procurement Reports
    Route::middleware('permission:view_inventory_reports')->group(function () {
        Route::get('/reports/inventory/stock-levels', [ReportsController::class, 'stockLevels'])->name('reports.inventory.stock-levels');
        Route::get('/reports/inventory/stock-levels/export', [ReportsController::class, 'stockLevelsExport'])->name('reports.inventory.stock-levels.export');
        Route::get('/reports/inventory/movements', [ReportsController::class, 'stockMovements'])->name('reports.inventory.movements');
        Route::get('/reports/inventory/movements/export', [ReportsController::class, 'stockMovementsExport'])->name('reports.inventory.movements.export');
        Route::get('/reports/inventory/valuation', [ReportsController::class, 'inventoryValuation'])->name('reports.inventory.valuation');
        Route::get('/reports/inventory/valuation/export', [ReportsController::class, 'inventoryValuationExport'])->name('reports.inventory.valuation.export');
        Route::get('/reports/procurement/supplier-payments', [ReportsController::class, 'supplierPaymentHistory'])->name('reports.procurement.supplier-payments');
        Route::get('/reports/procurement/supplier-payments/export', [ReportsController::class, 'supplierPaymentHistoryExport'])->name('reports.procurement.supplier-payments.export');
        Route::get('/reports/procurement/purchase-orders', [ReportsController::class, 'purchaseOrderSummary'])->name('reports.procurement.purchase-orders');
        Route::get('/reports/procurement/purchase-orders/export', [ReportsController::class, 'purchaseOrderSummaryExport'])->name('reports.procurement.purchase-orders.export');
    });

    // Executive Summary (finance managers and above only)
    Route::middleware('permission:view_executive_reports')->group(function () {
        Route::get('/reports/executive-summary', [ReportsController::class, 'executiveSummary'])->name('reports.executive-summary');
        Route::get('/reports/executive-summary/export', [ReportsController::class, 'executiveSummaryExport'])->name('reports.executive-summary.export');
    });

    // ─── USER MANAGEMENT ─────────────────────────────────────────────────────
    Route::middleware('permission:view_users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/activity-log', [UserController::class, 'activityLog'])->name('users.activity-log');
        Route::middleware('permission:create_users')->group(function () {
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
        });
        Route::middleware('permission:edit_users')->group(function () {
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });
        Route::middleware('permission:reset_user_password')->group(function () {
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        });
        Route::middleware('permission:toggle_user_status')->group(function () {
            Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        });
        Route::middleware('permission:unlock_users')->group(function () {
            Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
        });
    });

    // ─── ROLE PERMISSIONS MANAGEMENT ─────────────────────────────────────────
    Route::middleware('permission:manage_permissions')->group(function () {
        Route::get('/settings/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/settings/permissions/{role}', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('permissions.update');

        // Role management (CRUD for role definitions)
        Route::get('/settings/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::post('/settings/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        Route::put('/settings/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/settings/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // ─── AUDIT TRAIL ──────────────────────────────────────────────────────────
    Route::middleware('permission:view_audit_trail')->group(function () {
        Route::get('/settings/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    });

    // Notifications are already registered above (always accessible)
});

require __DIR__.'/auth.php';
