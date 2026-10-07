<?php

namespace Tests\Feature;

use App\Http\Helper\InvoiceHelper;
use App\Models\Admin;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Services\Corporate\CorporateBillingPeriod;
use App\Services\Corporate\CorporateInvoiceGenerator;
use App\Services\Corporate\CorporateInvoiceLifecycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporateInvoiceTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateFinanceSchema();
        (require database_path('migrations/2026_10_07_000005_add_corporate_invoice_settlement_and_delivery.php'))->up();
        $this->account->update(['billing_cycle' => 'monthly']);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-07 12:00:00', 'UTC'));
        Event::fake([\App\Events\DriverRatingTriggerEvent::class]);
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function actor(bool $permitted = true): Admin
    {
        $actor = \Mockery::mock(Admin::class)->makePartial();
        $actor->id = 1;
        $actor->shouldReceive('hasPermission')->with(['manage_company_accounts_invoicing'])->andReturn($permitted);

        return $actor;
    }

    private function charge(string $completion = '2026-09-29 10:15:00', bool $finalize = true): Trips
    {
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $end = \Carbon\Carbon::parse($completion, 'UTC');
        $trip = DB::transaction(fn () => Trips::create([
            'user_id' => 1, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'Payment',
            'completed_at' => $completion, 'end_trip' => $completion,
            'arrive_time' => $end->copy()->subMinutes(11), 'begin_trip' => $end->copy()->subMinutes(10),
            'tips' => '100.00', 'toll_fee' => '50.00',
        ]));

        return $finalize ? app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]) : $trip->fresh();
    }

    private function generate(): ?CompanyCorporateInvoice
    {
        return app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
    }

    public function test_generation_uses_half_open_local_period_and_idempotent_allocations(): void
    {
        $in = $this->charge('2026-09-30 20:59:59');
        $out = $this->charge('2026-09-30 21:00:00');
        $invoice = $this->generate();
        $this->assertSame('1470.00', $invoice->total_amount);
        $this->assertSame('2026-08-31 21:00:00', $invoice->period_start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-30 21:00:00', $invoice->period_end->format('Y-m-d H:i:s'));
        $this->assertSame([$in->id], $invoice->items->pluck('trip_id')->all());
        $this->assertSame('invoiced', $in->fresh()->billing_status);
        $this->assertSame('unbilled', $out->fresh()->billing_status);
        $this->assertSame('Deferred', $in->fresh()->payment_status);
        $this->assertNull($this->generate());
        $this->assertSame(1, DB::table('company_corporate_invoice_allocations')->count());
        $this->assertSame(1, CompanyCorporateInvoice::count());
    }

    public function test_dry_run_and_late_supplemental_charges_preserve_records(): void
    {
        $this->charge();
        $preview = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01', true);
        $this->assertFalse($preview->exists);
        $this->assertSame('1470.00', $preview->total_amount);
        $this->assertSame(0, CompanyCorporateInvoice::count());
        $first = $this->generate();
        $late = $this->charge('2026-09-20 10:15:00');
        $this->account->update(['status' => 'Inactive', 'archived_at' => now()]);
        config(['corporate.bookings_enabled' => false]);
        $second = $this->generate();
        $this->assertSame(2, $second->revision);
        $this->assertNotSame($first->invoice_number, $second->invoice_number);
        $this->assertSame([$late->id], $second->items->pluck('trip_id')->all());
        $this->assertSame(2, DB::table('company_corporate_invoice_allocations')->count());
    }

    public function test_send_settle_retry_and_paid_void_guard(): void
    {
        $trip = $this->charge();
        $invoice = $this->generate();
        $actor = $this->actor();
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $sent = $lifecycle->send($invoice, $actor);
        $this->assertSame('Sent', $sent->status);
        $lifecycle->send($invoice, $actor);
        $this->assertSame(1, DB::table('company_corporate_invoice_deliveries')->count());
        $this->assertSame('finance@example.test', DB::table('company_corporate_invoice_deliveries')->value('recipient'));
        $paid = $lifecycle->settle($sent, $actor, '1470.00', 'KES', '2026-10-07', 'BANK-1');
        $this->assertSame('Paid', $paid->status);
        $this->assertSame('paid', $trip->fresh()->billing_status);
        $this->assertSame('Completed', $trip->fresh()->payment_status);
        $this->assertSame('1170.00', $trip->fresh()->payable_driver_payout);
        $lifecycle->settle($sent, $actor, '1470', 'KES', '2026-10-07', 'BANK-1');
        $this->assertSame(1, DB::table('company_corporate_settlements')->count());
        $this->assertSame(1, $this->account->audits()->where('action', 'invoice.paid')->count());
        $this->expectException(ValidationException::class);
        $lifecycle->void($paid, $actor, 'Must not erase paid history');
    }

    public function test_void_releases_only_allocations_and_reinvoice_retains_items(): void
    {
        $trip = $this->charge();
        $invoice = $this->generate();
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $lifecycle->send($invoice, $this->actor());
        $void = $lifecycle->void($invoice, $this->actor(), 'Correct billing batch');
        $this->assertSame('Void', $void->status);
        $this->assertSame('unbilled', $trip->fresh()->billing_status);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertSame(0, DB::table('company_corporate_invoice_allocations')->count());
        $this->assertSame('cancelled', DB::table('company_corporate_invoice_deliveries')->value('status'));
        $replacement = $this->generate();
        $this->assertSame(2, $replacement->revision);
        $this->assertSame(2, DB::table('company_corporate_invoice_items')->count());
        $this->assertSame(1, DB::table('company_corporate_invoice_allocations')->count());
        $this->assertSame('1470.00', $trip->fresh()->corporate_amount);
    }

    public function test_wrong_amount_currency_date_reference_and_state_never_partially_settle(): void
    {
        $trip = $this->charge();
        $invoice = $this->generate();
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $actor = $this->actor();
        foreach ([['1.00', 'KES', '2026-10-07', 'B'], ['1470.00', 'USD', '2026-10-07', 'B'], ['1470.00', 'KES', '2026-10-08', 'B'], ['1470.00', 'KES', '2026-02-30', 'B'], ['1470.00', 'KES', '2026-10-07', '']] as $input) {
            try {
                $lifecycle->settle($invoice, $actor, ...$input);
                $this->fail('Invalid settlement must be rejected.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('invoice', $e->errors());
            }
        }
        $this->assertSame('Draft', $invoice->fresh()->status);
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
        $this->assertSame(0, DB::table('company_corporate_settlements')->count());
    }

    public function test_finance_permissions_and_legacy_headers_cannot_be_bypassed(): void
    {
        $this->charge();
        $invoice = $this->generate();
        try {
            app(CorporateInvoiceLifecycle::class)->send($invoice, $this->actor(false));
            $this->fail('Missing finance permission must be rejected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        DB::table('company_corporate_invoices')->where('id', $invoice->id)->update(['trip_backed' => false]);
        $this->expectException(ValidationException::class);
        app(CorporateInvoiceLifecycle::class)->send($invoice->fresh(), $this->actor());
    }

    public function test_period_validation_week_quarter_and_dst_boundaries(): void
    {
        $periods = app(CorporateBillingPeriod::class);
        $this->account->update(['billing_cycle' => 'weekly', 'billing_timezone' => 'Europe/London']);
        $week = $periods->requested($this->account, '2026-03-23');
        $this->assertSame('2026-03-23 00:00:00', $week['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-29 23:00:00', $week['end']->format('Y-m-d H:i:s'));
        $this->account->update(['billing_cycle' => 'quarterly', 'billing_timezone' => 'Africa/Nairobi']);
        $quarter = $periods->requested($this->account, '2026-07-01');
        $this->assertSame('2026-09-30 21:00:00', $quarter['end']->format('Y-m-d H:i:s'));
        foreach (['2026-07', '2026-07-02', '2026-02-30'] as $bad) {
            try { $periods->requested($this->account, $bad); $this->fail('Invalid start must fail.'); }
            catch (ValidationException $e) { $this->assertArrayHasKey('period_start', $e->errors()); }
        }
    }

    public function test_open_period_and_header_mutation_are_rejected(): void
    {
        $this->charge();
        try {
            app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-10-01');
            $this->fail('Open period must not be invoiced.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('period_start', $e->errors());
        }
        $invoice = $this->generate();
        $this->expectException(\DomainException::class);
        $invoice->update(['total_amount' => '1.00']);
    }

    public function test_pdf_delivery_uses_saved_identity_and_retries_without_reissuing(): void
    {
        $this->charge();
        $invoice = $this->generate();
        app(CorporateInvoiceLifecycle::class)->send($invoice, $this->actor());
        $this->account->update(['legal_name' => 'New employer name', 'contact_email' => 'new@example.test']);
        $id = DB::table('company_corporate_invoice_deliveries')->value('id');
        $mailer = \Illuminate\Support\Facades\Mail::getFacadeRoot();
        \Illuminate\Support\Facades\Mail::shouldReceive('to')->once()->with('finance@example.test')->andThrow(new \RuntimeException('Transport unavailable'));
        $delivery = app(\App\Services\Corporate\CorporateInvoiceDelivery::class);
        $this->assertFalse($delivery->deliver($id));
        $this->assertSame('failed', DB::table('company_corporate_invoice_deliveries')->where('id', $id)->value('status'));
        \Illuminate\Support\Facades\Mail::swap($mailer);
        \Illuminate\Support\Facades\Mail::fake();
        $this->travel(6)->minutes();
        $this->assertTrue($delivery->deliver($id));
        $this->assertFalse($delivery->deliver($id));
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\CorporateInvoiceMail::class, fn ($mail) => $mail->hasTo('finance@example.test') && data_get($mail->invoice->bill_to_snapshot, 'legal_name') === 'Employer Ltd');
        $this->assertSame(2, DB::table('company_corporate_invoice_deliveries')->where('id', $id)->value('attempts'));
        $this->assertSame(1, CompanyCorporateInvoice::count());
        $this->assertSame(1, $this->account->audits()->where('action', 'invoice.delivered')->count());
        $pdf = \PDF::loadView('corporate.invoice_pdf', ['invoice' => $invoice->load('items')])->output();
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_generation_command_previews_then_generates_all_closed_periods_once(): void
    {
        $this->charge();
        $this->artisan('corporate:generate-invoices', ['--account' => $this->account->id, '--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, CompanyCorporateInvoice::count());
        $this->artisan('corporate:generate-invoices', ['--account' => $this->account->id])->assertSuccessful();
        $this->artisan('corporate:generate-invoices', ['--account' => $this->account->id])->assertSuccessful();
        $this->assertSame(1, CompanyCorporateInvoice::count());
        $this->artisan('corporate:generate-invoices', ['--account' => 9999])->assertFailed();
    }

    public function test_competing_mysql_generators_cannot_allocate_the_same_charge_twice(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requires the isolated corporate MySQL profile.');
        }
        $trip = $this->charge();
        $db = config('database.connections.mysql');
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => $db['host'], 'DB_PORT' => (string) $db['port'],
            'DB_DATABASE' => $db['database'], 'DB_USERNAME' => $db['username'], 'DB_PASSWORD' => $db['password'], 'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'array'];
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $worker = new \Symfony\Component\Process\Process([PHP_BINARY, 'artisan', 'corporate:generate-invoices', '--account='.$this->account->id, '--period-start=2026-09-01'], base_path(), $environment);
            $worker->start();
            $workers[] = $worker;
        }
        foreach ($workers as $worker) {
            $this->assertSame(0, $worker->wait(), $worker->getOutput().$worker->getErrorOutput());
        }
        $this->assertSame(1, CompanyCorporateInvoice::count());
        $this->assertSame(1, DB::table('company_corporate_invoice_allocations')->where('trip_id', $trip->id)->count());
    }

    public function test_competing_mysql_settlements_create_one_financial_record(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requires the isolated corporate MySQL profile.');
        }
        $trip = $this->charge();
        $invoice = $this->generate();
        app(CorporateInvoiceLifecycle::class)->send($invoice, $this->actor());
        $db = config('database.connections.mysql');
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => $db['host'], 'DB_PORT' => (string) $db['port'],
            'DB_DATABASE' => $db['database'], 'DB_USERNAME' => $db['username'], 'DB_PASSWORD' => $db['password'], 'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'array'];
        $code = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Carbon\Carbon::setTestNow('2026-10-07 12:00:00');
Carbon\CarbonImmutable::setTestNow('2026-10-07 12:00:00');
$actor = new class extends App\Models\Admin { public function hasPermission($permissions) { return true; } };
$actor->id = 1;
$invoice = Modules\SaaSDispatch\Entities\CompanyCorporateInvoice::findOrFail($argv[1]);
app(App\Services\Corporate\CorporateInvoiceLifecycle::class)->settle($invoice, $actor, '1470.00', 'KES', '2026-10-07', 'BANK-CONCURRENT');
PHP;
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $worker = new \Symfony\Component\Process\Process([PHP_BINARY, '-r', $code, (string) $invoice->id], base_path(), $environment);
            $worker->start();
            $workers[] = $worker;
        }
        foreach ($workers as $worker) {
            $this->assertSame(0, $worker->wait(), $worker->getOutput().$worker->getErrorOutput());
        }
        $this->assertSame(1, DB::table('company_corporate_settlements')->count());
        $this->assertSame(1, $this->account->audits()->where('action', 'invoice.paid')->count());
        $this->assertSame('Paid', $invoice->fresh()->status);
        $this->assertSame('Completed', $trip->fresh()->payment_status);
    }

    public function test_incomplete_allocations_block_settlement_without_partial_changes(): void
    {
        $trip = $this->charge();
        $invoice = $this->generate();
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $lifecycle->send($invoice, $this->actor());
        DB::table('company_corporate_invoice_allocations')->where('trip_id', $trip->id)->delete();
        try {
            $lifecycle->settle($invoice, $this->actor(), '1470.00', 'KES', '2026-10-07', 'BANK-1');
            $this->fail('Missing allocation requires reconciliation.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('invoice', $e->errors());
        }
        $this->assertSame(0, DB::table('company_corporate_settlements')->count());
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
        $this->assertSame('Sent', $invoice->fresh()->status);
    }

    public function test_recovery_command_finalizes_physical_completion_without_duplicate_payouts(): void
    {
        $trip = $this->charge('2026-09-29 10:15:00', false);
        config(['corporate.bookings_enabled' => false]);
        $this->artisan('corporate:reconcile-trips', ['--dry-run' => true])->assertSuccessful();
        $this->assertNull($trip->fresh()->billing_finalized_at);
        $this->artisan('corporate:reconcile-trips')->assertSuccessful();
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
        $this->assertSame('1470.00', $trip->fresh()->corporate_amount);
        $this->artisan('corporate:reconcile-trips')->assertSuccessful();
        $this->assertSame(1, DB::table('payment')->where('trip_id', $trip->id)->count());
    }

    public function test_zero_charge_is_finalized_and_settled_without_gateway_collection(): void
    {
        DB::table('manage_fare')->update(['base_fare' => '0.00', 'min_fare' => '0.00', 'per_min' => '0.00', 'per_km' => '0.00']);
        $trip = $this->charge('2026-09-29 10:15:00', false);
        $trip->update(['tips' => '0.00', 'toll_fee' => '0.00']);
        $trip = app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
        $this->assertSame('0.00', $trip->corporate_amount);
        $invoice = $this->generate();
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $sent = $lifecycle->send($invoice, $this->actor());
        $paid = $lifecycle->settle($sent, $this->actor(), '0.00', 'KES', '2026-10-07', 'ZERO-CHARGE');
        $this->assertSame('Paid', $paid->status);
        $this->assertSame('Completed', $trip->fresh()->payment_status);
        $this->assertSame(1, DB::table('payment')->count());
        $this->assertNull(DB::table('payment')->value('correlation_id'));
    }

    public function test_financial_instants_remain_utc_when_legacy_code_changes_php_timezone(): void
    {
        $timezone = date_default_timezone_get();
        try {
            date_default_timezone_set('Africa/Nairobi');
            $trip = $this->charge('2026-09-30 20:59:59');
            $this->assertSame('UTC', $trip->completed_at->getTimezone()->getName());
            $this->assertSame('2026-09-30 20:59:59', $trip->completed_at->format('Y-m-d H:i:s'));
            $this->assertSame('UTC', $trip->billing_finalized_at->getTimezone()->getName());
            $invoice = $this->generate();
            $this->assertSame('UTC', $invoice->period_end->getTimezone()->getName());
            $this->assertSame('2026-09-30T20:59:59+00:00', data_get($invoice->items->first()->trip_snapshot, 'completed_at'));
        } finally {
            date_default_timezone_set($timezone);
        }
    }
}
