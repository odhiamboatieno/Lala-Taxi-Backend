<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManageFareTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'location_id' => 1, 'vehicle_id' => 1, 'base_fare' => 50.0, 'capacity' => 3, 'min_fare' => 10.0, 'per_min' => 1.0, 'per_km' => 10.0, 'schedule_fare' => 20.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 2, 'waiting_charge' => 20.0, 'currency_code' => 'USD', 'apply_peak' => 'No', 'apply_night' => 'No'],
            ['id' => 3, 'location_id' => 1, 'vehicle_id' => 2, 'base_fare' => 2.0, 'capacity' => 5, 'min_fare' => 20.0, 'per_min' => 0.07, 'per_km' => 7.0, 'schedule_fare' => 1.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 3, 'waiting_charge' => 0.2, 'currency_code' => 'ZAR', 'apply_peak' => 'No', 'apply_night' => 'No'],
            ['id' => 4, 'location_id' => 1, 'vehicle_id' => 3, 'base_fare' => 2.0, 'capacity' => 5, 'min_fare' => 5.0, 'per_min' => 1.0, 'per_km' => 1.0, 'schedule_fare' => 1.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 10, 'waiting_charge' => 0.2, 'currency_code' => 'USD', 'apply_peak' => 'No', 'apply_night' => 'No'],
            ['id' => 5, 'location_id' => 1, 'vehicle_id' => 4, 'base_fare' => 2.0, 'capacity' => 5, 'min_fare' => 1.0, 'per_min' => 1.0, 'per_km' => 1.0, 'schedule_fare' => 1.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 15, 'waiting_charge' => 0.1, 'currency_code' => 'USD', 'apply_peak' => 'No', 'apply_night' => 'No'],
            ['id' => 6, 'location_id' => 3, 'vehicle_id' => 1, 'base_fare' => 2.0, 'capacity' => 5, 'min_fare' => 5.0, 'per_min' => 1.0, 'per_km' => 1.0, 'schedule_fare' => 1.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 10, 'waiting_charge' => 0.2, 'currency_code' => 'USD', 'apply_peak' => 'No', 'apply_night' => 'No'],
            ['id' => 7, 'location_id' => 1, 'vehicle_id' => 5, 'base_fare' => 50.0, 'capacity' => 1, 'min_fare' => 50.0, 'per_min' => 0.25, 'per_km' => 30.0, 'schedule_fare' => 2.0, 'schedule_cancel_fare' => 0.0, 'waiting_time' => 5, 'waiting_charge' => 5.0, 'currency_code' => 'USD', 'apply_peak' => 'No', 'apply_night' => 'No'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('manage_fare')->insert($chunk);
        }
    }
}
