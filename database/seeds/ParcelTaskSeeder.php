<?php

use Illuminate\Database\Seeder;

class ParcelTaskSeeder extends Seeder
{
    public function run()
    {
        DB::table('parcel_tasks')->truncate();

        $tasks = [
            ['key' => 'documents', 'title' => 'Documents', 'sort_order' => 1],
            ['key' => 'groceries', 'title' => 'Groceries', 'sort_order' => 2],
            ['key' => 'clothes', 'title' => 'Clothes', 'sort_order' => 3],
            ['key' => 'electronics', 'title' => 'Electronics', 'sort_order' => 4],
            ['key' => 'food_delivery', 'title' => 'Food Delivery', 'sort_order' => 5],
            ['key' => 'furniture', 'title' => 'Furniture', 'sort_order' => 6],
            ['key' => 'pharmacy', 'title' => 'Pharmacy', 'sort_order' => 7],
            ['key' => 'keys', 'title' => 'Keys', 'sort_order' => 8],
            ['key' => 'packages', 'title' => 'Packages', 'sort_order' => 9],
            ['key' => 'other', 'title' => 'Other', 'sort_order' => 10],
        ];

        $insertData = [];
        foreach ($tasks as $task) {
            $insertData[] = [
                'key' => $task['key'],
                'title' => $task['title'],
                'icon_path' => null,
                'sort_order' => $task['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('parcel_tasks')->insert($insertData);
    }
}
