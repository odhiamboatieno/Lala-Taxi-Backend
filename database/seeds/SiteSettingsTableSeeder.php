<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'site_name', 'value' => 'LalaTaxi'],
            ['id' => 2, 'name' => 'payment_currency', 'value' => 'GBP'],
            ['id' => 3, 'name' => 'version', 'value' => '1.0'],
            ['id' => 4, 'name' => 'logo', 'value' => 'logo.png'],
            ['id' => 5, 'name' => 'page_logo', 'value' => 'page_logo.png'],
            ['id' => 6, 'name' => 'favicon', 'value' => 'favicon.png'],
            ['id' => 7, 'name' => 'driver_km', 'value' => '5'],
            ['id' => 8, 'name' => 'head_code', 'value' => ''],
            ['id' => 9, 'name' => 'admin_contact', 'value' => '0703803051'],
            ['id' => 10, 'name' => 'admin_country_code', 'value' => '110'],
            ['id' => 11, 'name' => 'site_url', 'value' => 'http://newtaxi.seentechs.com/public/'],
            ['id' => 12, 'name' => 'heat_map', 'value' => 'On'],
            ['id' => 13, 'name' => 'heat_map_hours', 'value' => '3'],
            ['id' => 14, 'name' => 'update_loc_interval', 'value' => '1'],
            ['id' => 15, 'name' => 'offline_hours', 'value' => '1'],
            ['id' => 16, 'name' => 'pickup_km', 'value' => '3'],
            ['id' => 17, 'name' => 'drop_km', 'value' => '1'],
            ['id' => 18, 'name' => 'max_waiting_time', 'value' => '2'],
            ['id' => 19, 'name' => 'social_logins', 'value' => 'facebook,google'],
            ['id' => 20, 'name' => 'otp_verification', 'value' => '1'],
            ['id' => 21, 'name' => 'covid_enable', 'value' => '0'],
            ['id' => 22, 'name' => 'driver_request_seconds', 'value' => '45'],
            ['id' => 23, 'name' => 'driver_request_limit', 'value' => '100'],
            ['id' => 24, 'name' => 'copyright_year', 'value' => '2023'],
            ['id' => 25, 'name' => 'copyright_url', 'value' => 'https://wilaso.com'],
            ['id' => 26, 'name' => 'copyright_name', 'value' => 'White Label Software'],
            ['id' => 27, 'name' => 'default_otp', 'value' => 'twilio'],
            ['id' => 28, 'name' => 'favorite_driver_enabled', 'value' => '1'],
            ['id' => 29, 'name' => 'driver_owe_limit', 'value' => '2000'],
            ['id' => 30, 'name' => 'fare_mode', 'value' => 'Taximeter'],
            ['id' => 31, 'name' => 'demo_mode', 'value' => '1'],
            ['id' => 32, 'name' => 'minimum_scheduled_lead_minutes', 'value' => '15'],
            ['id' => 33, 'name' => 'allow_asap_manual_booking', 'value' => '1'],
            ['id' => 34, 'name' => 'allow_dispatch_time_override', 'value' => '1'],
            ['id' => 35, 'name' => 'scheduled_dispatch_lead_minutes', 'value' => '5'],
            ['id' => 36, 'name' => 'default_assignment_timeout_seconds', 'value' => '30'],
            ['id' => 37, 'name' => 'asap_assignment_timeout_seconds', 'value' => '20'],
            ['id' => 38, 'name' => 'scheduled_assignment_timeout_seconds', 'value' => '45'],
            ['id' => 39, 'name' => 'max_assignment_attempts', 'value' => '10'],
            ['id' => 40, 'name' => 'enable_broadcast_dispatch', 'value' => '1'],
            ['id' => 41, 'name' => 'enable_force_dispatch', 'value' => '1'],
            ['id' => 42, 'name' => 'enable_sequential_dispatch', 'value' => '1'],
            ['id' => 43, 'name' => 'auto_expand_radius', 'value' => '1'],
            ['id' => 44, 'name' => 'default_radius_miles', 'value' => '10'],
            ['id' => 45, 'name' => 'saas_dispatch_module_enabled', 'value' => '1'],
            ['id' => 46, 'name' => 'manual_booking_module_enabled', 'value' => '1'],
            ['id' => 47, 'name' => 'company_onboarding_enabled', 'value' => '1'],
            ['id' => 48, 'name' => 'company_onboarding_require_phone_verification', 'value' => '0'],
            ['id' => 49, 'name' => 'company_onboarding_require_email_verification', 'value' => '0'],
            ['id' => 50, 'name' => 'company_onboarding_require_logo_upload', 'value' => '1'],
            ['id' => 51, 'name' => 'company_onboarding_require_documents_upload', 'value' => '0'],
            ['id' => 52, 'name' => 'company_onboarding_require_admin_approval', 'value' => '1'],
            ['id' => 53, 'name' => 'driver_profile_image_auto_approval', 'value' => '1'],
            ['id' => 54, 'name' => 'wallet_payment_owner', 'value' => 'company'],
            ['id' => 55, 'name' => 'user_level_owner', 'value' => 'admin'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('site_settings')->insert($chunk);
        }
    }
}
