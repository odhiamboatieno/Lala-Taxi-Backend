<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripTollReasonsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 2, 'trip_id' => 44, 'reason' => 'Luggage', 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('trip_toll_reasons')->insert($chunk);
        }
    }
}
