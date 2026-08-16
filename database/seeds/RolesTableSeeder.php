<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin', 'created_at' => '2021-12-18 12:35:24', 'updated_at' => '2021-12-18 12:35:24'],
            ['id' => 2, 'name' => 'dispatcher', 'display_name' => 'Dispatcher', 'description' => 'Dispatcher', 'created_at' => '2021-12-18 12:35:24', 'updated_at' => '2021-12-18 12:35:24'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('roles')->insert($chunk);
        }
    }
}
