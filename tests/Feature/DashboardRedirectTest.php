<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Company;
use App\Models\CorporatePortalUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    public function test_guest_legacy_dashboard_redirects_to_sign_in_instead_of_a_cms_404(): void
    {
        $this->get('/dashboard')->assertRedirect('/signin');
    }

    public function test_real_password_login_can_follow_a_previously_stored_admin_root_destination(): void
    {
        (require database_path('migrations/2026_08_16_000001_create_admins_table.php'))->up();
        $admin = (new Admin)->forceFill(['username' => 'dashboard_qa', 'email' => 'dashboard@example.test',
            'password' => 'IsolatedDashboard123!', 'country_code' => '254', 'mobile_number' => '700000151', 'status' => 'Active']);
        $admin->save();
        $this->withSession(['url.intended' => url('/admin')])->post('/admin/authenticate',
            ['username' => 'dashboard_qa', 'password' => 'IsolatedDashboard123!', 'user_type' => 'Admin'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get('/admin')->assertRedirect('/admin/dashboard');
    }

    public function test_admin_legacy_dashboard_and_admin_root_resolve_to_the_real_dashboard(): void
    {
        $admin = (new Admin)->forceFill(['id' => 1, 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        $this->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->get('/admin')->assertRedirect('/admin/dashboard');
        $root = Route::getRoutes()->match(Request::create('/admin'));
        $this->assertSame('/admin/dashboard', $root->defaults['destination']);
    }

    public function test_company_legacy_dashboard_has_a_cacheable_guarded_destination(): void
    {
        $company = (new Company)->forceFill(['id' => 2, 'status' => 'Active']);
        $this->actingAs($company, 'company');
        $this->get('/dashboard')->assertRedirect('/company/dashboard');
        $route = Route::getRoutes()->match(Request::create('/company/dashboard'));
        $this->assertSame('App\\Http\\Controllers\\Admin\\AdminController@index', $route->getActionName());
        $this->assertContains('admin_guest', $route->gatherMiddleware());
        $this->assertContains('company_approval', $route->gatherMiddleware());
    }

    public function test_corporate_legacy_dashboard_keeps_the_employer_portal_guard(): void
    {
        $user = (new CorporatePortalUser)->forceFill(['id' => 3, 'corporate_account_id' => 1]);
        $this->actingAs($user, 'corporate');
        $this->get('/dashboard')->assertRedirect('/corporate');
    }

    public function test_personal_rider_and_driver_keep_their_own_dashboard_destinations(): void
    {
        foreach (['Rider' => '/trip', 'Driver' => '/driver_profile'] as $type => $destination) {
            $user = (new User)->forceFill(['id' => 4, 'user_type' => $type, 'status' => 'Active']);
            $this->actingAs($user, 'web');
            $this->get('/dashboard')->assertRedirect($destination);
        }
    }

    public function test_both_operations_dashboards_exist_without_an_http_bootstrap_context(): void
    {
        $compiled = Route::getRoutes()->compile();
        $this->assertIsArray($compiled);
        $this->assertSame('admin/dashboard', Route::getRoutes()->match(Request::create('/admin/dashboard'))->uri());
        $this->assertSame('company/dashboard', Route::getRoutes()->match(Request::create('/company/dashboard'))->uri());
    }

    public function test_modules_management_routes_survive_console_registration_with_admin_authentication(): void
    {
        foreach (['GET' => ['/admin/modules', '/admin/modules/upload'],
            'POST' => ['/admin/modules', '/admin/modules/example/enable', '/admin/modules/example/disable',
                '/admin/modules/example/migrate', '/admin/modules/rediscover'],
            'DELETE' => ['/admin/modules/example']] as $method => $paths) {
            foreach ($paths as $path) {
                $route = Route::getRoutes()->match(Request::create($path, $method));
                $this->assertStringContainsString('ModuleController@', $route->getActionName());
                $this->assertContains('admin_guest', $route->gatherMiddleware());
            }
        }
        $this->get('/admin/modules')->assertRedirect('/admin/login');
        $this->assertIsArray(Route::getRoutes()->compile());
    }
}
