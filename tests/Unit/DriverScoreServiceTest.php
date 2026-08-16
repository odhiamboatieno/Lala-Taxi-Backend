<?php

namespace Tests\Unit;

use App\Models\DriverScoreSetting;
use App\Models\Rating;
use App\Models\User;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DriverScoreServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedSettings();
    }

    public function test_single_bad_rating_does_not_destroy_new_driver_score(): void
    {
        $driver = User::query()->forceCreate([
            'id' => 21001,
            'first_name' => 'Protected',
            'last_name' => 'Driver',
            'email' => 'protected@example.com',
            'country_code' => '+254',
            'mobile_number' => '700000111',
            'password' => bcrypt('secret'),
            'user_type' => 'Driver',
            'company_id' => 1,
            'status' => 'Active',
            'device_id' => 'device-one',
            'referral_code' => 'DRVTEST001',
            'currency_code' => 'KES',
            'language' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Rating::query()->create([
            'trip_id' => 1,
            'driver_id' => $driver->id,
            'user_id' => 5001,
            'rider_rating' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $score = resolve(DriverScoreService::class)->recalculateDriver($driver, ['trigger' => 'unit_test']);

        $this->assertNotNull($score);
        $this->assertGreaterThanOrEqual(90, (float) $score->score_percentage);
        $this->assertContains($score->marketplace_access_status, ['active', 'limited']);
        $this->assertSame(1, data_get($score->metadata, 'bias_protection.single_rating_protection') ? 1 : 0);
    }

    protected function createSchema(): void
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
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_km', 8, 2)->default(0);
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
            $table->string('event_type');
            $table->string('title');
            $table->decimal('impact_score', 7, 2)->default(0);
            $table->string('impact_direction')->default('neutral');
            $table->boolean('excluded_from_scoring')->default(false);
            $table->boolean('requires_admin_confirmation')->default(false);
            $table->timestamp('admin_confirmed_at')->nullable();
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
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('driver_score_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('driver_id');
            $table->timestamps();
        });
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'rating_weight', 'value' => '24', 'type' => 'float'],
            ['key' => 'completion_weight', 'value' => '18', 'type' => 'float'],
            ['key' => 'acceptance_weight', 'value' => '14', 'type' => 'float'],
            ['key' => 'cancellation_weight', 'value' => '10', 'type' => 'float'],
            ['key' => 'complaint_weight', 'value' => '10', 'type' => 'float'],
            ['key' => 'safety_weight', 'value' => '8', 'type' => 'float'],
            ['key' => 'lateness_weight', 'value' => '6', 'type' => 'float'],
            ['key' => 'fraud_weight', 'value' => '6', 'type' => 'float'],
            ['key' => 'verification_weight', 'value' => '4', 'type' => 'float'],
            ['key' => 'rolling_window_days', 'value' => '60', 'type' => 'int'],
            ['key' => 'history_window_days', 'value' => '90', 'type' => 'int'],
            ['key' => 'minimum_completed_trips', 'value' => '20', 'type' => 'int'],
            ['key' => 'soft_rating_count', 'value' => '5', 'type' => 'int'],
            ['key' => 'late_arrival_grace_minutes', 'value' => '12', 'type' => 'int'],
            ['key' => 'excellent_threshold', 'value' => '90', 'type' => 'float'],
            ['key' => 'good_threshold', 'value' => '80', 'type' => 'float'],
            ['key' => 'warning_threshold', 'value' => '70', 'type' => 'float'],
            ['key' => 'review_threshold', 'value' => '70', 'type' => 'float'],
            ['key' => 'suspension_threshold', 'value' => '55', 'type' => 'float'],
            ['key' => 'suspend_from_score_only', 'value' => 'false', 'type' => 'bool'],
            ['key' => 'serious_incident_suspend_count', 'value' => '2', 'type' => 'int'],
            ['key' => 'fraud_suspend_count', 'value' => '2', 'type' => 'int'],
            ['key' => 'recalculation_interval_hours', 'value' => '24', 'type' => 'int'],
        ];

        DriverScoreSetting::query()->insert(array_map(function ($item) {
            return array_merge($item, [
                'group' => 'tests',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $settings));
    }
}
