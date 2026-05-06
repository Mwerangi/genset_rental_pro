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
            $table->decimal('vat_amount', 12, 2)->default(0)->after('estimated_amount');
            $table->boolean('is_zero_rated')->default(false)->after('vat_amount');
            $table->decimal('total_amount', 12, 2)->nullable()->after('is_zero_rated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_request_items', function (Blueprint $table) {
            $table->dropColumn(['vat_amount', 'is_zero_rated', 'total_amount']);
        });
    }
};