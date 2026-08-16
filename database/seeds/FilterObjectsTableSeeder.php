<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterObjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'type' => 'vehicle', 'object_id' => 1, 'filter_id' => 2, 'created_at' => '2021-12-18 14:58:33', 'updated_at' => '2021-12-18 14:58:33'],
            ['id' => 2, 'type' => 'vehicle', 'object_id' => 1, 'filter_id' => 3, 'created_at' => '2021-12-18 14:58:33', 'updated_at' => '2021-12-18 14:58:33'],
            ['id' => 3, 'type' => 'vehicle', 'object_id' => 2, 'filter_id' => 2, 'created_at' => '2022-05-18 04:21:16', 'updated_at' => '2022-05-18 04:21:16'],
            ['id' => 4, 'type' => 'vehicle', 'object_id' => 2, 'filter_id' => 3, 'created_at' => '2022-05-18 04:21:16', 'updated_at' => '2022-05-18 04:21:16'],
            ['id' => 5, 'type' => 'vehicle', 'object_id' => 3, 'filter_id' => 2, 'created_at' => '2023-11-30 17:13:51', 'updated_at' => '2023-11-30 17:13:51'],
            ['id' => 6, 'type' => 'vehicle', 'object_id' => 3, 'filter_id' => 3, 'created_at' => '2023-11-30 17:13:51', 'updated_at' => '2023-11-30 17:13:51'],
            ['id' => 7, 'type' => 'vehicle', 'object_id' => 4, 'filter_id' => 2, 'created_at' => '2023-11-30 17:24:01', 'updated_at' => '2023-11-30 17:24:01'],
            ['id' => 8, 'type' => 'vehicle', 'object_id' => 4, 'filter_id' => 3, 'created_at' => '2023-11-30 17:24:01', 'updated_at' => '2023-11-30 17:24:01'],
            ['id' => 9, 'type' => 'vehicle', 'object_id' => 5, 'filter_id' => 2, 'created_at' => '2025-09-13 19:31:29', 'updated_at' => '2025-09-13 19:31:29'],
            ['id' => 10, 'type' => 'vehicle', 'object_id' => 5, 'filter_id' => 3, 'created_at' => '2025-09-13 19:31:29', 'updated_at' => '2025-09-13 19:31:29'],
            ['id' => 11, 'type' => 'vehicle', 'object_id' => 6, 'filter_id' => 2, 'created_at' => '2025-09-14 11:34:15', 'updated_at' => '2025-09-14 11:34:15'],
            ['id' => 12, 'type' => 'vehicle', 'object_id' => 6, 'filter_id' => 3, 'created_at' => '2025-09-14 11:34:15', 'updated_at' => '2025-09-14 11:34:15'],
            ['id' => 13, 'type' => 'vehicle', 'object_id' => 7, 'filter_id' => 2, 'created_at' => '2025-09-14 11:38:02', 'updated_at' => '2025-09-14 11:38:02'],
            ['id' => 14, 'type' => 'vehicle', 'object_id' => 7, 'filter_id' => 3, 'created_at' => '2025-09-14 11:38:02', 'updated_at' => '2025-09-14 11:38:02'],
            ['id' => 15, 'type' => 'vehicle', 'object_id' => 8, 'filter_id' => 2, 'created_at' => '2025-09-14 18:39:37', 'updated_at' => '2025-09-14 18:39:37'],
            ['id' => 16, 'type' => 'vehicle', 'object_id' => 8, 'filter_id' => 3, 'created_at' => '2025-09-14 18:39:37', 'updated_at' => '2025-09-14 18:39:37'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('filter_objects')->insert($chunk);
        }
    }
}
