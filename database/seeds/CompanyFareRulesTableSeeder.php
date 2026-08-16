<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyFareRulesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'company_vehicle_type_id' => 1, 'company_service_area_id' => 4, 'base_fare' => 20.0, 'capacity' => 3, 'per_km' => 4.0, 'schedule_fare' => 20.0, 'currency_code' => 'GBP', 'apply_peak' => 'No', 'apply_night' => 'No', 'waiting_time' => 5.0, 'waiting_charge' => 5.0, 'per_minute' => 1.0, 'minimum_fare' => 40.0, 'waiting_charge_per_minute' => 0.0, 'cancellation_fee' => 0.0, 'night_surcharge_type' => null, 'night_surcharge_value' => null, 'airport_fee' => null, 'commission_type' => null, 'commission_value' => null, 'status' => 'Active', 'created_at' => '2026-06-07 12:03:02', 'updated_at' => '2026-06-07 12:03:02'],
            ['id' => 2, 'company_id' => 4, 'company_vehicle_type_id' => 2, 'company_service_area_id' => 4, 'base_fare' => 30.0, 'capacity' => 4, 'per_km' => 6.0, 'schedule_fare' => 20.0, 'currency_code' => 'GBP', 'apply_peak' => 'No', 'apply_night' => 'No', 'waiting_time' => 5.0, 'waiting_charge' => 5.0, 'per_minute' => 1.0, 'minimum_fare' => 60.0, 'waiting_charge_per_minute' => 0.0, 'cancellation_fee' => 0.0, 'night_surcharge_type' => null, 'night_surcharge_value' => null, 'airport_fee' => null, 'commission_type' => null, 'commission_value' => null, 'status' => 'Active', 'created_at' => '2026-06-07 12:03:02', 'updated_at' => '2026-06-07 12:03:02'],
            ['id' => 3, 'company_id' => 4, 'company_vehicle_type_id' => 1, 'company_service_area_id' => 3, 'base_fare' => 30.0, 'capacity' => 1, 'per_km' => 2.5, 'schedule_fare' => 5.0, 'currency_code' => 'GBP', 'apply_peak' => 'No', 'apply_night' => 'No', 'waiting_time' => 5.0, 'waiting_charge' => 5.0, 'per_minute' => 1.0, 'minimum_fare' => 5.0, 'waiting_charge_per_minute' => 0.0, 'cancellation_fee' => 0.0, 'night_surcharge_type' => null, 'night_surcharge_value' => null, 'airport_fee' => null, 'commission_type' => null, 'commission_value' => null, 'status' => 'Active', 'created_at' => '2026-06-07 12:04:32', 'updated_at' => '2026-06-07 12:04:32'],
            ['id' => 4, 'company_id' => 4, 'company_vehicle_type_id' => 2, 'company_service_area_id' => 3, 'base_fare' => 40.0, 'capacity' => 1, 'per_km' => 3.5, 'schedule_fare' => 5.0, 'currency_code' => 'GBP', 'apply_peak' => 'No', 'apply_night' => 'No', 'waiting_time' => 5.0, 'waiting_charge' => 5.0, 'per_minute' => 1.0, 'minimum_fare' => 5.0, 'waiting_charge_per_minute' => 0.0, 'cancellation_fee' => 0.0, 'night_surcharge_type' => null, 'night_surcharge_value' => null, 'airport_fee' => null, 'commission_type' => null, 'commission_value' => null, 'status' => 'Active', 'created_at' => '2026-06-07 12:04:32', 'updated_at' => '2026-06-07 12:04:32'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_fare_rules')->insert($chunk);
        }
    }
}
