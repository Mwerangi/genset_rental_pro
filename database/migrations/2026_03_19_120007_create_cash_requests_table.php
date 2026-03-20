<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 30)->unique(); // CR-2026-0001
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('purpose');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('actual_amount', 12, 2)->nullable();  // filled after reconciliation
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'retired', 'rejected'])->default('draft');
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();         // JE when disbursed
            $table->foreignId('retire_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete(); // JE when reconciled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_requests');
    }
};
