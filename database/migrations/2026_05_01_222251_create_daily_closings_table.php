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
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('closing_date');

            // Balances
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('total_in', 18, 2)->default(0);
            $table->decimal('total_out', 18, 2)->default(0);
            $table->decimal('closing_balance', 18, 2)->default(0);

            // Summary counters
            $table->unsignedInteger('payments_count')->default(0);
            $table->unsignedInteger('expenses_count')->default(0);
            $table->unsignedInteger('cash_requests_count')->default(0);

            // Full transaction snapshot (JSON)
            $table->json('snapshot')->nullable();

            // Who/when
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_auto')->default(true); // true = scheduled, false = manual close
            $table->timestamps();

            $table->unique(['bank_account_id', 'closing_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
