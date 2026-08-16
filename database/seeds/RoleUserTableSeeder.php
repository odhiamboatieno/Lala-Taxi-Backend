<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['user_id' => 1, 'role_id' => 1],
            ['user_id' => 2, 'role_id' => 1],
            ['user_id' => 3, 'role_id' => 1],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('role_user')->insert($chunk);
        }
    }
}
