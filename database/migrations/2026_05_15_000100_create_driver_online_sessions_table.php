<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverOnlineSessionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('driver_online_sessions')) {
            return;
        }

        Schema::create('driver_online_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();
            $table->string('app_version')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['driver_id', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_online_sessions');
    }
}
