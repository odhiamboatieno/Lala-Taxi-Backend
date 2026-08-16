<?php

/**
 * Site Settings Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Site Settings
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Start\Helpers;
use App\Models\Currency;
use App\Models\SiteSettings;
use App\Models\PaymentGateway;
use App\Models\Language;
use App\Models\Timezones;
use App\Models\Country;
use Validator;

class SiteSettingsController extends Controller
{
	public function __construct()
	{
		$this->helper = new Helpers;
	}

	/**
	 * Load View and Update Site Settings Data
	 *
	 * @return redirect     to site_settings
	 */
	public function index(Request $request)
	{
		$payment_methods = collect(PAYMENT_METHODS);
		$payment_methods = $payment_methods->reject(function($value) {
			if($value['key'] == 'cash') {
				return false;
			}
			$is_enabled = payment_gateway('is_enabled',ucfirst($value['key']));
			return ($is_enabled != '1');
		});

		if ($request->isMethod('GET')) {
			$data['result'] = SiteSettings::get();

			$data['currency'] = @Currency::codeSelect();
			$data['payment_methods'] = $payment_methods;
			$data['countries'] = Country::select('id','long_name','phone_code')->get();
			$data['timezones'] = Timezones::orderBy('timezone','asc')->pluck('timezone','timezone');
			$data['default_currency'] = @Currency::defaultCurrency()->first()->code;
			return view('admin.site_settings', $data);
		}

		$payment_types = $payment_methods->pluck('key')->implode(',');

		// Site Settings Validation Rules
		$rules = [
			'site_name' => 'required',
			'logo' => 'image|mimes:jpg,png,jpeg,gif',
			'page_logo' => 'image|mimes:jpg,png,jpeg,gif',
			'favicon' => 'image|mimes:jpg,png,jpeg,gif',
			'default_currency' => 'required',
			'driver_km' => 'required|numeric',
			'pickup_km' => 'required|numeric',
			'drop_km' => 'required|numeric',
			'admin_contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
			'admin_country_code' => 'required',
			'heat_map' => 'required|In:On,Off',
			'trip_default_paymode' => 'required|in:'.$payment_types,
			'update_loc_interval' => 'required',
			'social_logins.*' => 'in:facebook,google,apple',
			'otp_verification' => 'in:1,0',
			'demo_mode' => 'in:1,0',
			'favorite_driver_enabled' => 'in:1,0',
            'company_onboarding_enabled' => 'in:1,0',
            'company_onboarding_require_phone_verification' => 'in:1,0',
            'company_onboarding_require_email_verification' => 'in:1,0',
            'company_onboarding_require_logo_upload' => 'in:1,0',
            'company_onboarding_require_documents_upload' => 'in:1,0',
            'company_onboarding_require_admin_approval' => 'in:1,0',
            'driver_profile_image_auto_approval' => 'in:1,0',
			'driver_request_seconds' => 'required|integer|min:5|max:300',
			'driver_request_limit' => 'required|integer|min:1|max:100',
            'minimum_scheduled_lead_minutes' => 'required|integer|min:0|max:720',
            'scheduled_dispatch_lead_minutes' => 'required|integer|min:0|max:720',
            'default_assignment_timeout_seconds' => 'required|integer|min:5|max:300',
            'asap_assignment_timeout_seconds' => 'required|integer|min:5|max:300',
            'scheduled_assignment_timeout_seconds' => 'required|integer|min:5|max:300',
            'max_assignment_attempts' => 'required|integer|min:1|max:100',
            'default_radius_miles' => 'required|integer|min:1|max:100',
			'copyright_year' => 'required|numeric|integer',
			'driver_owe_limit' => 'required|numeric',
            'copyright_url'  => 'required|url',
            'copyright_name' => 'required',
		];

		if($request->heat_map == 'On') {
			$rules['heat_map_hours'] = 'required|Integer|min:1';
		}

		// Site Settings Validation Custom Names
		$attributes = [
			'site_name' => 'Site Name',
			'logo' => 'logo Image',
			'logo' => 'Page logo Image',
			'favicon' => 'favicon logo',
			'default_currency' => 'Default Currency',
			'driver_km' => 'Driver Kilo meter',
			'pickup_km' => 'Pickup Kilo meter',
			'drop_km' => 'Drop Kilo meter',
			'admin_contact' => 'Admin Contact Number',
			'admin_country_code' => 'Country Code',
			'copyright_year' => 'Copyright Year',
            'copyright_url'  => 'Copyright URL',
            'driver_owe_limit'  => 'Driver Owe Amount Limit',
            'copyright_name' => 'Copyright Name',
			'timezone' => 'Timezone',
            'minimum_scheduled_lead_minutes' => 'Minimum Scheduled Lead Minutes',
            'scheduled_dispatch_lead_minutes' => 'Scheduled Dispatch Lead Minutes',
            'default_assignment_timeout_seconds' => 'Default Assignment Timeout Seconds',
            'asap_assignment_timeout_seconds' => 'ASAP Assignment Timeout Seconds',
            'scheduled_assignment_timeout_seconds' => 'Scheduled Assignment Timeout Seconds',
            'max_assignment_attempts' => 'Max Assignment Attempts',
            'default_radius_miles' => 'Default Radius Miles',
		];

		$validator = Validator::make($request->all(), $rules,[],$attributes);

		if($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
		$target_dir = '/images/logos';

		if ($request->hasFile('logo')) {
			$image = $request->file('logo');

            $extension = $image->getClientOriginalExtension();
            $file_name = "logo.".$extension;
            $options = compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($image,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }

			SiteSettings::where(['name' => 'logo'])->update(['value' => $upload_result['file_name']]);
		}

		if ($request->hasFile('page_logo')) {
			$image = $request->file('page_logo');
			
            $extension = $image->getClientOriginalExtension();
            $file_name = "page_logo.".$extension;
            $options = compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($image,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }

			SiteSettings::where(['name' => 'page_logo'])->update(['value' => $upload_result['file_name']]);
		}

		if ($request->hasFile('favicon')) {
			$image = $request->file('favicon');

            $extension = $image->getClientOriginalExtension();
            $file_name = "favicon.".$extension;
            $options = compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($image,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }

			SiteSettings::where(['name' => 'favicon'])->update(['value' => $upload_result['file_name']]);
		}

		Currency::where('status', 'Active')->update(['default_currency' => '0']);
		Currency::where('code', $request->default_currency)->update(['default_currency' => '1']);
          
        Language::where('default_language',1)->update(['default_language' => 0]);
        Language::where('value', $request->default_language)->update(['default_language' => 1]);

        PaymentGateway::where(['name' => 'trip_default', 'site' => 'Common'])->update(['value' => $request->trip_default_paymode]);

		SiteSettings::where(['name' => 'site_name'])->update(['value' => $request->site_name]);
		SiteSettings::where(['name' => 'version'])->update(['value' => $request->version]);
		SiteSettings::where(['name' => 'payment_currency'])->update(['value' => $request->payment_currency]);
		SiteSettings::updateOrCreate(['name' => 'fare_mode'], ['value' => $request->fare_mode ?: 'Taximeter']);
		SiteSettings::where(['name' => 'location_fare'])->update(['value' => $request->location_fare]);
		SiteSettings::where(['name' => 'head_code'])->update(['value' => $request->head_code]);
		SiteSettings::where(['name' => 'driver_km'])->update(['value' => $request->driver_km]);
		SiteSettings::where(['name' => 'pickup_km'])->update(['value' => $request->pickup_km]);
		SiteSettings::where(['name' => 'drop_km'])->update(['value' => $request->drop_km]);
		SiteSettings::where(['name' => 'admin_contact'])->update(['value' => $request->admin_contact]);
		SiteSettings::where(['name' => 'admin_country_code'])->update(['value' => $request->admin_country_code]);
		SiteSettings::where(['name' => 'heat_map'])->update(['value' => $request->heat_map]);
		SiteSettings::where(['name' => 'heat_map_hours'])->update(['value' => $request->heat_map_hours]);
		SiteSettings::where(['name' => 'update_loc_interval'])->update(['value' => $request->update_loc_interval]);
		SiteSettings::where(['name' => 'driver_request_seconds'])->update(['value' => $request->driver_request_seconds]);
		SiteSettings::where(['name' => 'driver_request_limit'])->update(['value' => $request->driver_request_limit]);
        SiteSettings::updateOrCreate(['name' => 'minimum_scheduled_lead_minutes'], ['value' => $request->minimum_scheduled_lead_minutes]);
        SiteSettings::updateOrCreate(['name' => 'manual_booking_module_enabled'], ['value' => $request->has('manual_booking_module_enabled') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'allow_asap_manual_booking'], ['value' => $request->has('allow_asap_manual_booking') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'allow_dispatch_time_override'], ['value' => $request->has('allow_dispatch_time_override') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'scheduled_dispatch_lead_minutes'], ['value' => $request->scheduled_dispatch_lead_minutes]);
        SiteSettings::updateOrCreate(['name' => 'default_assignment_timeout_seconds'], ['value' => $request->default_assignment_timeout_seconds]);
        SiteSettings::updateOrCreate(['name' => 'asap_assignment_timeout_seconds'], ['value' => $request->asap_assignment_timeout_seconds]);
        SiteSettings::updateOrCreate(['name' => 'scheduled_assignment_timeout_seconds'], ['value' => $request->scheduled_assignment_timeout_seconds]);
        SiteSettings::updateOrCreate(['name' => 'max_assignment_attempts'], ['value' => $request->max_assignment_attempts]);
        SiteSettings::updateOrCreate(['name' => 'enable_broadcast_dispatch'], ['value' => $request->has('enable_broadcast_dispatch') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'enable_force_dispatch'], ['value' => $request->has('enable_force_dispatch') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'enable_sequential_dispatch'], ['value' => $request->has('enable_sequential_dispatch') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'auto_expand_radius'], ['value' => $request->has('auto_expand_radius') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'default_radius_miles'], ['value' => $request->default_radius_miles]);
		SiteSettings::updateOrCreate(['name' => 'favorite_driver_enabled'], ['value' => $request->favorite_driver_enabled ?? '0']);
		SiteSettings::where(['name' => 'copyright_year'])->update(['value' => $request->copyright_year]);
		SiteSettings::where(['name' => 'copyright_url'])->update(['value' => $request->copyright_url]);
		SiteSettings::where(['name' => 'driver_owe_limit'])->update(['value' => $request->driver_owe_limit]);
		SiteSettings::where(['name' => 'copyright_name'])->update(['value' => $request->copyright_name]);
		SiteSettings::where(['name' => 'timezone'])->update(['value' => $request->timezone]);
		updateEnvFile('APP_TIMEZONE', $request->timezone);
		


		if($request->has('social_logins')) {
			$social_logins = implode(',', $request->social_logins);
		} else {
			$social_logins = '';
		}
		SiteSettings::where(['name' => 'social_logins'])->update(['value' => $social_logins]);

		if($request->has('otp_verification')) {
			$otp_verification = $request->otp_verification;
		} else {
			$otp_verification = '0';
		}

		if($request->has('demo_mode')) {
			$demo_mode = $request->demo_mode;
		} else {
			$demo_mode = '0';
		}
			//Default Sms
		if($request->has('default_otp')) {
			$default_otp = $request->default_otp;
		} else {
			$default_otp = 'firebase';
		}

		SiteSettings::where(['name' => 'default_otp'])->update(['value' => $default_otp]);
		SiteSettings::where(['name' => 'otp_verification'])->update(['value' => $otp_verification]);
		SiteSettings::updateOrCreate(['name' => 'demo_mode'], ['value' => $demo_mode]);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_enabled'], ['value' => $request->has('company_onboarding_enabled') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_require_phone_verification'], ['value' => $request->has('company_onboarding_require_phone_verification') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_require_email_verification'], ['value' => $request->has('company_onboarding_require_email_verification') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_require_logo_upload'], ['value' => $request->has('company_onboarding_require_logo_upload') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_require_documents_upload'], ['value' => $request->has('company_onboarding_require_documents_upload') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'company_onboarding_require_admin_approval'], ['value' => $request->has('company_onboarding_require_admin_approval') ? '1' : '0']);
        SiteSettings::updateOrCreate(['name' => 'driver_profile_image_auto_approval'], ['value' => $request->has('driver_profile_image_auto_approval') ? '1' : '0']);
		SiteSettings::where(['name' => 'covid_enable'])->update(['value' => $request->covid_enable ?? 0]);

		// Platform-wide payment gateway ownership mode.
		// Allowed values: admin | company.  Defaults to admin so that
		// pre-existing deployments see no behavioural change.
		$walletPaymentOwner = $request->wallet_payment_owner === 'company' ? 'company' : 'admin';
		SiteSettings::updateOrCreate(['name' => 'wallet_payment_owner'], ['value' => $walletPaymentOwner]);

		// Platform-wide user level (driver / customer) ownership mode.
		// Allowed values: admin | company.  When `company` SaaS tenants
		// can adopt the admin-configured levels and override their
		// values.  Defaults to `admin` so existing deployments are
		// unaffected.
		$userLevelOwner = $request->user_level_owner === 'company' ? 'company' : 'admin';
		SiteSettings::updateOrCreate(['name' => 'user_level_owner'], ['value' => $userLevelOwner]);

		flashMessage('success', 'Updated Successfully');
		return redirect('admin/site_setting');
	}
}
