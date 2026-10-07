<?php

/**
 * Earning Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Earning
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Trips;
use App\Models\User;
use App\Models\PoolTrip;
use App\Models\UsersPromoCode;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Http\Helper\RequestHelper;
use App\Http\Helper\InvoiceHelper;
use App\Services\DriverOnlineSessionService;
use JWTAuth;
use Validator;
use DB;

class EarningController extends Controller
{
	protected $request_helper; // Global variable for Helpers instance

	protected $provider;
	protected $online_session_service;

	public function __construct(RequestHelper $request,InvoiceHelper $invoice_helper, DriverOnlineSessionService $online_session_service)
	{
		$this->request_helper = $request;
		$this->invoice_helper = $invoice_helper;
		$this->online_session_service = $online_session_service;
	}

	/**
	 * Display the Earning chart details in Driver
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function earning_chart(Request $request)
	{
		$driver_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
			'start_date' => 'required',
			'end_date' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		$user = User::where('id', $driver_details->id)->where('user_type', $request->user_type)->first();
		$company_id = $user->company_id;

		$last_trip = Trips::where('driver_id', $driver_details->id)->where('status', 'Completed')->orderBy('id', 'DESC')->first();
		$last_trip = $last_trip->company_driver_earnings ?? "0";

		$currency = Currency::where('code', $user->currency_code)->first();

		$total_weekly_fare = 0;
		for($i = 0; $i < 7; $i++) {

			$created_at = date("Y-m-d", strtotime($request->start_date . '+' . $i . 'day'));
			$created_day = date("l", strtotime($created_at));

			$fare_amount = $this->calculateDriverEarnings($driver_details->id, $created_at, $created_at, $currency->rate);

			if($fare_amount == 0) {
				$fare_amount = 0.00;
			}

			$total_weekly_fare += $fare_amount;
			$trips_array[] = ["created_at" => $created_at, "day" => $created_day, "daily_fare" => number_format($fare_amount, 2)];
		}

		$today = date('Y-m-d');
		$monthStart = date('Y-m-01');
		$todayAmount = $this->calculateDriverEarnings($driver_details->id, $today, $today, $currency->rate);
		$monthAmount = $this->calculateDriverEarnings($driver_details->id, $monthStart, $today, $currency->rate);
		$todayTripsCount = Trips::where('driver_id', $driver_details->id)
			->where('status', 'Completed')
			->whereDate('created_at', $today)
			->count();
		$totalTripsToday = Trips::where('driver_id', $driver_details->id)
			->whereDate('created_at', $today)
			->count();
		$weekTripsCount = Trips::where('driver_id', $driver_details->id)
			->where('status', 'Completed')
			->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'])
			->count();
		$sessionStatus = $this->online_session_service->buildStatus($user);

		$last_trip = Trips::where('driver_id', $driver_details->id)->where('status', 'Completed')->orderBy('id', 'DESC')->first();
		$recentPayout = $last_trip ? number_format($last_trip->company_driver_earnings, 2) : '0.00';

		$today_trips = Trips::join('currency', 'currency.code', '=', 'trips.currency_code')
			->whereRaw("DATE_FORMAT(trips.created_at,'%Y-%m-%d') = '" . $today . "'")
			->where('trips.driver_id', $driver_details->id)
			->where('trips.status', 'Completed')
			->orderBy('trips.created_at', 'DESC')
			->limit(3)
			->get([
				'trips.pickup_location',
				'trips.drop_location',
				'trips.created_at',
				'trips.subtotal_fare',
				'trips.driver_peak_amount',
				'trips.tips',
				'trips.waiting_charge',
				'trips.toll_fee',
				'trips.additional_rider_amount',
				'trips.driver_or_company_commission',
				'currency.rate as currency_rate',
			]);

		$recentTrips = [];
		foreach ($today_trips as $trip) {
			$fareAmount = 0;
			$fareAmount += $trip->subtotal_fare;
			$fareAmount += $trip->driver_peak_amount;
			$fareAmount += $trip->tips;
			$fareAmount += $trip->waiting_charge;
			$fareAmount += $trip->toll_fee;
			$fareAmount += $trip->additional_rider_amount;
			$fareAmount -= $trip->driver_or_company_commission;
			if ($trip->currency_rate > 0) {
				$fareAmount = ($fareAmount / $trip->currency_rate) * $currency->rate;
			}

			$recentTrips[] = [
				'route' => trim(($trip->pickup_location ?? '') . ' → ' . ($trip->drop_location ?? '')),
				'time' => date('H:i', strtotime($trip->created_at)),
				'fare' => number_format($fareAmount, 2),
			];
		}

		$bonusTitle = '';
		$bonusSubtitle = '';
		$remainingTripsForBonus = max(0, 4 - $weekTripsCount);
		if ($remainingTripsForBonus > 0) {
			$bonusTitle = 'Complete '.$remainingTripsForBonus.' more trips to earn bonus!';
			$bonusSubtitle = $weekTripsCount.' of 4 completed · Weekend challenge';
		}
		else if ($weekTripsCount > 0) {
			$bonusTitle = 'Bonus milestone active';
			$bonusSubtitle = $weekTripsCount.' trips completed this week';
		}

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> "Success",
			'recent_payout' 	=> $recentPayout,
			'trip_details' 		=> $trips_array,
			'last_trip' 		=> $last_trip,
			'total_week_amount' => (string) number_format(($total_weekly_fare), 2),
			'today_amount' => (string) number_format($todayAmount, 2),
			'month_amount' => (string) number_format($monthAmount, 2),
			'total_trips_today' => (string) $totalTripsToday,
			'completed_trips_today' => (string) $todayTripsCount,
			'trips_today' => (string) $todayTripsCount,
			'current_session_seconds' => (int) $sessionStatus['current_session_seconds'],
			'today_online_seconds' => (int) $sessionStatus['today_online_seconds'],
			'week_online_seconds' => (int) $sessionStatus['week_online_seconds'],
			'month_online_seconds' => (int) $sessionStatus['month_online_seconds'],
			'bonus_title' => $bonusTitle,
			'bonus_subtitle' => $bonusSubtitle,
			'recent_trips' => $recentTrips,
			'currency_code' 	=> @$user->currency_code,
			'currency_symbol' 	=> $currency->symbol,
		]);

		return response()->json($user);
	}

	/**
	 * Calculate driver earnings for a date range in the user currency.
	 */
	protected function calculateDriverEarnings($driverId, $fromDate, $toDate, $targetRate)
	{
		return Trips::join('currency', 'currency.code', '=', 'trips.currency_code')
			->where('trips.driver_id', $driverId)
			->where('trips.status', 'Completed')
			->whereRaw("DATE_FORMAT(trips.created_at,'%Y-%m-%d') >= '" . $fromDate . "'")
			->whereRaw("DATE_FORMAT(trips.created_at,'%Y-%m-%d') <= '" . $toDate . "'")
			->sum(DB::raw('((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * ' . $targetRate));
	}

	/**
	 * After Payment and  update the trip status
	 * @param  POST method request inputs
	 *
	 * @return Response Json
	 */
	public function afterPayment(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$payment_methods = collect(PAYMENT_METHODS);
		$payment_types = $payment_methods->pluck('key')->implode(',');

		$rules = [
			'trip_id' 		=> 'required|exists:trips,id',
			'payment_type' 	=> 'required|in:'.$payment_types,
		];

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		$user = User::where('id', $user_details->id)->first();

		if($user == '') {
			return response()->json([
				'status_code'	 => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		$trip = Trips::find($request->trip_id); 
		app(\App\Services\Corporate\CorporateCollectionGuard::class)->assertPersonalTrip($trip, $user);
		if($trip->status != 'Payment') {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.api.something_went_wrong'),
			]);
		}

		if($trip->is_calculation == 0) {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $user_details->id,
				'save_to_trip_table' => 1,
			];
			$trip = $this->invoice_helper->calculation($data);
		}
		
		if($trip->total_fare > 0) {
			if($request->payment_type != "stripe" && $request->pay_key == '') {
	            return response()->json([
					'status_code' => '0',
					'status_message' => __('messages.api.something_went_wrong'),
				]);
	        }

	        $payment_data['currency_code'] = $trip->currency_code;
			$payment_data['amount'] = $trip->total_fare;

			if($request->payment_type == 'stripe') {
				$payment_method = PaymentMethod::where('user_id', $user_details->id)->first();
				if($payment_method == '') {
					return response()->json([
		                'status_code' => '0',
		                'status_message' => __('messages.api.please_add_card_details'),
		            ]);
				}

				$payment_data = [
					"amount" 		=> $trip->total_fare * 100,
					'currency' 		=> $trip->currency_code,
					'description' 	=> 'Trip Payment by '.$user_details->first_name,
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
					$trip->is_calculation = 0;
					$trip->status = 'Payment';
					$trip->save();

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
				$trip->is_calculation = 0;
				$trip->status = 'Payment';
				$trip->save();

				return response()->json([
	                'status_code' => '0',
	                'status_message' => $e->getMessage(),
	            ]);
			}
		}

		$trip = Trips::where('id', $request->trip_id)->first();
		$trip->status = 'Completed';
		$trip->paykey = $pay_result->transaction_id ?? '';
		$trip->payment_status = 'Completed';
		$trip->save();

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
			'trip_id' => $trip->id,
			'correlation_id' => $pay_result->transaction_id ?? '',
			'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
		];

		Payment::updateOrCreate(['trip_id' => $trip->id], $data);

		$driver = User::where('id', $trip->driver_id)->first();

		$push_title = __('messages.payment_complete_success');

		$push_data['push_title'] = $push_title;
        $push_data['data'] = [
        	'trip_payment' => [
        		'status' => 'Paid',
        		'trip_id' => $trip->id,
        		'rider_thumb_image' => $trip->rider_profile_picture
        	]
        ];
        $this->request_helper->SendPushNotification($driver,$push_data);

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> "Paid Successfully",
			'currency_code' 	=> $trip->currency_code ?? '',
			'total_time' 		=> $trip->total_time ?? '0.00',
			'total_km' 			=> $trip->total_km ?? '0.00',
			'total_time_fare' 	=> $trip->time_fare ?? '0.00',
			'total_km_fare' 	=> $trip->distance_fare ?? '0.00',
			'base_fare' 		=> $trip->base_fare ?? '0.00',
			'total_fare' 		=> $trip->total_fare ?? '0.00',
			'access_fee' 		=> $trip->access_fee ?? '0.00',
			'pickup_location' 	=> $trip->pickup_location ?? '',
			'drop_location' 	=> $trip->drop_location ?? '',
			'driver_payout' 	=> $trip->driver_payout ?? '0.00',
			'trip_status'		=> $trip->status,
			'driver_thumb_image'=> $driver->profile_picture->src ?? url('images/user.jpeg'),
		]);
	}

	

	public function add_promo_code(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'code' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }
		
		$promo_code_check = PromoCode::where(DB::raw('BINARY `code`'),$request->code)->where('status', 'Active')->first();
		$promo_code_date_check = PromoCode::where(DB::raw('BINARY `code`'),$request->code)->where('expire_date', '>=', date('Y-m-d'))->first();

		if (@$promo_code_check) {
			if (@$promo_code_date_check) {
				$users_promo_code_check = UsersPromoCode::whereUserId($user_details->id)->wherePromoCodeId($promo_code_date_check->id)->first();
				if (@$users_promo_code_check) {
					return ['status_code' => '0', 'status_message' => trans('messages.promo_already_applied')];
				}

				$users_promo_code = new UsersPromoCode;

				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;

				$users_promo_code->save();

				$users_promo_codes = UsersPromoCode::whereUserId($user_details->id)->whereTripId(0)->with('promo_code')->get();

				$final_promo_details = [];

				foreach ($users_promo_codes as $row) {
					if (@$row->promo_code) {
						$promo_details['id'] = $row->promo_code->id;
						$promo_details['amount'] = $row->promo_code->amount;
						$promo_details['code'] = $row->promo_code->code;
						$promo_details['expire_date'] = $row->promo_code->expire_date_dmy;
						$final_promo_details[] = $promo_details;
					}
				}

				return ['status_code' => '1', 'status_message' => trans('messages.promo_applied_success'), 'promo_details' => $final_promo_details];
			}
			return ['status_code' => '0', 'status_message' => trans('messages.promo_expired')];
		}
		return ['status_code' => '0', 'status_message' => trans('messages.promo_invalid')];
	}
}
