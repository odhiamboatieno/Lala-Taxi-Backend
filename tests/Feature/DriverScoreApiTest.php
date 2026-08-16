<?php

namespace Tests\Feature;

use App\Models\DriverScoreEvent;
use App\Models\User;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use JWTAuth;
use Tests\TestCase;

class DriverScoreApiTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDriverScoreTestSchema();
        $this->seedDriverScoreSettings();

        DB::table('companies')->insert([
            'id' => 1,
            'name' => 'Platform',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('currency')->insert([
            'id' => 1,
            'name' => 'Kenyan Shilling',
            'code' => 'KES',
            'symbol' => 'KSh',
            'rate' => 1,
            'status' => 'Active',
            'default_currency' => '1',
            'paypal_currency' => 'No',
        ]);
    }

    public function test_driver_can_view_only_their_own_score_summary(): void
    {
        $driver = $this->createDriver(['id' => 20001, 'user_type' => 'Driver']);
        $otherDriver = $this->createDriver(['id' => 20002, 'user_type' => 'Driver']);

        $service = resolve(DriverScoreService::class);
        $service->recalculateDriver($driver, ['trigger' => 'test']);
        $service->recalculateDriver($otherDriver, ['trigger' => 'test']);

        $token = JWTAuth::fromUser($driver);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/driver/score?token='.$token);

        $response->assertOk()
            ->assertJson([
                'status_code' => '1',
                'score' => [
                    'driver_id' => $driver->id,
                ],
            ]);

        $this->assertNotEquals(
            $otherDriver->id,
            (int) data_get($response->json(), 'score.driver_id')
        );
    }

    public function test_driver_cannot_appeal_another_drivers_score_event(): void
    {
        $driver = $this->createDriver(['id' => 20003, 'user_type' => 'Driver']);
        $otherDriver = $this->createDriver(['id' => 20004, 'user_type' => 'Driver']);

        $event = DriverScoreEvent::create([
            'driver_id' => $otherDriver->id,
            'event_type' => 'driver_cancelled_trip',
            'title' => 'Driver cancelled a trip',
            'description' => 'Unauthorized test event',
            'impact_score' => -3,
            'impact_direction' => 'negative',
            'source_type' => 'test',
            'event_date' => now(),
        ]);

        $token = JWTAuth::fromUser($driver);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/driver/score/appeal?token='.$token, [
                'reason' => 'This should not be allowed because it is not my score event.',
                'driver_score_event_id' => $event->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'status_code' => '0',
                'status_message' => 'Score event not found for this driver.',
            ]);
    }

    protected function createDriver(array $overrides = []): User
    {
        return User::query()->forceCreate(array_merge([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'email' => 'driver'.$this->faker->unique()->safeEmail(),
            'country_code' => '+254',
            'mobile_number' => (string) $this->faker->unique()->numberBetween(700000000, 799999999),
            'password' => bcrypt('secret'),
            'user_type' => 'Driver',
            'company_id' => 1,
            'status' => 'Active',
            'device_id' => 'device-'.$this->faker->uuid(),
            'referral_code' => strtoupper($this->faker->lexify('DRIV????')),
            'currency_code' => 'KES',
            'language' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function createDriverScoreTestSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'driver_score_appeals',
            'driver_score_events',
            'driver_scores',
            'driver_score_settings',
            'driver_documents',
            'support_issues',
            'rating',
            'trip_requests',
            'trips',
            'currency',
            'companies',
            'vehicle',
            'driver_location',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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

        Schema::create('vehicle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('vehicle_id')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('default_type')->default('1');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('currency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->string('symbol', 10);
            $table->decimal('rate', 10, 2);
            $table->string('status')->default('Active');
            $table->string('default_currency')->default('0');
            $table->string('paypal_currency')->default('No');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('country_code');
            $table->string('mobile_number');
            $table->string('password')->nullable();
            $table->string('user_type')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('status')->nullable();
            $table->text('device_id')->nullable();
            $table->string('referral_code')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('language')->nullable();
            $table->string('gender')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_km', 8, 2)->default(0);
            $table->string('currency_code')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('rating', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->decimal('rider_rating', 4, 2)->default(0);
            $table->decimal('driver_rating', 4, 2)->default(0);
            $table->text('rider_comments')->nullable();
            $table->text('driver_comments')->nullable();
            $table->timestamps();
        });

        Schema::create('support_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('trip_id')->nullable();
            $table->string('type')->default('issue');
            $table->string('category')->nullable();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->string('priority')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id')->unique();
            $table->decimal('score_percentage', 5, 2)->default(100);
            $table->string('status', 40)->default('Good');
            $table->string('marketplace_access_status', 40)->default('active');
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamp('next_calculation_at')->nullable();
            $table->unsignedInteger('total_completed_trips')->default(0);
            $table->unsignedInteger('total_cancelled_trips')->default(0);
            $table->decimal('acceptance_rate', 5, 2)->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('average_rating', 4, 2)->default(0);
            $table->decimal('complaint_rate', 5, 2)->default(0);
            $table->decimal('late_arrival_rate', 5, 2)->default(0);
            $table->unsignedInteger('safety_incident_count')->default(0);
            $table->unsignedInteger('fraud_flag_count')->default(0);
            $table->unsignedInteger('appeal_pending_count')->default(0);
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_score_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('trip_id')->nullable();
            $table->unsignedBigInteger('support_issue_id')->nullable();
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->string('event_key')->nullable();
            $table->string('source_type')->nullable();
            $table->string('event_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('impact_score', 7, 2)->default(0);
            $table->string('impact_direction')->default('neutral');
            $table->boolean('excluded_from_scoring')->default(false);
            $table->boolean('requires_admin_confirmation')->default(false);
            $table->timestamp('admin_confirmed_at')->nullable();
            $table->unsignedInteger('admin_confirmed_by')->nullable();
            $table->timestamp('event_date')->nullable();
            $table->text('weight_snapshot')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_score_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('driver_score_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->unsignedBigInteger('driver_score_id')->nullable();
            $table->unsignedBigInteger('driver_score_event_id')->nullable();
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution_metadata')->nullable();
            $table->timestamps();
        });
    }

    protected function seedDriverScoreSettings(): void
    {
        $defaults = [
            ['key' => 'rating_weight', 'value' => '24', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'completion_weight', 'value' => '18', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'acceptance_weight', 'value' => '14', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'cancellation_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'complaint_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'safety_weight', 'value' => '8', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'lateness_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'fraud_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'verification_weight', 'value' => '4', 'type' => 'float', 'group' => 'weights', 'is_public' => 1],
            ['key' => 'rolling_window_days', 'value' => '60', 'type' => 'int', 'group' => 'windows', 'is_public' => 1],
            ['key' => 'history_window_days', 'value' => '90', 'type' => 'int', 'group' => 'windows', 'is_public' => 1],
            ['key' => 'minimum_completed_trips', 'value' => '20', 'type' => 'int', 'group' => 'bias_protection', 'is_public' => 1],
            ['key' => 'soft_rating_count', 'value' => '5', 'type' => 'int', 'group' => 'bias_protection', 'is_public' => 1],
            ['key' => 'late_arrival_grace_minutes', 'value' => '12', 'type' => 'int', 'group' => 'bias_protection', 'is_public' => 1],
            ['key' => 'excellent_threshold', 'value' => '90', 'type' => 'float', 'group' => 'thresholds', 'is_public' => 1],
            ['key' => 'good_threshold', 'value' => '80', 'type' => 'float', 'group' => 'thresholds', 'is_public' => 1],
            ['key' => 'warning_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'is_public' => 1],
            ['key' => 'review_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'is_public' => 1],
            ['key' => 'suspension_threshold', 'value' => '55', 'type' => 'float', 'group' => 'thresholds', 'is_public' => 0],
            ['key' => 'suspend_from_score_only', 'value' => 'false', 'type' => 'bool', 'group' => 'thresholds', 'is_public' => 0],
            ['key' => 'serious_incident_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'is_public' => 0],
            ['key' => 'fraud_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'is_public' => 0],
            ['key' => 'recalculation_interval_hours', 'value' => '24', 'type' => 'int', 'group' => 'operations', 'is_public' => 1],
        ];

        DB::table('driver_score_settings')->insert(array_map(function ($setting) {
            return array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $defaults));
    }
}
