<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id')->nullable();
            $table->string('operator_type', 191)->nullable();
            $table->unsignedInteger('operator_id')->nullable();
            $table->unsignedInteger('seats')->default('1');
            $table->string('pickup_latitude', 100);
            $table->string('pickup_longitude', 100);
            $table->string('drop_latitude', 100);
            $table->string('drop_longitude', 100);
            $table->string('pickup_location', 255);
            $table->string('drop_location', 255);
            $table->string('booking_for_type', 30)->nullable();
            $table->string('booking_contact_name', 120)->nullable();
            $table->string('booking_contact_phone', 40)->nullable();
            $table->text('stop_points')->nullable();
            $table->unsignedInteger('car_id');
            $table->unsignedBigInteger('company_vehicle_type_id')->nullable();
            $table->unsignedInteger('global_vehicle_type_id')->nullable();
            $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->unsignedInteger('driver_id');
            $table->string('payment_mode', 50)->default('Credit Card');
            $table->string('schedule_id', 100)->default('Null');
            $table->unsignedInteger('location_id');
            $table->enum('additional_fare', ['Peak']);
            $table->string('peak_fare', 100);
            $table->decimal('extra_fare_percentage', 5, 2)->default('0.00');
            $table->string('extra_fare_reason', 191)->nullable();
            $table->unsignedBigInteger('surge_pricing_id')->nullable();
            $table->string('surge_name', 191)->nullable();
            $table->decimal('surge_percentage', 8, 2)->default('0.00');
            $table->decimal('estimated_fare', 5, 2)->default('0.00');
            $table->string('distance_estimate', 191)->nullable();
            $table->string('additional_rider', 10);
            $table->string('timezone', 100);
            $table->text('trip_path');
            $table->string('status', 100)->default('Null');
            $table->string('service_category', 30)->default('ride');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('user_id', 'request_user_id_foreign');
            $table->index('car_id', 'request_car_id_foreign');
            $table->index('driver_id', 'request_driver_id_foreign');
            $table->index('status', 'request_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request');
    }
};
