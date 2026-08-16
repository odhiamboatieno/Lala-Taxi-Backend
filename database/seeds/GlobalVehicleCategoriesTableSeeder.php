<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalVehicleCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Economy', 'slug' => 'economy', 'description' => 'Economy Cars', 'icon' => null, 'is_visible_to_rider' => 1, 'sort_order' => 0, 'status' => 'Active', 'created_at' => '2026-05-20 21:22:57', 'updated_at' => '2026-05-20 21:22:57'],
            ['id' => 2, 'name' => 'Comfort', 'slug' => 'Comfort', 'description' => 'Comfort', 'icon' => null, 'is_visible_to_rider' => 1, 'sort_order' => 0, 'status' => 'Active', 'created_at' => '2026-05-20 21:23:31', 'updated_at' => '2026-05-20 21:23:31'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('global_vehicle_categories')->insert($chunk);
        }
    }
}
