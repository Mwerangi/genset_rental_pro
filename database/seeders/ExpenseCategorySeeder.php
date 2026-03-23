<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Each category maps to the exact COA account code that gets debited
     * when a cash request item under that category is retired.
     *
     * Mapping:
     *   code 5110 → Fuel Expense          (cost of sales)
     *   code 5120 → Maintenance & Repair   (cost of sales)
     *   code 5130 → Parts & Consumables    (cost of sales — inventory used)
     *   code 5210 → Staff Costs / Salaries (operating)
     *   code 5220 → Rent & Utilities       (operating)
     *   code 5230 → Transport & Logistics  (operating)
     *   code 5240 → Administration         (operating — default fallback)
     *   code 5250 → Marketing & Advertising(operating)
     *   code 5260 → Office & Stationeries  (operating)
     *   code 5310 → Bank Charges & Fees    (financial)
     */
    public function run(): void
    {
        $categories = [
            // ── Operational / Cost of Sales ──────────────────────────────────
            [
                'name'        => 'Fuel Purchase',
                'code'        => '5110',
                'description' => 'Diesel, petrol and lubricant purchases for gensets and vehicles.',
            ],
            [
                'name'        => 'Maintenance & Repairs',
                'code'        => '5120',
                'description' => 'On-site and workshop repair labour, servicing costs.',
            ],
            [
                'name'        => 'Spare Parts & Consumables',
                'code'        => '5130',
                'description' => 'Filters, belts, batteries, oil and other consumable parts.',
            ],

            // ── Operating ─────────────────────────────────────────────────────
            [
                'name'        => 'Staff Allowances',
                'code'        => '5210',
                'description' => 'Per diem, overtime allowances and staff-related cash expenses.',
            ],
            [
                'name'        => 'Rent, Water & Utilities',
                'code'        => '5220',
                'description' => 'Office rent, electricity, water and other utility costs.',
            ],
            [
                'name'        => 'Transport & Logistics',
                'code'        => '5230',
                'description' => 'Driver fuel, vehicle hire, freight and delivery charges.',
            ],
            [
                'name'        => 'Administration',
                'code'        => '5240',
                'description' => 'General administrative costs — licences, government fees, sundry.',
            ],
            [
                'name'        => 'Marketing & Advertising',
                'code'        => '5250',
                'description' => 'Branding materials, promotional costs and client entertainment.',
            ],
            [
                'name'        => 'Office Supplies & Stationery',
                'code'        => '5260',
                'description' => 'Printing, stationery, toner and everyday office consumables.',
            ],

            // ── Financial ─────────────────────────────────────────────────────
            [
                'name'        => 'Bank Charges & Fees',
                'code'        => '5310',
                'description' => 'Bank transfer fees, mobile money fees, service charges.',
            ],
        ];

        foreach ($categories as $data) {
            $account = Account::where('code', $data['code'])->first();

            ExpenseCategory::firstOrCreate(
                ['name' => $data['name']],
                [
                    'account_id'  => $account?->id,
                    'description' => $data['description'],
                    'is_active'   => true,
                ]
            );
        }

        $this->command->info('Seeded ' . count($categories) . ' expense categories.');
    }
}
