<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleAssignmentAttemptsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('schedule_assignment_attempts')) {
            return;
        }

        Schema::create('schedule_assignment_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_ride_id');
            $table->unsignedInteger('driver_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('dispatcher_id')->nullable();
            $table->string('dispatch_mode')->default('manual');
            $table->string('status')->default('offered');
            $table->dateTime('offered_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('timed_out_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->integer('timeout_seconds')->default(30);
            $table->integer('sequence_order')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['schedule_ride_id', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_assignment_attempts');
    }
}
