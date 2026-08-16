<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peak_fare_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fare_id');
            $table->enum('type', ['Peak', 'Night']);
            $table->tinyInteger('day')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 10, 2);
            $table->index('fare_id', 'peak_fare_details_fare_id_foreign');
            $table->index(['day', 'start_time', 'end_time'], 'peak_fare_details_day_start_time_end_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peak_fare_details');
    }
};
