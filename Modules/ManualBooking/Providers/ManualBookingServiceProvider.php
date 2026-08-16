<?php

namespace Modules\ManualBooking\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\ManualBooking\Http\Middleware\EnsureManualBookingEnabled;

class ManualBookingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'manual_booking');
        $this->registerHelpers();
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'manualbooking');
        $router->aliasMiddleware('manual.booking.enabled', EnsureManualBookingEnabled::class);
    }

    protected function registerHelpers(): void
    {
        $helperPath = __DIR__.'/../Helpers/helpers.php';

        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }
}
