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
            // Activation (approved → active)
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            $table->timestamp('activated_at')->nullable()->after('activated_by');

            // Return (active → returned)
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete()->after('activated_at');
            $table->timestamp('returned_at')->nullable()->after('returned_by');

            // Invoice (returned → invoiced)
            $table->string('invoice_number')->nullable()->after('returned_at');
            $table->foreignId('invoiced_by')->nullable()->constrained('users')->nullOnDelete()->after('invoice_number');
            $table->timestamp('invoiced_at')->nullable()->after('invoiced_by');

            // Payment (invoiced → paid)
            $table->string('payment_reference')->nullable()->after('invoiced_at');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('paid_by');

            // Cancellation / Rejection
            $table->text('cancellation_reason')->nullable()->after('paid_at');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activated_by');
            $table->dropColumn('activated_at');
            $table->dropConstrainedForeignId('returned_by');
            $table->dropColumn('returned_at');
            $table->dropColumn('invoice_number');
            $table->dropConstrainedForeignId('invoiced_by');
            $table->dropColumn('invoiced_at');
            $table->dropColumn('payment_reference');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn('paid_at');
            $table->dropColumn('cancellation_reason');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('cancelled_at');
        });
    }
};
