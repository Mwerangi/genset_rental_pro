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
        Schema::table('inventory_categories', function (Blueprint $table) {
            // Links this category to a COA account for PO receipt routing.
            // E.g. Fuel category → 5110, Parts category → 1150 (Inventory Asset)
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
