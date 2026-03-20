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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['created', 'approved', 'active', 'returned', 'invoiced', 'paid', 'cancelled', 'rejected'])
                  ->default('created')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['created', 'approved', 'active', 'returned', 'invoiced', 'paid', 'cancelled'])
                  ->default('created')
                  ->change();
        });
    }
};
