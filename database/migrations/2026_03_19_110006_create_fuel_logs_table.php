<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('litres', 10, 2);
            $table->decimal('cost_per_litre', 10, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('run_hours_before', 10, 2)->nullable();   // meter reading before fuelling
            $table->decimal('run_hours_after', 10, 2)->nullable();    // meter reading after fuelling (if read)
            $table->timestamp('fuelled_at');
            $table->string('fuelled_by')->nullable();                 // person who fuelled
            $table->string('location')->nullable();                   // site or yard
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
