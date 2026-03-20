<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->boolean('is_reversed')->default(false)->after('recorded_by');
            $table->string('reversal_note')->nullable()->after('is_reversed');
            $table->timestamp('reversed_at')->nullable()->after('reversal_note');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn(['is_reversed', 'reversal_note', 'reversed_at']);
        });
    }
};
