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

            // Disable FK checks for the duration so we can delete in any order
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                // ── 1. Journal entries ─────────────────────────────────────────
                DB::statement('DELETE FROM journal_entry_lines');
                DB::statement('DELETE FROM journal_entries');

                // ── 2. Accounting records ──────────────────────────────────────
                DB::statement('DELETE FROM account_transfers');
                DB::statement('DELETE FROM supplier_payments');
                DB::statement('DELETE FROM invoice_payments');
                DB::statement('DELETE FROM expenses');
                DB::statement('DELETE FROM bank_transactions');
                DB::statement('DELETE FROM bank_statements');
                DB::statement('DELETE FROM cash_request_items');
                DB::statement('DELETE FROM cash_requests');
                DB::statement('DELETE FROM daily_closings');

                // ── 3. Client transactions ─────────────────────────────────────
                DB::statement('DELETE FROM quote_requests');
                DB::statement('DELETE FROM quotation_items');
                DB::statement('DELETE FROM quotations');
                DB::statement('DELETE FROM invoice_items');
                DB::statement('DELETE FROM invoices');
                DB::statement('DELETE FROM deliveries');
                DB::statement('DELETE FROM bookings');
                DB::statement('DELETE FROM credit_notes');

                // ── 4. Supplier transactions ───────────────────────────────────
                DB::statement('DELETE FROM purchase_order_items');
                DB::statement('DELETE FROM purchase_orders');

                // ── 5. Operations ──────────────────────────────────────────────
                DB::statement('DELETE FROM fuel_logs');
                DB::statement('DELETE FROM maintenance_records');
                DB::statement('DELETE FROM stock_movements');

                // ── 6. Logs & notifications ────────────────────────────────────
                DB::statement('DELETE FROM user_activity_logs');
                DB::statement('DELETE FROM notifications');

                // ── 7. Reset running balances on accounts ──────────────────────
                DB::statement('UPDATE accounts SET balance = 0');
                DB::statement('UPDATE bank_accounts SET current_balance = 0');
                DB::statement('UPDATE inventory_items SET current_stock = 0');

                // ── 8. Unlink journal_entry_id on gensets ──────────────────────
                DB::statement('UPDATE gensets SET journal_entry_id = NULL');

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
