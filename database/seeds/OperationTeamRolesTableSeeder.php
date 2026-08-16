<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationTeamRolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 3, 'name' => 'Manager', 'permissions' => '["manage_operation_team_dashboard","manage_locations","manage_trips","manage_requests","manage_rating","manage_parcel_categories","manage_parcel_package_sizes","manage_parcel_tasks","manage_parcel_pricing","manage_parcel_settings","manage_parcel_trips","manage_promo_code","manage_vehicle","manage_vehicle_type","view_vehicle_make","view_vehicle_model","manage_company_vehicle_types","manage_company_fares","manage_company_fees","manage_company_dispatch_settings","manage_company_service_areas","manage_company_driver_fees","manage_company_surge_pricing","view_driver","create_driver","update_driver","delete_driver","view_rider","create_rider","update_rider","delete_rider","manage_driver_levels","manage_rider_levels","manage_payments","manage_statements","manage_driver_payments","manage_owe_amount","manage_saas_module","manage_company_branding","manage_company_domains","manage_operation_team_roles","manage_operation_team_members","manage_support","manage_send_message"]', 'status' => 1, 'created_at' => '2026-05-22 18:26:37', 'updated_at' => '2026-05-22 18:26:37'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('operation_team_roles')->insert($chunk);
        }
    }
}
