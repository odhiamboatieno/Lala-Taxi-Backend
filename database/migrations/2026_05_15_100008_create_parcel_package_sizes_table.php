<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parcel_package_sizes')) {
            return;
        }

        Schema::create('parcel_package_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('title', 120);
            $table->string('subtitle', 120)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_multiplier', 5, 2)->default(1.00);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_package_sizes');
    }
};
