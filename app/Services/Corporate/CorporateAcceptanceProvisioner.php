<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use RuntimeException;

/** Isolated acceptance data, not a production seeder or a financial simulator. */
class CorporateAcceptanceProvisioner
{
    public const DATABASE = 'lala_corporate_acceptance_test';

    public function provision(string $password): array
    {
        $this->assertIsolated($password);

        return DB::transaction(function () use ($password) {
            $country = $this->insertOnce('country', ['short_name' => 'KE'], ['long_name' => 'Kenya', 'phone_code' => '254', 'iso3' => 'KEN']);
            $this->insertOnce('currency', ['code' => 'KES'], ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate' => '1.00', 'status' => 'Active', 'default_currency' => '1']);
            $this->insertOnce('language', ['value' => 'en'], ['name' => 'English', 'status' => 'Active', 'default_language' => '1']);
            foreach (['site_name' => 'Lala Taxi Acceptance', 'default_currency' => 'KES', 'time_zone' => 'Africa/Nairobi',
                'saas_dispatch_module_enabled' => '1', 'driver_km' => '10', 'otp_verification' => '1',
                'fare_mode' => 'Taximeter', 'driver_request_seconds' => '120'] as $name => $value) {
                $this->insertOnce('site_settings', ['name' => $name], ['value' => $value]);
            }
            app()->forgetInstance('site_settings');
            app()->singleton('site_settings', fn () => SiteSettings::all());
            $this->insertOnce('saas_feature_modules', ['slug' => 'accounts_invoicing'], ['name' => 'Accounts and invoicing', 'is_installed' => 1, 'is_enabled' => 1, 'status' => 'Active']);

            $adminId = $this->insertOnce('admins', ['username' => 'qa_njeri'], ['email' => 'njeri.admin@acceptance.lalataxi.test',
                'password' => Hash::make($password), 'country_code' => '254', 'country_id' => $country, 'mobile_number' => '700000100', 'status' => 'Active'], ['password']);
            $roleId = $this->insertOnce('roles', ['name' => 'corporate_acceptance'], ['display_name' => 'Corporate acceptance administrator']);
            foreach (['manage_company_accounts_invoicing', 'view_dashboard', 'manage_company'] as $permission) {
                $permissionId = $this->insertOnce('permissions', ['name' => $permission], ['display_name' => $permission]);
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
            DB::table('role_user')->insertOrIgnore(['user_id' => $adminId, 'role_id' => $roleId]);
            $admin = Admin::findOrFail($adminId);

            $operators = [];
            foreach (['Lala QA Platform', 'Rift Valley Fleet QA'] as $index => $name) {
                $email = 'operator'.($index + 1).'@acceptance.lalataxi.test';
                // The configured platform ID must resolve to our fictional company,
                // never an unrelated row already present in this database.
                $identity = $index === 0 ? ['id' => saas_platform_company_id()] : ['email' => $email];
                $companyId = $this->insertOnce('companies', $identity, ['name' => $name, 'profile' => '', 'email' => $email,
                    'country_code' => '254', 'mobile_number' => '70000010'.($index + 1), 'password' => Hash::make($password),
                    'device_id' => 'acceptance-only', 'language' => 'en', 'country' => 'Kenya', 'country_id' => $country,
                    'status' => 'Active', 'approval_status' => 'approved', 'onboarding_status' => 'approved'], ['password']);
                $operators[] = Company::findOrFail($companyId);
                $this->insertOnce('company_saas_settings', ['company_id' => $companyId], ['is_saas_enabled' => 1, 'dispatch_mode' => 'hybrid', 'status' => 'Active']);
            }

            $result = [['Web admin', 'Njeri QA Administrator', 'qa_njeri', 'Both fictional operators']];
            $people = [
                [['Njeri', 'Mwangi', 'member'], ['Amina', 'Wanjiku', 'manager'], ['Otieno', 'Omondi', 'finance']],
                [['Fatuma', 'Ali', 'member'], ['Baraka', 'Njoroge', 'manager'], ['Wanjiru', 'Kiptoo', 'finance']],
            ];
            foreach (['Mwangaza Workspaces Kenya', 'Tawi Coast Supplies'] as $index => $employer) {
                $operator = $operators[$index];
                $result[] = ['Company owner', $operator->name, $operator->email, $employer];
                $account = CompanyCorporateAccount::where('company_id', $operator->id)->where('account_name', $employer)->first();
                if (!$account) {
                    $account = app(CorporateAccountService::class)->create($operator, [
                        'account_name' => $employer, 'legal_name' => $employer.' (Fictional QA)',
                        'billing_address' => 'Acceptance test office, Kenya — not a real billing address',
                        'contact_name' => $people[$index][2][0].' '.$people[$index][2][1],
                        'contact_email' => 'finance'.($index + 1).'@acceptance.lalataxi.test',
                        'currency_code' => 'KES', 'billing_timezone' => 'Africa/Nairobi', 'billing_cycle' => 'monthly',
                        'payment_terms_days' => 30, 'credit_limit' => '50000', 'status' => 'Active',
                        'notes' => 'Fictional employer for isolated acceptance only; no real-world liability.',
                    ], $admin);
                }
                foreach ($people[$index] as $offset => [$first, $last, $role]) {
                    $phone = '700000'.(110 + $index * 10 + $offset);
                    $user = $this->person($first, $last, $phone, 'Rider', (int) $operator->id, $country, $password);
                    if ($user->corporate_account_id && ((int) $user->corporate_account_id !== (int) $account->id || $user->corporate_role !== $role)) {
                        throw new RuntimeException('Acceptance membership changed; refusing to overwrite it.');
                    }
                    app(CorporateMembershipService::class)->assign($account, $user, $role, $admin);
                    $result[] = [$role, $first.' '.$last, '+254 '.$phone, $employer];
                }
            }
            $this->person('Kamau', 'Mutua', '700000130', 'Rider', (int) $operators[0]->id, $country, $password);
            $result[] = ['Personal rider', 'Kamau Mutua', '+254 700000130', 'No corporate affiliation'];

            $car = $this->insertOnce('car_type', ['car_name' => 'Lala Standard QA'], ['vehicle_image' => 'WiTaxiGo.png', 'active_image' => 'WiTaxiGo.png', 'status' => 'Active', 'is_pool' => 'No']);
            $location = $this->insertOnce('locations', ['name' => 'Nairobi Acceptance QA'], ['coordinates' => 'POLYGON((-1.4 36.7, -1.1 36.7, -1.1 37.0, -1.4 37.0, -1.4 36.7))', 'status' => 'Active']);
            $this->insertOnce('manage_fare', ['location_id' => $location, 'vehicle_id' => $car], ['base_fare' => '150.00', 'capacity' => 4,
                'min_fare' => '250.00', 'per_min' => '5.00', 'per_km' => '40.00', 'schedule_fare' => '0.00', 'schedule_cancel_fare' => '0.00',
                'waiting_time' => 5, 'waiting_charge' => '5.00', 'currency_code' => 'KES', 'apply_peak' => 'No', 'apply_night' => 'No']);
            foreach (['access_fee' => '0', 'driver_access_fee' => '15', 'driver_peak_fare' => '0'] as $name => $value) {
                $this->insertOnce('fees', ['name' => $name], ['value' => $value]);
            }
            $make = $this->insertOnce('vehicle_make', ['make_vehicle_name' => 'Toyota QA'], ['status' => 'Active']);
            $model = $this->insertOnce('vehicle_model', ['vehicle_make_id' => $make, 'model_name' => 'Axio QA'], ['status' => 'Active']);
            foreach ([['David', 'Kiptoo'], ['Brian', 'Onyango']] as $index => [$first, $last]) {
                $phone = '70000014'.$index;
                $operator = $operators[$index];
                $driver = $this->person($first, $last, $phone, 'Driver', (int) $operator->id, $country, $password);
                $this->insertOnce('vehicle', ['user_id' => $driver->id, 'vehicle_number' => 'QA 00'.($index + 1).'D'], [
                    'company_id' => $operator->id, 'vehicle_make_id' => $make, 'vehicle_model_id' => $model,
                    'vehicle_id' => (string) $car, 'vehicle_type' => 'Lala Standard QA', 'vehicle_name' => 'Toyota Axio QA',
                    'year' => '2024', 'color' => 'White', 'is_active' => 1, 'default_type' => '1', 'status' => 'Active',
                ]);
                $this->insertOnce('driver_location', ['user_id' => $driver->id], ['latitude' => '-1.2676', 'longitude' => '36.8108', 'car_id' => $car], ['latitude', 'longitude']);
                $result[] = ['Driver', $first.' '.$last, '+254 '.$phone, $operator->name];
            }

            return $result;
        }, 3);
    }

    public function assertIsolated(string $password): void
    {
        if (!app()->environment(['local', 'staging', 'testing']) || !in_array(config('app.env'), ['local', 'staging', 'testing'], true)
            || DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== self::DATABASE) {
            throw new RuntimeException('Acceptance provisioning requires a non-production MySQL database named '.self::DATABASE.'.');
        }
        if (strlen($password) < 16) {
            throw new RuntimeException('Provide an operator-owned LALATAXI_ACCEPTANCE_PASSWORD of at least 16 characters.');
        }
    }

    private function person(string $first, string $last, string $phone, string $type, int $company, int $country, string $password): User
    {
        $id = $this->insertOnce('users', ['email' => strtolower($first.'.'.$last).'@acceptance.lalataxi.test'], [
            'first_name' => $first, 'last_name' => $last, 'mobile_number' => $phone, 'country_code' => '254',
            'country_id' => $country, 'user_type' => $type, 'company_id' => $company, 'currency_code' => 'KES',
            'language' => 'en', 'device_id' => 'acceptance-only', 'referral_code' => 'QA'.$phone,
            'password' => Hash::make($password), 'status' => 'Active',
        ], ['password', 'device_id']);

        return User::findOrFail($id);
    }

    /** Preserve existing credentials and runtime state, fail closed on identity/configuration drift. */
    private function insertOnce(string $table, array $identity, array $values, array $mutable = []): int
    {
        $row = DB::table($table)->where($identity)->first();
        if (!$row) {
            return (int) DB::table($table)->insertGetId($identity + $values);
        }
        foreach ($values as $key => $value) {
            if (!in_array($key, $mutable, true) && (string) $row->$key !== (string) $value) {
                throw new RuntimeException('Acceptance fixture drift in '.$table.'.'.$key.'; refusing to overwrite existing data.');
            }
        }

        return (int) $row->id;
    }
}
