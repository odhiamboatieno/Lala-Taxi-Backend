<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverScoreAppealsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'driver_id' => 10003, 'driver_score_id' => 6, 'driver_score_event_id' => null, 'reason' => 'f7f7cucuv. vvibohoh9hvvgv. vvvigviv. ygvvgcyc. gggvvv vvvvv.', 'status' => 'pending', 'admin_notes' => null, 'reviewed_by' => null, 'reviewed_at' => null, 'resolution_metadata' => null, 'created_at' => '2026-06-05 06:36:52', 'updated_at' => '2026-06-05 06:36:52'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_score_appeals')->insert($chunk);
        }
    }
}
