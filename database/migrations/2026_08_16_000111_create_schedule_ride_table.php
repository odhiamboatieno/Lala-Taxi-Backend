<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_ride', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id')->nullable();
            $table->string('operator_type', 191)->nullable();
            $table->unsignedInteger('operator_id')->nullable();
            $table->string('schedule_date', 255);
            $table->string('schedule_time', 255);
            $table->string('schedule_end_date', 100);
            $table->string('schedule_end_time', 100);
            $table->string('pickup_latitude', 100);
            $table->string('pickup_longitude', 100);
            $table->string('drop_latitude', 100);
            $table->string('drop_longitude', 100);
            $table->string('pickup_location', 255);
            $table->string('drop_location', 255);
            $table->string('via_location', 191)->nullable();
            $table->decimal('via_latitude', 10, 7)->nullable();
            $table->decimal('via_longitude', 10, 7)->nullable();
            $table->string('booking_for_type', 30)->nullable();
            $table->string('booking_contact_name', 120)->nullable();
            $table->string('booking_contact_phone', 40)->nullable();
            $table->text('stop_points')->nullable();
            $table->text('trip_path');
            $table->unsignedInteger('car_id');
            $table->unsignedBigInteger('company_vehicle_type_id')->nullable();
            $table->unsignedInteger('global_vehicle_type_id')->nullable();
            $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('peak_id');
            $table->enum('booking_type', ['Manual Booking', 'Schedule Booking'])->default('Schedule Booking');
            $table->string('booking_channel', 40)->nullable();
            $table->string('workspace_label', 120)->nullable();
            $table->unsignedInteger('draft_id')->nullable();
            $table->unsignedInteger('template_id')->nullable();
            $table->unsignedInteger('return_parent_id')->nullable();
            $table->longText('booking_meta')->nullable();
            $table->longText('booking_capabilities')->nullable();
            $table->string('booking_mode', 191)->default('scheduled');
            $table->string('dispatch_mode', 191)->default('manual');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('dispatch_at')->nullable();
            $table->string('assignment_status', 191)->default('unassigned');
            $table->integer('assignment_timeout_seconds')->default('30');
            $table->integer('assignment_attempt_count')->default('0');
            $table->unsignedInteger('assigned_driver_id')->nullable();
            $table->dateTime('last_assignment_attempt_at')->nullable();
            $table->text('dispatcher_note')->nullable();
            $table->text('driver_note')->nullable();
            $table->string('priority', 191)->default('normal');
            $table->tinyInteger('is_dispatch_override')->default('0');
            $table->longText('dispatch_policy_snapshot')->nullable();
            $table->tinyInteger('assign_now')->default('0');
            $table->integer('driver_id')->default('0');
            $table->enum('status', ['Pending', 'Completed', 'Cancelled', 'Car Not Found'])->nullable();
            $table->string('timezone', 100);
            $table->string('payment_method', 50);
            $table->string('fare_estimation', 15);
            $table->decimal('extra_fare_percentage', 5, 2)->default('0.00');
            $table->string('extra_fare_reason', 191)->nullable();
            $table->unsignedBigInteger('surge_pricing_id')->nullable();
            $table->string('surge_name', 191)->nullable();
            $table->decimal('surge_percentage', 8, 2)->default('0.00');
            $table->enum('is_wallet', ['Yes', 'No'])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'schedule_ride_user_id_foreign');
            $table->index('company_id', 'schedule_ride_company_id_foreign');
            $table->index('car_id', 'schedule_ride_car_id_foreign');
            $table->index('status', 'schedule_ride_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_ride');
    }
};
