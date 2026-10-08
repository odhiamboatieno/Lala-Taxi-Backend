<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporateReleaseTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        // Cleanup is explicitly scoped by the shared fixture to the isolated
        // test database, including recovery from an interrupted prior run.
        $this->dropCorporateTestSchema();
        $this->createCorporateFinanceSchema();
        (require database_path('migrations/2026_10_08_000002_create_corporate_portal_identities.php'))->up();
        (require database_path('migrations/2026_10_07_000005_add_corporate_invoice_settlement_and_delivery.php'))->up();
        config(['corporate.bookings_enabled' => false]);
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    public function test_read_only_schema_check_succeeds_and_does_not_create_financial_records(): void
    {
        $this->artisan('corporate:release-check --schema-only')->assertSuccessful();
        $this->assertSame(0, DB::table('company_corporate_invoices')->count());
        $this->assertSame(0, DB::table('company_corporate_settlements')->count());
        $this->assertSame(0, DB::table('company_corporate_invoice_deliveries')->count());
    }

    public function test_enabled_bookings_require_an_explicit_pilot_override(): void
    {
        config(['corporate.bookings_enabled' => true]);
        $this->artisan('corporate:release-check --schema-only')->assertFailed();
        $this->artisan('corporate:release-check --schema-only --allow-bookings')->assertSuccessful();
    }

    public function test_invalid_finalized_status_blocks_release_and_is_not_repaired_implicitly(): void
    {
        $trip = DB::table('trips')->insertGetId(['user_id' => 1, 'billing_mode' => 'corporate', 'corporate_account_id' => $this->account->id,
            'billing_status' => 'paid', 'billing_finalized_at' => now(), 'corporate_amount' => '100', 'corporate_currency_code' => 'KES', 'payment_status' => 'Pending']);
        $this->artisan('corporate:release-check --schema-only')->assertFailed();
        $this->assertSame('Pending', DB::table('trips')->where('id', $trip)->value('payment_status'));
    }

    public function test_runtime_checks_reject_test_mail_and_non_shared_cache(): void
    {
        config(['mail.default' => 'array', 'cache.default' => 'array', 'queue.default' => 'sync']);
        $this->artisan('corporate:release-check')->assertFailed();
    }

    public function test_runtime_check_does_not_accept_configured_but_unusable_redis_client(): void
    {
        config(['cache.default' => 'redis', 'queue.default' => 'redis', 'database.redis.client' => 'unavailable-driver']);
        $this->artisan('corporate:release-check')
            ->expectsOutput('FAIL Configured Redis client connects to Redis')->assertFailed();
    }

    public function test_finalized_charges_reject_missing_or_unknown_billing_status_and_invalid_money(): void
    {
        $trip = DB::table('trips')->insertGetId(['user_id' => 1, 'billing_mode' => 'corporate', 'corporate_account_id' => $this->account->id,
            'billing_status' => 'unbilled', 'billing_finalized_at' => now(), 'corporate_amount' => '0', 'corporate_currency_code' => 'KES', 'payment_status' => 'Deferred']);
        $this->artisan('corporate:release-check --schema-only')->assertSuccessful();
        foreach ([['billing_status' => null], ['billing_status' => 'pending'], ['corporate_amount' => '-1'], ['corporate_currency_code' => '']] as $invalid) {
            DB::table('trips')->where('id', $trip)->update(array_merge(['billing_status' => 'unbilled', 'corporate_amount' => '0', 'corporate_currency_code' => 'KES'], $invalid));
            $this->artisan('corporate:release-check --schema-only')->assertFailed();
            foreach ($invalid as $field => $value) {
                $saved = DB::table('trips')->where('id', $trip)->value($field);
                if ($field === 'corporate_amount') {
                    $this->assertSame(0, bccomp($value, (string) $saved, 2));
                } else {
                    $this->assertEquals($value, $saved);
                }
            }
        }
    }

    public function test_active_allocations_cannot_point_to_another_trip_or_void_invoice(): void
    {
        $trip = DB::table('trips')->insertGetId(['user_id' => 1, 'corporate_account_id' => $this->account->id]);
        $otherTrip = DB::table('trips')->insertGetId(['user_id' => 1, 'corporate_account_id' => $this->account->id]);
        $invoice = DB::table('company_corporate_invoices')->insertGetId(['company_id' => 1, 'corporate_account_id' => $this->account->id,
            'invoice_number' => 'RELEASE-CHECK', 'invoice_date' => '2026-10-08', 'status' => 'Draft', 'total_amount' => '100']);
        $item = DB::table('company_corporate_invoice_items')->insertGetId(['invoice_id' => $invoice, 'trip_id' => $trip,
            'amount' => '100', 'total_amount' => '100', 'currency_code' => 'KES', 'description' => 'Retained charge', 'trip_snapshot' => '{}']);
        DB::table('company_corporate_invoice_allocations')->insert(['trip_id' => $otherTrip, 'invoice_item_id' => $item, 'created_at' => now()]);
        $this->artisan('corporate:release-check --schema-only')->assertFailed();
        DB::table('company_corporate_invoice_allocations')->where('invoice_item_id', $item)->update(['trip_id' => $trip]);
        $this->artisan('corporate:release-check --schema-only')->assertSuccessful();
        DB::table('company_corporate_invoices')->where('id', $invoice)->update(['status' => 'Void']);
        $this->artisan('corporate:release-check --schema-only')->assertFailed();
        $this->assertSame(1, DB::table('company_corporate_invoice_allocations')->count());
    }
}
