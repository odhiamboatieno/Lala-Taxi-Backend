<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_fares', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parcel_category_id');
            $table->unsignedInteger('car_type_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->decimal('base_fare', 10, 2)->default(0);
            $table->decimal('per_km_rate', 10, 2)->default(0);
            $table->decimal('min_fare', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parcel_category_id')->references('id')->on('parcel_categories')->onDelete('cascade');
            $table->foreign('car_type_id')->references('id')->on('car_type')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');

            $table->unique(['parcel_category_id', 'car_type_id', 'location_id'], 'parcel_fare_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_fares');
    }
};
