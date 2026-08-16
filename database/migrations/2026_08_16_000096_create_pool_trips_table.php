<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_trips', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('seats')->default('1');
            $table->string('pickup_latitude', 100);
            $table->string('pickup_longitude', 100);
            $table->string('drop_latitude', 100);
            $table->string('drop_longitude', 100);
            $table->string('pickup_location', 255);
            $table->string('drop_location', 255);
            $table->text('trip_path');
            $table->text('map_image');
            $table->decimal('total_time', 7, 2);
            $table->decimal('total_km', 7, 2);
            $table->decimal('time_fare', 11, 2);
            $table->decimal('distance_fare', 11, 2);
            $table->decimal('base_fare', 11, 2);
            $table->decimal('additional_rider_amount', 11, 2);
            $table->decimal('peak_fare', 11, 2);
            $table->decimal('peak_amount', 11, 2);
            $table->decimal('driver_peak_amount', 11, 2);
            $table->decimal('schedule_fare', 11, 2);
            $table->decimal('access_fee', 11, 2);
            $table->decimal('tips', 11, 2)->default('0.00');
            $table->decimal('waiting_charge', 11, 2)->default('0.00');
            $table->unsignedInteger('toll_reason_id')->nullable();
            $table->decimal('toll_fee', 11, 2)->default('0.00');
            $table->decimal('wallet_amount', 11, 2);
            $table->decimal('promo_amount', 11, 2);
            $table->decimal('subtotal_fare', 11, 2);
            $table->decimal('total_fare', 11, 2);
            $table->decimal('driver_payout', 11, 2);
            $table->decimal('driver_or_company_commission', 11, 2);
            $table->decimal('owe_amount', 11, 2);
            $table->decimal('remaining_owe_amount', 11, 2);
            $table->decimal('applied_owe_amount', 11, 2);
            $table->timestamp('arrive_time')->default('0000-00-00 00:00:00');
            $table->timestamp('begin_trip')->default('0000-00-00 00:00:00');
            $table->timestamp('end_trip')->default('0000-00-00 00:00:00');
            $table->string('currency_code', 10);
            $table->string('status', 12);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('driver_id', 'pool_trips_driver_id_foreign');
            $table->index('toll_reason_id', 'pool_trips_toll_reason_id_foreign');
            $table->index('currency_code', 'pool_trips_currency_code_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_trips');
    }
};
