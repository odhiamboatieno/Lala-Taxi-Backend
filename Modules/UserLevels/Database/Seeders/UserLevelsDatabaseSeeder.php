<?php

namespace Modules\UserLevels\Database\Seeders;

use Illuminate\Database\Seeder;

class UserLevelsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserLevelsPermissionSeeder::class);
    }
}
