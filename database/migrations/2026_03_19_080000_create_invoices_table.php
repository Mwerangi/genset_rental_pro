<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-2026-0001

            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();

            // Status: draft, sent, partially_paid, paid, void, declined
            $table->enum('status', ['draft', 'sent', 'partially_paid', 'paid', 'void', 'declined'])->default('draft');

            // Dates
            $table->date('issue_date');
            $table->date('due_date');

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('is_zero_rated')->default(false); // Zero-rated VAT (0%)
            $table->decimal('vat_rate', 5, 2)->default(18.00);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0); // Updated on each payment
            // balance_due = total_amount - amount_paid (computed)

            // Terms
            $table->string('payment_terms')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('void_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('booking_id');
            $table->index('client_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
