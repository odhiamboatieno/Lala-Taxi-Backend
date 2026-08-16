<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'car_name' => 'LalaTaxi Comfort', 'description' => 'Red Comfort', 'vehicle_image' => 'vehicle_image_1778406767.png', 'active_image' => 'active_image_1778406767.png', 'is_pool' => 'No', 'status' => 'Active', 'price_per_km' => 15.0],
            ['id' => 2, 'car_name' => 'LalaTaxi Economy', 'description' => 'Red Economy', 'vehicle_image' => 'vehicle_image_1778406795.png', 'active_image' => 'active_image_1778406795.png', 'is_pool' => 'Yes', 'status' => 'Active', 'price_per_km' => 15.0],
            ['id' => 3, 'car_name' => 'LalaTaxi Family', 'description' => 'Red Family', 'vehicle_image' => 'vehicle_image_1778406961.png', 'active_image' => 'active_image_1778406961.png', 'is_pool' => 'No', 'status' => 'Active', 'price_per_km' => 15.0],
            ['id' => 4, 'car_name' => 'LalaTaxi Business', 'description' => 'Red Business', 'vehicle_image' => 'vehicle_image_1778406817.png', 'active_image' => 'active_image_1778406817.png', 'is_pool' => 'No', 'status' => 'Active', 'price_per_km' => 15.0],
            ['id' => 5, 'car_name' => 'LalaTaxi Flash', 'description' => 'Fast and Efficient Bike Ride', 'vehicle_image' => 'vehicle_image_1778406977.png', 'active_image' => 'active_image_1778406977.png', 'is_pool' => 'No', 'status' => 'Active', 'price_per_km' => 15.0],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('car_type')->insert($chunk);
        }
    }
}
