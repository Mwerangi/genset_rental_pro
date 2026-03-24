<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_number')->nullable()->unique()->after('id');
            $table->string('category')->nullable()->after('name');
            $table->string('phone_alt', 50)->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('country')->nullable()->after('city');
            $table->string('tin_number', 100)->nullable()->after('country');
            $table->string('vrn_number', 100)->nullable()->after('tin_number');
            $table->string('payment_terms')->nullable()->after('vrn_number');
            $table->string('currency', 3)->default('TZS')->after('payment_terms');
            $table->string('bank_name')->nullable()->after('currency');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
        });

        // Generate supplier numbers for existing records
        \DB::table('suppliers')->orderBy('id')->each(function ($sup) {
            $n = str_pad($sup->id, 4, '0', STR_PAD_LEFT);
            \DB::table('suppliers')->where('id', $sup->id)
                ->update(['supplier_number' => 'SUP-' . date('Y', strtotime($sup->created_at)) . '-' . $n]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_number', 'category', 'phone_alt', 'website',
                'country', 'tin_number', 'vrn_number', 'payment_terms',
                'currency', 'bank_name', 'bank_account_name', 'bank_account_number',
            ]);
        });
    }
};
