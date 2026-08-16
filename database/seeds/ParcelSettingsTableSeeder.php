<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcelSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'key' => 'platform_fee_percent', 'value' => '10', 'type' => 'int', 'description' => 'Platform service fee percentage', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 2, 'key' => 'request_timeout', 'value' => '120', 'type' => 'int', 'description' => 'Request timeout in seconds', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 3, 'key' => 'signature_required', 'value' => 'true', 'type' => 'bool', 'description' => 'Signature required for POD', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 4, 'key' => 'photo_required', 'value' => 'true', 'type' => 'bool', 'description' => 'Photo required for POD', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 5, 'key' => 'max_weight_kg', 'value' => '50', 'type' => 'int', 'description' => 'Max package weight in kg', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
            ['id' => 6, 'key' => 'driver_commission_percent', 'value' => '90', 'type' => 'int', 'description' => 'Driver commission percentage', 'created_at' => '2026-05-15 19:13:18', 'updated_at' => '2026-05-15 19:13:18'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('parcel_settings')->insert($chunk);
        }
    }
}
