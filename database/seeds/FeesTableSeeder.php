<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'access_fee', 'value' => '10'],
            ['id' => 2, 'name' => 'driver_peak_fare', 'value' => '70'],
            ['id' => 3, 'name' => 'driver_access_fee', 'value' => '10'],
            ['id' => 4, 'name' => 'additional_fee', 'value' => 'Yes'],
            ['id' => 5, 'name' => 'additional_rider_fare', 'value' => '75'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('fees')->insert($chunk);
        }
    }
}
