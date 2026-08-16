<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10002, 'company_id' => 1, 'vehicle_make_id' => 2, 'vehicle_model_id' => 3, 'vehicle_id' => '1,2,3,4', 'vehicle_type' => 'Jiji Comfort,Jiji Economy,Jiji Family,JiJi Business', 'vehicle_name' => 'Hyundai Elite i20', 'vehicle_number' => 'DKR 882S', 'is_active' => 1, 'year' => '1996', 'color' => 'Yellow', 'default_type' => '0', 'status' => 'Active'],
            ['id' => 2, 'user_id' => 10003, 'company_id' => 1, 'vehicle_make_id' => 3, 'vehicle_model_id' => 6, 'vehicle_id' => '1,2,3,4', 'vehicle_type' => 'Comfort,Economy,Family,Business', 'vehicle_name' => 'Nissan Sunny', 'vehicle_number' => 'KCB 2822', 'is_active' => 1, 'year' => '2015', 'color' => 'White', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 3, 'user_id' => 10002, 'company_id' => 1, 'vehicle_make_id' => 3, 'vehicle_model_id' => 5, 'vehicle_id' => '1,2,3,4', 'vehicle_type' => 'Jiji Comfort,Jiji Economy,Jiji Family,JiJi Business', 'vehicle_name' => 'Nissan Terrano', 'vehicle_number' => 'KCv454D', 'is_active' => 1, 'year' => '1993', 'color' => 'black', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 4, 'user_id' => 10005, 'company_id' => 1, 'vehicle_make_id' => 3, 'vehicle_model_id' => 5, 'vehicle_id' => '1,2,3,4', 'vehicle_type' => 'Jiji Comfort,Jiji Economy,Jiji Family,JiJi Business', 'vehicle_name' => 'Nissan Terrano', 'vehicle_number' => 'KVD3443D', 'is_active' => 1, 'year' => '2023', 'color' => 'reb', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 5, 'user_id' => 10009, 'company_id' => 1, 'vehicle_make_id' => 3, 'vehicle_model_id' => 5, 'vehicle_id' => '1,2,3,4,5', 'vehicle_type' => 'Comfort,Economy,Family, Business,Flash', 'vehicle_name' => 'Nissan Terrano', 'vehicle_number' => 'KRT435V', 'is_active' => 0, 'year' => '2016', 'color' => 'red', 'default_type' => '0', 'status' => 'Inactive'],
            ['id' => 6, 'user_id' => 10009, 'company_id' => 1, 'vehicle_make_id' => 3, 'vehicle_model_id' => 6, 'vehicle_id' => '1', 'vehicle_type' => 'Comfort', 'vehicle_name' => 'Nissan Sunny', 'vehicle_number' => 'KDT435T', 'is_active' => 0, 'year' => '2017', 'color' => 'white', 'default_type' => '0', 'status' => 'Inactive'],
            ['id' => 7, 'user_id' => 10012, 'company_id' => 1, 'vehicle_make_id' => 51, 'vehicle_model_id' => 8, 'vehicle_id' => '1,2,3,4,5', 'vehicle_type' => 'Comfort,Economy,Family, Business,Flash', 'vehicle_name' => 'Toyota Corolla', 'vehicle_number' => 'HG 33 JU GP', 'is_active' => 1, 'year' => '2014', 'color' => 'Blues', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 8, 'user_id' => 10014, 'company_id' => 1, 'vehicle_make_id' => 51, 'vehicle_model_id' => 8, 'vehicle_id' => '1,2,3,4,5', 'vehicle_type' => 'Comfort,Economy,Family, Business,Flash', 'vehicle_name' => 'Toyota Corolla', 'vehicle_number' => 'KX 97 TG GP', 'is_active' => 1, 'year' => '2023', 'color' => 'Charcoal', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 9, 'user_id' => 10019, 'company_id' => 1, 'vehicle_make_id' => 1, 'vehicle_model_id' => 1, 'vehicle_id' => '1', 'vehicle_type' => 'LalaTaxi Comfort', 'vehicle_name' => 'Maruti Swift', 'vehicle_number' => 'KDQ456T', 'is_active' => 1, 'year' => '2023', 'color' => 'white', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 10, 'user_id' => 10021, 'company_id' => 1, 'vehicle_make_id' => 2, 'vehicle_model_id' => 3, 'vehicle_id' => '1,2,3', 'vehicle_type' => 'LalaTaxi Comfort,LalaTaxi Economy,LalaTaxi Family', 'vehicle_name' => 'Hyundai Elite i20', 'vehicle_number' => 'KDT254T', 'is_active' => 1, 'year' => '2023', 'color' => 'white', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 11, 'user_id' => 10023, 'company_id' => 3, 'vehicle_make_id' => 1, 'vehicle_model_id' => 2, 'vehicle_id' => '1', 'vehicle_type' => 'LalaTaxi Comfort', 'vehicle_name' => 'Maruti Wagon R', 'vehicle_number' => 'KDP516T', 'is_active' => 1, 'year' => '2023', 'color' => 'red', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 12, 'user_id' => 10024, 'company_id' => 3, 'vehicle_make_id' => 1, 'vehicle_model_id' => 1, 'vehicle_id' => '1,2,3,4', 'vehicle_type' => 'LalaTaxi Comfort,LalaTaxi Economy,LalaTaxi Family,LalaTaxi Business', 'vehicle_name' => 'Maruti Swift', 'vehicle_number' => 'KDT514R', 'is_active' => 1, 'year' => '2023', 'color' => 'black', 'default_type' => '1', 'status' => 'Active'],
            ['id' => 13, 'user_id' => 10026, 'company_id' => 4, 'vehicle_make_id' => 1, 'vehicle_model_id' => 1, 'vehicle_id' => '1,2,3,4,5', 'vehicle_type' => 'LalaTaxi Comfort,LalaTaxi Economy,LalaTaxi Family,LalaTaxi Business,LalaTaxi Flash', 'vehicle_name' => 'Maruti Swift', 'vehicle_number' => 'KDP387T', 'is_active' => 1, 'year' => '2023', 'color' => 'red', 'default_type' => '1', 'status' => 'Active'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('vehicle')->insert($chunk);
        }
    }
}
