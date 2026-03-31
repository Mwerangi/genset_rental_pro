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
        Schema::table('quotations', function (Blueprint $table) {
            // Drop the incorrect FK that pointed to users.id
            $table->dropForeign(['client_id']);

            // Add the correct FK pointing to clients.id
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            $table->foreign('client_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }
};
