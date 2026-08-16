<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationTeamMembersTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'role_id' => 1, 'first_name' => 'Lorence', 'last_name' => 'Steveson', 'email' => 'gordonjohnao@gmail.com', 'country_code' => 'KE', 'mobile_number' => '0748977926', 'address' => 'Ngong\' Road', 'identity_type' => 'ID', 'identity_number' => '35635363', 'image' => 'operation-team/9sV80BSyaD6DjclU8d5I3XZ7ZcXEz6ow25cwHyTf.webp', 'identity_image' => 'operation-team/2JDttav8wY2mWjVm0wZdtnrXvmCF3POu53WkUXy9.png', 'password' => '$2y$10$XZtkAlnrSj3a6A1Njv8nguiu/cWYOOouddbuXeclJ21rUsQ/tcdyG', 'status' => 1, 'created_at' => '2026-05-22 18:29:40', 'updated_at' => '2026-05-22 18:30:19'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('operation_team_members')->insert($chunk);
        }
    }
}
