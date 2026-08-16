<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rider_id');
            $table->unsignedInteger('driver_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['rider_id', 'driver_id'], 'favorite_drivers_rider_id_driver_id_unique');
            $table->index('driver_id', 'favorite_drivers_driver_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_drivers');
    }
};
