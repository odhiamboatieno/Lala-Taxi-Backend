<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CancelTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 14, 'trip_id' => 90, 'user_id' => 10019, 'cancel_reason_id' => 4, 'cancel_comments' => '', 'cancelled_by' => 'Driver', 'created_at' => '2026-06-05 08:59:51', 'updated_at' => '2026-06-05 08:59:51', 'deleted_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('cancel')->insert($chunk);
        }
    }
}
