<?php

namespace Tests\Feature;

use App\Services\Corporate\CorporateAcceptanceProvisioner;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CorporateAcceptanceGuardTest extends TestCase
{
    public function test_production_is_rejected_before_any_query(): void
    {
        app()->instance('env', 'production');
        config(['app.env' => 'production']);
        $this->assertRejectedWithoutQuery();
    }

    public function test_sqlite_is_rejected_before_any_query(): void
    {
        $this->assertRejectedWithoutQuery();
    }

    public function test_other_mysql_databases_are_rejected_before_any_query(): void
    {
        config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'taxi']);
        $this->assertRejectedWithoutQuery();
    }

    public function test_missing_password_is_rejected_before_any_query(): void
    {
        config(['database.default' => 'mysql', 'database.connections.mysql.database' => CorporateAcceptanceProvisioner::DATABASE]);
        DB::enableQueryLog();
        try {
            app(CorporateAcceptanceProvisioner::class)->provision('');
            $this->fail('Missing operator credentials must fail closed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('at least 16', $exception->getMessage());
        }
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_cli_refuses_production_without_printing_password(): void
    {
        app()->instance('env', 'production');
        config(['app.env' => 'production', 'corporate_acceptance.password' => 'isolated-guard-test-only']);
        $this->artisan('corporate:provision-acceptance')->assertFailed();
    }

    private function assertRejectedWithoutQuery(): void
    {
        DB::enableQueryLog();
        try {
            app(CorporateAcceptanceProvisioner::class)->provision('isolated-guard-test-only');
            $this->fail('Unsafe environment must fail closed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('non-production MySQL', $exception->getMessage());
        }
        $this->assertSame([], DB::getQueryLog());
    }
}
