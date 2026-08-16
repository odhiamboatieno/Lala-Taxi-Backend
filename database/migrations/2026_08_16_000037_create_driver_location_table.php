<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_location', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('latitude', 100);
            $table->string('longitude', 100);
            $table->decimal('heading', 8, 2)->nullable();
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('pool_trip_id')->nullable();
            $table->enum('status', ['Online', 'Offline', 'Trip', 'Pool Trip'])->default('Offline');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'driver_location_user_id_foreign');
            $table->index('car_id', 'driver_location_car_id_foreign');
            $table->index(['status', 'latitude', 'longitude'], 'driver_location_status_latitude_longitude_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_location');
    }
};
