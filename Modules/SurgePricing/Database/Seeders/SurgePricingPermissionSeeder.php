<?php

namespace Modules\SurgePricing\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SurgePricingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['manage_surge_pricing', 'manage_company_surge_pricing'];
        $ids = [];

        foreach ($names as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => Str::headline(str_replace('manage_', '', $name)),
                    'description' => 'Surge pricing permission: '.$name,
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
