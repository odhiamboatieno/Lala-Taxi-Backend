<?php

namespace Tests\Feature;

use App\Http\Helper\InvoiceHelper;
use App\Models\Payment;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\User;
use App\Services\Corporate\CorporateTripFinalizer;
use App\Services\Rating\DriverRatingEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporateFinalizationTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateFinanceSchema();
        Event::fake([\App\Events\DriverRatingTriggerEvent::class]);
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function trip(array $attributes = []): Trips
    {
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        return DB::transaction(fn () => Trips::create(array_merge([
            'user_id' => 1, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'Payment',
            'completed_at' => '2026-10-07 10:15:00', 'end_trip' => '2026-10-07 10:15:00',
            'arrive_time' => '2026-10-07 10:04:00', 'begin_trip' => '2026-10-07 10:05:00', 'tips' => '100.00', 'toll_fee' => '50.00',
        ], $attributes)))->fresh();
    }

    public function test_real_fare_engine_finalizes_once_without_personal_promotion_wallet_or_debt_tables(): void
    {
        $trip = $this->trip();
        $helper = app(InvoiceHelper::class);
        $calculated = $helper->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
        $this->assertSame('1470.00', $calculated->corporate_amount);
        $this->assertSame('1170.00', $calculated->driver_payout);
        $this->assertSame('Deferred', $calculated->payment_status);
        $this->assertSame('unbilled', $calculated->billing_status);
        $this->assertSame('Completed', $calculated->getRawOriginal('status'));
        $this->assertSame(0.0, (float) $calculated->getRawOriginal('wallet_amount'));
        $this->assertSame(0.0, (float) $calculated->getRawOriginal('promo_amount'));
        $this->assertSame(0.0, (float) $calculated->getRawOriginal('owe_amount'));
        $this->assertSame(1, Payment::where('trip_id', $trip->id)->count());
        $this->assertNull(Payment::first()->correlation_id);
        $this->assertSame('Pending', Payment::first()->driver_payout_status);
        $this->assertSame(Payment::first()->id, DB::table('company_corporate_trip_records')->where('trip_id', $trip->id)->value('payment_id'));
        $this->assertSame(1, $this->account->audits()->where('action', 'trip.finalized')->count());
        $this->assertTrue(app(DriverRatingEligibilityService::class)->shouldShowRating($trip->id));
        $helper->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
        app(CorporateTripFinalizer::class)->finalize($trip);
        $this->assertSame(1, Payment::where('trip_id', $trip->id)->count());
        $this->assertSame(1, $this->account->audits()->where('action', 'trip.finalized')->count());
        $this->assertFalse($helper->needsRealtimeRecalculation($calculated));
    }

    public function test_financial_snapshot_and_currency_are_immutable_after_finalization(): void
    {
        $trip = app(InvoiceHelper::class)->calculation(['trip_id' => $this->trip()->id, 'save_to_trip_table' => 1]);
        DB::table('currency')->insert(['code' => 'USD', 'default_currency' => '0', 'symbol' => '$', 'rate' => '0.01']);
        DB::table('users')->where('id', 1)->update(['currency_code' => 'USD', 'corporate_account_id' => null, 'corporate_role' => null]);
        config(['corporate.bookings_enabled' => false]);
        session()->put('currency', 'USD');
        $trip->refresh();
        $this->assertSame('KES', $trip->currency_code);
        $this->assertSame('1470.00', $trip->total_fare);
        $this->assertFalse($trip->canConvert());
        $snapshot = $trip->corporate_financial_snapshot;
        foreach ([['total_fare' => '1.00'], ['payment_status' => 'Completed'], ['is_calculation' => 0], ['completed_at' => now()], ['wallet_amount' => '10.00']] as $changes) {
            try {
                $trip->update($changes);
                $this->fail('A finalized charge must not be rewritten by trip updates.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('billing_mode', $e->errors());
            }
            $trip->refresh();
        }
        $this->assertSame($snapshot, $trip->corporate_financial_snapshot);
        $this->assertContains('corporate_financial_snapshot', $trip->getHidden());
    }

    public function test_finalization_failure_rolls_back_calculation_and_can_be_retried_without_new_payout(): void
    {
        $trip = $this->trip();
        Payment::create(['trip_id' => $trip->id, 'driver_payout_status' => 'Pending', 'admin_payout_status' => 'Pending']);
        Payment::create(['trip_id' => $trip->id, 'driver_payout_status' => 'Pending', 'admin_payout_status' => 'Pending']);
        try {
            app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
            $this->fail('Conflicting legacy payout records require reconciliation.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('trip', $e->errors());
        }
        $this->assertSame(0, (int) $trip->fresh()->is_calculation);
        $this->assertNull($trip->fresh()->billing_finalized_at);
        $this->assertSame(2, Payment::count());
        $this->assertSame(0, $this->account->audits()->count());
        // Explicit fixture reconciliation, not automatic production deletion.
        Payment::orderByDesc('id')->first()->delete();
        $calculated = app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
        $this->assertSame('1470.00', $calculated->corporate_amount);
        $this->assertSame(1, Payment::count());
    }

    public function test_gateway_validation_and_callback_cannot_settle_corporate_trips(): void
    {
        $trip = $this->trip();
        request()->merge(['trip_id' => $trip->id]);
        $service = app(\App\Services\BusinessLogic::class);
        $this->assertSame('0', $service->Fortrip(['trip_id' => $trip->id], User::findOrFail(1))['status_code']);
        $this->expectException(ValidationException::class);
        $service->tripPayment(['trip_id' => $trip->id, 'transaction_id' => 'FORGED'], User::findOrFail(1));
    }

    public function test_uncompleted_and_uncalculated_trips_cannot_be_finalized_directly(): void
    {
        $trip = $this->trip(['completed_at' => null, 'end_trip' => null, 'status' => 'started']);
        $this->expectException(ValidationException::class);
        app(CorporateTripFinalizer::class)->finalize($trip);
    }

    public function test_deferred_earnings_are_not_withdrawable_or_reported_as_rider_cash(): void
    {
        $trip = app(InvoiceHelper::class)->calculation(['trip_id' => $this->trip()->id, 'save_to_trip_table' => 1]);
        $this->assertSame('1170.00', $trip->driver_payout);
        $this->assertSame('0.00', $trip->payable_driver_payout);
        $this->assertSame('0.00', $trip->rider_paid_amount);
        $this->assertSame(0.0, (float) $trip->cash_collectable);
        // Only the settlement service may perform this internal financial update.
        DB::table('trips')->where('id', $trip->id)->update(['payment_status' => 'Completed']);
        $this->assertSame('1170.00', $trip->fresh()->payable_driver_payout);
    }

    public function test_direct_gateway_entries_reject_company_billing_before_external_requests(): void
    {
        $trip = $this->trip();
        $rider = User::findOrFail(1);
        foreach ([\App\Http\Controllers\Api\MpesaController::class => 'customerMpesaSTKPush', \App\Http\Controllers\Api\FlutterwaveController::class => 'flutterwave_payment'] as $controller => $method) {
            \JWTAuth::shouldReceive('parseToken')->once()->andReturnSelf();
            \JWTAuth::shouldReceive('authenticate')->once()->andReturn($rider);
            $request = \Illuminate\Http\Request::create('/payment', 'POST', ['pay_for' => 'trip', 'trip_id' => $trip->id, 'user_id' => $rider->id, 'token' => 'test-token']);
            try {
                app($controller)->$method($request);
                $this->fail('A gateway must not initiate corporate collection.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('trip_id', $e->errors());
            }
        }
        $this->assertSame(0, Payment::count());
    }

    public function test_gateway_guard_validates_identity_and_requires_explicit_trip(): void
    {
        $trip = $this->trip();
        $guard = app(\App\Services\Corporate\CorporateCollectionGuard::class);
        try {
            $guard->assertPersonalTrip($trip, User::findOrFail(2));
            $this->fail('Only the owning rider can pay.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->expectException(ValidationException::class);
        $guard->validateEntry(\Illuminate\Http\Request::create('/payment', 'POST', ['pay_for' => 'trip']), User::findOrFail(1));
    }

    public function test_realtime_completion_is_atomic_and_retry_releases_driver_only_after_finalization(): void
    {
        $trip = $this->trip(['completed_at' => null, 'end_trip' => null, 'status' => 'started']);
        DB::table('users')->where('id', 2)->update(['current_trip_id' => $trip->id, 'is_available' => false]);
        config(['corporate.bookings_enabled' => false]);
        $this->mock(InvoiceHelper::class, fn ($mock) => $mock->shouldReceive('calculation')->once()->andThrow(new \RuntimeException('Accounting unavailable')));
        $dispatch = app(\App\Services\Dispatch\TripDispatchService::class);
        try {
            $dispatch->transitionTrip($trip, 'completed', 'driver');
            $this->fail('Completion must roll back if financial finalization fails.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Accounting unavailable', $e->getMessage());
        }
        $this->assertSame('started', $trip->fresh()->getRawOriginal('status'));
        $this->assertNull($trip->fresh()->completed_at);
        $this->assertFalse(User::findOrFail(2)->is_available);
        $this->assertSame(0, Payment::count());
        $this->app->instance(InvoiceHelper::class, new InvoiceHelper(app(\App\Repositories\DriverOweAmountRepository::class)));
        Event::fake([\App\Events\TripStatusUpdated::class, \App\Events\TripCompleted::class, \App\Events\DriverRatingTriggerEvent::class]);
        // Keep the physical trip interval deterministic for the real fare engine.
        $this->travelTo(\Carbon\Carbon::parse('2026-10-07 10:15:00'));
        $completed = $dispatch->transitionTrip($trip->fresh(), 'completed', 'driver');
        $this->assertNotNull($completed->billing_finalized_at);
        $this->assertSame('Deferred', $completed->payment_status);
        $this->assertTrue(User::findOrFail(2)->is_available);
        $this->assertNull(User::findOrFail(2)->current_trip_id);
        $again = $dispatch->transitionTrip($completed, 'completed', 'driver');
        $this->assertSame($completed->corporate_amount, $again->corporate_amount);
        $this->assertSame(1, Payment::count());
        Event::assertDispatched(\App\Events\TripCompleted::class, 1);
    }
}
