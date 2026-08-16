<?php

namespace Modules\UserLevels\Providers;

use Illuminate\Support\ServiceProvider;

class UserLevelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'user_levels');
        $this->registerHelpers();
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'userlevels');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    protected function registerHelpers(): void
    {
        $helperPath = __DIR__.'/../Helpers/helpers.php';

        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }
}
