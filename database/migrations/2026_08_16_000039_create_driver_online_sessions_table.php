<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_online_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id');
            $table->timestamp('started_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('total_seconds')->default('0');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();
            $table->string('app_version', 191)->nullable();
            $table->string('device_id', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['driver_id', 'status'], 'driver_online_sessions_driver_id_status_index');
            $table->index(['driver_id', 'started_at'], 'driver_online_sessions_driver_id_started_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_online_sessions');
    }
};
