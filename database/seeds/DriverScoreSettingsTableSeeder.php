<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverScoreSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'key' => 'rating_weight', 'value' => '24', 'type' => 'float', 'group' => 'weights', 'label' => 'Ratings Weight', 'description' => 'Weight for rolling rider ratings', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 2, 'key' => 'completion_weight', 'value' => '18', 'type' => 'float', 'group' => 'weights', 'label' => 'Completion Weight', 'description' => 'Weight for trip completion rate', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 3, 'key' => 'acceptance_weight', 'value' => '14', 'type' => 'float', 'group' => 'weights', 'label' => 'Acceptance Weight', 'description' => 'Weight for driver acceptance rate', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 4, 'key' => 'cancellation_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'label' => 'Cancellation Weight', 'description' => 'Penalty weight for cancellations', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 5, 'key' => 'complaint_weight', 'value' => '10', 'type' => 'float', 'group' => 'weights', 'label' => 'Complaints Weight', 'description' => 'Penalty weight for rider complaints', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 6, 'key' => 'safety_weight', 'value' => '8', 'type' => 'float', 'group' => 'weights', 'label' => 'Safety Weight', 'description' => 'Penalty weight for confirmed safety incidents', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 7, 'key' => 'lateness_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'label' => 'Lateness Weight', 'description' => 'Penalty weight for repeated late arrivals', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 8, 'key' => 'fraud_weight', 'value' => '6', 'type' => 'float', 'group' => 'weights', 'label' => 'Fraud Weight', 'description' => 'Penalty weight for confirmed fraud or abuse flags', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 9, 'key' => 'verification_weight', 'value' => '4', 'type' => 'float', 'group' => 'weights', 'label' => 'Verification Weight', 'description' => 'Weight for compliance and document verification', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 10, 'key' => 'rolling_window_days', 'value' => '60', 'type' => 'int', 'group' => 'windows', 'label' => 'Rolling Window Days', 'description' => 'Recent scoring window in days', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 11, 'key' => 'history_window_days', 'value' => '90', 'type' => 'int', 'group' => 'windows', 'label' => 'History Window Days', 'description' => 'Score history window in days', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 12, 'key' => 'minimum_completed_trips', 'value' => '20', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Minimum Completed Trips', 'description' => 'Minimum completed trips before full scoring applies', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 13, 'key' => 'soft_rating_count', 'value' => '5', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Soft Rating Count', 'description' => 'First few ratings are soft-weighted', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 14, 'key' => 'rating_outlier_floor', 'value' => '2.0', 'type' => 'float', 'group' => 'bias_protection', 'label' => 'Outlier Rating Floor', 'description' => 'Ratings at or below this value can be treated as outliers', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 15, 'key' => 'late_arrival_grace_minutes', 'value' => '12', 'type' => 'int', 'group' => 'bias_protection', 'label' => 'Late Arrival Grace Minutes', 'description' => 'Grace period before a trip counts as late arrival', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 16, 'key' => 'excellent_threshold', 'value' => '90', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Excellent Threshold', 'description' => 'Excellent driver threshold', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 17, 'key' => 'good_threshold', 'value' => '80', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Good Threshold', 'description' => 'Good driver threshold', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 18, 'key' => 'warning_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Warning Threshold', 'description' => 'Warning/critical threshold', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 19, 'key' => 'review_threshold', 'value' => '70', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Review Threshold', 'description' => 'Below this threshold the account can require review', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 20, 'key' => 'suspension_threshold', 'value' => '55', 'type' => 'float', 'group' => 'thresholds', 'label' => 'Suspension Threshold', 'description' => 'Configured suspension threshold', 'is_public' => 0, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 21, 'key' => 'suspend_from_score_only', 'value' => 'false', 'type' => 'bool', 'group' => 'thresholds', 'label' => 'Suspend From Score Alone', 'description' => 'Allow suspension using score alone', 'is_public' => 0, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 22, 'key' => 'serious_incident_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'label' => 'Serious Incident Suspend Count', 'description' => 'Confirmed serious incidents required before suspension if score-only suspension is off', 'is_public' => 0, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 23, 'key' => 'fraud_suspend_count', 'value' => '2', 'type' => 'int', 'group' => 'thresholds', 'label' => 'Fraud Suspend Count', 'description' => 'Confirmed fraud flags required before suspension if score-only suspension is off', 'is_public' => 0, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
            ['id' => 24, 'key' => 'recalculation_interval_hours', 'value' => '24', 'type' => 'int', 'group' => 'operations', 'label' => 'Recalculation Interval Hours', 'description' => 'How often to refresh scores automatically', 'is_public' => 1, 'created_at' => '2026-05-19 20:20:57', 'updated_at' => '2026-05-19 20:20:57'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_score_settings')->insert($chunk);
        }
    }
}
