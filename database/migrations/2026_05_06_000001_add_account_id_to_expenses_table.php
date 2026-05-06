<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add a direct COA account_id FK to expenses.
 *
 * New bulk-entry flow bypasses expense_categories entirely and stores
 * the COA account directly on the expense row.
 * Old records keep expense_category_id; both fields are nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('account_id')
                  ->nullable()
                  ->after('expense_category_id')
                  ->constrained('accounts')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
