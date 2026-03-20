<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 12, 3);         // always positive; type determines direction
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->foreignId('maintenance_record_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('purchase_order_id')->nullable(); // FK added after purchase_orders table exists
            $table->string('reference')->nullable();     // free-text reference if no FK
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
