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
        $this->assertSame(24, DB::table('driver_score_settings')->count());
        $this->assertSame(0, DB::table('driver_scores')->count());
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

    public function test_personal_fare_handles_a_driver_without_a_debt_ledger(): void
    {
        app(CorporateAcceptanceProvisioner::class)->provision(self::PASSWORD);
        $rider = \App\Models\User::where('mobile_number', '700000130')->firstOrFail();
        $driver = \App\Models\User::where('mobile_number', '700000140')->firstOrFail();
        $car = DB::table('car_type')->where('car_name', 'Lala Standard QA')->value('id');
        $offer = \App\Models\Request::create(['user_id' => $rider->id, 'driver_id' => $driver->id, 'car_id' => $car, 'payment_mode' => 'Cash', 'schedule_id' => '']);
        $trip = \App\Models\Trips::create(['user_id' => $rider->id, 'driver_id' => $driver->id, 'request_id' => $offer->id,
            'status' => 'Payment', 'company_id' => 1, 'car_id' => $car, 'currency_code' => 'KES', 'owe_amount' => 0]);
        $helper = app(\App\Http\Helper\InvoiceHelper::class);
        $this->assertSame(['remaining' => 0, 'applied' => 0, 'driver_payout' => 500], $helper->oweAmount(500, $driver->id, $trip->id, 1, 'KES'));
        $this->assertSame(0, \App\Models\DriverOweAmount::count());
        \App\Models\DriverOweAmount::create(['user_id' => $driver->id, 'amount' => '100', 'currency_code' => 'KES']);
        $debt = $helper->oweAmount(500, $driver->id, $trip->id, 0, 'KES');
        $this->assertEquals(100, $debt['remaining']);
        $this->assertEquals(500, $debt['driver_payout']);
    }

    public function test_new_wallet_is_zero_before_its_first_cash_fee_debit(): void
    {
        app(CorporateAcceptanceProvisioner::class)->provision(self::PASSWORD);
        $wallet = new \App\Models\Wallet;
        $this->assertSame('0.00', $wallet->amount);
        $this->assertSame('0.00', $wallet->original_amount);
        $wallet->amount = '25.50';
        $this->assertSame('25.50', $wallet->amount);
        $this->assertSame('25.50', $wallet->original_amount);
    }
}
