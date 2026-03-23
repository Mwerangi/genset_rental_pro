<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quotations
        Schema::table('quotations', function (Blueprint $table) {
            $table->char('currency', 3)->default('TZS')->after('vat_rate');
            $table->decimal('exchange_rate_to_tzs', 12, 4)->default(1.0000)->after('currency')
                  ->comment('Rate at time of creation: 1 unit of currency = X TZS');
        });

        // Bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->char('currency', 3)->default('TZS')->after('total_amount');
            $table->decimal('exchange_rate_to_tzs', 12, 4)->default(1.0000)->after('currency');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->char('currency', 3)->default('TZS')->after('vat_rate');
            $table->decimal('exchange_rate_to_tzs', 12, 4)->default(1.0000)->after('currency')
                  ->comment('Rate locked at invoice creation');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_to_tzs']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_to_tzs']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate_to_tzs']);
        });
    }
};
