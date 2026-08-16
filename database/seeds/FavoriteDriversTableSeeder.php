<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteDriversTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'rider_id' => 10010, 'driver_id' => 10003, 'created_at' => '2026-05-11 17:48:54', 'updated_at' => '2026-05-11 17:48:54'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('favorite_drivers')->insert($chunk);
        }
    }
}
