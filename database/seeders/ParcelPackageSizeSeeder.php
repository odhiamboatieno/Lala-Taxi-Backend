<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParcelPackageSize;

class ParcelPackageSizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            [
                'key' => 'small',
                'title' => 'Small',
                'subtitle' => '(envelope)',
                'description' => 'Documents, letters, small items',
                'price_multiplier' => 1.00,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'medium',
                'title' => 'Medium',
                'subtitle' => '(box)',
                'description' => 'Shoe boxes, small packages',
                'price_multiplier' => 1.25,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'large',
                'title' => 'Large',
                'subtitle' => '(luggage)',
                'description' => 'Large boxes, luggage, bulky items',
                'price_multiplier' => 1.50,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($sizes as $size) {
            ParcelPackageSize::updateOrCreate(
                ['key' => $size['key']],
                $size
            );
        }
    }
}
