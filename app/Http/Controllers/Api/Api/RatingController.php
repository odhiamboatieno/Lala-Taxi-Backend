<?php

/**
 * Rating Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Rating
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\Fees;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\ManageFare;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\Wallet;
use App\Models\ScheduleRide;
use App\Models\Company;
use App\Models\DriverOweAmount;
use App\Models\PoolTrip;
use App\Repositories\DriverOweAmountRepository;
use App\Services\DriverScore\DriverScoreService;
use App\Services\Rating\DriverRatingEligibilityService;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use App\Http\Helper\InvoiceHelper;
use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Schema;

class RatingController extends Controller
{
	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request,DriverOweAmountRepository $driver_owe_amt_repository,InvoiceHelper $invoice_helper)
	{
		$this->request_helper = $request;
		$this->helper = new Helpers;
		$this->invoice_helper = $invoice_helper;
		$this->driver_owe_amt_repository = $driver_owe_amt_repository;
		$this->driver_score_service = resolve(DriverScoreService::class);
		$this->driver_rating_eligibility = resolve(DriverRatingEligibilityService::class);
	}

	/**
	 * Display the Diver rating
	 * @param  Post method request inputs
	 *
	 * @return Response Json
	 */
	public function driver_rating(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}
		$user = User::where('id', $user_details->id)->first();

		if($user == '') {
			return response()->json([
				'status_code' 	 => '0',
				'status_message' => "Invalid credentials",
			]);
		}

		$total_rated_trips = DB::table('rating')->select(DB::raw('count(id) as total_rated_trips'))
			->where('driver_id', $user_details->id)->where('rider_rating', '>', 0)->value('total_rated_trips');

		$total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
			->where('driver_id', $user_details->id)->where('rider_rating', '>', 0)->where('driver_id', $user_details->id)->value('rating');

		$total_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating','>', 0)->count();

		$life_time_trips = DB::table('trips')->select(DB::raw('count(id) as total_trips'))
			->where('driver_id', $user_details->id)->value('total_trips');

		$five_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 5)->count();
		$four_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 4)->count();
		$three_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 3)->count();
		$two_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 2)->count();
		$one_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 1)->count();

		$driver_rating = '0.00';
		if ($total_rating_count != 0) {
			$driver_rating = (string) round(($total_rating / $total_rating_count), 2);
		}

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> "Success",
			'total_rating' 		=> @$total_rated_trips != '' ? $total_rated_trips : '0',
			'total_rating_count'=> @$life_time_trips != '' ? $life_time_trips : '0',
			'driver_rating' 	=> @$driver_rating != '' ? $driver_rating : '0.00',
			'five_rating_count' => @$five_rating_count != '' ? $five_rating_count : '0',
			'four_rating_count' => @$four_rating_count != '' ? $four_rating_count : '0',
			'three_rating_count' => @$three_rating_count != '' ? $three_rating_count : '0',
			'two_rating_count' => @$two_rating_count != '' ? $two_rating_count : '0',
			'one_rating_count' => @$one_rating_count != '' ? $one_rating_count : '0',
		]);
	}

	/**
	 * Get The Invoice of the given Trip id
	 *
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function getinvoice(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$request->merge(['payment_mode' =>strtolower($request->payment_mode)]);
		$rules = [
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'trip_id' => 'required',
			'payment_mode' => 'in:paypal,paytm,stripe,cash,braintree,onlinepayment,mpesa,flutterwave',
			'is_wallet' => 'in:Yes,No',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}
		// set onlinepayment as paypal for temporary
		$request->payment_mode = ($request->payment_mode=='onlinepayment' || $request->payment_mode=='paypal')?'PayPal':ucfirst($request->payment_mode);

		

		$user = User::where('id', $user_details->id)->first();

		$trips = Trips::where('id', $request->trip_id)->first();
		if (!$trips) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => __('messages.api.no_trips_found'),
            ]);
		}

		$save = 0;
		$tripStatus = strtolower((string) $trips->status);
		if ($request->payment_mode && !in_array($tripStatus, ['completed','rating','cancelled'], true)) {
			$payment_method_store = $request->payment_mode;
			if ($request->is_wallet == 'Yes' && $payment_method_store != 'Wallet') {
				$payment_method_store = $request->payment_mode . ' & Wallet';
			}

			//If user change payment mode then change payment mode in trips,requests & schedule_rides tables also
			Trips::where('id', $request->trip_id)->update(['payment_mode' => $payment_method_store]);
			$rideRequest = RideRequest::where('id', $trips->request_id)->first();
			if ($rideRequest) {
				$rideRequest->payment_mode = $payment_method_store;
				$rideRequest->save();
				ScheduleRide::where('id', $rideRequest->schedule_id)->update(['payment_method' => $rideRequest->payment_mode]);
			}
			$trips->payment_mode = $payment_method_store;
		}
		
		if ($tripStatus == 'payment') {
			$data = [
				'trip_id' 	=> $request->trip_id,
				'user_type' => $request->user_type,
				'user_id' 	=> $user->id,
				'save_to_trip_table' => $save,
			];
			$trips = $this->invoice_helper->calculation($data);
			return $this->invoice_helper->getInvoice($trips,$data);
		}

		if (in_array($tripStatus, ['end trip','end_trip','completed','rating'], true)) {
			$data = [
				'trip_id' 	=> $request->trip_id,
				'user_type' => $request->user_type,
				'user_id' 	=> $user->id,
				'save_to_trip_table' => 0,
			];
			return $this->invoice_helper->getInvoice($trips,$data);
		}

		return response()->json([
			'status_code' 	 => '2',
			'status_message' => __('messages.api.something_went_wrong'),
		]);
	}

	/**
	 * Driver-only checkout summary payload for the trip completion screen.
	 *
	 * Keeps rider checkout/invoice responses unchanged while exposing a
	 * normalized JSON contract for the driver app.
	 */
	public function getDriverCheckoutSummary(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
			'trip_id' => 'required|exists:trips,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$trip = Trips::with(['driver.vehicle', 'users', 'currency'])
			->where('id', $request->trip_id)
			->where('driver_id', $user_details->id)
			->first();

		if (!$trip) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		if (!in_array($trip->status, ['Rating', 'Payment', 'Completed'])) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.api.something_went_wrong'),
			]);
		}

		if (in_array($trip->status, ['Rating', 'Payment', 'Completed']) && $this->invoice_helper->needsRealtimeRecalculation($trip)) {
			$data = [
				'trip_id' => $trip->id,
				'user_type' => 'Driver',
				'user_id' => $user_details->id,
				'save_to_trip_table' => 0,
			];
			$this->invoice_helper->calculation($data);
			$trip = Trips::with(['driver.vehicle', 'users', 'currency'])->find($trip->id);
		}

		$invoice = $this->invoice_helper->formatInvoice($trip, [
			'trip_id' => $trip->id,
			'user_type' => 'Driver',
			'user_id' => $user_details->id,
			'save_to_trip_table' => 0,
		]);

		$currencySymbol = $trip->currency_symbol ?? optional($trip->currency)->symbol ?? '';
		$fareMode = strtolower((string) data_get($trip->fare_estimation, 'fare_mode', $trip->fare_mode ?? site_settings('fare_mode') ?: 'taximeter'));
		$totalFare = (float) $trip->total_fare;
		if ($totalFare <= 0 && $fareMode === 'pre-calculated') {
			$totalFare = (float) data_get($trip->fare_estimation, 'estimated_fare', data_get($trip->fare_estimation, 'fare_estimate', 0));
		}
		if ($totalFare <= 0) {
			$totalFare = (float) ($trip->admin_total_amount ?? 0);
		}
		$isCashTrip = in_array(strtolower((string) $trip->payment_mode), ['cash', 'cash & wallet'], true);
		$grossDriverEarnings = (float) $trip->subtotal_fare
			+ (float) $trip->driver_peak_amount
			+ (float) $trip->tips
			+ (float) $trip->waiting_charge
			+ (float) $trip->toll_fee
			+ (float) $trip->additional_rider_amount
			+ (float) ($trip->extra_fare_amount ?? 0);
		if ($grossDriverEarnings <= 0) {
			$grossDriverEarnings = (float) ($trip->company_driver_earnings ?? 0);
		}
		$appliedOweAmount = (float) ($trip->applied_owe_amount ?? 0);
		$serviceCharge = (float) $trip->driver_or_company_commission;
		$passengerSystemFee = (float) $trip->access_fee;
		$driverServiceFee = (float) ($trip->platform_driver_fee_amount ?? 0);
		$companyCommission = (float) ($trip->company_commission_amount ?? 0);
		$companyDriverFee = (float) ($trip->company_driver_fee_amount ?? 0);
		$totalDriverDeductions = ($isCashTrip ? $passengerSystemFee : 0) + $driverServiceFee + $companyCommission + $companyDriverFee + $appliedOweAmount;
		$netEarnings = max(0, $grossDriverEarnings - $totalDriverDeductions);
		$withdrawablePayout = $isCashTrip ? 0 : (float) $trip->driver_payout;
		if (!$isCashTrip && $withdrawablePayout <= 0) {
			$withdrawablePayout = $netEarnings;
		}
		$distanceKm = $this->invoice_helper->sanitizeTripDistanceKm($trip);
		$serviceChargeRate = $grossDriverEarnings > 0 ? round(($serviceCharge / $grossDriverEarnings) * 100) : 0;
		$driverRating = (float) optional(Rating::where('trip_id', $trip->id)->where('driver_rating', '>', 0)->first())->driver_rating;
		$summaryPrimaryLabel = 'Total received from passenger';
		$breakdown = [];

		$breakdown[] = [
			'key' => 'total_received',
			'label' => $summaryPrimaryLabel,
			'value' => number_format($totalFare, 2, '.', ''),
			'display_value' => $this->formatCurrencyDisplay($currencySymbol, $totalFare),
			'emphasis' => 'positive',
		];

		$breakdown[] = [
			'key' => 'gross_driver_earnings',
			'label' => 'Driver gross earnings',
			'value' => number_format($grossDriverEarnings, 2, '.', ''),
			'display_value' => $this->formatCurrencyDisplay($currencySymbol, $grossDriverEarnings),
			'emphasis' => 'default',
		];

		if ($isCashTrip && $passengerSystemFee > 0) {
			$breakdown[] = [
				'key' => 'passenger_system_fee',
				'label' => 'Passenger system service fee',
				'value' => number_format($passengerSystemFee, 2, '.', ''),
				'display_value' => '-'.$this->formatCurrencyDisplay($currencySymbol, $passengerSystemFee),
				'emphasis' => 'negative',
			];
		}

		if ($driverServiceFee > 0) {
			$breakdown[] = [
				'key' => 'driver_service_fee',
				'label' => 'Driver service fee',
				'value' => number_format($driverServiceFee, 2, '.', ''),
				'display_value' => '-'.$this->formatCurrencyDisplay($currencySymbol, $driverServiceFee),
				'emphasis' => 'negative',
			];
		}

		if ($companyCommission > 0) {
			$breakdown[] = [
				'key' => 'company_commission',
				'label' => 'Company commission',
				'value' => number_format($companyCommission, 2, '.', ''),
				'display_value' => '-'.$this->formatCurrencyDisplay($currencySymbol, $companyCommission),
				'emphasis' => 'negative',
			];
		}

		if ($companyDriverFee > 0) {
			$breakdown[] = [
				'key' => 'company_driver_fee',
				'label' => $trip->company_driver_fee_label ?: 'Company driver fee',
				'value' => number_format($companyDriverFee, 2, '.', ''),
				'display_value' => '-'.$this->formatCurrencyDisplay($currencySymbol, $companyDriverFee),
				'emphasis' => 'negative',
			];
		}

		if ($appliedOweAmount > 0) {
			$breakdown[] = [
				'key' => 'applied_owe_amount',
				'label' => 'Applied owe amount',
				'value' => number_format($appliedOweAmount, 2, '.', ''),
				'display_value' => '-'.$this->formatCurrencyDisplay($currencySymbol, $appliedOweAmount),
				'emphasis' => 'negative',
			];
		}

		$breakdown[] = [
			'key' => 'net_earnings',
			'label' => 'Net earnings',
			'value' => number_format($netEarnings, 2, '.', ''),
			'display_value' => $this->formatCurrencyDisplay($currencySymbol, $netEarnings),
			'emphasis' => 'positive',
		];

		$breakdown[] = [
			'key' => 'withdrawable_payout',
			'label' => $isCashTrip ? 'Withdrawable payout' : 'Driver payout',
			'value' => number_format($withdrawablePayout, 2, '.', ''),
			'display_value' => $this->formatCurrencyDisplay($currencySymbol, $withdrawablePayout),
			'emphasis' => $withdrawablePayout > 0 ? 'positive' : 'default',
		];

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Success',
			'trip_id' => $trip->id,
			'trip_status' => $trip->status,
			'service_category' => $trip->service_category ?? 'ride',
			'payment_mode' => $trip->payment_mode ?? '',
			'payment_status' => $trip->payment_status ?? '',
			'currency_code' => $trip->currency_code ?? '',
			'currency_symbol' => $currencySymbol,
			'fare_mode' => $fareMode,
			'fare_estimate' => number_format((float) data_get($trip->fare_estimation, 'fare_estimate', data_get($trip->fare_estimation, 'estimated_fare', 0)), 2, '.', ''),
			'estimated_fare' => number_format((float) data_get($trip->fare_estimation, 'estimated_fare', data_get($trip->fare_estimation, 'fare_estimate', 0)), 2, '.', ''),
			'fare_estimation' => $trip->fare_estimation ?? null,
			'live_total_fare' => data_get($trip, 'live_total_fare', null),
			'total_fare' => number_format($totalFare, 2, '.', ''),
			'total_received' => number_format($totalFare, 2, '.', ''),
			'driver_gross_earnings' => number_format($grossDriverEarnings, 2, '.', ''),
			'driver_net_earnings' => number_format($netEarnings, 2, '.', ''),
			'driver_payout' => number_format($withdrawablePayout, 2, '.', ''),
			'withdrawable_payout' => number_format($withdrawablePayout, 2, '.', ''),
			'driver_or_company_commission' => number_format($totalDriverDeductions, 2, '.', ''),
			'total_time' => (int) round((float) $trip->total_time),
			'total_km' => number_format($distanceKm, 1, '.', ''),
			'driver_rating' => $driverRating,
			'driver_rating_submitted' => $driverRating > 0,
			'invoice' => $invoice,
			'summary' => [
				'total_received' => number_format($totalFare, 2, '.', ''),
				'total_received_display' => $this->formatCurrencyDisplay($currencySymbol, $totalFare),
				'fare_earned' => number_format($grossDriverEarnings, 2, '.', ''),
				'fare_earned_display' => $this->formatCurrencyDisplay($currencySymbol, $grossDriverEarnings),
				'service_charge' => number_format($totalDriverDeductions, 2, '.', ''),
				'service_charge_display' => '-'.$this->formatCurrencyDisplay($currencySymbol, $totalDriverDeductions),
				'service_charge_rate' => $serviceChargeRate,
				'gross_driver_earnings' => number_format($grossDriverEarnings, 2, '.', ''),
				'gross_driver_earnings_display' => $this->formatCurrencyDisplay($currencySymbol, $grossDriverEarnings),
				'applied_owe_amount' => number_format($appliedOweAmount, 2, '.', ''),
				'applied_owe_amount_display' => '-'.$this->formatCurrencyDisplay($currencySymbol, $appliedOweAmount),
				'net_earnings' => number_format($netEarnings, 2, '.', ''),
				'net_earnings_display' => $this->formatCurrencyDisplay($currencySymbol, $netEarnings),
				'withdrawable_payout' => number_format($withdrawablePayout, 2, '.', ''),
				'withdrawable_payout_display' => $this->formatCurrencyDisplay($currencySymbol, $withdrawablePayout),
				'is_cash_trip' => $isCashTrip,
				'distance_km' => number_format($distanceKm, 1, '.', ''),
				'distance_display' => $this->trimTrailingZero($distanceKm).' km',
				'duration_minutes' => (int) round((float) $trip->total_time),
				'duration_display' => (int) round((float) $trip->total_time).' min',
				'passenger_system_fee' => number_format($passengerSystemFee, 2, '.', ''),
				'driver_service_fee' => number_format($driverServiceFee, 2, '.', ''),
				'company_commission' => number_format($companyCommission, 2, '.', ''),
				'company_driver_fee' => number_format($companyDriverFee, 2, '.', ''),
				'company_driver_fee_label' => $trip->company_driver_fee_label ?: '',
				'breakdown' => $breakdown,
			],
		]);
	}

	protected function formatCurrencyDisplay($symbol, $amount)
	{
		return $symbol.$this->trimTrailingZero($amount);
	}

	protected function trimTrailingZero($amount)
	{
		$formatted = number_format((float) $amount, 2, '.', '');
		$formatted = rtrim(rtrim($formatted, '0'), '.');
		return $formatted === '' ? '0' : $formatted;
	}

	/**
	 * Update the trip Rating given by Driver or Rider
	 * @param  Post method request inputs
	 *
	 * @return Response Json
	 */
	public function trip_rating(Request $request) 
	{
        $user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'rating' => 'required|numeric|min:1|max:5',
			'trip_id' => 'required',
			'tips' => 'nullable|numeric|min:0',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return [
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ];
        }
		$user = User::where('id', $user_details->id)->first();

		$trips = Trips::where('id', $request->trip_id)->first();

		if(! $trips) {
			return response()->json([
				'status_code'	 => '0',
				'status_message' => 'Invalid trip.',
			]);
		}

		if($user == '') {
			return response()->json([
				'status_code'	 => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		$user_type = strtolower($request->user_type);
		$raterId = $user_type == 'driver' ? $trips->driver_id : $trips->user_id;

		if ($user_type == 'driver') {
			if ((int) $user_details->id !== (int) $trips->driver_id) {
				return response()->json([
					'status_code' => '0',
					'status_message' => __('messages.invalid_credentials'),
				], 403);
			}

			if ($blocked = $this->driver_rating_eligibility->assertCanRate($trips)) {
				return response()->json([
					'status_code' => '0',
					'status_message' => $blocked['already_rated'] ? 'Rating already submitted for this trip.' : 'Rating is available only after trip completion and payment settlement.',
					'rating_status' => $blocked,
				], $blocked['already_rated'] ? 409 : 422);
			}
		}

		if ($user_type == 'rider') {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $trips->user_id,
				'driver_id' => $trips->driver_id,
				'rider_rating' => $request->rating,
				'rider_comments' => @$request->rating_comments != '' ? $request->rating_comments : '',
				'driver_rating' => 0,
				'driver_comments' => '',
			];

			if (Schema::hasColumn('rating', 'rater_id') && Schema::hasColumn('rating', 'rater_type')) {
				$data['rater_id'] = $raterId;
				$data['rater_type'] = 'rider';
				Rating::updateOrCreate(['trip_id' => $request->trip_id, 'rater_id' => $raterId, 'rater_type' => 'rider'], $data);
			}
			else {
				Rating::updateOrCreate(['trip_id' => $request->trip_id], [
					'trip_id' => $request->trip_id,
					'user_id' => $trips->user_id,
					'driver_id' => $trips->driver_id,
				'rider_rating' => $request->rating,
				'rider_comments' => @$request->rating_comments != '' ? $request->rating_comments : '',
				]);
			}
		}
		else {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $trips->user_id,
				'driver_id' => $trips->driver_id,
				'rider_rating' => 0,
				'rider_comments' => '',
				'driver_rating' => $request->rating,
				'driver_comments' => @$request->rating_comments != '' ? $request->rating_comments : '',
			];

			if (Schema::hasColumn('rating', 'rater_id') && Schema::hasColumn('rating', 'rater_type')) {
				$data['rater_id'] = $raterId;
				$data['rater_type'] = 'driver';
				Rating::updateOrCreate(['trip_id' => $request->trip_id, 'rater_id' => $raterId, 'rater_type' => 'driver'], $data);
			}
			else {
				Rating::updateOrCreate(['trip_id' => $request->trip_id], $data);
			}
		}

		$trip = Trips::where('id', $request->trip_id)->first();
		$tipAmount = max(0, (float) $request->input('tips', 0));
		$tripStatus = strtolower((string) $trip->status);

		if(!in_array($tripStatus, ['rating','payment','completed'], true)) {
			return response()->json([
				'status_code' => '2',
				'status_message' => __('messages.api.trip_already_completed'),
			]);
		}
		if($tripStatus == 'completed') {
			if($user_type == 'rider' && $tipAmount > 0) {
				$currency_code = $user_details->currency->code;
				$tips = currencyConvert($currency_code, $trip->getOriginal('currency_code'), $tipAmount);
				$trip->tips = $tips;
				$trip->save();
			}

			if ($user_type == 'rider' && $trip->driver_id) {
				$this->driver_score_service->recalculateDriver($trip->driver_id, [
					'trigger' => 'rider_rating_submitted',
					'trip_id' => $trip->id,
				]);
			}

			return response()->json([
				'status_code' => '1',
				'status_message' => "Rating successfully",
			]);
		}
		if($tripStatus != 'completed') {
			$trip->status = 'Payment';
		}

		if($user_type == 'rider') {
			if($tipAmount > 0) {
				$currency_code = $user_details->currency->code;
				$tips 		= currencyConvert($currency_code, $trip->getOriginal('currency_code'), $tipAmount);
				$trip->tips = $tips;
			}
		}

		$trip->save();

		if ($user_type == 'rider' && $trip->driver_id) {
			$this->driver_score_service->recalculateDriver($trip->driver_id, [
				'trigger' => 'rider_rating_submitted',
				'trip_id' => $trip->id,
			]);
		}

		if($trip->pool_id>0) {

			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating'])->count();
			
			if(!$trips) {
				// update status
				$pool_trip->status = 'Payment';
				$pool_trip->save();
			}
		}
		
		return response()->json([
			'status_code' => '1',
			'status_message' => "Rating successfully",
		]);
	}

	public function ratingStatus(Request $request, $trip_id)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$trip = Trips::query()->find($trip_id);

		if (! $trip || (int) $trip->driver_id !== (int) $user_details->id) {
			return response()->json([
				'show_rating' => false,
				'trip_id' => (int) $trip_id,
				'reason' => 'unauthorized_or_missing_trip',
				'already_rated' => false,
			], $trip ? 403 : 404);
		}

		return response()->json($this->driver_rating_eligibility->status((int) $trip_id));
	}

	public function rateTrip(Request $request, $trip_id)
	{
		$request->merge([
			'trip_id' => $trip_id,
			'user_type' => 'Driver',
		]);

		return $this->trip_rating($request);
	}

	/**
	 * Display the Rider Feedback
	 * @param  Post method request inputs
	 *
	 * @return Response Json
	 */
	public function rider_feedback(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
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
		$per_page = $request->per_page ?? 50;
		$rider_comments = DB::table('rating')
		->select(DB::raw('DATE_FORMAT(created_at, "%d %M %Y") AS date,rider_rating,rider_comments,trip_id'))
		->where('driver_id', $user_details->id)
		->where('rider_rating', '>', 0)
		->orderBy('trip_id', 'DESC')
		->paginate($per_page);

		return response()->json([
			'status_code' 	 => '1',
			'status_message' => __('messages.api.listed_successfully'),
			'rider_feedback' => $rider_comments->items(),
			'current_page'	 =>  $rider_comments->currentPage(),
			'total_pages'	 =>  $rider_comments->lastPage(),
		]);
	}

}
