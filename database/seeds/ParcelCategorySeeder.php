<?php

use Illuminate\Database\Seeder;

class ParcelCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('parcel_categories')->truncate();

        DB::table('parcel_categories')->insert([
            [
                'key' => 'small',
                'title' => 'Small Package',
                'description' => 'Documents, gifts, and light packages (up to 5kg)',
                'icon_path' => null,
                'base_fare' => 100.00,
                'multiplier' => 1.00,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'large',
                'title' => 'Large Package',
                'description' => 'Boxes, shopping bags, and larger items (5-25kg)',
                'icon_path' => null,
                'base_fare' => 250.00,
                'multiplier' => 1.50,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'truck',
                'title' => 'Furniture / Truck',
                'description' => 'Bulky or heavy delivery needs (25kg+)',
                'icon_path' => null,
                'base_fare' => 600.00,
                'multiplier' => 2.50,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
