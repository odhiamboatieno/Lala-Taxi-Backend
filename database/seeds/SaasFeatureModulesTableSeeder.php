<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaasFeatureModulesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'slug' => 'company_documents', 'name' => 'Documents Management', 'description' => 'Manage company driver and vehicle documents.', 'is_installed' => 1, 'is_enabled' => 1, 'sort_order' => 10, 'status' => 'Active', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-06-07 08:43:44'],
            ['id' => 2, 'slug' => 'networking', 'name' => 'Networking', 'description' => 'Share overflow work with partner fleets or receive jobs from them.', 'is_installed' => 1, 'is_enabled' => 1, 'sort_order' => 20, 'status' => 'Active', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-06-07 08:43:53'],
            ['id' => 3, 'slug' => 'accounts_invoicing', 'name' => 'Accounts / Invoicing', 'description' => 'Corporate accounts, split invoices, and customer billing.', 'is_installed' => 1, 'is_enabled' => 1, 'sort_order' => 30, 'status' => 'Active', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-06-07 08:43:59'],
            ['id' => 4, 'slug' => 'data_suite', 'name' => 'Data Suite', 'description' => 'Fleet performance, passenger behavior, and earnings analytics.', 'is_installed' => 1, 'is_enabled' => 1, 'sort_order' => 40, 'status' => 'Active', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-06-07 08:44:05'],
            ['id' => 5, 'slug' => 'messages', 'name' => 'Messages', 'description' => 'Centralized communication hub for drivers and staff.', 'is_installed' => 1, 'is_enabled' => 1, 'sort_order' => 50, 'status' => 'Active', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-06-07 08:44:10'],
            ['id' => 6, 'slug' => 'support_system_status', 'name' => 'Support & System Status', 'description' => 'Advanced ticketing between companies and platform admin.', 'is_installed' => 1, 'is_enabled' => 0, 'sort_order' => 60, 'status' => 'Inactive', 'created_at' => '2026-05-23 18:59:59', 'updated_at' => '2026-05-25 11:12:30'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('saas_feature_modules')->insert($chunk);
        }
    }
}
