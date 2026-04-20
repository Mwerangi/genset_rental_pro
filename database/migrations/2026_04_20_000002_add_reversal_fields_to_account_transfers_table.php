<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('journal_entry_id');
            $table->unsignedBigInteger('reversed_by')->nullable()->after('reversed_at');
            $table->unsignedBigInteger('reversal_of_transfer_id')->nullable()->after('reversed_by');
        });
    }

    public function down(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->dropColumn(['reversed_at', 'reversed_by', 'reversal_of_transfer_id']);
        });
    }
};
