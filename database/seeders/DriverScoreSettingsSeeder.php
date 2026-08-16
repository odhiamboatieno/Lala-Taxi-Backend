<?php

namespace Database\Seeders;

use App\Models\DriverScoreSetting;
use Illuminate\Database\Seeder;

class DriverScoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (DriverScoreSetting::count() > 0) {
            return;
        }

        $this->command?->call('migrate', ['--path' => 'database/migrations/2026_05_19_230200_create_driver_score_settings_table.php']);
    }
}
