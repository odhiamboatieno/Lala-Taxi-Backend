<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Company;
use App\Models\SiteSettings;
use App\Services\Corporate\CorporateCapabilities;
use App\Services\Dispatch\ManualBookingPayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Http\Controllers\CompanyAccountsController;
use Modules\SaaSDispatch\Services\CompanyContextResolver;
use Tests\TestCase;

class CorporatePrerequisitesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['corporate.bookings_enabled' => true]);
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->default('Active');
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('value');
        });
        Schema::create('saas_feature_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->boolean('is_installed');
            $table->boolean('is_enabled');
        });
        Schema::create('company_saas_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->boolean('is_saas_enabled');
        });
        (require database_path('migrations/2026_08_16_000011_create_company_corporate_accounts_table.php'))->up();
        (require database_path('migrations/2026_08_16_000012_create_company_corporate_invoices_table.php'))->up();
        DB::table('companies')->insert([['id' => 1, 'name' => 'Platform'], ['id' => 2, 'name' => 'Operator'], ['id' => 3, 'name' => 'Other operator']]);
        DB::table('site_settings')->insert(['name' => 'saas_dispatch_module_enabled', 'value' => '1']);
        DB::table('saas_feature_modules')->insert(['slug' => 'accounts_invoicing', 'is_installed' => true, 'is_enabled' => true]);
        DB::table('company_saas_settings')->insert(['company_id' => 2, 'is_saas_enabled' => true]);
        $this->app->bind('site_settings', fn () => SiteSettings::query()->get());
    }

    private function requestAt(string $path, array $input = []): Request
    {
        $request = Request::create($path, 'POST', $input);
        $this->app->instance('request', $request);

        return $request;
    }

    public function test_both_management_routes_are_registered_without_request_context(): void
    {
        $this->assertIsArray(Route::getRoutes()->compile());
        $this->assertSame('company/saas-dispatch/accounts', Route::getRoutes()->getByName('saasdispatch.self.accounts.index')->uri());
        $this->assertSame('admin/companies/{company}/corporate-accounts', Route::getRoutes()->getByName('saasdispatch.companies.accounts.index')->uri());
        $this->assertSame('admin/saas-dispatch/accounts', Route::getRoutes()->getByName('saasdispatch.admin.accounts.index')->uri());
    }

    public function test_capabilities_include_platform_but_require_enabled_tenant_and_feature(): void
    {
        $service = app(CorporateCapabilities::class);
        $this->assertTrue($service->newBookingsEnabled(Company::findOrFail(1)));
        $this->assertTrue($service->newBookingsEnabled(Company::findOrFail(2)));
        $this->assertFalse($service->newBookingsEnabled(Company::findOrFail(3)));
        DB::table('saas_feature_modules')->update(['is_enabled' => false]);
        $this->assertFalse($service->newBookingsEnabled(Company::findOrFail(1)));
    }

    public function test_admin_uses_explicit_company_and_scoped_form_routes(): void
    {
        $this->actingAs(new Admin(['id' => 10]), 'admin');
        $this->requestAt('/admin/companies/2/corporate-accounts');
        $data = app(CompanyAccountsController::class)->index(app(CompanyContextResolver::class), Company::findOrFail(2))->getData();
        $this->assertSame(2, $data['company']->id);
        $this->assertSame('saasdispatch.companies.accounts.', $data['accountsRoute']);
        $this->assertSame(['company' => 2], $data['accountsRouteParameters']);
    }

    public function test_company_context_rejects_another_company_even_when_both_guards_authenticated(): void
    {
        $this->actingAs(new Admin(['id' => 10]), 'admin');
        $this->actingAs(Company::findOrFail(2), 'company');
        $this->requestAt('/company/saas-dispatch/accounts');
        $this->assertSame(2, app(CompanyContextResolver::class)->actingCompany()->id);
        try {
            app(CompanyContextResolver::class)->actingCompany(Company::findOrFail(3));
            $this->fail('Cross-company context must fail.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    public function test_invoice_input_cannot_reference_another_company_account(): void
    {
        $this->actingAs(new Admin(['id' => 10]), 'admin');
        $id = DB::table('company_corporate_accounts')->insertGetId(['company_id' => 3, 'account_name' => 'Other employer']);
        $request = $this->requestAt('/admin/companies/2/corporate-accounts/invoices', ['corporate_account_id' => $id, 'invoice_date' => '2026-10-07', 'subtotal' => '100.00']);
        try {
            app(CompanyAccountsController::class)->storeInvoice($request, app(CompanyContextResolver::class), Company::findOrFail(2));
            $this->fail('Cross-company invoice must fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('corporate_account_id', $exception->errors());
            $this->assertSame(0, DB::table('company_corporate_invoices')->count());
        }
    }

    public function test_unsupported_manual_methods_fail_instead_of_silently_becoming_cash(): void
    {
        $service = app(ManualBookingPayment::class);
        $this->assertSame(['payment_method' => 'Cash', 'is_wallet' => 'No'], $service->resolve(null));
        foreach (['Card', 'Wallet', 'Invoice', 'invalid'] as $method) {
            try {
                $service->resolve($method);
                $this->fail('Unsupported method must fail.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('payment_method', $exception->errors());
            }
        }
    }
}
