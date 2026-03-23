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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_type', 20)->default('tax_invoice')->after('invoice_number')
                  ->comment('tax_invoice or proforma');
            $table->unsignedBigInteger('converted_from_id')->nullable()->after('invoice_type')
                  ->comment('If this is a tax invoice converted from a proforma');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'converted_from_id']);
        });
    }
};
