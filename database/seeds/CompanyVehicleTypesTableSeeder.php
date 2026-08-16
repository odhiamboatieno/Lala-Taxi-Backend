<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyVehicleTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'global_vehicle_category_id' => 2, 'name' => '1-3 Passengers', 'slug' => '3 passengers', 'description' => null, 'is_pool' => 'Yes', 'icon' => null, 'vehicle_image' => 'vehicle_image_1780840613.png', 'active_image' => 'active_image_1780840613.png', 'app_image' => 'app_image_1780840613.png', 'passenger_capacity' => 3, 'luggage_capacity' => 1, 'is_ev' => 0, 'is_accessible' => 0, 'sort_order' => 0, 'status' => 'Active', 'created_at' => '2026-06-07 11:56:53', 'updated_at' => '2026-06-07 11:56:53'],
            ['id' => 2, 'company_id' => 4, 'global_vehicle_category_id' => 1, 'name' => '4 Passengers', 'slug' => '4 Passengers', 'description' => null, 'is_pool' => 'No', 'icon' => null, 'vehicle_image' => 'vehicle_image_1780840734.png', 'active_image' => 'active_image_1780840734.png', 'app_image' => 'app_image_1780840734.png', 'passenger_capacity' => 4, 'luggage_capacity' => 3, 'is_ev' => 0, 'is_accessible' => 0, 'sort_order' => 0, 'status' => 'Active', 'created_at' => '2026-06-07 11:58:54', 'updated_at' => '2026-06-07 11:58:54'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_vehicle_types')->insert($chunk);
        }
    }
}
