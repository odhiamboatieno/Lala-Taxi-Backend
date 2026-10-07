<?php

namespace Tests\Concerns;

use App\Models\SiteSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

trait CreatesCorporateBookingSchema
{
    use CreatesCorporateSchema;

    protected CompanyCorporateAccount $account;

    protected function createCorporateBookingSchema(): void
    {
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

}
