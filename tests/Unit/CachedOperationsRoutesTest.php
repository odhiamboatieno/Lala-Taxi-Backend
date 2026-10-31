<?php

namespace Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CachedOperationsRoutesTest extends TestCase
{
    public function test_console_generated_cache_retains_dashboard_redirects_and_module_routes(): void
    {
        $root = dirname(__DIR__, 2);
        $temporary = sys_get_temp_dir().'/lala-cached-operations-'.bin2hex(random_bytes(8));
        mkdir($temporary, 0700, true);
        $environment = ['APP_ENV' => 'testing', 'APP_ROUTES_CACHE' => $temporary.'/routes.php',
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'LOG_CHANNEL' => 'null',
            'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync'];
        try {
            $build = new Process([PHP_BINARY, 'artisan', 'route:cache'], $root, $environment);
            $build->mustRun();
            $reader = new Process([PHP_BINARY, '-r', <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$routes = app('router')->getRoutes();
$result = ['cached' => $app->routesAreCached()];
foreach (['/admin', '/dashboard', '/admin/dashboard', '/company/dashboard', '/admin/modules', '/admin/modules/upload'] as $path) {
    $route = $routes->match(Illuminate\Http\Request::create($path));
    $result[$path] = ['action' => $route->getActionName(), 'middleware' => $route->gatherMiddleware(), 'defaults' => $route->defaults];
}
echo json_encode($result, JSON_THROW_ON_ERROR);
PHP], $root, $environment);
            $reader->mustRun();
            $result = json_decode($reader->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertTrue($result['cached']);
            $this->assertSame('/admin/dashboard', $result['/admin']['defaults']['destination']);
            $this->assertSame('App\\Http\\Controllers\\DashboardRedirectController', $result['/dashboard']['action']);
            foreach (['/admin/dashboard', '/company/dashboard', '/admin/modules', '/admin/modules/upload'] as $path) {
                $this->assertContains('admin_guest', $result[$path]['middleware']);
            }
            $this->assertStringEndsWith('ModuleController@index', $result['/admin/modules']['action']);
            $this->assertStringEndsWith('ModuleController@create', $result['/admin/modules/upload']['action']);
        } finally {
            (new Filesystem())->deleteDirectory($temporary);
        }
    }
}
