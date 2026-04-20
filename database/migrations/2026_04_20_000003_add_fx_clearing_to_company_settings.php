<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fx_clearing_account_id to company_settings
        Schema::table('company_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('fx_clearing_account_id')->nullable()->after('default_currency');
        });

        // 2. Insert 1190 FX Clearing account if it doesn't already exist
        $existing = DB::table('accounts')->where('code', '1190')->first();
        if (!$existing) {
            $parentId = DB::table('accounts')->where('code', '1100')->value('id');
            $accountId = DB::table('accounts')->insertGetId([
                'parent_id'   => $parentId,
                'code'        => '1190',
                'name'        => 'FX Clearing',
                'type'        => 'asset',
                'description' => 'Foreign currency exchange clearing account. Used as a bridge for inter-bank FX transfers. Should net to zero over time.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 3. Point the existing company settings row to it
            DB::table('company_settings')->where('id', 1)->update([
                'fx_clearing_account_id' => $accountId,
            ]);
        } else {
            DB::table('company_settings')->where('id', 1)->update([
                'fx_clearing_account_id' => $existing->id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('fx_clearing_account_id');
        });
        DB::table('accounts')->where('code', '1190')->delete();
    }
};
