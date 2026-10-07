<?php

namespace App\Console;

use App\Jobs\CleanupStaleDriversJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Http\Controllers\Api\CronController@requestCars')->everyMinute();
        $schedule->call('App\Http\Controllers\Api\CronController@updateCurrency')->daily();
        $schedule->call('App\Http\Controllers\Api\CronController@updateReferralStatus')->daily();
        $schedule->call('App\Http\Controllers\Api\CronController@updateOfflineUsers')->everyFifteenMinutes();
        $schedule->call('App\Http\Controllers\Api\CronController@updatePaypalPayouts')->twiceDaily();
        $schedule->job(new CleanupStaleDriversJob())->everyMinute();
        $schedule->command('queue:work --tries=3 --once')->cron('* * * * *');
        $schedule->command('notifications:process-campaigns')->everyMinute();
        $schedule->command('driver-scores:recalculate --all')->daily();
        $schedule->command('documents:sync-status')->hourly();
        $corporateSchedules = [
            $schedule->command('corporate:reconcile-trips')->everyFiveMinutes(),
            $schedule->command('corporate:generate-invoices')->hourly(),
            $schedule->command('corporate:deliver-invoices')->everyMinute(),
        ];
        foreach ($corporateSchedules as $event) {
            $event->timezone('UTC')->withoutOverlapping();
            if (in_array(config('cache.default'), ['redis', 'memcached', 'database'], true)) {
                $event->onOneServer();
            }
        }
        $schedule->command('backup:run')->monthly();
        $schedule->command('backup:run --only-db')->daily();
        $schedule->command('backup:clean')->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
