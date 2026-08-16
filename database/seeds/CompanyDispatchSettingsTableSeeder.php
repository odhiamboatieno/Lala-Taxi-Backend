<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyDispatchSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'dispatch_strategy' => null, 'driver_search_radius' => 20.0, 'max_driver_search_radius' => 20.0, 'request_timeout_seconds' => 30, 'retry_rounds' => 3, 'allow_auto_dispatch' => 1, 'allow_manual_dispatch' => 1, 'allow_scheduled_trips' => 1, 'allow_airport_trips' => 1, 'allow_out_of_area_trips' => 1, 'status' => 'Active', 'created_at' => '2026-06-07 12:05:44', 'updated_at' => '2026-06-07 12:05:44'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_dispatch_settings')->insert($chunk);
        }
    }
}
