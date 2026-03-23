<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');                 // 'super_admin', 'admin', etc.
            $table->string('permission_name');      // matches permissions.name
            $table->timestamps();

            $table->unique(['role', 'permission_name']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
