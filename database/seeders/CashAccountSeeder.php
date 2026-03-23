<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class CashAccountSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Ensure COA entry for Office Cash in Hand exists ──────────
        $currentAssets = Account::where('code', '1100')->first();

        $officeCashCoa = Account::firstOrCreate(
            ['code' => '1115'],
            [
                'name'           => 'Cash in Hand (Office)',
                'type'           => 'asset',
                'sub_type'       => 'current_asset',
                'normal_balance' => 'debit',
                'is_system'      => false,
                'parent_id'      => $currentAssets?->id,
            ]
        );

        $pettyCashCoa = Account::where('code', '1130')->first();

        // ── 2. Office Cash (physical cash in the office) ─────────────────
        BankAccount::firstOrCreate(
            ['name' => 'Office Cash'],
            [
                'account_type'    => 'cash',
                'bank_name'       => null,
                'account_number'  => null,
                'account_id'      => $officeCashCoa->id,
                'currency'        => 'TZS',
                'current_balance' => 0,
                'is_active'       => true,
                'notes'           => 'Physical cash kept in the office safe / drawer.',
            ]
        );

        // ── 3. Petty Cash ────────────────────────────────────────────────
        BankAccount::firstOrCreate(
            ['name' => 'Petty Cash'],
            [
                'account_type'    => 'cash',
                'bank_name'       => null,
                'account_number'  => null,
                'account_id'      => $pettyCashCoa?->id,
                'currency'        => 'TZS',
                'current_balance' => 0,
                'is_active'       => true,
                'notes'           => 'Petty cash fund for small day-to-day purchases.',
            ]
        );

        $this->command?->info('CashAccountSeeder: Office Cash and Petty Cash seeded successfully.');
    }
}
