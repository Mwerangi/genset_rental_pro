<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Currency this account operates in. NULL / 'TZS' = base currency.
            $table->string('currency', 10)->nullable()->default('TZS')->after('normal_balance');
        });

        // Back-fill from linked bank accounts
        DB::statement("
            UPDATE accounts a
            INNER JOIN bank_accounts ba ON ba.account_id = a.id
            SET a.currency = ba.currency
            WHERE ba.currency IS NOT NULL AND ba.currency <> ''
        ");
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
