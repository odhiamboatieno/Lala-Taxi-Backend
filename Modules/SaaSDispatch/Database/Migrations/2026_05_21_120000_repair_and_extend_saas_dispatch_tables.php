<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_fare_rules')) {
            Schema::create('company_fare_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('company_vehicle_type_id');
                $table->unsignedBigInteger('company_service_area_id')->nullable();
                $table->decimal('base_fare', 11, 2)->default(0);
                $table->decimal('per_km', 11, 2)->default(0);
                $table->decimal('per_minute', 11, 2)->default(0);
                $table->decimal('minimum_fare', 11, 2)->default(0);
                $table->decimal('waiting_charge_per_minute', 11, 2)->default(0);
                $table->decimal('cancellation_fee', 11, 2)->default(0);
                $table->string('night_surcharge_type')->nullable();
                $table->decimal('night_surcharge_value', 11, 2)->nullable();
                $table->decimal('airport_fee', 11, 2)->nullable();
                $table->string('commission_type')->nullable();
                $table->decimal('commission_value', 11, 2)->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_fee_settings')) {
            Schema::create('company_fee_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('fee_name');
                $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
                $table->decimal('fee_value', 11, 2)->default(0);
                $table->enum('applies_to', ['rider', 'driver', 'company', 'trip'])->default('trip');
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_dispatch_settings')) {
            Schema::create('company_dispatch_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('dispatch_strategy')->nullable();
                $table->decimal('driver_search_radius', 8, 2)->default(5);
                $table->decimal('max_driver_search_radius', 8, 2)->default(12);
                $table->unsignedInteger('request_timeout_seconds')->default(30);
                $table->unsignedInteger('retry_rounds')->default(3);
                $table->boolean('allow_auto_dispatch')->default(true);
                $table->boolean('allow_manual_dispatch')->default(true);
                $table->boolean('allow_scheduled_trips')->default(true);
                $table->boolean('allow_airport_trips')->default(true);
                $table->boolean('allow_out_of_area_trips')->default(false);
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_peak_fare_details')) {
            Schema::create('company_peak_fare_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_fare_rule_id');
                $table->unsignedTinyInteger('day')->nullable();
                $table->time('start_time');
                $table->time('end_time');
                $table->decimal('price', 8, 2);
                $table->string('type', 20);
                $table->timestamps();
            });
        }

        Schema::table('company_vehicle_types', function (Blueprint $table) {
            if (!Schema::hasColumn('company_vehicle_types', 'vehicle_image')) {
                $table->string('vehicle_image')->nullable()->after('icon');
            }
            if (!Schema::hasColumn('company_vehicle_types', 'active_image')) {
                $table->string('active_image')->nullable()->after('vehicle_image');
            }
            if (!Schema::hasColumn('company_vehicle_types', 'app_image')) {
                $table->string('app_image')->nullable()->after('active_image');
            }
            if (!Schema::hasColumn('company_vehicle_types', 'is_pool')) {
                $table->string('is_pool', 10)->default('No')->after('description');
            }
        });

        Schema::table('company_fare_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('company_fare_rules', 'capacity')) {
                $table->unsignedInteger('capacity')->default(1)->after('base_fare');
            }
            if (!Schema::hasColumn('company_fare_rules', 'schedule_fare')) {
                $table->decimal('schedule_fare', 11, 2)->default(0)->after('per_km');
            }
            if (!Schema::hasColumn('company_fare_rules', 'currency_code')) {
                $table->string('currency_code', 10)->nullable()->after('schedule_fare');
            }
            if (!Schema::hasColumn('company_fare_rules', 'apply_peak')) {
                $table->string('apply_peak', 10)->default('No')->after('currency_code');
            }
            if (!Schema::hasColumn('company_fare_rules', 'apply_night')) {
                $table->string('apply_night', 10)->default('No')->after('apply_peak');
            }
            if (!Schema::hasColumn('company_fare_rules', 'waiting_time')) {
                $table->decimal('waiting_time', 11, 2)->default(0)->after('apply_night');
            }
            if (!Schema::hasColumn('company_fare_rules', 'waiting_charge')) {
                $table->decimal('waiting_charge', 11, 2)->default(0)->after('waiting_time');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('company_peak_fare_details')) {
            Schema::dropIfExists('company_peak_fare_details');
        }
    }
};
