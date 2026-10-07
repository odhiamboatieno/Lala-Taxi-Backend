<?php

// Local, read-only rendering of real admin views with synthetic data.
// Never use this router on a public server. No production DB or auth is used.
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$asset = realpath($root.'/public'.$uri);
if ($asset && str_starts_with($asset, $root.'/public/') && is_file($asset)) { return false; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Read-only admin design preview.'); }
$scope = str_starts_with($uri, '/company/') ? 'company' : 'admin';
define('LOGIN_USER_TYPE', $scope);
foreach (['APP_ENV'=>'testing','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','SESSION_DRIVER'=>'array','CACHE_DRIVER'=>'array','LOG_CHANNEL'=>'null','SHOW_CREDENTIALS'=>'false'] as $key=>$value) { putenv("$key=$value"); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// Explicit memory-only connection also protects against cached local configuration.
config(['app.debug'=>true,'database.default'=>'admin_preview','database.connections.admin_preview'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'session.driver'=>'array','cache.default'=>'array']);
Illuminate\Support\Facades\Schema::create('metas', function ($table) { $table->increments('id'); $table->string('url'); $table->string('title'); $table->string('description'); $table->string('keywords'); });
Illuminate\Support\Facades\DB::table('metas')->insert(['url'=>ucfirst($scope),'title'=>'LalaTaxi — Dashboard design preview','description'=>'Read-only preview','keywords'=>'']);
Illuminate\Support\Facades\Schema::create('users', function ($table) {
    $table->increments('id'); $table->integer('company_id');
    foreach (['first_name', 'last_name', 'email', 'mobile_number', 'user_type', 'status'] as $field) $table->string($field);
});
Illuminate\Support\Facades\DB::table('users')->insert([
    ['id'=>101, 'company_id'=>2, 'first_name'=>'Alice', 'last_name'=>'Kamau', 'email'=>'alice@example.test', 'mobile_number'=>'0700123456', 'user_type'=>'Driver', 'status'=>'Active'],
    ['id'=>102, 'company_id'=>2, 'first_name'=>'Mary', 'last_name'=>'Otieno', 'email'=>'mary@example.test', 'mobile_number'=>'0700654321', 'user_type'=>'Rider', 'status'=>'Active'],
    ['id'=>103, 'company_id'=>3, 'first_name'=>'Alice', 'last_name'=>'Other company', 'email'=>'other@example.test', 'mobile_number'=>'0700999999', 'user_type'=>'Driver', 'status'=>'Active'],
]);
$request = Illuminate\Http\Request::capture();
$request->setLaravelSession($app['session']->driver());
$app->instance('request', $request);
$app['url']->setRequest($request);
$app['url']->forceRootUrl('http://127.0.0.1:8097');
$user = $scope === 'company'
    ? new class extends App\Models\Company { public function can($ability, $arguments = []) { return false; } }
    : new class extends App\Models\Admin { public function can($ability, $arguments = []) { return true; } };
$user->forceFill(['id'=>2,'name'=>'Preview Taxis','username'=>'Design preview','email'=>'preview@example.test','status'=>'Active','approval_status'=>'approved']);
Illuminate\Support\Facades\Auth::guard($scope)->setUser($user);
Illuminate\Support\Facades\View::share([
    'site_name'=>'LalaTaxi','favicon'=>asset('images/marketing/lala-taxi-logo.webp'), 'logo_url'=>asset('images/marketing/lala-taxi-logo.webp'),
    'errors'=>new Illuminate\Support\ViewErrorBag(), 'country'=>collect([(object)['id'=>1,'phone_code'=>'254','long_name'=>'Kenya'],(object)['id'=>2,'phone_code'=>'256','long_name'=>'Uganda']]),
    'country_code_option'=>collect([(object)['id'=>1,'phone_code'=>'254','long_name'=>'Kenya'],(object)['id'=>2,'phone_code'=>'256','long_name'=>'Uganda']]),
    'copyright_year'=>date('Y'),'copyright_name'=>'LalaTaxi','copyright_url'=>url('/'), 'map_key'=>'',
]);
$app['view']->addNamespace('admin-preview', __DIR__.'/views');
$pages = ['/admin/dashboard'=>'admin.index', '/company/dashboard'=>'admin.index', '/admin/add_rider'=>'admin.rider.add', '/admin/design-controls'=>'admin-preview::controls', '/admin/login'=>'admin.login'];
if ($uri === '/'.$scope.'/search') {
    // Run the production controller against the synthetic, memory-only records above.
    app(App\Http\Controllers\Admin\DashboardSearchController::class)($request, app(App\Services\DashboardSearch::class))->send();
    exit;
}
if (!isset($pages[$uri])) { http_response_code(404); exit('Read-only design preview: this route is not available.'); }
Illuminate\Support\Facades\Route::get(ltrim($uri, '/'), function () use ($pages, $uri) {
    return view($pages[$uri], ['currency_code'=>'KES ', 'users_count'=>1248, 'total_driver'=>186, 'total_rider'=>1062, 'today_trips'=>74, 'total_success_trips'=>8462, 'today_revenue'=>82450,
        'earnings_chart'=>['default_period'=>'week','series_title'=>'Earnings','periods'=>['week'=>['label'=>'This week','data'=>['labels'=>['Mon','Tue','Wed','Thu','Fri'],'gross'=>[16000,21000,18500,28000,32000],'operator'=>[12000,16000,13500,21000,24000]]]]],
    ]);
});
$response = $app['router']->dispatch($request);
$response->send();
