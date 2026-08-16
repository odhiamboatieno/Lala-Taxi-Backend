<?php

namespace Modules\OperationTeam\Providers;

use Illuminate\Support\ServiceProvider;

class OperationTeamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'operation_team');
        $helperPath = __DIR__.'/../Helpers/helpers.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'operationteam');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
