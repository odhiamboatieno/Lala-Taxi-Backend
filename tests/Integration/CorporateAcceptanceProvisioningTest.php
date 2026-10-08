<?php

namespace Tests\Integration;

use App\Models\Admin;
use App\Services\Corporate\CorporateAcceptanceProvisioner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CorporateAcceptanceProvisioningTest extends TestCase
{
    private const PASSWORD = 'isolated-suite-password-only';

    protected function setUp(): void
    {
        parent::setUp();
        app(CorporateAcceptanceProvisioner::class)->assertIsolated(self::PASSWORD);
        $this->assertSame(0, Artisan::call('migrate', ['--force' => true]));
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_provisioning_is_repeatable_audited_and_does_not_seed_financial_records(): void
    {
        $service = app(CorporateAcceptanceProvisioner::class);
        $first = $service->provision(self::PASSWORD);
        $audits = DB::table('company_corporate_audits')->count();
        $hash = DB::table('admins')->where('username', 'qa_njeri')->value('password');
        $second = $service->provision('another-isolated-password-only');
        $this->assertSame($first, $second);
        $this->assertCount(12, $first);
        $this->assertSame(9, DB::table('users')->count());
        $this->assertSame(2, DB::table('company_corporate_accounts')->count());
        $this->assertSame(8, $audits);
        $this->assertSame($audits, DB::table('company_corporate_audits')->count());
        $this->assertSame($hash, DB::table('admins')->where('username', 'qa_njeri')->value('password'));
        $this->assertTrue(Hash::check(self::PASSWORD, $hash));
        $this->assertTrue(Admin::where('username', 'qa_njeri')->firstOrFail()->hasPermission(['manage_company_accounts_invoicing']));
        foreach (['trips', 'payment', 'payment_transactions', 'company_corporate_invoices', 'company_corporate_settlements'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), $table.' must remain empty.');
        }
        $this->assertSame(1, DB::table('users')->whereNull('corporate_account_id')->where('user_type', 'Rider')->count());
        $this->assertSame(2, DB::table('vehicle')->where('status', 'Active')->count());
    }

    public function test_configuration_drift_fails_and_rolls_back_all_provisioning(): void
    {
        DB::table('site_settings')->insert(['name' => 'saas_dispatch_module_enabled', 'value' => '0']);
        try {
            app(CorporateAcceptanceProvisioner::class)->provision(self::PASSWORD);
            $this->fail('Existing configuration must not be overwritten.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('fixture drift', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('admins')->count());
        $this->assertSame(0, DB::table('country')->count());
        $this->assertSame('0', DB::table('site_settings')->where('name', 'saas_dispatch_module_enabled')->value('value'));
    }
}
