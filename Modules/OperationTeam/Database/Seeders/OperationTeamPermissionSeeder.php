<?php

namespace Modules\OperationTeam\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OperationTeamPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_operation_team_roles',
            'manage_operation_team_members',
            'manage_operation_team_dashboard',
            'manage_company_surge_pricing',
        ];

        $ids = [];

        foreach ($permissions as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => Str::headline(str_replace('manage_', '', $name)),
                    'description' => 'Operation team or company surge permission: '.$name,
                ]
            );

            $ids[] = $permission->id;
        }

        $adminRole = Role::query()->where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($ids);
        }
    }
}
