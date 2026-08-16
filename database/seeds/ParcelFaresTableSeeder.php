<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelFaresTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'parcel_category_id' => 1, 'car_type_id' => 1, 'location_id' => null, 'base_fare' => 50.0, 'per_km_rate' => 15.0, 'min_fare' => 50.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 2, 'parcel_category_id' => 1, 'car_type_id' => 2, 'location_id' => null, 'base_fare' => 50.0, 'per_km_rate' => 15.0, 'min_fare' => 50.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 3, 'parcel_category_id' => 1, 'car_type_id' => 3, 'location_id' => null, 'base_fare' => 50.0, 'per_km_rate' => 15.0, 'min_fare' => 50.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 4, 'parcel_category_id' => 1, 'car_type_id' => 4, 'location_id' => null, 'base_fare' => 50.0, 'per_km_rate' => 15.0, 'min_fare' => 50.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 5, 'parcel_category_id' => 1, 'car_type_id' => 5, 'location_id' => null, 'base_fare' => 50.0, 'per_km_rate' => 15.0, 'min_fare' => 50.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 6, 'parcel_category_id' => 2, 'car_type_id' => 1, 'location_id' => null, 'base_fare' => 100.0, 'per_km_rate' => 15.0, 'min_fare' => 100.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 7, 'parcel_category_id' => 2, 'car_type_id' => 2, 'location_id' => null, 'base_fare' => 100.0, 'per_km_rate' => 15.0, 'min_fare' => 100.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 8, 'parcel_category_id' => 2, 'car_type_id' => 3, 'location_id' => null, 'base_fare' => 100.0, 'per_km_rate' => 15.0, 'min_fare' => 100.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 9, 'parcel_category_id' => 2, 'car_type_id' => 4, 'location_id' => null, 'base_fare' => 100.0, 'per_km_rate' => 15.0, 'min_fare' => 100.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 10, 'parcel_category_id' => 2, 'car_type_id' => 5, 'location_id' => null, 'base_fare' => 100.0, 'per_km_rate' => 15.0, 'min_fare' => 100.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 11, 'parcel_category_id' => 3, 'car_type_id' => 1, 'location_id' => null, 'base_fare' => 200.0, 'per_km_rate' => 15.0, 'min_fare' => 200.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 12, 'parcel_category_id' => 3, 'car_type_id' => 2, 'location_id' => null, 'base_fare' => 200.0, 'per_km_rate' => 15.0, 'min_fare' => 200.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 13, 'parcel_category_id' => 3, 'car_type_id' => 3, 'location_id' => null, 'base_fare' => 200.0, 'per_km_rate' => 15.0, 'min_fare' => 200.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 14, 'parcel_category_id' => 3, 'car_type_id' => 4, 'location_id' => null, 'base_fare' => 200.0, 'per_km_rate' => 15.0, 'min_fare' => 200.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 15, 'parcel_category_id' => 3, 'car_type_id' => 5, 'location_id' => null, 'base_fare' => 200.0, 'per_km_rate' => 15.0, 'min_fare' => 200.0, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('parcel_fares')->insert($chunk);
        }
    }
}
