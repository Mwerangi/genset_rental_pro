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
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete()->after('internal_notes');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()->after('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['bank_account_id', 'journal_entry_id']);
        });
    }
};
