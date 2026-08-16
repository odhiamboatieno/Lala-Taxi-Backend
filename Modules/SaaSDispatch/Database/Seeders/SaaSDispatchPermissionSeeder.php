<?php

namespace Modules\SaaSDispatch\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaaSDispatchPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionMap = [
            'manage_saas_module',
            'manage_saas_companies',
            'manage_global_vehicle_categories',
            'manage_company_service_areas',
            'manage_company_vehicle_types',
            'manage_company_fares',
            'manage_company_fees',
            'manage_company_dispatch_settings',
            'manage_company_payment_gateways',
            'manage_company_branding',
            'manage_company_domains',
            'manage_company_documents',
            'manage_company_networking',
            'manage_company_accounts_invoicing',
            'manage_company_data_suite',
            'manage_company_messages',
            'manage_company_support_tickets',
        ];

        $permissionIds = [];

        foreach ($permissionMap as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => Str::headline(str_replace('manage_', '', $name)),
                    'description' => 'SaaS Dispatch module permission: '.$name,
                ]
            );

            $permissionIds[] = $permission->id;
        }

        $adminRole = Role::query()->where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
