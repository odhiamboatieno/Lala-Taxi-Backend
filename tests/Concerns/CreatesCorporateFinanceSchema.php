<?php

namespace Tests\Concerns;

use App\Models\Fees;
use App\Services\Corporate\CorporateTripFinalizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesCorporateFinanceSchema
{
    use CreatesCorporateBookingSchema;

    protected function createCorporateFinanceSchema(): void
    {
        $this->createCorporateBookingSchema();
        Schema::table('trips', function (Blueprint $t) {
            foreach (CorporateTripFinalizer::FINANCIAL_FIELDS as $field) {
                if (!Schema::hasColumn('trips', $field)) {
                    $t->decimal($field, 12, 2)->default('0.00');
                }
            }
            $t->unsignedInteger('car_id')->default(1); $t->unsignedInteger('pool_id')->default(0);
            $t->unsignedInteger('is_calculation')->default(0); $t->decimal('total_km', 10, 5)->default(20);
            $t->integer('total_time')->default(0); $t->decimal('peak_fare', 8, 2)->default(0);
            $t->decimal('additional_rider', 8, 2)->default(0); $t->decimal('extra_fare_percentage', 8, 2)->default(0);
            $t->string('extra_fare_reason')->nullable(); $t->string('company_driver_fee_type')->nullable(); $t->string('company_driver_fee_label')->nullable();
            $t->json('fare_estimation')->nullable(); $t->timestamp('arrive_time')->nullable(); $t->timestamp('begin_trip')->nullable();
            $t->timestamp('accepted_at')->nullable(); $t->timestamp('started_at')->nullable();
            $t->decimal('pickup_latitude', 10, 6)->default(-1.2676); $t->decimal('pickup_longitude', 10, 6)->default(36.8108);
            $t->decimal('drop_latitude', 10, 6)->default(-1.3192); $t->decimal('drop_longitude', 10, 6)->default(36.9278);
            $t->string('pickup_location')->default('Westlands'); $t->string('drop_location')->default('JKIA');
        });
        Schema::table('request', function (Blueprint $t) { $t->unsignedInteger('location_id')->default(1); $t->integer('seats')->default(1); });
        Schema::table('users', function (Blueprint $t) { $t->unsignedInteger('current_trip_id')->nullable(); $t->boolean('is_available')->default(false); });
        (require database_path('migrations/2026_08_16_000086_create_payment_table.php'))->up();
        (require database_path('migrations/2026_10_07_000004_add_corporate_finalization_evidence.php'))->up();
        (require database_path('migrations/2026_08_16_000067_create_manage_fare_table.php'))->up();
        (require database_path('migrations/2026_08_16_000052_create_fees_table.php'))->up();
        Schema::create('rating', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('trip_id'); $t->integer('driver_rating')->default(0); });
        DB::table('car_type')->insert(['id' => 1, 'is_pool' => 'No']);
        DB::table('users')->where('id', 2)->update(['company_id' => 1, 'user_type' => 'Driver']);
        DB::table('fees')->insert([['name' => 'access_fee', 'value' => '10'], ['name' => 'driver_access_fee', 'value' => '15']]);
        $this->app->bind('fees', fn () => Fees::all());
        DB::table('manage_fare')->insert([
            'location_id' => 1, 'vehicle_id' => 1, 'currency_code' => 'KES', 'base_fare' => '100.00', 'min_fare' => '100.00',
            'per_min' => '10.00', 'per_km' => '50.00', 'schedule_fare' => '0.00', 'schedule_cancel_fare' => '0.00',
            'waiting_time' => 5, 'waiting_charge' => '10.00', 'capacity' => 4, 'apply_peak' => 'No', 'apply_night' => 'No',
        ]);
    }
}
