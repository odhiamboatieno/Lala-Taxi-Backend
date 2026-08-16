<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySaasSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'is_saas_enabled' => 1, 'dispatch_mode' => 'saas', 'use_own_locations' => 1, 'use_own_fares' => 1, 'use_own_fees' => 1, 'use_own_vehicle_types' => 1, 'use_own_dispatch_settings' => 1, 'use_own_branding' => 1, 'domain' => 'taximate.co.uk', 'subdomain' => 'taximate', 'status' => 'Active', 'created_at' => '2026-05-20 20:20:16', 'updated_at' => '2026-05-20 20:20:16'],
            ['id' => 2, 'company_id' => 4, 'is_saas_enabled' => 1, 'dispatch_mode' => 'global', 'use_own_locations' => 1, 'use_own_fares' => 1, 'use_own_fees' => 1, 'use_own_vehicle_types' => 1, 'use_own_dispatch_settings' => 1, 'use_own_branding' => 1, 'domain' => 'taximate.co.uk', 'subdomain' => 'taximate', 'status' => 'Active', 'created_at' => '2026-06-05 15:03:46', 'updated_at' => '2026-06-05 15:03:46'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_saas_settings')->insert($chunk);
        }
    }
}
