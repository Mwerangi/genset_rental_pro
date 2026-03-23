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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('null = broadcast to all');
            $table->string('type', 50)->index()->comment('booking, invoice, maintenance, system, etc.');
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('link', 500)->nullable()->comment('URL to navigate to');
            $table->string('icon', 50)->nullable()->comment('icon name hint for frontend');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
