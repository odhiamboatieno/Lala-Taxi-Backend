<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'key' => 'small', 'title' => 'Small Parcel', 'description' => 'Documents, envelopes, small packages up to 5kg', 'icon_path' => 'images/parcel_icons/parcel_cat_1778887642.png', 'base_fare' => 50.0, 'multiplier' => 1.0, 'sort_order' => 1, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-27 18:49:58'],
            ['id' => 2, 'key' => 'medium', 'title' => 'Medium Parcel', 'description' => 'Boxes, medium packages up to 15kg', 'icon_path' => 'images/parcel_icons/parcel_cat_1778887631.png', 'base_fare' => 100.0, 'multiplier' => 1.5, 'sort_order' => 2, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 20:27:11'],
            ['id' => 3, 'key' => 'large', 'title' => 'Large Parcel', 'description' => 'Furniture, large items up to 50kg', 'icon_path' => 'images/parcel_icons/parcel_cat_1778887619.png', 'base_fare' => 200.0, 'multiplier' => 2.0, 'sort_order' => 3, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 20:26:59'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('parcel_categories')->insert($chunk);
        }
    }
}
