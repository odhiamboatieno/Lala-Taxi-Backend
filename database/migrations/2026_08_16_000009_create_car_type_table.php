<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('car_name', 191);
            $table->string('description', 191)->nullable();
            $table->string('vehicle_image', 191);
            $table->string('active_image', 191);
            $table->enum('is_pool', ['Yes', 'No'])->default('No');
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->decimal('price_per_km', 8, 2)->default('15.00');
            $table->index('status', 'car_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_type');
    }
};
