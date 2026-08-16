<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmergencySosTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10004, 'name' => 'Gordon Johnson', 'country_code' => '254', 'mobile_number' => '790873585', 'country_id' => 110],
            ['id' => 3, 'user_id' => 10003, 'name' => 'Thomas', 'country_code' => '254', 'mobile_number' => '797049288', 'country_id' => 110],
            ['id' => 4, 'user_id' => 10018, 'name' => 'James Gordon', 'country_code' => '254', 'mobile_number' => '0700781342', 'country_id' => 110],
            ['id' => 9, 'user_id' => 10010, 'name' => '0wenda', 'country_code' => '254', 'mobile_number' => '711904293', 'country_id' => 110],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('emergency_sos')->insert($chunk);
        }
    }
}
