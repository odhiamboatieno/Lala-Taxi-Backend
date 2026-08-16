<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyFeeSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'fee_name' => 'access_fee', 'fee_type' => 'percentage', 'fee_value' => 2.0, 'applies_to' => 'rider', 'status' => 'Active', 'created_at' => '2026-06-07 12:05:10', 'updated_at' => '2026-06-07 12:05:10'],
            ['id' => 2, 'company_id' => 4, 'fee_name' => 'driver_peak_fare', 'fee_type' => 'percentage', 'fee_value' => 2.0, 'applies_to' => 'driver', 'status' => 'Active', 'created_at' => '2026-06-07 12:05:10', 'updated_at' => '2026-06-07 12:05:10'],
            ['id' => 3, 'company_id' => 4, 'fee_name' => 'driver_access_fee', 'fee_type' => 'percentage', 'fee_value' => 10.0, 'applies_to' => 'driver', 'status' => 'Active', 'created_at' => '2026-06-07 12:05:10', 'updated_at' => '2026-06-07 12:05:10'],
            ['id' => 4, 'company_id' => 4, 'fee_name' => 'additional_fee', 'fee_type' => 'fixed', 'fee_value' => 1.0, 'applies_to' => 'trip', 'status' => 'Active', 'created_at' => '2026-06-07 12:05:10', 'updated_at' => '2026-06-07 12:05:10'],
            ['id' => 5, 'company_id' => 4, 'fee_name' => 'additional_rider_fare', 'fee_type' => 'percentage', 'fee_value' => 2.0, 'applies_to' => 'rider', 'status' => 'Active', 'created_at' => '2026-06-07 12:05:10', 'updated_at' => '2026-06-07 12:05:10'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_fee_settings')->insert($chunk);
        }
    }
}
