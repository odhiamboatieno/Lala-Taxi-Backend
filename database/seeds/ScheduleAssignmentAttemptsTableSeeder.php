<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleAssignmentAttemptsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'schedule_ride_id' => 4, 'driver_id' => null, 'company_id' => null, 'dispatcher_id' => null, 'dispatch_mode' => 'auto', 'status' => 'offered', 'offered_at' => '2026-05-25 17:33:09', 'accepted_at' => null, 'rejected_at' => null, 'timed_out_at' => null, 'rejection_reason' => null, 'timeout_seconds' => 20, 'sequence_order' => 1, 'metadata' => '{"source":"manual_booking_store","booking_mode":"asap","priority":"normal"}', 'created_at' => '2026-05-25 15:33:09', 'updated_at' => '2026-05-25 15:33:09'],
            ['id' => 2, 'schedule_ride_id' => 5, 'driver_id' => null, 'company_id' => null, 'dispatcher_id' => null, 'dispatch_mode' => 'auto', 'status' => 'offered', 'offered_at' => '2026-05-25 17:50:19', 'accepted_at' => null, 'rejected_at' => null, 'timed_out_at' => null, 'rejection_reason' => null, 'timeout_seconds' => 20, 'sequence_order' => 1, 'metadata' => '{"source":"manual_booking_store","booking_mode":"asap","priority":"normal"}', 'created_at' => '2026-05-25 15:50:19', 'updated_at' => '2026-05-25 15:50:19'],
            ['id' => 3, 'schedule_ride_id' => 6, 'driver_id' => null, 'company_id' => null, 'dispatcher_id' => null, 'dispatch_mode' => 'auto', 'status' => 'offered', 'offered_at' => '2026-05-25 17:55:45', 'accepted_at' => null, 'rejected_at' => null, 'timed_out_at' => null, 'rejection_reason' => null, 'timeout_seconds' => 45, 'sequence_order' => 1, 'metadata' => '{"source":"manual_booking_store","booking_mode":"scheduled","priority":"normal"}', 'created_at' => '2026-05-25 15:55:45', 'updated_at' => '2026-05-25 15:55:45'],
            ['id' => 4, 'schedule_ride_id' => 3, 'driver_id' => 10019, 'company_id' => null, 'dispatcher_id' => null, 'dispatch_mode' => 'manual', 'status' => 'rejected', 'offered_at' => '2026-05-26 21:19:20', 'accepted_at' => null, 'rejected_at' => '2026-05-26 21:19:20', 'timed_out_at' => null, 'rejection_reason' => 'Rider not respond proper', 'timeout_seconds' => 30, 'sequence_order' => 1, 'metadata' => '{"source":"driver_pending_cancel","cancel_reason_id":"5","cancel_comments":""}', 'created_at' => '2026-05-26 19:19:20', 'updated_at' => '2026-05-26 19:19:20'],
            ['id' => 5, 'schedule_ride_id' => 7, 'driver_id' => null, 'company_id' => null, 'dispatcher_id' => null, 'dispatch_mode' => 'auto', 'status' => 'offered', 'offered_at' => '2026-06-05 15:14:58', 'accepted_at' => null, 'rejected_at' => null, 'timed_out_at' => null, 'rejection_reason' => null, 'timeout_seconds' => 20, 'sequence_order' => 1, 'metadata' => '{"source":"manual_booking_store","booking_mode":"asap","priority":"normal"}', 'created_at' => '2026-06-05 13:14:58', 'updated_at' => '2026-06-05 13:14:58'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('schedule_assignment_attempts')->insert($chunk);
        }
    }
}
