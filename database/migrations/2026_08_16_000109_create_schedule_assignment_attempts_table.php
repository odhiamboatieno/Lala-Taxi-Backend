<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_assignment_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_ride_id');
            $table->unsignedInteger('driver_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('dispatcher_id')->nullable();
            $table->string('dispatch_mode', 191)->default('manual');
            $table->string('status', 191)->default('offered');
            $table->dateTime('offered_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('timed_out_at')->nullable();
            $table->string('rejection_reason', 191)->nullable();
            $table->integer('timeout_seconds')->default('30');
            $table->integer('sequence_order')->default('1');
            $table->longText('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['schedule_ride_id', 'status'], 'schedule_assignment_attempts_schedule_ride_id_status_index');
            $table->index(['driver_id', 'status'], 'schedule_assignment_attempts_driver_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_assignment_attempts');
    }
};
