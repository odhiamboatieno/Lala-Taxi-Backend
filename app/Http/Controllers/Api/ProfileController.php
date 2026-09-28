<?php

/**
 * Profile Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Profile
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\Documents;
use App\Models\ProfilePicture;
use App\Models\RiderLocation;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\DriverOweAmount;
use App\Models\DriverLocation;
use App\Models\PaymentMethod;
use App\Models\ReferralUser;
use App\Models\MakeVehicle;
use App\Models\Country;
use App\Models\CarType;
use App\Models\FilterOption;
use App\Models\FilterObject;
use App\Services\DriverComplianceService;
use App\Services\DriverScore\DriverScoreService;
use JWTAuth;
use DB;
use Illuminate\Support\Facades\Schema;
use Validator;
use Lang;
use Modules\UserLevels\Services\UserLevelService;

class ProfileController extends Controller
{
	/**
	 * User Profile photo upload
	 * @param  Post method request inputs
	 *
	 * @return Response Json
	 */
	public function upload_profile_image(Request $request)
	{
		$rules = [
            'image' => 'required|mimes:jpg,jpeg,png,gif',
        ];

        $validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$user_details = JWTAuth::parseToken()->authenticate();

		$image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
        $target_dir = '/images/users/'.$user_details->id;

		if(!$request->hasFile('image')) {
			return response()->json([
				'status_code' 		=> "0",
				'status_message' 	=> "Invalid File",
			]);
		}

		$image = $request->file('image');

		$extension = $image->getClientOriginalExtension();
		$file_name = "profile_pic_".time().".".$extension;
		$compress_size = [
			["height" => 225, "width" => 225],
		];
        $options = compact('target_dir','file_name','compress_size');

        $upload_result = $image_uploader->upload($image,$options);
        if(!$upload_result['status']) {
            return response()->json([
				'status_code' 		=> "0",
				'status_message' 	=> $upload_result['status_message'],
			]);
        }

        $imageUrl = asset($target_dir.'/'.$upload_result['file_name']);
        $autoApprove = site_settings('driver_profile_image_auto_approval') !== '0';
        $profilePicture = ProfilePicture::firstOrNew(['user_id' => $user_details->id]);
        $profilePicture->photo_source = $profilePicture->photo_source ?: 'Local';

        if ($autoApprove) {
        	$profilePicture->src = $imageUrl;
        	$profilePicture->pending_src = null;
        	$profilePicture->approval_status = 'approved';
        	$profilePicture->submitted_at = now();
        	$profilePicture->reviewed_at = now();
        	$profilePicture->reviewed_by = null;
        	$profilePicture->rejection_reason = null;
        }
        else {
        	$profilePicture->pending_src = $imageUrl;
        	$profilePicture->approval_status = 'pending';
        	$profilePicture->submitted_at = now();
        	$profilePicture->reviewed_at = null;
        	$profilePicture->reviewed_by = null;
        	$profilePicture->rejection_reason = null;
        }

        $profilePicture->save();

		return response()->json([
			'status_code' 		=> "1",
			'status_message' 	=> $autoApprove ? "Profile image updated successfully" : "Profile image uploaded and sent for approval",
			'image_url' 		=> $imageUrl,
			'profile_image' 	=> $profilePicture->src,
			'profile_src' 		=> $profilePicture->src,
			'pending_profile_image' => $profilePicture->pending_src_url ?? '',
			'approval_status' 	=> $profilePicture->approval_status ?: ($autoApprove ? 'approved' : 'pending'),
		]);
	}

	/**
	 * Display the vehicle details
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function updateVehicleDetails(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		
		if($request->action == 'active') {
			$vehicle_ids = explode(',', $request->id);
			Vehicle::where('user_id',$user_details->id)->update(['is_active' => 0]);
			Vehicle::where('user_id',$user_details->id)->whereIn('id',$vehicle_ids)->update(['is_active' => 0]);
		}

		$user_details->load('vehicles');
		$vehicles = $user_details->vehicles;
		$vehicle_list = $vehicles->map(function($vehicle) {
			return [
				"id" => $vehicle->id,
				"vehicle_id" => $vehicle->vehicle_id,
				"vehicle_type" => $vehicle->vehicle_type,
				"vehicle_name" => $vehicle->vehicle_name,
				"vehicle_number" => $vehicle->vehicle_number,
				"is_active" => (bool)$vehicle->is_active,
				"status" => $vehicle->status,
			];
		});
		return response()->json([
			'status_code'	=> "1",
			'status_message'=> \Lang::get('messages.api.listed_successfully'),
			'data' 			=> $vehicle_list,
		]);
	}
	
	/**
	 * Display the vehicle details
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function vehicleDetails(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user_id = $user_details->id;

		$rules = [
			'vehicle_id' => 'required',
			'vehicle_name' => 'required',
			'vehicle_type' => 'required',
			'vehicle_number' => 'required',
		];

		$messages = [
			'vehicle_id.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_type.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_number.required' => ':attribute ' . trans('messages.field_is_required') . '',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}
		$data = [
			'user_id' => $user_id,
			'vehicle_id' => $request->vehicle_id,
			'vehicle_name' => urldecode($request->vehicle_name),
			'vehicle_type' => $request->vehicle_type,
			'vehicle_number' => urldecode($request->vehicle_number),
		];

		Vehicle::updateOrCreate(['user_id' => $user_id], $data);
		User::where('id', $user_details->id)->update(['status' => 'Document_details']);

		return response()->json([
			'status_code' => "1",
			'status_message' => trans('messages.update_success'),
		]);
	}

	/**
	 * Display the Rider profile details & get the trip information while app closed
	 * 
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function get_rider_profile(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$invoice_helper = resolve(\App\Http\Helper\InvoiceHelper::class);

		$user_data = collect($user_details)->only(['first_name','last_name','mobile_number','country_code']);
		$user_details->load('rider_location','profile_picture');
		
		$location_data = collect($user_details->rider_location)->only('home','work','home_latitude','home_longitude','work_latitude','work_longitude','latitude','longitude');

		$user_data['email_id'] 		= $user_details->email;
		$user_data['profile_image'] = $user_details->profile_picture->src ?? url('images/user.jpeg');
		$user_data['currency_code'] = $user_details->currency->code;
		$user_data['country_code'] 	= isset($user_details->country->short_name) ? $user_details->country->short_name:$user_details->country_code;
		$user_data['gender'] 		= $user_details->gender_text;
		$user_data['currency_symbol'] = html_entity_decode($user_details->currency->original_symbol);
		$user_data = $user_data->merge($location_data);

		$wallet_amount = getUserWalletAmount($user_details->id);
		$promo_details = $invoice_helper->getUserPromoDetails($user_details->id);

		$user_data['wallet_amount'] = $wallet_amount;
		$user_data['promo_details'] = $promo_details;

		// save filter options
		if($request->has('options')) {
			$options = explode(',', $request->options);
			$filter_insert = FilterObject::optionsInsert('rider',$user_details->id,$options);
		}
	
		// get filter options
		$female_riders = FilterObject::exist('rider',$user_details->id,4) ? true:false;
		$handicap = FilterObject::exist('rider',$user_details->id,2) ? true:false;
		$child_seat = FilterObject::exist('rider',$user_details->id,3) ? true:false;
		$skip = $user_details->gender=='1' ? true:false;
		$request_options = FilterOption::options($skip,$female_riders,$handicap,$child_seat,'rider');
		$user_data['request_options'] = $request_options;
		$user_data['updated_at'] = date('Y-m-d H:i:s',strtotime($user_details->updated_at));

		return response()->json(array_merge([
			'status_code' 		=> '1',
			'status_message' 	=> trans('messages.success'),
		],$user_data->toArray()));
	}

	/**
	 * Update the location of Rider
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function update_rider_location(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		if ($request->home) {
			$rules = [
				'home' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',
			];
			$location_type = 'home';
		}
		else {
			$rules = [
				'work' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',
			];
			$location_type = 'work';
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$user_check = User::where('id', $user_details->id)->first();

		if($user_check == '') {
			return response()->json([
				'status_code' 	 => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		if ($location_type == 'work') {
			$data = [
				'user_id' => $user_details->id,
				'work' => $request->work,
				'work_latitude' => $request->latitude,
				'work_longitude' => $request->longitude,
			];
		}
		else {
			$data = [
				'user_id' => $user_details->id,
				'home' => $request->home,
				'home_latitude' => $request->latitude,
				'home_longitude' => $request->longitude,
			];
		}

		RiderLocation::updateOrCreate(['user_id' => $user_details->id], $data);

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('messages.update_success'),
		]);
	}

	/**
	 * Update Rider Profile
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function update_rider_profile(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		// Every field is optional so a caller that only changes the profile
		// photo does not have to resend the whole profile.
		$rules = [
			'profile_image' => 'sometimes|required',
			'first_name' => 'sometimes|required|string',
			'last_name' => 'sometimes|required|string',
			'country_code' => 'sometimes|required',
			'mobile_number' => 'sometimes|required',
			'email_id' => 'sometimes|required|email',
		];

		$messages = [
			'first_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'last_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'mobile_number.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'country_code.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'email_id.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'profile_image.required' => ':attribute ' . trans('messages.field_is_required') . '',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status_code'     => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$updates = [];
		if ($request->filled('first_name')) {
			$updates['first_name'] = $request->first_name;
		}
		if ($request->filled('last_name')) {
			$updates['last_name'] = $request->last_name;
		}
		if ($request->filled('mobile_number')) {
			$updates['mobile_number'] = $request->mobile_number;
		}
		if ($request->filled('email_id')) {
			$updates['email'] = $request->email_id;
		}

		$country = null;
		if ($request->filled('country_code')) {
			$country = Country::whereShortName($request->country_code)->first();
			if (!$country) {
				$country = Country::where('phone_code', ltrim($request->country_code, '+'))->first();
			}
			if ($country) {
				$updates['country_code'] = $country->phone_code;
				$updates['country_id'] = $country->id;
			}
		}

		if (!empty($updates)) {
			User::where('id', $user_details->id)->update($updates);
		}

		if ($request->filled('profile_image')) {
			$image = html_entity_decode($request->profile_image);
			$profilePicture = ProfilePicture::firstOrNew(['user_id' => $user_details->id]);
			$profilePicture->photo_source = $profilePicture->photo_source ?: 'Local';
			$profilePicture->src = $image;
			$profilePicture->save();
		}

		$user = User::where('id', $user_details->id)->first();
		if (!$country) {
			$country = Country::where('id', $user->country_id)->first();
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('messages.update_success'),
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'mobile_number' => $user->mobile_number,
			'country_code' => $country->short_name ?? '',
			'email_id' => $user->email,
			'profile_image' => @$user->profile_picture->src ?? '',
			'home' => @$user->rider_location->home ?? '',
			'work' => @$user->rider_location->work ??'',
		]);
	}

	public function get_driver_profile(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		if ($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.invalid_credentials'),
			]);
		}
		$symbol = @Currency::where('code', $user->currency_code)->first()->symbol;

		$owe_amount = 0;
		$driver_owe = DriverOweAmount::where('user_id',$user_details->id)->first();
		if($driver_owe)
			$owe_amount = number_format($driver_owe->amount,2,'.','');

		$wallet_amount = getUserWalletAmount($user_details->id);

		$driver_referral_earning = ReferralUser::where('user_id',$user_details->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
		$driver_referral_earning = number_format(@$driver_referral_earning->sum('pending_amount'),2,'.','');
		$vehicle = $user->vehicle ?? $user->vehicles->first();
		$vehicles_detail = Vehicle::where('user_id',$user->id)->get();
		$driver_rating = getDriverRating($user->id);
		$rating_count = \App\Models\Rating::where('driver_id', $user->id)->where('rider_rating', '>', 0)->count();
		$vehicles_details = [];

		foreach($vehicles_detail as $key => $value) {
			$vehicles_details[$key]['id'] 			= $value->id;
			$vehicles_details[$key]['vehicle_name'] = $value->vehicle_name;
			$vehicles_details[$key]['make'] 		= $value->makeWithSelected;
			$vehicles_details[$key]['model'] 		= $value->modelWithSelected;
			$vehicles_details[$key]['license_number'] = $value->vehicle_number;
			$vehicles_details[$key]['year'] 		= $value->year;
			$vehicles_details[$key]['color'] 		= $value->color;
			$vehicles_details[$key]['vehicleImageURL'] = url('static/Driving-Licence.jpg');	
			$vehicles_details[$key]['status'] 		= trans('messages.driver_dashboard.'.$value->status);
			$vehicles_details[$key]['is_active'] 	= $value->is_active;
			$vehicles_details[$key]['is_default'] 	= $value->default_type;

			$vehicle_types = explode(',', $value->vehicle_id);
			$vehicles_details[$key]['vehicle_types'] = getVehicleType($vehicle_types);
			$vehicles_details[$key]['vechile_documents'] = UserDocuments('Vehicle',$user,$value->id);

			// get filter options
			$female_riders = FilterObject::exist('vehicle',$value->id,1) ? true:false;
			$handicap = FilterObject::exist('vehicle',$value->id,2) ? true:false;
			$child_seat = FilterObject::exist('vehicle',$value->id,3) ? true:false;
			$skip = $user_details->gender=='1' ? true:false;
			$request_options = FilterOption::options($skip,$female_riders,$handicap,$child_seat);

			$vehicles_details[$key]['request_options'] = $request_options;
		}

		if($user->status=="Active") {
			$status = 1;
		} else {
			$status = 0;
		}

        $accessPayload = $this->driverAccessPayload($user);
        $status = !empty($accessPayload['can_go_online']) ? 1 : 0;

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> 'Success',
			'first_name' 		=> $user->first_name,
			'last_name' 		=> $user->last_name,
			'mobile_number' 	=> $user->mobile_number,
			'country_code' 		=> isset($user->country->short_name) ? $user->country->short_name:$user->country_code,
			'gender' 			=> $user->gender_text,
			'email_id' 			=> $user->email,
			'status'			=> $status,
			'car_type' 			=> $user->car_type,
			'profile_image' 	=> @$user->profile_picture->src ?? '',
			'address_line1' 	=> @$user->driver_address->address_line1 ?? '',
			'address_line2' 	=> @$user->driver_address->address_line2 ?? '',
			'city' 				=> @$user->driver_address->city ?? '',
			'state'				=> @$user->driver_address->state ?? '',
			'postal_code' 		=> @$user->driver_address->postal_code ?? '',
			'vehicle_name' 		=> optional($vehicle)->vehicle_name ?? '',
			'vehicle_number' 	=> optional($vehicle)->vehicle_number ?? '',
			'currency_code' 	=> @$user->currency->code,
			'currency_symbol' 	=> html_entity_decode(@$user->currency->original_symbol),
			'car_image' 		=> optional($vehicle)->car_type ? optional($vehicle)->car_type->vehicle_image : '',
			'car_active_image' 	=> optional($vehicle)->car_type ? optional($vehicle)->car_type->active_image : '',
			'company_id' 		=> $user->company_id,
			'company_name' 		=> @$user->company->name,
			'owe_amount' 		=> $owe_amount,
			'wallet_amount' 	=> $wallet_amount,
			'driver_referral_earning' => $driver_referral_earning,
			'driver_rating' => $driver_rating,
			'rating_count' => $rating_count,
			'navigation_preference' => 'Google Maps',
			'notifications_enabled' => true,
			'driver_documents' 	=> UserDocuments('Driver',$user,0),
			'vehicle_details' 	=> $vehicles_details,
			'updated_at' 		=> date('Y-m-d H:i:s',strtotime($user->updated_at)),
            'driver_access'     => $accessPayload,
            'driver_access_state' => $accessPayload['driver_access_state'],
            'driver_access_message' => $accessPayload['driver_access_message'],
            'marketplace_access_status' => $accessPayload['marketplace_access_status'],
            'can_go_online' => $accessPayload['can_go_online'],
            'driver_level' => $accessPayload['driver_level'],
            'driver_level_metrics' => $accessPayload['driver_level_metrics'],
            'driver_score' => $accessPayload['driver_score'],
		]);
	}

    protected function driverAccessPayload(User $user): array
    {
        $compliance = resolve(DriverComplianceService::class)->compliancePayload($user);
        $companyId = ($user->company_id && (int) $user->company_id !== 1) ? (int) $user->company_id : null;
        $driverLevel = null;
        $driverLevelMetrics = [];

        if (Schema::hasTable('user_level_assignments') && Schema::hasTable('user_levels')) {
            try {
                $levelService = resolve(UserLevelService::class);
                $assignment = $levelService->assignmentOrDefault($user, 'driver', $companyId);
                $driverLevel = $assignment && $assignment->level ? [
                    'id' => $assignment->level->id,
                    'name' => $assignment->level->name,
                    'description' => $assignment->level->description,
                    'icon' => $assignment->level->icon,
                    'points' => (float) ($assignment->points ?? 0),
                    'assigned_at' => optional($assignment->assigned_at)->toIso8601String(),
                ] : null;
                $driverLevelMetrics = $levelService->metricsForUser($user, 'driver', $companyId);
            } catch (\Throwable $e) {
                $driverLevel = null;
                $driverLevelMetrics = [];
            }
        }

        $driverScore = null;
        $marketplaceAccessStatus = 'active';
        $marketplaceExplanation = '';

        if (Schema::hasTable('driver_scores')) {
            try {
                $scoreService = resolve(DriverScoreService::class);
                $score = $scoreService->recalculateDriver($user, ['trigger' => 'driver_profile_api']);
                if ($score) {
                    $driverScore = $scoreService->scoreSummaryPayload($score);
                    $marketplaceAccessStatus = $driverScore['marketplace_access_status'] ?? 'active';
                    $marketplaceExplanation = data_get($driverScore, 'marketplace_status.explanation', '');
                }
            } catch (\Throwable $e) {
                $driverScore = null;
            }
        }

        $legacyStatus = strtolower((string) $user->status);
        $driverAccessState = 'active';
        $driverAccessMessage = trans('messages.user.active_message');

        if ($legacyStatus === 'suspended') {
            $driverAccessState = 'suspended';
            $driverAccessMessage = 'Your driver account is suspended. Contact support or submit an appeal for review.';
        } elseif (!($compliance['is_compliant'] ?? false)) {
            $driverAccessState = 'review_required';
            $driverAccessMessage = data_get($compliance, 'issues.0.message') ?: 'Your documents must be updated before you can go online.';
        } elseif ($marketplaceAccessStatus === 'suspended') {
            $driverAccessState = 'suspended';
            $driverAccessMessage = $marketplaceExplanation ?: 'Your driver marketplace access is suspended right now.';
        } elseif ($marketplaceAccessStatus === 'review_required') {
            $marketplaceAccessStatus = 'limited';
            $marketplaceExplanation = $marketplaceExplanation ?: 'Marketplace access is under review, but verified active drivers can continue operating.';
        } elseif ($legacyStatus !== 'active') {
            $driverAccessState = 'approval_required';
            $driverAccessMessage = trans('messages.user.pending_message');
        }

        return [
            'driver_access_state' => $driverAccessState,
            'driver_access_message' => $driverAccessMessage,
            'marketplace_access_status' => $marketplaceAccessStatus,
            'marketplace_explanation' => $marketplaceExplanation,
            'can_go_online' => $driverAccessState === 'active',
            'is_suspended' => $driverAccessState === 'suspended',
            'requires_manual_review' => $driverAccessState === 'review_required',
            'document_compliance' => $compliance,
            'driver_level' => $driverLevel,
            'driver_level_metrics' => $driverLevelMetrics,
            'driver_score' => $driverScore,
        ];
    }

	/**
	 * Update Driver  Profile
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function update_driver_profile(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'first_name' => 'required',
			'last_name' => 'required',
			'mobile_number' => 'required',
			'country_code' => 'required',
			'email_id' => 'required',
			'profile_image' => 'required',
			'address_line1' => 'required',
			'address_line2' => 'required',
			'city' => 'required',
			'state' => 'required',
			'postal_code' => 'required',
		];

		$messages = [
			'first_name.required' => trans('messages.first_name_required'),
			'last_name.required' => trans('messages.last_name_required'),
			'mobile_number.required' => trans('messages.mobile_num_required'),
			'country_code.required' => trans('messages.country_code_required'),
			'email_id.required' => trans('messages.email_id_required'),
			'profile_image.required' => trans('messages.profile_image_required'),
			'address_line1.required' => trans('messages.address_line1_required'),
			'address_line2.required' => trans('messages.address_line2_required'),
			'city.required' => trans('messages.city_required'),
			'state.required' => trans('messages.state_required'),
			'postal_code.required' => trans('messages.postal_code_required'),
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$country = Country::whereShortName($request->country_code)->first();
		$country_code = $country->phone_code;
		$country_id = $country->id;

		User::where('id', $user_details->id)->update([
			'first_name' 	=> $request->first_name,
			'last_name' 	=> $request->last_name,
			'mobile_number' => $request->mobile_number,
			'country_code' 	=> $country_code,
			'country_id' 	=> $country_id,
			'email' 		=> $request->email_id,
		]);

		DriverAddress::where('user_id', $user_details->id)->update([
			'address_line1' => $request->address_line1,
			'address_line2' => $request->address_line2,
			'city' 			=> $request->city,
			'state' 		=> $request->state,
			'postal_code' 	=> $request->postal_code,
		]);

		if ($request->profile_image) {
			$profilePicture = ProfilePicture::firstOrNew(['user_id' => $user_details->id]);
			$profilePicture->photo_source = $profilePicture->photo_source ?: 'Local';
			if ((string) ($profilePicture->approval_status ?: 'approved') === 'approved') {
				$profilePicture->src = $request->profile_image;
			}
			$profilePicture->save();
		}

		$user = User::where('id', $user_details->id)->first();
		$vehicle = $user->vehicle ?? $user->vehicles->first();

		$driver_owe = DriverOweAmount::where('user_id',$user_details->id)->first();
		$owe_amount = number_format($driver_owe->amount,2,'.','');

		$driver_referral_earning = ReferralUser::where('user_id',$user_details->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
		$driver_referral_earning = number_format(@$driver_referral_earning->sum('pending_amount'),2,'.','');

		$driver_doc = DriverDocuments::where('user_id',$user->id)->get();
		$driverArr = [];
		if($driver_doc->count() > 0){
			foreach ($driver_doc as $key => $value) {
				$driverArr[$key]['id'] = $value->document_id;
				$driverArr[$key]['name'] = $value->doc_name;
				$driverArr[$key]['document'] = $value->document;
				$driverArr[$key]['status'] = '1';					
			}
		}else{
			$data = Documents::Active()->DocumentCheck('Driver',$user->country_code)->get();
	        if($data->count() == 0){
	            $data = Documents::Active()->DocumentCheck('Driver','all')->get();
	        }
	        foreach ($data as $key => $value) {
	        	$driverArr[$key]['id'] = $value->id;
				$driverArr[$key]['name'] = $value->document_name;
				$driverArr[$key]['document'] = '';
				$driverArr[$key]['status'] = '0';					
			}
		}

		$vehicles_detail = Vehicle::where('user_id',$user->id)->get();
		$vehicles_details = [];
		foreach($vehicles_detail as $key => $value) {
			$vehicles_details[$key]['id'] 			= $value->id;
			$vehicles_details[$key]['vehicle_name'] = $value->vehicle_name;
			$vehicles_details[$key]['make'] 		= $value->makeWithSelected;
			$vehicles_details[$key]['model'] 		= $value->modelWithSelected;
			$vehicles_details[$key]['license_number'] = $value->vehicle_number;
			$vehicles_details[$key]['year'] 		= $value->year;
			$vehicles_details[$key]['color'] 		= $value->color;
			$vehicles_details[$key]['vehicleImageURL'] = url('static/Driving-Licence.jpg');	
			$vehicles_details[$key]['status'] 		= $value->status;
			//$vehicles_details[$key]['is_active'] 	= $value->is_active;
			$vehicles_details[$key]['is_default'] 	= $value->default_type;

			$vehicle_types = explode(',', $value->vehicle_id);
			$vehicles_details[$key]['vehicle_types'] = getVehicleType($vehicle_types);	
		}

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> 'Success',
			'first_name' 		=> $user->first_name,
			'last_name' 		=> $user->last_name,
			'mobile_number' 	=> $user->mobile_number,
			'country_code' 		=> isset($user->country->short_name) ? $user->country->short_name:$user->country_code,
			'email_id' 			=> $user->email,
			//'car_type' 			=> $user->car_type,
			'profile_image' 	=> @$user->profile_picture->src ?? '',
			'pending_profile_image' => optional($user->profile_picture)->pending_src_url ?? '',
			'profile_image_approval_status' => optional($user->profile_picture)->approval_status ?: 'approved',
			'address_line1' 	=> @$user->driver_address->address_line1 ?? '',
			'address_line2' 	=> @$user->driver_address->address_line2 ?? '',
			'city' 				=> @$user->driver_address->city ?? '',
			'state'				=> @$user->driver_address->state ?? '',
			'postal_code' 		=> @$user->driver_address->postal_code ?? '',
			'vehicle_name' 		=> optional($vehicle)->vehicle_name ?? '',
			'vehicle_number' 	=> optional($vehicle)->vehicle_number ?? '',
			'currency_code' 	=> @$user->currency->code,
			'currency_symbol' 	=> html_entity_decode(@$user->currency->original_symbol),
			//'car_image' 		=> optional($vehicle)->car_type ? optional($vehicle)->car_type->vehicle_image : '',
			'car_active_image' 	=> optional($vehicle)->car_type ? optional($vehicle)->car_type->active_image : '',
			'company_id' 		=> $user->company_id,
			'company_name' 		=> @$user->company->name,
			'owe_amount' 		=> $owe_amount,
			'driver_referral_earning' => $driver_referral_earning,
			'driver_documents' 	=> $driverArr,
			'vehicle_details' 	=> $vehicles_details,
		]);
	}

	/**
	 * To update the currency code for the user
	 * @param  Request $request Get values
	 * @return Response Json
	 */
	public function update_user_currency(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'currency_code' => 'required|exists:currency,code',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		User::where('id', $user_details->id)->update(['currency_code' => $request->currency_code]);

		$wallet_amount = getUserWalletAmount($user_details->id);

		return response()->json([
			'status_message' => trans('messages.update_success'),
			'status_code' => '1',
			'wallet_amount' => $wallet_amount,
		]);
	}

	public function get_caller_detail(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_id' => 'required|exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return [
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ];
        }

        $user = User::find($request->user_id);

		if($request->send_push_notification) {
			$request_helper = resolve(\App\Http\Helper\RequestHelper::class);

			$push_title = $user->first_name." Calling";
			$push_data['push_title'] = $push_title;
	        $push_data['data'] = [
	            'user_calling' => [
	                'user_id' => $user->id,
	                'title' => $push_title,
	            ]
	        ];

			if ($user->device_type != null && $user->device_type != '') {
				$request_helper->checkAndSendMessage($user,'',$push_data);
			}
		}

		return response()->json([
			'status_code' 	=> '1',
			'status_message'=> __('messages.api.listed_successfully'),
			'first_name' 	=> $user->first_name,
			'last_name' 	=> $user->last_name,
			'profile_image' => optional($user->profile_picture)->src ?? url('images/user.jpeg'),
		]);
	}

	
	public function vehicleDescriptions(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$make = MakeVehicle::getMakeModel()->map(function($value) {

			$data['id'] = $value->id;
			$data['name'] = $value->make_vehicle_name;

			foreach($value->vehicle_model as $key=>$model) {
				$data['model'][$key]['id'] = $model->id;
				$data['model'][$key]['name'] = $model->model_name;
			}

			return $data;
		});

		$vehicle_types = CarType::select('car_name as type','id','is_pool')->active()->get();
		$vehicle_type = [];

		foreach($vehicle_types as $key=>$value) {
			$vehicle_type[$key]['id'] = $value->id;
			$vehicle_type[$key]['type'] = $value->type;
			$vehicle_type[$key]['isPooled'] = getPoolValue($value->is_pool);
			$vehicle_type[$key]['location'] = getVehicleLocation($value->id);
		}

		// get filter options
		$vehicle_id = isset($user_details->vehicle) ?? $user_details->vehicle->id;

		$female_riders = $handicap = $child_seat = false;
		if($vehicle_id) {
			$female_riders = FilterObject::exist('vehicle',$vehicle_id,1) ? true:false;
			$handicap = FilterObject::exist('vehicle',$vehicle_id,2) ? true:false;
			$child_seat = FilterObject::exist('vehicle',$vehicle_id,3) ? true:false;
		}
		$skip = $user_details->gender=='1' ? true:false;
		$request_options = FilterOption::options($skip,$female_riders,$handicap,$child_seat);

		return response()->json([
			'status_code' 	=> '1',
			'status_message'=> __('messages.api.listed_successfully'),
			'year' => '1990',
			'make' => $make,
			'vehicle_types' => $vehicle_type,
			'request_options' => $request_options,
		]);
	}
}
