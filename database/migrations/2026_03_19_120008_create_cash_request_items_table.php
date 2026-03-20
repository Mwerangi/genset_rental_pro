<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_request_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('estimated_amount', 12, 2);
            $table->decimal('actual_amount', 12, 2)->nullable();
            $table->string('receipt_ref')->nullable();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_request_items');
    }
};
