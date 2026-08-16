<?php

namespace Modules\SaasDispatch\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SaasDispatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind SaaS-specific services here.
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'saasdispatch');
    }
}
