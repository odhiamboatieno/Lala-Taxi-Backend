<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_location', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('home');
            $table->text('work');
            $table->string('home_latitude', 100);
            $table->string('home_longitude', 100);
            $table->string('work_latitude', 100);
            $table->string('work_longitude', 100);
            $table->string('latitude', 100);
            $table->string('longitude', 100);
            $table->unique('user_id', 'rider_location_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_location');
    }
};
