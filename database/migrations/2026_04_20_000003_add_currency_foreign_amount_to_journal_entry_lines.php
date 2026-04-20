<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            // Original currency of the line (e.g. 'USD'). Null means base currency (TZS).
            $table->string('currency', 10)->nullable()->after('description');
            // Original foreign amount before conversion to base currency.
            // debit/credit always hold the TZS equivalent for reporting.
            $table->decimal('foreign_amount', 15, 2)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropColumn(['currency', 'foreign_amount']);
        });
    }
};
