<?php

use Illuminate\Database\Seeder;

class SiteSettingsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('site_settings')->delete();

		DB::table('site_settings')->insert([
			['name' => 'site_name', 'value' => 'Your Domain'],
			['name' => 'payment_currency', 'value' => 'USD'],
			['name' => 'version', 'value' => '2.4'],
			['name' => 'logo', 'value' => 'logo.png'],
			['name' => 'page_logo', 'value' => 'page_logo.png'],
			['name' => 'favicon', 'value' => 'favicon.png'],
			['name' => 'driver_km', 'value' => '5'],
			['name' => 'head_code', 'value' => ''],
			['name' => 'admin_contact', 'value' => '1234567890'],
			['name' => 'admin_country_code', 'value' => '91'],
			['name' => 'site_url', 'value' => ''],
			['name' => 'heat_map', 'value' => 'On'],
			['name' => 'heat_map_hours', 'value' => '3'],
			['name' => 'update_loc_interval', 'value' => '10'],
			['name' => 'offline_hours', 'value' => '1'],
			['name' => 'pickup_km', 'value' => '3'],
			['name' => 'drop_km', 'value' => '1'],
			['name' => 'max_waiting_time', 'value' => '2'],
			['name' => 'social_logins', 'value' => 'facebook,google,apple'],
			['name' => 'otp_verification', 'value' => '1'],
			['name' => 'demo_mode', 'value' => '0'],
			['name' => 'covid_enable', 'value' => '1'],
			['name' => 'favorite_driver_enabled', 'value' => '0'],
			['name' => 'driver_request_seconds', 'value' => '10'],
			['name' => 'driver_request_limit', 'value' => '10'],
			['name' => 'minimum_scheduled_lead_minutes', 'value' => '15'],
			['name' => 'allow_asap_manual_booking', 'value' => '1'],
			['name' => 'allow_dispatch_time_override', 'value' => '1'],
			['name' => 'scheduled_dispatch_lead_minutes', 'value' => '5'],
			['name' => 'default_assignment_timeout_seconds', 'value' => '30'],
			['name' => 'asap_assignment_timeout_seconds', 'value' => '20'],
			['name' => 'scheduled_assignment_timeout_seconds', 'value' => '45'],
			['name' => 'max_assignment_attempts', 'value' => '10'],
			['name' => 'enable_broadcast_dispatch', 'value' => '1'],
			['name' => 'enable_force_dispatch', 'value' => '1'],
			['name' => 'enable_sequential_dispatch', 'value' => '1'],
			['name' => 'auto_expand_radius', 'value' => '1'],
			['name' => 'default_radius_miles', 'value' => '10'],
			['name' => 'copyright_year', 'value' => '2021'],
			['name' => 'copyright_url', 'value' => 'https://cloneappsolutions.com/'],
			['name' => 'copyright_name', 'value' => 'Cabme'],
			['name' => 'biometric_login_enabled', 'value' => '0'],
		]);
	}
}
