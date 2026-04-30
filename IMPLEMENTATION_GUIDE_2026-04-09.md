# Implementation Guide — Historical Sales & Bank Statements
**Date:** April 9, 2026  
**System:** Genset Rental Management (Laravel 11 / Blade / Tailwind CSS / Alpine.js)  
**Scope:** All features added, enhancements, and bug fixes in this session.

---

## Table of Contents
1. [Feature: Record Historical Sale (Single Entry)](#1-feature-record-historical-sale-single-entry)
2. [Feature: Inline New Client Modal](#2-feature-inline-new-client-modal)
3. [Feature: Bulk Excel Import for Historical Sales](#3-feature-bulk-excel-import-for-historical-sales)
4. [Enhancement: Bookings Index — Historical Badge & Client Display](#4-enhancement-bookings-index--historical-badge--client-display)
5. [Bug Fixes](#5-bug-fixes)
6. [Database Changes](#6-database-changes)
7. [Routes Summary](#7-routes-summary)
8. [Dependency Notes](#8-dependency-notes)
9. *(April 10)* Bank Statement Reconciliation features (§9–§15)
10. *(April 15)* [Fix: Historical Sales JE + Bank Selector](#16-fix-historical-sales--journal-entry--bank-account-selector)
11. *(March 24)* [Permissions Overhaul — Granular Access Control + Row-Level Scoping](#34-march-24--permissions-overhaul)
12. *(March 24)* [Audit Trail + Supplier Profile Enhancements + Zero-Rated Expenses](#35-march-24--audit-trail--supplier-profile--zero-rated-expenses)
13. *(March 25)* [Booking Contract PDF, Re-Approval on Edit, JE Export, Expense COA Linkage](#36-march-25--booking-contract-pdf--je-export--expense-coa)
14. *(March 30)* [Company Settings Module — Logo, PDFs, Bank Details, Branding](#37-march-30--company-settings-module)
15. *(March 30–31)* [Dynamic Quotation Line Item Types + Payment Ledger Fixes + March 31 Bug Fixes](#38-march-3031--dynamic-line-item-types--bug-fixes)
16. *(April 1–3)* [PDF Header Spacing, Page Loader, Quotation Stats + Cancelled Page](#39-april-13--pdf-fixes--quotation-stats)
11. *(April 15)* [Fix: Backfill 12 Existing Historical Payments](#17-fix-backfill-12-existing-historical-payments)
12. *(April 15)* [Fix: AR/Revenue JE Auto-Posted on First Payment](#18-fix-ar--revenue-je-auto-posted-on-first-payment)
13. *(April 15)* [Fix: Bank Account Balance Sync on Statement Posting](#19-fix-bank-account-balance-sync-on-bank-statement-posting)
14. *(April 15)* [Accounting Rules Reference](#20-accounting-rules-reference)
15. *(April 13)* [Multi-Genset Booking, Contract PDF, Zero-Rated, Genset Type Dropdown](#26-april-13--multi-genset-booking-contract-pdf-zero-rated-genset-type)
16. *(April 15)* [Invoice Improvements — Pagination, Financial Summary Cards, Amount Paid](#27-april-15--invoice-improvements)
17. *(April 17)* [Bank Statement JE Reversal + COA Balance Fixes + Recalculate Command](#28-april-17--bank-statement-je-reversal--coa-balance-fixes)
18. *(April 20)* [Multi-Currency & FX — COA Currency, Account Transfer FX, Option A JE Lines](#29-april-20--multi-currency--fx)
19. *(April 22)* [Custom Pagination Bar + JE Source Filter + Independent Bank Transfer Reconciliation](#30-april-22--custom-pagination--je-filter--bank-transfer-reconciliation)
20. *(April 23)* [Account Transfer Reversal Fix + JE Reversed Tab + Backfill Migration](#31-april-23--account-transfer-reversal--je-reversed-tab)
19. *(April 8)* [Company Stamp Upload + Display on PDFs](#32-april-8--company-stamp-upload)
20. *(April 8)* [Separate Cancelled / Voided views for Invoices, Bookings, Quotations](#33-april-8--separate-cancelledvoided-views)
21. *(April 24)* [Expense Module — Edit, Reject, Export, Pagination, Stats](#21-expense-module--edit-reject-export-pagination-stats)
22. *(April 24)* [Expense Categories — is_active Fix, Unlinked Warning, Delete Guard](#22-expense-categories--is_active-fix-unlinked-warning-delete-guard)
23. *(April 24)* [Bank Statement — Expense Reconciliation](#23-bank-statement--expense-reconciliation)
24. *(April 25)* [Expense bank_reconciled_at — Bank-Verified / Paid Status](#24-expense-bank_reconciled_at--bank-verified--paid-status)

---

## 1. Feature: Record Historical Sale (Single Entry)

### Purpose
Allow users to add past rental sales that were completed before the system was in use. Records are created **already paid** and flagged as historical — they bypass the normal approval workflow but appear in all revenue reports.

### Database
**Migration:** `add_is_historical_to_bookings_table`
```php
$table->boolean('is_historical')->default(false)->after('status');
```

**Model:** `app/Models/Booking.php`
```php
// Add to $fillable:
'is_historical',

// Add to $casts:
'is_historical' => 'boolean',
```

### Controller Methods
**File:** `app/Http/Controllers/Admin/BookingController.php`

#### `recordHistoricalForm()`
```php
public function recordHistoricalForm()
{
    $clients = Client::orderBy('company_name')->orderBy('full_name')->get();
    $gensets = Genset::orderBy('asset_number')->get();
    return view('admin.bookings.record-historical', compact('clients', 'gensets'));
}
```

#### `storeHistorical(Request $request)`
Validates input then wraps the following in `DB::transaction()`:
1. Creates `Booking` with `status = 'paid'`, `is_historical = true`
2. Creates `Invoice` linked to the booking with `status = 'paid'`
3. Creates `InvoiceItem` linked to the invoice
4. Creates `InvoicePayment` linked to the invoice
5. Updates `booking->invoice_id`

**Key validation rules:**
```php
$request->validate([
    'client_id'           => 'required|exists:clients,id',
    'rental_start_date'   => 'required|date',
    'rental_end_date'     => 'required|date|after_or_equal:rental_start_date',
    'delivery_location'   => 'required|string',
    'description'         => 'required|string',
    'currency'            => 'required|in:TZS,USD',
    'exchange_rate_to_tzs'=> 'required_if:currency,USD|numeric|min:0.0001',
    'subtotal'            => 'required|numeric|min:0.01',
    'issue_date'          => 'required|date',
    'payment_date'        => 'required|date',
    'payment_method'      => 'required|in:cash,bank_transfer,mpesa,cheque,other',
]);
```

**VAT logic:**
```php
$subtotal  = (float) $request->subtotal;
$vatRate   = $request->boolean('is_zero_rated') ? 0 : 18.0;
$vatAmount = round($subtotal * $vatRate / 100, 2);
$total     = $subtotal + $vatAmount;
```

### View
**File:** `resources/views/admin/bookings/record-historical.blade.php`

The page uses an outer Alpine.js component for mode switching between Single Entry and Bulk Upload:
```html
<div x-data="{ mode: 'single' }">
    <!-- Tab buttons toggling mode -->
    <div x-show="mode === 'single'">
        <!-- single entry form -->
    </div>
    <div x-show="mode === 'bulk'" style="display:none">
        <!-- bulk upload panels -->
    </div>
</div>
```

**Important:** Use `style="display:none"` (not `x-cloak`) on panels that are hidden by default within an Alpine `x-show`. `x-cloak` can race with Alpine's initialisation and leave panels permanently hidden. See [Bug Fix #3](#bug-3-alpine-xcloak-race-condition) below.

The single-entry form contains:
- Client `<select>` with a "New Client" button that opens an inline modal
- Genset selector (optional)
- Rental start/end dates, delivery/pickup locations
- Currency radio (TZS / USD) with conditional exchange rate field using `x-show="currency === 'USD'" style="display:none"`
- Subtotal, zero-rated checkbox, live VAT/total calculation using Alpine `x-data`
- Invoice date, payment method/date/reference
- Submit → `POST /bookings/record-historical`

**Live VAT calculation (Alpine):**
```js
x-data="{
    currency: 'TZS',
    isZeroRated: false,
    subtotal: 0,
    vatRate: 18,
    get vatAmount() { return this.isZeroRated ? 0 : Math.round(this.subtotal * this.vatRate) / 100; },
    get total() { return parseFloat(this.subtotal || 0) + this.vatAmount; },
    fmt(n) { return new Intl.NumberFormat().format(Math.round(n)); }
}"
```

---

## 2. Feature: Inline New Client Modal

### Purpose
Allow users to create a new client without leaving the historical sale form. On save, the new client is auto-added to the client dropdown and selected.

### API Endpoint
**File:** `app/Http/Controllers/Admin/ClientController.php`

```php
public function quickStore(Request $request): \Illuminate\Http\JsonResponse
{
    $data = $request->validate([
        'full_name'    => 'required|string|max:255',
        'phone'        => 'required|string|max:50',
        'email'        => 'nullable|email|unique:clients,email',
        'company_name' => 'nullable|string|max:255',
        'tin_number'   => 'nullable|string|max:50',
    ]);

    $client = Client::create([
        ...$data,
        'status'             => 'active',
        'risk_level'         => 'low',
        'credit_limit'       => 0,
        'payment_terms_days' => 30,
        'source'             => 'manual',
        'created_by'         => auth()->id(),
    ]);

    return response()->json([
        'id'    => $client->id,
        'label' => ($client->company_name ?? $client->full_name) . ' (' . $client->client_number . ')',
    ]);
}
```

**Route:**
```php
Route::post('/clients/quick-store', [ClientController::class, 'quickStore'])
    ->middleware('permission:create_clients')
    ->name('admin.clients.quick-store');
```

### Frontend (Alpine.js `newClientModal()`)
```js
function newClientModal() {
    return {
        open: false,
        saving: false,
        errors: [],
        form: { full_name: '', company_name: '', phone: '', email: '', tin_number: '' },

        async submit() {
            this.errors = [];
            this.saving = true;
            try {
                const res = await fetch('/admin/clients/quick-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errors = data.errors
                        ? Object.values(data.errors).flat()
                        : [data.message ?? 'An error occurred.'];
                    return;
                }
                // Append to select and auto-select
                const select = document.getElementById('client_id_select');
                const option = new Option(data.label, data.id, true, true);
                select.add(option);
                this.form = { full_name: '', company_name: '', phone: '', email: '', tin_number: '' };
                this.open = false;
            } catch (e) {
                this.errors = ['Network error. Please try again.'];
            } finally {
                this.saving = false;
            }
        }
    };
}
```

The modal uses `x-show="open" x-cloak` (correct use of x-cloak — the modal starts hidden and is never the default-visible panel).

---

## 3. Feature: Bulk Excel Import for Historical Sales

### Overview
Three-step flow:
1. **Download template** — pre-filled xlsx with headers, 2 sample rows, Instructions sheet
2. **Upload & preview** — parse the file, show row-by-row validation results, store in session
3. **Confirm** — save all valid rows; new clients are created; DB transaction per row

### 3a. Template Download

**Controller method:** `historicalTemplate()`

Uses `phpoffice/phpspreadsheet`. Returns a `streamDownload` response — **do not use raw `header()` + `ob_end_clean()` + `exit()`** inside Laravel middleware (causes `ERR_INVALID_RESPONSE`).

```php
return response()->streamDownload(function () use ($writer) {
    $writer->save('php://output');
}, 'historical_sales_template.xlsx', [
    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'Cache-Control' => 'max-age=0',
]);
```

**Template columns (17 total):**

| # | Column | Required? |
|---|--------|-----------|
| A | client_identifier | Yes — client number OR company/person name |
| B | client_phone | Only for new clients |
| C | client_email | Only for new clients |
| D | genset_type | No |
| E | rental_start_date | Yes (YYYY-MM-DD) |
| F | rental_end_date | Yes (YYYY-MM-DD) |
| G | delivery_location | Yes |
| H | currency | Yes — TZS or USD |
| I | exchange_rate | Only for USD |
| J | subtotal | Yes — numeric, excl. VAT |
| K | zero_rated | Yes — YES or NO |
| L | description | Yes |
| M | invoice_date | Yes (YYYY-MM-DD) |
| N | payment_date | Yes (YYYY-MM-DD) |
| O | payment_method | Yes — cash/bank_transfer/mpesa/cheque/other |
| P | payment_reference | No |
| Q | notes | No |

Force all cell values as TYPE_STRING when setting sample rows to prevent Excel auto-formatting number/date columns.

### 3b. Preview / Parse

**Controller method:** `bulkHistoricalPreview(Request $request)`

**Critical: Excel date serial number handling**

When a user types a date in Excel, Excel may store it as a float (e.g. `45307.0` instead of `"2024-01-15"`). `Carbon::parse("45307")` does not throw — it silently returns a wrong date, causing all downstream saves to use wrong dates. The correct approach:

```php
// After toArray(), use a date-aware getter for date columns:
$getDate = function (array $row, string $key, int $sheetRowNumber) use ($colMap, $sheet): string {
    $idx = $colMap[$key] ?? null;
    if ($idx === null) return '';
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
    $cell = $sheet->getCell($colLetter . $sheetRowNumber);
    $value = $cell->getValue();
    if ($value === null || $value === '') return '';
    if ((is_float($value) || is_int($value))
        && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)
            ->format('Y-m-d');
    }
    return trim((string) $value);
};

// Sheet row = loop index + 1 (header is row 1, first data row is row 2)
$sheetRow = $i + 1;
$rentalStartDate = $getDate($row, 'rental_start_date', $sheetRow);
```

> **Note:** `getCellByColumnAndRow()` was removed in PhpSpreadsheet v2+. Use `getCell($letterAndRow)` instead.

**Client matching logic:**
```php
$matchedClient = $allClients->first(fn($c) => strtolower($c->client_number) === strtolower($clientId))
    ?? $allClients->first(fn($c) => strtolower($c->company_name ?? '') === strtolower($clientId))
    ?? $allClients->first(fn($c) => strtolower($c->full_name ?? '') === strtolower($clientId));
```
Unmatched → `client_status = 'new'`; requires `client_email` to be present (to satisfy the `clients.email NOT NULL` constraint).

**Session storage:**
```php
session(['bulk_historical' => $parsed]);
```
Each `$parsed` row contains all normalised values including pre-converted date strings.

**Preview view:** `resources/views/admin/bookings/bulk-historical-preview.blade.php`
- Summary strip: Total / Will Save / Will Skip / New Clients
- Table showing each row with OK (green) or Skip + error list (red)
- Sticky bottom bar with confirm button posting to `bulk-historical-confirm`

### 3c. Confirm / Save

**Controller method:** `bulkHistoricalConfirm(Request $request)`

```php
foreach ($rows as $row) {
    if (!empty($row['errors'])) { $failed++; continue; }

    try {
        DB::transaction(function () use ($row) {
            // 1. Resolve or create client
            // 2. Booking::create([..., 'status' => 'paid', 'is_historical' => true])
            // 3. Invoice::create([..., 'status' => 'paid'])
            // 4. InvoiceItem::create([...])
            // 5. InvoicePayment::create([...])
            // 6. $booking->update(['invoice_id' => $invoice->id])
        });
        $saved++;
    } catch (\Throwable $e) {
        \Log::error('Bulk historical import row failed: ' . $e->getMessage(), ['exception' => $e]);
        $failed++;
    }
}
session()->forget('bulk_historical');
```

**Flash message logic:**
```php
$flashKey = ($saved > 0) ? 'success' : 'error';
return redirect()->route('admin.bookings.index')->with($flashKey, $message);
```
Always use `error` when `$saved === 0` so the user is clearly notified of a full failure rather than seeing a misleading green banner.

---

## 4. Enhancement: Bookings Index — Historical Badge & Client Display

### Eager-load `client` relationship
Historical bookings have no `quoteRequest` — they link directly to a `Client`. Without eager-loading, the client column shows blank.

```php
// BookingController::index()
$query = Booking::with(['quoteRequest', 'client', 'createdBy', 'approvedBy'])
    ->whereNotIn('status', $cancelledStatuses)
    ->latest();
```

### Fix client name display
`Client` model uses `full_name`, not `name`. The view must use:
```html
@elseif($booking->client)
    <p>{{ $booking->client->company_name ?? $booking->client->full_name }}</p>
```

### Historical badge
```html
@if($booking->is_historical)
    <span class="inline-flex items-center px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-medium rounded">
        Historical
    </span>
@endif
```

---

## 5. Bug Fixes

### Bug 1: `ERR_INVALID_RESPONSE` on file download
**Cause:** Using raw `header()` / `ob_end_clean()` / `exit` inside a Laravel controller bypasses the response pipeline (middleware, session writes, etc.), resulting in a broken HTTP response.

**Fix:** Use `response()->streamDownload(callback, filename, headers)` instead:
```php
// WRONG:
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
ob_end_clean();
$writer->save('php://output');
exit;

// CORRECT:
return response()->streamDownload(function () use ($writer) {
    $writer->save('php://output');
}, 'file.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
```

### Bug 2: `getCellByColumnAndRow()` — method removed in PhpSpreadsheet v2
**Error:** `Call to undefined method PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::getCellByColumnAndRow()`

**Fix:**
```php
// WRONG (v1 API):
$cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex);

// CORRECT (v2+ API):
$colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
$cell = $sheet->getCell($colLetter . $rowIndex);
```

### Bug 3: Alpine `x-cloak` race condition on `x-show` panels
**Cause:** `x-cloak` with CSS `[x-cloak] { display: none }` is removed by Alpine during init. If Alpine removes the attribute before evaluating `x-show`, a panel that should be hidden becomes visible permanently — or vice versa: a panel whose `x-show` evaluates to `false` may flicker or stay hidden even after the condition becomes true.

**Fix:**
- **Default-visible panel** (`x-show="mode === 'single'"` where `mode` defaults to `'single'`): omit `x-cloak` entirely — no flash risk.
- **Default-hidden panel** (`x-show="mode === 'bulk'"` where `mode` defaults to `'single'`): use `style="display:none"` instead of `x-cloak`. Alpine's `x-show` directive cleanly overrides inline styles.

```html
<!-- CORRECT pattern -->
<div x-show="mode === 'single'">            <!-- default visible: no cloak needed -->
<div x-show="mode === 'bulk'" style="display:none">  <!-- default hidden: inline style -->
```

### Bug 4: Silent bulk-confirm failure with misleading success flash
**Cause:** `catch (\Throwable $e) { $failed++; }` swallowed all errors silently; the success flash was always shown regardless of `$saved` count.

**Fix:** Log errors + use conditional flash key (covered in section 3c above).

### Bug 5: `clients.email` NOT NULL with no default
**Cause:** The `clients` table has `email NOT NULL` with no default value. Creating a new client from bulk import without providing an email threw a DB error caught silently.

**Fix:** Added `client_email` column to the Excel template. Validation now requires `client_email` for new clients. The confirm method passes it to `Client::create()`.

---

## 6. Database Changes

| Migration file | Change |
|---|---|
| `2026_04_09_114158_add_is_historical_to_bookings_table.php` | Adds `is_historical` boolean (default `false`) to `bookings` |
| `2026_04_09_085935_create_bank_statements_table.php` | Creates `bank_statements` table |
| `2026_04_09_085935_create_bank_transactions_table.php` | Creates `bank_transactions` table |
| `2026_04_09_072447_add_partner_to_journal_entry_lines.php` | Adds `partner` column to `journal_entry_lines` |

Run: `php artisan migrate`

---

## 7. Routes Summary

All routes are inside `auth` + `admin` middleware groups.

```php
// Historical sales (requires permission: view_bookings)
Route::get('/bookings/record-historical',       [BookingController::class, 'recordHistoricalForm'])  ->name('admin.bookings.record-historical');
Route::post('/bookings/record-historical',      [BookingController::class, 'storeHistorical'])       ->name('admin.bookings.store-historical');
Route::get('/bookings/historical-template',     [BookingController::class, 'historicalTemplate'])    ->name('admin.bookings.historical-template');
Route::post('/bookings/bulk-historical-preview',[BookingController::class, 'bulkHistoricalPreview']) ->name('admin.bookings.bulk-historical-preview');
Route::post('/bookings/bulk-historical-confirm',[BookingController::class, 'bulkHistoricalConfirm'])->name('admin.bookings.bulk-historical-confirm');

// Quick client creation (requires permission: create_clients)
Route::post('/clients/quick-store', [ClientController::class, 'quickStore'])->name('admin.clients.quick-store');
```

> **Important:** Place named static routes (`record-historical`, `historical-template`, etc.) **before** any `{booking}` wildcard route to prevent Laravel's route model binding from matching literals as IDs.

---

## 8. Dependency Notes

- **phpoffice/phpspreadsheet ^5.5** — used for both template generation and xlsx parsing
  - Use `TYPE_STRING` when writing cells that must stay as text (dates, phone numbers)
  - Use `Date::isDateTime($cell)` + `Date::excelToDateTimeObject()` when reading date cells
  - `getCellByColumnAndRow()` was removed in v2 — use `getCell()` with coordinate string
- **Alpine.js** — `x-show` + `x-cloak` pitfalls documented in Bug Fix #3 above
- **`DB::transaction()`** — wrap each row independently so one failure does not block others
- **`\Log::error()`** — always log inside bulk-import catch blocks; silent failures make debugging extremely difficult

---

---

# Session Update — April 10, 2026
**Scope:** Bank Statement Reconciliation System + supporting hardening fixes.

## Table of Contents (April 10 additions)
9. [Feature: Bank Statement Reconciliation](#9-feature-bank-statement-reconciliation)
10. [Feature: Reconcile Modal — Human-friendly Search](#10-feature-reconcile-modal--human-friendly-search)
11. [Feature: Un-reconcile](#11-feature-un-reconcile)
12. [Upload UX Fixes (Bank Statement Create)](#12-upload-ux-fixes-bank-statement-create)
13. [Hardening Fixes](#13-hardening-fixes)
14. [Database Changes (April 10)](#14-database-changes-april-10)
15. [Routes Summary (April 10)](#15-routes-summary-april-10)

---

## 9. Feature: Bank Statement Reconciliation

### Problem Solved
When an invoice payment or supplier payment is recorded in the system, it already creates a Journal Entry. If the same transaction is then "posted" from the bank statement screen, a second JE is created for the same money movement — a double-posting error that inflates the GL.

### Solution
Two distinct workflows:
- **Post** — for transactions with no matching payment in the system. Creates a new JE.
- **Reconcile** — for transactions that match an existing invoice/supplier payment. Links the bank transaction to the existing payment and its JE. **No new JE is created.**

### Migration
**File:** `database/migrations/2026_04_10_101907_add_reconciliation_to_bank_transactions_table.php`

```php
$table->string('reconciled_payment_type')->nullable()->after('journal_entry_id');
$table->unsignedBigInteger('reconciled_payment_id')->nullable()->after('reconciled_payment_type');
$table->timestamp('reconciled_at')->nullable()->after('reconciled_payment_id');
$table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciled_at');
$table->index(['reconciled_payment_type', 'reconciled_payment_id'], 'bt_reconciled_payment_idx');
// Status enum expanded to: pending, posted, ignored, reconciled
```

Run: `php artisan migrate`

### Model: `BankTransaction`
**File:** `app/Models/BankTransaction.php`

New fillable fields: `reconciled_payment_type`, `reconciled_payment_id`, `reconciled_at`, `reconciled_by`

New cast: `'reconciled_at' => 'datetime'`

New relationships and methods:
```php
public function reconciledBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'reconciled_by');
}

public function reconciledPayment(): ?Model
{
    if (!$this->reconciled_payment_type || !$this->reconciled_payment_id) return null;

    // Whitelist to prevent arbitrary class execution from DB value
    $allowed = [
        \App\Models\InvoicePayment::class  => \App\Models\InvoicePayment::class,
        \App\Models\SupplierPayment::class => \App\Models\SupplierPayment::class,
    ];
    $class = $allowed[$this->reconciled_payment_type] ?? null;
    if (!$class) return null;

    return $class::find($this->reconciled_payment_id);
}
```

`getStatusBadgeAttribute()` extended with `'reconciled' => 'bg-purple-100 text-purple-700'`.

> **Security note:** The `reconciled_payment_type` column stores the full PHP class name (e.g. `App\Models\InvoicePayment`). Never call `$this->reconciled_payment_type::find()` directly — always validate against a whitelist first, as shown above.

### Controller: `suggestMatches()`
**File:** `app/Http/Controllers/Admin/BankStatementController.php`

```php
public function suggestMatches(Request $request, BankStatement $bankStatement, BankTransaction $transaction)
```

**Two modes, controlled by `?q=` query string:**

| Mode | Trigger | Behaviour |
|---|---|---|
| Auto-match | No `q` param | Amount ±1% (min ±1), date ±7 days, direction-aware (credit→InvoicePayments, debit→SupplierPayments) |
| Free-text search | `?q=<term>` | LIKE search on reference, receipt_number, invoice_number, company_name, full_name, supplier name; searches **both** InvoicePayments and SupplierPayments regardless of direction |

Already-reconciled payments are excluded from all results to prevent double-linking.

Returns JSON: `{ matches: [...] }` where each match has `type`, `id`, `date`, `amount`, `reference`, `method`, `description`, `invoice_number`.

### Controller: `reconcileTransaction()`

Guards:
- `$transaction->bank_statement_id !== $bankStatement->id` → 404
- `status === 'posted'` → 422 (already posted, cannot reconcile)
- `status === 'reconciled'` → 422 (already reconciled)
- Payment's `bank_account_id !== $bankStatement->bank_account_id` → 422 (wrong account)
- Payment already linked to another transaction → 409 (conflict)

On success:
```php
$transaction->update([
    'status'                  => 'reconciled',
    'reconciled_payment_type' => $modelClass,
    'reconciled_payment_id'   => $payment->id,
    'reconciled_at'           => now(),
    'reconciled_by'           => auth()->id(),
    'journal_entry_id'        => $payment->journal_entry_id ?? $transaction->journal_entry_id,
]);
```

No new JE is created. The existing payment's JE is linked for traceability.

### Controller: `postTransaction()` guard
Added `abort_if($transaction->status === 'reconciled', 422, '...')` — prevents posting a reconciled transaction.

### Controller: `postAll()` guard
`postAll()` already only processes `status = 'pending'`, so reconciled rows are naturally excluded.

### Show View Changes
**File:** `resources/views/admin/accounting/bank-statements/show.blade.php`

- **Summary strip**: 6th tile added — "Reconciled" (purple), counts `$transactions->where('status','reconciled')`.
- **Transaction rows — Pending**: Three action buttons — Post (green), **Reconcile** (purple), Ignore.
- **Transaction rows — Reconciled**: Inline purple badge "Reconciled {date}" in the description column; actions column shows "Reconciled" + Un-reconcile button (see §11); JE column links to the linked payment's JE.
- **Reconcile modal**: Purple "Reconcile vs Post" explanation banner; AJAX-loaded match cards; search box for manual lookup; hidden form for submission.

---

## 10. Feature: Reconcile Modal — Human-friendly Search

### Problem
The initial implementation had a raw "Payment Type" dropdown + "Payment ID" number input in the reconcile modal. Users don't know internal database IDs, making this unusable.

### Solution
Replaced the manual ID fields with a single free-text search box (`#reconcileSearchInput`) that searches the `suggestMatches` endpoint with `?q=`.

### JavaScript (in `show.blade.php`)

**On modal open:** Auto-fetches matches by amount/date (no `q`), clears the search input.

**Debounced input handler:**
- Debounce: 400ms; minimum 2 chars to trigger search
- Calls `suggestMatches?q=<term>` on the controller
- Shows spinner (`#reconcileSearchSpinner`) during fetch
- Re-renders `#reconcileMatchList` with grouped results (Invoice Payments / Supplier Payments sections)
- Empty `q` re-runs the auto-match (not an empty result)

**Match card rendering (`renderReconcileMatches`):**
- Groups results into "Invoice Payments" and "Supplier Payments" sections via `matchCard()`
- Each card shows: client/supplier name, invoice number, date, method, reference, amount
- Clicking a card calls `selectReconcilePayment(type, id, btnEl)` which highlights the card and sets `selectedPaymentType`/`selectedPaymentId`

**`submitReconcile()`:**
```js
function submitReconcile() {
    if (!selectedPaymentType || !selectedPaymentId) {
        alert('Please select a payment from the list...');
        return;
    }
    document.getElementById('reconcileForm').action = `.../reconcile`;
    document.getElementById('reconcilePaymentType').value = selectedPaymentType;
    document.getElementById('reconcilePaymentId').value   = selectedPaymentId;
    document.getElementById('reconcileForm').submit();
}
```

The hidden `<form id="reconcileForm">` with `reconcilePaymentType` and `reconcilePaymentId` inputs remains; `submitReconcile()` populates them from the selected card only.

---

## 11. Feature: Un-reconcile

### Purpose
Allow a user to undo a wrong reconciliation — resets the transaction back to `pending` without touching the original payment or its JE.

### Controller: `unreconcileTransaction()`
```php
public function unreconcileTransaction(BankStatement $bankStatement, BankTransaction $transaction)
{
    abort_if($transaction->bank_statement_id !== $bankStatement->id, 404);
    abort_if($transaction->status !== 'reconciled', 422, 'Transaction is not reconciled.');

    $transaction->update([
        'status'                  => 'pending',
        'reconciled_payment_type' => null,
        'reconciled_payment_id'   => null,
        'reconciled_at'           => null,
        'reconciled_by'           => null,
        'journal_entry_id'        => null,
    ]);

    return back()->with('success', 'Reconciliation removed — transaction reset to pending.');
}
```

### Route
```php
Route::post('.../transactions/{transaction}/unreconcile', [..., 'unreconcileTransaction'])
    ->name('accounting.bank-statements.transactions.unreconcile');
```

### View
Reconciled rows in the actions column now show:
```html
<span class="text-xs text-purple-600 italic">Reconciled</span>
<form method="POST" action="{{ route(...unreconcile...) }}"
      onsubmit="return confirm('Remove reconciliation and reset to pending?')">
    @csrf
    <button type="submit" class="... border-red-200 text-red-500 ...">Un-reconcile</button>
</form>
```

---

## 12. Upload UX Fixes (Bank Statement Create)

**File:** `resources/views/admin/accounting/bank-statements/create.blade.php`

- **Inline error banner** (`#uploadBankError`): shown before the parse button when bank account is not selected, instead of silent JS block.
- **Scroll to field**: `document.getElementById('bank_account_id').scrollIntoView()` on validation fail.
- **Loading spinner**: Parse & Preview button shows spinner + "Parsing…" text while the file upload is in progress, preventing double-submit confusion.
- **Auto-clear error**: Selecting a bank account clears the error banner immediately.
- **Download Template button**: Green button in the form header linking to `admin.accounting.bank-statements.template`.

---

## 13. Hardening Fixes

### Fix 1: `reconciledPayment()` class whitelist (Security)
**File:** `app/Models/BankTransaction.php`

**Before (unsafe):**
```php
return $this->reconciled_payment_type::find($this->reconciled_payment_id);
```
Calling an arbitrary class name from a DB column as a static method is a code execution risk if the column value is tampered with.

**After (safe):**
```php
$allowed = [
    \App\Models\InvoicePayment::class  => \App\Models\InvoicePayment::class,
    \App\Models\SupplierPayment::class => \App\Models\SupplierPayment::class,
];
$class = $allowed[$this->reconciled_payment_type] ?? null;
if (!$class) return null;
return $class::find($this->reconciled_payment_id);
```

### Fix 2: Duplicate statement period detection
**Files:** `BankStatementController::store()` and `confirmImport()`

If `period_from`/`period_to` overlap an existing statement for the same bank account, a `warning` toast is shown after redirect (using the global `session('warning')` handler in `admin-layout.blade.php`). The statement is still created — not blocked — since multi-part uploads are legitimate.

```php
$overlap = BankStatement::where('bank_account_id', $bankAccountId)
    ->whereNotNull('period_from')->whereNotNull('period_to')
    ->where('period_from', '<=', $periodTo)
    ->where('period_to', '>=', $periodFrom)
    ->first();
if ($overlap) {
    $overlapWarning = "Another statement ({$overlap->reference}) already covers an overlapping period...";
}
```

### Fix 3: `postAll()` skip count reporting
**File:** `BankStatementController::postAll()`

Before: silently skipped pending rows missing a contra account with no user feedback.

After: counts skipped rows upfront and includes them in the success/error message:
```
"5 transaction(s) posted successfully. 3 pending transaction(s) were skipped — set their contra account to post them."
```

### Fix 4: `ignoreTransaction()` blocks reconciled transactions
**File:** `BankStatementController::ignoreTransaction()`

Added:
```php
abort_if($transaction->status === 'reconciled', 422,
    'Cannot ignore a reconciled transaction. Un-reconcile it first.');
```

Without this guard, clicking "Ignore" on a reconciled row would change status to `ignored` while leaving `reconciled_payment_type`, `reconciled_payment_id`, and the linked JE columns populated — an inconsistent state that would orphan reconciliation data.

---

## 14. Database Changes (April 10)

| Migration file | Change |
|---|---|
| `2026_04_10_101907_add_reconciliation_to_bank_transactions_table.php` | Adds `reconciled_payment_type` (string, nullable), `reconciled_payment_id` (unsignedBigInt, nullable), `reconciled_at` (timestamp, nullable), `reconciled_by` (unsignedBigInt, nullable); index `bt_reconciled_payment_idx`; expands `status` enum to `['pending','posted','ignored','reconciled']` |

Run: `php artisan migrate`

---

## 15. Routes Summary (April 10)

All routes are inside the `manage_bank_accounts` permission middleware group.

```php
// Reconciliation
Route::get(
    '/accounting/bank-statements/{bankStatement}/transactions/{transaction}/suggest-matches',
    [BankStatementController::class, 'suggestMatches']
)->name('accounting.bank-statements.transactions.suggest-matches');

Route::post(
    '/accounting/bank-statements/{bankStatement}/transactions/{transaction}/reconcile',
    [BankStatementController::class, 'reconcileTransaction']
)->name('accounting.bank-statements.transactions.reconcile');

Route::post(
    '/accounting/bank-statements/{bankStatement}/transactions/{transaction}/unreconcile',
    [BankStatementController::class, 'unreconcileTransaction']
)->name('accounting.bank-statements.transactions.unreconcile');
```

---

*End of April 10 additions.*

---

# April 15, 2026 — Accounting Integrity Fixes

**Scope:** Journal entry gaps for historical sales, AR/revenue posting on invoice payment, and bank account balance sync on bank statement posting.

---

## 16. Fix: Historical Sales — Journal Entry + Bank Account Selector

### Problem
`storeHistorical()` created `InvoicePayment` records with no `bank_account_id` and never called any `JournalEntryService` method. This meant TZS 106M+ in collected historical sales was completely invisible to the Chart of Accounts.

Three compounding failures:
1. `InvoicePayment` saved with `bank_account_id = NULL`
2. `onPaymentRecorded()` returns `null` immediately when `bankAccount` is null — no JE posted
3. No revenue JE was ever posted for historical bookings at all

### Solution

#### New method: `JournalEntryService::onHistoricalSale()`
**File:** `app/Services/JournalEntryService.php`

Posts a single combined cash-sale JE (no AR leg — payment is immediate):
```
DR  1110  Bank account        total_amount in TZS
CR  4100  Rental Income       rental items subtotal
CR  4110  Delivery Income     delivery items (if any)
CR  4120  Other Income        other items (if any)
CR  2120  VAT Payable         vat_amount (if not zero-rated)
```
Also increments `BankAccount::current_balance` after posting.

#### `BookingController::recordHistoricalForm()` — passes `$bankAccounts`
```php
$bankAccounts = BankAccount::orderBy('bank_name')->get(['id','bank_name','account_name','currency']);
return view('admin.bookings.record-historical', compact('clients', 'gensets', 'bankAccounts'));
```

#### `BookingController::storeHistorical()` — validation + JE call
Added `bank_account_id` to validation:
```php
'bank_account_id' => 'required|exists:bank_accounts,id',
```
Payment creation now includes `bank_account_id`. After creating the invoice and payment, the JE is posted and linked:
```php
$bankAccount = BankAccount::find($validated['bank_account_id']);
$je = app(JournalEntryService::class)->onHistoricalSale($invoice, $bankAccount);
if ($je) {
    $payment->update(['journal_entry_id' => $je->id]);
}
```

#### View: `record-historical.blade.php` — bank account selector
Added a required **"Received Into"** `<select name="bank_account_id">` field at the top of the Payment Record card, populated from `$bankAccounts`.

---

## 17. Fix: Backfill 12 Existing Historical Payments

### Problem
12 existing `InvoicePayment` records (INV-2026-0003 through INV-2026-0014, total TZS ~43M) had `bank_account_id = NULL` and `journal_entry_id = NULL`.

### Solution: Artisan command
**File:** `app/Console/Commands/BackfillHistoricalJournalEntries.php`

```
php artisan je:backfill-historical              # apply (defaults to bank ID 1)
php artisan je:backfill-historical --dry-run    # preview without writing
php artisan je:backfill-historical --bank-id=2  # use a different bank account
```

The command:
1. Finds all `InvoicePayment` records with `journal_entry_id = NULL` and `is_reversed = false`
2. Assigns `bank_account_id` if missing
3. Calls `onHistoricalSale($invoice, $bankAccount)` for each
4. Links returned JE back to the payment

**Backfill result (run April 15):** 12/12 payments → JE #13–24 posted.
```
1110 CRDB Bank:       DR 101,686,500
4100 Rental Income:   CR  86,175,000
2120 VAT Payable:     CR  15,511,500
```

---

## 18. Fix: AR / Revenue JE Auto-Posted on First Payment

### Problem
`onInvoiceSent()` (which posts `DR 1140 AR / CR 4100 Revenue / CR 2120 VAT`) only fires when a user manually clicks "Mark Sent". In practice, users go straight from generating an invoice to recording payment — so `sent_at` was never set, and AR + Revenue were never booked.

Result: 0 invoices had `sent_at` set → AR balance = TZS 0 → Revenue = TZS 0 for all normal invoices.

### Solution
**File:** `app/Http/Controllers/Admin/InvoiceController.php` — `recordPayment()`

Added an auto-guard before the payment JE:
```php
// If the invoice was never formally sent (no sent_at), auto-post
// the AR / Revenue / VAT journal entry now before recording the payment.
if (!$invoice->sent_at) {
    $invoice->markSent();
    app(JournalEntryService::class)->onInvoiceSent($invoice);
}
```

This ensures DR 1140 AR is always established before it is credited by `onPaymentRecorded()`. Users no longer need to click "Mark Sent" — accounting is always correct regardless.

### Backfill: INV-2026-0002
INV-2026-0002 (partially paid, TZS 7,080,000) had a payment JE (DR Bank 5M / CR AR 5M) but no invoice sent JE. Posted manually via tinker → JE #25:
```
DR 1140 Accounts Receivable   7,080,000
CR 4100 Rental Income         6,000,000
CR 2120 VAT Payable           1,080,000
```

**Verification after all fixes:**
```
1140 AR net balance:              TZS 2,080,000
Outstanding invoice balance:      TZS 2,080,000  ✓ exact match
```

---

## 19. Fix: Bank Account Balance Sync on Bank Statement Posting

### Problem
`BankStatementController::postTransaction()` and `postAll()` created JE entries correctly but never updated `BankAccount::current_balance`. The balance tiles on the Bank Accounts page stayed permanently stale after posting statements.

Note: `reconcileTransaction()` correctly leaves `current_balance` unchanged — the balance was already updated when the original payment was first recorded.

### Solution
**File:** `app/Http/Controllers/Admin/BankStatementController.php`

#### `postTransaction()` — added after `$transaction->update()` inside the DB transaction:
```php
// credit = money IN → increment; debit = money OUT → decrement
if ($transaction->type === 'credit') {
    BankAccount::where('id', $bankAccount->id)->increment('current_balance', $transaction->amount);
} else {
    BankAccount::where('id', $bankAccount->id)->decrement('current_balance', $transaction->amount);
}
```

#### `postAll()` — accumulates net change, applies in one query at end of loop:
```php
$netBalanceChange += $transaction->type === 'credit'
    ? (float) $transaction->amount
    : -(float) $transaction->amount;

// After the foreach:
if ($netBalanceChange > 0) {
    BankAccount::where('id', $bankAccount->id)->increment('current_balance', $netBalanceChange);
} elseif ($netBalanceChange < 0) {
    BankAccount::where('id', $bankAccount->id)->decrement('current_balance', abs($netBalanceChange));
}
```

---

## 20. Accounting Rules Reference

| Flow | JE posted by | DR | CR |
|---|---|---|---|
| Normal invoice generated | `onInvoiceSent()` (auto on first payment) | 1140 AR | 4100/4110/4120 Revenue + 2120 VAT |
| Client pays invoice | `onPaymentRecorded()` | 1110 Bank | 1140 AR |
| Historical sale recorded | `onHistoricalSale()` | 1110 Bank | 4100 Revenue + 2120 VAT |
| Account transfer | `onAccountTransfer()` | Destination COA | Source COA |
| Expense posted | `onExpensePosted()` | Expense COA | Bank COA |
| Bank statement tx posted | `BankStatementController` | Bank / Contra | Contra / Bank |

**Bank `current_balance` is updated by:** invoice payments, historical sales, account transfers, expense postings, and bank statement postings. Manual JEs do **not** update `current_balance`.

---

*End of April 15 additions.*

---

---

# April 24–25, 2026 — Expense Module Overhaul + Bank Reconciliation

---

## 21. Expense Module — Edit, Reject, Export, Pagination, Stats

### Overview
Comprehensive overhaul of the expense module introducing missing CRUD operations, CSV export, pagination upgrade, and stat tile improvements.

### New Routes
**File:** `routes/web.php` — inside `permission:view_expenses` group:

```php
Route::get('/accounting/expenses/export', [ExpenseController::class, 'export'])
    ->name('accounting.expenses.export');

Route::middleware('permission:create_expenses')->group(function () {
    Route::get('/accounting/expenses/{expense}/edit',  [ExpenseController::class, 'edit'])
        ->name('accounting.expenses.edit');
    Route::put('/accounting/expenses/{expense}',       [ExpenseController::class, 'update'])
        ->name('accounting.expenses.update');
});

Route::middleware('permission:approve_expenses')->group(function () {
    Route::post('/accounting/expenses/{expense}/reject', [ExpenseController::class, 'reject'])
        ->name('accounting.expenses.reject');
});
```

### Controller Changes
**File:** `app/Http/Controllers/Admin/ExpenseController.php`

#### `index()` — fixed stats, added `$perPage`
```php
$perPage = in_array((int) $request->get('per_page', 25), [10,25,50,100])
    ? (int) $request->get('per_page', 25) : 25;

$stats = [
    'total_this_month' => (clone $base)->whereMonth('expense_date', now()->month)
                                       ->whereYear('expense_date', now()->year)
                                       ->where('status', 'posted')
                                       ->sum('total_amount'),        // posted only
    'pending_approval' => (clone $base)->where('status', 'draft')->count(),
    'approved'         => (clone $base)->where('status', 'approved')->count(),  // NEW
    'posted'           => (clone $base)->where('status', 'posted')->count(),
];
```

#### `edit()` — new method
```php
public function edit(Expense $expense)
{
    $user = auth()->user();
    if (!PermissionService::can($user, 'view_all_expenses') && $expense->created_by !== $user->id)
        abort(403);
    if ($expense->status !== 'draft')
        return redirect()->route('admin.accounting.expenses.show', $expense)
            ->with('error', 'Only draft expenses can be edited.');

    $categories   = ExpenseCategory::with('account')->where('is_active', true)->orderBy('name')->get();
    $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
    return view('admin.accounting.expenses.edit', compact('expense', 'categories', 'bankAccounts'));
}
```

#### `update()` — new method
Identical validation to `store()`. Handles optional attachment replacement (leaves existing if no new file uploaded). Updates all fields then redirects to show.

#### `reject()` — new method
Reverts an `approved` expense back to `draft`, clears `approved_by` / `approved_at`.

```php
public function reject(Expense $expense)
{
    if ($expense->status !== 'approved')
        return back()->with('error', 'Only approved expenses can be rejected.');

    $expense->update([
        'status'      => 'draft',
        'approved_by' => null,
        'approved_at' => null,
    ]);
    // activity log...
    return back()->with('success', 'Expense returned to Draft.');
}
```

#### `export()` — new method
Streams a UTF-8 BOM CSV of all filtered expenses (respects all index filter params: `status`, `category_id`, `from`, `to`, `search`). Uses `$expense->source_label` accessor for human-readable source column.

### View Changes

#### `resources/views/admin/accounting/expenses/edit.blade.php` — NEW FILE
Copy of `create.blade.php` adapted for editing:
- Form action: `route('admin.accounting.expenses.update', $expense)` + `@method('PUT')`
- All fields pre-populated from `$expense->field` (with `old()` fallback)
- Alpine.js `is_zero_rated` toggle pre-initialised from `$expense->is_zero_rated`
- Shows current attachment link; file upload is optional (leave blank = keep existing)
- Heading: "Edit Expense", back link to show page

#### `resources/views/admin/accounting/expenses/index.blade.php`
1. **Stats grid** expanded from 3 to 4 tiles: `Posted This Month | Pending Approval | Approved | Posted to Ledger`
2. **Export CSV button** added to header (passes current query params to export route)
3. **Edit link** in table row for `draft` expenses (permission-gated: `create_expenses`)
4. **Pagination** replaced `$expenses->links()` with `<x-pagination-bar :paginator="$expenses" :per-page="$perPage" />`

#### `resources/views/admin/accounting/expenses/show.blade.php`
1. **Source label** fix: `ucfirst($expense->source_type ?? 'manual')` → `$expense->source_label`
2. **Edit button** added for `draft` expenses (`create_expenses` permission)
3. **Reject → Draft button** added for `approved` expenses (`approve_expenses` permission, with confirm dialog)

---

## 22. Expense Categories — is_active Fix, Unlinked Warning, Delete Guard

### Bug Fix: `is_active` never saved as `false`
**File:** `app/Http/Controllers/Admin/ExpenseCategoryController.php`

**`store()`** — was hardcoding `'is_active' => true` regardless of checkbox:
```php
// BEFORE (bug):
ExpenseCategory::create($data + ['is_active' => true]);

// AFTER:
$data['is_active'] = $request->boolean('is_active', true);
ExpenseCategory::create($data);
```

**`update()`** — unchecked checkbox sent nothing; `$data` never contained `is_active = false`:
```php
// Add before $expenseCategory->update($data):
$data['is_active'] = $request->boolean('is_active');
```

### New: `$unlinkedCount` passed to index view
```php
public function index()
{
    $categories    = ExpenseCategory::with('account')->withCount('expenses')->orderBy('name')->get();
    $unlinkedCount = $categories->whereNull('account_id')->count();
    return view('admin.accounting.expense-categories.index', compact('categories', 'unlinkedCount'));
}
```

### View Changes

#### `index.blade.php`
1. **Amber warning banner** when `$unlinkedCount > 0` — explains that posting will fail and links to each category's edit page
2. **"Not mapped"** cell now rendered as an amber badge (`bg-amber-50 text-amber-700 border border-amber-200`) instead of plain italic
3. **Delete button** disabled (greyed out with tooltip) when `$cat->expenses_count > 0` — previously allowed deleting categories that had expenses (the controller already blocked it server-side, now blocked in UI too)
4. **Edit link** colour changed from `text-gray-500` to `text-blue-600` for visibility

#### `create.blade.php`
- Added `is_active` checkbox (checked by default)
- Improved account hint text: "Leave blank if for cash requests only"

#### `edit.blade.php`
- Contextual hint under account select: amber warning if no account linked, grey note if linked

---

## 23. Bank Statement — Expense Reconciliation

### Overview
Bank statement debit transactions (money out) can now be reconciled against existing **Expenses** the same way they can be reconciled against supplier payments or account transfers.

### Model: `BankTransaction` — allowed reconciliation types
**File:** `app/Models/BankTransaction.php` — `reconciledPayment()`:
```php
$allowed = [
    \App\Models\InvoicePayment::class  => \App\Models\InvoicePayment::class,
    \App\Models\SupplierPayment::class => \App\Models\SupplierPayment::class,
    \App\Models\Expense::class         => \App\Models\Expense::class,   // NEW
];
```

### Model: `Expense` — reverse relationship + import
**File:** `app/Models/Expense.php`:
```php
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The bank statement transaction this expense was reconciled to (if any).
 */
public function bankTransaction(): HasOne
{
    return $this->hasOne(BankTransaction::class, 'reconciled_payment_id')
                ->where('reconciled_payment_type', static::class);
}
```

### Controller: `BankStatementController`

#### `suggestMatches()` — new expense candidates
```php
$usedExpenseIds = BankTransaction::where('reconciled_payment_type', Expense::class)
    ->whereNotNull('reconciled_payment_id')->pluck('reconciled_payment_id');

// For debit lines (money out) or free-text search
if (!$isCredit || $isSearch) {
    $query = Expense::with(['category'])
        ->where('bank_account_id', $bankAccountId)
        ->whereIn('status', ['approved', 'posted'])
        ->whereNotIn('id', $usedExpenseIds);

    // Auto-match: same bank, ±7 days, ±1% of total_amount
    // Free-text search: expense_number, description, reference, category name

    // Returns array entries with type = 'expense'
}
```

#### `reconcileTransaction()` — expense branch
```php
if ($request->payment_type === 'expense') {
    $expense = Expense::findOrFail($request->payment_id);
    abort_if($expense->bank_account_id !== $bankStatement->bank_account_id, 422, '...');
    // Guard: not already reconciled
    $transaction->update([
        'status'                  => 'reconciled',
        'reconciled_payment_type' => Expense::class,
        'reconciled_payment_id'   => $expense->id,
        'reconciled_at'           => now(),
        'reconciled_by'           => auth()->id(),
        'journal_entry_id'        => $expense->journal_entry_id ?? $transaction->journal_entry_id,
    ]);
    // No new JE — links to expense's existing posted JE
}
```

Validation updated to allow `'expense'` as a `payment_type`:
```php
'payment_type' => 'required|in:invoice_payment,supplier_payment,account_transfer,expense',
```

### View: Bank Statement Show — JS `renderReconcileMatches()`
Added an `exp` group to the reconcile modal match renderer:
```js
const exp = matches.filter(m => m.type === 'expense');
// ...
if (exp.length) {
    html += `<p class="...">Expenses</p>`;
    html += exp.map(m => matchCard(m)).join('');
}
```

### View: Expense Show
- Eager loads `bankTransaction.bankStatement` + `bankReconciledBy`
- Shows a **purple reconciliation card** in the sidebar with: statement name (linked), tx date, bank reference, reconciled timestamp

---

## 24. Expense `bank_reconciled_at` — Bank-Verified / Paid Status

### Problem
After reconciling an expense against a bank statement line, nothing on the expense itself indicated the payment was bank-confirmed. Un-reconciling also did nothing to the expense.

### Solution
Added two new columns to `expenses` to track bank-verification status. No new `status` enum value was needed — the existing `draft → approved → posted` flow is preserved.

### Database
**Migration:** `2026_04_24_134953_add_bank_reconciliation_to_expenses_table.php`
```php
Schema::table('expenses', function (Blueprint $table) {
    $table->timestamp('bank_reconciled_at')->nullable()->after('approved_at')
          ->comment('Set when a bank statement transaction is reconciled against this expense');
    $table->foreignId('bank_reconciled_by')->nullable()->after('bank_reconciled_at')
          ->constrained('users')->nullOnDelete();
});
```

### Model: `Expense`
**File:** `app/Models/Expense.php`

Added to `$fillable`: `'bank_reconciled_at'`, `'bank_reconciled_by'`

Added to `$casts`: `'bank_reconciled_at' => 'datetime'`

Added relationship:
```php
public function bankReconciledBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'bank_reconciled_by');
}
```

### Controller: `BankStatementController`

#### On reconcile (expense branch)
```php
// Mark the expense as bank-confirmed (paid)
$expense->update([
    'bank_reconciled_at' => now(),
    'bank_reconciled_by' => auth()->id(),
]);
```

#### On un-reconcile — clear the flag
```php
if ($transaction->reconciled_payment_type === \App\Models\Expense::class
    && $transaction->reconciled_payment_id) {
    \App\Models\Expense::where('id', $transaction->reconciled_payment_id)->update([
        'bank_reconciled_at' => null,
        'bank_reconciled_by' => null,
    ]);
}
```

### View: Expense Show Header
```blade
@if($expense->bank_reconciled_at)
<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold
             bg-emerald-50 text-emerald-700 border border-emerald-200">
    ✓ Bank Verified
</span>
@endif
```

### View: Expense Show Sidebar
| State | Card shown |
|---|---|
| `bank_reconciled_at` is set | **Emerald green** "Bank Verified / Reconciled" card — statement link, tx date, bank ref, confirmed timestamp, confirmed by |
| Status = `posted` but not reconciled | **Amber** "Not yet reconciled" nudge card — hints to open the bank statement and reconcile the matching debit line |
| Draft or approved | Nothing shown |

### Note on JE Reversal
Un-reconciling a bank transaction **does not reverse the Journal Entry**. Reconciliation is a linking action only — it never creates a new JE. The JE (created when the expense was *posted*) remains intact in the ledger. If a JE reversal is needed, that must be done separately through the Journal Entries module (reversal support already exists in the system).

---

## 25. Updated Accounting Rules Reference (April 25)

| Flow | Triggered by | DR | CR | `bank_reconciled_at` set? |
|---|---|---|---|---|
| Normal invoice generated | `onInvoiceSent()` (auto on first payment) | 1140 AR | 4100 Revenue + 2120 VAT | — |
| Client pays invoice | `onPaymentRecorded()` | 1110 Bank | 1140 AR | — |
| Historical sale | `onHistoricalSale()` | 1110 Bank | 4100 Revenue + 2120 VAT | — |
| Account transfer | `onAccountTransfer()` | Destination COA | Source COA | — |
| Expense posted | `onExpensePosted()` | Expense COA | Bank COA | No — posted only |
| Bank statement tx posted | `BankStatementController` | Bank / Contra | Contra / Bank | — |
| Expense reconciled to bank statement | `reconcileTransaction()` | *(no JE)* | *(no JE)* | **Yes** |
| Expense un-reconciled | `unreconcileTransaction()` | *(no JE)* | *(no JE)* | **Cleared** |

---

*End of April 24–25 additions.*

---

---

# April 13–23, 2026 — Booking Enhancements, Invoice Improvements, Multi-Currency & FX, Pagination, JE Tabs

*(These sections were written retrospectively on April 25 to fill the documentation gap.)*

---

## 26. April 13 — Multi-Genset Booking, Contract PDF, Zero-Rated, Genset Type

### 26.1 Multi-Genset Booking + Drop-ON/OFF Locations + Destination Field
**Commit:** `3dd3575`

#### Database
**Migration:** `2026_04_13_000001_update_bookings_for_multi_genset_and_locations.php`
- Renamed `delivery_location` → `drop_on_location`
- Renamed `pickup_location` → `drop_off_location`
- Added `destination` (varchar) — country/region/city of deployment
- Added new pivot table: `booking_genset (id, booking_id, genset_id, created_at, updated_at)`

#### Models
**`app/Models/Booking.php`**
```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Added to $fillable:
'drop_on_location', 'drop_off_location', 'destination',

// New relationship:
public function gensets(): BelongsToMany
{
    return $this->belongsToMany(Genset::class, 'booking_genset')->withTimestamps();
}
```

**`app/Models/Genset.php`**
```php
// New reverse relationship:
public function bookingsViaMany(): BelongsToMany
{
    return $this->belongsToMany(Booking::class, 'booking_genset')->withTimestamps();
}
```

#### Controller Changes
- **`QuotationController::approve()`** — reads `genset_ids[]` from request, calls `$booking->gensets()->sync($request->genset_ids)` after creating booking
- **`BookingController::update()`** — syncs `genset_ids[]` via `$booking->gensets()->sync()`
- **`BookingController::storeHistorical()`** — syncs genset_ids after creating historical booking
- **`GensetController::show()`** — merges FK bookings + pivot bookings into a single collection for complete rental history
- Replaced all old field names (`delivery_location`, `pickup_location`) throughout controllers

#### Views Updated
`bookings/index.blade.php`, `bookings/show.blade.php`, `bookings/edit.blade.php`, `bookings/record-historical.blade.php`, `genset/show.blade.php`

---

### 26.2 Contract PDF — All Assigned Gensets in Clauses 1.1 & 1.2
**Commit:** `dea6717`

**`BookingController::downloadContractPdf()`** — eager-loads `booking->gensets` (pivot) + `booking->genset` (FK fallback):
```php
$booking->load(['client', 'gensets', 'genset']);
$allGensets = $booking->gensets->isNotEmpty() ? $booking->gensets : collect([$booking->genset])->filter();
```

**`contract-pdf.blade.php`**
- Clause 1.1: iterates `$allGensets` — asset_number, genset_type, capacity for each unit
- Clause 1.2: lists all asset_numbers (comma-separated inline or as a list)
- Graceful fallback when neither pivot nor FK genset found: skips the genset block with a comment

---

### 26.3 Zero-Rated Option in Booking Edit + Multi-Genset on Invoice
**Commit:** `9773b26`

#### Database
**Migration:** `2026_04_13_000002_add_is_zero_rated_to_bookings_table.php`
```php
$table->boolean('is_zero_rated')->default(false)->after('destination');
```

#### Models
**`Booking.php`** — `is_zero_rated` added to `$fillable` and `$casts` (`'boolean'`)

#### Controller Changes
- **`QuotationController::approve()`** — copies `is_zero_rated` from the source quotation to the new booking
- **`BookingController::update()`** — reads and saves `is_zero_rated` via `$request->boolean('is_zero_rated')`
- **`InvoiceController::generate()`** — uses `$booking->is_zero_rated` as the zero-rate override when generating invoice (previously only used per-invoice override)
- **`InvoiceController::show()` + `downloadPdf()`** — eager-loads `booking.gensets`

#### View Changes
- **`bookings/edit.blade.php`**: replaces the flat `total_amount` field with subtotal input + zero-rated checkbox + Alpine.js live VAT breakdown (0% or 18%)
- **`invoices/pdf.blade.php`**: lists all pivot gensets (with FK fallback) in the equipment section
- **`invoices/show.blade.php`**: shows all pivot gensets under the "Booking" section
- **`bookings/show.blade.php`**: the "Deploy" confirmation modal shows the pre-assigned gensets for verification before deploying

---

### 26.4 Genset Type Dropdown in Booking Edit
**Commit:** `77023a0`

**`BookingController::edit()`** — now loads genset types from the fleet:
```php
$gensetTypes = Genset::distinct()->pluck('genset_type')->filter()->sort()->values();
return view('admin.bookings.edit', compact('booking', 'gensets', 'gensetTypes', ...));
```

**`bookings/edit.blade.php`** — `genset_type` text input replaced with a `<select>` driven by `$gensetTypes` (with freeform "Other" fallback option).

---

## 27. April 15 — Invoice Improvements

### 27.1 Invoice List Pagination
**Commit:** `c231995`

**`InvoiceController::index()`** — default `per_page` changed from `25` to `10` for better readability of the invoice list in production.

---

### 27.2 Financial Summary Cards on Invoice Index
**Commit:** `dbbb746`

**`InvoiceController::index()`** — three aggregate stats added and passed to view:
```php
$stats = [
    'total_invoiced'   => $query->clone()->whereNotIn('status', ['voided'])->sum('total_amount'),
    'total_collected'  => InvoicePayment::whereHas('invoice', fn($q) => $q->whereNotIn('status', ['voided']))->sum('amount'),
    'total_outstanding'=> $query->clone()->where('status', 'unpaid')->sum('balance_due'),
];
```

**`invoices/index.blade.php`** — three summary cards added above the table:
- **Total Invoiced** (all non-voided) — blue card
- **Total Collected** (sum of all payments) — green card
- **Outstanding** (unpaid balance_due) — amber card

---

### 27.3 Amount Paid Shown on Invoice Index and Show Page
**Commit:** `2f52840`

**`invoices/index.blade.php`** — new `Amount Paid` column added to the table (shows `$invoice->payments->sum('amount')` formatted).

**`invoices/show.blade.php`** — payments summary row added at the bottom of the invoice items table: `Subtotal | VAT | Total | Paid: X | Balance Due: Y`.

---

## 28. April 17 — Bank Statement JE Reversal + COA Balance Fixes

### 28.1 Reverse Bank Statement JE Resets Transaction to Pending
**Commit:** `d88c0d8`

**Problem:** Reversing a JE that was linked to a bank statement transaction left the transaction stuck in `reconciled` or `posted` state, and the bank account balance was not corrected.

**Fix — `JournalEntryController::reverse()`:**
When the JE being reversed has `source_type = 'bank_statement'` and a `source_id`:
1. The linked `BankTransaction` is located
2. Its `status` is reset to `'pending'`
3. Its `journal_entry_id` is nulled
4. The bank account `current_balance` is adjusted in the opposite direction (debit/credit swapped)
5. The reversal JE is created as normal (with `is_reversed = true` on the original)

---

### 28.2 COA Balance Sync on Bank Statement Posting
**Commit:** `e6916c5`

**Problem:** Posting a bank statement transaction created a JE but did not update the `Account.current_balance` column in the COA.

**Fix — `BankStatementController::postTransaction()`:**
After creating the JE via `JournalEntryService`, loops through the JE lines and updates each account's `current_balance`:
```php
foreach ($journalEntry->lines as $line) {
    $account = Account::find($line->account_id);
    $account->increment('current_balance', $line->debit - $line->credit);
}
```

---

### 28.3 Artisan Command: `accounts:recalculate-balances`
**Commit:** `c740ae7`

**File:** `app/Console/Commands/RecalculateAccountBalances.php`

Rebuilds every COA account's `current_balance` from scratch by summing all **posted** JE lines:
```
php artisan accounts:recalculate-balances
```

```php
foreach (Account::all() as $account) {
    $balance = JournalEntryLine::whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
        ->where('account_id', $account->id)
        ->selectRaw('SUM(debit) - SUM(credit) as net')
        ->value('net') ?? 0;
    $account->update(['current_balance' => $balance]);
}
```

Output: `Updated X accounts.` — safe to run multiple times (idempotent).

---

### 28.4 Fix: Inline Z-Index on Shared Dropdown (appears above Post modal)
**Commit:** `6580792`

**Problem:** The shared account-selector dropdown on the bank statement show page was being clipped behind the "Post Transaction" modal overlay due to stacking context.

**Fix — `bank-statements/show.blade.php`:**
Added `style="z-index: 9999; position: relative;"` inline on the dropdown container element, bypassing the Tailwind `z-*` utility class limitation when inside a modal stack.

---

## 29. April 20 — Multi-Currency & FX

### 29.1 COA — Currency Field + Foreign-Currency Balance Display
**Commit:** `9521bfe`

#### Database
**Migration:** `2026_04_20_000004_add_currency_to_accounts_table.php`
```php
$table->string('currency', 10)->default('TZS')->after('current_balance');
```

#### Model: `Account`
- `currency` added to `$fillable`
- New computed attribute `formatted_balance` — returns balance formatted with currency symbol
- New helper `isForex()` — returns `true` if `currency !== 'TZS'`

#### Controller: `AccountController`
- `store()` / `update()` — save `currency` from request (defaults to `'TZS'`)

#### Views
- **`accounts/create.blade.php`** + **`accounts/edit.blade.php`** — currency selector (TZS / USD / EUR / GBP / other)
- **`accounts/index.blade.php`** — forex accounts show balance in their native currency + TZS equivalent in smaller text

---

### 29.2 COA — Abnormal Balance Warnings + Foreign Ledger Column + Recalculate Button
**Commit:** `5c2071f`

**Migration:** Seeder migration to add missing office supplies account (1610) and link it to the expense category if unlinked.

**`accounts/index.blade.php`**:
- **Abnormal balance warning** — asset/expense accounts with a credit balance (or liability/equity/revenue accounts with a debit balance) show an amber ⚠ warning badge
- **Foreign currency column** — forex accounts show native-currency balance in the COA table

**`accounts/show.blade.php`**:
- **Ledger section** — shows all posted JE lines for this account in chronological order with running balance
- **Recalculate button** — triggers `accounts:recalculate-balances` via a POST route (admin only)

---

### 29.3 COA — Searchable Account Dropdown + Multi-Currency Dashboard Totals
**Commit:** `637905c`

**`resources/js/app.js`** — adds a lightweight Alpine.js searchable-dropdown component used throughout the COA account selectors (JE entry, expense, bank account forms).

**`dashboard.blade.php`** — bank account balance summary now groups accounts by currency, showing each currency's total separately (e.g., TZS total + USD total) instead of summing them all into a single meaningless figure.

**`bank-accounts/create.blade.php`** + **`bank-accounts/edit.blade.php`** — COA account selector now uses the searchable dropdown component.

---

### 29.4 FX Exchange Rate on Account Transfers + Live Rate API
**Commit:** `21f839e`

#### Problem
Cross-currency (FX) account transfers (e.g., USD → TZS) had no way to record the exchange rate used, meaning the destination amount was always assumed equal to the source.

#### Database
**Migration:** `2026_04_20_000001_add_fx_fields_to_account_transfers_table.php`
```php
$table->decimal('to_amount', 15, 2)->nullable();    // Amount credited to destination account
$table->decimal('exchange_rate', 15, 6)->nullable(); // Rate: 1 source unit = X destination units
$table->string('from_currency', 10)->nullable();
$table->string('to_currency', 10)->nullable();
```

#### Model: `AccountTransfer`
- `to_amount`, `exchange_rate`, `from_currency`, `to_currency` added to `$fillable` and `$casts`
- `isFx()` helper: returns `true` if `from_currency !== to_currency`

#### Controller: `AccountTransferController`
- `create()` — passes `$fromAccount` and `$toAccount` to view with their currencies
- `store()` — validates `to_amount` (required on FX, defaults to `amount` on same-currency), calculates `exchange_rate = to_amount / amount` (or inverse), saves `from_currency` + `to_currency` from account records
- `BankStatementController` — updated to pass account currency info when linking bank statement transactions to transfers

#### `JournalEntryService::onAccountTransfer()`
FX transfers now pass `to_amount` as the debit on the destination account line (instead of the `amount` field):
```php
// DR destination account (in destination currency amount)
$lines[] = ['account_id' => $toAccountId, 'debit' => $toAmount, 'credit' => 0, ...];
// CR source account (in source amount)
$lines[] = ['account_id' => $fromAccountId, 'debit' => 0, 'credit' => $amount, ...];
```

#### `bank-accounts/index.blade.php`
- New "Transfer" modal includes `to_amount` field (shown only when source and destination currencies differ)
- Small "Get live rate" button calls the public `api.exchangerate-host.com` endpoint and fills in the current rate for convenience

---

### 29.5 Multi-Currency JE Lines — Option A (currency + foreign_amount columns)
**Commits:** `9a1d2b1` (Option B — FX clearing account, later superseded), `d77f1d5` (Option A — column on line)

**Option B** (commit `9a1d2b1`) used a 1190 FX Clearing account to balance FX transfer JEs. This was replaced by **Option A** in `d77f1d5`.

#### Option A: Database
**Migration:** `2026_04_20_000003_add_currency_foreign_amount_to_journal_entry_lines.php`
```php
$table->string('currency', 10)->default('TZS')->after('credit');
$table->decimal('foreign_amount', 15, 2)->nullable()->after('currency');
```

#### Option A: Model `JournalEntryLine`
- `currency`, `foreign_amount` added to `$fillable` and `$casts`

#### Option A: `JournalEntryService`
- `createAndPost()` passes `currency` and `foreign_amount` through to line creation
- `onAccountTransfer()` for FX transfers sets:
  - Source credit line: `currency = from_currency`, `foreign_amount = amount` (original FCY)
  - Destination debit line: `currency = to_currency`, `foreign_amount = to_amount`
  - `debit` / `credit` columns remain in **TZS** (functional currency) for all reporting

#### JE Show View
New **Foreign Amount** column — displayed only when any line has `foreign_amount` set. Shows: `USD 1,200.00 → TZS 3,120,000`.

---

### 29.6 Bank Statement Pagination, JE Sort Fix, Account Transfer Reversal
**Commit:** `89f4ecf`

#### Bank Statement Pagination
`BankStatementController::show()` — transactions paginated at 10 per page. Keeps `$allTransactions` for the summary stats bar and "Post All" count (unpaginated).

#### JE Sort Fix
`JournalEntryController::index()` — changed `->orderBy('date')` to `->orderBy('created_at', 'desc')` so the most recently created entries appear first.

#### Account Transfer Reversal
**Migration:** `2026_04_20_000002_add_reversal_fields_to_account_transfers_table.php`
```php
$table->timestamp('reversed_at')->nullable();
$table->unsignedBigInteger('reversed_by')->nullable();
$table->unsignedBigInteger('reversal_of_transfer_id')->nullable();
```

**`AccountTransferController::reverse()`** — new method:
1. Guards: transfer must not already be reversed, and not be a reversal itself
2. Creates a new `AccountTransfer` with from/to swapped and same amounts
3. Sets `reversal_of_transfer_id` on the new transfer and `reversed_at` / `reversed_by` on the original
4. Calls `JournalEntryService::onAccountTransfer()` to post the reversal JE
5. Marks the original JE as reversed (`is_reversed = true`, `reversed_by_id`)

**`bank-accounts/show.blade.php`** — for each completed transfer in the history:
- A **Reverse** button appears (hidden once reversed)
- Reversed transfers show a `REV` badge and link to the reversal transfer
- Reversal transfers show a `Reversal of #X` label

---

### 29.7 Fix: TZS→FX Transfer JE Uses TZS Amount
**Commit:** `93ad784`

**Problem:** When transferring from a TZS account to a USD account, the JE debit/credit lines were using the USD `to_amount` value instead of the TZS `amount`, making the JE unbalanced in the functional currency.

**Fix — `JournalEntryService::onAccountTransfer()`:**
```php
// Always use functional-currency amounts (TZS) for debit/credit
$debitAmount  = $transfer->isFx() ? $transfer->amount : $transfer->amount; // TZS source
$creditAmount = $transfer->isFx() ? $transfer->amount : $transfer->amount;
// foreign_amount on the line stores the FCY amount for display only
```

The fix ensures debit = credit = TZS source amount, and the forex foreign amount is stored separately in `journal_entry_lines.foreign_amount` for display purposes only.

---

### 29.8 Fix: Contract PDF Blade Syntax Error
**Commit:** `df23ebd`

`contract-pdf.blade.php` line 145 had `@endif@if(...)` with no whitespace/newline between them, which caused a Blade parse error when generating the contract PDF.

**Fix:** Split into `@endif` on its own line followed by `@if(...)` on the next line.

---

## 30. April 22 — Custom Pagination Bar + JE Filter + Bank Transfer Reconciliation

### 30.1 Custom Pagination Bar Component — All List Views
**Commit:** `8bfa102`

#### New Component
**File:** `resources/views/components/pagination-bar.blade.php`

A shared Blade component providing:
- **Per-page selector** (10 / 25 / 50 / 100) — updates URL and re-fetches
- **Group-jump dropdown** — jumps to any page group based on a letter/number prefix (e.g., all records starting with "A")
- **Showing X–Y of Z** result count
- Standard previous/next page links

Usage (replace `$model->links()` with):
```blade
<x-pagination-bar :paginator="$items" :per-page="$perPage" />
```

#### Controllers Updated
Each controller now reads and validates `per_page` from the request:
```php
$perPage = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100])
    ? (int) $request->get('per_page', 25) : 25;
```

Controllers updated: `JournalEntryController`, `QuotationController`, `InvoiceController`, `ClientController`, `BookingController`, `SupplierPaymentController`

#### Views Updated (replaced `->links()` with `<x-pagination-bar>`)
`journal-entries/index.blade.php`, `quotations/index.blade.php`, `invoices/index.blade.php`, `clients/index.blade.php`, `bookings/index.blade.php`, all cancelled/voided variant views

---

### 30.2 Bank Statement Custom Pagination with Per-Page Selector
**Commit:** `92730b8`

`BankStatementController::show()` — supports `?per_page=` (10/25/50/100) with the same validation pattern. Defaults to 10.

`bank-statements/show.blade.php` — per-page selector + group-jump dropdown matched to the bank statement transaction list style. Keeps `$allTransactions` (unpaginated) for summary totals.

---

### 30.3 Fix: Both Sides of a Bank Transfer Independently Reconcilable
**Commit:** `aa9c560`

**Problem:** When a transfer goes FROM bank account A TO bank account B, both the debit (outflow from A) and the credit (inflow to B) appear as separate transactions on their respective bank statements. Reconciling the debit was blocking or conflicting with reconciling the credit.

**Fix — `BankStatementController::reconcileTransaction()`:**
- Removed the guard that blocked reconciling a transfer that had already been reconciled on the other side
- Each `BankTransaction` is reconciled independently — the debit side and the credit side are treated as separate reconcilable events
- A `side` field (`debit`/`credit`) is stored on the `BankTransaction` to distinguish the two legs

---

### 30.4 Dynamic Source Type Filter on Journal Entries Index
**Commits:** `df01569` (dynamic list from DB), `7195f63` (always show full known list)

**Problem 1 (`df01569`):** The JE filter dropdown had 8 hardcoded source types. New types (`account_transfer_reversal`, `fuel_log`, `maintenance`, etc.) were never showing in the filter.

**Fix 1:** `JournalEntryController::index()` queries `DISTINCT source_type` from `journal_entries`, maps each to a human-readable label, and passes the list to the view. Unknown future types fall back to `ucfirst(str_replace('_', ' ', $type))`.

**Problem 2 (`7195f63`):** After the fix above, if no JEs existed for a type yet, it wouldn't appear in the dropdown.

**Fix 2:** The controller now merges the DB-found types with a **hardcoded master list** of all 14 known types, ensuring the full list is always shown regardless of data. Any DB types NOT in the known list are appended at the end.

Known types list:
```php
$knownTypes = [
    'invoice', 'payment', 'historical_sale', 'account_transfer',
    'account_transfer_reversal', 'bank_statement', 'expense',
    'supplier_payment', 'fuel_log', 'maintenance', 'genset',
    'cash_request', 'credit_note', 'manual',
];
```

---

## 31. April 23 — Account Transfer Reversal Fix + JE Reversed Tab + Backfill Migration

### 31.1 Fix: Account Transfer Reversal Properly Clears Original JE
**Commit:** `c0d350a`

Three bugs were fixed in `AccountTransferController::reverse()`:

**Bug 1 — Original JE not marked reversed:**
The original `JournalEntry` was never updated after creating the reversal JE. Fixed:
```php
$originalJE->update([
    'is_reversed'   => true,
    'reversed_by_id'=> $reversalJE->id,
]);
```

**Bug 2 — Reversal JE description was set to username:**
```php
// BEFORE (wrong):
$reversalJE->update(['description' => auth()->user()->name]);

// AFTER:
$reversalJE->update([
    'description' => $jeDesc,
    'notes'       => 'Reversed by ' . auth()->user()->name,
]);
```

**Bug 3 — FX reversal JE used FCY amounts instead of TZS:**
FX transfer reversals were passing `to_amount` (foreign currency) to the JE debit/credit lines. Fixed to always use the TZS functional-currency `amount` — consistent with the original JE creation logic in `JournalEntryService::onAccountTransfer()`.

---

### 31.2 Separate "Reversed" Tab on Journal Entries Index
**Commit:** `cf4d9c1`

#### Problem
Reversed JEs cluttered the Active tab and had confusing strikethrough styling interspersed with normal entries.

#### Solution
A dedicated `/accounting/journal-entries/reversed` route and view now shows only `is_reversed = true` entries.

**Route added:**
```php
Route::get('/accounting/journal-entries/reversed', [JournalEntryController::class, 'reversed'])
    ->name('admin.accounting.journal-entries.reversed');
```

**`JournalEntryController::index()`** — Active tab query now adds `->where('is_reversed', false)` to exclude reversed entries.

**`JournalEntryController::reversed()`** — new method, returns only `is_reversed = true` entries with extra eager-loads.

**`JournalEntryController::buildSourceTypes()`** — extracted as a private helper to avoid duplication between `index()` and `reversed()`.

**Stats bar** on the index page updated from 3 to 4 columns: **Active | Draft | Posted | Reversed**

**New view:** `resources/views/admin/accounting/journal-entries/reversed.blade.php`
- Strikethrough styling throughout
- **Source badge** (type + source_id)
- **"Reversed by JE"** column with link to the reversal entry
- Read-only — View button only, no Edit/Post/Delete actions

---

### 31.3 Backfill: `is_reversed` on Historical Transfer JEs
**Commit:** `c8806e4`

After the `c0d350a` fix, any account transfer reversals done *before* the fix were missing `is_reversed = true` and `reversed_by_id` on the original JEs.

**Migration:** `2026_04_23_094417_backfill_is_reversed_on_journal_entries.php`

Logic:
1. Finds all `journal_entries` where `source_type = 'account_transfer_reversal'`
2. Loads the reversal `AccountTransfer` record and its `reversal_of_transfer_id`
3. Via `reversal_of_transfer_id`, finds the original transfer's JE
4. Updates the original JE: `is_reversed = true`, `reversed_by_id = reversalJE->id`
5. Rows already correctly set are skipped (idempotent)

---

*End of April 13–23 retrospective additions.*

---

---

# April 8, 2026 — Company Stamp + Cancelled/Voided Views

*(Documented retrospectively on April 25. These commits predate the original guide.)*

---

## 32. April 8 — Company Stamp Upload

**Commit:** `ed2387e`

### Problem
The company settings page had no way to upload an official stamp/seal image. Invoices and quotation PDFs only showed the company logo but had no stamp.

### Database
**Migration:** `2026_04_08_080943_add_stamp_path_to_company_settings_table.php`
```php
$table->string('stamp_path')->nullable()->after('logo_path');
```

### Model: `CompanySetting`
- `stamp_path` added to `$fillable`
- New accessor:
```php
public function getStampUrlAttribute(): ?string
{
    return $this->stamp_path ? Storage::url($this->stamp_path) : null;
}
```

### Controller: `CompanySettingController`
- `update()` — validates `stamp` as `nullable|image|mimes:png,jpg,jpeg,webp|max:2048`; stores to `company/` on the `public` disk; deletes the old stamp before saving the new one; strips `stamp` from `$validated` before the DB update (like `logo`)
- New `deleteStamp()` method — deletes file from disk, nulls `stamp_path`, redirects back with success flash

### Routes
```php
Route::post('/company-settings/stamp/delete', [CompanySettingController::class, 'deleteStamp'])
    ->name('company-settings.stamp.delete');
```

### Views
- **`company-settings/edit.blade.php`** — new "Company Stamp" upload section with file preview, current stamp thumbnail, and a "Remove stamp" button (POST to delete route)
- **`invoices/pdf.blade.php`** — stamp image rendered in the PDF footer/header area when `$settings->stamp_url` is set
- **`quotations/pdf.blade.php`** — same stamp rendering as invoice PDF

---

## 33. April 8 — Separate Cancelled/Voided Views

**Commit:** `ad9d223`

### Problem
The invoice, booking, and quotation index pages were listing all statuses including cancelled, voided, and rejected records. This cluttered the active working views and made filtering unwieldy.

### Solution
Terminal-status records are excluded from the main index pages and given their own dedicated read-only views.

### Routes Added
```php
Route::get('/bookings/cancelled', [BookingController::class, 'cancelled'])
    ->name('bookings.cancelled');
Route::get('/invoices/voided', [InvoiceController::class, 'voided'])
    ->name('invoices.voided');
// JE route fix (moved above wildcard to fix 404):
Route::get('/accounting/journal-entries/{journalEntry}', [JournalEntryController::class, 'show'])
    ->name('admin.accounting.journal-entries.show');
```

### Controller Changes

**`InvoiceController::index()`** — now excludes `['void', 'declined', 'written_off']` statuses:
```php
$cancelledStatuses = ['void', 'declined', 'written_off'];
$query = Invoice::with(...)->whereNotIn('status', $cancelledStatuses)->latest();
```

**`InvoiceController::voided()`** — new method, mirrors `index()` but with `->whereIn('status', $cancelledStatuses)`. Supports `?status=` filter within cancelled statuses.

**`BookingController::index()`** — now excludes `['cancelled', 'rejected']` statuses.

**`BookingController::cancelled()`** — new method, shows only cancelled/rejected bookings.

**`QuotationController::index()`** — terminal statuses removed from the filter dropdown options (rejected quotations were already handled by a prior commit).

**`QuotationController`** — `rejected` stat card added to index stats.

### New Views
- **`resources/views/admin/bookings/cancelled.blade.php`** — read-only table of cancelled/rejected bookings with status badges, client name, booking number, dates
- **`resources/views/admin/invoices/voided.blade.php`** — read-only table of voided/declined/written-off invoices

### Navigation
Both index pages got a **"View Cancelled"** / **"View Voided"** link in the header area pointing to the new views.

---

*End of April 8 retrospective additions.*

---

---

# March 24–April 3, 2026 — Permissions, Audit Trail, Company Settings, Contract PDF

*(Documented retrospectively on April 25. These commits predate the original guide.)*

---

## 34. March 24 — Permissions Overhaul

### 34.1 Comprehensive Permissions Overhaul — Granular Access Control
**Commit:** `fee6813`

#### Problem
Access control was either on/off per role (admin/staff). No granular per-feature permissions existed. Staff could access everything or nothing.

#### Solution
A new `Permission` model + `CheckPermission` middleware were introduced. All routes and views are gated by named permissions.

**Files changed:** `CheckPermission.php`, `PermissionsSeeder.php`, `DatabaseSeeder.php`, `admin-layout.blade.php`, `routes/web.php`

**`app/Http/Middleware/CheckPermission.php`** — middleware that looks up `auth()->user()->permissions` and aborts 403 if the named permission is absent.

**`PermissionsSeeder.php`** — defines all permissions in groups:

| Group | Key examples |
|---|---|
| Invoicing | `manage_invoices` |
| Accounting | `approve_payments`, `view_all_expenses`, `create_expenses`, `approve_expenses`, `post_expenses`, `view_all_journal_entries` |
| Bookings | `manage_bookings`, `approve_bookings`, `view_all_bookings` |
| Clients | `manage_clients`, `view_all_clients` |
| Inventory | `manage_inventory` |
| Suppliers | `manage_suppliers` |
| HR | `manage_users`, `manage_roles` |
| Settings | `manage_company_settings` |

**`routes/web.php`** — all protected admin routes now wrapped in `Route::middleware('permission:key_name')` groups.

**`admin-layout.blade.php`** — all nav items wrapped in `@permission('key_name')` directive.

---

### 34.2 Row-Level Data Scoping
**Commit:** `85b044b`

**Problem:** All users could see all records (invoices, bookings, quotations, expenses, etc.) regardless of who created them.

**Fix:** Six controllers now scope queries to `created_by = auth()->id()` by default:

```php
// Pattern applied across all affected controllers:
$user = auth()->user();
$query = Invoice::where('created_by', $user->id)->latest();
```

Controllers scoped: `BookingController`, `CashRequestController`, `CreditNoteController`, `ExpenseController`, `InvoiceController`, `QuotationController`, `SupplierPaymentController`

---

### 34.3 Replace Hardcoded `seeAll` with `view_all_*` Permissions
**Commit:** `743caad`

**Problem:** Row-level scoping in `85b044b` used a hardcoded `$user->role === 'admin'` check to decide if a user sees all records.

**Fix:** All six controllers now use `PermissionService::can($user, 'view_all_invoices')` (etc.) instead:

```php
$seeAll = PermissionService::can($user, 'view_all_invoices');
$query = $seeAll
    ? Invoice::latest()
    : Invoice::where('created_by', $user->id)->latest();
```

This makes "see all" a configurable permission assigned per role/user — no hardcoding.

**New permissions added:** `view_all_bookings`, `view_all_invoices`, `view_all_quotations`, `view_all_expenses`, `view_all_cash_requests`, `view_all_credit_notes`, `view_all_supplier_payments`

---

### 34.4 Hide Action Buttons Based on Permissions in All Show Views
**Commit:** `79654a7`

All action buttons throughout show views (Approve, Edit, Delete, Post, Reject, etc.) are now conditionally rendered using `@permission()`:

```blade
@permission('approve_bookings')
<form method="POST" action="...">
    <button>Approve</button>
</form>
@endpermission
```

Controllers: `BookingController`, `CashRequestController`, `CreditNoteController`, `ExpenseController`, `GensetController`, `InvoiceController`, `QuotationController`

---

### 34.5 Role Modal Redesign
**Commit:** `b3f62ba`

The "Create Role" and "Edit Role" modals were narrow single-column layouts that made the permission list hard to read.

**Changes to `admin-layout.blade.php`** (or the roles view):
- Modal widened to `max-w-4xl`
- Permission checkboxes organised in a **2-column grid** grouped by module
- Scrollable modal body (`overflow-y-auto max-h-[70vh]`) so long permission lists don't overflow

---

## 35. March 24 — Audit Trail + Supplier Profile + Zero-Rated Expenses

### 35.1 Comprehensive Audit Trail
**Commit:** `42fcc4d`

**New controller:** `app/Http/Controllers/Admin/AuditTrailController.php`

**New view:** `resources/views/admin/audit-trail/index.blade.php`

**Route:**
```php
Route::get('/audit-trail', [AuditTrailController::class, 'index'])
    ->name('admin.audit-trail.index')
    ->middleware('permission:manage_users');
```

All major controllers now call `ActivityLog::log(...)` (or equivalent) on create/update/delete/approve/reject/post actions. The audit trail index shows:
- Actor (user name + role)
- Action performed
- Target record (type + ID/number)
- Timestamp
- IP address / user agent

---

### 35.2 Enhanced Supplier Profile
**Commit:** `9367890`

#### Database
Three migrations added:
- `2026_03_24_141038_add_enhanced_fields_to_suppliers_table.php` — adds `tin`, `vrn`, `bank_name`, `bank_account_number`, `bank_branch`, `contact_person`, `contact_phone`, `contact_email`, `address`

#### Model: `Supplier`
New fields added to `$fillable`.

#### Views
- **`suppliers/create.blade.php`** — expanded form with TIN/VRN, bank details section, contact person section
- **`suppliers/show.blade.php`** — full profile card showing all new fields; separate contact and banking sections

---

### 35.3 Zero-Rated Expenses
**Commit:** `9367890`

#### Database
**Migration:** `2026_03_24_144649_add_is_zero_rated_to_expenses_table.php`
```php
$table->boolean('is_zero_rated')->default(false)->after('vat_amount');
```

#### Model: `Expense`
`is_zero_rated` added to `$fillable` and `$casts`.

#### Views
- **`expenses/create.blade.php`** — zero-rated Alpine.js toggle; when ON, VAT amount field is hidden and VAT is set to 0 automatically
- **`expenses/show.blade.php`** — amber "Zero-Rated" badge shown when `$expense->is_zero_rated`

---

### 35.4 Booking Company Name + Quotation Customer Fields
**Commit:** `9367890`

#### Database
- `2026_03_24_133936_add_company_name_to_bookings_table.php` — adds `company_name` (nullable string) to `bookings`
- `2026_03_24_134637_add_customer_fields_to_quotations_table.php` — adds `customer_name`, `customer_email`, `customer_phone`, `customer_company` to `quotations` (for direct quotations without a linked client)

#### Views
- **`bookings/index.blade.php`** + **`bookings/show.blade.php`** — display `company_name` alongside client name
- **`quotations/create.blade.php`**, **`index.blade.php`**, **`show.blade.php`**, **`approved.blade.php`**, **`rejected.blade.php`** — customer fields shown/collected for non-client quotations

---

## 36. March 25 — Booking Contract PDF + JE Export + Expense COA

**Commit:** `a40804e`

### 36.1 Booking Contract PDF (DomPDF)
**Added to `BookingController`:**
- `downloadContractPdf()` — generates a formal rental contract as PDF using DomPDF
- Requires booking status ≥ `approved`
- Loads base64-encoded signature/stamp from `public/img/signature-stamp.png`

**New view:** `resources/views/admin/bookings/contract-pdf.blade.php`
- Full contract with company letterhead, client details, genset description, rental period, terms and conditions clauses
- Signature blocks, DomPDF-compatible inline CSS

**Config:** `config/dompdf.php` published; `enable_remote` set to `true` to support base64 image embedding.

**`bookings/show.blade.php`** — "Download Contract PDF" button added for approved+ bookings.

---

### 36.2 Re-Approval on Booking Edit
**Added to `BookingController::update()`:**
If a booking is in `approved` status when edited:
1. Status is reverted to `created`
2. `approved_by` and `approved_at` are cleared
3. The original approver receives an `AppNotification` asking them to re-approve

**`bookings/show.blade.php`** — "Edit Booking" button shown in sidebar for `created` and `approved` bookings.

---

### 36.3 Journal Entries — Sort, Pagination, CSV Export
**`JournalEntryController::index()`:**
- Sorted by `date DESC`
- Paginated to **10 per page**
- Passes `$perPage` to view

**`JournalEntryController::export()`** — new method, streams CSV of filtered JE list (all posted entries, with source type/id, debit/credit totals).

**`journal-entries/index.blade.php`** — CSV export button added.

---

### 36.4 Expense Categories — COA Account Linkage + VAT Split in JEs
**`ExpenseController::store()` / `approve()` / `post()`:**
- When posting an expense to the ledger, if the expense category has a linked COA `account_id`, the JE debit line uses that account instead of a generic expense account
- If `is_zero_rated = false`, VAT portion is split: net amount → expense COA, VAT → 1180 VAT Input

**ChartOfAccountsSeeder:** 1180 VAT Input account seeded.

**DatabaseSeeder:** `ChartOfAccountsSeeder` and `ExpenseCategorySeeder` wired in.

---

### 36.5 Permissions Audit — `@permission` Guards in Views
**Commit:** `a40804e` also applied `@permission()` guards across remaining show views:
`cash-requests/show.blade.php`, `credit-notes/show.blade.php`, `supplier-payments/show.blade.php`, `deliveries/show.blade.php`, `invoices/show.blade.php`, `gensets/index.blade.php`, `gensets/show.blade.php`, `maintenance/index.blade.php`, `maintenance/show.blade.php`, `inventory/categories/index.blade.php`, `inventory/items/index.blade.php`, `inventory/items/show.blade.php`

---

## 37. March 30 — Company Settings Module

**Commit:** `7b9c094`

### Overview
A central **Company Settings** module allows admins to configure all company-level information that appears on invoices, quotations, PDFs, and other system documents.

### Database
**Migration:** `2026_03_30_000001_create_company_settings_table.php`

Key columns:
```
company_name, trading_name, address_line1, address_line2, city, country
phone, email, website
tin, vrn
bank_name, bank_account_number, bank_branch, bank_swift
logo_path
primary_color (hex, for PDF theming)
invoice_prefix, quotation_prefix
invoice_terms (text), quotation_terms (text)
footer_note
```

### Model: `CompanySetting`
- Singleton pattern: `CompanySetting::current()` returns or creates the single settings row
- `getLogoUrlAttribute()` — returns `Storage::url($logo_path)` or null
- `getAddressBlockAttribute()` — formats multi-line address for PDF/letterhead
- `getBankDetailsBlockAttribute()` — formats bank info block

### Controller: `CompanySettingController`
- `edit()` — loads current settings
- `update()` — validates all fields; handles logo file upload (PNG/JPG/SVG ≤ 2MB); replaces old logo on re-upload
- `deleteLogo()` — deletes logo file + clears `logo_path`

### Global View Sharing
**`AppServiceProvider::boot()`:**
```php
View::composer('*', function ($view) {
    $view->with('companySetting', CompanySetting::current());
});
```
Every Blade view automatically has `$companySetting` available.

### PDF Integration
**`invoices/pdf.blade.php`** — now uses:
- `$companySetting->logo_url` for letterhead logo
- `$companySetting->company_name`, `address_block`, `tin`, `vrn`
- `$companySetting->primary_color` for accent color theming
- `$companySetting->bank_details_block` for the payment section
- Dynamic footer: `$companySetting->footer_note`

Same integration applied to **`quotations/pdf.blade.php`**.

### Additional
- **"Issued By" sidebar card** added to `invoices/show.blade.php` and `quotations/show.blade.php` showing company name + contact
- **Navigation** — "Company Settings" link added to admin Settings dropdown (`manage_company_settings` permission)

### Routes
```php
Route::middleware('permission:manage_company_settings')->group(function () {
    Route::get('/company-settings',         [CompanySettingController::class, 'edit'])->name('company-settings.edit');
    Route::put('/company-settings',         [CompanySettingController::class, 'update'])->name('company-settings.update');
    Route::post('/company-settings/logo/delete', [CompanySettingController::class, 'deleteLogo'])->name('company-settings.logo.delete');
});
```

---

## 38. March 30–31 — Dynamic Line Item Types + Bug Fixes

### 38.1 Dynamic Quotation Line Item Types
**Commit:** `92074da`

#### Problem
Quotation line items had a hardcoded `item_type` enum (e.g., `genset_rental`, `delivery`, `fuel`). Adding new types required a code change.

#### Solution
A new `quotation_item_types` table and admin management UI allow types to be configured without code changes.

#### Database
- **`2026_03_30_100000_create_quotation_item_types_table.php`** — `id`, `name`, `slug`, `is_active`, `sort_order`
- **`2026_03_30_100001_change_item_type_to_string.php`** — converts `quotation_items.item_type` from enum to `varchar(100)` so any slug can be stored

#### Model: `QuotationItemType`
Standard model with `$fillable = ['name', 'slug', 'is_active', 'sort_order']`.

**`QuotationItem`** — `item_type` cast changed from enum to plain string.

#### New Controller: `QuotationItemTypeController`
Full CRUD for quotation item types (index, store, update, destroy). Accessible at `/admin/quotation-item-types`.

#### View: `quotation-item-types/index.blade.php`
Inline-edit table of all types with name, slug, active toggle, sort order, and delete.

#### Quotation Create/Edit Views
The `item_type` dropdown in `quotations/create.blade.php` and `quotations/edit.blade.php` now reads from `QuotationItemType::where('is_active', true)->orderBy('sort_order')->get()` instead of a hardcoded list.

---

### 38.2 Payment Ledger + Bank Balance Fixes
**Commit:** `92074da`

- **`InvoiceController`** — invoice show now loads `payments.recordedBy` relation for the payment history table
- **`JournalEntryService`** fixes — payment JE lines now correctly apply debit/credit on the bank account and AR account
- **`dashboard.blade.php`** — bank account total now sums `current_balance` across all active accounts

---

### 38.3 March 31 Bug Fixes (6 commits)

| Commit | Fix |
|---|---|
| `f3d299c` | **FK constraint**: `quotations.client_id` was incorrectly constrained to `users.id` — fixed to `clients.id`. Caused `client_id` to always be `NULL` silently, breaking PDF client display. |
| `efc98ad` | **Duplicate migrations**: removed duplicate migration files that caused FK ordering failures on `php artisan migrate:fresh`. |
| `4f45804` | **Manual quotation PDFs**: show customer name/email/company for quotations with no `client_id` (created as "Direct Quotation"). Form now defaults to "Existing Client" mode. |
| `91a3664` | **Null `issue_date` crash** in quotation PDF template — guarded with `$quotation->issue_date ?? now()`. |
| `615f5e0` | **Alpine.js scope bug** causing zero-rated toggle to default incorrectly when multiple expense forms on page. Fixed by scoping `x-data` more tightly. |
| `b3618db` | **Nested `<form>` for logo delete** — HTML spec forbids nested forms; the inner delete form was silently broken. Replaced with a standalone `<form>` outside the settings form. |
| `137c0c1` | **Zero-rated toggle** replaced CSS `peer` toggle (which requires adjacent sibling) with Alpine.js `:class` binding for reliable cross-browser toggle state. |
| `b823780` | **Zero-rated toggle visual** — enhanced ON/OFF visual states with color feedback (green = ON/zero-rated, gray = OFF/VAT applies). |
| `e0ce654` | **Em dash in PDF templates** — `&mdash;` HTML entity is not rendered by DomPDF; replaced with the UTF-8 `—` character directly in the Blade templates. |
| `d7feb57` | **Seeder**: updated `DatabaseSeeder` to auto-create a super admin user (`admin@milelepower.com`) on fresh migrations for production bootstrapping. |

---

## 39. April 1–3 — PDF Fixes, Page Loader, Quotation Stats

### 39.1 Reduce PDF Header Spacing
**Commit:** `ec4c9cd`

**Problem:** When a large company logo was uploaded, it pushed the invoice/quotation content to page 2 in the PDF.

**Fix — `invoices/pdf.blade.php`** + **`quotations/pdf.blade.php`:**
- Reduced `padding-top` on the header block
- Logo constrained to `max-height: 60px` in the PDF stylesheet
- Company name block condensed (removed extra margin between lines)

---

### 39.2 Five-Dot Page Loader Overlay
**Commit:** `d6c4ff4`

**File:** `resources/views/components/admin-layout.blade.php`

Added a full-screen overlay with an animated five-dot loader that appears on page navigation (form submit or link click) and disappears once the new page is fully loaded:

```html
<div id="page-loader" style="display:none" class="fixed inset-0 z-[9999] bg-white/80 flex items-center justify-center">
    <!-- five dots animation -->
</div>
<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href]') || e.target.closest('form')) {
            document.getElementById('page-loader').style.display = 'flex';
        }
    });
    window.addEventListener('pageshow', function() {
        document.getElementById('page-loader').style.display = 'none';
    });
</script>
```

---

### 39.3 Quotation Stats Fix + Cancelled Quotations Page
**Commit:** `259d1fe`

#### Stats Fix
The **"Accepted"** stat card on the quotations index was counting all quotations linked to any booking, including cancelled ones. Fixed:
```php
// BEFORE:
'accepted' => Quotation::whereHas('booking')->count(),

// AFTER:
'accepted' => Quotation::whereHas('booking', fn($q) => $q->whereNotIn('status', ['cancelled', 'rejected']))->count(),
```

#### Cancelled Quotations Page
**`QuotationController::cancelled()`** — new method showing quotations with status `rejected` or `cancelled`.

**New view:** `resources/views/admin/quotations/cancelled.blade.php`

**Route:**
```php
Route::get('/quotations/cancelled', [QuotationController::class, 'cancelled'])
    ->name('quotations.cancelled');
```

#### Clickable Stat Cards
All five quotation stat cards (`Draft`, `Sent`, `Viewed`, `Accepted`, `Cancelled`) now link to a filtered list view.

#### Payment Modal Widened
The "Record Payment" modal on the invoice show page widened from `max-w-md` to `max-w-2xl` with a 2-column layout for better usability on wider screens.

---

*End of March 24–April 3 retrospective additions.*
