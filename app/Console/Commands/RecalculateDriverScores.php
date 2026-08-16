<?php

namespace App\Console\Commands;

use App\Services\DriverScore\DriverScoreService;
use Illuminate\Console\Command;

class RecalculateDriverScores extends Command
{
    protected $signature = 'driver-scores:recalculate {driverId? : Recalculate one driver by ID} {--all : Recalculate all drivers}';

    protected $description = 'Recalculate driver score snapshots for one driver or all drivers';

    public function handle(DriverScoreService $driverScoreService): int
    {
        $driverId = $this->argument('driverId');

        if ($driverId) {
            $score = $driverScoreService->recalculateDriver($driverId, ['trigger' => 'artisan_single_recalculation']);
            if (! $score) {
                $this->error('Driver not found or invalid driver account.');

                return self::FAILURE;
            }

            $this->info('Driver #'.$score->driver_id.' recalculated to '.$score->score_percentage.'%.');

            return self::SUCCESS;
        }

        if (! $this->option('all')) {
            $this->warn('Pass a driver ID or use --all.');

            return self::INVALID;
        }

        $driverScoreService->recalculateAll();
        $this->info('All driver scores recalculated successfully.');

        return self::SUCCESS;
    }
}
