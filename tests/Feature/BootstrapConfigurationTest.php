<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BootstrapConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_08_16_000031_create_country_table.php'))->up();
        foreach (['site_settings', 'email_settings'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('value');
            });
        }
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('site');
            $table->string('name');
            $table->string('value');
        });
        config(['cache.default' => 'redis', 'mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'acceptance-mailpit',
            'mail.mailers.smtp.port' => 1025, 'mail.mailers.smtp.encryption' => null, 'mail.from.address' => 'invoices@acceptance.lalataxi.test']);
    }

    public function test_optional_credentials_and_empty_mail_settings_preserve_runtime_configuration(): void
    {
        (new AppServiceProvider(app()))->boot();
        $this->assertSame('redis', config('cache.default'));
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('acceptance-mailpit', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('invoices@acceptance.lalataxi.test', config('mail.from.address'));
        $this->assertSame('', config('facebook.client_id'));
    }

    public function test_saved_mail_settings_win_but_absent_fields_preserve_environment_defaults(): void
    {
        DB::table('email_settings')->insert([['name' => 'driver', 'value' => 'smtp'], ['name' => 'host', 'value' => 'saved-smtp'],
            ['name' => 'encryption', 'value' => ''], ['name' => 'from_name', 'value' => 'Saved Sender']]);
        (new AppServiceProvider(app()))->boot();
        $this->assertSame('saved-smtp', config('mail.mailers.smtp.host'));
        $this->assertSame(1025, config('mail.mailers.smtp.port'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('Saved Sender', config('mail.from.name'));
        $this->assertSame('invoices@acceptance.lalataxi.test', config('mail.from.address'));
    }

    public function test_api_boot_does_not_force_shared_cache_to_file(): void
    {
        app()->instance('request', \Illuminate\Http\Request::create('/api/corporate/profile'));
        $console = new \ReflectionProperty(app(), 'isRunningInConsole');
        $original = $console->getValue(app());
        $console->setValue(app(), false);
        try {
            (new AppServiceProvider(app()))->boot();
            $this->assertSame('redis', config('cache.default'));
            $this->assertSame('acceptance-mailpit', config('mail.mailers.smtp.host'));
        } finally {
            $console->setValue(app(), $original);
        }
    }

    public function test_ordinary_user_pages_do_not_instantiate_unconfigured_facebook_sdk(): void
    {
        config(['facebook.client_id' => '', 'facebook.client_secret' => '']);
        $controller = app(\App\Http\Controllers\UserController::class);
        $this->assertInstanceOf(\App\Http\Controllers\UserController::class, $controller);
        $this->assertSame(url('signin_rider'), $controller->facebook_login()->getTargetUrl());
    }
}
