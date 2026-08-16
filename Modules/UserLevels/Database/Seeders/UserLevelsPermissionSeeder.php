<?php

namespace Modules\UserLevels\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserLevelsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_driver_levels',
            'manage_rider_levels',
        ];

        $permissionIds = [];

        foreach ($permissions as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => Str::headline(str_replace('manage_', '', $name)),
                    'description' => 'User levels module permission: '.$name,
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
