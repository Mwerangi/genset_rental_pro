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
