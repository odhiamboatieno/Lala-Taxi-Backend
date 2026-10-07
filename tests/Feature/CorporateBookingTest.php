<?php

namespace Tests\Feature;

use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\SiteSettings;
use App\Models\Trips;
use App\Models\User;
use App\Services\Corporate\CorporateBillingContext;
use App\Services\Corporate\CorporateCapabilities;
use App\Services\Dispatch\ManualBookingPayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Tests\Concerns\CreatesCorporateSchema;
use Tests\TestCase;

class CorporateBookingTest extends TestCase
{
    use CreatesCorporateSchema;

    private CompanyCorporateAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateSchema();
        (require database_path('migrations/2026_10_07_000003_add_corporate_schedule_blocking_evidence.php'))->up();
        Schema::create('site_settings', function (Blueprint $t) { $t->increments('id'); $t->string('name'); $t->string('value'); });
        Schema::create('saas_feature_modules', function (Blueprint $t) { $t->increments('id'); $t->string('slug'); $t->boolean('is_installed'); $t->boolean('is_enabled'); });
        Schema::create('company_saas_settings', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('company_id'); $t->boolean('is_saas_enabled'); });
        Schema::create('car_type', function (Blueprint $t) { $t->increments('id'); $t->string('is_pool')->default('No'); });
        DB::table('site_settings')->insert(['name' => 'saas_dispatch_module_enabled', 'value' => '1']);
        DB::table('saas_feature_modules')->insert(['slug' => 'accounts_invoicing', 'is_installed' => true, 'is_enabled' => true]);
        $this->app->bind('site_settings', fn () => SiteSettings::all());
        config(['corporate.bookings_enabled' => true]);
        $this->account = CompanyCorporateAccount::create([
            'company_id' => 1, 'account_name' => 'Employer', 'legal_name' => 'Employer Ltd',
            'billing_address' => 'Kenya', 'contact_email' => 'finance@example.test', 'currency_code' => 'KES',
            'billing_timezone' => 'Africa/Nairobi', 'status' => 'Active',
        ]);
        DB::table('users')->where('id', 1)->update(['corporate_account_id' => $this->account->id, 'corporate_role' => 'member']);
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function context(): CorporateBillingContext { return app(CorporateBillingContext::class); }

    private function schedule(array $attributes = []): ScheduleRide
    {
        $schedule = new ScheduleRide;
        $schedule->forceFill(array_merge(['user_id' => 1, 'payment_method' => 'Cash', 'schedule_date' => '2026-10-09', 'schedule_time' => '10:30'], $attributes))->save();
        return $schedule->fresh();
    }

    private function offer(array $attributes = []): RideRequest
    {
        return RideRequest::create(array_merge(['user_id' => 1, 'payment_mode' => 'Cash'], $attributes));
    }

    private function rejects(callable $operation, string $field = 'billing_mode'): void
    {
        try {
            $operation();
            $this->fail('Invalid corporate billing must fail without personal fallback.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());
        }
    }

    public function test_membership_defaults_to_corporate_but_explicit_personal_stays_personal(): void
    {
        $rider = User::findOrFail(1);
        $this->assertSame('corporate', $this->context()->resolveForBooking($rider)['billing_mode']);
        $this->assertSame('personal', $this->context()->resolveForBooking($rider, ['billing_mode' => 'personal'])['billing_mode']);
        $this->assertSame('personal', $this->context()->resolveForBooking(User::findOrFail(2))['billing_mode']);
        $this->rejects(fn () => $this->context()->resolveForBooking($rider, ['billing_mode' => 'personal', 'payment_method' => 'Corporate']));
        $this->rejects(fn () => $this->context()->resolveForBooking($rider, ['corporate_account_id' => 999]));
    }

    public function test_wallet_foreign_currency_pool_parcel_and_disabled_bookings_fail(): void
    {
        $pool = DB::table('car_type')->insertGetId(['is_pool' => 'Yes']);
        foreach ([['payment_mode' => 'Wallet'], ['is_wallet' => 'Yes'], ['currency_code' => 'USD'], ['car_id' => $pool], ['service_category' => 'parcel']] as $payload) {
            $this->rejects(fn () => $this->context()->resolveForBooking(User::findOrFail(1), $payload));
        }
        config(['corporate.bookings_enabled' => false]);
        $this->rejects(fn () => $this->schedule());
        $this->assertTrue(app(CorporateCapabilities::class)->managementEnabled($this->account->company));
        $this->assertSame(0, DB::table('schedule_ride')->count());
    }

    public function test_schedule_offer_retry_and_accept_preserve_the_server_snapshot(): void
    {
        $schedule = $this->schedule();
        $this->assertSame('Corporate', $schedule->payment_method);
        $offer = $this->offer(['schedule_id' => (string) $schedule->id]);
        $offer->update(['group_id' => $offer->id]);
        $this->account->update(['account_name' => 'Renamed employer']);
        $retry = $this->offer(['group_id' => $offer->id]);
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'request_id' => $retry->id, 'currency_code' => 'USD']));
        $this->assertSame($schedule->billing_snapshot, $trip->billing_snapshot);
        $this->assertSame('Employer', $trip->billing['account_name']);
        $this->assertSame('KES', $trip->getRawOriginal('currency_code'));
        $this->assertSame('Corporate', $trip->payment_mode);
        $this->assertSame('pending', $trip->billing_status);
        $this->assertSame(1, DB::table('company_corporate_trip_records')->where('trip_id', $trip->id)->count());
        $trip = $trip->fresh();
        $trip->is_convert = false;
        $payload = $trip->setAppends(['billing'])->toArray();
        $this->assertArrayNotHasKey('billing_snapshot', $payload);
        $this->assertFalse($payload['billing']['collection_required']);
        $this->assertStringNotContainsString('finance@example.test', json_encode($payload['billing']));
    }

    public function test_stored_personal_schedule_does_not_inherit_later_membership(): void
    {
        $schedule = $this->schedule(['billing_mode' => 'personal']);
        $offer = $this->offer(['schedule_id' => (string) $schedule->id]);
        $trip = Trips::create(['user_id' => 1, 'request_id' => $offer->id]);
        $this->assertFalse($trip->isCorporate());
        $this->assertNull($trip->corporate_account_id);
        $this->assertSame('Cash', $offer->payment_mode);
        $this->rejects(fn () => $this->offer(['schedule_id' => (string) $schedule->id, 'payment_mode' => 'Corporate']));
    }

    public function test_revocation_blocks_pending_acceptance_but_not_an_accepted_trip(): void
    {
        $offer = $this->offer();
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'request_id' => $offer->id]));
        DB::table('users')->where('id', 1)->update(['corporate_account_id' => null, 'corporate_role' => null]);
        $this->rejects(fn () => $this->context()->validatePending($offer));
        $this->rejects(fn () => Trips::create(['user_id' => 1, 'request_id' => $offer->id]));
        config(['corporate.bookings_enabled' => false]);
        $trip->total_fare = '1180.00';
        $trip->save();
        $this->assertSame(1180.0, (float) $trip->fresh()->getRawOriginal('total_fare'));
        $this->assertTrue($trip->fresh()->isCorporate());
    }

    public function test_source_identity_and_payer_mutation_are_rejected(): void
    {
        $offer = $this->offer();
        $this->rejects(fn () => Trips::create(['user_id' => 2, 'request_id' => $offer->id]));
        $this->rejects(fn () => Trips::create(['user_id' => 1, 'payment_mode' => 'Corporate']));
        $this->rejects(fn () => $offer->update(['billing_mode' => 'personal']));
        $offer->refresh();
        $this->rejects(fn () => $offer->update(['payment_mode' => 'Cash']));
        $this->assertSame('Corporate', $offer->fresh()->payment_mode);
    }

    public function test_manual_corporate_intent_requires_a_known_eligible_rider(): void
    {
        $payment = app(ManualBookingPayment::class);
        $this->assertSame('Corporate', $payment->intent('Corporate'));
        $this->rejects(fn () => $payment->resolve('Corporate'), 'payment_method');
        $this->rejects(fn () => $payment->resolve('Corporate', User::findOrFail(2)));
        $this->assertSame(['payment_method' => 'Corporate', 'is_wallet' => 'No'], $payment->resolve('Corporate', User::findOrFail(1)));
        foreach (['Card', 'Wallet', 'Invoice'] as $method) {
            $this->rejects(fn () => $payment->intent($method), 'payment_method');
        }
    }

    public function test_reschedule_revalidates_and_only_eligible_riders_can_resume_blocked_schedule(): void
    {
        $schedule = $this->schedule();
        DB::table('schedule_ride')->where('id', $schedule->id)->update(['assignment_status' => 'billing_blocked', 'billing_blocked_at' => now(), 'billing_blocked_reason' => 'corporate_eligibility']);
        $schedule->refresh();
        config(['corporate.bookings_enabled' => false]);
        $this->rejects(fn () => $schedule->forceFill(['schedule_time' => '11:30'])->save());
        config(['corporate.bookings_enabled' => true]);
        $schedule->refresh();
        $schedule->schedule_time = '11:30';
        $schedule->save();
        $this->assertSame('unassigned', $schedule->fresh()->assignment_status);
        $this->assertNull($schedule->fresh()->billing_blocked_at);
    }

    public function test_existing_active_trip_blocks_competing_dispatch_group_acceptance(): void
    {
        $offer = $this->offer();
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'request_id' => $offer->id, 'status' => 'driver_assigned']));
        $otherOffer = $this->offer();
        $this->rejects(fn () => DB::transaction(fn () => $this->context()->assertCanAccept($otherOffer)), 'trip');
        $this->assertSame(1, DB::table('trips')->count());
    }

    public function test_cron_block_is_retained_and_notified_once_without_canceling_other_schedules(): void
    {
        $schedule = $this->schedule();
        $other = $this->schedule();
        $this->mock(\App\Http\Helper\RequestHelper::class, fn ($mock) => $mock->shouldReceive('checkAndSendMessage')->once());
        $controller = new class extends \App\Http\Controllers\Api\CronController {
            public function pause(ScheduleRide $schedule): void
            {
                $this->blockCorporateSchedule($schedule, ValidationException::withMessages(['billing_mode' => 'Unavailable']));
            }
        };
        $controller->pause($schedule);
        $controller->pause($schedule);
        $this->assertSame('Pending', $schedule->fresh()->status);
        $this->assertSame('billing_blocked', $schedule->fresh()->assignment_status);
        $this->assertNotNull($schedule->fresh()->billing_blocked_at);
        $this->assertSame('corporate_eligibility', $schedule->fresh()->billing_blocked_reason);
        $this->assertNull($other->fresh()->assignment_status);
    }

    public function test_realtime_offer_and_retry_expose_only_the_safe_billing_summary(): void
    {
        $offer = $this->offer();
        $offer->group_id = $offer->id;
        $offer->save();
        $dispatch = new class(app(\App\Services\Dispatch\NearbyDriverService::class)) extends \App\Services\Dispatch\TripDispatchService {
            public function offerPayload(RideRequest $offer): array
            {
                return $this->buildRideRequestPayload($offer, User::findOrFail($offer->user_id), [], now()->addSeconds(20), 'offer');
            }
            public function retryPayload(RideRequest $offer, array $overrides): array
            {
                return $this->payloadFromRideRequest($offer, $overrides);
            }
        };
        $payload = $dispatch->offerPayload($offer);
        $this->assertSame('Corporate', $payload['payment_mode']);
        $this->assertSame('Employer', $payload['billing']['account_name']);
        $this->assertFalse($payload['billing']['collection_required']);
        $this->assertArrayNotHasKey('billing_snapshot', $payload);
        $retry = $dispatch->retryPayload($offer, ['billing_mode' => 'personal', 'corporate_account_id' => 999, 'billing_snapshot' => ['account_name' => 'Forged']]);
        $this->assertSame($offer->billing_snapshot, $retry['billing_snapshot']);
        $this->assertSame($this->account->id, $retry['corporate_account_id']);
        $this->assertSame('corporate', $retry['billing_mode']);
    }
}
