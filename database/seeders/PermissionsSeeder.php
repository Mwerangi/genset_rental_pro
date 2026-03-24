<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Permissions catalogue.
     * [ name => [label, module, sort_order] ]
     */
    private array $permissions = [
        // ── Sales Pipeline ────────────────────────────────────────────
        'view_quote_requests'      => ['View Quote Requests',              'Sales Pipeline', 1],
        'manage_quote_requests'    => ['Manage Quote Requests',             'Sales Pipeline', 2],
        'view_quotations'          => ['View Quotations',                   'Sales Pipeline', 3],
        'manage_quotations'        => ['Manage Quotations',                 'Sales Pipeline', 4],
        'view_all_quotations'      => ['View All Quotations (not just own)', 'Sales Pipeline', 5],

        // ── Bookings ─────────────────────────────────────────────────
        'view_bookings'            => ['View Bookings',                     'Bookings',        1],
        'manage_bookings'          => ['Manage Bookings',                   'Bookings',        2],
        'approve_bookings'         => ['Approve / Reject Bookings',         'Bookings',        3],
        'view_all_bookings'        => ['View All Bookings (not just own)',   'Bookings',        4],

        // ── Clients (CRM) ─────────────────────────────────────────────
        'view_clients'             => ['View Clients',                'Clients',         1],
        'manage_clients'           => ['Manage Clients',              'Clients',         2],

        // ── Fleet & Operations ────────────────────────────────────────
        'view_fleet'               => ['View Fleet (Gensets / Deliveries / Maintenance)', 'Fleet & Operations', 1],
        'manage_fleet'             => ['Manage Gensets',              'Fleet & Operations', 2],
        'manage_deliveries'        => ['Manage Deliveries',           'Fleet & Operations', 3],
        'manage_maintenance'       => ['Manage Maintenance Records',  'Fleet & Operations', 4],

        // ── Fuel Logs ─────────────────────────────────────────────────
        'view_fuel_logs'           => ['View Fuel Logs',              'Fuel Logs',       1],
        'manage_fuel_logs'         => ['Manage Fuel Logs',            'Fuel Logs',       2],

        // ── Inventory ────────────────────────────────────────────────
        'view_inventory'           => ['View Inventory',              'Inventory',       1],
        'manage_inventory'         => ['Manage Inventory Items & Categories', 'Inventory', 2],
        'manage_suppliers'         => ['Manage Suppliers',            'Inventory',       3],
        'manage_purchase_orders'   => ['Manage Purchase Orders',      'Inventory',       4],

        // ── Invoicing ─────────────────────────────────────────────────
        'view_invoices'            => ['View Invoices',                      'Invoicing',       1],
        'manage_invoices'          => ['Manage Invoices & Payments',          'Invoicing',       2],
        'view_all_invoices'        => ['View All Invoices (not just own)',    'Invoicing',       3],

        // ── Accounting ────────────────────────────────────────────────
        'view_accounting'          => ['View Accounting (Chart of Accounts & Bank Accounts)', 'Accounting', 1],
        'manage_accounting'        => ['Manage Chart of Accounts & Journal Entries',  'Accounting', 2],
        'approve_payments'         => ['Approve Expenses & Payments', 'Accounting',      3],
        'view_cash_requests'       => ['View / Submit Cash Requests',        'Accounting',      4],
        'approve_cash_requests'    => ['Approve / Pay Cash Requests',        'Accounting',      5],
        'view_all_cash_requests'   => ['View All Cash Requests (not just own)', 'Accounting',   6],
        'view_expenses'            => ['View Expenses',                      'Accounting',      7],
        'view_all_expenses'        => ['View All Expenses (not just own)',    'Accounting',      8],
        'view_journal_entries'     => ['View Journal Entries',               'Accounting',      9],
        'view_supplier_payments'   => ['View Supplier Payments',             'Accounting',     10],
        'view_all_supplier_payments' => ['View All Supplier Payments (not just own)', 'Accounting', 11],
        'view_credit_notes'        => ['View Credit Notes',                  'Accounting',     12],
        'view_all_credit_notes'    => ['View All Credit Notes (not just own)', 'Accounting',   13],

        // ── Reports ───────────────────────────────────────────────────
        'view_reports'             => ['View Reports & Tax Documents', 'Reports',        1],

        // ── System ────────────────────────────────────────────────────
        'manage_users'             => ['Manage Users',                'System',          1],
        'manage_permissions'       => ['Manage Role Permissions',     'System',          2],
        'view_audit_trail'         => ['View Audit Trail',            'System',          3],
    ];

    /**
     * Default role → permissions matrix.
     */
    private array $rolePermissions = [
        // Super Admin gets permissions dynamically (bypassed in middleware), seeding anyway:
        'super_admin' => '*', // special marker — handled below

        'admin' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations', 'view_all_quotations',
            'view_bookings', 'manage_bookings', 'approve_bookings', 'view_all_bookings',
            'view_clients', 'manage_clients',
            'view_fleet', 'manage_fleet', 'manage_deliveries', 'manage_maintenance',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_inventory', 'manage_inventory', 'manage_suppliers', 'manage_purchase_orders',
            'view_invoices', 'manage_invoices', 'view_all_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_cash_requests', 'approve_cash_requests', 'view_all_cash_requests',
            'view_expenses', 'view_all_expenses',
            'view_journal_entries',
            'view_supplier_payments', 'view_all_supplier_payments',
            'view_credit_notes', 'view_all_credit_notes',
            'view_reports',
            'manage_users',
            'view_audit_trail',
            // manage_permissions intentionally excluded for admin
        ],

        'finance_manager' => [
            'view_quote_requests', 'view_quotations', 'view_all_quotations',
            'view_bookings', 'view_all_bookings',
            'view_clients',
            'view_fleet', 'view_fuel_logs',
            'view_inventory',
            'view_invoices', 'manage_invoices', 'view_all_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_cash_requests', 'approve_cash_requests', 'view_all_cash_requests',
            'view_expenses', 'view_all_expenses',
            'view_journal_entries',
            'view_supplier_payments', 'view_all_supplier_payments',
            'view_credit_notes', 'view_all_credit_notes',
            'view_reports',
        ],

        'operations_manager' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations', 'view_all_quotations',
            'view_bookings', 'manage_bookings', 'approve_bookings', 'view_all_bookings',
            'view_clients', 'manage_clients',
            'view_fleet', 'manage_fleet', 'manage_deliveries', 'manage_maintenance',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_inventory', 'manage_inventory', 'manage_suppliers', 'manage_purchase_orders',
            'view_invoices',
            'view_accounting',
            'view_cash_requests', 'view_all_cash_requests',
            'view_expenses', 'view_all_expenses',
            'view_reports',
        ],

        'sales_manager' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations', 'view_all_quotations',
            'view_bookings', 'manage_bookings', 'view_all_bookings',
            'view_clients', 'manage_clients',
            'view_fleet',
            'view_invoices', 'view_all_invoices',
            'view_cash_requests',
            'view_reports',
        ],

        'dispatcher' => [
            'view_bookings',
            'view_fleet', 'manage_deliveries',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_cash_requests',
        ],

        'driver' => [
            'view_bookings',
            'view_fleet',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_cash_requests',
        ],

        'technician' => [
            'view_fleet', 'manage_maintenance',
            'view_inventory',
            'view_fuel_logs',
            'view_cash_requests',
        ],

        'accountant' => [
            'view_quote_requests', 'view_quotations', 'view_all_quotations',
            'view_bookings', 'view_all_bookings',
            'view_clients',
            'view_inventory',
            'view_invoices', 'manage_invoices', 'view_all_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_cash_requests', 'approve_cash_requests', 'view_all_cash_requests',
            'view_expenses', 'view_all_expenses',
            'view_journal_entries',
            'view_supplier_payments', 'view_all_supplier_payments',
            'view_credit_notes', 'view_all_credit_notes',
            'view_reports',
        ],

        'staff' => [
            'view_bookings',
            'view_fleet',
            'view_fuel_logs',
            'view_cash_requests',
        ],
    ];

    public function run(): void
    {
        // 1. Seed permissions catalogue
        foreach ($this->permissions as $name => [$label, $module, $sortOrder]) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'module' => $module, 'sort_order' => $sortOrder]
            );
        }

        $allPermissionNames = array_keys($this->permissions);

        // 2. Seed role → permission mappings (additive — safe to run multiple times)
        foreach ($this->rolePermissions as $role => $perms) {
            $toInsert = ($perms === '*') ? $allPermissionNames : $perms;

            foreach ($toInsert as $perm) {
                RolePermission::firstOrCreate([
                    'role'            => $role,
                    'permission_name' => $perm,
                ]);
            }
        }
    }
}
