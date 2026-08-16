<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleModelTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'vehicle_make_id' => 1, 'model_name' => 'Swift', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 2, 'vehicle_make_id' => 1, 'model_name' => 'Wagon R', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 3, 'vehicle_make_id' => 2, 'model_name' => 'Elite i20', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 4, 'vehicle_make_id' => 2, 'model_name' => 'Grand i10', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 5, 'vehicle_make_id' => 3, 'model_name' => 'Terrano', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 6, 'vehicle_make_id' => 3, 'model_name' => 'Sunny', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 7, 'vehicle_make_id' => 51, 'model_name' => 'Almera Hatchback', 'status' => 'Active', 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'vehicle_make_id' => 51, 'model_name' => 'Corolla', 'status' => 'Active', 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('vehicle_model')->insert($chunk);
        }
    }
}
