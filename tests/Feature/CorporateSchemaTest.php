<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Trips;
use App\Models\User;
use App\Services\Corporate\CorporateMembershipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Tests\Concerns\CreatesCorporateSchema;
use Tests\TestCase;

class CorporateSchemaTest extends TestCase
{
    use CreatesCorporateSchema;

    protected function setUp(): void
    {
        parent::setUp();
        // Recover only this guarded test database after an interrupted run.
        $this->dropCorporateTestSchema();
        $this->createCorporateSchema(false);
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function account(int $companyId = 1): CompanyCorporateAccount
    {
        return CompanyCorporateAccount::create(['company_id' => $companyId, 'account_name' => 'Employer', 'legal_name' => 'Employer Ltd', 'billing_address' => 'Kenya', 'currency_code' => 'KES', 'billing_timezone' => 'Africa/Nairobi', 'credit_limit' => '10000.25', 'status' => 'Active']);
    }

    private function admin(): Admin
    {
        $admin = new class extends Admin { public function hasPermission($permissions) { return in_array('manage_company_accounts_invoicing', (array) $permissions, true); } };
        $admin->id = 10;

        return $admin;
    }

    public function test_additive_upgrade_preserves_legacy_headers_and_personal_trips_and_is_retry_safe(): void
    {
        $account = DB::table('company_corporate_accounts')->insertGetId(['company_id' => 1, 'account_name' => 'Historical employer']);
        DB::table('company_corporate_invoices')->insert(['company_id' => 1, 'corporate_account_id' => $account, 'invoice_number' => 'LEGACY-1', 'invoice_date' => '2026-09-01', 'total_amount' => '1000.00']);
        DB::table('trips')->insert(['user_id' => 1, 'total_fare' => '1180.00']);
        $this->corporateMigration()->up();
        $foreignCount = count(\Schema::getForeignKeys('users'));
        $this->corporateMigration()->up();
        $this->assertCount($foreignCount, \Schema::getForeignKeys('users'));
        $this->assertSame('personal', DB::table('trips')->value('billing_mode'));
        $this->assertNull(DB::table('trips')->value('corporate_account_id'));
        $invoice = CompanyCorporateInvoice::firstOrFail();
        $this->assertFalse($invoice->trip_backed);
        $this->assertSame('1000.00', $invoice->total_amount);
        $this->assertSame('LEGACY-1', $invoice->invoice_number);
        $this->assertSame(0, DB::table('company_corporate_invoice_items')->count());
        if (DB::getDriverName() === 'mysql') {
            DB::table('trips')->update(['payment_status' => 'Deferred']);
            $this->assertSame('Deferred', DB::table('trips')->value('payment_status'));
        }
    }

    public function test_inconsistent_legacy_ownership_fails_before_ddl(): void
    {
        $account = DB::table('company_corporate_accounts')->insertGetId(['company_id' => 1, 'account_name' => 'Employer']);
        DB::table('company_corporate_invoices')->insert(['company_id' => 2, 'corporate_account_id' => $account, 'invoice_number' => 'INVALID', 'invoice_date' => '2026-09-01']);
        try {
            $this->corporateMigration()->up();
            $this->fail('Mismatched ownership must not be silently repaired.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ownership', $e->getMessage());
            $this->assertFalse(\Schema::hasColumn('users', 'corporate_account_id'));
        }
    }

    public function test_membership_is_audited_idempotent_and_removal_preserves_trip_identity(): void
    {
        $this->corporateMigration()->up();
        $account = $this->account();
        $rider = User::findOrFail(1);
        $service = app(CorporateMembershipService::class);
        $service->assign($account, $rider, 'member', $this->admin());
        $service->assign($account, $rider, 'member', $this->admin());
        $this->assertSame(1, $account->audits()->count());
        $this->assertSame('10000.25', $account->credit_limit);
        DB::table('trips')->insert(['user_id' => 1, 'billing_mode' => 'corporate', 'corporate_account_id' => $account->id, 'billing_snapshot' => json_encode(['account_name' => 'Original employer'])]);
        $service->remove($account, $rider, $this->admin());
        $this->assertNull($rider->fresh()->corporate_account_id);
        $trip = Trips::firstOrFail();
        $this->assertTrue($trip->isCorporate());
        $this->assertSame('Original employer', $trip->billing_snapshot['account_name']);
        $this->assertContains('billing_snapshot', $trip->getHidden());
        $this->assertSame(2, $account->audits()->count());
    }

    public function test_cross_account_transfer_and_invalid_roles_are_rejected(): void
    {
        $this->corporateMigration()->up();
        $service = app(CorporateMembershipService::class);
        $rider = User::findOrFail(1);
        $service->assign($this->account(), $rider, 'member', $this->admin());
        foreach (['member', 'administrator'] as $role) {
            try {
                $service->assign($this->account(), $rider, $role, $this->admin());
                $this->fail('Explicit removal is required before transferring accounts.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('user_id', $e->errors());
            }
        }
    }

    public function test_durable_trip_record_blocks_existing_rider_cascade_before_invoice(): void
    {
        $this->corporateMigration()->up();
        $account = $this->account();
        $id = DB::table('trips')->insertGetId(['user_id' => 1, 'billing_mode' => 'corporate', 'corporate_account_id' => $account->id]);
        DB::table('company_corporate_trip_records')->insert(['trip_id' => $id, 'corporate_account_id' => $account->id, 'created_at' => now()]);
        try {
            DB::table('users')->where('id', 1)->delete();
            $this->fail('A parent cascade must not erase corporate financial evidence.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertSame(1, DB::table('trips')->where('id', $id)->count());
            $this->assertSame(1, DB::table('users')->where('id', 1)->count());
        }
    }

    public function test_owner_constraint_rejects_cross_company_invoice_headers(): void
    {
        $this->corporateMigration()->up();
        $account = $this->account();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('company_corporate_invoices')->insert(['company_id' => 2, 'corporate_account_id' => $account->id, 'invoice_number' => 'CROSS-OWNER', 'invoice_date' => '2026-10-07']);
    }

    public function test_estimated_fare_upgrade_accepts_normal_kenya_amounts(): void
    {
        (require database_path('migrations/2026_10_07_000001_widen_request_estimated_fare.php'))->up();
        DB::table('request')->insert(['estimated_fare' => '2140.00']);
        $this->assertSame(2140.0, (float) DB::table('request')->value('estimated_fare'));
    }
}
