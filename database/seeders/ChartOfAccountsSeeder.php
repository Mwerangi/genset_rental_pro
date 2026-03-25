<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ── ASSETS ────────────────────────────────────────────────
            ['code' => '1000', 'name' => 'Assets',                        'type' => 'asset',     'sub_type' => null,             'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => null],
            ['code' => '1100', 'name' => 'Current Assets',                'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1000'],
            ['code' => '1110', 'name' => 'Cash — Main Account (CRDB)',     'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1120', 'name' => 'Cash — Secondary Account (NBC)', 'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '1100'],
            ['code' => '1130', 'name' => 'Petty Cash',                     'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1140', 'name' => 'Accounts Receivable',            'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1150', 'name' => 'Inventory Asset',                'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1160', 'name' => 'Staff Advances',                 'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1170', 'name' => 'Prepaid Expenses',               'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '1100'],
            ['code' => '1180', 'name' => 'VAT Input (Recoverable)',         'type' => 'asset',     'sub_type' => 'current_asset',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '1100'],
            ['code' => '1200', 'name' => 'Fixed Assets',                   'type' => 'asset',     'sub_type' => 'fixed_asset',    'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '1000'],
            ['code' => '1210', 'name' => 'Generator Fleet — Cost',         'type' => 'asset',     'sub_type' => 'fixed_asset',    'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '1200'],
            ['code' => '1220', 'name' => 'Vehicles',                       'type' => 'asset',     'sub_type' => 'fixed_asset',    'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '1200'],
            ['code' => '1230', 'name' => 'Accumulated Depreciation',       'type' => 'asset',     'sub_type' => 'fixed_asset',    'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '1200'],

            // ── LIABILITIES ───────────────────────────────────────────
            ['code' => '2000', 'name' => 'Liabilities',                   'type' => 'liability', 'sub_type' => null,                      'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => null],
            ['code' => '2100', 'name' => 'Current Liabilities',           'type' => 'liability', 'sub_type' => 'current_liability',       'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => '2000'],
            ['code' => '2110', 'name' => 'Accounts Payable (Suppliers)',  'type' => 'liability', 'sub_type' => 'current_liability',       'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => '2100'],
            ['code' => '2120', 'name' => 'VAT Payable (TRA)',             'type' => 'liability', 'sub_type' => 'current_liability',       'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => '2100'],
            ['code' => '2130', 'name' => 'WHT Payable (TRA)',             'type' => 'liability', 'sub_type' => 'current_liability',       'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => '2100'],
            ['code' => '2140', 'name' => 'Accrued Expenses',              'type' => 'liability', 'sub_type' => 'current_liability',       'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '2100'],
            ['code' => '2200', 'name' => 'Long-term Liabilities',         'type' => 'liability', 'sub_type' => 'long_term_liability',     'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '2000'],
            ['code' => '2210', 'name' => 'Loans Payable',                 'type' => 'liability', 'sub_type' => 'long_term_liability',     'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '2200'],

            // ── EQUITY ────────────────────────────────────────────────
            ['code' => '3000', 'name' => 'Equity',                        'type' => 'equity',    'sub_type' => null,             'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => null],
            ['code' => '3100', 'name' => "Owner's Capital",               'type' => 'equity',    'sub_type' => 'equity',         'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '3000'],
            ['code' => '3200', 'name' => 'Retained Earnings',             'type' => 'equity',    'sub_type' => 'equity',         'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '3000'],

            // ── REVENUE ───────────────────────────────────────────────
            ['code' => '4000', 'name' => 'Revenue',                       'type' => 'revenue',   'sub_type' => null,             'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => null],
            ['code' => '4100', 'name' => 'Rental Income (Genset)',         'type' => 'revenue',   'sub_type' => 'operating',      'normal_balance' => 'credit', 'is_system' => true,  'parent_code' => '4000'],
            ['code' => '4110', 'name' => 'Delivery Income',               'type' => 'revenue',   'sub_type' => 'operating',      'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '4000'],
            ['code' => '4120', 'name' => 'Other Income',                  'type' => 'revenue',   'sub_type' => 'other',          'normal_balance' => 'credit', 'is_system' => false, 'parent_code' => '4000'],

            // ── EXPENSES ──────────────────────────────────────────────
            ['code' => '5000', 'name' => 'Expenses',                      'type' => 'expense',   'sub_type' => null,             'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => null],
            ['code' => '5100', 'name' => 'Cost of Sales',                 'type' => 'expense',   'sub_type' => 'cost_of_sales',  'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5000'],
            ['code' => '5110', 'name' => 'Fuel Expense',                  'type' => 'expense',   'sub_type' => 'cost_of_sales',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '5100'],
            ['code' => '5120', 'name' => 'Maintenance & Repair Expense',  'type' => 'expense',   'sub_type' => 'cost_of_sales',  'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '5100'],
            ['code' => '5130', 'name' => 'Parts & Consumables (Inventory Used)', 'type' => 'expense', 'sub_type' => 'cost_of_sales', 'normal_balance' => 'debit', 'is_system' => true, 'parent_code' => '5100'],
            ['code' => '5200', 'name' => 'Operating Expenses',            'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5000'],
            ['code' => '5210', 'name' => 'Staff Costs / Salaries',        'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5200'],
            ['code' => '5220', 'name' => 'Rent & Utilities',              'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5200'],
            ['code' => '5230', 'name' => 'Transport & Logistics',         'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5200'],
            ['code' => '5240', 'name' => 'Administration',                'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5200'],
            ['code' => '5250', 'name' => 'Marketing & Advertising',       'type' => 'expense',   'sub_type' => 'operating',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5200'],
            ['code' => '5300', 'name' => 'Financial Expenses',            'type' => 'expense',   'sub_type' => 'financial',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5000'],
            ['code' => '5310', 'name' => 'Bank Charges',                  'type' => 'expense',   'sub_type' => 'financial',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5300'],
            ['code' => '5320', 'name' => 'Bad Debt Write-off',            'type' => 'expense',   'sub_type' => 'financial',      'normal_balance' => 'debit',  'is_system' => true,  'parent_code' => '5300'],
            ['code' => '5330', 'name' => 'Discounts Allowed',             'type' => 'expense',   'sub_type' => 'financial',      'normal_balance' => 'debit',  'is_system' => false, 'parent_code' => '5300'],
        ];

        $codeToId = [];

        foreach ($accounts as $data) {
            $parentCode = $data['parent_code'];
            unset($data['parent_code']);
            $data['parent_id'] = $parentCode ? ($codeToId[$parentCode] ?? null) : null;

            $account = Account::updateOrCreate(['code' => $data['code']], $data);
            $codeToId[$account->code] = $account->id;
        }

        $this->command?->info('Chart of Accounts seeded — ' . count($accounts) . ' accounts.');
    }
}
