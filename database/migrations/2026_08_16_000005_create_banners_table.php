<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('type', 191)->default('Ride');
            $table->string('title', 191)->nullable();
            $table->string('subtitle', 191)->nullable();
            $table->text('link')->nullable();
            $table->string('image', 191)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
