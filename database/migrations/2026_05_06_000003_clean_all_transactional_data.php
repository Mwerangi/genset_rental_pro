<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Production data-clean migration — COMPREHENSIVE.
 *
 * This single migration supersedes:
 *   • 2026_05_05_000002_clean_expenses_cash_requests_bank_statements
 *   • 2026_05_05_000003_clean_transfers_supplier_payments_invoice_payments
 *
 * WHAT IS DELETED (all transactional / operational data):
 * ──────────────────────────────────────────────────────
 *   Accounting
 *     journal_entry_lines    (deleted via journal_entries cascade)
 *     journal_entries        (all of them)
 *     account_transfers
 *     supplier_payments
 *     invoice_payments
 *     expenses
 *     bank_transactions      (deleted via bank_statements cascade)
 *     bank_statements
 *     cash_request_items     (deleted via cash_requests cascade)
 *     cash_requests
 *     daily_closings
 *
 *   Client transactions
 *     quote_requests
 *     quotation_items        (deleted via quotations cascade, or direct)
 *     quotations
 *     invoice_items          (deleted via invoices cascade)
 *     invoices
 *     deliveries
 *     bookings
 *     credit_notes
 *
 *   Supplier transactions
 *     purchase_order_items   (deleted via purchase_orders cascade, or direct)
 *     purchase_orders
 *
 *   Operations
 *     fuel_logs
 *     maintenance_records
 *     stock_movements
 *
 *   Logs / notifications
 *     user_activity_logs
 *     notifications
 *
 * WHAT IS KEPT (structure preserved, balances/links reset):
 * ─────────────────────────────────────────────────────────
 *   accounts           — kept; balance reset to 0
 *   bank_accounts      — kept; current_balance reset to 0
 *   inventory_items    — kept; current_stock reset to 0
 *   gensets            — kept; journal_entry_id unlinked (NULL)
 *   clients            — kept (profile rows only, incl. addresses & contacts)
 *   suppliers          — kept (profile rows only)
 *   users, roles, permissions, company_settings, expense_categories,
 *   inventory_categories, gensets, quotation_item_types — all kept intact
 *
 * IRREVERSIBLE — data cannot be recovered once run.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            // Disable FK checks so we can delete in any order without constraint errors
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                // Helper: delete from table only if it exists (guards against
                // tables not yet created on this environment).
                $wipe   = fn(string $t) => \Schema::hasTable($t) ? DB::statement("DELETE FROM `{$t}`") : null;
                $reset  = fn(string $t, string $col, mixed $val = 0) => \Schema::hasTable($t) ? DB::statement("UPDATE `{$t}` SET `{$col}` = {$val}") : null;
                $nullify = fn(string $t, string $col) => \Schema::hasTable($t) && \Schema::hasColumn($t, $col) ? DB::statement("UPDATE `{$t}` SET `{$col}` = NULL") : null;

                // ── 1. Journal entries ─────────────────────────────────────────
                $wipe('journal_entry_lines');
                $wipe('journal_entries');

                // ── 2. Accounting records ──────────────────────────────────────
                $wipe('account_transfers');
                $wipe('supplier_payments');
                $wipe('invoice_payments');
                $wipe('expenses');
                $wipe('bank_transactions');
                $wipe('bank_statements');
                $wipe('cash_request_items');
                $wipe('cash_requests');
                $wipe('daily_closings');

                // ── 3. Client transactions ─────────────────────────────────────
                $wipe('quote_requests');
                $wipe('quotation_items');
                $wipe('quotations');
                $wipe('invoice_items');
                $wipe('invoices');
                $wipe('deliveries');
                $wipe('bookings');
                $wipe('credit_notes');

                // ── 4. Supplier transactions ───────────────────────────────────
                $wipe('purchase_order_items');
                $wipe('purchase_orders');

                // ── 5. Operations ──────────────────────────────────────────────
                $wipe('fuel_logs');
                $wipe('maintenance_records');
                $wipe('stock_movements');

                // ── 6. Logs & notifications ────────────────────────────────────
                $wipe('user_activity_logs');
                $wipe('notifications');

                // ── 7. Reset running balances ──────────────────────────────────
                $reset('accounts',        'balance');
                $reset('bank_accounts',   'current_balance');
                $reset('inventory_items', 'current_stock');

                // ── 8. Unlink journal_entry_id on gensets ──────────────────────
                $nullify('gensets', 'journal_entry_id');

            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });
    }

    public function down(): void
    {
        // Irreversible — no rollback possible.
    }
};
