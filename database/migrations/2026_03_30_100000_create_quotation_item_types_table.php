<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_item_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();        // stored value: 'genset_rental', etc.
            $table->string('label');                     // display name in forms/reports
            $table->boolean('is_rental')->default(false); // shows Duration Days field when true
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the five default types that match the original hardcoded enum
        DB::table('quotation_item_types')->insert([
            [
                'key'        => 'genset_rental',
                'label'      => 'Generator Rental',
                'is_rental'  => true,
                'sort_order' => 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'delivery',
                'label'      => 'Delivery Charges',
                'is_rental'  => false,
                'sort_order' => 2,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'fuel',
                'label'      => 'Fuel Costs',
                'is_rental'  => false,
                'sort_order' => 3,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'maintenance',
                'label'      => 'Maintenance Fee',
                'is_rental'  => false,
                'sort_order' => 4,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'other',
                'label'      => 'Other',
                'is_rental'  => false,
                'sort_order' => 5,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_item_types');
    }
};
