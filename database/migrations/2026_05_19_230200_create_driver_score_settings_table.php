<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_score_settings')) {
            Schema::create('driver_score_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->text('value')->nullable();
                $table->string('type', 30)->default('string');
                $table->string('group', 60)->default('general');
                $table->string('label', 160)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        $defaults = [
            ['key' => 'rating_weight', 'value' => '24', 'type' => 'float', 'group' => 'weights', 'label' => 'Ratings Weight', 'description' => 'Weight for rolling rider ratings', 'is_public' => 1],
            ['key' => 'completion_weight', 'value' => '18', 'type' => 'float', 'group' => 'weights', 'label' => 'Completion Weight', 'description' => 'Weight for trip completion rate', 'is_public' => 1],
            ['key' => 'acceptance_weight', 'value' => '14', 'type' => 'float', 'group' => 'weights', 'label' => 'Acceptance Weight', 'description' => 'Weight for driver acceptance rate', 'is_public' => 1],
            ['key' => 'cancellation_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'label' => 'Cancellation Weight', 'description' => 'Penalty weight for cancellations', 'is_public' => 1],
            ['key' => 'complaint_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'label' => 'Complaints Weight', 'description' => 'Penalty weight for rider complaints', 'is_public' => 1],
            ['key' => 'safety_weight', 'value' => '8', 'type' => 'float', 'group' => 'weights', 'label' => 'Safety Weight', 'description' => 'Penalty weight for confirmed safety incidents', 'is_public' => 1],
            ['key' => 'lateness_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'label' => 'Lateness Weight', 'description' => 'Penalty weight for repeated late arrivals', 'is_public' => 1],
            ['key' => 'fraud_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'label' => 'Fraud Weight', 'description' => 'Penalty weight for confirmed fraud or abuse flags', 'is_public' => 1],
            ['key' => 'verification_weight', 'value' => '4', 'type' => 'float', 'group' => 'weights', 'label' => 'Verification Weight', 'description' => 'Weight for compliance and document verification', 'is_public' => 1],
            ['key' => 'rolling_window_days', 'value' => '60', 'type' => 'int', 'group' => 'windows', 'label' => 'Rolling Window Days', 'description' => 'Recent scoring window in days', 'is_public' => 1],
            ['key' => 'history_window_days', 'value' => '90', 'type' => 'int', 'group' => 'windows', 'label' => 'History Window Days', 'description' => 'Score history window in days', 'is_public' => 1],
            ['key' => 'minimum_completed_trips', 'value' => '20', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Minimum Completed Trips', 'description' => 'Minimum completed trips before full scoring applies', 'is_public' => 1],
            ['key' => 'soft_rating_count', 'value' => '5', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Soft Rating Count', 'description' => 'First few ratings are soft-weighted', 'is_public' => 1],
            ['key' => 'rating_outlier_floor', 'value' => '2.0', 'type' => 'float', 'group' => 'bias_protection', 'label' => 'Outlier Rating Floor', 'description' => 'Ratings at or below this value can be treated as outliers', 'is_public' => 1],
            ['key' => 'late_arrival_grace_minutes', 'value' => '12', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Late Arrival Grace Minutes', 'description' => 'Grace period before a trip counts as late arrival', 'is_public' => 1],
            ['key' => 'excellent_threshold', 'value' => '90', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Excellent Threshold', 'description' => 'Excellent driver threshold', 'is_public' => 1],
            ['key' => 'good_threshold', 'value' => '80', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Good Threshold', 'description' => 'Good driver threshold', 'is_public' => 1],
            ['key' => 'warning_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Warning Threshold', 'description' => 'Warning/critical threshold', 'is_public' => 1],
            ['key' => 'review_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Review Threshold', 'description' => 'Below this threshold the account can require review', 'is_public' => 1],
            ['key' => 'suspension_threshold', 'value' => '55', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Suspension Threshold', 'description' => 'Configured suspension threshold', 'is_public' => 0],
            ['key' => 'suspend_from_score_only', 'value' => 'false', 'type' => 'bool', 'group' => 'thresholds', 'label' => 'Suspend From Score Alone', 'description' => 'Allow suspension using score alone', 'is_public' => 0],
            ['key' => 'serious_incident_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'label' => 'Serious Incident Suspend Count', 'description' => 'Confirmed serious incidents required before suspension if score-only suspension is off', 'is_public' => 0],
            ['key' => 'fraud_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'label' => 'Fraud Suspend Count', 'description' => 'Confirmed fraud flags required before suspension if score-only suspension is off', 'is_public' => 0],
            ['key' => 'recalculation_interval_hours', 'value' => '24', 'type' => 'int', 'group' => 'operations', 'label' => 'Recalculation Interval Hours', 'description' => 'How often to refresh scores automatically', 'is_public' => 1],
        ];

        foreach ($defaults as $setting) {
            DB::table('driver_score_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_score_settings');
    }
};
