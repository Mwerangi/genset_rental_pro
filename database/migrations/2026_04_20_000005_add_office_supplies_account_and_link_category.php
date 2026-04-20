<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 5260 Office Supplies & Stationery account if it doesn't exist
        $exists = DB::table('accounts')->where('code', '5260')->exists();

        if (!$exists) {
            $parent = DB::table('accounts')->where('code', '5200')->value('id');
            DB::table('accounts')->insert([
                'code'           => '5260',
                'name'           => 'Office Supplies & Stationery',
                'type'           => 'expense',
                'sub_type'       => null,
                'parent_id'      => $parent,
                'normal_balance' => 'debit',
                'balance'        => 0,
                'currency'       => 'TZS',
                'is_active'      => true,
                'is_system'      => false,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // Link expense category "Office Supplies & Stationery" (name match) to account 5260
        $accountId = DB::table('accounts')->where('code', '5260')->value('id');
        if ($accountId) {
            DB::table('expense_categories')
                ->whereNull('account_id')
                ->where('name', 'like', '%Office Supplies%')
                ->update(['account_id' => $accountId]);
        }
    }

    public function down(): void
    {
        // Unlink the category
        $accountId = DB::table('accounts')->where('code', '5260')->value('id');
        if ($accountId) {
            DB::table('expense_categories')
                ->where('account_id', $accountId)
                ->update(['account_id' => null]);
        }

        // Remove the account (only if it has no JE lines)
        $hasLines = DB::table('journal_entry_lines')
            ->whereIn('account_id', DB::table('accounts')->where('code', '5260')->pluck('id'))
            ->exists();

        if (!$hasLines) {
            DB::table('accounts')->where('code', '5260')->delete();
        }
    }
};
