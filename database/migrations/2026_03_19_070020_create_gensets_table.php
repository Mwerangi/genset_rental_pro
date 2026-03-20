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
        Schema::create('gensets', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('asset_number')->unique();          // e.g. MP-001
            $table->string('serial_number')->nullable();        // manufacturer serial
            $table->string('name');                            // friendly name
            $table->enum('type', ['clip-on', 'underslung', 'open-frame', 'canopy', 'other'])->default('clip-on');
            $table->string('brand')->nullable();               // e.g. Cummins, Perkins
            $table->string('model')->nullable();               // e.g. 20ESX
            $table->unsignedInteger('kva_rating')->nullable(); // power output in KVA
            $table->unsignedInteger('kw_rating')->nullable();  // kilowatts
            $table->string('fuel_type')->default('diesel');    // diesel / petrol / gas

            // Operational
            $table->enum('status', [
                'available',
                'rented',
                'maintenance',
                'repair',
                'retired',
                'reserved',
            ])->default('available');
            $table->unsignedBigInteger('current_booking_id')->nullable(); // denormalised for speed

            // Physical
            $table->string('color')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->string('dimensions')->nullable();          // e.g. "2.5m x 1.2m x 1.8m"
            $table->string('tank_capacity_litres')->nullable();
            $table->unsignedInteger('run_hours')->default(0);  // total hours run

            // Acquisition
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->date('warranty_expiry')->nullable();

            // Location
            $table->string('location')->nullable();            // current depot/site

            // Rates
            $table->decimal('daily_rate', 12, 2)->nullable();
            $table->decimal('weekly_rate', 12, 2)->nullable();
            $table->decimal('monthly_rate', 12, 2)->nullable();

            // Maintenance
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->unsignedInteger('service_interval_hours')->default(250);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gensets');
    }
};
