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
        'view_quote_requests'      => ['View Quote Requests',                           'Sales Pipeline', 1],
        'review_quote_requests'    => ['Review / Reject Quote Requests',                'Sales Pipeline', 2],
        'view_quotations'          => ['View Quotations',                               'Sales Pipeline', 3],
        'create_quotations'        => ['Create Quotations',                             'Sales Pipeline', 4],
        'edit_quotations'          => ['Edit Quotations',                               'Sales Pipeline', 5],
        'approve_quotations'       => ['Approve / Reject Quotations',                   'Sales Pipeline', 6],
        'view_all_quotations'      => ['View All Quotations (not just own)',             'Sales Pipeline', 7],

        // ── Bookings ─────────────────────────────────────────────────
        'view_bookings'            => ['View Bookings',                                 'Bookings', 1],
        'create_bookings'          => ['Create Bookings',                               'Bookings', 2],
        'edit_bookings'            => ['Edit Bookings',                                 'Bookings', 3],
        'approve_bookings'         => ['Approve / Reject Bookings',                     'Bookings', 4],
        'activate_bookings'        => ['Activate Bookings',                             'Bookings', 5],
        'cancel_bookings'          => ['Cancel Bookings',                               'Bookings', 6],
        'return_bookings'          => ['Mark Bookings as Returned',                     'Bookings', 7],
        'view_all_bookings'        => ['View All Bookings (not just own)',               'Bookings', 8],

        // ── Clients (CRM) ─────────────────────────────────────────────
        'view_clients'             => ['View Clients',                                  'Clients', 1],
        'create_clients'           => ['Create Clients',                                'Clients', 2],
        'edit_clients'             => ['Edit Clients',                                  'Clients', 3],
        'delete_clients'           => ['Delete Clients',                                'Clients', 4],
        'manage_client_contacts'   => ['Manage Client Contacts & Addresses',            'Clients', 5],

        // ── Fleet & Gensets ───────────────────────────────────────────
        'view_fleet'               => ['View Fleet (Gensets)',                          'Fleet & Operations', 1],
        'create_gensets'           => ['Create Gensets',                                'Fleet & Operations', 2],
        'edit_gensets'             => ['Edit Gensets',                                  'Fleet & Operations', 3],
        'delete_gensets'           => ['Delete Gensets',                                'Fleet & Operations', 4],
        'update_genset_status'     => ['Update Genset Status',                          'Fleet & Operations', 5],

        // ── Deliveries ────────────────────────────────────────────────
        'view_deliveries'          => ['View Deliveries',                               'Deliveries', 1],
        'create_deliveries'        => ['Create Deliveries',                             'Deliveries', 2],
        'dispatch_deliveries'      => ['Dispatch Deliveries',                           'Deliveries', 3],
        'complete_deliveries'      => ['Complete / Fail Deliveries',                    'Deliveries', 4],

        // ── Maintenance ───────────────────────────────────────────────
        'view_maintenance'         => ['View Maintenance Records',                      'Maintenance', 1],
        'create_maintenance'       => ['Create Maintenance Records',                    'Maintenance', 2],
        'edit_maintenance'         => ['Edit Maintenance Records',                      'Maintenance', 3],
        'delete_maintenance'       => ['Delete Maintenance Records',                    'Maintenance', 4],
        'start_maintenance'        => ['Start Maintenance Jobs',                        'Maintenance', 5],
        'complete_maintenance'     => ['Complete Maintenance Jobs',                     'Maintenance', 6],
        'cancel_maintenance'       => ['Cancel Maintenance Jobs',                       'Maintenance', 7],

        // ── Fuel Logs ─────────────────────────────────────────────────
        'view_fuel_logs'           => ['View Fuel Logs',                                'Fuel Logs', 1],
        'create_fuel_logs'         => ['Create Fuel Log Entries',                       'Fuel Logs', 2],

        // ── Inventory ────────────────────────────────────────────────
        'view_inventory'           => ['View Inventory',                                'Inventory', 1],
        'create_inventory_items'   => ['Create Inventory Items',                        'Inventory', 2],
        'edit_inventory_items'     => ['Edit Inventory Items',                          'Inventory', 3],
        'delete_inventory_items'   => ['Delete Inventory Items',                        'Inventory', 4],
        'adjust_inventory_stock'   => ['Adjust Inventory Stock',                        'Inventory', 5],
        'manage_inventory_categories' => ['Manage Inventory Categories',               'Inventory', 6],

        // ── Suppliers ─────────────────────────────────────────────────
        'view_suppliers'           => ['View Suppliers',                                'Suppliers', 1],
        'create_suppliers'         => ['Create Suppliers',                              'Suppliers', 2],
        'edit_suppliers'           => ['Edit Suppliers',                                'Suppliers', 3],

        // ── Purchase Orders ───────────────────────────────────────────
        'view_purchase_orders'     => ['View Purchase Orders',                          'Purchase Orders', 1],
        'create_purchase_orders'   => ['Create Purchase Orders',                        'Purchase Orders', 2],
        'send_purchase_orders'     => ['Send Purchase Orders to Supplier',              'Purchase Orders', 3],
        'receive_purchase_orders'  => ['Receive Purchase Orders',                       'Purchase Orders', 4],
        'cancel_purchase_orders'   => ['Cancel Purchase Orders',                        'Purchase Orders', 5],

        // ── Invoicing ─────────────────────────────────────────────────
        'view_invoices'            => ['View Invoices',                                 'Invoicing', 1],
        'create_invoices'          => ['Create Invoices',                               'Invoicing', 2],
        'edit_invoices'            => ['Edit Invoice Items & Discounts',                'Invoicing', 3],
        'send_invoices'            => ['Mark Invoices as Sent',                         'Invoicing', 4],
        'record_invoice_payment'   => ['Record & Reverse Invoice Payments',             'Invoicing', 5],
        'void_invoices'            => ['Void Invoices',                                 'Invoicing', 6],
        'write_off_invoices'       => ['Write Off / Dispute Invoices',                  'Invoicing', 7],
        'view_all_invoices'        => ['View All Invoices (not just own)',               'Invoicing', 8],

        // ── Accounting — Setup ────────────────────────────────────────
        'view_accounting'          => ['View Chart of Accounts & Bank Accounts',        'Accounting', 1],
        'manage_accounts'          => ['Manage Chart of Accounts',                      'Accounting', 2],
        'manage_bank_accounts'     => ['Manage Bank Accounts & Transfers',              'Accounting', 3],
        'manage_expense_categories' => ['Manage Expense Categories',                   'Accounting', 4],

        // ── Journal Entries ───────────────────────────────────────────
        'view_journal_entries'          => ['View Journal Entries',                     'Accounting', 5],
        'create_journal_entries'        => ['Create Journal Entries',                   'Accounting', 6],
        'edit_journal_entries'          => ['Edit Draft Journal Entries',               'Accounting', 7],
        'delete_journal_entries'        => ['Delete Draft Journal Entries',             'Accounting', 8],
        'force_delete_journal_entries'  => ['Force-Delete Posted Journal Entries',      'Accounting', 9],
        'post_journal_entries'          => ['Post Journal Entries to Ledger',           'Accounting', 10],
        'reverse_journal_entries'       => ['Reverse Journal Entries',                  'Accounting', 11],

        // ── Expenses ──────────────────────────────────────────────────
        'view_expenses'            => ['View Own Expenses',                             'Accounting', 9],
        'create_expenses'          => ['Create Expenses',                               'Accounting', 10],
        'delete_expenses'          => ['Delete Draft Expenses',                         'Accounting', 11],
        'approve_expenses'         => ['Approve & Post Expenses',                       'Accounting', 12],
        'view_all_expenses'        => ['View All Expenses (not just own)',               'Accounting', 13],

        // ── Supplier Payments ─────────────────────────────────────────
        'view_supplier_payments'   => ['View Own Supplier Payments',                    'Accounting', 14],
        'create_supplier_payments' => ['Create Supplier Payments',                      'Accounting', 15],
        'confirm_supplier_payments' => ['Confirm Supplier Payments',                    'Accounting', 16],
        'view_all_supplier_payments' => ['View All Supplier Payments (not just own)',   'Accounting', 17],

        // ── Cash Requests ─────────────────────────────────────────────
        'view_cash_requests'       => ['View Own Cash Requests',                        'Accounting', 18],
        'create_cash_requests'     => ['Create Cash Requests',                          'Accounting', 19],
        'submit_cash_requests'     => ['Submit Cash Requests for Approval',             'Accounting', 20],
        'approve_cash_requests'    => ['Approve Cash Requests',                         'Accounting', 21],
        'reject_cash_requests'     => ['Reject Cash Requests',                          'Accounting', 22],
        'pay_cash_requests'        => ['Pay Cash Requests',                             'Accounting', 23],
        'view_all_cash_requests'   => ['View All Cash Requests (not just own)',          'Accounting', 24],

        // ── Credit Notes ──────────────────────────────────────────────
        'view_credit_notes'        => ['View Own Credit Notes',                         'Accounting', 25],
        'create_credit_notes'      => ['Create Credit Notes',                           'Accounting', 26],
        'issue_credit_notes'       => ['Issue Credit Notes',                            'Accounting', 27],
        'void_credit_notes'        => ['Void Credit Notes',                             'Accounting', 28],
        'view_all_credit_notes'    => ['View All Credit Notes (not just own)',           'Accounting', 29],

        // ── Reports ───────────────────────────────────────────────────
        'view_sales_reports'       => ['View Sales Reports (Quotations / Pipeline / Revenue)', 'Reports', 1],
        'view_fleet_reports'       => ['View Fleet Reports (Utilisation / Fuel / Maintenance)', 'Reports', 2],
        'view_financial_reports'   => ['View Financial Reports (P&L / Balance Sheet / Ledger / Tax)', 'Reports', 3],
        'view_expense_reports'     => ['View Expense Reports (By Category / Period / Petty Cash)', 'Reports', 4],
        'view_inventory_reports'   => ['View Inventory Reports (Stock / Movements / Procurement)', 'Reports', 5],
        'view_executive_reports'   => ['View Executive Summary Report',                 'Reports', 6],

        // ── System — Users ────────────────────────────────────────────
        'view_users'               => ['View Users',                                    'System', 1],
        'create_users'             => ['Create Users',                                  'System', 2],
        'edit_users'               => ['Edit Users',                                    'System', 3],
        'reset_user_password'      => ['Reset User Passwords',                          'System', 4],
        'toggle_user_status'       => ['Activate / Deactivate Users',                   'System', 5],
        'unlock_users'             => ['Unlock User Accounts',                          'System', 6],

        // ── System ────────────────────────────────────────────────────
        'manage_permissions'         => ['Manage Role Permissions',                     'System', 7],
        'view_audit_trail'           => ['View Audit Trail',                            'System', 8],
        'manage_company_settings'    => ['Manage Company Settings',                     'System', 9],
    ];

    /**
     * Default role → permissions matrix.
     */
    private array $rolePermissions = [
        // Super Admin gets permissions dynamically (bypassed in middleware), seeding anyway:
        'super_admin' => '*', // special marker — handled below

        'admin' => [
            // Sales Pipeline
            'view_quote_requests', 'review_quote_requests',
            'view_quotations', 'create_quotations', 'edit_quotations', 'approve_quotations', 'view_all_quotations',
            // Bookings
            'view_bookings', 'create_bookings', 'edit_bookings', 'approve_bookings',
            'activate_bookings', 'cancel_bookings', 'return_bookings', 'view_all_bookings',
            // Clients
            'view_clients', 'create_clients', 'edit_clients', 'delete_clients', 'manage_client_contacts',
            // Fleet
            'view_fleet', 'create_gensets', 'edit_gensets', 'delete_gensets', 'update_genset_status',
            // Deliveries
            'view_deliveries', 'create_deliveries', 'dispatch_deliveries', 'complete_deliveries',
            // Maintenance
            'view_maintenance', 'create_maintenance', 'edit_maintenance', 'delete_maintenance',
            'start_maintenance', 'complete_maintenance', 'cancel_maintenance',
            // Fuel Logs
            'view_fuel_logs', 'create_fuel_logs',
            // Inventory
            'view_inventory', 'create_inventory_items', 'edit_inventory_items', 'delete_inventory_items',
            'adjust_inventory_stock', 'manage_inventory_categories',
            // Suppliers
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            // Purchase Orders
            'view_purchase_orders', 'create_purchase_orders', 'send_purchase_orders',
            'receive_purchase_orders', 'cancel_purchase_orders',
            // Invoicing
            'view_invoices', 'create_invoices', 'edit_invoices', 'send_invoices',
            'record_invoice_payment', 'void_invoices', 'write_off_invoices', 'view_all_invoices',
            // Accounting
            'view_accounting', 'manage_accounts', 'manage_bank_accounts', 'manage_expense_categories',
            'view_journal_entries', 'create_journal_entries', 'edit_journal_entries', 'delete_journal_entries', 'force_delete_journal_entries',
            'post_journal_entries', 'reverse_journal_entries',
            // Expenses
            'view_expenses', 'create_expenses', 'delete_expenses', 'approve_expenses', 'view_all_expenses',
            // Supplier Payments
            'view_supplier_payments', 'create_supplier_payments', 'confirm_supplier_payments', 'view_all_supplier_payments',
            // Cash Requests
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
            'approve_cash_requests', 'reject_cash_requests', 'pay_cash_requests', 'view_all_cash_requests',
            // Credit Notes
            'view_credit_notes', 'create_credit_notes', 'issue_credit_notes', 'void_credit_notes', 'view_all_credit_notes',
            // Reports
            'view_sales_reports', 'view_fleet_reports', 'view_financial_reports',
            'view_expense_reports', 'view_inventory_reports', 'view_executive_reports',
            // System
            'view_users', 'create_users', 'edit_users', 'reset_user_password', 'toggle_user_status', 'unlock_users',
            'view_audit_trail',
            'manage_company_settings',
            // manage_permissions intentionally excluded — super_admin only
        ],

        'finance_manager' => [
            // Sales Pipeline (read-only)
            'view_quote_requests', 'view_quotations', 'view_all_quotations',
            // Bookings (read-only)
            'view_bookings', 'view_all_bookings',
            // Clients (read-only)
            'view_clients',
            // Fleet (read-only)
            'view_fleet', 'view_deliveries', 'view_maintenance', 'view_fuel_logs',
            // Inventory / Suppliers / POs (read-only)
            'view_inventory', 'view_suppliers', 'view_purchase_orders',
            // Invoicing (full)
            'view_invoices', 'create_invoices', 'edit_invoices', 'send_invoices',
            'record_invoice_payment', 'void_invoices', 'write_off_invoices', 'view_all_invoices',
            // Accounting (full setup)
            'view_accounting', 'manage_accounts', 'manage_bank_accounts', 'manage_expense_categories',
            'view_journal_entries', 'create_journal_entries', 'edit_journal_entries', 'delete_journal_entries', 'force_delete_journal_entries',
            'post_journal_entries', 'reverse_journal_entries',
            // Expenses (full)
            'view_expenses', 'create_expenses', 'delete_expenses', 'approve_expenses', 'view_all_expenses',
            // Supplier Payments (full)
            'view_supplier_payments', 'create_supplier_payments', 'confirm_supplier_payments', 'view_all_supplier_payments',
            // Cash Requests (full)
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
            'approve_cash_requests', 'reject_cash_requests', 'pay_cash_requests', 'view_all_cash_requests',
            // Credit Notes (full)
            'view_credit_notes', 'create_credit_notes', 'issue_credit_notes', 'void_credit_notes', 'view_all_credit_notes',
            // Reports (all)
            'view_sales_reports', 'view_fleet_reports', 'view_financial_reports',
            'view_expense_reports', 'view_inventory_reports', 'view_executive_reports',
            // System
            'view_users',
        ],

        'operations_manager' => [
            // Sales Pipeline
            'view_quote_requests', 'review_quote_requests',
            'view_quotations', 'create_quotations', 'edit_quotations', 'approve_quotations', 'view_all_quotations',
            // Bookings
            'view_bookings', 'create_bookings', 'edit_bookings', 'approve_bookings',
            'activate_bookings', 'cancel_bookings', 'return_bookings', 'view_all_bookings',
            // Clients
            'view_clients', 'create_clients', 'edit_clients', 'manage_client_contacts',
            // Fleet
            'view_fleet', 'create_gensets', 'edit_gensets', 'delete_gensets', 'update_genset_status',
            // Deliveries
            'view_deliveries', 'create_deliveries', 'dispatch_deliveries', 'complete_deliveries',
            // Maintenance (full)
            'view_maintenance', 'create_maintenance', 'edit_maintenance', 'delete_maintenance',
            'start_maintenance', 'complete_maintenance', 'cancel_maintenance',
            // Fuel Logs
            'view_fuel_logs', 'create_fuel_logs',
            // Inventory
            'view_inventory', 'create_inventory_items', 'edit_inventory_items',
            'adjust_inventory_stock', 'manage_inventory_categories',
            // Suppliers
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            // Purchase Orders
            'view_purchase_orders', 'create_purchase_orders', 'send_purchase_orders',
            'receive_purchase_orders', 'cancel_purchase_orders',
            // Invoicing (read-only)
            'view_invoices', 'view_all_invoices',
            // Accounting (read-only)
            'view_accounting',
            // Expenses
            'view_expenses', 'create_expenses', 'delete_expenses', 'view_all_expenses',
            // Supplier Payments (read-only)
            'view_supplier_payments', 'view_all_supplier_payments',
            // Cash Requests
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests', 'view_all_cash_requests',
            // Credit Notes (read-only)
            'view_credit_notes', 'view_all_credit_notes',
            // Reports
            'view_fleet_reports', 'view_inventory_reports',
        ],

        'sales_manager' => [
            // Sales Pipeline (full)
            'view_quote_requests', 'review_quote_requests',
            'view_quotations', 'create_quotations', 'edit_quotations', 'approve_quotations', 'view_all_quotations',
            // Bookings
            'view_bookings', 'create_bookings', 'edit_bookings', 'view_all_bookings',
            // Clients
            'view_clients', 'create_clients', 'edit_clients', 'manage_client_contacts',
            // Fleet (read-only)
            'view_fleet',
            // Invoicing (read-only)
            'view_invoices', 'view_all_invoices',
            // Cash Requests
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
            // Reports
            'view_sales_reports',
        ],

        'dispatcher' => [
            'view_bookings',
            'view_fleet',
            'view_deliveries', 'create_deliveries', 'dispatch_deliveries', 'complete_deliveries',
            'view_fuel_logs', 'create_fuel_logs',
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
        ],

        'driver' => [
            'view_bookings',
            'view_fleet',
            'view_deliveries',
            'view_fuel_logs', 'create_fuel_logs',
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
        ],

        'technician' => [
            'view_fleet',
            'view_maintenance', 'create_maintenance', 'edit_maintenance', 'start_maintenance', 'complete_maintenance',
            'view_inventory',
            'view_fuel_logs',
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
        ],

        'accountant' => [
            // Sales Pipeline (read-only)
            'view_quote_requests', 'view_quotations', 'view_all_quotations',
            // Bookings (read-only)
            'view_bookings', 'view_all_bookings',
            // Clients (read-only)
            'view_clients',
            // Deliveries / Fuel / Inventory read-only
            'view_deliveries', 'view_fuel_logs', 'view_inventory', 'view_suppliers', 'view_purchase_orders',
            // Invoicing (full except write-off)
            'view_invoices', 'create_invoices', 'edit_invoices', 'send_invoices',
            'record_invoice_payment', 'void_invoices', 'view_all_invoices',
            // Accounting (full setup)
            'view_accounting', 'manage_accounts', 'manage_bank_accounts', 'manage_expense_categories',
            'view_journal_entries', 'create_journal_entries', 'edit_journal_entries', 'delete_journal_entries',
            'post_journal_entries',
            // Expenses
            'view_expenses', 'create_expenses', 'delete_expenses', 'approve_expenses', 'view_all_expenses',
            // Supplier Payments
            'view_supplier_payments', 'create_supplier_payments', 'confirm_supplier_payments', 'view_all_supplier_payments',
            // Cash Requests (full)
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
            'approve_cash_requests', 'reject_cash_requests', 'pay_cash_requests', 'view_all_cash_requests',
            // Credit Notes
            'view_credit_notes', 'create_credit_notes', 'issue_credit_notes', 'view_all_credit_notes',
            // Reports
            'view_sales_reports', 'view_financial_reports', 'view_expense_reports', 'view_inventory_reports',
        ],

        'staff' => [
            'view_bookings',
            'view_fleet',
            'view_fuel_logs',
            'view_expenses', 'create_expenses', 'delete_expenses',
            'view_cash_requests', 'create_cash_requests', 'submit_cash_requests',
        ],
    ];

    public function run(): void
    {
        // 1. Upsert permissions catalogue (update label/module/sort if name already exists)
        foreach ($this->permissions as $name => [$label, $module, $sortOrder]) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $label, 'module' => $module, 'sort_order' => $sortOrder]
            );
        }

        // 2. Remove permissions no longer in catalogue + their orphaned role assignments
        $validNames = array_keys($this->permissions);
        RolePermission::whereNotIn('permission_name', $validNames)->delete();
        Permission::whereNotIn('name', $validNames)->delete();

        // 3. Rebuild role → permission assignments from matrix (fresh slate per role)
        foreach ($this->rolePermissions as $role => $perms) {
            $toInsert = ($perms === '*') ? $validNames : $perms;

            // Remove all existing assignments for this role then re-insert
            RolePermission::where('role', $role)->delete();
            foreach ($toInsert as $perm) {
                RolePermission::create([
                    'role'            => $role,
                    'permission_name' => $perm,
                ]);
            }
        }
    }
}
