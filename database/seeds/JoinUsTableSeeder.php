<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JoinUsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'facebook', 'value' => '#'],
            ['id' => 2, 'name' => 'google_plus', 'value' => ''],
            ['id' => 3, 'name' => 'twitter', 'value' => '#'],
            ['id' => 4, 'name' => 'linkedin', 'value' => '#'],
            ['id' => 5, 'name' => 'pinterest', 'value' => '#'],
            ['id' => 6, 'name' => 'youtube', 'value' => '#'],
            ['id' => 7, 'name' => 'instagram', 'value' => '#'],
            ['id' => 8, 'name' => 'app_store_rider', 'value' => '#'],
            ['id' => 9, 'name' => 'app_store_driver', 'value' => '#'],
            ['id' => 10, 'name' => 'play_store_rider', 'value' => '#'],
            ['id' => 11, 'name' => 'play_store_driver', 'value' => '#'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('join_us')->insert($chunk);
        }
    }
}
