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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('day_close_time', 5)->default('23:00')->after('contract_terms');
            $table->boolean('day_close_enabled')->default(false)->after('day_close_time');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['day_close_time', 'day_close_enabled']);
        });
    }
};
