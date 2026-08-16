<?php

namespace Modules\SurgePricing\Database\Seeders;

use Illuminate\Database\Seeder;

class SurgePricingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SurgePricingPermissionSeeder::class);
    }
}
