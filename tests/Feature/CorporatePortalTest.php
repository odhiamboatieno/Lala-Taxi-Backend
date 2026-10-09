<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CorporatePortalUser;
use App\Services\Corporate\CorporatePortalUserService;
use App\Notifications\CorporateResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Tests\Concerns\CreatesCorporateFinanceSchema;
use Tests\TestCase;

class CorporatePortalTest extends TestCase
{
    use CreatesCorporateFinanceSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCorporateFinanceSchema();
        (require database_path('migrations/2026_10_08_000002_create_corporate_portal_identities.php'))->up();
        (require database_path('migrations/2026_10_07_000005_add_corporate_invoice_settlement_and_delivery.php'))->up();
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code')->default('254');
            $table->string('mobile_number')->default('700000110');
            $table->string('email')->default('employee@example.test');
        });
        Notification::fake();
    }

    protected function tearDown(): void
    {
        $this->dropCorporateTestSchema();
        parent::tearDown();
    }

    private function staff(string $role = 'admin', ?CompanyCorporateAccount $account = null): CorporatePortalUser
    {
        return CorporatePortalUser::create(['corporate_account_id' => ($account ?? $this->account)->id,
            'name' => 'Amina Wanjiku', 'email' => 'amina'.CorporatePortalUser::count().'@example.test',
            'password' => 'CorporateTest123!', 'password_set_at' => now(), 'role' => $role, 'status' => 'Active']);
    }

    private function signIn(CorporatePortalUser $user): void
    {
        $this->actingAs($user, 'corporate')->withSession(['corporate.session_version' => $user->session_version]);
    }

    private function foreignAccount(): CompanyCorporateAccount
    {
        return CompanyCorporateAccount::create(['company_id' => 2, 'account_name' => 'Foreign Employer',
            'legal_name' => 'Foreign Ltd', 'billing_address' => 'Kenya', 'contact_email' => 'foreign@example.test',
            'currency_code' => 'KES', 'billing_timezone' => 'Africa/Nairobi', 'status' => 'Active']);
    }

    private function admin(bool $permitted = true): Admin
    {
        $admin = \Mockery::mock(Admin::class)->makePartial();
        $admin->id = 1;
        $admin->status = 'Active';
        $admin->shouldReceive('hasPermission')->with(['manage_company_accounts_invoicing'])->andReturn($permitted);
        return $admin;
    }

    private function staffInput(array $overrides = []): array
    {
        return array_replace(['name' => 'Njeri Mwangi', 'email' => 'njeri@example.test', 'role' => 'admin', 'status' => 'Active'], $overrides);
    }

    public function test_guest_forms_and_real_password_login_use_a_separate_guard(): void
    {
        $this->get('/corporate')->assertRedirect('/corporate/login');
        $this->get('/corporate/login')->assertOk()->assertSee('Work email')->assertSee('name="_token"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->get('/corporate/forgot-password')->assertOk();
        $user = $this->staff();
        $this->post('/corporate/login', ['email' => strtoupper($user->email), 'password' => 'CorporateTest123!'])
            ->assertRedirect('/corporate')->assertSessionHas('corporate.session_version', 1);
        $this->assertAuthenticatedAs($user, 'corporate');
        $this->assertGuest('admin');
        $this->assertGuest('company');
        $this->get('/corporate')->assertOk()->assertSee('Employer')->assertDontSee('corporate.$1');
        $this->post('/corporate/logout')->assertRedirect('/corporate/login');
        $this->assertGuest('corporate');
    }

    public function test_wrong_password_pending_invitation_and_disabled_identity_cannot_login(): void
    {
        $user = $this->staff();
        $this->postJson('/corporate/login', ['email' => $user->email, 'password' => 'incorrect'])->assertUnprocessable();
        $user->update(['password_set_at' => null]);
        $this->postJson('/corporate/login', ['email' => $user->email, 'password' => 'CorporateTest123!'])->assertUnprocessable();
        $user->update(['password_set_at' => now(), 'status' => 'Inactive']);
        $this->postJson('/corporate/login', ['email' => $user->email, 'password' => 'CorporateTest123!'])->assertUnprocessable();
        $this->assertGuest('corporate');
    }

    public function test_portal_design_reuses_brand_assets_and_accessible_auth_controls(): void
    {
        foreach (['/corporate/login', '/corporate/forgot-password', '/corporate/reset-password/render-only-token?email=amina@example.test'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('css/marketing-tokens.css', false)
                ->assertSee('images/marketing/lala-taxi-logo.webp', false)
                ->assertSee('images/marketing/corporate-hero.webp', false)
                ->assertSee('portal-auth-shell', false)
                ->assertSee('name="_token"', false)
                ->assertDontSee('corporate.portal_', false);
        }
        $this->get('/corporate/login')->assertSee('aria-controls="password"', false)
            ->assertSee('autocomplete="current-password"', false);
        $this->get('/corporate/reset-password/render-only-token?email=amina@example.test')
            ->assertSee('aria-controls="password_confirmation"', false)->assertSee('minlength="12"', false);
        $css = file_get_contents(public_path('css/corporate-portal.css'));
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b|rgba?\(/', $css);
        $this->assertStringContainsString('--portal-radius: 0;', $css);
        $this->assertStringContainsString('var(--site-gold)', $css);
    }

    public function test_branded_navigation_only_exposes_permitted_pages_and_staff_error_editor_reopens(): void
    {
        foreach (['admin', 'finance', 'viewer'] as $role) {
            $staff = $this->staff($role);
            $this->signIn($staff);
            $response = $this->get('/corporate')->assertOk()->assertSee('portal-sidebar', false)
                ->assertSee('aria-current="page"', false)->assertSee('data-lucide="layout-dashboard"', false);
            foreach (['members', 'staff'] as $page) {
                if ($role === 'admin') $response->assertSee('href="http://127.0.0.1/corporate/'.$page.'"', false);
                else $response->assertDontSee('href="http://127.0.0.1/corporate/'.$page.'"', false);
            }
            if ($role === 'viewer') $response->assertDontSee('href="http://127.0.0.1/corporate/invoices"', false);
            else $response->assertSee('href="http://127.0.0.1/corporate/invoices"', false);
        }
        $admin = $this->staff();
        $this->signIn($admin);
        $response = $this->withSession(['_old_input'=>['_portal_form'=>(string) $admin->id]])
            ->get('/corporate/staff')->assertOk()->assertSee('portal-form--grid', false);
        $this->assertMatchesRegularExpression('/<details class="portal-card portal-staff"\s+open/', $response->getContent());
        $this->account->update(['archived_at'=>now(), 'status'=>'Inactive']);
        $this->get('/corporate/staff')->assertOk()->assertDontSee('name="_portal_form"', false);
    }

    public function test_platform_admin_invites_corporate_staff_without_changing_mobile_privileges(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $url = '/admin/companies/1/corporate-accounts/accounts/'.$this->account->id.'/portal-users';
        $this->post($url, $this->staffInput())->assertRedirect()->assertSessionHas('status', __('corporate.portal_invitation_sent'));
        $staff = CorporatePortalUser::firstOrFail();
        $this->assertNull($staff->password_set_at);
        $this->assertFalse(Hash::check('CorporateTest123!', $staff->password));
        $this->assertSame('member', DB::table('users')->where('id', 1)->value('corporate_role'));
        Notification::assertSentTo($staff, CorporateResetPassword::class, function ($notification) use ($staff) {
            $this->assertStringContainsString('/corporate/reset-password/', $notification->toMail($staff)->actionUrl);
            $this->assertTrue(Password::broker('corporate')->tokenExists($staff, $notification->token));
            return true;
        });
        $this->assertSame('portal.invited', $this->account->audits()->first()->action);
        $this->post($url.'/'.$staff->id.'/invite')->assertRedirect()->assertSessionHasErrors('email');
        $this->actingAs($this->admin(false), 'admin');
        $this->postJson($url, $this->staffInput(['email' => 'other@example.test']))->assertForbidden();
    }

    public function test_invitation_setup_enforces_password_strength_expiry_and_single_use(): void
    {
        $staff = $this->staff();
        $staff->update(['password_set_at' => null]);
        $token = Password::broker('corporate')->createToken($staff);
        $data = ['token' => $token, 'email' => $staff->email, 'password' => 'NewCorporate123!', 'password_confirmation' => 'NewCorporate123!'];
        $this->get('/corporate/reset-password/'.$token.'?email='.urlencode($staff->email))->assertOk();
        $this->postJson('/corporate/reset-password', array_replace($data, ['password' => 'short', 'password_confirmation' => 'short']))
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->post('/corporate/reset-password', $data)->assertRedirect('/corporate/login');
        $this->assertTrue(Hash::check('NewCorporate123!', $staff->fresh()->password));
        $this->assertSame(2, $staff->fresh()->session_version);
        $this->postJson('/corporate/reset-password', $data)->assertUnprocessable();
        $expired = Password::broker('corporate')->createToken($staff);
        $this->travel(61)->minutes();
        $this->postJson('/corporate/reset-password', array_replace($data, ['token' => $expired]))->assertUnprocessable();
        $this->travelBack();
    }

    public function test_recovery_never_enumerates_unknown_or_disabled_emails(): void
    {
        $staff = $this->staff();
        foreach ([$staff->email, 'unknown@example.test'] as $email) {
            $this->from('/corporate/forgot-password')->post('/corporate/forgot-password', ['email' => $email])
                ->assertRedirect('/corporate/forgot-password')->assertSessionHas('status', __('corporate.portal_recovery_sent'));
        }
        $staff->update(['status' => 'Inactive']);
        $this->post('/corporate/forgot-password', ['email' => $staff->email])->assertRedirect()->assertSessionHas('status', __('corporate.portal_recovery_sent'));
        Notification::assertSentToTimes($staff, CorporateResetPassword::class, 1);
    }

    public function test_real_markdown_invitation_renders_without_optional_subcopy_and_uses_the_corporate_broker(): void
    {
        $staff = $this->staff();
        $mail = (new CorporateResetPassword('render-only-test-token'))->toMail($staff);
        $html = (string) $mail->render();
        $this->assertStringContainsString(__('corporate.portal_email_intro'), $html);
        $this->assertStringContainsString('/corporate/reset-password/render-only-test-token', $html);
        $this->assertStringContainsString(__('corporate.portal_email_expiry', ['minutes' => 60]), $html);
        $this->assertSame(__('corporate.portal_reset'), $mail->subject);
        // Existing non-corporate Markdown messages share this layout and must still render.
        $ordinary = (new \Illuminate\Notifications\Messages\MailMessage)->line('Compatibility test message');
        $this->assertNotEmpty((string) $ordinary->render());
    }

    public function test_finance_and_viewer_roles_cannot_manage_members_or_staff(): void
    {
        foreach (['finance', 'viewer'] as $role) {
            $staff = $this->staff($role);
            $this->signIn($staff);
            $this->get('/corporate')->assertOk();
            $this->get('/corporate/trips')->assertOk()->assertSee(__('corporate.no_trips'));
            $this->get('/corporate/members')->assertForbidden();
            $this->get('/corporate/staff')->assertForbidden();
            $this->postJson('/corporate/staff', $this->staffInput())->assertForbidden();
            $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'manager'])->assertForbidden();
            $this->delete('/corporate/members/1')->assertForbidden();
            $this->get('/corporate/invoices')->assertStatus($role === 'finance' ? 200 : 403);
        }
    }

    public function test_staff_and_employee_access_cannot_cross_employer_boundaries(): void
    {
        $this->signIn($this->staff());
        $foreign = $this->staff('admin', $this->foreignAccount());
        $this->get('/corporate/staff')->assertOk()->assertDontSee($foreign->email);
        $this->putJson('/corporate/staff/'.$foreign->id, $this->staffInput())->assertNotFound();
        $this->post('/corporate/staff/'.$foreign->id.'/invite')->assertNotFound();
        DB::table('users')->insert(['id' => 3, 'company_id' => 2, 'first_name' => 'Foreign', 'corporate_account_id' => $foreign->corporate_account_id, 'corporate_role' => 'member', 'mobile_number' => '700000113']);
        $this->get('/corporate/members')->assertOk()->assertDontSee('Foreign');
        $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000113', 'role' => 'member'])
            ->assertUnprocessable()->assertJsonValidationErrors('mobile_number');
        $this->delete('/corporate/members/3')->assertNotFound();
        $this->assertSame('admin', $foreign->fresh()->role);
    }

    public function test_employees_can_be_assigned_and_removed_with_audit_and_retained_history(): void
    {
        $staff = $this->staff();
        $this->signIn($staff);
        DB::table('users')->where('id', 1)->update(['corporate_account_id' => null, 'corporate_role' => null]);
        $input = ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'];
        $this->post('/corporate/members', $input)->assertRedirect('/corporate/members');
        $this->assertSame('member', DB::table('users')->where('id', 1)->value('corporate_role'));
        $this->post('/corporate/members', array_replace($input, ['role' => 'manager']))->assertRedirect();
        $this->delete('/corporate/members/1')->assertRedirect('/corporate/members');
        $this->assertNull(DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertSame(3, $this->account->audits()->where('actor', 'portal:'.$staff->id)->count());
        $this->assertSame(1, CompanyCorporateAccount::count());
    }

    public function test_normal_customer_registration_can_join_platform_employer_without_changing_dispatch_owner(): void
    {
        $this->signIn($this->staff());
        DB::table('users')->where('id', 1)->update(['company_id' => null, 'corporate_account_id' => null, 'corporate_role' => null]);
        $input = ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'];
        $this->post('/corporate/members', $input)->assertRedirect('/corporate/members');
        $this->post('/corporate/members', $input)->assertRedirect('/corporate/members');
        $this->assertSame($this->account->id, (int) DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertNull(DB::table('users')->where('id', 1)->value('company_id'));
        $this->assertSame(1, $this->account->audits()->where('action', 'membership.assigned')->count());
        $this->delete('/corporate/members/1')->assertRedirect('/corporate/members');
        $this->assertNull(DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertNull(DB::table('users')->where('id', 1)->value('company_id'));
    }

    public function test_phone_lookup_accepts_customer_otp_trunk_zero_spellings_in_both_directions(): void
    {
        $this->signIn($this->staff());
        foreach ([['700000110', '0700000110'], ['0700000110', '700000110']] as [$stored, $entered]) {
            DB::table('users')->where('id', 1)->update(['company_id' => null, 'mobile_number' => $stored,
                'corporate_account_id' => null, 'corporate_role' => null]);
            $this->post('/corporate/members', ['country_code' => '254', 'mobile_number' => $entered, 'role' => 'member'])
                ->assertRedirect('/corporate/members');
            $this->assertSame($this->account->id, (int) DB::table('users')->where('id', 1)->value('corporate_account_id'));
            $this->assertSame($stored, DB::table('users')->where('id', 1)->value('mobile_number'));
        }
    }

    public function test_portal_lookup_never_links_foreign_inactive_deleted_driver_or_wrong_country_accounts(): void
    {
        $this->signIn($this->staff());
        $otherEmployer = CompanyCorporateAccount::create(['company_id' => 1, 'account_name' => 'Other Employer',
            'legal_name' => 'Other Employer Ltd', 'billing_address' => 'Kenya', 'currency_code' => 'KES', 'status' => 'Active']);
        foreach ([['company_id' => 2], ['status' => 'Inactive'], ['deleted_at' => now()],
            ['user_type' => 'Driver'], ['country_code' => '27'], ['corporate_account_id' => $otherEmployer->id]] as $invalid) {
            DB::table('users')->where('id', 1)->update(array_replace(['company_id' => null, 'status' => 'Active',
                'deleted_at' => null, 'user_type' => 'Rider', 'country_code' => '254',
                'corporate_account_id' => null, 'corporate_role' => null], $invalid));
            $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'])
                ->assertUnprocessable()->assertJsonValidationErrors('mobile_number');
            $this->assertSame(0, $this->account->audits()->count());
        }
    }

    public function test_null_platform_riders_do_not_become_available_to_other_operators(): void
    {
        $foreign = $this->foreignAccount();
        $this->signIn($this->staff('admin', $foreign));
        DB::table('users')->where('id', 1)->update(['company_id' => null, 'corporate_account_id' => null, 'corporate_role' => null]);
        $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'])
            ->assertUnprocessable()->assertJsonValidationErrors('mobile_number');
        $this->assertNull(DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertSame(0, $foreign->audits()->count());
    }

    public function test_platform_rider_lookup_uses_configured_platform_id_not_a_hardcoded_company(): void
    {
        config(['saas_dispatch.platform_company_id' => 2]);
        $foreign = $this->foreignAccount();
        $this->signIn($this->staff('admin', $foreign));
        DB::table('users')->where('id', 1)->update(['company_id' => null, 'corporate_account_id' => null, 'corporate_role' => null]);
        $this->post('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'])
            ->assertRedirect('/corporate/members');
        $this->assertSame($foreign->id, (int) DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertNull(DB::table('users')->where('id', 1)->value('company_id'));
    }

    public function test_duplicate_local_phone_spellings_fail_safely_without_linking_either_rider(): void
    {
        $this->signIn($this->staff());
        DB::table('users')->where('id', 1)->update(['company_id' => null, 'corporate_account_id' => null, 'corporate_role' => null]);
        DB::table('users')->insert(['id' => 3, 'company_id' => null, 'mobile_number' => '0700000110']);
        $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'])
            ->assertUnprocessable()->assertJsonValidationErrors('mobile_number')
            ->assertJsonPath('errors.mobile_number.0', __('corporate.portal_ambiguous_rider'));
        $this->assertSame(0, DB::table('users')->whereNotNull('corporate_account_id')->count());
        $this->assertSame(0, $this->account->audits()->count());
    }

    public function test_other_employer_affiliation_does_not_hide_a_duplicate_phone_identity(): void
    {
        $this->signIn($this->staff());
        $other = CompanyCorporateAccount::create(['company_id' => 1, 'account_name' => 'Other Employer',
            'legal_name' => 'Other Employer Ltd', 'billing_address' => 'Kenya', 'currency_code' => 'KES', 'status' => 'Active']);
        DB::table('users')->where('id', 1)->update(['company_id' => null, 'corporate_account_id' => null, 'corporate_role' => null]);
        DB::table('users')->insert(['id' => 3, 'company_id' => null, 'mobile_number' => '0700000110',
            'corporate_account_id' => $other->id, 'corporate_role' => 'member']);
        $this->postJson('/corporate/members', ['country_code' => '254', 'mobile_number' => '700000110', 'role' => 'member'])
            ->assertUnprocessable()->assertJsonPath('errors.mobile_number.0', __('corporate.portal_ambiguous_rider'));
        $this->assertNull(DB::table('users')->where('id', 1)->value('corporate_account_id'));
        $this->assertSame($other->id, (int) DB::table('users')->where('id', 3)->value('corporate_account_id'));
        $this->assertSame(0, $this->account->audits()->count());
    }

    public function test_privilege_changes_revoke_existing_sessions_and_protect_the_last_administrator(): void
    {
        $staff = $this->staff();
        $this->signIn($staff);
        $this->putJson('/corporate/staff/'.$staff->id, $this->staffInput(['email' => $staff->email, 'role' => 'viewer']))
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->staff();
        $this->put('/corporate/staff/'.$staff->id, $this->staffInput(['email' => $staff->email, 'role' => 'viewer']))->assertRedirect();
        $this->get('/corporate/members')->assertRedirect('/corporate/login');
        $this->assertGuest('corporate');
        $this->assertSame(2, $staff->fresh()->session_version);
        $this->signIn($staff->fresh());
        $staff->update(['status' => 'Inactive']);
        $this->get('/corporate')->assertRedirect('/corporate/login');
    }

    public function test_changed_email_invalidates_old_recovery_and_requires_new_setup(): void
    {
        $staff = $this->staff();
        $actor = $this->staff();
        $token = Password::broker('corporate')->createToken($staff);
        app(CorporatePortalUserService::class)->save($this->account, $this->staffInput(['email' => 'changed@example.test']), $actor, $staff);
        $this->assertNull($staff->fresh()->password_set_at);
        $this->assertFalse(Password::broker('corporate')->tokenExists($staff, $token));
        $this->assertSame(0, DB::table('corporate_password_resets')->where('email', $staff->email)->count());
    }

    public function test_archived_accounts_are_read_only_and_inactive_accounts_cannot_sign_in(): void
    {
        $staff = $this->staff();
        $this->signIn($staff);
        $this->account->update(['archived_at' => now(), 'status' => 'Inactive']);
        $this->get('/corporate')->assertOk()->assertSee(__('corporate.account_archived'));
        $this->get('/corporate/members')->assertOk()->assertDontSee('action="http://127.0.0.1/corporate/members"', false);
        $this->postJson('/corporate/staff', $this->staffInput())->assertStatus(409);
        $this->delete('/corporate/members/1')->assertStatus(409);
        $this->account->update(['archived_at' => null]);
        $this->get('/corporate')->assertRedirect('/corporate/login');
        $this->postJson('/corporate/login', ['email' => $staff->email, 'password' => 'CorporateTest123!'])->assertUnprocessable();
    }

    public function test_additive_identity_migration_is_repeatable_and_rollback_retains_access_records(): void
    {
        $staff = $this->staff();
        $migration = require database_path('migrations/2026_10_08_000002_create_corporate_portal_identities.php');
        $migration->up();
        $migration->down();
        $this->assertSame($staff->email, CorporatePortalUser::firstOrFail()->email);
    }

    public function test_portal_reads_only_own_corporate_trips_and_published_trip_backed_invoices(): void
    {
        $this->signIn($this->staff('finance'));
        $foreign = $this->foreignAccount();
        $ownTrip = DB::table('trips')->insertGetId(['user_id' => 1, 'corporate_account_id' => $this->account->id,
            'billing_mode' => 'corporate', 'billing_status' => 'unbilled', 'corporate_amount' => '250.00',
            'corporate_currency_code' => 'KES', 'pickup_location' => 'Own workplace', 'drop_location' => 'Own destination']);
        $foreignTrip = DB::table('trips')->insertGetId(['user_id' => 2, 'corporate_account_id' => $foreign->id,
            'billing_mode' => 'corporate', 'billing_status' => 'unbilled', 'pickup_location' => 'Foreign secret']);
        $personal = DB::table('trips')->insertGetId(['user_id' => 1, 'corporate_account_id' => $this->account->id,
            'billing_mode' => 'personal', 'pickup_location' => 'Private personal trip']);
        $this->get('/corporate/trips')->assertOk()->assertSee('Own workplace')->assertDontSee('Foreign secret')->assertDontSee('Private personal trip');
        $this->get('/corporate/trips/'.$ownTrip)->assertOk()->assertSee('250.00');
        $this->get('/corporate/trips/'.$foreignTrip)->assertNotFound();
        $this->get('/corporate/trips/'.$personal)->assertNotFound();

        $base = ['company_id' => 1, 'corporate_account_id' => $this->account->id, 'currency_code' => 'KES',
            'status' => 'Sent', 'trip_backed' => true, 'period_start' => '2026-09-01 00:00:00', 'period_end' => '2026-10-01 00:00:00',
            'billing_timezone' => 'Africa/Nairobi', 'invoice_date' => '2026-10-01', 'bill_to_snapshot' => ['account_name' => 'Employer', 'legal_name' => 'Employer Ltd',
                'billing_address' => 'Kenya', 'contact_email' => 'finance@example.test'], 'subtotal' => '250.00', 'total_amount' => '250.00'];
        $invoice = \Modules\SaaSDispatch\Entities\CompanyCorporateInvoice::create($base + ['invoice_number' => 'OWN-ISSUED']);
        $draft = \Modules\SaaSDispatch\Entities\CompanyCorporateInvoice::create(array_replace($base, ['invoice_number' => 'SECRET-DRAFT', 'status' => 'Draft']));
        $legacy = \Modules\SaaSDispatch\Entities\CompanyCorporateInvoice::create(array_replace($base, ['invoice_number' => 'LEGACY-NO-EVIDENCE', 'trip_backed' => false]));
        $other = \Modules\SaaSDispatch\Entities\CompanyCorporateInvoice::create(array_replace($base, ['invoice_number' => 'FOREIGN-ISSUED', 'corporate_account_id' => $foreign->id, 'company_id' => 2]));
        $this->get('/corporate/invoices')->assertOk()->assertSee('OWN-ISSUED')->assertDontSee('SECRET-DRAFT')->assertDontSee('FOREIGN-ISSUED')->assertDontSee('LEGACY-NO-EVIDENCE');
        $this->get('/corporate/invoices/'.$invoice->id)->assertOk()->assertSee('OWN-ISSUED');
        $pdf = $this->get('/corporate/invoices/'.$invoice->id.'/download')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        foreach ([$draft, $other, $legacy] as $hidden) {
            $this->get('/corporate/invoices/'.$hidden->id)->assertNotFound();
            $this->get('/corporate/invoices/'.$hidden->id.'/download')->assertNotFound();
        }
        $this->post('/corporate/invoices/'.$invoice->id.'/settle', [])->assertNotFound();
        $this->signIn($this->staff('viewer'));
        $this->get('/corporate/invoices/'.$invoice->id)->assertForbidden();
        $this->get('/corporate/invoices/'.$invoice->id.'/download')->assertForbidden();
    }

    public function test_invitation_delivery_failure_retains_the_identity_and_can_be_retried(): void
    {
        $actor = $this->staff();
        $this->signIn($actor);
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Isolated mail delivery failure'));
        $this->post('/corporate/staff', $this->staffInput(['role' => 'viewer']))->assertRedirect('/corporate/staff')
            ->assertSessionHasErrors(['email' => __('corporate.portal_mail_failed')]);
        $invited = CorporatePortalUser::where('email', 'njeri@example.test')->firstOrFail();
        $this->assertNull($invited->password_set_at);
        Notification::fake();
        $this->travel(61)->seconds();
        $this->post('/corporate/staff/'.$invited->id.'/invite')->assertRedirect('/corporate/staff')
            ->assertSessionHas('status', __('corporate.portal_invitation_sent'));
        Notification::assertSentTo($invited, CorporateResetPassword::class);
        $this->travelBack();
    }

    public function test_login_rate_limiting_and_actual_csrf_middleware_remain_enabled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/corporate/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/corporate/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertStatus(429);
        $this->app->bind(\App\Http\Middleware\PreventRequestForgery::class, function ($app) {
            return new class($app, $app['encrypter']) extends \App\Http\Middleware\PreventRequestForgery {
                protected function runningUnitTests() { return false; }
            };
        });
        $staff = $this->staff();
        $this->signIn($staff);
        $this->from('/corporate')->post('/corporate/logout')->assertRedirect('/corporate/login')->assertSessionHasErrors('email');
        // The existing HTML exception handler redirects expired CSRF requests;
        // the logout controller must not run or destroy a valid login.
        $this->assertAuthenticatedAs($staff, 'corporate');
    }

    public function test_operator_web_invites_cannot_reach_another_operators_account_or_staff(): void
    {
        $foreign = $this->staff('admin', $this->foreignAccount());
        $this->actingAs(\App\Models\Company::findOrFail(1), 'company');
        $base = '/company/saas-dispatch/accounts/accounts/';
        $this->post($base.$this->account->id.'/portal-users', $this->staffInput())->assertRedirect();
        $own = CorporatePortalUser::where('email', 'njeri@example.test')->firstOrFail();
        $this->assertSame($this->account->id, $own->corporate_account_id);
        $this->putJson($base.$this->account->id.'/portal-users/'.$foreign->id, $this->staffInput())->assertNotFound();
        $this->post($base.$this->account->id.'/portal-users/'.$foreign->id.'/invite')->assertNotFound();
        $this->postJson($base.$foreign->corporate_account_id.'/portal-users', $this->staffInput())->assertNotFound();
    }

    public function test_auth_rate_limits_do_not_collide_between_people_or_login_and_setup(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/corporate/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/corporate/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertStatus(429);
        $other = $this->staff();
        $this->post('/corporate/login', ['email' => $other->email, 'password' => 'CorporateTest123!'])->assertRedirect('/corporate');
        $token = Password::broker('corporate')->createToken($other);
        $this->post('/corporate/reset-password', ['email' => $other->email, 'token' => $token,
            'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!'])->assertRedirect('/corporate/login');
        $this->postJson('/corporate/login', ['email' => ['malformed'], 'password' => 'wrong'])->assertUnprocessable();
    }

    public function test_malformed_invitation_email_is_validated_without_creating_staff_or_sending_mail(): void
    {
        $actor = $this->staff();
        $this->signIn($actor);
        foreach ([['malformed'], 123, null] as $email) {
            $this->postJson('/corporate/staff', $this->staffInput(['email' => $email]))
                ->assertUnprocessable()->assertJsonValidationErrors('email');
        }
        $this->actingAs($this->admin(), 'admin');
        $url = '/admin/companies/1/corporate-accounts/accounts/'.$this->account->id.'/portal-users';
        foreach ([['malformed'], 123, null] as $email) {
            $this->postJson($url, $this->staffInput(['email' => $email]))
                ->assertUnprocessable()->assertJsonValidationErrors('email');
        }
        $this->assertSame(1, CorporatePortalUser::count());
        $this->assertSame(0, $this->account->audits()->count());
        Notification::assertNothingSent();
    }
}
