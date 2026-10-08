<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Module seeders are not run on normal upgrades. Register only this
        // feature's permission; never replace an existing role's permissions.
        DB::transaction(function () {
            $permission = Permission::firstOrCreate(
                ['name' => 'manage_company_accounts_invoicing'],
                ['display_name' => 'Corporate accounts and invoicing',
                    'description' => 'Manage corporate employer accounts, membership and invoices.']
            );
            $role = Role::where('name', 'admin')->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        });
    }

    public function down(): void
    {
        // Retain grants on rollback: removing them would strand retained
        // corporate invoices. Administrators can explicitly revoke access.
    }
};
