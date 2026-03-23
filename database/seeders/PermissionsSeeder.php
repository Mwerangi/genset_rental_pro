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
        'view_quote_requests'      => ['View Quote Requests',        'Sales Pipeline', 1],
        'manage_quote_requests'    => ['Manage Quote Requests',       'Sales Pipeline', 2],
        'view_quotations'          => ['View Quotations',             'Sales Pipeline', 3],
        'manage_quotations'        => ['Manage Quotations',           'Sales Pipeline', 4],

        // ── Bookings ─────────────────────────────────────────────────
        'view_bookings'            => ['View Bookings',               'Bookings',        1],
        'manage_bookings'          => ['Manage Bookings',             'Bookings',        2],
        'approve_bookings'         => ['Approve / Reject Bookings',   'Bookings',        3],

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
        'view_invoices'            => ['View Invoices',               'Invoicing',       1],
        'manage_invoices'          => ['Manage Invoices & Payments',  'Invoicing',       2],

        // ── Accounting ────────────────────────────────────────────────
        'view_accounting'          => ['View Accounting Records',     'Accounting',      1],
        'manage_accounting'        => ['Manage Accounting Records',   'Accounting',      2],
        'approve_payments'         => ['Approve Expenses & Payments', 'Accounting',      3],

        // ── Reports ───────────────────────────────────────────────────
        'view_reports'             => ['View Reports & Tax Documents', 'Reports',        1],

        // ── System ────────────────────────────────────────────────────
        'manage_users'             => ['Manage Users',                'System',          1],
        'manage_permissions'       => ['Manage Role Permissions',     'System',          2],
    ];

    /**
     * Default role → permissions matrix.
     */
    private array $rolePermissions = [
        // Super Admin gets permissions dynamically (bypassed in middleware), seeding anyway:
        'super_admin' => '*', // special marker — handled below

        'admin' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations',
            'view_bookings', 'manage_bookings', 'approve_bookings',
            'view_clients', 'manage_clients',
            'view_fleet', 'manage_fleet', 'manage_deliveries', 'manage_maintenance',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_inventory', 'manage_inventory', 'manage_suppliers', 'manage_purchase_orders',
            'view_invoices', 'manage_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_reports',
            'manage_users',
            // manage_permissions intentionally excluded for admin
        ],

        'finance_manager' => [
            'view_quote_requests', 'view_quotations', 'view_bookings',
            'view_clients',
            'view_fleet', 'view_fuel_logs',
            'view_inventory',
            'view_invoices', 'manage_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_reports',
        ],

        'operations_manager' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations',
            'view_bookings', 'manage_bookings', 'approve_bookings',
            'view_clients', 'manage_clients',
            'view_fleet', 'manage_fleet', 'manage_deliveries', 'manage_maintenance',
            'view_fuel_logs', 'manage_fuel_logs',
            'view_inventory', 'manage_inventory', 'manage_suppliers', 'manage_purchase_orders',
            'view_invoices',
            'view_accounting',
            'view_reports',
        ],

        'sales_manager' => [
            'view_quote_requests', 'manage_quote_requests',
            'view_quotations', 'manage_quotations',
            'view_bookings', 'manage_bookings',
            'view_clients', 'manage_clients',
            'view_fleet',
            'view_invoices',
            'view_reports',
        ],

        'dispatcher' => [
            'view_bookings',
            'view_fleet', 'manage_deliveries',
            'view_fuel_logs', 'manage_fuel_logs',
        ],

        'driver' => [
            'view_bookings',
            'view_fleet',
            'view_fuel_logs', 'manage_fuel_logs',
        ],

        'technician' => [
            'view_fleet', 'manage_maintenance',
            'view_inventory',
            'view_fuel_logs',
        ],

        'accountant' => [
            'view_quote_requests', 'view_quotations', 'view_bookings',
            'view_clients',
            'view_inventory',
            'view_invoices', 'manage_invoices',
            'view_accounting', 'manage_accounting', 'approve_payments',
            'view_reports',
        ],

        'staff' => [
            'view_bookings',
            'view_fleet',
            'view_fuel_logs',
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

        // 2. Seed role → permission mappings (skip if already has any assignments)
        foreach ($this->rolePermissions as $role => $perms) {
            // Skip roles that already have custom permissions set in DB
            if (RolePermission::where('role', $role)->exists()) {
                continue;
            }

            $toInsert = ($perms === '*') ? $allPermissionNames : $perms;

            foreach ($toInsert as $perm) {
                RolePermission::create(['role' => $role, 'permission_name' => $perm]);
            }
        }
    }
}
