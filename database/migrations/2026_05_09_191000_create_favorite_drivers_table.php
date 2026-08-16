<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoriteDriversTable extends Migration
{
    public function up()
    {
        Schema::create('favorite_drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rider_id');
            $table->unsignedInteger('driver_id');
            $table->timestamps();

            $table->foreign('rider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['rider_id', 'driver_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorite_drivers');
    }
}
