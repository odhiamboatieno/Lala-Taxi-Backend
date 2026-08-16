<?php

namespace Modules\OperationTeam\Database\Seeders;

use Illuminate\Database\Seeder;

class OperationTeamDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OperationTeamPermissionSeeder::class);
    }
}
