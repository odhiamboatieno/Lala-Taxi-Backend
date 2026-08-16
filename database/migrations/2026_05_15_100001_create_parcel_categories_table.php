<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->string('icon_path', 255)->nullable();
            $table->decimal('base_fare', 10, 2)->default(0);
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_categories');
    }
};
