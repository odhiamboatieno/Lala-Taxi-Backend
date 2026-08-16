<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleMakeTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'make_vehicle_name' => 'Maruti', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 2, 'make_vehicle_name' => 'Hyundai', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 3, 'make_vehicle_name' => 'Nissan', 'status' => 'Active', 'created_at' => '2021-12-18 12:35:23', 'updated_at' => '2021-12-18 12:35:23'],
            ['id' => 10, 'make_vehicle_name' => 'Abarth', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 11, 'make_vehicle_name' => 'Alfa Romeo', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 12, 'make_vehicle_name' => 'Aston Martin', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 13, 'make_vehicle_name' => 'Audi', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 14, 'make_vehicle_name' => 'BAIC', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 15, 'make_vehicle_name' => 'Bentley', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 16, 'make_vehicle_name' => 'BMW', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 17, 'make_vehicle_name' => 'Changan', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 18, 'make_vehicle_name' => 'Datsun', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 19, 'make_vehicle_name' => 'Fiat', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 20, 'make_vehicle_name' => 'Ford', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 21, 'make_vehicle_name' => 'GWM', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 22, 'make_vehicle_name' => 'Haval', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 23, 'make_vehicle_name' => 'Honda', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 24, 'make_vehicle_name' => 'Hyundai', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 25, 'make_vehicle_name' => 'Isuzu', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 26, 'make_vehicle_name' => 'JAC', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 27, 'make_vehicle_name' => 'Jaguar', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 28, 'make_vehicle_name' => 'Jeep', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 29, 'make_vehicle_name' => 'JMC', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 30, 'make_vehicle_name' => 'Kia', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 31, 'make_vehicle_name' => 'Lamborghini', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 32, 'make_vehicle_name' => 'Land Rover', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 33, 'make_vehicle_name' => 'Lexus', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 34, 'make_vehicle_name' => 'Mahindra', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 35, 'make_vehicle_name' => 'Mazda', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 36, 'make_vehicle_name' => 'McLaren', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 37, 'make_vehicle_name' => 'Mercedes-AMG', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 38, 'make_vehicle_name' => 'Mercedes-Benz', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 39, 'make_vehicle_name' => 'Mercedes-Maybach', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 40, 'make_vehicle_name' => 'MINI', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 41, 'make_vehicle_name' => 'Mitsubishi', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 42, 'make_vehicle_name' => 'Nissan', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 43, 'make_vehicle_name' => 'Opel', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 44, 'make_vehicle_name' => 'Peugeot', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 45, 'make_vehicle_name' => 'Porsche', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 46, 'make_vehicle_name' => 'Renault', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 47, 'make_vehicle_name' => 'smart', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 48, 'make_vehicle_name' => 'Subaru', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 49, 'make_vehicle_name' => 'Suzuki', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 50, 'make_vehicle_name' => 'Tata', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 51, 'make_vehicle_name' => 'Toyota', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 52, 'make_vehicle_name' => 'Volkswagen', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
            ['id' => 53, 'make_vehicle_name' => 'Volvo', 'status' => 'Active', 'created_at' => '2021-12-18 10:35:23', 'updated_at' => '2021-12-18 10:35:23'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('vehicle_make')->insert($chunk);
        }
    }
}
