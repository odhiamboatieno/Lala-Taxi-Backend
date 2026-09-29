<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class MarketingViewsTest extends TestCase
{
    public function test_public_marketing_routes_render_without_querying_car_types(): void
    {
        // UI routes are checked independently of installation and DB-backed locale setup.
        $this->withoutMiddleware();
        foreach (['/', '/ride', '/drive', '/safety', '/business', '/business/fleets', '/requirements', '/driver_app', '/drive_safety', '/signin', '/signup'] as $route) {
            $this->get($route)->assertOk()->assertSee('id="main"', false)->assertSee('css/marketing.css', false);
        }
    }

    public function test_unconfigured_downloads_offer_an_account_instead_of_dead_store_links(): void
    {
        View::share('app_links', collect());
        $html = view('home.home')->render();
        $this->assertStringContainsString(__('website.download_unavailable'), $html);
        $this->assertStringContainsString(url('signup_rider'), $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_configured_store_links_are_used_and_unsafe_schemes_are_excluded(): void
    {
        View::share('app_links', collect([
            (object) ['value' => 'https://apps.apple.com/app/lala-test'],
            (object) ['value' => ''],
            (object) ['value' => 'javascript:alert(1)'],
        ]));
        $html = view('home.home')->render();
        $this->assertStringContainsString('https://apps.apple.com/app/lala-test', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString(__('website.download_unavailable'), $html);
    }

    public function test_copy_is_translated_and_falls_back_to_english(): void
    {
        app()->setLocale('fr');
        app('translator')->addLines(['website.home_heading' => 'Votre prochain trajet.'], 'fr');
        $html = view('home.home')->render();
        $this->assertStringContainsString('Votre prochain trajet.', $html);
        $this->assertStringContainsString('Connecting possibilities.', $html);
        $this->assertStringNotContainsString('website.home_heading', $html);
    }

    public function test_company_login_preserves_the_existing_authentication_contract(): void
    {
        View::share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = view('user.signin_company')->render();
        $this->assertStringContainsString('action="'.url('admin/authenticate').'"', $html);
        $this->assertStringContainsString('name="user_type" value="Company"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('autocomplete="current-password"', $html);
    }
}
