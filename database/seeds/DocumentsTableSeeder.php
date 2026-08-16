<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'document_name' => 'License', 'type' => 'Driver', 'country_code' => 'all', 'expire_on_date' => 'Yes', 'status' => 'Active', 'created_at' => '2021-12-18 15:00:05', 'updated_at' => '2021-12-18 15:00:05'],
            ['id' => 2, 'document_name' => 'Insurance', 'type' => 'Vehicle', 'country_code' => 'all', 'expire_on_date' => 'Yes', 'status' => 'Active', 'created_at' => '2021-12-18 15:00:20', 'updated_at' => '2021-12-18 15:00:20'],
            ['id' => 3, 'document_name' => 'Insurence', 'type' => 'Driver', 'country_code' => 'all', 'expire_on_date' => 'Yes', 'status' => 'Inactive', 'created_at' => '2022-05-20 04:36:06', 'updated_at' => '2022-05-20 04:43:26'],
            ['id' => 4, 'document_name' => 'Insurance', 'type' => 'Company', 'country_code' => '110', 'expire_on_date' => 'No', 'status' => 'Active', 'created_at' => '2025-10-29 20:10:02', 'updated_at' => '2025-10-29 20:10:02'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('documents')->insert($chunk);
        }
    }
}
