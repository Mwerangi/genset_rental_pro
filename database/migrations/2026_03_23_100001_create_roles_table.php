<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();                              // slug: 'super_admin'
            $table->string('label');                                      // display: 'Super Admin'
            $table->string('description')->nullable();
            $table->string('badge_color')->default('bg-gray-100 text-gray-700');
            $table->boolean('is_system')->default(false);                 // system roles cannot be deleted
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
