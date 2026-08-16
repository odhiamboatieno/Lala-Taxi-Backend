<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelTasksTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'key' => 'pick_up', 'title' => 'Pick Up', 'icon_path' => null, 'sort_order' => 1, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 2, 'key' => 'deliver', 'title' => 'Deliver', 'icon_path' => null, 'sort_order' => 2, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 3, 'key' => 'wait', 'title' => 'Wait', 'icon_path' => null, 'sort_order' => 3, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 4, 'key' => 'return', 'title' => 'Return', 'icon_path' => null, 'sort_order' => 4, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 5, 'key' => 'buy_for_me', 'title' => 'Buy For Me', 'icon_path' => null, 'sort_order' => 5, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 6, 'key' => 'queue', 'title' => 'Queue', 'icon_path' => null, 'sort_order' => 6, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 7, 'key' => 'drop_off', 'title' => 'Drop Off', 'icon_path' => null, 'sort_order' => 7, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 8, 'key' => 'signature', 'title' => 'Get Signature', 'icon_path' => null, 'sort_order' => 8, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 9, 'key' => 'photo', 'title' => 'Take Photo', 'icon_path' => null, 'sort_order' => 9, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 10, 'key' => 'confirm', 'title' => 'Confirm Delivery', 'icon_path' => null, 'sort_order' => 10, 'is_active' => 1, 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:56:10'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('parcel_tasks')->insert($chunk);
        }
    }
}
