<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->timestamp('bank_reconciled_at')->nullable()->after('approved_at')
                  ->comment('Set when a bank statement transaction is reconciled against this expense');
            $table->foreignId('bank_reconciled_by')->nullable()->after('bank_reconciled_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['bank_reconciled_by']);
            $table->dropColumn(['bank_reconciled_at', 'bank_reconciled_by']);
        });
    }
};
