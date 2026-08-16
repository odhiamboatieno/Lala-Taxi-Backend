<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Whatsapp', 'link' => '0786700522', 'status' => 'Active', 'Owner' => 'All', 'image' => 'whatsapp.png', 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'name' => 'Skype', 'link' => '4', 'status' => 'Inactive', 'Owner' => 'All', 'image' => 'skype.jpeg', 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'name' => 'Delete Account', 'link' => '/rider_deletion', 'status' => 'Active', 'Owner' => 'Rider', 'image' => 'category-image1668441212.png', 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'name' => 'Delete Account', 'link' => '/driver_deletion', 'status' => 'Active', 'Owner' => 'Driver', 'image' => 'category-image1675754098.png', 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supports')->insert($chunk);
        }
    }
}
