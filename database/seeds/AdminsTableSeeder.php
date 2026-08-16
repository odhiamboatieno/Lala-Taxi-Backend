<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@redkm.co.za', 'password' => '$2y$10$P6prTl28zCoUFtU6q4ZtGu7gbgsSczzA4A.eHeb/t7XpdH6LlV9Am', 'country_code' => '44', 'country_id' => 226, 'mobile_number' => '254703803051', 'remember_token' => null, 'status' => 'Active', 'created_at' => '2021-12-18 12:35:20', 'updated_at' => '2025-09-13 21:17:30', 'deleted_at' => null],
            ['id' => 2, 'username' => 'superadmin', 'email' => 'superadmin@admin.com', 'password' => '$2y$10$23M2AcD/1xIy.BH3aETTS.PsmXH7fv41ZZqlKSESDMU41fXQCd2/W', 'country_code' => '254', 'country_id' => 110, 'mobile_number' => '748977926', 'remember_token' => null, 'status' => 'Active', 'created_at' => '2025-09-22 16:49:15', 'updated_at' => '2025-09-22 16:49:15', 'deleted_at' => null],
            ['id' => 3, 'username' => 'gordonjohnao', 'email' => 'admin@querysofts.com', 'password' => '$2y$10$nObrfEvjrPNBRK4FJu8sFe70Wr4pHX3wwl1fiBHtOD6LBql1s13ay', 'country_code' => '254', 'country_id' => 110, 'mobile_number' => '748977926', 'remember_token' => null, 'status' => 'Active', 'created_at' => '2025-10-29 19:25:21', 'updated_at' => '2025-10-29 19:25:21', 'deleted_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('admins')->insert($chunk);
        }
    }
}
