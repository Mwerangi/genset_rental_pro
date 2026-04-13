<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename delivery/pickup location columns and add destination on bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('delivery_location', 'drop_on_location');
            $table->renameColumn('pickup_location', 'drop_off_location');
            $table->string('destination', 500)->nullable()->after('drop_off_location');
        });

        // 2. Create booking_genset pivot table for multiple gensets per booking
        Schema::create('booking_genset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genset_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['booking_id', 'genset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_genset');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('destination');
            $table->renameColumn('drop_on_location', 'delivery_location');
            $table->renameColumn('drop_off_location', 'pickup_location');
        });
    }
};
