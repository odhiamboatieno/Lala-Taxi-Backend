<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('company_id')->nullable();
            $table->string('operator_type', 191)->nullable();
            $table->unsignedInteger('operator_id')->nullable();
            $table->unsignedInteger('pool_id');
            $table->string('pickup_latitude', 100);
            $table->string('pickup_longitude', 100);
            $table->string('drop_latitude', 100);
            $table->string('drop_longitude', 100);
            $table->string('pickup_location', 255);
            $table->string('drop_location', 255);
            $table->unsignedInteger('car_id');
            $table->unsignedBigInteger('company_vehicle_type_id')->nullable();
            $table->unsignedInteger('global_vehicle_type_id')->nullable();
            $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
            $table->unsignedInteger('request_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
            $table->text('trip_path');
            $table->text('map_image');
            $table->unsignedTinyInteger('seats');
            $table->decimal('total_time', 7, 2);
            $table->decimal('total_km', 7, 2);
            $table->decimal('time_fare', 11, 2);
            $table->decimal('distance_fare', 11, 2);
            $table->decimal('base_fare', 11, 2);
            $table->unsignedInteger('additional_rider');
            $table->decimal('additional_rider_amount', 11, 2);
            $table->decimal('peak_fare', 11, 2);
            $table->decimal('peak_amount', 11, 2);
            $table->decimal('extra_fare_percentage', 5, 2)->default('0.00');
            $table->decimal('extra_fare_amount', 11, 2)->default('0.00');
            $table->string('extra_fare_reason', 191)->nullable();
            $table->unsignedBigInteger('surge_pricing_id')->nullable();
            $table->string('surge_name', 191)->nullable();
            $table->decimal('surge_percentage', 8, 2)->default('0.00');
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
            $table->decimal('platform_driver_fee_amount', 11, 2)->default('0.00');
            $table->decimal('company_commission_amount', 11, 2)->default('0.00');
            $table->decimal('company_driver_fee_amount', 11, 2)->default('0.00');
            $table->string('company_driver_fee_type', 191)->nullable();
            $table->string('company_driver_fee_label', 191)->nullable();
            $table->decimal('owe_amount', 11, 2);
            $table->decimal('remaining_owe_amount', 11, 2);
            $table->decimal('applied_owe_amount', 11, 2);
            $table->string('to_trip_id', 100);
            $table->timestamp('arrive_time')->default('0000-00-00 00:00:00');
            $table->timestamp('begin_trip')->default('0000-00-00 00:00:00');
            $table->timestamp('end_trip')->default('0000-00-00 00:00:00');
            $table->text('paykey');
            $table->string('payment_mode', 50)->default('Braintree');
            $table->enum('payment_status', ['Pending', 'Completed', 'Trip Cancelled'])->default('Pending');
            $table->tinyInteger('cash_collected')->default('0');
            $table->tinyInteger('driver_confirmed_cash')->default('0');
            $table->timestamp('driver_rating_triggered_at')->nullable();
            $table->timestamp('cash_fees_debited_at')->nullable();
            $table->enum('is_calculation', ['1', '0'])->default('0');
            $table->string('currency_code', 10);
            $table->text('fare_estimation');
            $table->enum('status', ['requested', 'searching_driver', 'driver_assigned', 'driver_arriving', 'arrived', 'started', 'completed', 'cancelled', 'Scheduled', 'Begin trip', 'End trip', 'Payment', 'Rating', 'Null'])->nullable()->default('Null');
            $table->text('parcel_pod_signature')->nullable();
            $table->string('parcel_pod_photo', 255)->nullable();
            $table->timestamp('parcel_delivered_at')->nullable();
            $table->unsignedInteger('parcel_category_id')->nullable();
            $table->unsignedInteger('parcel_task_id')->nullable();
            $table->timestamp('parcel_picked_up_at')->nullable();
            $table->string('service_category', 30)->default('ride');
            $table->string('otp', 10)->default('Null');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->index('user_id', 'trips_user_id_foreign');
            $table->index('car_id', 'trips_car_id_foreign');
            $table->index('request_id', 'trips_request_id_foreign');
            $table->index('driver_id', 'trips_driver_id_foreign');
            $table->index('toll_reason_id', 'trips_toll_reason_id_foreign');
            $table->index('currency_code', 'trips_currency_code_foreign');
            $table->index(['status', 'company_id', 'driver_id', 'user_id'], 'trips_dispatch_lookup_idx');
            $table->index('parcel_category_id', 'trips_parcel_category_id_foreign');
            $table->index('parcel_task_id', 'trips_parcel_task_id_foreign');
            $table->index('status', 'trips_status_idx');
            $table->index('created_at', 'trips_created_at_idx');
            $table->index('company_id', 'trips_company_id_idx');
            $table->index('driver_id', 'trips_driver_id_idx');
            $table->index('user_id', 'trips_user_id_idx');
            $table->index('car_id', 'trips_car_id_idx');
            $table->index('payment_status', 'trips_payment_status_idx');
            $table->index(['operator_type', 'operator_id'], 'trips_operator_lookup_idx');
            $table->index('company_vehicle_type_id', 'trips_company_vehicle_type_idx');
            $table->index('global_vehicle_category_id', 'trips_global_vehicle_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
