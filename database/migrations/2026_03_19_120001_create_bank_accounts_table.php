<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "CRDB Bank — Main"
            $table->enum('account_type', ['bank', 'cash', 'mobile_money']);
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete(); // COA link (1xxx)
            $table->string('currency', 10)->default('TZS');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
