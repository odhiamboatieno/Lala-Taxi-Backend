<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('heading', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('trip_id', 'driver_locations_trip_id_foreign');
            $table->index(['driver_id', 'recorded_at'], 'driver_locations_driver_recorded_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
