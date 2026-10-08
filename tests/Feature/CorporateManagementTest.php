<?php

namespace Tests\Feature;

use App\Http\Helper\InvoiceHelper;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Services\Corporate\CorporateAccountService;
use App\Services\Corporate\CorporateExposure;
use App\Services\Corporate\CorporateInvoiceGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Modules\SaaSDispatch\Http\Controllers\CompanyAccountsController;
use Modules\SaaSDispatch\Services\CompanyContextResolver;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporateManagementTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateFinanceSchema();
        (require database_path('migrations/2026_10_07_000005_add_corporate_invoice_settlement_and_delivery.php'))->up();
        Schema::table('users', function (Blueprint $table) { $table->string('email')->default(''); $table->string('mobile_number')->default(''); });
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
        $actor->status = 'Active';
        $actor->shouldReceive('hasPermission')->with(['manage_company_accounts_invoicing'])->andReturn($permitted);
        return $actor;
    }

    private function input(array $overrides = []): array
    {
        return array_replace(['account_name' => 'New employer', 'legal_name' => 'Employer Limited', 'billing_address' => 'Kenya',
            'contact_email' => 'finance@example.test', 'currency_code' => 'KES', 'billing_timezone' => 'Africa/Nairobi',
            'billing_cycle' => 'monthly', 'payment_terms_days' => 30, 'credit_limit' => '1000.00', 'status' => 'Active'], $overrides);
    }

    private function charge(string $completion = '2026-09-29 10:15:00'): Trips
    {
        $offer = RideRequest::create(['user_id' => 1, 'driver_id' => 2, 'car_id' => 1, 'schedule_id' => '']);
        $end = \Carbon\Carbon::parse($completion, 'UTC');
        $trip = DB::transaction(fn () => Trips::create(['user_id' => 1, 'driver_id' => 2, 'request_id' => $offer->id,
            'status' => 'Payment', 'completed_at' => $completion, 'end_trip' => $completion,
            'arrive_time' => $end->copy()->subMinutes(11), 'begin_trip' => $end->copy()->subMinutes(10), 'tips' => '100.00', 'toll_fee' => '50.00']));
        return app(InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
    }

    private function prefix(int $company = 1): string
    {
        return '/admin/companies/'.$company.'/corporate-accounts';
    }

    public function test_real_routes_create_update_archive_and_audit_without_deleting_history(): void
    {
        $this->actingAs($this->actor(), 'admin');
        $this->post($this->prefix().'/accounts', $this->input())->assertRedirect();
        $account = CompanyCorporateAccount::latest('id')->first();
        $this->assertSame(1, $account->company_id);
        $this->assertSame('1000.00', $account->credit_limit);
        $this->put($this->prefix().'/accounts/'.$account->id, $this->input(['account_name' => 'Updated employer']))->assertRedirect($this->prefix().'/accounts/'.$account->id);
        $this->post($this->prefix().'/accounts/'.$account->id.'/archive')->assertRedirect();
        $this->post($this->prefix().'/accounts/'.$account->id.'/archive')->assertRedirect();
        $this->assertSame('Inactive', $account->fresh()->status);
        $this->assertNotNull($account->fresh()->archived_at);
        $this->assertSame(['account.created', 'account.updated', 'account.archived'], $account->audits()->orderBy('id')->pluck('action')->all());
        $this->putJson($this->prefix().'/accounts/'.$account->id, $this->input())->assertUnprocessable();
    }

    public function test_missing_permission_and_forged_owner_ids_cannot_mutate_accounts_or_members(): void
    {
        $this->actingAs($this->actor(false), 'admin');
        $this->postJson($this->prefix().'/accounts', $this->input())->assertForbidden();
        $this->actingAs($this->actor(), 'admin');
        $this->putJson($this->prefix(2).'/accounts/'.$this->account->id, $this->input())->assertNotFound();
        $this->postJson($this->prefix(2).'/accounts/'.$this->account->id.'/members', ['user_id' => 1, 'role' => 'finance'])->assertNotFound();
        $this->deleteJson($this->prefix(2).'/accounts/'.$this->account->id.'/members/1')->assertNotFound();
        $this->assertSame('member', DB::table('users')->where('id', 1)->value('corporate_role'));
        $this->assertSame(0, $this->account->audits()->count());
    }

    public function test_company_route_uses_authenticated_owner_and_cannot_assign_foreign_rider(): void
    {
        $this->actingAs(Company::findOrFail(1), 'company');
        $url = '/company/saas-dispatch/accounts';
        $this->post($url.'/accounts', $this->input(['company_id' => 2]))->assertRedirect();
        $created = CompanyCorporateAccount::latest('id')->first();
        $this->assertSame(1, $created->company_id);
        DB::table('users')->insert(['id' => 3, 'company_id' => 2, 'user_type' => 'Rider']);
        $this->postJson($url.'/accounts/'.$created->id.'/members', ['user_id' => 3, 'role' => 'member'])->assertForbidden();
        $this->postJson($url.'/accounts/'.$created->id.'/members', ['user_id' => 2, 'role' => 'member'])->assertUnprocessable();
        $this->post($url.'/accounts/'.$this->account->id.'/members', ['user_id' => 1, 'role' => 'finance'])->assertRedirect();
        $this->assertSame('finance', DB::table('users')->where('id', 1)->value('corporate_role'));
    }

    public function test_account_validation_rejects_incomplete_billing_and_invalid_money(): void
    {
        $this->actingAs($this->actor(), 'admin');
        $this->postJson($this->prefix().'/accounts', $this->input(['legal_name' => '', 'currency_code' => 'INVALID', 'credit_limit' => '-1',
            'billing_timezone' => 'invalid', 'payment_terms_days' => 366]))->assertUnprocessable()->assertJsonValidationErrors(['legal_name', 'currency_code', 'credit_limit', 'billing_timezone', 'payment_terms_days']);
        $this->assertSame(1, CompanyCorporateAccount::count());
    }

    public function test_billing_agreement_is_fixed_after_acceptance_but_contacts_can_be_updated(): void
    {
        $trip = $this->charge();
        $service = app(CorporateAccountService::class);
        $before = $trip->billing_snapshot;
        $this->assertSame('Employer', $before['account_name']);
        $service->update($this->account, $this->input(['account_name' => 'Employer', 'legal_name' => 'Renamed legal entity']), $this->actor());
        $this->assertSame($before, $trip->fresh()->billing_snapshot);
        try {
            $service->update($this->account, $this->input(['billing_cycle' => 'weekly']), $this->actor());
            $this->fail('Booked billing agreement must not change.');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('billing_cycle', $exception->errors()); }
        $this->assertSame('monthly', $this->account->fresh()->billing_cycle);
    }

    public function test_disabled_feature_preserves_servicing_but_blocks_new_accounts(): void
    {
        $this->actingAs($this->actor(), 'admin');
        DB::table('saas_feature_modules')->update(['is_enabled' => false]);
        config(['corporate.bookings_enabled' => false]);
        $this->put($this->prefix().'/accounts/'.$this->account->id, $this->input())->assertRedirect();
        $this->postJson($this->prefix().'/accounts', $this->input())->assertNotFound();
        $this->assertSame(1, CompanyCorporateAccount::count());
    }

    public function test_inactive_currency_can_be_retained_for_servicing_but_not_new_agreements(): void
    {
        $this->charge();
        DB::table('currency')->where('code', 'KES')->update(['status' => 'Inactive']);
        $this->actingAs($this->actor(), 'admin');
        $this->put($this->prefix().'/accounts/'.$this->account->id, $this->input(['contact_email' => 'updated@example.test']))->assertRedirect();
        $this->assertSame('KES', $this->account->fresh()->currency_code);
        $this->assertSame('updated@example.test', $this->account->fresh()->contact_email);
        $this->postJson($this->prefix().'/accounts', $this->input())->assertUnprocessable()->assertJsonValidationErrors('currency_code');
    }

    public function test_web_invoice_generation_issue_download_and_settlement_use_real_saved_charges(): void
    {
        $trip = $this->charge();
        $this->actingAs($this->actor(), 'admin');
        $this->post($this->prefix().'/invoices', ['corporate_account_id' => $this->account->id, 'period_start' => '2026-09-01', 'subtotal' => '0.01'])->assertRedirect();
        $invoice = CompanyCorporateInvoice::first();
        $this->assertSame('1470.00', $invoice->total_amount);
        $path = $this->prefix().'/invoices/'.$invoice->id;
        $this->post($path.'/send')->assertRedirect($path);
        $this->get($path.'/download')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->postJson($path.'/settle', ['amount' => '0.01', 'currency_code' => 'KES', 'settlement_date' => '2026-10-07', 'reference' => 'WRONG'])->assertUnprocessable();
        $this->assertSame('Deferred', $trip->fresh()->payment_status);
        $this->post($path.'/settle', ['amount' => '1470.00', 'currency_code' => 'KES', 'settlement_date' => '2026-10-07', 'reference' => 'BANK-WEB'])->assertRedirect($path);
        $this->assertSame('Paid', $invoice->fresh()->status);
        $this->assertSame('Completed', $trip->fresh()->payment_status);
        $this->postJson($path.'/void', ['reason' => 'Cannot void paid'])->assertUnprocessable();
        $this->getJson($this->prefix(2).'/invoices/'.$invoice->id.'/download')->assertNotFound();
    }

    public function test_exposure_counts_all_periods_once_and_zero_limit_is_unconfigured(): void
    {
        $this->charge();
        $this->charge('2026-08-25 10:15:00');
        $exposure = app(CorporateExposure::class);
        $this->assertSame('2940.00', $exposure->summary($this->account)['outstanding_amount']);
        $this->assertNull($exposure->summary($this->account)['remaining_credit']);
        app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        $this->assertSame('2940.00', $exposure->summary($this->account)['outstanding_amount']);
        $this->account->update(['credit_limit' => '1000.00']);
        $this->assertTrue($exposure->summary($this->account)['over_credit_limit']);
        $this->assertSame('0.00', $exposure->summary($this->account)['remaining_credit']);
        $this->account->update(['credit_limit' => '3000.00']);
        $this->assertSame('60.00', $exposure->summary($this->account)['remaining_credit']);
        DB::table('trips')->update(['billing_status' => 'paid']);
        $this->assertSame('0.00', $exposure->summary($this->account)['outstanding_amount']);
    }

    public function test_account_pages_render_actual_data_and_paginate_members_trips_and_invoices(): void
    {
        $trip = $this->charge();
        $invoice = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        $this->actingAs($this->actor(), 'admin');
        $request = Request::create($this->prefix().'/accounts/'.$this->account->id);
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);
        $data = app(CompanyAccountsController::class)->showAccount($request, app(CompanyContextResolver::class), Company::findOrFail(1))->getData();
        foreach (['members', 'trips', 'invoices', 'eligibleRiders'] as $key) { $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $data[$key]); }
        $data['errors'] = new ViewErrorBag;
        // Render the real page body independently of unrelated legacy admin-shell data dependencies.
        $body = preg_replace('/^@extends.*\n|^@section\([^\n]+\)\n|^@endsection\n?/m', '', file_get_contents(base_path('Modules/SaaSDispatch/Resources/views/accounts/show.blade.php')));
        $html = Blade::render($body, $data);
        $this->assertStringContainsString('1470.00', $html);
        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringContainsString($this->prefix().'/accounts/'.$this->account->id.'/trips/'.$trip->id, $html);
        $this->assertStringNotContainsString('corporate.', strip_tags($html));
    }

    public function test_management_forms_and_invoice_lifecycle_states_render_translated_saved_data(): void
    {
        $trip = $this->charge();
        $invoice = app(CorporateInvoiceGenerator::class)->generate($this->account, '2026-09-01');
        $context = ['company' => Company::findOrFail(1), 'accountsRoute' => 'saasdispatch.companies.accounts.', 'accountsRouteParameters' => ['company' => 1],
            'errors' => new ViewErrorBag, 'account' => $this->account, 'trip' => $trip, 'invoice' => $invoice, 'delivery' => null, 'settlement' => null];
        foreach (['form', 'trip', 'invoice'] as $page) {
            $extra = $page === 'form' ? ['currencies' => \App\Models\Currency::all()] : [];
            $html = $this->renderBody($page, $context + $extra);
            $this->assertStringNotContainsString('corporate.', strip_tags($html));
            $this->assertStringContainsString($page === 'form' ? 'billing_address' : '1470.00', $html);
        }
        $html = $this->renderBody('invoice', $context);
        $this->assertStringContainsString('/send', $html);
        $this->assertStringNotContainsString('/settle', $html);
        app(\App\Services\Corporate\CorporateInvoiceLifecycle::class)->send($invoice, $this->actor());
        $context['invoice'] = $invoice->fresh()->load('items');
        $context['delivery'] = DB::table('company_corporate_invoice_deliveries')->first();
        $html = $this->renderBody('invoice', $context);
        $this->assertStringContainsString('finance@example.test', $html);
        $this->assertStringContainsString('/settle', $html);
        $this->assertStringNotContainsString('/send', $html);
        app(\App\Services\Corporate\CorporateInvoiceLifecycle::class)->settle($invoice, $this->actor(), '1470.00', 'KES', '2026-10-07', 'BANK-UI');
        $context['invoice'] = $invoice->fresh()->load('items');
        $context['settlement'] = DB::table('company_corporate_settlements')->first();
        $html = $this->renderBody('invoice', $context);
        $this->assertStringContainsString('BANK-UI', $html);
        $this->assertStringNotContainsString('/void', $html);
        DB::table('company_corporate_invoices')->where('id', $invoice->id)->update(['trip_backed' => false]);
        $context['invoice'] = $invoice->fresh();
        $html = $this->renderBody('invoice', $context);
        $this->assertStringContainsString(__('corporate.legacy_invoice_hint'), $html);
        $this->assertStringNotContainsString('/download', $html);
    }

    public function test_company_operation_team_permission_is_independent_of_rider_corporate_role(): void
    {
        (require database_path('migrations/2026_08_16_000075_create_operation_team_roles_table.php'))->up();
        (require database_path('migrations/2026_08_16_000074_create_operation_team_members_table.php'))->up();
        $role = \Modules\OperationTeam\Entities\OperationTeamRole::create(['company_id' => 1, 'name' => 'Restricted', 'permissions' => [], 'status' => true]);
        $member = \Modules\OperationTeam\Entities\OperationTeamMember::create(['company_id' => 1, 'role_id' => $role->id, 'first_name' => 'Operator', 'password' => 'test-password', 'status' => true]);
        $this->actingAs(Company::findOrFail(1), 'company');
        $url = '/company/saas-dispatch/accounts/accounts/'.$this->account->id;
        $this->withSession(['operation_team_member_id' => $member->id])->putJson($url, $this->input())->assertForbidden();
        $role->update(['permissions' => ['manage_company_accounts_invoicing']]);
        $this->put($url, $this->input())->assertRedirect();
        $role->update(['company_id' => 2]);
        $this->putJson($url, $this->input())->assertForbidden();
        $this->withSession(['operation_team_member_id' => 999999])->putJson($url, $this->input())->assertForbidden();
    }

    public function test_archiving_preserves_accepted_charges_and_void_allows_reinvoicing_through_web(): void
    {
        $trip = $this->charge();
        $this->actingAs($this->actor(), 'admin');
        $this->post($this->prefix().'/accounts/'.$this->account->id.'/archive')->assertRedirect();
        $this->post($this->prefix().'/invoices', ['corporate_account_id' => $this->account->id, 'period_start' => '2026-09-01'])->assertRedirect();
        $invoice = CompanyCorporateInvoice::first();
        $this->post($this->prefix().'/invoices/'.$invoice->id.'/void', ['reason' => 'Correct batch'])->assertRedirect();
        $this->assertSame('unbilled', $trip->fresh()->billing_status);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertSame($this->account->id, DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->post($this->prefix().'/invoices', ['corporate_account_id' => $this->account->id, 'period_start' => '2026-09-01'])->assertRedirect();
        $this->assertSame(2, CompanyCorporateInvoice::count());
        $this->assertSame(2, DB::table('company_corporate_invoice_items')->count());
    }

    private function renderBody(string $page, array $data): string
    {
        $body = preg_replace('/^@extends.*\n|^@section\([^\n]+\)\n|^@endsection\n?/m', '', file_get_contents(base_path('Modules/SaaSDispatch/Resources/views/accounts/'.$page.'.blade.php')));
        return Blade::render($body, $data);
    }
}
