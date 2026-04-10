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
        Schema::table('bank_transactions', function (Blueprint $table) {
            // Expand status enum to include 'reconciled'
            // MySQL requires re-defining the column
            $table->enum('status', ['pending', 'posted', 'ignored', 'reconciled'])
                  ->default('pending')
                  ->change();

            // Polymorphic link to the existing payment that covers this bank line
            $table->string('reconciled_payment_type')->nullable()->after('notes')
                  ->comment('App\\Models\\InvoicePayment or App\\Models\\SupplierPayment');
            $table->unsignedBigInteger('reconciled_payment_id')->nullable()->after('reconciled_payment_type');
            $table->timestamp('reconciled_at')->nullable()->after('reconciled_payment_id');
            $table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciled_at');

            $table->index(['reconciled_payment_type', 'reconciled_payment_id'], 'bt_reconciled_payment_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropIndex('bt_reconciled_payment_idx');
            $table->dropColumn(['reconciled_payment_type', 'reconciled_payment_id', 'reconciled_at', 'reconciled_by']);
            $table->enum('status', ['pending', 'posted', 'ignored'])->default('pending')->change();
        });
    }
};
