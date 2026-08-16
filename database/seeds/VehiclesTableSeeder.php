<?php

use Illuminate\Database\Seeder;

class VehiclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('car_type')->insert([
            ['id'=>'1', 'car_name' =>'Micro', 'description' =>'Micro', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'CabmeGo.png', 'active_image' =>'CabmeGo_blue.png'],
            ['id'=>'2', 'car_name' =>'Mini', 'description' =>'Mini', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'CabmeX.png', 'active_image' =>'CabmeX_Blue.png'],
            ['id'=>'3', 'car_name' =>'Prime', 'description' =>'Prime', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'CabmeXL.png', 'active_image' =>'CabmeXL_Blue.png'],
            ['id'=>'4', 'car_name' =>'POOL', 'description' =>'POOL', 'is_pool' => "Yes", 'status' =>'Active', 'vehicle_image'=> 'Cabmepool_black.png', 'active_image' =>'Cabmepool.png'],
        ]);
    }
}
