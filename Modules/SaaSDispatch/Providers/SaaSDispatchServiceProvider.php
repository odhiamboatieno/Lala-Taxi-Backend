<?php

namespace Modules\SaaSDispatch\Providers;

use App\Models\Trips;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\SaaSDispatch\Http\Middleware\EnsureSaaSDispatchEnabled;
use Modules\SaaSDispatch\Policies\CompanyDispatchPolicy;

class SaaSDispatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'saas_dispatch');
        $this->registerHelpers();
    }

    public function boot(Router $router): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'saasdispatch');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'saasdispatch');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $router->aliasMiddleware('saas.dispatch.enabled', EnsureSaaSDispatchEnabled::class);

        Gate::policy(Trips::class, CompanyDispatchPolicy::class);
        Gate::define('saas-dispatch.access', [CompanyDispatchPolicy::class, 'access']);
        Gate::define('saas-dispatch.assign-driver', [CompanyDispatchPolicy::class, 'assignDriver']);
    }

    protected function registerHelpers(): void
    {
        $helperPath = __DIR__.'/../Helpers/helpers.php';

        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }
}
