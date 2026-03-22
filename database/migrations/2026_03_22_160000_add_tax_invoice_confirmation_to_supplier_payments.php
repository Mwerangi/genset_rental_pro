<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->string('tax_invoice_number', 100)->nullable()->after('withholding_tax')
                  ->comment('Supplier tax invoice or EFD receipt number');
            $table->enum('status', ['paid', 'confirmed'])->default('paid')->after('tax_invoice_number');
            $table->string('remittance_path', 500)->nullable()->after('status')
                  ->comment('Path to uploaded remittance proof (bank advice, SWIFT, cheque scan)');
            $table->foreignId('confirmed_by')->nullable()->after('remittance_path')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['tax_invoice_number', 'status', 'remittance_path', 'confirmed_by', 'confirmed_at']);
        });
    }
};
