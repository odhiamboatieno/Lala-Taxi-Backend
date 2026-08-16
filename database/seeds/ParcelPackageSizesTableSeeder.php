<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelPackageSizesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'key' => 'small', 'title' => 'Small', 'subtitle' => '(paper)', 'description' => 'Documents, letters, small items', 'price_multiplier' => 1.0, 'sort_order' => 1, 'is_active' => 1, 'created_at' => '2026-05-16 08:08:12', 'updated_at' => '2026-05-16 07:43:51'],
            ['id' => 2, 'key' => 'medium', 'title' => 'Medium', 'subtitle' => '(box)', 'description' => 'Shoe boxes, small packages', 'price_multiplier' => 1.25, 'sort_order' => 2, 'is_active' => 1, 'created_at' => '2026-05-16 08:08:12', 'updated_at' => '2026-05-16 08:08:12'],
            ['id' => 3, 'key' => 'large', 'title' => 'Large', 'subtitle' => '(luggage)', 'description' => 'Large boxes, luggage, bulky items', 'price_multiplier' => 1.5, 'sort_order' => 3, 'is_active' => 1, 'created_at' => '2026-05-16 08:08:12', 'updated_at' => '2026-05-16 08:08:12'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('parcel_package_sizes')->insert($chunk);
        }
    }
}
