<?php

namespace Modules\SaaSDispatch\Database\Seeders;

use Illuminate\Database\Seeder;

class SaaSDispatchDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SaaSDispatchPermissionSeeder::class,
        ]);
    }
}
