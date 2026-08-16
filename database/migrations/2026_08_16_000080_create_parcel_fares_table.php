<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_fares', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parcel_category_id');
            $table->unsignedInteger('car_type_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->decimal('base_fare', 10, 2)->default('0.00');
            $table->decimal('per_km_rate', 10, 2)->default('0.00');
            $table->decimal('min_fare', 10, 2)->default('0.00');
            $table->tinyInteger('is_active')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['parcel_category_id', 'car_type_id', 'location_id'], 'parcel_fare_unique');
            $table->index('car_type_id', 'parcel_fares_car_type_id_foreign');
            $table->index('location_id', 'parcel_fares_location_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_fares');
    }
};
