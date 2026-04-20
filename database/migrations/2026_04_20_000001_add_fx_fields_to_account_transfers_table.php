<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            // Amount received in the destination account currency (may differ from `amount` on FX transfers)
            $table->decimal('to_amount', 15, 2)->nullable()->after('amount')
                  ->comment('Amount credited to destination account. Equals amount when same currency.');
            // Exchange rate used: from_currency → to_currency (e.g. 1 USD = 2500 TZS → rate = 2500)
            $table->decimal('exchange_rate', 15, 6)->nullable()->after('to_amount')
                  ->comment('Rate applied: 1 unit of source currency = exchange_rate units of destination currency');
            // Store currencies at time of transfer for audit trail
            $table->string('from_currency', 10)->nullable()->after('exchange_rate');
            $table->string('to_currency', 10)->nullable()->after('from_currency');
        });
    }

    public function down(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->dropColumn(['to_amount', 'exchange_rate', 'from_currency', 'to_currency']);
        });
    }
};
