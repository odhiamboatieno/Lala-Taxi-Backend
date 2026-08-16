<?php

/**
 * Driver Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Driver
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\DriverLocation;
use App\Models\Company;
use App\Models\Payment;
use App\Models\DriverOweAmountPayment;
use App\Models\DriverOweAmount;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\PaymentMethod;
use App\Models\Trips;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\Country;
use App\Models\BankDetail;
use App\Models\AppliedReferrals;
use App\Models\ReferralUser;
use App\Models\Fees;
use App\Models\MakeVehicle;
use App\Models\PoolTrip;
use App\Models\VehicleModel;
use Auth;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use File;
use App\Http\Helper\InvoiceHelper;
use App\Models\Vehicle;
use App\Models\CarType;
use App\Models\DriverDocuments;
use App\Models\Documents;
use App\Models\FilterOption;
use App\Models\FilterObject;
use App\Services\DriverComplianceService;
use App\Services\DriverScore\DriverScoreService;
use App\Services\Notifications\NotificationManager;
use App\Services\Wallet\CashTripFeeLedgerService;
use Illuminate\Support\Facades\Schema;
use Modules\UserLevels\Services\UserLevelService;

class DriverController extends Controller
{
	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request,InvoiceHelper $invoice_helper)
	{
		$this->request_helper = $request;
		$this->helper = new Helpers;
		$this->invoice_helper = $invoice_helper;
	}

	/**
	 * Update Location of Driver & calculate the trip distance while trip
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */

	public function updateLocation(Request $request)
	{
	 	$user_details = JWTAuth::parseToken()->authenticate();
	 	
	 	$getCompanyStatus=Company::where('id',$user_details->company_id)->first();
	 	
	 	if($getCompanyStatus && $getCompanyStatus->status!='Active'){
	 		return redirect('api/logout');;
	 	}

	 	$rules = [
			'latitude' 	=> 'required',
			'longitude' => 'required',
			'user_type' => 'required|in:Driver,driver',
			'status' 	=> 'required|in:Online,Offline,online,offline,Trip,trip',
		];

		if ($request->trip_id) {
			$rules['trip_id'] = 'required|exists:trips,id';
			$rules['total_km'] = 'required';
		}

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
        }

		$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

		if ($user == '') {
			return response()->json([
				'status_code'	 => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		if (strtolower((string) $request->status) == 'online') {
			$compliance = resolve(DriverComplianceService::class)->compliancePayload($user);
			if (!($compliance['is_compliant'] ?? false)) {
				return response()->json([
					'status_code' => '0',
					'status_message' => data_get($compliance, 'issues.0.message') ?: 'Your account cannot go online until your documents are approved.',
					'driver_access' => [
						'driver_access_state' => 'review_required',
						'driver_access_message' => data_get($compliance, 'issues.0.message') ?: 'Your account cannot go online until your documents are approved.',
						'can_go_online' => false,
						'document_compliance' => $compliance,
					],
				]);
			}
		}
		$driver_location = DriverLocation::where('user_id', $user_details->id)->first();
		$normalizedStatus = ucfirst(strtolower((string) $request->status));
		$isOffline = strtolower((string) $request->status) === 'offline';
		$isTripLocation = strtolower((string) $request->status) === 'trip' || $request->filled('trip_id');

		if ($request->trip_id) {

			$old_km = Trips::where('id', $request->trip_id)->first()->total_km;
			$user_id = Trips::where('id', $request->trip_id)->first()->user_id;

			$user_rider = User::where('id', $user_id)->first();

			$device_type = $user_rider->device_type;

			$device_id = $user_rider->device_id;
			$user_type = $user_rider->user_type;
			$push_title = "Live Tracking";
			$data = ['live_tracking' => ['trip_id' => $request->trip_id, 'driver_latitude' => @$request->latitude, 'driver_longitude' => @$request->longitude]];
			

			if ($user->device_type == 3) {
				$old_latitude = $driver_location->latitude;
				$old_longitude = $driver_location->longitude;

				$earthRadius = 6371000;
				$latFrom = deg2rad($old_latitude);
				$lonFrom = deg2rad($old_longitude);
				$latTo = deg2rad($request->latitude);
				$lonTo = deg2rad($request->longitude);

				$latDelta = $latTo - $latFrom;
				$lonDelta = $lonTo - $lonFrom;

				$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

				$meter = number_format((($angle * $earthRadius)), 2);

				$km = (($meter) / 1000);

			} else {
				$km = $request->total_km;
			}

			$new_km = $old_km + $km;
			 
			/* json file */
			$trip_id = $request->trip_id;		

			$file = $trip_id. '_file.json';
			$destinationPath = public_path()."/trip_file/";

			if(!is_dir($destinationPath)) { 
			  mkdir($destinationPath,0777,true);  
			}

			$old_path = base_path('public/trip_file/'.$trip_id.'_file.json');

			if(file_exists($old_path)) {
				$jsonString = file_get_contents($old_path);
				$datas = json_decode($jsonString, true);
			}

			$datas[] = [
				'latitude' => $request->latitude,
				'longitude'=>$request->longitude,
				'current_km' =>  $km,
				'old_km'=>$old_km,
				'new_km'=> (string)$new_km,
				'time' => date('H:i:s')
			];

			$data = json_encode($datas ,JSON_PRETTY_PRINT);
			File::put($destinationPath.$file,$data);
			/* json file */

			Trips::where('id', $request->trip_id)->update(['total_km' => $new_km]);

			$this->touchDriverPresence($user, $request, 'Trip', false);

			return response()->json([
				'status_code' => '1',
				'status_message' => "updated successfully",
			]);
		}
		if ($driver_location != '' && in_array($driver_location->status,['Trip','Pool Trip'])) {
			return response()->json([
				'status_code' => '2',
				'status_message' => trans('messages.please_complete_your_current_trip'),
			]);
		}

		$data['user_id'] = $user_details->id;
		$data['latitude'] = $request->latitude;
		$data['longitude'] = $request->longitude;
		
		if($driver_location && $driver_location->status == "Pool Trip") {
			$data['pool_trip_id'] = $driver_location->pool_trip_id;
			$data['status'] = "Pool Trip";
		} else if (in_array($normalizedStatus, ['Online', 'Offline'], true)) {
			$data['status'] = $normalizedStatus;
		}

		$user->forceFill([
			'current_lat' => (float) $request->latitude,
			'current_lng' => (float) $request->longitude,
			'last_seen_at' => now(),
			'is_online' => ! $isOffline,
			'is_available' => ! $isTripLocation && $normalizedStatus === 'Online',
			'online_started_at' => ! $isOffline
				? ($user->online_started_at ?: now())
				: null,
		])->save();

		$onlineSessionService = resolve(\App\Services\DriverOnlineSessionService::class);
		if (! $isOffline) {
			$onlineSessionService->ensureOpenSession($user, [
				'lat' => (float) $request->latitude,
				'lng' => (float) $request->longitude,
				'device_id' => $request->device_id,
				'app_version' => $request->app_version,
			]);
		}
		if (strtolower($request->status) == 'offline') {
			$onlineSessionService->endSession($user, [
				'lat' => (float) $request->latitude,
				'lng' => (float) $request->longitude,
			]);
		}

		$vehicle_list = [];
		if(isset($user_details->vehicle->vehicle_id)) {
			$vehicle_list = $user_details->vehicle->vehicle_id;
			$vehicle_list = explode(',', $vehicle_list);

			foreach($vehicle_list as $vehicle) {
				$data['car_id'] = $vehicle;
				DriverLocation::updateOrCreate(['user_id' => $user_details->id, 'car_id' => $vehicle], $data);
			}
		}

		DriverLocation::where('user_id',$user_details->id)->whereNotIn('car_id',$vehicle_list)->delete();

		return response()->json([
			'status_code' => '1',
			'status_message' => "updated successfully",
		]);
	}

	protected function touchDriverPresence(User $user, Request $request, string $locationStatus, bool $available): void
	{
		$user->forceFill([
			'current_lat' => (float) $request->latitude,
			'current_lng' => (float) $request->longitude,
			'last_seen_at' => now(),
			'is_online' => strtolower((string) $request->status) !== 'offline',
			'is_available' => $available,
			'online_started_at' => strtolower((string) $request->status) !== 'offline'
				? ($user->online_started_at ?: now())
				: null,
		])->save();

		resolve(\App\Services\DriverOnlineSessionService::class)->ensureOpenSession($user, [
			'lat' => (float) $request->latitude,
			'lng' => (float) $request->longitude,
			'device_id' => $request->device_id,
			'app_version' => $request->app_version,
		]);

		$locationData = [
			'user_id' => $user->id,
			'latitude' => $request->latitude,
			'longitude' => $request->longitude,
			'status' => $locationStatus,
		];

		$vehicleList = [];
		if (isset($user->vehicle->vehicle_id)) {
			$vehicleList = array_filter(explode(',', $user->vehicle->vehicle_id));
		}

		if ($vehicleList === []) {
			DriverLocation::updateOrCreate(['user_id' => $user->id], $locationData);
			return;
		}

		foreach ($vehicleList as $vehicle) {
			$locationData['car_id'] = $vehicle;
			DriverLocation::updateOrCreate(['user_id' => $user->id, 'car_id' => $vehicle], $locationData);
		}
	}

	/**
	 * Check the Document status from driver side
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function checkStatus(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver,Rider,rider',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return [
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ];
        }

		$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

		if($user == '') {
			return response()->json([
				'status_code' 		=> '0',
				'status_message'	=> trans('messages.api.invalid_credentials'),
			]);
		}

		if($user->status=="Active") {

			$vehicle_documents = $user->driver_documents('Vehicle')->count();
			$driver_documents = $user->driver_documents('Driver')->count();

			if(!$user->vehicles->count()){
				$user->status = "Car_details";
				$message = trans('messages.user.car_details_message1');
			} elseif(!$vehicle_documents) {
				$user->status = "Car_details";
				$message = trans('messages.user.car_details_message2');
			} elseif(!$driver_documents) {
				$user->status = "Document_details";
				$message = trans('messages.user.document_details_message');
			} else {
				$message = trans('messages.user.active_message');
			}

		} else if($user->status=="Pending") {
			$message = trans('messages.user.pending_message');
		} else if($user->status=="Document_details") {
			$message = trans('messages.user.document_details_message');
		} else if($user->status=="Car_details") {
			if(!$user->vehicles->count())
				$message = trans('messages.user.car_details_message1');
			else
				$message = trans('messages.user.car_details_message2');
		} else {
			$message = trans('messages.user.inactive_message');
		}

        $accessPayload = $this->driverAccessPayload($user, $message);
		$status = !empty($accessPayload['can_go_online']) ? 1 : 0;

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> trans('messages.success'),
			'driver_status' 	=> $status,
			'driver_status_message' => $accessPayload['driver_access_message'] ?: $message,
            'driver_access'     => $accessPayload,
            'driver_access_state' => $accessPayload['driver_access_state'],
            'marketplace_access_status' => $accessPayload['marketplace_access_status'],
            'can_go_online' => $accessPayload['can_go_online'],
		]);
	}

    protected function driverAccessPayload(User $user, ?string $approvalMessage = null): array
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
                $score = $scoreService->recalculateDriver($user, ['trigger' => 'driver_status_api']);
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
            $driverAccessMessage = $approvalMessage ?: trans('messages.user.pending_message');
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

	public function cash_collected(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$rules = [
			'trip_id' => 'required|exists:trips,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
        }
        
		$trip = Trips::where('id', $request->trip_id)->first();

		if ($trip->status != 'Payment') {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.api.something_went_wrong'),
			]);
		}
		if ($trip->is_calculation == 0) {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $user_details->id,
				'save_to_trip_table' => 1,
			];
			$this->invoice_helper->calculation($data);
	 		$trip = Trips::where('id', $request->trip_id)->first();
		}

		$trip_save = Trips::where('id', $request->trip_id)->first();
		$trip_save->status = 'Completed';
		$trip_save->paykey = @$request->paykey;
		$trip_save->payment_status = 'Completed';
		if (Schema::hasColumn('trips', 'cash_collected')) {
			$trip_save->cash_collected = true;
		}
		if (Schema::hasColumn('trips', 'driver_confirmed_cash')) {
			$trip_save->driver_confirmed_cash = true;
		}
		$trip_save->save();

		if($trip->pool_id>0) {

			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])->count();
			
			if(!$trips) {
				// update status
				$pool_trip->status = 'Completed';
				$pool_trip->save();
			}
		}

		$data = [
			'trip_id' => $request->trip_id,
			'correlation_id' => @$request->paykey,
			'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
		];

		Payment::updateOrCreate(['trip_id' => $request->trip_id], $data);
		$debitedCashFees = resolve(CashTripFeeLedgerService::class)->debitDriverFees($trip_save->fresh());
		$rider = User::where('id', $trip->user_id)->first();
		$driver_thumb_image = @$trip->driver_thumb_image != '' ? $trip->driver_thumb_image : url('images/user.jpeg');

		$push_data['push_title'] = __('messages.dashboard.cash_collect');
		$push_data['data'] = [
			'trip_payment' => [
				'status' 	=> __('messages.dashboard.cash_collect'),
				'trip_id' 	=> $request->trip_id,
				'driver_thumb_image' => $driver_thumb_image
			]
		];
        resolve(NotificationManager::class)->sendEvent('regular.customer.payment_successful', $rider, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ], $push_data['data']);

		$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);
		if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {

			$push_title = __('messages.trip_cash_collected');
	        $text 		= __('messages.api.trip_total_fare',['total_fare'=> $trip->total_fare,'currency' => $trip->currency_code]);

	        $push_data['push_title'] = $push_title;
	        $push_data['data'] = [
	            'custom_message' => [
	                'title' => $push_title,
	                'message_data' => $text,
	            ]
	        ];

	        $text = $push_title.$text;

	        $this->request_helper->checkAndSendMessage($rider,$text,$push_data);
		}

		$invoice_helper = resolve(\App\Http\Helper\InvoiceHelper::class);
        $promo_details = $invoice_helper->getUserPromoDetails($trip->user_id);

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> "Cash Collected Successfully",
			'trip_id' 			=> $trip->id,
			'promo_details' 	=> $promo_details,
			'rider_thumb_image' => $trip->rider_thumb_image,
			'driver_wallet_fee_debit' => number_format($debitedCashFees, 2, '.', ''),
		]);
	}

	/**
	 * Display Country List
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function country_list(Request $request)
	{
		$data = Country::select(
			'id as country_id',
			'long_name as country_name',
			'short_name as country_code'
		)->get();

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Country Listed Successfully',
			'country_list' => $data,
		]);
	}

    /**
	 * Driver Bank Details if company is private
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function driver_bank_details(Request $request)
	{
		$user = JWTAuth::toUser($request->token);

		if(!$request) {
			$bank_detail = BankDetail::where('user_id',$user->id)->first();
			if(isset($bank_detail)) {
				$bank_detail = (object)[];
			}
		}
		else {
			$rules = [
    			'account_holder_name' => 'required',
                'account_number' => 'required',
                'bank_name' => 'required',
                'bank_location' => 'required',
                'bank_code' => 'required',
            ];

            $attributes = [
                'account_holder_name'  => trans('messages.account.holder_name'),
                'account_number'  => trans('messages.account.account_number'),
                'bank_name'  => trans('messages.account.bank_name'),
                'bank_location'  => trans('messages.account.bank_location'),
                'bank_code'  => trans('messages.account.bank_code'),
            ];

    		$messages   = ['required'=> ':attribute '.trans('messages.home.field_is_required').'',];
            $validator = Validator::make($request->all(), $rules,$messages,$attributes);
            
            if($validator->fails()) {
	            return response()->json([
	            	'status_code' => '0',
	            	'status_message' => $validator->messages()->first()
	            ]);
	        }

    		$bank_detail = BankDetail::firstOrNew(['user_id' => $user->id]);

            $bank_detail->user_id = $user->id;
            $bank_detail->holder_name = $request->account_holder_name;
            $bank_detail->account_number = $request->account_number;
            $bank_detail->bank_name = $request->bank_name;
            $bank_detail->bank_location = $request->bank_location;
            $bank_detail->code = $request->bank_code;
            $bank_detail->save();
		}
                
		return response()->json([
			'status_code' => '1',
			'status_message' => 'Listed Successfully',
			'bank_detail' =>  $bank_detail,
		]);
    }

	public function pay_to_admin(Request $request)
	{
		$user 	= JWTAuth::toUser($request->token);

		//validation started
		$rules = [
			'applied_referral_amount' => 'In:0,1',
            'amount'	=> 'numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
        }

		$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
		if ($owe_amount && $owe_amount->amount > 0) {
			//applying referral amount start
			if ($request->has('applied_referral_amount') && $request->applied_referral_amount==1) {

				$total_referral_amount = ReferralUser::where('user_id',$user->id)
					->where('payment_status','Completed')
					->where('pending_amount','>',0)
					->get()
					->sum('pending_amount');

				if ($owe_amount->amount < $total_referral_amount) {
					$total_referral_amount = $owe_amount->amount;
				}

				if ($total_referral_amount > 0) {
					$applied_referrals = new AppliedReferrals;
					$applied_referrals->amount = $total_referral_amount;
					$applied_referrals->user_id = $user->id;
					$applied_referrals->currency_code = $user->currency->code;
					$applied_referrals->save();

					$this->invoice_helper->referralUpdate($user->id,$total_referral_amount,$user->currency->code);

					//owe amount
					$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
					$currency_code = $owe_amount->currency_code;
					$owe_amount->amount = $owe_amount->amount - $total_referral_amount;
					$owe_amount->currency_code = $currency_code;
					$owe_amount->save();

					$payment = new DriverOweAmountPayment;
					$payment->user_id = $user->id;
					$payment->transaction_id = "";
					$payment->amount = $total_referral_amount;
					$payment->currency_code = $currency_code;
					$payment->status = 1;
					$payment->save();

					$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				}
			}
			//applying referral amount

			//pay to admin from payout preference start
			$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			if ($owe_amount->amount < $request->amount) {
				$request->amount = $owe_amount->amount;
			}
			$amount = $request->amount;

			if($request->has('amount') && $request->amount > 0) {
				if($owe_amount->amount < $request->amount) {
					return response()->json([
						'status_code' => '0',
						'status_message' => trans('messages.api.invalid'),
					]);
				}

				$rules = [
					'payment_type' 	=> 'required|in:paypal,stripe,braintree',
				];

				if($request->payment_type != "stripe") {
		            $rules['pay_key'] = 'required';
		        }

				$validator = Validator::make($request->all(), $rules);

				if($validator->fails()) {
		            return response()->json([
		                'status_code' => '0',
		                'status_message' => $validator->messages()->first()
		            ]);
		        }

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				$total_owe_amount = $owe_amount->amount;
				$currency_code = $owe_amount->currency_code;
				$remaining_amount = $total_owe_amount - $amount;

				$payment_data['currency_code'] = $user->currency_code;
				$payment_data['amount'] = $amount;
				$payment_data['user_id'] = $user->id;

				if($request->payment_type == 'stripe') {
					$payment_method = PaymentMethod::where('user_id', $user->id)->first();
					if($payment_method == '') {
						return response()->json([
			                'status_code' => '0',
			                'status_message' => __('messages.api.please_add_card_details'),
			            ]);
					}

					$payment_data = [
						"amount" 		=> $amount * 100,
						'currency' 		=> $user->currency_code,
						'description' 	=> 'Owe Payment By '.$user->first_name,
						"customer" 		=> $payment_method->customer_id,
						'payment_method'=> $payment_method->payment_method_id,
				      	'confirm' 		=> true,
				      	'off_session' 	=> true,
					];
				}

				try {
					$service = 'App\Services\Payments\\'.ucfirst($request->payment_type)."Payment";
					$payment_service = resolve($service);
					$pay_result = $payment_service->makePayment($payment_data,$request->pay_key);

					if(!$pay_result->status) {
						return response()->json([
			                'status_code' => '0',
			                'status_message' => $pay_result->status_message,
			            ]);
					}

					if($pay_result->is_two_step) {
						return response()->json([
			                'status_code' => '2',
			                'status_message' => $pay_result->status_message,
			                'two_step_id' => $pay_result->two_step_id,
			            ]);
					}
				}
				catch (\Exception $e) {
					return response()->json([
		                'status_code' => '0',
		                'status_message' => $e->getMessage(),
		            ]);
				}

				//owe amount
				$owe_amount->amount = $remaining_amount;
				$owe_amount->currency_code = $currency_code;
				$owe_amount->save();

				$payment = new DriverOweAmountPayment;
				$payment->user_id = $user->id;
				$payment->transaction_id = $pay_result->transaction_id;
				$payment->amount = $amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			}

			$referral_amount = ReferralUser::where('user_id',$user->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
			$referral_amount = number_format($referral_amount->sum('pending_amount'),2,'.','');

			return response()->json([
				'status_code' 	=> '1',
				'status_message'=> __('messages.api.payout_successfully'),
				'referral_amount' => $referral_amount,
				'owe_amount' 	=> $owe_amount->amount,
				'currency_code' => $owe_amount->currency_code
			]);
		}
		
		return response()->json([
			'status_code' => '0',
			'status_message' => __('messages.api.not_generate_amount'),
		]);
	}


	public function updateVehicle(Request $request) {

		$user = JWTAuth::toUser($request->token);

		$rules = [
			'vehicle_type' 	=> 'required',
			'make_id' 		=> 'required',
			'model_id'		=> 'required',
			'year'			=> 'required',
			'color'			=> 'required',
		];

		if($request->id) {
            $rules['license_no'] = 'required|unique:vehicle,vehicle_number,'.$request->id;
        } else {
            $rules['license_no'] = 'required|unique:vehicle,vehicle_number';
        }

		$attributes = [
			'license_no' 	=> trans('messages.account.license_no'),
			'vehicle_type'	=> trans('messages.account.vehicle_type'),
			'make_id'		=> trans('messages.account.make_id'),
			'model_id'		=> trans('messages.account.model_id'),
			'year'			=> trans('messages.account.year'),
		];

		$validator = Validator::make($request->all(), $rules,$attributes);
		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$other_update = 0;

		if(!$request->id) {
			$vehicles =  new Vehicle;
			$other_update = 1;
		} else {
			$vehicles = Vehicle::find($request->id);
			if(!$vehicles) {
				return response()->json([
					'status_code' => '0',
					'status_message' => trans('messages.account.invalid_id'),
				]);
			}

	        if($request->license_no!=$vehicles->vehicle_number || $request->make_id!=$vehicles->vehicle_make_id || $request->model_id!=$vehicles->vehicle_model_id || $request->year!=$vehicles->year || $request->color!=$vehicles->color) {
	        	$other_update = 1;
	        }
		}

		$make_name = MakeVehicle::whereId($request->make_id)->value('make_vehicle_name');
		$model_name = VehicleModel::whereId($request->model_id)->value('model_name');

		$vehicles->vehicle_name = $make_name.' '.$model_name;
		$vehicles->company_id   = $user->company_id;

		$type_name = '';
		$vehicle_types_id = $request->vehicle_type;
		$types_id = explode(',', $vehicle_types_id);
        foreach($types_id as $type_id) {
            // for vehicle type name
            if($type_name!='') {
                $delimeter = ',';
            } else {
                $delimeter = '';
            }
            $car_name = CarType::find($type_id)->car_name;
            $type_name .= $delimeter.$car_name;
        }

        $vehicles->vehicle_number   = $request->license_no;
		$vehicles->vehicle_id     	= $vehicle_types_id;
		$vehicles->vehicle_type     = $type_name;
		$vehicles->vehicle_make_id  = $request->make_id; 
		$vehicles->vehicle_model_id = $request->model_id; 
		$vehicles->user_id   		= $user->id;
		$vehicles->year   			= $request->year;
		$vehicles->color   			= $request->color;

		if($other_update==1) {

			$user = User::find($user->id);
			
			if(!$user->vehicle || $vehicles->default_type=='1') {
				$user->status = UserStatusUpdate($user);
				$user->save();
			}

			$vehicles->is_active = '0';
			$vehicles->status = "Inactive";
			$vehicles->default_type = '0';

			if(isLiveEnv()) {
				
				if($user->vehicles->count()==1 && $request->id) {

					$vehicle_documents = $user->driver_documents('Vehicle')->count();
					$required_documents = UserDocuments('Vehicle',$user,$request->id);

					if($user->vehicles->count()==1 && $vehicle_documents==count($required_documents)) {
						$vehicles->is_active = '1';
						$vehicles->status = "Active";
						$vehicles->default_type = '1';
					}
				}
			}
		}
		$vehicles->save();
		Vehicle::ensureDefaultForUser($user->id);

		// for default selection update car type in driver location
        $vehicles = Vehicle::find($vehicles->id);
        if($vehicles && $vehicles->default_type=='1') {

            $driver_location = DriverLocation::where('user_id', $vehicles->user_id)->first();

            if($driver_location) {
                $dr_location['user_id']     = $vehicles->user_id;
                $dr_location['latitude']    = $driver_location->latitude;
                $dr_location['longitude']   = $driver_location->longitude;
                $dr_location['status']      = $driver_location->status;
                $dr_location['pool_trip_id']= $driver_location->pool_trip_id;

                $vehicle_types = explode(',', $vehicles->vehicle_id);
                foreach($vehicle_types as $vehicle_type) {
                    $dr_location['car_id'] = $vehicle_type;
                    DriverLocation::updateOrCreate(['user_id' => $vehicles->user_id, 'car_id' => $vehicle_type], $dr_location);
                }
                DriverLocation::where('user_id',$vehicles->user_id)->whereNotIn('car_id',$vehicle_types)->delete();
            }                
        }

		// save filter options
		$options = explode(',', $request->options);
		$filter_insert = FilterObject::optionsInsert('vehicle',$vehicles->id,$options);

        // get vehicles
		$vehicles_detail = Vehicle::where('user_id',$user->id)->get();
		$vehicles_details = [];
		foreach($vehicles_detail as $key => $value) {
			$vehicles_details[$key]['id'] 			= $value->id;
			$vehicles_details[$key]['vehicle_name'] = $value->vehicle_name;
			$vehicles_details[$key]['make'] 		= $value->makeWithSelected;
			$vehicles_details[$key]['make_id'] 		= $value->vehicle_make_id;
			$vehicles_details[$key]['model'] 		= $value->modelWithSelected;
			$vehicles_details[$key]['model_id'] 	= $value->vehicle_model_id;
			$vehicles_details[$key]['license_number'] = $value->vehicle_number;
			$vehicles_details[$key]['year'] 		= $value->year;
			$vehicles_details[$key]['color'] 		= $value->color;
			$vehicles_details[$key]['vehicle_id'] 	= $value->vehicle_id;
			$vehicles_details[$key]['vehicleImageURL'] = url('static/Driving-Licence.jpg');	
			$vehicles_details[$key]['status'] 		= $value->trans_status;
			$vehicles_details[$key]['is_active'] 	= $value->is_active;
			$vehicles_details[$key]['is_default'] 	= $value->default_type;

			$vehicle_types = explode(',', $value->vehicle_id);
			$vehicles_details[$key]['vehicle_types'] = getVehicleType($vehicle_types,$value->default_type);
			$vehicles_details[$key]['vechile_documents'] = UserDocuments('Vehicle',$user,$value->id);

			// get filter options
			$female_riders = FilterObject::exist('vehicle',$value->id,1) ? true:false;
			$handicap = FilterObject::exist('vehicle',$value->id,2) ? true:false;
			$child_seat = FilterObject::exist('vehicle',$value->id,3) ? true:false;
			$skip = $user->gender=='1' ? true:false;
			$request_options = FilterOption::options($skip,$female_riders,$handicap,$child_seat);

			$vehicles_details[$key]['request_options'] = $request_options;
		}	

		if(!$request->id) {
			$message = trans('messages.user.add_success');
		} else {
			$message = trans('messages.user.update_success');
		}

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> $message,
			'vehicles_details'  => $vehicles_details
		]);
	}

	public function deleteVehicle(Request $request) {

		$user = JWTAuth::toUser($request->token);

		$rules['id'] = 'required';

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$vehicle = Vehicle::find($request->id);

        if(!$vehicle) {
            $status = '0';
			$message = trans('messages.account.invalid_id');
        } else if($vehicle->default_type=='1') {
        	$status = '0';
			$message = trans('messages.user.default_vehicle_delete_msg');
        } else {
        	try {
        		// delete vehicle
	            $vehicle->delete();
	            $filters_delete = FilterObject::whereObjectId($request->id)->delete();
	            // update status if there is no active vehicles
	            if(!$user->vehicles()->active()->count()) {
	            	User::where('id', $user->id)->update(['status' => 'Car_details']);
	            }
	            $status = '1';
				$message = trans('messages.user.delete_success');
	        } catch(\Exception $e) {
	            $status = '0';
				$message = $e->getMessage();
	        }
        }

        return response()->json([
			'status_code' => $status,
			'status_message' => $message,
		]);
	}

	public function updateDefaultVehicle(Request $request) {

		$rules['vehicle_id'] = 'required';

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$user = JWTAuth::toUser($request->token);
		$user_id = $user->id;
		$vehicle_id = $request->vehicle_id;
		$vehicle_exists = Vehicle::findVehicleExist($vehicle_id,$user_id);

		if(!$vehicle_exists) {
            return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.account.invalid_id'),
			]);
        }

        if($vehicle_exists->status!='Active') {
        	return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.user.vehicle_not_activated'),
			]);
        }

        // Check pre default vehicle is in ride or not
        $driver_status = checkDefault($user_id,$vehicle_id,'1');
        if($driver_status=='1') {
        	return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.user.default_vehicle_trip_error'),
			]);
        }

        
		
        // check pre default vehicle to update non default
		$pre_default_vehicle = Vehicle::getPreDefaultVehicle($user_id);

        if($pre_default_vehicle) {
			// set as non default vehicle
    		$pre_default_vehicle = Vehicle::find($pre_default_vehicle->id);
    		$pre_default_vehicle->default_type = '0';
    		$pre_default_vehicle->save();
        }

        // update default vehicle
		$default_vehicle = Vehicle::find($vehicle_id);
		$default_vehicle->default_type = '1';
		$default_vehicle->save();

		// update vehicle types in driver location
		$driver_location = DriverLocation::where('user_id', $user_id)->first();

        $dr_location['latitude'] = $user_id;
        $dr_location['latitude'] = $driver_location->latitude;
        $dr_location['longitude']= $driver_location->longitude;
        $dr_location['status'] 	 = $driver_location->status;

        $vehicle_list = $default_vehicle->vehicle_id;
		$vehicle_list = explode(',', $vehicle_list);

		foreach($vehicle_list as $vehicle) {
            $dr_location['car_id'] = $vehicle;
            DriverLocation::updateOrCreate(['user_id' => $user_id, 'car_id' => $vehicle], $dr_location);
        }

        DriverLocation::where('user_id',$user_id)->whereNotIn('car_id',$vehicle_list)->delete();

        return response()->json([
			'status_code' => '1',
			'status_message' => trans('messages.user.update_success'),
		]);
	}

	public function update_document(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user_id = $user_details->id;
		$rules = [
			'type' 				=> 'required|in:Driver,Vehicle',
			'document_id'      	=> 'required|exists:documents,id',
		];

		if($request->type=="Vehicle"){
			$rules['vehicle_id'] = 'required';
		}

		if($request->document_id) {
			$documents = Documents::find($request->document_id);

			if($documents) {
				$vehicleID = ($request->type == 'Vehicle') ? $request->vehicle_id : 0;
				$checkDoc = DriverDocuments::where('type',$request->type)->where('vehicle_id',$vehicleID)->where('user_id',$user_id)->where('document_id',$documents->id)->first();

				if($documents->expire_on_date=='Yes' && !$checkDoc) {
					$rules['expired_date'] = 'required|date|date_format:Y-m-d';
				}

				if(!$checkDoc) {
					$rules['document_image'] = 'required|mimes:jpg,jpeg,png,gif';
				}
			}
		}

		$messages = [
			'required' 	=> ':attribute '.trans('messages.field_is_required'),
			'exists' 	=> trans('messages.document_select'), 
		];

		$attributes = [
			'type'  => trans('messages.account.type'),
			'document_id'  => trans('messages.account.document_id'),
			'vehicle_id'  => trans('messages.account.vehicle_id'),
			'expired_date'  => trans('messages.account.expired_date'),
			'document_image'  => trans('messages.account.document_image'),
		];
		
		$validator = Validator::make($request->all(), $rules, $messages, $attributes);
		if($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		if($checkDoc == ''){
			$driver_document = new DriverDocuments;
		}else{
			$driver_document = DriverDocuments::find($checkDoc->id);
		}

		$driver_document->user_id 		= $user_id;
		$driver_document->type 			= $request->type;
		$driver_document->vehicle_id 	= $vehicleID;
		$driver_document->document_id 	= $documents->id;
		$driver_document->uploaded_at 	= now();
		
		if($request->hasFile('document_image')) {
			$image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
			$target_dir 	= '/images/users/'.$user_details->id;
			$image 			= $request->file('document_image');
			$original_name 	= $image->getClientOriginalName();
			$mime_type 		= $image->getMimeType();
			$file_size 		= $image->getSize();
			$extension 		= $image->getClientOriginalExtension();
			$file_name 		= $documents->doc_name."_".time().".".$extension;			
		    $options 		= compact('target_dir','file_name');
		    $upload_result 	= $image_uploader->upload($image,$options);

		    if(!$upload_result['status']) {
		        return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $upload_result['status_message'],
				]);
		    }

		   	$filename = asset($target_dir.'/'.$upload_result['file_name']);
		   	$driver_document->document = $filename;
		   	$driver_document->original_name = $original_name;
		   	$driver_document->mime_type = $mime_type;
		   	$driver_document->file_size = $file_size;
		}
		
		if($request->expired_date) {
			$driver_document->expired_date 	= $request->expired_date;
		}
		$driver_document->status = '0';
		$driver_document->reviewed_at = null;
		$driver_document->reviewed_by = null;
		$driver_document->rejection_reason = null;
		$driver_document->save();

		$user = User::find($user_id);
		
		if($request->type == 'Vehicle'){
			$vehicle = Vehicle::find($vehicleID);
			
			if($vehicle->default_type == '1' || !$user->vehicle){
				$user->status = UserStatusUpdate($user);
				$user->save();			
			}

			$vehicle->is_active = '0';
			$vehicle->status = "Inactive";
			$vehicle->default_type = '0';

			if(isLiveEnv()) {
				$vehicle_documents = $user->driver_documents('Vehicle')->count();
				$required_documents = UserDocuments('Vehicle',$user,$vehicleID);
				if($user->vehicles->count()==1 && $vehicle_documents==count($required_documents)) {
					$vehicle->is_active = '1';
					$vehicle->status = "Active";
					$vehicle->default_type = '1';
				}
			}

			$vehicle->save();
			Vehicle::ensureDefaultForUser($user_id);
		} else {
			$user->status = UserStatusUpdate($user);
			$user->save();
		}

		return response()->json([
			'status_code' 			=> "1",
			'status_message' 		=> "Upload Successfully",
			'document_url' 			=> $driver_document->document ?? '',
		]);
	}

	public function confirmParcelPickup(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();
		$validator = Validator::make($request->all(), [
			'trip_id' => 'required|exists:trips,id',
			'token' => 'nullable',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->errors()->first(),
			]);
		}

		$trip = Trips::find($request->trip_id);

		if (!$user || $user->id != $trip->driver_id) {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'Unauthorized',
			]);
		}

		if ($trip->service_category !== 'parcel') {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'This trip is not a parcel delivery',
			]);
		}

		$trip->parcel_picked_up_at = now();
		$trip->save();

        resolve(NotificationManager::class)->sendEvent('parcel.customer.parcel_picked_up', $trip->users, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type', 'ride_request.parcelRequest']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ]);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Parcel pickup confirmed',
			'data' => [
				'trip_id' => $trip->id,
				'parcel_picked_up_at' => $trip->parcel_picked_up_at,
			],
		]);
	}

	public function confirmParcelDelivery(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();
		$validator = Validator::make($request->all(), [
			'trip_id' => 'required|exists:trips,id',
			'token' => 'nullable',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->errors()->first(),
			]);
		}

		$trip = Trips::find($request->trip_id);

		if (!$user || $user->id != $trip->driver_id) {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'Unauthorized',
			]);
		}

		if ($trip->service_category !== 'parcel') {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'This trip is not a parcel delivery',
			]);
		}

		$trip->parcel_delivered_at = now();
		$trip->save();

        resolve(NotificationManager::class)->sendEvent('parcel.customer.delivery_completed', $trip->users, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type', 'ride_request.parcelRequest']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ]);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Parcel delivery confirmed',
			'data' => [
				'trip_id' => $trip->id,
				'parcel_delivered_at' => $trip->parcel_delivered_at,
			],
		]);
	}

	public function submitParcelProofOfDelivery(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();
		$validator = Validator::make($request->all(), [
			'trip_id' => 'required|exists:trips,id',
			'token' => 'nullable',
			'signature' => 'nullable|string',
			'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->errors()->first(),
			]);
		}

		$trip = Trips::find($request->trip_id);

		if (!$user || $user->id != $trip->driver_id) {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'Unauthorized',
			]);
		}

		if ($trip->service_category !== 'parcel') {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'This trip is not a parcel delivery',
			]);
		}

		if ($request->hasFile('photo')) {
			$file = $request->file('photo');
			$filename = 'pod_' . $trip->id . '_' . time() . '.' . $file->getClientOriginalExtension();
			$path = public_path('images/parcel_pod');
			
			if (!File::exists($path)) {
				File::makeDirectory($path, 0755, true);
			}
			
			$file->move($path, $filename);
			$trip->parcel_pod_photo = 'images/parcel_pod/' . $filename;
		}

		if ($request->filled('signature')) {
			$signatureData = $request->signature;
			if (preg_match('/^data:image\/(\w+);base64,/', $signatureData)) {
				$signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
				$signatureData = base64_decode($signatureData);
				$filename = 'sig_' . $trip->id . '_' . time() . '.png';
				$path = public_path('images/parcel_pod');
				
				if (!File::exists($path)) {
					File::makeDirectory($path, 0755, true);
				}
				
				file_put_contents($path . '/' . $filename, $signatureData);
				$trip->parcel_pod_signature = 'images/parcel_pod/' . $filename;
			}
		}

		$trip->parcel_delivered_at = now();
		$trip->save();

        resolve(NotificationManager::class)->sendEvent('parcel.customer.delivery_completed', $trip->users, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type', 'ride_request.parcelRequest']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ]);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Proof of delivery submitted',
			'data' => [
				'trip_id' => $trip->id,
				'pod_signature' => $trip->parcel_pod_signature ? asset($trip->parcel_pod_signature) : null,
				'pod_photo' => $trip->parcel_pod_photo ? asset($trip->parcel_pod_photo) : null,
				'parcel_delivered_at' => $trip->parcel_delivered_at,
			],
		]);
	}
}
