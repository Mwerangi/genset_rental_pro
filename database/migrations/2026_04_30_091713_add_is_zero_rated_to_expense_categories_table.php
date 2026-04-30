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
        Schema::table('expense_categories', function (Blueprint $table) {
            // When true, all expenses in this category are automatically zero-rated (VAT exempt).
            // Used for fuel purchases and other VAT-exempt categories.
            $table->boolean('is_zero_rated')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('is_zero_rated');
        });
    }
};
