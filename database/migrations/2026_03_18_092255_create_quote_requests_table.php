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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // QR-2026-0001
            
            // Customer Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('company_name')->nullable();
            
            // Rental Requirements
            $table->enum('genset_type', ['clip-on', 'underslung', 'not_sure']);
            $table->date('rental_start_date');
            $table->integer('rental_duration_days');
            
            // Location Details
            $table->text('delivery_location');
            $table->text('pickup_location')->nullable();
            
            // Additional Information
            $table->text('additional_requirements')->nullable();
            
            // Status & Tracking
            $table->enum('status', ['new', 'reviewed', 'quoted', 'converted', 'rejected'])->default('new');
            $table->string('source')->default('website'); // website, phone, email, walk-in
            
            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Review Tracking
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
