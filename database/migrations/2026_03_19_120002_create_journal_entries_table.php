<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 30)->unique();   // JE-2026-0001
            $table->date('entry_date');
            $table->string('description');
            $table->string('reference')->nullable();
            $table->string('source_type')->nullable();      // invoice|payment|purchase_order|expense|cash_request|credit_note|manual
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
