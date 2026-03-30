# Genset Rental System — Technical Documentation
**Laravel 11 · PHP 8.2 · Tailwind CSS · MySQL**  
_Document Date: March 26, 2026_

---

## Table of Contents

1. [Bookings Module](#1-bookings-module)
2. [Accounting Module](#2-accounting-module)
3. [Settings Module — Roles & Permissions](#3-settings-module--roles--permissions)

---

---

# 1. Bookings Module

## 1.1 Purpose

The Bookings module is the operational core of the system. It manages the full rental lifecycle of a genset — from a sales inquiry through deployment, return, invoicing, and final payment. It acts as the bridge between the sales pipeline (quote requests → quotations) and the finance pipeline (invoices → payments).

---

## 1.2 Database Schema

### `bookings` table (primary table)

| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `booking_number` | string unique | Auto-generated: `BK-{YEAR}-{0001}` |
| `quote_request_id` | FK nullable | Links to `quote_requests` (nullOnDelete) |
| `quotation_id` | FK nullable | Links to `quotations` (nullOnDelete) |
| `client_id` | FK nullable | Links to `clients` |
| `genset_id` | FK nullable | Links to `gensets` — assigned at activation |
| `invoice_id` | FK nullable | Links to `invoices` — set after invoice generated |
| `status` | enum | `created › approved › active › returned › invoiced › paid › cancelled › rejected` |
| `genset_type` | string | e.g. "100KVA Cummins" |
| `rental_start_date` | date | |
| `rental_end_date` | date | Computed: start + duration_days |
| `rental_duration_days` | integer | |
| `delivery_location` | string | |
| `pickup_location` | string nullable | |
| `total_amount` | decimal(15,2) | Rental price |
| `currency` | string | `TZS` or `USD` |
| `exchange_rate_to_tzs` | decimal(15,4) | Rate at time of booking (1.0 for TZS) |
| `notes` | text nullable | |
| `customer_name/email/phone/company_name` | string nullable | Used when no linked quote request |
| `created_by` | FK | User who created (tracks creator) |
| `approved_by` | FK nullable | User who approved |
| `approved_at` | timestamp nullable | |
| `activated_by` | FK nullable | User who activated (deployed genset) |
| `activated_at` | timestamp nullable | |
| `returned_by` | FK nullable | |
| `returned_at` | timestamp nullable | |
| `invoiced_by / invoiced_at / invoice_number` | — | Post-return invoicing |
| `paid_by / paid_at / payment_reference` | — | Final payment confirmation |
| `cancelled_by / cancelled_at / cancellation_reason` | — | Cancellation audit |

---

## 1.3 Lifecycle State Machine

The booking progresses through exactly **8 states**. Each transition is gated by a `canBe*()` guard method on the Booking model and a corresponding route-level permission middleware.

```
                        ┌──────────────┐
    [FORM SUBMITTED]    │   CREATED    │  ← Any user with create_bookings
                        └──────┬───────┘
                               │ approve()        (requires approve_bookings)
                               │ reject()         (requires approve_bookings)
                    ┌──────────┴──────────┐
                    ▼                     ▼
              ┌──────────┐         ┌──────────┐
              │ APPROVED │         │ REJECTED │
              └────┬─────┘         └──────────┘
                   │ activate()    (requires activate_bookings)
                   │ + genset_id assigned
                   ▼
              ┌──────────┐
              │  ACTIVE  │  ← Genset status set to 'rented'
              └────┬─────┘
                   │ markReturned()   (requires return_bookings)
                   │ Genset → 'maintenance'
                   ▼
              ┌──────────┐
              │ RETURNED │
              └────┬─────┘
                   │ generate invoice  (requires activate_bookings)
                   ▼
              ┌──────────┐
              │ INVOICED │
              └────┬─────┘
                   │ markPaid()   (requires return_bookings)
                   ▼
              ┌──────────┐
              │   PAID   │
              └──────────┘

  cancel() available from: created | approved | active  (requires cancel_bookings)
```

**Key design rule:** The state is only ever advanced by calling the action method on the model (`$booking->approve()`, `$booking->activate()`, etc.). The controller **never** writes status directly. This ensures audit columns (`approved_by`, `activated_by`, etc.) are always populated.

---

## 1.4 Model — `App\Models\Booking`

### Auto-number generation
```php
// In boot() → creating event:
$booking->booking_number = 'BK-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
// Example: BK-2026-0001
```
Queries the table for the last booking number of the current year and increments by 1.

### Relationships
| Method | Type | Target |
|---|---|---|
| `client()` | BelongsTo | `Client` |
| `genset()` | BelongsTo | `Genset` |
| `invoice()` | BelongsTo | `Invoice` |
| `quoteRequest()` | BelongsTo | `QuoteRequest` |
| `quotation()` | BelongsTo | `Quotation` (with items) |
| `createdBy()` | BelongsTo | `User` |
| `approvedBy()` | BelongsTo | `User` |
| `activatedBy()` | BelongsTo | `User` |
| `returnedBy()` | BelongsTo | `User` |
| `invoicedBy()` | BelongsTo | `User` |
| `paidBy()` | BelongsTo | `User` |
| `cancelledBy()` | BelongsTo | `User` |
| `deliveries()` | HasMany | `Delivery` |
| `maintenanceRecords()` | HasMany | `MaintenanceRecord` |

### Guard methods (all return `bool`)
```php
canBeApproved()      → status === 'created'
canBeRejected()      → status === 'created'
canBeActivated()     → status === 'approved'
canBeMarkedReturned()→ status === 'active'
canBeInvoiced()      → status === 'returned'
canBeMarkedPaid()    → status === 'invoiced'
canBeCancelled()     → status in ['created', 'approved', 'active']
```

### Side effects on state transitions
- **`activate()`** → Sets `gensets.status = 'rented'` for the assigned genset.
- **`markReturned()`** → Sets `gensets.status = 'maintenance'`. The genset must go through the Maintenance module and be cleared before it can be re-hired.
- **Computed accessors**: `getFormattedTotalAttribute()`, `getStatusColorAttribute()`, `getStatusLabelAttribute()` — used by Blade for display, never stored.

---

## 1.5 Controller — `App\Http\Controllers\Admin\BookingController`

| Method | HTTP | Route | Permission Guard | What it does |
|---|---|---|---|---|
| `index` | GET | `/admin/bookings` | `view_bookings` | Paginated list. Scopes to own bookings unless user has `view_all_bookings`. Accepts `?status=` and `?search=` filters. Computes 4-stat header counts. |
| `show` | GET | `/admin/bookings/{booking}` | `view_bookings` | Loads all relationships. Provides `$availableGensets` for activation dropdown. |
| `create` | GET | `/admin/bookings/create` | `create_bookings` | Pre-populates from `?quote_request_id=`. |
| `store` | POST | `/admin/bookings` | `create_bookings` | Validates, computes `rental_end_date`, creates booking at `status=created`. Fires notification + audit log. |
| `edit` / `update` | GET/PUT | `/admin/bookings/{booking}/edit` | `edit_bookings` | Standard edit. |
| `approve` | POST | `/admin/bookings/{booking}/approve` | `approve_bookings` | Calls `$booking->approve(auth()->id())`. Notifies creator. |
| `reject` | POST | `/admin/bookings/{booking}/reject` | `approve_bookings` | Calls `$booking->reject()` with optional reason. |
| `activate` | POST | `/admin/bookings/{booking}/activate` | `activate_bookings` | Validates `genset_id`, checks genset is still `available`, calls `$booking->activate()`. |
| `markReturned` | POST | `/admin/bookings/{booking}/return` | `return_bookings` | Calls `$booking->markReturned()`. |
| `markInvoiced` | POST | `/admin/bookings/{booking}/invoice` | `return_bookings` | Legacy manual step — superseded by `InvoiceController::generate()`. |
| `markPaid` | POST | `/admin/bookings/{booking}/mark-paid` | `return_bookings` | Records payment reference. |
| `cancel` | POST | `/admin/bookings/{booking}/cancel` | `cancel_bookings` | Records reason. |
| `contractPdf` | GET | `/admin/bookings/{booking}/contract` | `view_bookings` | Renders `contract-pdf.blade.php` via DomPDF as download. |
| `activeRentals` | GET | `/admin/bookings/active-rentals` | `view_bookings` | Filtered list of only `active` bookings for operations board. |

### Visibility scoping pattern (reused across multiple modules)
```php
$seeAll = PermissionService::can($user, 'view_all_bookings');
$query = Booking::latest();
if (!$seeAll) {
    $query->where('created_by', $user->id);
}
```
Staff-level users only see their own bookings. Managers with `view_all_bookings` see everything.

---

## 1.6 Invoice Generation from Booking

When `InvoiceController::generate(Booking $booking)` is called:
1. Checks booking status is `approved | active | returned`.
2. Checks no invoice exists yet.
3. Inherits pricing from linked Quotation (subtotal, VAT rate, VAT amount, total), or falls back to booking `total_amount`.
4. Creates `Invoice` record at `status=draft`.
5. Copies all `QuotationItem` records into `InvoiceItem` (or creates a single fallback line).
6. Links back: `bookings.invoice_id = invoice.id`.

---

## 1.7 Notifications & Audit Trail

Every booking action fires two side effects:

**1. In-app notification** via `AppNotification::notify()`:
- On creation: notifies all admins — "New Booking awaiting approval"
- On approval: notifies the creator — "Your booking has been approved"
- On rejection: notifies the creator with rejection reason

**2. Audit log** via `UserActivityLog::record()`:
```php
UserActivityLog::record(auth()->id(), 'approved', 'Approved booking BK-2026-0001', Booking::class, $booking->id);
```
Creates an immutable record of who did what, when. Viewable on the Audit Trail page.

---

---

# 2. Accounting Module

## 2.1 Architecture Overview

The accounting module is a **double-entry bookkeeping engine** embedded in the application. Every money event anywhere in the system automatically produces a balanced Journal Entry. It is NOT a standalone module — it is integrated deeply into Bookings, Invoices, Expenses, Inventory, and Supplier Payments.

```
Financial Event                 JournalEntryService            Chart of Accounts
─────────────────              ─────────────────────          ─────────────────
Invoice sent         ──────►  onInvoiceSent()       ──────►  DR 1140 AR
                                                              CR 4100/4110/4120 Revenue
                                                              CR 2120 VAT Payable

Payment received     ──────►  onPaymentRecorded()   ──────►  DR bank account
                                                              CR 1140 AR

Invoice voided       ──────►  onInvoiceVoided()     ──────►  DR Revenue (reverse)
                                                              CR 1140 AR (reverse)

PO received          ──────►  onPurchaseOrderReceived() ───►  DR 1150 Inventory
                                                              CR 2110 AP

Supplier paid        ──────►  onSupplierPayment()   ──────►  DR 2110 AP
                                                              CR Bank account
                                                              CR 2130 WHT Payable

Expense posted       ──────►  onExpensePosted()     ──────►  DR expense COA
                                                              DR 1180 VAT Input
                                                              CR Bank account

Fuel logged          ──────►  onFuelLogged()        ──────►  DR 5110 Fuel
                                                              CR Bank account
```

---

## 2.2 Chart of Accounts (COA)

### Database — `accounts` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | string(20) unique | e.g. `1140`, `4100` |
| `name` | string | e.g. `Accounts Receivable` |
| `type` | enum | `asset`, `liability`, `equity`, `revenue`, `expense` |
| `sub_type` | string nullable | e.g. `current_asset`, `fixed_asset` |
| `parent_id` | FK self-referencing | For hierarchical grouping |
| `normal_balance` | enum | `debit` (assets/expenses) or `credit` (liabilities/equity/revenue) |
| `balance` | decimal(15,2) | **Running balance** — updated on every JE post |
| `is_system` | boolean | System accounts (e.g. AR, AP) cannot be deleted |
| `is_active` | boolean | Inactive accounts are excluded from JE creation |

### Key System Account Codes Used in Journal Entries

| Code | Name | Type | Normal Balance |
|---|---|---|---|
| `1140` | Accounts Receivable | Asset | Debit |
| `1150` | Inventory | Asset | Debit |
| `1180` | VAT Input | Asset | Debit |
| `2110` | Accounts Payable | Liability | Credit |
| `2120` | VAT Payable | Liability | Credit |
| `2130` | WHT Payable | Liability | Credit |
| `4100` | Rental Income | Revenue | Credit |
| `4110` | Delivery Income | Revenue | Credit |
| `4120` | Other Income | Revenue | Credit |
| `5110` | Fuel Expense | Expense | Debit |
| _Bank accounts_ | _Assigned per bank account_ | Asset | Debit |
| _Expense categories_ | _Assigned per category_ | Expense | Debit |

**Important:** The JE engine resolves accounts by `code` at runtime. If a system account with a required code does not exist (i.e., COA has not been seeded), the engine returns `null` silently — meaning no JE is created. Existing functionality never breaks; JEs are simply skipped until the COA is set up.

---

## 2.3 Journal Entry Engine — `App\Services\JournalEntryService`

This is the single most important file in the accounting module. All JE creation flows through it.

### Core method: `createAndPost()`

```php
public function createAndPost(
    string $description,
    string $sourceType,     // 'invoice', 'payment', 'expense', etc.
    int    $sourceId,       // ID of the source model
    array  $lines,          // [['account_code'=>'1140','debit'=>500,'credit'=>0,'description'=>'...']]
    ?string $date = null,
    ?int   $userId = null,
    ?string $reference = null
): ?JournalEntry
```

**How it works (step by step):**
1. Resolves each `account_code` to an `Account` model. If any code is missing → returns `null`.
2. Validates that `SUM(debit) === SUM(credit)` — if not balanced → returns `null`.
3. Wraps everything in a `DB::transaction()`:
   - Creates the `JournalEntry` record at `status=draft`.
   - Creates all `JournalEntryLine` records.
   - Calls `$je->post()`.
4. `post()` iterates each line and calls `$account->applyDebit()` or `$account->applyCredit()` to update the running `balance` column.
5. Sets `journal_entries.status = 'posted'` and `posted_at = now()`.

### Auto-numbered Journal Entries
```
JE-{YEAR}-{0001}   e.g. JE-2026-0001
```

### `JournalEntry` model — Reversal
A posted JE can be reversed by calling `$je->reverse($reason)`. This creates a **new** mirror JE where all debit/credit columns are swapped, then posts it. The original JE is flagged `is_reversed = true` and linked to the reversal JE via `reversed_by_id`.

---

## 2.4 JE Trigger Map — Every event and the JE it produces

### A. Invoice Sent (`InvoiceController::markSent()`)
```
DR 1140 Accounts Receivable          total_amount × rate (TZS)
CR 4100 Rental Income                rental items subtotal × rate
CR 4110 Delivery Income              delivery items subtotal × rate  [if any]
CR 4120 Other Income                 other items subtotal × rate     [if any]
CR 2120 VAT Payable                  vat_amount × rate               [if vat > 0]
```
Revenue is split by `InvoiceItem.item_type`: `genset_rental|extra_days|damage|penalty|credit` → 4100, `delivery` → 4110, `fuel|maintenance|other` → 4120.

For USD invoices, all amounts are multiplied by `exchange_rate_to_tzs` before being posted to the TZS accounts.

### B. Invoice Voided (`InvoiceController::void()`)
Full reversal of the sent JE — all debits/credits swapped.

### C. Payment Recorded (`InvoiceController::recordPayment()`)
Only fires if a `bank_account_id` is provided on the payment:
```
DR [bank COA account]    amount × rate
CR 1140 AR               amount × rate
```
Also increments `bank_accounts.current_balance` by the TZS amount.

### D. PO Received (`PurchaseOrderController::receive()`)
```
DR [category COA / 1150]     per-item or total value
CR 2110 Accounts Payable     total value
```
If an inventory category has a linked COA account (configured in Inventory Categories settings), that account is used instead of the default `1150`.

### E. Supplier Payment (`SupplierPaymentController::store()`)
```
DR 2110 Accounts Payable     gross amount
CR [bank COA]                net = gross − withholding_tax
CR 2130 WHT Payable          withholding_tax   [if > 0]
```
Also decrements `bank_accounts.current_balance` by net.

### F. Expense Posted (`ExpenseController::post()`)
```
DR [expense category COA]    expense amount
DR 1180 VAT Input            vat_amount         [if VAT-registered expense]
CR [bank COA]                total_amount
```
Decrements bank balance. Sets `expenses.status = 'posted'` and links `expenses.journal_entry_id`.

### G. Fuel Logged (`FuelLogController` → `JournalEntryService::onFuelLogged()`)
Auto-creates an `Expense` record then calls `onExpensePosted()`:
```
DR 5110 Fuel Expense     total_cost
CR [bank COA]            total_cost
```

### H. Maintenance Record (`MaintenanceController`)
Follows same pattern as Expense — DR maintenance expense COA, CR bank.

### I. Genset Capitalized (Asset Registration)
When a genset is registered with a purchase value, a JE is auto-posted:
```
DR [genset asset COA]    purchase_value
CR [funding source COA]  purchase_value
```

---

## 2.5 Sub-Modules

### Chart of Accounts (`AccountController`)
- Full CRUD. System accounts (`is_system=true`) cannot have their code/type changed or be deleted.
- Hierarchical: accounts can have a `parent_id` pointing to another account (for grouping).
- The `show` page lists all `posted` JE lines for that account (paginated at 30).

### Bank Accounts (`BankAccountController`)
- Each bank account **must** be linked to a COA account (`account_id` FK).
- `current_balance` is a denormalized running total updated on every JE that touches this bank.
- Account Transfers (`AccountTransferController`) move money between bank accounts and create a two-line JE.

### Journal Entries (`JournalEntryController`)
- Manual JE creation for adjustments/corrections.
- Supports manual `post` (requires `post_journal_entries`) and `reverse` (requires `reverse_journal_entries`).
- Export to CSV.

### Expenses (`ExpenseController`)
- Flow: `draft → approved → posted`
- `approve` = finance sign-off. `post` = triggers JE via `JournalEntryService::onExpensePosted()`.
- Every expense is linked to an `ExpenseCategory` which in turn links to a COA account.
- Supports VAT tracking (`vat_amount`) and zero-rated flag.

### Supplier Payments (`SupplierPaymentController`)
- Linked to a `Supplier` and a `BankAccount`.
- Supports Withholding Tax (`withholding_tax` column).
- `confirm` action = finance director sign-off. Triggers `onSupplierPayment()` JE.
- Tax Invoice number (`tax_invoice_number`) and confirmation stored separately.
- Generates PDF Remittance Advice.

### Cash Requests / Petty Cash (`CashRequestController`)
- Self-service: any user with `view_cash_requests` can submit.
- Flow: `draft → submitted → approved → paid → retired`
- Multiple `CashRequestItem` records per request (with receipt upload).
- Approval by finance. Payment recorded against a bank/petty cash account. Retirement = staff confirms spending with receipts.
- Triggers expense JE on retirement.

### Credit Notes (`CreditNoteController`)
- Flow: `draft → issued → void`
- Issued credit notes reduce AR via a JE reversal.

### Tax Reports (`TaxReportController`)
- **VAT Report**: aggregates `vat_amount` from invoices and `vat_amount` from expenses for a date range, producing output/input VAT and net payable.
- **WHT Report**: aggregates `withholding_tax` from supplier payments.
- **Z-Report**: daily revenue summary.
- **Trial Balance**: sums all account balances grouped by type (Assets, Liabilities, Equity, Revenue, Expense) and verifies that Assets = Liabilities + Equity + (Revenue − Expense).

---

## 2.6 Financial Reports

| Report | Route Permission | What it computes |
|---|---|---|
| Profit & Loss | `view_financial_reports` | Revenue accounts − Expense accounts for period |
| Balance Sheet | `view_financial_reports` | Assets vs Liabilities + Equity snapshot |
| General Ledger | `view_financial_reports` | All JE lines for all accounts, exportable to CSV |
| AR Aging | `view_financial_reports` | Outstanding invoices grouped by age bands (0-30, 31-60, 61-90, 90+) |
| Revenue by Period | `view_financial_reports` | Invoice revenue summed by month |
| Payment Methods | `view_financial_reports` | Breakdown of payments by method (cash, M-Pesa, bank transfer, etc.) |
| Outstanding Invoices | `view_financial_reports` | All unpaid/partially paid invoices |
| Payables | `view_financial_reports` | Supplier AP summary |
| Gross Margin | `view_expense_reports` | Revenue − direct costs per booking |

---

---

# 3. Settings Module — Roles & Permissions

## 3.1 Architecture Overview

The access control system is a **role-based permission system (RBAC)** with the following layers:

```
User
 └── has one role (string key stored on users.role)
      └── Role (roles table — label, color, sort order)
           └── has many RolePermissions (role_permissions table)
                └── each maps to a Permission (permissions table — slug + label + module)

Enforcement:
  Route middleware  →  CheckPermission.php  →  PermissionService::can()
  Controller code   →  PermissionService::can()
  Blade templates   →  @if(PermissionService::can(auth()->user(), 'permission_name'))
```

---

## 3.2 Database Tables (4 tables)

### `users` table (extended)
```
users.role  →  string  → stores the role key, e.g. 'super_admin', 'admin', 'staff'
```

### `roles` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `key` | string unique | Slug, e.g. `finance_manager` — stored on `users.role` |
| `label` | string | Display name, e.g. `Finance Manager` |
| `description` | string nullable | |
| `badge_color` | string | Tailwind classes, e.g. `bg-blue-100 text-blue-800` |
| `is_system` | boolean | System roles (`super_admin`) cannot be deleted |
| `sort_order` | integer | Controls display order in UI |

### `permissions` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string unique | Slug, e.g. `view_bookings`, `approve_invoices` |
| `label` | string | Display, e.g. `View Bookings` |
| `module` | string | Grouping, e.g. `Bookings`, `Accounting`, `Settings` |
| `sort_order` | integer | Display order within module |

### `role_permissions` table (the pivot)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `role` | string | The role key (not FK — intentional, for flexibility) |
| `permission_name` | string | The permission slug (not FK — intentional) |
| Unique index | | `(role, permission_name)` |
| Index | | `(role)` — for fast lookups |

**Design note:** The join is string-based rather than FK-based. This avoids cascade complexity and allows the permission list and role list to evolve independently.

---

## 3.3 The Permission Service — `App\Services\PermissionService`

This is the single enforcement point for all permission checks.

```php
class PermissionService
{
    // Returns array of permission slugs for a role (1-hour cache per role)
    public static function getForRole(string $role): array
    {
        return Cache::remember("role_permissions.{$role}", 3600, function () use ($role) {
            return RolePermission::where('role', $role)->pluck('permission_name')->toArray();
        });
    }

    // The main check — used everywhere
    public static function can(User $user, string $permission): bool
    {
        if ($user->role === 'super_admin') return true;   // Super admin bypasses ALL checks
        return in_array($permission, static::getForRole($user->role), true);
    }

    // Called after any role permission update
    public static function clearCache(string $role): void
    {
        Cache::forget("role_permissions.{$role}");
    }
}
```

**Caching strategy:** The permission list for each role is cached for **1 hour** using the key `role_permissions.{role_key}`. On any permission update via `PermissionController`, the relevant cache key is immediately invalidated using `clearCache($role)`.

---

## 3.4 User Model Integration

```php
// users.role is a plain string column (role key)
// The User model delegates to PermissionService and Role model:

$user->hasPermission('approve_bookings');    // → PermissionService::can($user, 'approve_bookings')
$user->hasRole('super_admin', 'admin');      // → in_array($this->role, $roles)
$user->role_label;                           // → Role::labelFor($this->role) — dynamic attribute
$user->role_badge_color;                     // → Role::badgeColorFor($this->role) — for UI badges

User::roles();                               // → Role::asKeyLabel() → ['key' => 'Label', ...]
```

The `Role` model caches the full role list for 1 hour under the key `roles_all`. Any role create/update/delete calls `Role::clearCache()`.

---

## 3.5 Route-Level Enforcement — `CheckPermission` Middleware

**Registration:** The middleware alias `permission` is registered in `bootstrap/app.php`.

**Usage on routes:**
```php
Route::middleware('permission:view_bookings')->group(function () {
    Route::get('/bookings', [...]);                   // protected
    
    Route::middleware('permission:approve_bookings')->group(function () {
        Route::post('/bookings/{id}/approve', [...]);  // double-gated
    });
});
```

**OR logic within a single permission argument:**
```php
'permission:view_accounting|view_cash_requests'
// Passes if user has EITHER permission
```

**AND logic between separate arguments:**
```php
'permission:view_accounting,approve_something'
// User must have BOTH
```

**Middleware logic:**
```php
foreach ($permissions as $permGroup) {
    $anyOf = explode('|', $permGroup);  // OR logic within a group
    $passed = false;
    foreach ($anyOf as $perm) {
        if (PermissionService::can($user, $perm)) { $passed = true; break; }
    }
    if (!$passed) abort(403);    // AND logic across groups
}
```

If the user is `super_admin`, `PermissionService::can()` returns `true` unconditionally, so the middleware passes without any DB or cache lookups.

---

## 3.6 All Defined Permissions (by Module)

| Module | Permission Slug | Label |
|---|---|---|
| **Bookings** | `view_bookings` | View Bookings |
| | `view_all_bookings` | View All Bookings (not just own) |
| | `create_bookings` | Create Bookings |
| | `edit_bookings` | Edit Bookings |
| | `approve_bookings` | Approve / Reject Bookings |
| | `activate_bookings` | Activate Bookings (deploy genset) |
| | `return_bookings` | Mark Returned / Paid |
| | `cancel_bookings` | Cancel Bookings |
| **Quote Requests** | `view_quote_requests` | View Quote Requests |
| | `review_quote_requests` | Review / Reject Quote Requests |
| **Quotations** | `view_quotations` | View Quotations |
| | `create_quotations` | Create Quotations |
| | `edit_quotations` | Edit Quotations |
| | `approve_quotations` | Approve / Reject Quotations |
| **Invoices** | `view_invoices` | View Invoices |
| | `view_all_invoices` | View All Invoices |
| | `edit_invoices` | Edit Invoice Items |
| | `send_invoices` | Mark Invoice as Sent |
| | `record_invoice_payment` | Record Invoice Payment |
| | `void_invoices` | Void Invoices |
| | `write_off_invoices` | Write Off / Dispute Invoices |
| **Fleet** | `view_fleet` | View Gensets |
| | `create_gensets` | Add Gensets |
| | `edit_gensets` | Edit Gensets |
| | `delete_gensets` | Delete Gensets |
| | `update_genset_status` | Update Genset Status |
| | `view_deliveries` | View Deliveries |
| | `create_deliveries` | Create Deliveries |
| | `dispatch_deliveries` | Dispatch Deliveries |
| | `complete_deliveries` | Complete Deliveries |
| | `view_maintenance` | View Maintenance |
| | `create_maintenance` | Create Maintenance Records |
| | `edit_maintenance` | Edit Maintenance |
| | `delete_maintenance` | Delete Maintenance |
| | `start_maintenance` | Start Maintenance |
| | `complete_maintenance` | Complete Maintenance |
| | `cancel_maintenance` | Cancel Maintenance |
| | `view_fuel_logs` | View Fuel Logs |
| | `create_fuel_logs` | Log Fuel |
| **Inventory** | `view_inventory` | View Inventory |
| | `manage_inventory_categories` | Manage Categories |
| | `create_inventory_items` | Add Items |
| | `edit_inventory_items` | Edit Items |
| | `adjust_inventory_stock` | Adjust Stock |
| **Suppliers & POs** | `view_suppliers` | View Suppliers |
| | `create_suppliers` | Add Suppliers |
| | `edit_suppliers` | Edit Suppliers |
| | `view_purchase_orders` | View Purchase Orders |
| | `create_purchase_orders` | Create POs |
| | `send_purchase_orders` | Send POs |
| | `receive_purchase_orders` | Receive POs |
| | `cancel_purchase_orders` | Cancel POs |
| **Clients** | `view_clients` | View Clients |
| | `create_clients` | Create Clients |
| | `edit_clients` | Edit Clients |
| | `manage_client_contacts` | Manage Contacts & Addresses |
| **Accounting** | `view_accounting` | View Accounting (full access) |
| | `manage_accounts` | Manage Chart of Accounts |
| | `manage_bank_accounts` | Manage Bank Accounts |
| | `manage_expense_categories` | Manage Expense Categories |
| | `view_journal_entries` | View Journal Entries |
| | `create_journal_entries` | Create Manual Journal Entries |
| | `post_journal_entries` | Post Journal Entries |
| | `reverse_journal_entries` | Reverse Journal Entries |
| | `view_expenses` | View Expenses |
| | `create_expenses` | Create Expenses |
| | `approve_expenses` | Approve & Post Expenses |
| | `delete_expenses` | Delete Expenses |
| | `view_supplier_payments` | View Supplier Payments |
| | `create_supplier_payments` | Create Supplier Payments |
| | `confirm_supplier_payments` | Confirm Supplier Payments |
| | `view_cash_requests` | View / Submit Cash Requests |
| | `approve_cash_requests` | Approve Cash Requests |
| | `view_credit_notes` | View Credit Notes |
| | `create_credit_notes` | Create Credit Notes |
| | `issue_credit_notes` | Issue Credit Notes |
| | `void_credit_notes` | Void Credit Notes |
| **Reports** | `view_sales_reports` | Sales Reports |
| | `view_fleet_reports` | Fleet & Operations Reports |
| | `view_financial_reports` | Financial Reports |
| | `view_expense_reports` | Expense Reports |
| | `view_inventory_reports` | Inventory & Procurement Reports |
| | `view_executive_reports` | Executive Summary |
| **Users & Settings** | `view_users` | View Users |
| | `create_users` | Create Users |
| | `edit_users` | Edit Users |
| | `delete_users` | Delete Users |
| | `manage_roles` | Manage Roles |
| | `manage_permissions` | Manage Permissions |
| | `view_audit_trail` | View Audit Trail |

---

## 3.7 Permission Management Interface

### Roles Page (`RoleController`)

**`index`**: Loads all roles ordered by `sort_order`. Joins user counts per role and permission counts per role for the table display.

**`store`**: Creates a new role. Key is auto-generated from label via `Str::snake(Str::slug($label, '_'))` if not provided. Clears role cache.

**`update`**: Updates label, description, badge_color, sort_order. Does **not** allow key or `is_system` to be changed. Clears role cache.

**`destroy`**: Blocked if `is_system=true` or any users are assigned. Deletes all `RolePermission` rows for this role. Clears caches.

### Permissions Page (`PermissionController`)

**`index`**: Loads all permissions grouped by `module`. Builds a map of `role → [permission_names]` for every role (used for the checkbox grid in the UI).

**`update($role)`**:
1. Validates the role key exists.
2. Blocks modification of `super_admin` (hard-coded bypass — no permission record needed).
3. Intersects submitted checkboxes against the actual `permissions.name` list to prevent injection of fake permission names.
4. **Atomically replaces** all role permissions: `DELETE WHERE role = $role`, then batch-inserts new ones.
5. Calls `PermissionService::clearCache($role)`.

---

## 3.8 How Permissions Are Applied in Blade Templates

In Blade views, the check is done inline:
```blade
@if(PermissionService::can(auth()->user(), 'approve_bookings'))
    <button>Approve</button>
@endif
```

Or using the `User` model proxy:
```blade
@if(auth()->user()->hasPermission('approve_bookings'))
    <button>Approve</button>
@endif
```

Or for role checks (no permission lookup needed):
```blade
@if(auth()->user()->hasRole('super_admin', 'admin'))
    {{-- Admin-only UI --}}
@endif
```

---

## 3.9 Cache Invalidation Flow

```
Admin saves permissions for role "finance_manager"
         │
         ▼
PermissionController::update('finance_manager')
         │
         ▼
DELETE FROM role_permissions WHERE role = 'finance_manager'
INSERT INTO role_permissions (role, permission_name) VALUES (...)  [for each checked perm]
         │
         ▼
PermissionService::clearCache('finance_manager')
→ Cache::forget('role_permissions.finance_manager')
         │
         ▼
Next request by a finance_manager user:
→ PermissionService::getForRole('finance_manager')
→ Cache miss → fresh DB query → cached for 3600 seconds
```

---

## 3.10 Super Admin Special Case

`super_admin` is **hardcoded** at two levels:
1. **`PermissionService::can()`** — returns `true` immediately, no DB/cache lookup.
2. **`CheckPermission` middleware** — returns `$next($request)` immediately.
3. **`PermissionController::update()`** — `abort_if($role === 'super_admin', 403)` — nobody can modify super admin permissions through the UI.

This ensures the system always has at least one role that cannot be locked out.

---

## 3.11 Replicating This System in Another Project

To reproduce this exact RBAC pattern in a new Laravel project:

1. **Migrations**: Create `roles`, `permissions`, `role_permissions` tables. Add `role` (string) + `status` columns to `users`.

2. **Seed permissions**: Create all permission slugs in the `permissions` table (name, label, module, sort_order).

3. **Seed roles**: Create role records in `roles` table. Mark `super_admin` as `is_system=true`.

4. **Seed role_permissions**: For each role, insert the initial set of permissions.

5. **Models**: `Role`, `Permission`, `RolePermission` (all simple Eloquent models with string-based join).

6. **Service**: `PermissionService` with `getForRole()` (cached), `can()`, `clearCache()`.

7. **Middleware**: `CheckPermission` handles `|` (OR) within a group and multiple arguments for AND. Register alias `permission` in `bootstrap/app.php`.

8. **User model**: Add `hasPermission()` proxy and `hasRole()` helper. Wire `roles()` to `Role::asKeyLabel()`.

9. **Route protection**: Nest route groups under `->middleware('permission:slug')`.

10. **Controllers**: For "view all vs own only" scoping, use `PermissionService::can($user, 'view_all_*')` inside the controller index method.

11. **Blade**: Use `auth()->user()->hasPermission('slug')` for conditional UI rendering.

12. **Cache invalidation**: Always call `PermissionService::clearCache($role)` after any `role_permissions` change.

---

_End of Technical Documentation_
