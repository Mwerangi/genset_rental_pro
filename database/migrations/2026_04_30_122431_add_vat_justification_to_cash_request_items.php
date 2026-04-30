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
        Schema::table('cash_request_items', function (Blueprint $table) {
            $table->string('vat_justification', 500)->nullable()->after('is_zero_rated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_request_items', function (Blueprint $table) {
            $table->dropColumn('vat_justification');
        });
    }
};
