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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->unique(); // CL-2026-0001
            $table->string('company_name')->nullable();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 30);
            $table->string('tin_number', 50)->nullable();
            $table->string('vrn', 50)->nullable();
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->unsignedInteger('payment_terms_days')->default(30);
            $table->text('notes')->nullable();
            $table->string('source')->default('quote_request'); // quote_request / manual
            $table->foreignId('quote_request_id')->nullable()->constrained('quote_requests')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
