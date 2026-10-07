<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardSearchController;
use App\Models\Admin;
use App\Services\DashboardSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable();
            foreach (['first_name', 'last_name', 'email', 'mobile_number', 'user_type', 'password', 'status'] as $field) {
                $table->string($field)->default('');
            }
            $table->timestamp('deleted_at')->nullable();
        });
        DB::table('users')->insert([
            ['id' => 11, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.test', 'mobile_number' => '0700123456', 'user_type' => 'Driver', 'password' => 'SECRET-ALICE'],
            ['id' => 12, 'company_id' => 3, 'first_name' => 'Alice', 'last_name' => 'Jones', 'email' => 'other@example.test', 'mobile_number' => '0700555555', 'user_type' => 'Driver', 'password' => 'SECRET-OTHER'],
            ['id' => 13, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Rider', 'email' => 'rider@example.test', 'mobile_number' => '0700666666', 'user_type' => 'Rider', 'password' => 'SECRET-RIDER'],
        ]);
    }

    private function search(string $term, string $scope = 'company', ?int $companyId = 2, array $permissions = ['view_driver', 'view_rider'], bool $features = true): array
    {
        return app(DashboardSearch::class)->search($term, $scope, $companyId, fn ($permission) => in_array($permission, $permissions, true), fn () => $features);
    }

    public function test_company_results_never_include_another_company_or_deleted_people(): void
    {
        DB::table('users')->where('id', 13)->update(['deleted_at' => now()]);
        $results = $this->search('Alice')['results'];
        $this->assertCount(1, $results);
        $this->assertSame('Alice Smith', $results[0]['title']);
        $this->assertSame(url('company/driver').'?search=alice%40example.test', $results[0]['url']);
        $this->assertStringNotContainsString('SECRET', json_encode($results));
        $this->assertSame([], $this->search('SECRET-ALICE')['results']);
    }

    public function test_permissions_control_categories_and_edit_destinations(): void
    {
        $this->assertSame([], $this->search('Alice', permissions: [])['results']);
        $viewOnly = $this->search('Alice', permissions: ['view_driver'])['results'];
        $this->assertCount(1, $viewOnly);
        $this->assertSame('Drivers', $viewOnly[0]['group']);
        $editable = $this->search('Alice', permissions: ['view_driver', 'update_driver'])['results'];
        $this->assertSame(url('company/edit_driver/11'), $editable[0]['url']);
    }

    public function test_names_phones_ids_category_prefixes_and_literal_wildcards(): void
    {
        foreach (['alice smith', 'drivers Alice', '0700123456', '#11'] as $term) {
            $this->assertSame('Alice Smith', $this->search($term)['results'][0]['title']);
        }
        $this->assertSame([], $this->search('%_')['results']);
        $this->assertSame([], $this->search("' OR 1=1 --")['results']);
        DB::table('users')->where('id', 11)->update(['first_name' => '100%_Alice']);
        $this->assertCount(1, $this->search('%_')['results']);
    }

    public function test_admin_can_search_across_companies_and_results_are_bounded(): void
    {
        $this->assertCount(3, $this->search('Alice', 'admin', null)['results']);
        for ($index = 0; $index < 8; $index++) {
            DB::table('users')->insert(['company_id' => 2, 'first_name' => 'Alice', 'user_type' => 'Driver']);
        }
        $result = $this->search('Alice', permissions: ['view_driver']);
        $this->assertCount(5, $result['results']);
        $this->assertTrue($result['hasMore']);
    }

    public function test_company_trip_scope_follows_the_owning_driver(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('driver_id');
            $table->string('pickup_location');
            $table->string('drop_location');
        });
        DB::table('trips')->insert([
            ['driver_id' => 11, 'pickup_location' => 'Airport', 'drop_location' => 'Town'],
            ['driver_id' => 12, 'pickup_location' => 'Airport', 'drop_location' => 'Other company'],
        ]);
        $result = $this->search('Airport', permissions: ['manage_trips'])['results'];
        $this->assertCount(1, $result);
        $this->assertSame(url('company/view_trips/1'), $result[0]['url']);
    }

    public function test_optional_modules_are_gated_and_missing_tenant_columns_fail_closed(): void
    {
        Schema::create('company_corporate_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('invoice_number');
        });
        DB::table('company_corporate_invoices')->insert(['company_id' => 2, 'invoice_number' => 'INV-2042']);
        $this->assertCount(1, $this->search('INV-2042', permissions: ['manage_company_accounts_invoicing'])['results']);
        $this->assertSame([], $this->search('INV-2042', permissions: ['manage_company_accounts_invoicing'], features: false)['results']);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('company_id'));
        $this->assertSame([], $this->search('Alice')['results']);
    }

    public function test_endpoint_requires_authentication_and_validates_query(): void
    {
        try {
            app(DashboardSearchController::class)(Request::create('/admin/search', 'GET', ['q' => 'Alice']), app(DashboardSearch::class));
            $this->fail('Guest search must fail.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }
        $admin = new class extends Admin {
            public function can($ability, $arguments = []) { return $ability === 'view_driver'; }
        };
        $this->actingAs($admin, 'admin');
        $response = app(DashboardSearchController::class)(Request::create('/admin/search', 'GET', ['q' => 'Alice']), app(DashboardSearch::class));
        $this->assertCount(2, $response->getData(true)['results']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(DashboardSearchController::class)(Request::create('/admin/search', 'GET', ['q' => '  ']), app(DashboardSearch::class));
    }
}
