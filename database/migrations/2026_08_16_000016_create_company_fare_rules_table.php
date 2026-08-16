<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_fare_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('company_vehicle_type_id');
            $table->unsignedBigInteger('company_service_area_id')->nullable();
            $table->decimal('base_fare', 11, 2)->default('0.00');
            $table->unsignedInteger('capacity')->default('1');
            $table->decimal('per_km', 11, 2)->default('0.00');
            $table->decimal('schedule_fare', 11, 2)->default('0.00');
            $table->string('currency_code', 10)->nullable();
            $table->string('apply_peak', 10)->default('No');
            $table->string('apply_night', 10)->default('No');
            $table->decimal('waiting_time', 11, 2)->default('0.00');
            $table->decimal('waiting_charge', 11, 2)->default('0.00');
            $table->decimal('per_minute', 11, 2)->default('0.00');
            $table->decimal('minimum_fare', 11, 2)->default('0.00');
            $table->decimal('waiting_charge_per_minute', 11, 2)->default('0.00');
            $table->decimal('cancellation_fee', 11, 2)->default('0.00');
            $table->string('night_surcharge_type', 191)->nullable();
            $table->decimal('night_surcharge_value', 11, 2)->nullable();
            $table->decimal('airport_fee', 11, 2)->nullable();
            $table->string('commission_type', 191)->nullable();
            $table->decimal('commission_value', 11, 2)->nullable();
            $table->string('status', 191)->default('Active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('company_id', 'sd_cfr_company_fk');
            $table->index('company_vehicle_type_id', 'sd_cfr_cvt_fk');
            $table->index('company_service_area_id', 'sd_cfr_csa_fk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_fare_rules');
    }
};
