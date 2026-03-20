<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('payment_method')->constrained('bank_accounts')->nullOnDelete();
            $table->string('receipt_number', 30)->nullable()->after('bank_account_id'); // RCP-2026-XXXX
            $table->foreignId('journal_entry_id')->nullable()->after('receipt_number')->constrained('journal_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['bank_account_id', 'receipt_number', 'journal_entry_id']);
        });
    }
};
