<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CorporatePermissionMigrationTest extends TestCase
{
    public function test_upgrade_registers_only_corporate_access_idempotently_and_preserves_other_roles(): void
    {
        Schema::create('roles', function (Blueprint $t) { $t->increments('id'); $t->string('name'); $t->timestamps(); });
        Schema::create('permissions', function (Blueprint $t) {
            $t->increments('id'); $t->string('name')->unique(); $t->string('display_name'); $t->string('description'); $t->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $t) { $t->unsignedInteger('permission_id'); $t->unsignedInteger('role_id'); });
        Schema::create('role_user', function (Blueprint $t) { $t->unsignedInteger('role_id'); $t->unsignedInteger('user_id'); });
        DB::table('roles')->insert([['id' => 1, 'name' => 'admin'], ['id' => 2, 'name' => 'dispatcher']]);
        DB::table('permissions')->insert(['id' => 1, 'name' => 'existing', 'display_name' => 'Existing', 'description' => 'Existing']);
        DB::table('permission_role')->insert([['permission_id' => 1, 'role_id' => 1], ['permission_id' => 1, 'role_id' => 2]]);
        DB::table('role_user')->insert([['role_id' => 1, 'user_id' => 1], ['role_id' => 2, 'user_id' => 2]]);
        $migration = require database_path('migrations/2026_10_08_000001_register_corporate_management_permission.php');
        $migration->up();
        $migration->up();
        $migration->down();
        $admin = new Admin; $admin->id = 1; $admin->status = 'Active';
        $dispatcher = new Admin; $dispatcher->id = 2; $dispatcher->status = 'Active';
        $this->assertTrue($admin->hasPermission('manage_company_accounts_invoicing'));
        $this->assertFalse($dispatcher->hasPermission('manage_company_accounts_invoicing'));
        $this->assertTrue($admin->hasPermission('existing'));
        $this->assertSame(2, DB::table('permissions')->count());
        $this->assertSame(3, DB::table('permission_role')->count());
        Route::middleware(['web', 'admin_can:manage_company_accounts_invoicing'])->get('/_corporate/permission-check', fn () => response('', 204));
        $this->actingAs($admin, 'admin')->get('/_corporate/permission-check')->assertNoContent();
        $this->actingAs($dispatcher, 'admin')->get('/_corporate/permission-check')->assertForbidden();
    }
}
