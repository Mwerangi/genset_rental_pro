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
        Schema::table('cash_requests', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete()->after('requested_by');
            $table->decimal('amount', 12, 2)->nullable()->after('total_amount');        // net (ex-VAT)
            $table->decimal('vat_amount', 12, 2)->default(0)->after('amount');          // total VAT
            $table->boolean('is_zero_rated')->default(false)->after('vat_amount');
            $table->date('expense_date')->nullable()->after('is_zero_rated');
            $table->string('attachment')->nullable()->after('expense_date');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete()->after('retire_journal_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_requests', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropForeign(['expense_id']);
            $table->dropColumn(['expense_category_id', 'amount', 'vat_amount', 'is_zero_rated', 'expense_date', 'attachment', 'expense_id']);
        });
    }
};
