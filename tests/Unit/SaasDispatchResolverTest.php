<?php

namespace Tests\Unit;

use App\Models\CompanySaasSetting;
use App\Models\ManageFare;
use App\Models\SiteSettings;
use App\Models\User;
use App\Services\Dispatch\SaaS\DispatchContextResolver;
use App\Services\Dispatch\SaaS\DriverPoolResolver;
use App\Services\Dispatch\SaaS\FareResolver;
use App\Services\Dispatch\SaaS\OperatorResolver;
use App\Services\Dispatch\SaaS\SaaSModuleManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaasDispatchResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedBaseData();
        $this->app->bind('site_settings', fn () => SiteSettings::query()->get());
    }

    public function test_module_disabled_keeps_company_on_global_fare_logic(): void
    {
        SiteSettings::updateOrCreate(['name' => 'saas_dispatch_module_enabled'], ['value' => '0']);

        CompanySaasSetting::query()->create([
            'company_id' => 2,
            'is_saas_enabled' => true,
            'dispatch_mode' => 'saas',
            'use_own_locations' => true,
            'use_own_fares' => true,
            'use_own_fees' => true,
            'use_own_vehicle_types' => true,
            'use_own_dispatch_settings' => true,
            'use_own_branding' => true,
            'status' => 'Active',
        ]);

        $resolver = $this->app->make(DispatchContextResolver::class);
        $rider = User::query()->findOrFail(5001);

        $context = $resolver->forPayload($rider, [
            'company_id' => 2,
            'car_id' => 10,
            'location_id' => 100,
        ]);

        $this->assertSame('company', $context['operator_type']);
        $this->assertSame('global', $context['fare_source']);
        $this->assertInstanceOf(ManageFare::class, $context['fare_model']);
    }

    public function test_admin_platform_context_always_uses_global_fare(): void
    {
        SiteSettings::updateOrCreate(['name' => 'saas_dispatch_module_enabled'], ['value' => '1']);

        $resolver = $this->app->make(DispatchContextResolver::class);
        $rider = User::query()->findOrFail(5001);

        $context = $resolver->forPayload($rider, [
            'operator_type' => 'admin',
            'company_id' => 1,
            'car_id' => 10,
            'location_id' => 100,
        ]);

        $this->assertTrue($context['is_platform']);
        $this->assertSame('global', $context['fare_source']);
        $this->assertInstanceOf(ManageFare::class, $context['fare_model']);
    }

    public function test_company_global_mode_uses_global_fare(): void
    {
        SiteSettings::updateOrCreate(['name' => 'saas_dispatch_module_enabled'], ['value' => '1']);

        CompanySaasSetting::query()->create([
            'company_id' => 2,
            'is_saas_enabled' => true,
            'dispatch_mode' => 'global',
            'status' => 'Active',
        ]);

        $context = $this->app->make(DispatchContextResolver::class)->forPayload(
            User::query()->findOrFail(5001),
            ['company_id' => 2, 'car_id' => 10, 'location_id' => 100]
        );

        $this->assertSame('global', $context['fare_source']);
        $this->assertSame(2, $context['company_id']);
    }

    public function test_company_saas_mode_uses_company_fare(): void
    {
        SiteSettings::updateOrCreate(['name' => 'saas_dispatch_module_enabled'], ['value' => '1']);

        CompanySaasSetting::query()->create([
            'company_id' => 2,
            'is_saas_enabled' => true,
            'dispatch_mode' => 'saas',
            'use_own_locations' => true,
            'use_own_fares' => true,
            'use_own_fees' => true,
            'use_own_vehicle_types' => true,
            'use_own_dispatch_settings' => true,
            'status' => 'Active',
        ]);

        Schema::table('company_vehicle_types', function (Blueprint $table) {
            // no-op to ensure table exists in sqlite setup path
        });

        \DB::table('company_vehicle_types')->insert([
            'id' => 1000,
            'company_id' => 2,
            'global_vehicle_category_id' => 1,
            'name' => 'Saloon',
            'slug' => 'saloon',
            'passenger_capacity' => 4,
            'luggage_capacity' => 2,
            'is_ev' => 0,
            'is_accessible' => 0,
            'sort_order' => 1,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('company_fare_rules')->insert([
            'company_id' => 2,
            'company_vehicle_type_id' => 1000,
            'company_service_area_id' => null,
            'base_fare' => 55,
            'per_km' => 7,
            'per_minute' => 3,
            'minimum_fare' => 75,
            'waiting_charge_per_minute' => 1,
            'cancellation_fee' => 10,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $context = $this->app->make(DispatchContextResolver::class)->forPayload(
            User::query()->findOrFail(5001),
            [
                'company_id' => 2,
                'car_id' => 10,
                'location_id' => 100,
                'company_vehicle_type_id' => 1000,
                'global_vehicle_category_id' => 1,
            ]
        );

        $this->assertSame('company', $context['fare_source']);
        $this->assertSame(55.0, (float) $context['fare_model']->base_fare);
    }

    public function test_company_hybrid_mode_falls_back_to_global_fare_when_company_rule_missing(): void
    {
        SiteSettings::updateOrCreate(['name' => 'saas_dispatch_module_enabled'], ['value' => '1']);

        CompanySaasSetting::query()->create([
            'company_id' => 2,
            'is_saas_enabled' => true,
            'dispatch_mode' => 'hybrid',
            'use_own_fares' => true,
            'status' => 'Active',
        ]);

        $context = $this->app->make(DispatchContextResolver::class)->forPayload(
            User::query()->findOrFail(5001),
            [
                'company_id' => 2,
                'car_id' => 10,
                'location_id' => 100,
                'company_vehicle_type_id' => 9999,
            ]
        );

        $this->assertSame('global', $context['fare_source']);
        $this->assertInstanceOf(ManageFare::class, $context['fare_model']);
    }

    public function test_driver_pool_resolver_separates_platform_and_company_drivers(): void
    {
        $driverPoolResolver = $this->app->make(DriverPoolResolver::class);
        $operatorResolver = $this->app->make(OperatorResolver::class);

        User::query()->insert([
            [
                'id' => 6001,
                'first_name' => 'Platform',
                'last_name' => 'Driver',
                'email' => 'platform-driver@example.com',
                'country_code' => '254',
                'mobile_number' => '700000201',
                'password' => bcrypt('secret'),
                'user_type' => 'Driver',
                'company_id' => 1,
                'status' => 'Active',
                'is_online' => true,
                'is_available' => true,
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6002,
                'first_name' => 'Company',
                'last_name' => 'Driver',
                'email' => 'company-driver@example.com',
                'country_code' => '254',
                'mobile_number' => '700000202',
                'password' => bcrypt('secret'),
                'user_type' => 'Driver',
                'company_id' => 2,
                'status' => 'Active',
                'is_online' => true,
                'is_available' => true,
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6003,
                'first_name' => 'Null Platform',
                'last_name' => 'Driver',
                'email' => 'null-platform-driver@example.com',
                'country_code' => '254',
                'mobile_number' => '700000203',
                'password' => bcrypt('secret'),
                'user_type' => 'Driver',
                'company_id' => null,
                'status' => 'Active',
                'is_online' => true,
                'is_available' => true,
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $platformIds = $driverPoolResolver
            ->applyScope(User::query()->where('user_type', 'Driver'), $operatorResolver->platformContext())
            ->pluck('id')
            ->all();

        $companyIds = $driverPoolResolver
            ->applyScope(User::query()->where('user_type', 'Driver'), $operatorResolver->resolveForPayload(null, ['company_id' => 2]))
            ->pluck('id')
            ->all();

        sort($platformIds);
        sort($companyIds);

        $this->assertSame([6001, 6003], $platformIds);
        $this->assertSame([6002], $companyIds);
    }

    protected function createSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'company_dispatch_settings',
            'company_fee_settings',
            'company_fare_rules',
            'company_vehicle_category_mappings',
            'company_vehicle_types',
            'company_service_areas',
            'company_saas_settings',
            'global_vehicle_categories',
            'driver_location',
            'vehicle',
            'profile_picture',
            'users',
            'companies',
            'manage_fare',
            'fees',
            'site_settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('site_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('value')->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('password')->nullable();
            $table->string('user_type')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(false);
            $table->unsignedInteger('current_trip_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->string('currency_code')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('profile_picture', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('src')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('vehicle_id')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('default_type')->default('1');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('driver_location', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('car_id')->nullable();
            $table->unsignedInteger('pool_trip_id')->nullable();
            $table->timestamps();
        });

        Schema::create('manage_fare', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('vehicle_id');
            $table->decimal('base_fare', 11, 2)->default(0);
            $table->decimal('min_fare', 11, 2)->default(0);
            $table->decimal('per_min', 11, 2)->default(0);
            $table->decimal('per_km', 11, 2)->default(0);
            $table->decimal('schedule_fare', 11, 2)->default(0);
            $table->decimal('schedule_cancel_fare', 11, 2)->default(0);
            $table->decimal('waiting_charge', 11, 2)->default(0);
            $table->string('currency_code')->nullable();
        });

        Schema::create('fees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });

        Schema::create('global_vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_visible_to_rider')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('company_saas_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->boolean('is_saas_enabled')->default(false);
            $table->string('dispatch_mode')->default('global');
            $table->boolean('use_own_locations')->default(false);
            $table->boolean('use_own_fares')->default(false);
            $table->boolean('use_own_fees')->default(false);
            $table->boolean('use_own_vehicle_types')->default(false);
            $table->boolean('use_own_dispatch_settings')->default(false);
            $table->boolean('use_own_branding')->default(false);
            $table->string('domain')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('company_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zone_name')->nullable();
            $table->longText('polygon')->nullable();
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('dropoff_enabled')->default(true);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('company_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('global_vehicle_category_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('passenger_capacity')->default(4);
            $table->unsignedTinyInteger('luggage_capacity')->default(0);
            $table->boolean('is_ev')->default(false);
            $table->boolean('is_accessible')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('global_vehicle_category_id');
            $table->unsignedBigInteger('company_vehicle_type_id');
            $table->unsignedInteger('priority')->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

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

        Schema::create('company_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('fee_name');
            $table->string('fee_type')->default('fixed');
            $table->decimal('fee_value', 11, 2)->default(0);
            $table->string('applies_to')->default('trip');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

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

    protected function seedBaseData(): void
    {
        SiteSettings::query()->insert([
            ['name' => 'saas_dispatch_module_enabled', 'value' => '0'],
            ['name' => 'fare_mode', 'value' => 'Taximeter'],
        ]);

        \DB::table('companies')->insert([
            ['id' => 1, 'name' => 'Platform', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Company A', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        User::query()->create([
            'id' => 5001,
            'first_name' => 'Rider',
            'last_name' => 'One',
            'email' => 'rider@example.com',
            'country_code' => '254',
            'mobile_number' => '700000101',
            'password' => bcrypt('secret'),
            'user_type' => 'Rider',
            'company_id' => 1,
            'status' => 'Active',
            'currency_code' => 'KES',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('manage_fare')->insert([
            'id' => 1,
            'location_id' => 100,
            'vehicle_id' => 10,
            'base_fare' => 25,
            'min_fare' => 40,
            'per_min' => 2,
            'per_km' => 5,
            'schedule_fare' => 0,
            'schedule_cancel_fare' => 0,
            'waiting_charge' => 1,
            'currency_code' => 'KES',
        ]);

        \DB::table('global_vehicle_categories')->insert([
            'id' => 1,
            'name' => 'Economy',
            'slug' => 'economy',
            'is_visible_to_rider' => 1,
            'sort_order' => 1,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
