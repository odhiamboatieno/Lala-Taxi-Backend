<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TollReasonsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'reason' => 'Other Fees', 'status' => 'Active'],
            ['id' => 2, 'reason' => 'Express Way', 'status' => 'Active'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('toll_reasons')->insert($chunk);
        }
    }
}
