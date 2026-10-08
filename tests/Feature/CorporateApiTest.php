<?php

namespace Tests\Feature;

use App\Http\Helper\InvoiceHelper;
use App\Models\Admin;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\User;
use App\Services\Corporate\CorporateInvoiceGenerator;
use App\Services\Corporate\CorporateInvoiceLifecycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporateApiTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateFinanceSchema();
        (require database_path('migrations/2026_10_07_000005_add_corporate_invoice_settlement_and_delivery.php'))->up();
        $this->account->update(['billing_cycle' => 'monthly']);
        DB::table('users')->insert(['id' => 3, 'company_id' => 1, 'user_type' => 'Rider', 'corporate_account_id' => $this->account->id, 'corporate_role' => 'member']);
        DB::table('users')->insert(['id' => 4, 'company_id' => 1, 'user_type' => 'Rider']);
        config(['jwt.secret' => 'corporate-api-test-only-signing-key-at-least-32-characters', 'corporate.manager_finance_access' => false]);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-07 12:00:00', 'UTC'));
        Event::fake([\App\Events\DriverRatingTriggerEvent::class]);
    }

    protected function tearDown(): void
    {
        if (DB::getDriverName() !== 'sqlite' && DB::connection()->getDatabaseName() !== 'lala_corporate_schema_test') {
            throw new \RuntimeException('Refusing transport fixture cleanup outside the isolated test database.');
        }
        foreach (['parcel_requests', 'trip_requests', 'vehicle', 'profile_picture', 'driver_documents', 'driver_location', 'cancel_reasons', 'cancel'] as $table) { Schema::dropIfExists($table); }
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function callAs(int $id, string $path)
    {
        $token = \JWTAuth::fromUser(User::findOrFail($id));
        $this->assertSame($id, \JWTAuth::setToken($token)->authenticate()->id);
        return $this->withToken($token)->getJson('/api/corporate/'.$path);
    }

    private function role(string $role): void
    {
        DB::table('users')->where('id', 1)->update(['corporate_role' => $role]);
    }

    private function charge(int $user = 1): Trips
    {
        $offer = RideRequest::create(['user_id' => $user, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $trip = DB::transaction(fn () => Trips::create(['user_id' => $user, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'Payment',
            'completed_at' => '2026-09-29 10:15:00', 'end_trip' => '2026-09-29 10:15:00',
            'arrive_time' => '2026-09-29 10:04:00', 'begin_trip' => '2026-09-29 10:05:00', 'tips' => '100.00', 'toll_fee' => '50.00']));
        return app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
    }

    private function actor(): Admin
    {
        $actor = \Mockery::mock(Admin::class)->makePartial();
        $actor->id = 1;
        $actor->shouldReceive('hasPermission')->with(['manage_company_accounts_invoicing'])->andReturn(true);
        return $actor;
    }

    public function test_profile_requires_verified_rider_and_null_membership_is_normal(): void
    {
        $this->getJson('/api/corporate/profile')->assertUnauthorized()->assertJsonPath('status_code', '0');
        $this->callAs(2, 'profile')->assertForbidden()->assertJsonPath('status_code', '0');
        $this->callAs(4, 'profile')->assertOk()->assertJsonPath('corporate', null)->assertJsonPath('default_billing.mode', 'personal');
        $response = $this->callAs(1, 'profile')->assertOk()->assertJsonPath('corporate.role', 'member')->assertJsonPath('corporate.booking_available', true)
            ->assertJsonPath('default_billing.collection_required', false);
        $this->assertArrayNotHasKey('credit', $response->json('corporate'));
        $this->assertStringNotContainsString('finance@example.test', $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_employee_can_only_view_own_trips_and_never_users_invoices_or_account_credit(): void
    {
        $own = $this->charge(1);
        $other = $this->charge(3);
        $response = $this->callAs(1, 'trips?account_id=999&per_page=1')->assertOk()->assertJsonPath('pagination.total', 1);
        $this->assertSame($own->id, $response->json('data.0.id'));
        $this->callAs(1, 'trips/'.$other->id)->assertNotFound();
        $this->callAs(1, 'trips/'.$own->id)->assertOk()->assertJsonPath('trip.billing.collection_required', false)->assertJsonPath('trip.payment_status', 'Deferred');
        $this->callAs(1, 'users')->assertForbidden();
        $this->callAs(1, 'invoices')->assertForbidden();
        $dashboard = $this->callAs(1, 'dashboard?period_start=2026-09-01')->assertOk()->assertJsonPath('dashboard.scope', 'self')
            ->assertJsonPath('dashboard.finalized_trip_count', 1)->assertJsonPath('dashboard.finalized_amount', '1470.00');
        $this->assertArrayNotHasKey('credit', $dashboard->json('dashboard'));
        $this->assertArrayNotHasKey('active_member_count', $dashboard->json('dashboard'));
        $this->assertStringNotContainsString('billing_snapshot', $response->getContent());
    }

    public function test_manager_has_account_metrics_and_minimum_member_fields_but_needs_explicit_invoice_policy(): void
    {
        $this->charge(1);
        $this->charge(3);
        $this->role('manager');
        $this->callAs(1, 'trips')->assertOk()->assertJsonPath('pagination.total', 2);
        $this->callAs(1, 'dashboard?period_start=2026-09-01')->assertOk()->assertJsonPath('dashboard.scope', 'account')
            ->assertJsonPath('dashboard.finalized_amount', '2940.00')->assertJsonPath('dashboard.active_member_count', 2);
        $users = $this->callAs(1, 'users?per_page=1&page=2')->assertOk()->assertJsonPath('pagination.total', 2);
        $this->assertSame(['id', 'name', 'role', 'status'], array_keys($users->json('data.0')));
        $this->callAs(1, 'invoices')->assertForbidden();
        config(['corporate.manager_finance_access' => true]);
        $this->callAs(1, 'invoices')->assertOk()->assertJsonPath('pagination.total', 0);
    }

    public function test_finance_invoice_detail_pdf_and_metrics_are_owned_and_do_not_count_drafts_as_issued(): void
    {
        $this->charge();
        $invoice = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        $this->role('finance');
        $this->callAs(1, 'users')->assertForbidden();
        $this->callAs(1, 'dashboard')->assertOk()->assertJsonPath('dashboard.draft_invoice_count', 1)->assertJsonPath('dashboard.issued_unpaid_invoice_count', 0);
        $this->callAs(1, 'invoices/'.$invoice->id)->assertOk()->assertJsonPath('invoice.amount', '1470.00')->assertJsonPath('invoice.items.0.currency_code', 'KES');
        $this->callAs(1, 'invoices/'.$invoice->id.'/download')->assertOk()->assertHeader('content-type', 'application/pdf');
        app(CorporateInvoiceLifecycle::class)->send($invoice, $this->actor());
        $this->callAs(1, 'dashboard')->assertOk()->assertJsonPath('dashboard.draft_invoice_count', 0)->assertJsonPath('dashboard.issued_unpaid_amount', '1470.00');
        $this->callAs(1, 'invoices/999999')->assertNotFound()->assertJsonPath('status_code', '0');
        $this->callAs(3, 'invoices/'.$invoice->id.'/download')->assertForbidden();
    }

    public function test_role_revocation_and_archiving_do_not_enable_new_bookings_or_drop_history(): void
    {
        $trip = $this->charge();
        $this->role('finance');
        $this->account->update(['archived_at' => now('UTC'), 'status' => 'Inactive']);
        config(['corporate.bookings_enabled' => false]);
        $this->callAs(1, 'profile')->assertOk()->assertJsonPath('corporate.booking_available', false)->assertJsonPath('corporate.status', 'Archived');
        $this->callAs(1, 'trips/'.$trip->id)->assertOk();
        $this->role('member');
        $this->callAs(1, 'invoices')->assertForbidden();
        DB::table('users')->where('id', 1)->update(['corporate_account_id' => null, 'corporate_role' => null]);
        $this->callAs(1, 'profile')->assertOk()->assertJsonPath('corporate', null);
        $this->callAs(1, 'dashboard')->assertForbidden();
    }

    public function test_foreign_account_and_legacy_headers_are_never_exposed_to_finance(): void
    {
        $this->charge();
        $invoice = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        $other = CompanyCorporateAccount::create(['company_id' => 1, 'account_name' => 'Other account']);
        DB::table('users')->where('id', 4)->update(['corporate_account_id' => $other->id, 'corporate_role' => 'finance']);
        $this->callAs(4, 'invoices/'.$invoice->id)->assertNotFound();
        $this->callAs(4, 'invoices/'.$invoice->id.'/download')->assertNotFound();
        $this->callAs(4, 'trips/'.Trips::firstOrFail()->id)->assertNotFound();
        $this->role('finance');
        DB::table('company_corporate_invoices')->where('id', $invoice->id)->update(['trip_backed' => false]);
        $this->callAs(1, 'invoices')->assertOk()->assertJsonPath('pagination.total', 0);
        $this->callAs(1, 'invoices/'.$invoice->id.'/download')->assertNotFound();
    }

    public function test_invalid_filters_have_localized_envelopes_and_proper_http_status(): void
    {
        foreach (['trips?per_page=51', 'trips?page=0', 'trips?billing_status=forged', 'dashboard?period_start=2026-09-02'] as $path) {
            $this->callAs(1, $path)->assertUnprocessable()->assertJsonPath('status_code', '0')->assertJsonStructure(['status_message', 'errors']);
        }
    }

    private function transportSchema(): void
    {
        Schema::table('trips', function (Blueprint $t) {
            $t->unsignedInteger('company_id')->nullable(); $t->string('operator_type')->nullable(); $t->unsignedInteger('operator_id')->nullable();
            $t->integer('seats')->default(1); $t->string('map_image')->default('fixture.png');
            $t->text('trip_path')->nullable(); $t->integer('otp')->default(0);
            $t->decimal('remaining_owe_amount', 12, 2)->default(0); $t->json('stop_points')->nullable();
        });
        Schema::table('rating', function (Blueprint $t) { $t->unsignedInteger('user_id')->nullable(); $t->unsignedInteger('driver_id')->nullable(); $t->integer('rider_rating')->default(0); });
        Schema::table('car_type', function (Blueprint $t) { $t->string('car_name')->default('Standard'); $t->string('active_image')->default('standard.png'); });
        Schema::table('schedule_ride', function (Blueprint $t) { $t->string('booking_type')->nullable(); });
        Schema::table('request', function (Blueprint $t) {
            $t->string('timezone')->default('Africa/Nairobi');
            foreach (['pickup_latitude', 'pickup_longitude', 'drop_latitude', 'drop_longitude'] as $column) { $t->decimal($column, 10, 6)->default(0); }
            $t->string('pickup_location')->default('Westlands'); $t->string('drop_location')->default('JKIA');
        });
        Schema::table('users', function (Blueprint $t) { $t->boolean('is_online')->default(false); $t->timestamp('last_seen_at')->nullable(); });
        Schema::create('vehicle', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('user_id'); $t->string('default_type')->default('1'); $t->string('vehicle_name')->nullable(); $t->string('vehicle_number')->nullable(); });
        (require database_path('migrations/2026_08_16_000126_create_trip_requests_table.php'))->up();
        Schema::create('profile_picture', function (Blueprint $t) { $t->unsignedInteger('user_id'); $t->string('src')->nullable(); $t->string('photo_source')->default('Local'); });
        Schema::create('driver_documents', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('user_id'); $t->string('vehicle_number')->nullable(); $t->string('vehicle_name')->nullable(); });
        Schema::create('driver_location', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('user_id'); $t->decimal('latitude', 10, 6)->default(0); $t->decimal('longitude', 10, 6)->default(0); });
        Schema::create('cancel_reasons', function (Blueprint $t) { $t->increments('id'); $t->string('cancelled_by'); $t->string('status')->default('Active'); });
        Schema::create('cancel', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('trip_id'); });
        DB::table('cancel_reasons')->insert(['id' => 1, 'cancelled_by' => 'Rider']);
        if (DB::getDriverName() === 'sqlite') {
            // Compatibility for legacy MySQL SQL; the same contracts also run on actual MySQL.
            DB::connection()->getPdo()->sqliteCreateFunction('FORMAT', fn ($value, $decimals) => number_format((float) $value, (int) $decimals), 2);
            DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$values) => implode('', $values), -1);
        }
    }

    private function legacyAs(int $user, string $path, array $query = [])
    {
        $token = \JWTAuth::fromUser(User::findOrFail($user));
        return $this->withToken($token)->getJson('/api/'.$path.'?'.http_build_query(array_merge(['token' => $token], $query)));
    }

    public function test_realtime_receipts_survive_revocation_without_becoming_active_or_exposing_bill_to_details(): void
    {
        $this->transportSchema();
        $trip = $this->charge();
        DB::table('request')->update(['status' => 'Accepted']);
        DB::table('users')->where('id', 1)->update(['corporate_account_id' => null, 'corporate_role' => null]);
        $this->account->update(['account_name' => 'Renamed employer']);
        config(['corporate.bookings_enabled' => false]);
        foreach ([1, 2] as $participant) {
            $response = $this->legacyAs($participant, 'trips/'.$trip->id)->assertOk()
                ->assertJsonPath('trip.billing.account_name', 'Employer')
                ->assertJsonPath('trip.billing.collection_required', false)
                ->assertJsonPath('trip.payment_status', 'Deferred')
                ->assertJsonPath('trip.total_fare', '1470.00')
                ->assertJsonPath('trip.currency_code', 'KES');
            $this->assertStringNotContainsString('finance@example.test', $response->getContent());
            $this->assertStringNotContainsString('billing_snapshot', $response->getContent());
            $this->legacyAs($participant, 'trips/current')->assertOk()->assertJsonPath('status_code', '0');
        }
        $this->legacyAs(4, 'trips/'.$trip->id)->assertForbidden();
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
    }

    public function test_realtime_search_resume_has_request_identity_and_pending_safe_billing(): void
    {
        $this->transportSchema();
        $request = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '', 'group_id' => 10]);
        $response = $this->legacyAs(1, 'trips/current')->assertOk()->assertJsonPath('status_code', '1')
            ->assertJsonPath('trip.id', null)->assertJsonPath('trip.request_id', $request->id)
            ->assertJsonPath('trip.payment_mode', 'Corporate')->assertJsonPath('trip.payment_status', 'Pending')
            ->assertJsonPath('trip.billing.account_name', 'Employer')->assertJsonPath('trip.billing.collection_required', false);
        $this->assertStringNotContainsString('finance@example.test', $response->getContent());
        $this->legacyAs(4, 'trips/current')->assertOk()->assertJsonPath('status_code', '0');
    }

    public function test_alternate_offer_acceptance_revalidates_membership_and_broadcasts_safe_billing(): void
    {
        $this->transportSchema();
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'request_id' => $offer->id, 'status' => 'searching_driver', 'company_id' => 1]));
        $request = \App\Models\TripRequest::create(['trip_id' => $trip->id, 'driver_id' => 2, 'rider_id' => 1, 'expires_at' => now()->addMinute()]);
        $dispatch = app(\App\Services\Dispatch\TripDispatchService::class);
        DB::table('users')->where('id', 1)->update(['corporate_role' => null]);
        try {
            $dispatch->acceptTripRequest($request, User::findOrFail(2));
            $this->fail('Revoked membership must block pending acceptance.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('billing_mode', $e->errors());
        }
        $this->assertSame('pending', $request->fresh()->status);
        $this->assertNull($trip->fresh()->driver_id);
        DB::table('users')->where('id', 1)->update(['corporate_role' => 'member']);
        $other = DB::transaction(fn () => Trips::create(['user_id' => 1, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'started']));
        try {
            $dispatch->acceptTripRequest($request, User::findOrFail(2));
            $this->fail('A different active trip must block acceptance.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('trip', $e->errors());
        }
        $other->update(['status' => 'cancelled']);
        Event::fake([\App\Events\TripDriverAssigned::class, \App\Events\TripStatusUpdated::class]);
        $accepted = $dispatch->acceptTripRequest($request, User::findOrFail(2));
        $this->assertSame('driver_arriving', $accepted->status);
        $this->assertSame('Corporate', $accepted->payment_mode);
        $this->legacyAs(2, 'trips/current')->assertOk()->assertJsonPath('trip.billing.account_name', 'Employer');
        Event::assertDispatched(\App\Events\TripDriverAssigned::class, fn ($event) => $event->payload['billing']['collection_required'] === false
            && $event->payload['payment_mode'] === 'Corporate' && $event->payload['payment_status'] === 'Pending'
            && !str_contains(json_encode($event->payload), 'finance@example.test'));
    }

    public function test_driver_operations_and_gps_reject_riders_foreign_trips_and_completed_charges(): void
    {
        $this->transportSchema();
        $riderToken = \JWTAuth::fromUser(User::findOrFail(1));
        foreach (['online', 'offline', 'location/update'] as $action) {
            $this->withToken($riderToken)->postJson('/api/driver/'.$action, ['lat' => -1.2676, 'lng' => 36.8108])->assertForbidden();
        }
        $driverToken = \JWTAuth::fromUser(User::findOrFail(2));
        $completed = $this->charge();
        $this->withToken($driverToken)->postJson('/api/driver/location/update', ['lat' => -1.2676, 'lng' => 36.8108, 'trip_id' => $completed->id])->assertForbidden();
        DB::table('users')->insert(['id' => 9, 'company_id' => 1, 'user_type' => 'Driver']);
        $offer = RideRequest::create(['user_id' => 3, 'driver_id' => 9, 'car_id' => 1, 'schedule_id' => '']);
        $foreign = DB::transaction(fn () => Trips::create(['user_id' => 3, 'driver_id' => 9, 'request_id' => $offer->id, 'status' => 'started']));
        $this->withToken($driverToken)->postJson('/api/driver/location/update', ['lat' => -1.2676, 'lng' => 36.8108, 'trip_id' => $foreign->id])->assertForbidden();
        $this->assertSame(0, DB::table('driver_location')->count());
        $this->assertSame('Deferred', $completed->fresh()->payment_status);
    }

    public function test_rider_rating_cannot_change_another_riders_trip(): void
    {
        $this->transportSchema();
        $trip = $this->charge();
        $token = \JWTAuth::fromUser(User::findOrFail(4));
        $this->withToken($token)->getJson('/api/trip_rating?'.http_build_query(['trip_id' => $trip->id, 'user_type' => 'Rider', 'rating' => 5]))->assertForbidden();
        $this->assertSame(0, DB::table('rating')->count());
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
    }

    public function test_driver_polling_preserves_saved_corporate_offer_without_private_billing_fields(): void
    {
        $this->transportSchema();
        (require database_path('migrations/2026_08_16_000082_create_parcel_requests_table.php'))->up();
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '', 'status' => 'Pending']);
        $token = \JWTAuth::fromUser(User::findOrFail(2));
        $response = $this->withToken($token)->getJson('/api/driver/current-request')->assertOk()
            ->assertJsonPath('trip_request.id', $offer->id)
            ->assertJsonPath('trip_request.billing.mode', 'corporate')
            ->assertJsonPath('trip_request.billing.account_name', 'Employer')
            ->assertJsonPath('trip_request.billing.collection_required', false)
            ->assertJsonPath('trip_request.payment_mode', 'Corporate');
        foreach (['billing_snapshot', 'finance@example.test', 'billing_address', 'corporate_financial_snapshot'] as $private) {
            $this->assertStringNotContainsString($private, $response->getContent());
        }
    }

    public function test_driver_gps_rate_limit_is_independent_of_general_api_calls(): void
    {
        $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/api/driver/location/update', 'POST'));
        $middleware = app('router')->gatherRouteMiddleware($route);
        $limiter = \Illuminate\Routing\Middleware\ThrottleRequests::class;
        $this->assertContains($limiter.':120,1', $middleware);
        $this->assertContains($limiter.':60,1,driver-location:', $middleware);
        $this->assertNotContains($limiter.':60,1', $middleware);
    }

    public function test_realtime_completion_http_and_events_report_deferred_and_retry_is_idempotent(): void
    {
        $this->transportSchema();
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'started',
            'arrive_time' => now()->subMinutes(11), 'begin_trip' => now()->subMinutes(10)]));
        DB::table('users')->where('id', 2)->update(['current_trip_id' => $trip->id, 'is_available' => false]);
        config(['corporate.bookings_enabled' => false]);
        Event::fake([\App\Events\TripCompleted::class, \App\Events\TripStatusUpdated::class, \App\Events\DriverRatingTriggerEvent::class]);
        $token = \JWTAuth::fromUser(User::findOrFail(2));
        foreach ([1, 2] as $attempt) {
            $this->withToken($token)->postJson('/api/trips/'.$trip->id.'/complete')->assertOk()
                ->assertJsonPath('trip.payment_status', 'Deferred')->assertJsonPath('trip.billing.collection_required', false);
        }
        $this->assertSame(1, DB::table('payment')->count());
        $this->assertTrue(User::findOrFail(2)->is_available);
        $this->assertNull(User::findOrFail(2)->current_trip_id);
        foreach ([\App\Events\TripCompleted::class, \App\Events\TripStatusUpdated::class] as $eventClass) {
            Event::assertDispatched($eventClass, 1);
            Event::assertDispatched($eventClass, fn ($event) => $event->payload['billing']['collection_required'] === false
                && $event->payload['payment_status'] === 'Deferred' && $event->payload['currency_code'] === 'KES'
                && (string) $event->payload['total_fare'] === $trip->fresh()->corporate_amount
                && !str_contains(json_encode($event->payload), 'finance@example.test'));
        }
    }

    public function test_legacy_driver_details_and_history_preserve_private_saved_billing_and_currency(): void
    {
        $this->transportSchema();
        $trip = $this->charge();
        DB::table('currency')->insert(['code' => 'USD', 'symbol' => '$', 'rate' => '0.01', 'default_currency' => '0']);
        DB::table('users')->where('id', 2)->update(['currency_code' => 'USD']);
        $response = $this->legacyAs(2, 'get_trip_details', ['trip_id' => $trip->id])->assertOk()
            ->assertJsonPath('billing.account_name', 'Employer')->assertJsonPath('payment_status', 'Deferred')
            ->assertJsonPath('riders.0.total_fare', '1470.00')->assertJsonPath('riders.0.earnings_pending', true)
            ->assertJsonPath('riders.0.driver_payout', '1170.00');
        $this->assertStringNotContainsString('finance@example.test', $response->getContent());
        $this->assertStringNotContainsString('corporate_financial_snapshot', $response->getContent());
        $this->legacyAs(2, 'get_completed_trips', ['page' => 1])->assertOk()->assertJsonPath('data.0.total_fare', '1470.00')
            ->assertJsonPath('data.0.currency_code', 'KES')->assertJsonPath('data.0.billing.collection_required', false)
            ->assertJsonPath('data.0.driver_earnings', 'KES 1170.00')
            ->assertJsonStructure(['data' => [['created_at', 'total_km', 'name']]]);
        $this->legacyAs(1, 'get_past_trips', ['page' => 1])->assertOk()->assertJsonPath('data.0.billing.mode', 'corporate');
        $this->legacyAs(2, 'get_invoice', ['trip_id' => $trip->id])->assertOk()->assertJsonPath('billing.collection_required', false)
            ->assertJsonPath('payment_details.payment_status', 'Deferred');
        $this->assertFalse(Trips::$withoutAppends);
    }

    public function test_history_metadata_is_limited_to_the_authenticated_trip_participant(): void
    {
        $this->transportSchema();
        DB::table('users')->where('id', 1)->update(['first_name' => 'Njeri', 'last_name' => 'Mwangi']);
        $trip = $this->charge();
        $history = $this->legacyAs(2, 'get_completed_trips', ['page' => 1])->assertOk()
            ->assertJsonPath('data.0.name', 'Njeri Mwangi');
        $this->assertSame(20.0, (float) $history->json('data.0.total_km'));
        $this->assertSame($trip->getRawOriginal('created_at'), $history->json('data.0.created_at'));
        $this->assertArrayNotHasKey('email', $history->json('data.0'));
        $this->assertArrayNotHasKey('mobile_number', $history->json('data.0'));
        $this->legacyAs(4, 'get_past_trips', ['page' => 1])->assertOk()->assertJsonPath('status_code', '0')
            ->assertJsonMissing(['name' => 'Njeri Mwangi']);
    }

    public function test_legacy_mutations_reject_foreign_participants_and_finalized_charge_reopening(): void
    {
        $this->transportSchema();
        $trip = $this->charge();
        DB::table('users')->insert(['id' => 5, 'company_id' => 1, 'user_type' => 'Driver']);
        $before = DB::table('trips')->find($trip->id);
        $this->legacyAs(4, 'get_trip_details', ['trip_id' => $trip->id])->assertNotFound();
        $this->legacyAs(4, 'arive_now', ['trip_id' => $trip->id])->assertForbidden();
        $this->legacyAs(4, 'begin_trip', ['trip_id' => $trip->id, 'begin_latitude' => -1, 'begin_longitude' => 36])->assertForbidden();
        $this->legacyAs(5, 'begin_trip', ['trip_id' => $trip->id, 'begin_latitude' => -1, 'begin_longitude' => 36])->assertNotFound();
        $this->legacyAs(5, 'arive_now', ['trip_id' => $trip->id])->assertNotFound();
        $token = \JWTAuth::fromUser(User::findOrFail(5));
        $this->withToken($token)->post('/api/map_upload', ['trip_id' => $trip->id, 'token' => $token,
            'image' => \Illuminate\Http\UploadedFile::fake()->image('map.png')], ['Accept' => 'application/json'])->assertNotFound();
        $this->legacyAs(4, 'cancel_trip', ['trip_id' => $trip->id, 'user_type' => 'Rider', 'cancel_reason_id' => 1])->assertNotFound();
        $this->legacyAs(2, 'begin_trip', ['trip_id' => $trip->id, 'begin_latitude' => -1, 'begin_longitude' => 36])->assertStatus(409);
        $this->legacyAs(2, 'arive_now', ['trip_id' => $trip->id])->assertStatus(409);
        $this->legacyAs(1, 'cancel_trip', ['trip_id' => $trip->id, 'user_type' => 'Rider', 'cancel_reason_id' => 1])->assertStatus(409);
        $this->assertSame(0, DB::table('cancel')->count());
        $this->assertEquals($before, DB::table('trips')->find($trip->id));
    }

    public function test_currency_is_never_aggregated_across_incompatible_saved_charges(): void
    {
        $trip = $this->charge();
        DB::table('trips')->where('id', $trip->id)->update(['corporate_currency_code' => null]);
        $this->callAs(1, 'dashboard?period_start=2026-09-01')->assertUnprocessable();
    }

    public function test_personal_history_retains_payment_currency_and_map_contracts(): void
    {
        $this->transportSchema();
        $offer = RideRequest::create(['user_id' => 4, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $trip = Trips::create(['user_id' => 4, 'driver_id' => 2, 'request_id' => $offer->id, 'status' => 'Completed',
            'payment_status' => 'Completed', 'payment_mode' => 'Cash', 'subtotal_fare' => '100.00', 'tips' => '20.00']);
        DB::table('currency')->insert(['code' => 'USD', 'symbol' => '$', 'rate' => '0.01', 'default_currency' => '0']);
        DB::table('users')->where('id', 4)->update(['currency_code' => 'USD']);
        $response = $this->legacyAs(4, 'get_past_trips', ['page' => 1])->assertOk()->assertJsonPath('data.0.billing.mode', 'personal')
            ->assertJsonPath('data.0.payment_mode', 'Cash')->assertJsonPath('data.0.total_fare', '1.20')->assertJsonPath('data.0.currency_code', 'USD')
            ->assertJsonPath('data.0.map_image', url('images/map/'.$trip->id.'/fixture.png'));
        $this->assertArrayNotHasKey('billing_snapshot', $response->json('data.0'));
        $this->assertFalse(Trips::$withoutAppends);
    }

    public function test_mobile_contract_and_finance_settlement_keep_earned_and_withdrawable_distinct(): void
    {
        $this->transportSchema();
        $trip = $this->charge();
        $contract = [];
        foreach (['member', 'manager', 'finance'] as $role) {
            $this->role($role);
            $contract['profile_'.$role] = $this->callAs(1, 'profile')->assertOk()->json();
        }
        $contract['dashboard'] = $this->callAs(1, 'dashboard?period_start=2026-09-01')->assertOk()->json();
        $contract['trips'] = $this->callAs(1, 'trips')->assertOk()->json();
        $contract['driver_detail'] = $this->legacyAs(2, 'get_trip_details', ['trip_id' => $trip->id])->assertOk()->json();
        $contract['driver_checkout'] = $this->legacyAs(2, 'get_driver_checkout_summary', ['trip_id' => $trip->id, 'user_type' => 'Driver'])->assertOk()
            ->assertJsonPath('driver_net_earnings', '1170.00')->assertJsonPath('withdrawable_payout', '0.00')
            ->assertJsonPath('payment_status', 'Deferred')->json();
        $contract['receipt'] = $this->legacyAs(1, 'get_invoice', ['trip_id' => $trip->id])->assertOk()->json();
        $contract['history'] = $this->legacyAs(2, 'get_completed_trips', ['page' => 1])->assertOk()->json();
        \Illuminate\Support\Str::createUlidsUsing(fn () => new \Symfony\Component\Uid\Ulid('01M4CY7G5YS9X7CDDM4VQ13QWV'));
        try {
            $invoice = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        } finally {
            \Illuminate\Support\Str::createUlidsNormally();
        }
        $lifecycle = app(CorporateInvoiceLifecycle::class);
        $lifecycle->send($invoice, $this->actor());
        $contract['invoices'] = $this->callAs(1, 'invoices')->assertOk()->json();
        $contract['invoice_detail'] = $this->callAs(1, 'invoices/'.$invoice->id)->assertOk()->json();
        $contract['pdf_valid'] = str_starts_with($this->callAs(1, 'invoices/'.$invoice->id.'/download')->assertOk()->getContent(), '%PDF-');
        foreach ([1, 2] as $attempt) {
            $lifecycle->settle($invoice, $this->actor(), '1470.00', 'KES', '2026-10-07', 'test-contract-settlement');
        }
        $this->assertSame(1, DB::table('company_corporate_settlements')->count());
        $this->assertSame('paid', $trip->fresh()->billing_status);
        $contract['driver_settled'] = $this->legacyAs(2, 'get_driver_checkout_summary', ['trip_id' => $trip->id, 'user_type' => 'Driver'])->assertOk()
            ->assertJsonPath('driver_net_earnings', '1170.00')->assertJsonPath('withdrawable_payout', '1170.00')
            ->assertJsonPath('payment_status', 'Completed')->json();
        $this->assertTrue($contract['pdf_valid']);
        foreach (['driver_detail', 'driver_checkout', 'history', 'driver_settled'] as $key) {
            $this->assertStringNotContainsString('finance@example.test', json_encode($contract[$key]));
            $this->assertStringNotContainsString('corporate_financial_snapshot', json_encode($contract[$key]));
        }
        // Optional generated artifact for Dart consumers; no JWT/secrets included.
        if (getenv('CORPORATE_EXPORT_MOBILE_CONTRACT') === '1') {
            \Illuminate\Support\Facades\File::ensureDirectoryExists(base_path('tests/fixtures'));
            \Illuminate\Support\Facades\File::put(base_path('tests/fixtures/corporate-mobile-contract.json'), json_encode($contract, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        }
    }
}
