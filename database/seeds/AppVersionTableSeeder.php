<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppVersionTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'version' => '1.0', 'device_type' => 2, 'user_type' => 0, 'force_update' => 1, 'created_at' => '2025-10-29 20:52:34', 'updated_at' => '2025-10-29 20:52:34'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('app_version')->insert($chunk);
        }
    }
}
