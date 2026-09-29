<?php

// Read-only visual preview. Never use this router on a public server.
// php -S 127.0.0.1:8096 -t public scripts/preview-marketing.php
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$asset = realpath($root.'/public'.$uri);
if ($asset && str_starts_with($asset, $root.'/public/') && is_file($asset)) {
    return false;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('This is a read-only design preview. Account actions use the normal application server.');
}

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_DRIVER=array');
putenv('LOG_CHANNEL=null');
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$app['url']->setRequest($request);
$app['url']->forceRootUrl('http://127.0.0.1:8096');
Illuminate\Support\Facades\View::share('site_name', 'LalaTaxi');
Illuminate\Support\Facades\View::share('favicon', $app['url']->asset('favicon.ico'));
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

$pages = [
    '/' => 'home.home', '/ride' => 'ride.ride', '/drive' => 'drive.drive',
    '/safety' => 'ride.safety', '/business' => 'marketing.business',
    '/business/fleets' => 'marketing.fleets',
    '/requirements' => 'drive.requirements', '/driver_app' => 'drive.driver_app',
    '/drive_safety' => 'drive.drive_safety', '/signin' => 'user.signin',
    '/signup' => 'user.signup', '/signin_company' => 'user.signin_company',
];
if (!isset($pages[$uri])) {
    http_response_code(404);
    exit('This route belongs to the normal Laravel application, not the read-only marketing preview.');
}
echo view($pages[$uri])->render();
