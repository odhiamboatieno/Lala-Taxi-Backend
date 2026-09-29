<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAppearanceTest extends TestCase
{
    public function test_appearance_assets_share_brand_tokens_and_are_cache_versioned(): void
    {
        $html = view('admin.partials.appearance')->render();
        foreach (['marketing-tokens.css', 'admin-tokens.css', 'admin-refinement.css'] as $file) {
            $this->assertStringContainsString($file.'?v='.filemtime(public_path('css/'.$file)), $html);
        }
        $this->assertLessThan(strpos($html, 'admin-refinement.css'), strpos($html, 'admin-tokens.css'));
    }

    public function test_login_keeps_authentication_form_and_role_switch_contract(): void
    {
        $html = view('admin.login')->render();
        foreach (['action="'.url('admin/authenticate').'"', 'name="_token"', 'name="username"', 'name="password"', 'name="user_type"', 'user="Admin"', 'user="Dispatcher"', 'class="admin-theme"', 'admin-refinement.css'] as $contract) {
            $this->assertStringContainsString($contract, $html);
        }
        $this->assertStringNotContainsString('user-scalable=no', $html);
    }

    public function test_existing_square_and_avatar_rules_are_not_overridden(): void
    {
        $css = file_get_contents(public_path('css/admin-refinement.css'));
        $this->assertDoesNotMatchRegularExpression('/border(?:-[\w]+)?-radius\s*:/', $css);
        $tokens = file_get_contents(public_path('css/admin-tokens.css'));
        $this->assertStringContainsString('--admin-accent: var(--site-gold)', $tokens);
        $this->assertStringContainsString('[data-bs-theme="dark"]', $tokens);
    }

    public function test_admin_layer_does_not_leak_into_public_website(): void
    {
        $html = view('home.home')->render();
        $this->assertStringNotContainsString('admin-refinement.css', $html);
        $this->assertStringNotContainsString('admin-theme', $html);
    }
}
