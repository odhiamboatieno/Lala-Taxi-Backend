<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverScoreEventsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'driver_id' => 10003, 'trip_id' => null, 'support_issue_id' => null, 'driver_score_id' => 6, 'event_key' => 'appeal_submitted_1', 'source_type' => 'driver_appeal', 'event_type' => 'appeal_submitted', 'title' => 'Driver appeal submitted', 'description' => 'The driver requested a human review for a score-impacting event.', 'impact_score' => 0.0, 'impact_direction' => 'neutral', 'excluded_from_scoring' => 0, 'requires_admin_confirmation' => 0, 'admin_confirmed_at' => null, 'admin_confirmed_by' => null, 'event_date' => '2026-06-05 06:36:52', 'weight_snapshot' => '{"ratings":24,"completion":18,"acceptance":14,"cancellations":10,"complaints":10,"safety":8,"lateness":6,"fraud":6,"verification":4}', 'metadata' => '{"appeal_id":1,"driver_score_event_id":null}', 'created_at' => '2026-06-05 06:36:52', 'updated_at' => '2026-06-05 06:36:52'],
            ['id' => 2, 'driver_id' => 10019, 'trip_id' => 90, 'support_issue_id' => null, 'driver_score_id' => 1, 'event_key' => 'trip_cancelled_90', 'source_type' => 'trip', 'event_type' => 'driver_cancelled_trip', 'title' => 'Driver cancelled a trip', 'description' => 'Driver cancelled the accepted trip.', 'impact_score' => -3.0, 'impact_direction' => 'negative', 'excluded_from_scoring' => 0, 'requires_admin_confirmation' => 0, 'admin_confirmed_at' => null, 'admin_confirmed_by' => null, 'event_date' => '2026-06-05 08:59:51', 'weight_snapshot' => '{"ratings":24,"completion":18,"acceptance":14,"cancellations":10,"complaints":10,"safety":8,"lateness":6,"fraud":6,"verification":4}', 'metadata' => '{"cancel_reason_id":"4"}', 'created_at' => '2026-06-05 08:59:51', 'updated_at' => '2026-06-05 08:59:51'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_score_events')->insert($chunk);
        }
    }
}
