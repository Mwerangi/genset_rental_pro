<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change item_type from ENUM to VARCHAR on both quotation_items and invoice_items
 * so that user-defined item types from quotation_item_types table are accepted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('item_type', 100)->default('genset_rental')->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('item_type', 100)->default('genset_rental')->change();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->enum('item_type', ['genset_rental', 'delivery', 'fuel', 'maintenance', 'other'])
                  ->default('genset_rental')->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->enum('item_type', ['genset_rental', 'delivery', 'fuel', 'maintenance', 'other'])
                  ->default('genset_rental')->change();
        });
    }
};
