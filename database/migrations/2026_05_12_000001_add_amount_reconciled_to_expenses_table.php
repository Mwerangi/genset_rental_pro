<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Cumulative amount confirmed by bank statement reconciliations.
            // Allows an expense to be partially reconciled (e.g. advance + final payment)
            // without blocking the second reconciliation.
            $table->decimal('amount_reconciled', 15, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('amount_reconciled');
        });
    }
};
