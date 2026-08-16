<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CancelReasonsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'reason' => 'Driver Not Available', 'cancelled_by' => 'Rider', 'status' => 'Active'],
            ['id' => 2, 'reason' => 'Driver not respond proper	', 'cancelled_by' => 'Rider', 'status' => 'Active'],
            ['id' => 3, 'reason' => 'Wrong Route', 'cancelled_by' => 'Rider', 'status' => 'Active'],
            ['id' => 4, 'reason' => 'Rider Not Available', 'cancelled_by' => 'Driver', 'status' => 'Active'],
            ['id' => 5, 'reason' => 'Rider not respond proper', 'cancelled_by' => 'Driver', 'status' => 'Active'],
            ['id' => 6, 'reason' => 'Rider not yet come', 'cancelled_by' => 'Driver', 'status' => 'Active'],
            ['id' => 7, 'reason' => 'Rider ask for Cancel', 'cancelled_by' => 'Admin', 'status' => 'Active'],
            ['id' => 8, 'reason' => 'Other Reasons', 'cancelled_by' => 'Admin', 'status' => 'Active'],
            ['id' => 9, 'reason' => 'Rider Cancelled', 'cancelled_by' => 'Company', 'status' => 'Active'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('cancel_reasons')->insert($chunk);
        }
    }
}
