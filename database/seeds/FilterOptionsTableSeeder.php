<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterOptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Prefer Female Riders only', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 2, 'name' => 'Prefer Handicap Accessibility', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 3, 'name' => 'Prefer Child Seat Accessibility', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
            ['id' => 4, 'name' => 'Prefer Female Drivers only', 'created_at' => '2021-12-18 13:35:24', 'updated_at' => '2021-12-18 13:35:24'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('filter_options')->insert($chunk);
        }
    }
}
