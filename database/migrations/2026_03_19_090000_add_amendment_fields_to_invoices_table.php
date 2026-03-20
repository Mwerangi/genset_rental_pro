<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add discount and new status values to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->string('discount_reason')->nullable()->after('discount_amount');
        });

        // Expand the status enum to include disputed and written_off
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'draft','sent','partially_paid','paid','void','declined','disputed','written_off'
        ) NOT NULL DEFAULT 'draft'");

        // Expand item_type enum to include extra_days, damage, penalty, credit
        DB::statement("ALTER TABLE invoice_items MODIFY COLUMN item_type ENUM(
            'genset_rental','delivery','fuel','maintenance','extra_days','damage','penalty','credit','other'
        ) NOT NULL DEFAULT 'genset_rental'");
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_reason']);
        });

        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'draft','sent','partially_paid','paid','void','declined'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("ALTER TABLE invoice_items MODIFY COLUMN item_type ENUM(
            'genset_rental','delivery','fuel','maintenance','other'
        ) NOT NULL DEFAULT 'genset_rental'");
    }
};
