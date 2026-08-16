<?php

namespace Modules\SurgePricing\Providers;

use Illuminate\Support\ServiceProvider;

class SurgePricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'surge_pricing');
        $helperPath = __DIR__.'/../Helpers/helpers.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'surgepricing');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
