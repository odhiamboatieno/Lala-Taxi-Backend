<?php

/**
 * Trip Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Trip
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cancel;
use App\Models\DriverLocation;
use App\Models\ScheduleRide;
use App\Models\PoolTrip;
use App\Models\Trips;
use App\Models\CarType;
use App\Models\User;
use App\Models\Rating;
use App\Models\ManageFare;
use App\Models\Request as RideRequest;
use App\Models\UsersPromoCode;
use App\Models\CancelReason;
use App\Models\FavoriteDriver;
use App\Models\FilterObject;
use App\Models\ScheduleAssignmentAttempt;
use App\Models\TollReason;
use App\Models\ApiCredentials;
use App\Models\TripTollReason;
use App\Models\Fees;
use App\Mail\MailQueue;
use DateTime;
use DB;
use File;
use JWTAuth;
use Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use App\Services\DriverScore\DriverScoreService;
use App\Services\Notifications\NotificationManager;

class TripController extends Controller
{
	public function __construct()
	{
		\Log::error('TripController start');

		$this->request_helper = resolve(\App\Http\Helper\RequestHelper::class);
		$this->invoice_helper = resolve(\App\Http\Helper\InvoiceHelper::class);
		$this->driver_score_service = resolve(DriverScoreService::class);
		$this->paginate_limit = 50;
	}

	protected function checkPendingTrips($user)
	{
		$column = strtolower($user->user_type) == 'rider' ? 'user_id':'driver_id';
		$incomplete_trips = DB::table('trips')->select('id')->where($column,$user->id)
		// ->whereNotIn('status',['Cancelled','Completed','Null'])
		->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])
		->whereNotNull('status')->orderBy('id','desc')->first();

		return $incomplete_trips->id ?? 0;
	}


	protected function getTripDetails($trip_id,$user,$is_trip_id=false,$response=true,$riders_only=false) {

		if(!is_object($trip_id)) {
			$trip = Trips::with('car_type.manage_fare','users','driver.driver_location')->where('id', $trip_id)->first();
		} else {
			$trip = $trip_id;
			if(isset($trip_id->booking_type))
				$trip = ScheduleRide::with('users','driver.driver_location')->where('id', $trip_id->id)->first();
		}

		$driver = $trip->driver ?? '';
		$driver_location = $driver->driver_location ?? '';
		$arrival_time = -1;
		
		$invoice_data = ['user_id' => $user->id,'user_type' => $user->user_type];
		$trip_data=[];
	if($user->user_type == 'Rider') {
		$trip_data = [
			'driver_latitude' 	=> $driver->driver_location->latitude ?? '',
			'driver_longitude' 	=> $driver->driver_location->longitude ?? '',
			'vehicle_number' 	=> $driver->driver_documents->vehicle_number ?? '',
			'vehicle_name' 		=> $driver->driver_documents->vehicle_name ?? '',
			//'arrival_time' 		=> $arrival_time,
		];
	}
	if(!isset($trip->booking_type)) {
			if($user->user_type == 'Rider') 
				$trip_data['pool_id'] = $trip->pool_id;
			$trip_data['is_pool'] = $trip->pool_id>0 ? true:false;
			$trip_data['seats']   = $trip->seats;
		} else {
			if($user->user_type == 'Rider') 
			 $trip_data['pool_id'] = '';
			$trip_data['is_pool'] = false;
		}
		// $trip_data['seats']   = 0;
		// if(!isset($trip->booking_type)) {
		// //	$trip_data['pool_id'] = $trip->pool_id;
		// 	$trip_data['is_pool'] = $trip->pool_id>0 ? true:false;
		// } else {
		// 	//$trip_data['pool_id'] = '';
		// 	$trip_data['is_pool'] = false;
		// }

		// Set Waiting time and charge based on car type when trip is not completed
		/*if(in_array($trip->status,['Completed','Rating'])) {
			$trip_data['waiting_charge'] = $trip->waiting_charge;
		}*/

		if($user->user_type == 'Rider') {
			$driver_rating = getRiderRating($trip->driver_id);
			$final_promo_details= $this->invoice_helper->getUserPromoDetails($user->id);
			$is_favorite_driver = false;
			if (site_settings('favorite_driver_enabled') == '1' && $trip->driver_id) {
				$is_favorite_driver = FavoriteDriver::query()
					->where('rider_id', $user->id)
					->where('driver_id', $trip->driver_id)
					->exists();
			}
			$user_data = [
				'driver_id' 		=> $driver->id ?? '',
				'driver_name' 		=> trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')),
				'mobile_number' 	=> $driver->phone_number ?? '',
				'driver_thumb_image'=> @$driver->profile_picture->src ?? url('images/user.jpeg'),
				'rating'	 		=> $driver_rating,
			    'promo_details' 	=> $final_promo_details,
				'favorite_driver_enabled' => site_settings('favorite_driver_enabled')=='1',
				'is_favorite_driver' => $is_favorite_driver,
			];
		} else {
			$user_data = [];
		}

		$other_data = [
			// 'paypal_mode' 	=> PAYPAL_MODE,
			// 'paypal_app_id' => PAYPAL_CLIENT_ID,
		];

		$trip_id = $trip->id;
		if($trip->pool_id>0 && !$is_trip_id) {
			$pool_trip = PoolTrip::with(['trips' => function($query) use($user) {
				if($user->user_type == 'Rider') {
					$query->where('user_id',$user->id);
				} else {
					$query->with('driver_location')->select(
						\DB::raw(
							'*,
							(CASE 
							WHEN status="Scheduled" OR status="Begin trip" THEN ( 6371 * acos( cos( radians(' . $query->get()[0]->driver_location->latitude . ') ) * cos( radians( pickup_latitude ) ) * cos(radians( pickup_longitude ) - radians(' . $query->get()[0]->driver_location->longitude . ') ) + sin( radians(' . $query->get()[0]->driver_location->latitude . ') ) * sin( radians( pickup_latitude ) ) ) )
							ELSE ( 6371 * acos( cos( radians(' . $query->get()[0]->driver_location->latitude . ') ) * cos( radians( drop_latitude ) ) * cos(radians( drop_longitude ) - radians(' . $query->get()[0]->driver_location->longitude . ') ) + sin( radians(' . $query->get()[0]->driver_location->latitude . ') ) * sin( radians( drop_latitude ) ) ) ) END) as distance'
						)
					)
					->orderBy('distance','asc');
				}
			}])->find($trip->pool_id);

			$trips = $pool_trip->trips
			// ->whereNotIn('status',['Cancelled','Completed','Null'])
			->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])
			->whereNotNull('status');
		} else {
			$trips[] = $trip;
		}

		$data = [];
		
		if(count($trips)) {
			foreach($trips as $tkey=>$trip) {
				$normalizedStatus = strtolower((string) ($trip->status ?? ''));
				if (isset($trip->driver) && in_array($normalizedStatus, ['rating', 'payment', 'completed'], true) && $this->invoice_helper->needsRealtimeRecalculation($trip)) {
					$trip = $this->invoice_helper->calculation([
						'trip_id' => $trip->id,
						'user_id' => $user->id,
						'user_type' => $user->user_type,
						'save_to_trip_table' => 0,
					]);
				}

				// $data[$tkey]['distance']     = $trip->distance;
				$data[$tkey]['id']     = $trip->users->id;
		        $data[$tkey]['name']   = $trip->users->first_name.' '.$trip->users->last_name;
		        $data[$tkey]['image']  = $trip->users->profile_picture->src ?? url('images/user.jpeg');
		        $data[$tkey]['mobile_number'] = $trip->users->phone_number;
		        $data[$tkey]['otp'] 	= $trip->otp;
		        $data[$tkey]['status'] = $trip->status ?? '';
		        $data[$tkey]['trip_id']= $trip->id;
		        $data[$tkey]['pickup'] = $trip->pickup_location;
		        $data[$tkey]['drop']   = $trip->drop_location;
		        $data[$tkey]['pickup_lat'] = $trip->pickup_latitude;
		        $data[$tkey]['pickup_lng'] = $trip->pickup_longitude;
		        $data[$tkey]['drop_lat']   = $trip->drop_latitude;
		        $data[$tkey]['drop_lng']   = $trip->drop_longitude;
		        $data[$tkey]['request_id'] = $trip->request_id;
		        $data[$tkey]['trip_path'] = $trip->trip_path;
		        $data[$tkey]['map_image'] = $trip->map_image;
		        if(!isset($trip->booking_type)) {
		        	$car_type = $trip->car_type;
		        } else {
		        	$car_type = CarType::find($trip->car_id);
		        }

		        if($user->user_type == 'Rider') {
		       	  $data[$tkey]['waiting_time'] = strval(optional(optional($car_type)->manage_fare)->waiting_time);
		       	  $data[$tkey]['free_wait_time'] = strval(optional(optional($car_type)->manage_fare)->waiting_time);
		       	  $data[$tkey]['waiting_charge'] = optional(optional($car_type)->manage_fare)->waiting_charge;
		       	  $data[$tkey]['arrive_time'] = $trip->arrive_time ? date('Y-m-d H:i:s', strtotime($trip->arrive_time)) : '';
		   		}
		       // $data[$tkey]['total_fare'] = $total_fare;
		        $data[$tkey]['booking_type'] = 'Manual Booking';
		        //$data[$tkey]['car_type'] = $car_type->car_name;
		        $data[$tkey]['schedule_display_date'] = $trip->schedule_display_date;
		        //$data[$tkey]['invoice'] = $invoice;
		        
		        $data[$tkey]['car_type'] = optional($car_type)->car_name;
		        $data[$tkey]['car_active_image']= optional($car_type)->active_image;
		      

		        $data[$tkey]['total_time'] 	= $trip->total_time;
		        $data[$tkey]['total_km'] 	= number_format($this->invoice_helper->sanitizeTripDistanceKm($trip), 2, '.', '');
		       // $data[$tkey]['begin_trip'] 	= $trip->begin_trip;
		       // $data[$tkey]['end_trip'] 	= $trip->end_trip;
		        if($user->user_type == 'Rider') 
		      	   $data[$tkey]['payment_mode'] = $trip->payment_mode;
		       // $data[$tkey]['payment_status'] = $trip->payment_status;

		        if(!isset($trip->booking_type)) {
		        	$symbol = html_entity_decode($trip->currency->symbol);
		        } else {
		        	$symbol = html_entity_decode($trip->currency_symbol);
		        }
		        
		        $data[$tkey]['currency_symbol']= $symbol;

		        $subtotal_fare = checkIsCashTrip($trip->payment_mode) ? $trip->total_fare : $trip->subtotal_fare;
		        //$data[$tkey]['sub_total_fare'] = $subtotal_fare;

		        if(!isset($trip->booking_type)) {
					$total_fare = $trip->admin_total_amount;
					if(isset($trip->driver) && $trip->driver->company_id != 1 && checkIsCashTrip($trip->payment_mode) && $trip->total_fare == 0) {
						$total_fare = $trip->company_driver_earnings;
					}
					
				} else {
					$total_fare = $trip->fare_estimation ?? '0';
				}
				$fareMode = strtolower((string) data_get($trip->fare_estimation, 'fare_mode', site_settings('fare_mode') ?: 'taximeter'));
				if ((float) $total_fare <= 0 && $fareMode === 'pre-calculated') {
					$total_fare = data_get($trip->fare_estimation, 'estimated_fare', 0);
				}
				$data[$tkey]['total_fare'] = number_format((float) $total_fare, 2, '.', '');

				$driverEarningsValue = (float) ($trip->company_driver_earnings ?? 0);
				if ($driverEarningsValue <= 0) {
					$driverEarningsValue = (float) ($trip->driver_payout ?? 0);
				}
				$driver_earnings = $symbol.number_format($driverEarningsValue,2);
				if(!in_array($trip->status, ['Rating', 'Payment', 'Completed'], true)){
		        		$driver_earnings=$symbol.'0.00';
		        		$driverEarningsValue = 0;
		        }
		        $data[$tkey]['driver_earnings']= $driver_earnings;
		        $driverPayoutValue = checkIsCashTrip($trip->payment_mode) ? 0 : (float) ($trip->driver_payout ?? 0);
		        if ($driverPayoutValue <= 0 && !checkIsCashTrip($trip->payment_mode)) {
		        	$driverPayoutValue = $driverEarningsValue;
		        }
		        $data[$tkey]['driver_payout'] = number_format($driverPayoutValue, 2, '.', '');

		        if(!isset($trip->booking_type)) {
		        	$data[$tkey]['booking_type'] = @$trip->ride_request->schedule_ride->booking_type ?? '';
		        } else {
		        	$data[$tkey]['booking_type'] = 'Manual Booking';
		        }

		        $data[$tkey]['created_at'] 	= $trip->created_at ? $trip->created_at->format('Y-m-d H:i:s'):'';
		       // $data[$tkey]['is_current_trip']= $trip->id==$trip_id;
		        $data[$tkey]['rating'] 	= getDriverRating($trip->users->id);
		        $data[$tkey]['schedule_display_date'] = $trip->schedule_display_date;

		        if(isset($trip->driver))
		        	$invoice = $this->invoice_helper->formatInvoice($trip,$invoice_data);
		        else
		        	$invoice = [];
		        
		        $data[$tkey]['invoice'] = $invoice;
		        $data[$tkey]['otp_enabled'] = site_settings('otp_verification')=='1' ? true:false;
			}
		}

		$trip_data['riders'] = array_values($data);

		if($riders_only)
			return $trip_data['riders'];

		if($response==false)
			return array_merge($trip_data,$user_data,$other_data);

		return [
			'status' => true,
			'data' => array_merge($trip_data,$user_data,$other_data),
		];
	}

	protected function getPendingScheduleRide(int $scheduleRideId, User $driver): ?ScheduleRide
	{
		return ScheduleRide::with(['users', 'driver', 'schedule_cancel.cancel_reasons'])
			->where('id', $scheduleRideId)
			->where('driver_id', $driver->id)
			->where('status', 'Pending')
			->first();
	}

	protected function formatPendingScheduleForDriver(ScheduleRide $scheduleRide): array
	{
		$rider = $scheduleRide->users;
		$driver = $scheduleRide->driver;
		$scheduledAt = trim(($scheduleRide->schedule_date ?? '').' '.($scheduleRide->schedule_time ?? ''));
		$dispatchAt = $scheduleRide->dispatch_at ? $scheduleRide->dispatch_at->toDateTimeString() : null;

		return [
			'trip_id' => (string) $scheduleRide->id,
			'schedule_ride_id' => (string) $scheduleRide->id,
			'status' => $scheduleRide->status,
			'assignment_status' => $scheduleRide->assignment_status ?: 'assigned',
			'dispatch_mode' => $scheduleRide->dispatch_mode ?: 'manual',
			'booking_mode' => $scheduleRide->booking_mode ?: 'scheduled',
			'pickup_location' => $scheduleRide->pickup_location,
			'drop_location' => $scheduleRide->drop_location,
			'pickup_latitude' => (string) $scheduleRide->pickup_latitude,
			'pickup_longitude' => (string) $scheduleRide->pickup_longitude,
			'drop_latitude' => (string) $scheduleRide->drop_latitude,
			'drop_longitude' => (string) $scheduleRide->drop_longitude,
			'pickup_lat' => (string) $scheduleRide->pickup_latitude,
			'pickup_lng' => (string) $scheduleRide->pickup_longitude,
			'drop_lat' => (string) $scheduleRide->drop_latitude,
			'drop_lng' => (string) $scheduleRide->drop_longitude,
			'trip_path' => $scheduleRide->trip_path ?: '',
			'vehicle_name' => $scheduleRide->car_name,
			'car_type' => $scheduleRide->car_name,
			'currency_symbol' => $scheduleRide->currency_symbol ?: '',
			'total_fare' => (string) ($scheduleRide->fare_estimation ?? 0),
			'fare_estimation' => (string) ($scheduleRide->fare_estimation ?? 0),
			'scheduled_at' => $scheduledAt,
			'dispatch_at' => $dispatchAt,
			'schedule_date' => $scheduleRide->schedule_date,
			'schedule_time' => $scheduleRide->schedule_time,
			'driver_name' => optional($driver)->first_name ?: '',
			'driver_phone' => optional($driver)->mobile_number ?: '',
			'driver_note' => $scheduleRide->driver_note ?: '',
			'dispatcher_note' => $scheduleRide->dispatcher_note ?: '',
			'priority' => $scheduleRide->priority ?: 'normal',
			'booking_type' => $scheduleRide->booking_type ?: 'Manual Booking',
			'can_cancel' => true,
			'rider' => [
				'id' => (string) optional($rider)->id,
				'name' => trim((optional($rider)->first_name ?: '').' '.(optional($rider)->last_name ?: '')),
				'phone_number' => optional($rider)->mobile_number ?: optional($rider)->phone_number ?: '',
				'country_code' => optional($rider)->country_code ?: '',
			],
		];
	}

	protected function dispatchPendingScheduleReplacement(ScheduleRide $scheduleRide, int $excludedDriverId): array
	{
		if ($scheduleRide->assign_now || $scheduleRide->booking_mode === 'asap') {
			$this->request_helper->find_driver([
				'rider_id' => $scheduleRide->user_id,
				'pickup_latitude' => $scheduleRide->pickup_latitude,
				'pickup_longitude' => $scheduleRide->pickup_longitude,
				'drop_latitude' => $scheduleRide->drop_latitude,
				'drop_longitude' => $scheduleRide->drop_longitude,
				'user_type' => 'rider',
				'car_id' => $scheduleRide->car_id,
				'driver_group_id' => null,
				'pickup_location' => $scheduleRide->pickup_location,
				'drop_location' => $scheduleRide->drop_location,
				'payment_method' => $scheduleRide->payment_method,
				'is_wallet' => $scheduleRide->is_wallet,
				'timezone' => $scheduleRide->timezone,
				'schedule_id' => $scheduleRide->id,
				'additional_fare' => '',
				'location_id' => $scheduleRide->location_id,
				'peak_price' => 0,
				'booking_type' => $scheduleRide->booking_type,
				'driver_id' => 0,
				'exclude_driver_ids' => [$excludedDriverId],
			]);

			$scheduleRide->refresh();

			return [
				'reassigned' => RideRequest::where('schedule_id', $scheduleRide->id)->where('status', 'Pending')->exists(),
				'mode' => 'dispatch_request',
				'driver_id' => null,
			];
		}

		$ignoreAssigned = $this->request_helper->ignoreAssigned([
			'pickup_latitude' => $scheduleRide->pickup_latitude,
			'pickup_longitude' => $scheduleRide->pickup_longitude,
			'drop_latitude' => $scheduleRide->drop_latitude,
			'drop_longitude' => $scheduleRide->drop_longitude,
			'car_id' => $scheduleRide->car_id,
			'start_date' => $scheduleRide->schedule_date,
			'start_time' => $scheduleRide->schedule_time,
			'schedule_id' => $scheduleRide->id,
		]);

		$ignoreDrivers = collect($ignoreAssigned)
			->push($excludedDriverId)
			->filter(function ($value) {
				return (int) $value > 0;
			})
			->map(function ($value) {
				return (int) $value;
			})
			->unique()
			->values()
			->all();

		$handicap = $childSeat = $requestFrom = '';
		$options = FilterObject::options('rider', $scheduleRide->user_id);
		if (in_array('4', $options)) {
			$requestFrom = '1';
		}
		if (in_array('2', $options)) {
			$handicap = '1';
		}
		if (in_array('3', $options)) {
			$childSeat = '1';
		}

		$minimumTimestamp = now('UTC')->subHours(site_settings('offline_hours'));

		$replacementLocation = DriverLocation::select(
			DB::raw('*, ( 6371 * acos( cos( radians(' . $scheduleRide->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $scheduleRide->pickup_longitude . ') ) + sin( radians(' . $scheduleRide->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance')
		)
			->having('distance', '<=', site_settings('driver_km'))
			->where('car_id', $scheduleRide->car_id)
			->where('status', 'Online')
			->where('updated_at', '>=', $minimumTimestamp)
			->whereNotIn('user_id', $ignoreDrivers)
			->whereHas('users', function ($query) use ($scheduleRide, $handicap, $childSeat, $requestFrom) {
				$query->activeOnlyStrict();
				if ($scheduleRide->company_id) {
					$query->where('company_id', $scheduleRide->company_id);
				}
				$query->whereHas('vehicle', function ($vehicleQuery) use ($handicap, $childSeat, $requestFrom) {
					if ($handicap) {
						$vehicleQuery->whereHas('handicap');
					}
					if ($childSeat) {
						$vehicleQuery->whereHas('child_seat');
					}
					if ($requestFrom) {
						$vehicleQuery->whereHas('female_driver');
					}
				});
			})
			->orderBy('distance', 'ASC')
			->first();

		if (!$replacementLocation) {
			$scheduleRide->assignment_status = 'unassigned';
			$scheduleRide->status = 'Car Not Found';
			$scheduleRide->save();

			return [
				'reassigned' => false,
				'mode' => 'scheduled_assignment',
				'driver_id' => null,
			];
		}

		$replacementDriver = User::find($replacementLocation->user_id);
		if (!$replacementDriver) {
			return [
				'reassigned' => false,
				'mode' => 'scheduled_assignment',
				'driver_id' => null,
			];
		}

		$scheduleRide->driver_id = $replacementDriver->id;
		$scheduleRide->assigned_driver_id = $replacementDriver->id;
		$scheduleRide->assignment_status = 'assigned';
		$scheduleRide->status = 'Pending';
		$scheduleRide->save();

		ScheduleAssignmentAttempt::create([
			'schedule_ride_id' => $scheduleRide->id,
			'driver_id' => $replacementDriver->id,
			'company_id' => $scheduleRide->company_id,
			'dispatch_mode' => $scheduleRide->dispatch_mode ?: 'manual',
			'status' => 'assigned',
			'offered_at' => now(),
			'accepted_at' => now(),
			'timeout_seconds' => $scheduleRide->assignment_timeout_seconds ?: 30,
			'sequence_order' => ((int) $scheduleRide->assignment_attempt_count) + 1,
			'metadata' => [
				'source' => 'driver_pending_cancel_reassignment',
				'reassigned_from_driver_id' => $excludedDriverId,
			],
		]);

		$rider = $scheduleRide->users;
		$pushTitle = __('messages.api.trip_scheduled');
		$pushData['push_title'] = $pushTitle;
		$pushData['data'] = [
			'manual_booking_trip_booked_info' => [
				'date' => $scheduleRide->schedule_date,
				'time' => $scheduleRide->schedule_time,
				'pickup_location' => $scheduleRide->pickup_location,
				'pickup_latitude' => $scheduleRide->pickup_latitude,
				'pickup_longitude' => $scheduleRide->pickup_longitude,
				'rider_first_name' => optional($rider)->first_name,
				'rider_last_name' => optional($rider)->last_name,
				'rider_mobile_number' => optional($rider)->mobile_number,
				'rider_country_code' => optional($rider)->country_code,
			],
		];
		$text = trans('messages.trip_booked_driver_message', [
			'date' => $scheduleRide->schedule_date.' '.$scheduleRide->schedule_time,
			'pickup_location' => $scheduleRide->pickup_location,
			'drop_location' => $scheduleRide->drop_location,
		]);
        resolve(NotificationManager::class)->sendEvent('scheduled.driver.new_trip_request', $replacementDriver, [
            'schedule' => $scheduleRide,
            'customer' => $rider,
            'company_id' => (int) ($scheduleRide->company_id ?? 0),
        ], $pushData['data']);

		return [
			'reassigned' => true,
			'mode' => 'scheduled_assignment',
			'driver_id' => $replacementDriver->id,
			'driver_name' => $replacementDriver->first_name,
		];
	}

	/**
	 * Display the Arrive Now Status
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function arriveNow(Request $request)
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


		if($trip->status=='Cancelled') {
			return response()->json([
				'status_code' 	 => '2',
				'status_message' => 'Trips already cancelled by '. strtolower($trip->cancel->cancelled_by),
			]);
		}


		$user_timezone = $trip->ride_request->timezone;
		if ($user_timezone != '') {
			date_default_timezone_set($user_timezone);
		}

		$arrive_time = new DateTime(date("Y-m-d H:i:s"));
		
		Trips::where('id', $request->trip_id)->update(['arrive_time' => $arrive_time, 'status' => 'Begin trip']);

		if($trip->pool_id>0) {

			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
			$scheduled_trips = $pool_trip->trips->where('status','Scheduled')->count();
			
			if(!$scheduled_trips) {
				// update seats
				$pool_trip->status = 'Begin trip';
				$pool_trip->save();
			}
		}
		

		$push_data['push_title'] = __('messages.api.driver_arrived');
		$push_data['data'] = [
			'arrive_now' => [
				'status' => 'Arrive Now',
				'trip_id' => $request->trip_id,
			]
		];
        resolve(NotificationManager::class)->sendEvent('regular.customer.driver_on_the_way', $trip->users, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ], $push_data['data']);

		$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);

		//if booking is manual booking then send "Driver Arrived" SMS to rider
		if(isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
			/*$fare_details = ManageFare::where('location_id',$schedule_ride->location_id)->where('vehicle_id',$schedule_ride->car_id)->first();
			$waiting_time = $fare_details->waiting_time;
			$waiting_charge = $fare_details->currency_code.' '.$fare_details->waiting_charge;

			$push_title = __('messages.driver_arrive');
	        $text 		= __('messages.driver_arrived').' '.__('messages.api.waiting_charge_apply_after',['amount' => $waiting_charge,'minutes' => $waiting_time]);

	        $push_data['push_title'] = $push_title;
	        $push_data['data'] = array(
	            'custom_message' => array(
	                'title' => $push_title,
	                'message_data' => $text,
	            )
	        );

	        $this->request_helper->checkAndSendMessage($data->users,$text,$push_data);*/
		}

		$trip_detail = $this->getTripDetails($request->trip_id,$user_details);
		return response()->json(array_merge(['status_code' => '1','status_message' => "Success"],$trip_detail['data']));
	}

	/**
	 * Begin Trip From Driver
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function beginTrip(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'trip_id' => 'required|exists:trips,id',
			'begin_latitude' => 'required',
			'begin_longitude' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }
		$pickup_location = $this->request_helper->GetLocation($request->begin_latitude, $request->begin_longitude);

		$user_location = DriverLocation::where('user_id', $user_details->id)->first();

		$trip = Trips::where('id', $request->trip_id)->first();


		if($trip->status=='Cancelled') {
			return response()->json([
				'status_code' 	 => '2',
				'status_message' => 'Trips already cancelled by '.strtolower($trip->cancel->cancelled_by),
			]);
		}

		$user_timezone = $trip->ride_request->timezone;
		if ($user_timezone != '') {
			date_default_timezone_set($user_timezone);
		}

		$begin_time = new DateTime(date("Y-m-d H:i:s"));

		Trips::where('id', $request->trip_id)->update(['status' => 'End trip', 'begin_trip' => $begin_time, 'pickup_latitude' => $request->begin_latitude, 'pickup_longitude' => $request->begin_longitude, 'pickup_location' => $pickup_location, 'otp' => 0]);

		$trip = Trips::where('id', $request->trip_id)->first();

		if($trip->pool_id>0) {

			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip'])->count();
			
			if(!$trips) {
				// update status
				$pool_trip->status = 'End trip';
				$pool_trip->save();
			}
		}

		$push_data['push_title'] = __('messages.api.trip_begin_by_driver');
        $push_data['data'] = [
            'begin_trip' => [
            	'trip_id' => $request->trip_id
            ]
        ];
        resolve(NotificationManager::class)->sendEvent('regular.customer.trip_started', $trip->users, [
            'trip' => $trip->loadMissing(['users', 'driver.company', 'car_type']),
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
        ], $push_data['data']);

		$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);
		//if booking is manual booking then send "Trip Began" SMS to rider
		if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
			$push_title = __('messages.trip_begined');
	        $text 		= __('messages.trip_begined');

	        $push_data['push_title'] = $push_title;
	        $push_data['data'] = [
	            'custom_message' => [
	                'title' => $push_title,
	                'message_data' => $text,
	            ]
	        ];

	        $this->request_helper->checkAndSendMessage($trip->users,$text,$push_data);
		}

		$trip_detail = $this->getTripDetails($request->trip_id,$user_details);
		return response()->json(array_merge(['status_code' => '1','status_message' => "Trip Started"],$trip_detail['data']));
	}

	/**
	 * End Trip From Driver
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function end_trip(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'trip_id' => 'required|exists:trips,id',
			'end_latitude' => 'required',
			'end_longitude' => 'required',
		];

		$apply_extra_fee = fees('additional_fee');
        if($apply_extra_fee == 'Yes') {
        	$rules = [
				'trip_id' => 'required|exists:trips,id',
				'end_latitude' => 'required',
				'end_longitude' => 'required',
				'toll_reason_id' => 'required_with:toll_fee|exists:toll_reasons,id',
				'toll_reason' => 'required_if:toll_reason_id,1',
				'toll_fee' => 'required_with:toll_reason_id',
			];
        }

		$messages = [
			'toll_reason.required' => __('messages.api.toll_reason').' '. __('messages.field_is_required'),
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		$trip = Trips::find($request->trip_id);

		$toll_fee = 0;
		if($request->toll_fee) {
			$toll_fee = $request->toll_fee;
		}

		// Final Distance calcualtion
		$driver_location = DriverLocation::where('user_id', $user_details->id)->first();

		$user_timezone = $trip->ride_request->timezone;
		if($user_timezone != '') {
			date_default_timezone_set($user_timezone);
		}

		if($request->trip_id) {
			$trip->total_km = $request->total_km;
			$total_km = number_format($this->invoice_helper->sanitizeTripDistanceKm($trip),5,'.','0');
			$data = [
				'user_id' 	=> $user_details->id,
				'latitude' 	=> $request->end_latitude,
				'longitude' => $request->end_longitude,
			];

			DriverLocation::updateOrCreate(['user_id' => $user_details->id], $data);
		}

		$end_time = new DateTime(date("Y-m-d H:i:s"));
		$drop_location = $this->request_helper->GetLocation($request->end_latitude, $request->end_longitude);

		if ($request->hasFile('image')) {
			$image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);

			$target_dir = '/images/map/'.$trip->id;
			$image 		= $request->file('image');
            $extension 	= $image->getClientOriginalExtension();
            $file_name 	= substr(md5(uniqid(rand(), true)), 0, 8).".".$extension;
            $options 	= compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($image,$options);
            if(!$upload_result['status']) {
            	return response()->json([
					'status_code' => "0",
					'status_message' => $upload_result['status_message'],
				]);
            }
            $image_url = url('/').'/images/map/'.$trip->id.'/'.$file_name;
		}

		$status = 'Payment';
		$trip_data = [
			'drop_latitude'	=> $request->end_latitude,
			'drop_longitude'=> $request->end_longitude,
			'drop_location' => $drop_location,
			'status' 		=> $status,
			'end_trip' 		=> $end_time,
			'completed_at' => $end_time,
			'total_km' 		=> $total_km,
			'map_image' 	=> $file_name ?? '',
			'toll_fee'		=> $toll_fee,
			'is_calculation'=> 0,
		];

		if ($request->toll_reason_id) {
			$trip_data['toll_reason_id'] = $request->toll_reason_id;
		}

	  	Trips::where('id', $request->trip_id)->update($trip_data);
	  	$trip = Trips::with(['users', 'driver.company', 'car_type', 'currency', 'ride_request'])->find($request->trip_id);
	  	if($trip && $trip->is_calculation == 0) {
	  		$this->invoice_helper->calculation([
	  			'trip_id' => $trip->id,
	  			'user_id' => $user_details->id,
	  			'user_type' => 'Driver',
	  			'save_to_trip_table' => 1,
	  		]);
	  		$trip = Trips::with(['users', 'driver.company', 'car_type', 'currency', 'ride_request'])->find($request->trip_id);
	  	}
	  	if ($request->toll_reason_id == 1 && $request->toll_reason) {
	  		$trip_toll_reason = new TripTollReason();
	  		$trip_toll_reason->trip_id = $request->trip_id;
	  		$trip_toll_reason->reason = $request->toll_reason;
	  		$trip_toll_reason->save();
	  	}
	  	
		$driver_thumb_image = @$trip->driver_thumb_image != '' ? $trip->driver_thumb_image : url('images/user.jpeg');
		$amountToPay = (float) ($trip->total_fare ?? 0);
		$symbol = html_entity_decode(optional($trip->currency)->symbol ?? $trip->currency_symbol ?? '');
		$amountToPayDisplay = $symbol.number_format($amountToPay, 2);
		$push_data['data'] = [
			'end_trip' => [
				'trip_id' => $request->trip_id,
				'driver_thumb_image' => $driver_thumb_image,
				'total_fare' => number_format($amountToPay, 2, '.', ''),
				'amount_to_pay' => $amountToPayDisplay,
				'status' => 'Payment',
			],
			'trip_payment_due' => [
				'trip_id' => $request->trip_id,
				'total_fare' => number_format($amountToPay, 2, '.', ''),
				'amount_to_pay' => $amountToPayDisplay,
				'status' => 'Payment',
			],
		];
		$push_data['push_title'] = __('messages.api.trip_ended_by_driver');

		resolve(NotificationManager::class)->sendEvent('regular.customer.trip_payment_due', $trip->users, [
            'trip' => $trip,
            'driver' => $trip->driver,
            'company' => optional($trip->driver)->company,
			'amount_to_pay' => $amountToPay,
        ], $push_data['data'], [
			'channels' => ['push' => true, 'sms' => false],
		]);
		if(optional($trip->users)->email) {
			Mail::to($trip->users->email, trim(($trip->users->first_name ?? '').' '.($trip->users->last_name ?? '')))
				->queue(new MailQueue([
					'locale' => $trip->users->language ?? app()->getLocale(),
					'subject' => 'Pay your driver '.$amountToPayDisplay,
					'first_name' => $trip->users->first_name ?? '',
					'content' => '<p>Your trip #'.$trip->id.' is complete.</p><p><strong>Pay your driver '.$amountToPayDisplay.'.</strong></p>',
					'view_file' => 'emails.custom_email',
				]));
		}

		//if booking is manual booking then send "Trip Ended" SMS to rider
		if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
			$data = [
				'trip_id' 	=> $request->trip_id,
				'user_type' => $request->user_type,
				'user_id' 	=> $user_details->id,
				'save_to_trip_table' => 0,
			];

    		$push_title = __('messages.trip_ended');
	        $text 		= __('messages.trip_ended');

	        $push_data['push_title'] = $push_title;
	        $push_data['data'] = [
	            'custom_message' => [
	                'title' => $push_title,
	                'message_data' => $text,
	            ]
	        ];

	        $this->request_helper->checkAndSendMessage($trip->users,$text,$push_data);
		}

		if($driver_location->status != 'Pool Trip') {
			DriverLocation::where('user_id', $user_details->id)->update(['status' => 'Online']);
		} else {

			$pool_trip = PoolTrip::with('trips')->find($driver_location->pool_trip_id);
			
			// update seats
			$pool_trip->seats = $pool_trip->seats + $trip->ride_request->seats;

			// get pending pool trips count
			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip'])->count();
			if(!$trips) {
				// update status
				$pool_trip->status = 'Payment';
				// update driver location once pool trip end
				DriverLocation::where('user_id', $user_details->id)->update(['pool_trip_id' => NULL,'status' => 'Online']);
			}
			$polyline = $this->request_helper->GetPolyline($pool_trip->pickup_latitude, $pool_trip->drop_latitude, $pool_trip->pickup_longitude, $pool_trip->drop_longitude);
			$pool_trip->trip_path = $polyline;
			$pool_trip->save();
		}

		$this->driver_score_service->recalculateDriver($trip->driver_id, [
			'trigger' => 'trip_completed',
			'trip_id' => $trip->id,
		]);

		$trip_detail = $this->getTripDetails($request->trip_id,$user_details);
		return response()->json(array_merge(['status_code'=>'1','status_message'=>"Trip Completed",'image_url'=>$image_url ?? ''],$trip_detail['data']));
	}

	/**
	 * Display the Past Trips of Rider
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function get_past_trips(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'page' => 'required|min:1',
		];

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }
        
        Trips::$withoutAppends = true;
		$trips = Trips::getTripLists($user_details,$this->paginate_limit,['Completed','Cancelled']);

		if($trips->total()==0 || !count($trips->items()) ) {
			return response()->json([
				'status_code' 	=> '0',
				'status_message'=> trans('messages.api.no_data_found'),
			]);
		}

		return response()->json([
			'status_code'	=>	'1',
			'status_message'=>	__('messages.api.listed_successfully'),
			'current_page'	=>  $trips->currentPage(),
			'total_pages'	=>  $trips->lastPage(),
			'data'			=>	$trips->items(),
		]);
	}

	/**
	 * Display the Upcoming Trips of Rider
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function get_upcoming_trips(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'page' => 'required|min:1',
		];

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		Trips::$withoutAppends = true;
		$trips = Trips::getTripLists($user_details,$this->paginate_limit / 2,['Completed','Cancelled'],false);

		ScheduleRide::$withoutAppends = true;
		$schedule = ScheduleRide::getTripLists($user_details,$this->paginate_limit - count($trips),'Pending');

		$total = $trips->total() + $schedule->total();
        $perPage = $trips->perPage() + $schedule->perPage();
        $items = array_merge($trips->items(), $schedule->items());
        $trips = new LengthAwarePaginator($items, $total, $perPage);
		
		if($trips->total()==0) {
			return response()->json([
				'status_code' 	=> '0',
				'status_message'=> trans('messages.api.no_data_found'),
			]);
		}

		return response()->json([
			'status_code'		=>	'1',
			'status_message'	=>	__('messages.api.listed_successfully'),
			'current_page'		=>  $trips->currentPage(),
			'total_pages'		=>  $trips->lastPage(),
			'data'				=>	$trips->items(),
		]);
	}

	/**
	 * Display the Pending Trips For the Driver
	 * 
	 * @param Get method request inputs
	 * @return Response Json
	 */
	public function get_pending_trips(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'page' => 'required|min:1',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		ScheduleRide::$withoutAppends = true;
		$trips = ScheduleRide::getTripLists($user_details,$this->paginate_limit,'Pending');

		if($trips->isEmpty()) {
			return response()->json([
				'status_code' 		=> '0',
				'status_message' 	=> trans('messages.api.no_data_found'),
			]);
		}

		return response()->json([
			'status_code'		=>	'1',
			'status_message'	=>	__('messages.api.listed_successfully'),
			'current_page'		=>  $trips->currentPage(),
			'total_pages'		=>  $trips->lastPage(),
			'data'				=>	$trips->items(),
		]);
	}

	public function get_pending_trip_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$validator = Validator::make($request->all(), [
			'trip_id' => 'required|exists:schedule_ride,id',
		]);

		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$scheduleRide = $this->getPendingScheduleRide((int) $request->trip_id, $user_details);
		if(!$scheduleRide) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.api.no_trips_found'),
			]);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => __('messages.api.listed_successfully'),
			'data' => $this->formatPendingScheduleForDriver($scheduleRide),
		]);
	}

	public function cancel_pending_trip(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
			'trip_id' => 'required|exists:schedule_ride,id',
			'cancel_reason_id' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);
		$validator->after(function ($validator) use ($request) {
			$exists = CancelReason::active()
				->where('cancelled_by', 'Driver')
				->where('id', $request->cancel_reason_id)
				->exists();

			if (!$exists) {
				$validator->errors()->add('cancel_reason_id', __('messages.api.reason_inactive_admin'));
			}
		});

		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();
		if(!$user) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}

		$scheduleRide = $this->getPendingScheduleRide((int) $request->trip_id, $user);
		if(!$scheduleRide) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.api.no_trips_found'),
			]);
		}

		$cancelReason = CancelReason::find($request->cancel_reason_id);
		$cancelComments = trim((string) ($request->cancel_comments ?? ''));
		$driverNote = trim(
			'Driver '.trim($user->first_name.' '.$user->last_name).' cancelled scheduled assignment on '.now()->format('Y-m-d H:i:s').
			' ['.optional($cancelReason)->reason.']'.
			($cancelComments !== '' ? ' - '.$cancelComments : '')
		);

		ScheduleAssignmentAttempt::create([
			'schedule_ride_id' => $scheduleRide->id,
			'driver_id' => $user->id,
			'company_id' => $scheduleRide->company_id,
			'dispatch_mode' => $scheduleRide->dispatch_mode ?: 'manual',
			'status' => 'rejected',
			'offered_at' => now(),
			'rejected_at' => now(),
			'rejection_reason' => optional($cancelReason)->reason,
			'timeout_seconds' => $scheduleRide->assignment_timeout_seconds ?: 30,
			'sequence_order' => ((int) $scheduleRide->assignment_attempt_count) + 1,
			'metadata' => [
				'source' => 'driver_pending_cancel',
				'cancel_reason_id' => $request->cancel_reason_id,
				'cancel_comments' => $cancelComments,
			],
		]);

		RideRequest::where('schedule_id', $scheduleRide->id)
			->where('driver_id', $user->id)
			->whereIn('status', ['Pending'])
			->update(['status' => 'Cancelled']);

		$scheduleRide->driver_id = 0;
		$scheduleRide->assigned_driver_id = null;
		$scheduleRide->assignment_status = 'reassigned';
		$scheduleRide->last_assignment_attempt_at = now();
		$scheduleRide->assignment_attempt_count = ((int) $scheduleRide->assignment_attempt_count) + 1;
		$scheduleRide->driver_note = $driverNote;
		$scheduleRide->status = 'Pending';
		$scheduleRide->save();

		DriverLocation::where('user_id', $user->id)
			->whereNotIn('status', ['Trip', 'Pool Trip'])
			->update(['status' => 'Online', 'pool_trip_id' => null]);

		$reassignment = $this->dispatchPendingScheduleReplacement($scheduleRide, $user->id);
		$scheduleRide->refresh();

		return response()->json([
			'status_code' => '1',
			'status_message' => $reassignment['reassigned']
				? 'Scheduled trip released and reassigned successfully.'
				: 'Scheduled trip released. No replacement driver is currently available.',
			'data' => array_merge(
				$this->formatPendingScheduleForDriver($scheduleRide),
				[
					'reassigned' => $reassignment['reassigned'],
					'reassignment_mode' => $reassignment['mode'],
					'replacement_driver_id' => $reassignment['driver_id'],
					'replacement_driver_name' => $reassignment['driver_name'] ?? '',
				]
			),
		]);
	}

	/**
	 * Display the Completed Trips
	 * 
	 * @param Get method request inputs
	 * @return Response Json
	 */
	public function get_completed_trips(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'page' => 'required|min:1',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		Trips::$withoutAppends = true;
		$trips = Trips::getTripLists($user_details,$this->paginate_limit,['Completed','Cancelled']);
		
		if($trips->total()==0 || !count($trips->items()) ) {
			return response()->json([
				'status_code' 	 => '0',
				'status_message' => trans('messages.api.no_data_found'),
			]); 
		}

		return response()->json([
			'status_code'		=>	'1',
			'status_message'	=>	__('messages.api.listed_successfully'),
			'current_page'		=>  $trips->currentPage(),
			'total_pages'		=>  $trips->lastPage(),
			'data'				=>	$trips->items(),
		]);
	}

	/**
	 * Map Image upload
	 * @param  Post method request inputs
	 *
	 * @return Response Json
	 */
	public function map_upload(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();
		$rules = [
			'trip_id' => 'required|exists:trips,id',
			'image' => 'required',
			'token' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		$user_id = $user->id;
		$trip_id = $request->trip_id;
		$image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
		$target_dir = '/images/map/'.$trip_id;

		if ($request->hasFile('image')) {
			$image = $request->file('image');

			$extension = $image->getClientOriginalExtension();
			$file_name = substr(md5(uniqid(rand(), true)), 0, 8).".".$extension;
			
	        $options = compact('target_dir','file_name');

	        $upload_result = $image_uploader->upload($image,$options);
	        if(!$upload_result['status']) {
	            return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $upload_result['status_message'],
				]);
	        }

	        Trips::where('id', $request->trip_id)->update(['map_image' => $file_name]);

			$image_url = url('/') . '/images/map/' . $trip_id . '/' . $file_name;

			return response()->json([
				'status_code' 		=> "1",
				'status_message' 	=> "Upload Successfully",
				'image_url' 		=> $image_url,
			]);
	    }
	}

	/**
	 * Trip Cancel by Driver or Rider
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */
	public function cancel_trip(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'trip_id' => 'required',
			'cancel_reason_id' => 'required',
		];

		$messages = [
			'user_type.required' => trans('messages.required.user_type') . ' ' . trans('messages.field_is_required') . '',
			'trip_id.required' => trans('messages.required.trip_id') . ' ' . trans('messages.field_is_required') . '',
			'cancel_reason_id.required' => trans('messages.required.cancel_reason_id') . ' ' . trans('messages.field_is_required') . '',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->after(function ($validator) use ($request) {
			$cancelled_by = 'Driver';
			if ($request->user_type == 'Rider' || $request->user_type == 'rider') {
				$cancelled_by = 'Rider';
			}

			$cancel_reason_exists= CancelReason::active()->where('cancelled_by',$cancelled_by)->where('id',$request->cancel_reason_id)->exists();
	        if (!$cancel_reason_exists) {
	            $validator->errors()->add('cancel_reason_id', __('messages.api.reason_inactive_admin'));
	        }
	    });

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

		$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

		if($user == '') {
			return response()->json([
				'status_code' 	 => '0',
				'status_message' => "Invalid credentials",
			]);
		}

		$cancelled_id = Trips::where('id', $request->trip_id)->first();

		if($cancelled_id->status=='Cancelled') {
			return response()->json([
				'status_code' 	 => '2',
				'status_message' => 'Trips already cancelled',
			]);
		}

		$user_type = strtolower($request->user_type);
		if ($user_type == 'rider') {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $user_details->id,
				'cancel_comments' => @$request->cancel_comments != '' ? $request->cancel_comments : '',
				'cancelled_by' => 'Rider',
				'cancel_reason_id' => @$request->cancel_reason_id,

			];

			Cancel::updateOrCreate(['trip_id' => $request->trip_id], $data);
			$push_user = $cancelled_id->driver;
			$push_title = __('messages.api.trip_cancelled_by_rider');
		}
		else {
			$data = [
				'trip_id' => $request->trip_id,
				'user_id' => $user_details->id,
				'cancel_reason_id' => $request->cancel_reason_id,
				'cancel_comments' => @$request->cancel_comments != '' ? $request->cancel_comments : '',
				'cancelled_by' => 'Driver',
			];

			Cancel::updateOrCreate(['trip_id' => $request->trip_id], $data);
			$push_user = $cancelled_id->users;
			$push_title = __('messages.api.trip_cancelled_by_driver');
		}

		Trips::where('id', $request->trip_id)->update(['status' => 'Cancelled', 'payment_status' => 'Trip Cancelled']);

		if($cancelled_id->pool_id > 0) {
			$pool_trip = PoolTrip::find($cancelled_id->pool_id);
			$pool_trip->seats = $pool_trip->seats + $cancelled_id->ride_request->seats;

			$pending_count 	= $pool_trip->trips->whereNotIn('status',['Payment','Rating','Completed','Cancelled'])->count();
			$completed_count= $pool_trip->trips->whereIn('status',['Payment','Rating','Completed'])->count();

			if($pending_count == 0) {
				DriverLocation::where('user_id', $cancelled_id->driver_id)->update(['status' => 'Online','pool_trip_id' => NULL]);
				if(!$completed_count) {
					$pool_trip->status = 'Cancelled';
				}
			}

			$pool_trip->save();
		} else {
			DriverLocation::where('user_id', $cancelled_id->driver_id)->update(['status' => 'Online','pool_trip_id' => NULL]);
		}

		$trip_riders = $this->getTripDetails($request->trip_id,$user_details,0,0,1);
		
		// push notification
		$push_data['data'] = [
			'cancel_trip' => [
				'trip_id' => $request->trip_id,
				//'trip_riders' => $trip_riders,
				'status' => 'Cancelled',
			]
		];
		$push_data['push_title'] = $push_title;
		
		// Send push notification
		$this->request_helper->SendPushNotification($push_user,$push_data);

		if (strtolower((string) $request->user_type) === 'driver') {
			$this->driver_score_service->logEvent($cancelled_id->driver_id, [
				'event_key' => 'trip_cancelled_'.$cancelled_id->id,
				'trip_id' => $cancelled_id->id,
				'source_type' => 'trip',
				'event_type' => 'driver_cancelled_trip',
				'title' => 'Driver cancelled a trip',
				'description' => $data['cancel_comments'] ?: 'Driver cancelled the accepted trip.',
				'impact_score' => -3,
				'impact_direction' => 'negative',
				'metadata' => [
					'cancel_reason_id' => $request->cancel_reason_id,
				],
			]);
		}
		else if ($cancelled_id->driver_id) {
			$this->driver_score_service->recalculateDriver($cancelled_id->driver_id, [
				'trigger' => 'rider_cancelled_trip',
				'trip_id' => $cancelled_id->id,
			]);
		}

		return response()->json([
			'status_code' 	 => '1',
			'status_message' => "Success",
			'trip_riders' => $trip_riders,
		]);
	}

	public function cancel_reasons()
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$cancel_reasons = CancelReason::active()->where('cancelled_by',$user_details->user_type)->get();

		$get_cancel_reasons = $cancel_reasons->toArray();
		foreach ($get_cancel_reasons as $key => $toll_reason) {
			unset($get_cancel_reasons[$key]['translations']);
			unset($get_cancel_reasons[$key]['status']);
			unset($get_cancel_reasons[$key]['cancelled_by']);
		}


		return response()->json([
			'status_code' 	 => '1',
			'status_message' => "Success",
			'cancel_reasons' => $get_cancel_reasons,
		]);
	}

	public function toll_reasons()
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$toll_reasons = TollReason::where('id','>',1)->active()->get();
		$toll_reasons[] = TollReason::where('id',1)->active()->first();

		foreach ($toll_reasons as $toll_reason) {
			$toll_reason->commendable = 0; 
			if ($toll_reason->id == 1) {
				$toll_reason->commendable = 1;
			}
		}

		return response()->json([
			'status_message' => "Success",
			'status_code' => '1',
			'toll_reasons' => $toll_reasons
		]);
	}

	/**
	 * Get Trip details Of Given trip id. If trip id not passed then returns incomplete trip details
	 * 
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function get_trip_details(Request $request)
	{
\Log::error('trip responce start');
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'trip_id' => 'nullable|exists:trips,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }
        
        $user_details 	= JWTAuth::parseToken()->authenticate();
        $trip_id 		= $request->trip_id;
        
        // Check Any Trip Is Incomplete Or Not
        $is_trip_id = true;
        if($trip_id == '') {
        	$trip_id = $this->checkPendingTrips($user_details);
        	
        	if($trip_id == 0) {
				return response()->json([
					'status_code' => '0',
					'status_message' => __('messages.api.no_trips_found'),
				]);
        	}

        	$is_trip_id = false;
        }

        $trip_detail = $this->getTripDetails($trip_id,$user_details,$is_trip_id);

		$return_data = array_merge([
            'status_code' => '1',
            'status_message' => __('messages.api.listed_successfully'), 
        ],$trip_detail['data']);
\Log::error('trip responce'. json_encode($return_data));
		return response()->json($return_data);
	}

	public function route_polyline(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'trip_id' => 'nullable|exists:trips,id',
			'pickup_latitude' => 'nullable|numeric',
			'pickup_longitude' => 'nullable|numeric',
			'drop_latitude' => 'nullable|numeric',
			'drop_longitude' => 'nullable|numeric',
			'pickup_location' => 'nullable|string',
			'drop_location' => 'nullable|string',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$trip = null;
		if($request->trip_id) {
			$column = strtolower($user_details->user_type) == 'driver' ? 'driver_id' : 'user_id';
			$trip = Trips::where('id', $request->trip_id)
				->where($column, $user_details->id)
				->first();

			if(!$trip) {
				return response()->json([
					'status_code' => '0',
					'status_message' => 'Trip not found for this user.',
				]);
			}

			if(trim((string) $trip->trip_path) != '') {
				return response()->json([
					'status_code' => '1',
					'status_message' => 'Success',
					'polyline' => $trip->trip_path,
					'distance_meters' => (float) $trip->total_km * 1000,
					'duration_seconds' => (float) $trip->total_time * 60,
					'distance_text' => $trip->total_km ? number_format((float) $trip->total_km, 1).' km' : '',
					'duration_text' => $trip->total_time ? $this->formatRouteDurationText((float) $trip->total_time * 60) : '',
				]);
			}
		}

		$pickup_latitude = $trip->pickup_latitude ?? $request->pickup_latitude;
		$pickup_longitude = $trip->pickup_longitude ?? $request->pickup_longitude;
		$drop_latitude = $trip->drop_latitude ?? $request->drop_latitude;
		$drop_longitude = $trip->drop_longitude ?? $request->drop_longitude;
		$pickup_location = $trip->pickup_location ?? $request->pickup_location;
		$drop_location = $trip->drop_location ?? $request->drop_location;

		$origin = $this->routePoint($pickup_latitude, $pickup_longitude, $pickup_location);
		$destination = $this->routePoint($drop_latitude, $drop_longitude, $drop_location);

		if($origin == '' || $destination == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => 'Pickup and drop route points are required.',
			]);
		}

		$waypoints = $this->routeWaypoints($trip->stop_points ?? $request->stop_points);
		$route = $this->request_helper->GetRoutePolyline($origin, $destination, $waypoints);

		if(($route['status'] ?? '') != 'success' || ($route['polyline'] ?? '') == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => $route['status_message'] ?? 'No route found.',
			]);
		}

		if($trip && trim((string) $trip->trip_path) == '') {
			$trip->trip_path = $route['polyline'];
			$trip->save();
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Success',
			'polyline' => $route['polyline'],
			'distance_meters' => $route['distance_meters'] ?? 0,
			'duration_seconds' => $route['duration_seconds'] ?? 0,
			'distance_text' => $route['distance_text'] ?? '',
			'duration_text' => $route['duration_text'] ?? '',
		]);
	}

	public function routePolyline(Request $request)
	{
		return $this->route_polyline($request);
	}

	protected function formatRouteDurationText($seconds)
	{
		$minutes = (int) ceil($seconds / 60);
		if($minutes < 60) {
			return $minutes.' min';
		}
		$hours = intdiv($minutes, 60);
		$remaining = $minutes % 60;
		return $remaining > 0 ? $hours.' hr '.$remaining.' min' : $hours.' hr';
	}

	protected function routePoint($latitude, $longitude, $address = '')
	{
		if($latitude !== null && $longitude !== null && $latitude !== '' && $longitude !== '') {
			return $latitude.','.$longitude;
		}

		return trim((string) $address);
	}

	protected function routeWaypoints($raw)
	{
		if(!$raw) {
			return [];
		}

		$decoded = is_string($raw) ? json_decode($raw, true) : $raw;
		if(!is_array($decoded)) {
			return [];
		}

		return collect($decoded)->map(function($stop) {
			if(!is_array($stop)) {
				return '';
			}
			$lat = $stop['latitude'] ?? $stop['lat'] ?? $stop['stop_latitude'] ?? null;
			$lng = $stop['longitude'] ?? $stop['lng'] ?? $stop['stop_longitude'] ?? null;
			if($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
				return $lat.','.$lng;
			}
			return $stop['address'] ?? $stop['location'] ?? $stop['name'] ?? '';
		})->filter()->values()->all();
	}

	/**
	 * Accept the Trip Request
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function acceptTrip(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'user_type' => 'required|in:Driver,driver',
			'status' 	=> 'required|in:Online,online,Trip,trip',
			'request_id'=> 'required|exists:request,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

		if (!$user) {
			return response()->json([
				'status_code'	 => '0',
				'status_message' => __('messages.invalid_credentials'),
			]);
		}
		
		$req = RideRequest::where('id', $request->request_id)->first();
		$request_group = $req->group_id;
		$request_status = RideRequest::where('group_id', $request_group)->where('status', 'Accepted')->count();
		if ($request_status != "0") {
			return response()->json([
				'status_code' 	=> '0',
				'status_message'=> "Already Accepted",
			]);
		}
		
		$firbase = resolve(\App\Services\FirebaseService::class);
		$firbase->deleteReference("Notification/".$user_details->id);

		RideRequest::where('id', $request->request_id)->update(['status' => 'Accepted']);

		$ride_request 	= RideRequest::where('id', $request->request_id)->first();
		$vehicle 		= CarType::find($ride_request->car_id);
		$fare_details 	= ManageFare::where('location_id',$ride_request->location_id)->where('vehicle_id',$ride_request->car_id)->first();

		$status = $vehicle->is_pool == 'Yes' ? 'Pool Trip' : $request->status;
		DriverLocation::where('user_id', $user->id)->update(['status' => $status]);

		if($ride_request->schedule_id != '') {
			ScheduleRide::where('id', $ride_request->schedule_id)->update(['status' => 'Completed']);
			$schedule_ride = ScheduleRide::where('id', $ride_request->schedule_id)->first();
		}
		
		if($req->timezone != '') {
			date_default_timezone_set($req->timezone);
		} else {
			$driver_location = DriverLocation::where('user_id', $user_details->id)->first();
			//  get user default location
			$user_timezone = $this->request_helper->getTimeZone($driver_location->latitude, $driver_location->longitude);

			if($user_timezone != '') {
				date_default_timezone_set($user_timezone);
			}
		}

		if($vehicle->is_pool == 'Yes') {
			$driver_location = DriverLocation::where('user_id', $user->id)->where('car_id', $ride_request->car_id)->first();
			if(!$driver_location->pool_trip_id) {
				$pool_trip = new PoolTrip;
				$pool_trip->car_id = $ride_request->car_id;
				$pool_trip->driver_id = $user->id;
				$pool_trip->pickup_latitude = $ride_request->pickup_latitude;
				$pool_trip->pickup_longitude = $ride_request->pickup_longitude;
				$pool_trip->drop_latitude = $ride_request->drop_latitude;
				$pool_trip->drop_longitude = $ride_request->drop_longitude;
				$pool_trip->seats = $fare_details->capacity - $ride_request->seats;
				$pool_trip->pickup_location = $ride_request->pickup_location;
				$pool_trip->drop_location = $ride_request->drop_location;
			} else {
				$pool_trip = PoolTrip::find($driver_location->pool_trip_id);

				// get distance between driver location to last drop location
				$driverToLastDrop = getDistanceBetweenPoints($driver_location->latitude,$driver_location->longitude,$pool_trip->drop_latitude,$pool_trip->drop_longitude);

				// get distance between driver location to request drop location
				$driverToRequestPickup = getDistanceBetweenPoints($driver_location->latitude,$driver_location->longitude,$ride_request->drop_latitude,$ride_request->drop_longitude);

				if($driverToLastDrop < $driverToRequestPickup) {
					$pool_trip->drop_latitude = $ride_request->drop_latitude;
					$pool_trip->drop_longitude = $ride_request->drop_longitude;
					$pool_trip->drop_location = $ride_request->drop_location;
				}
				
				$pool_trip->seats = $pool_trip->seats - $ride_request->seats;
			}

			$pool_trip->status = 'Scheduled';
			$pool_trip->currency_code = $user->currency->code;
			$pool_trip->save();

			$pool_trip_id = $pool_trip->id;
			DriverLocation::where('user_id', $user->id)->update(['pool_trip_id' => $pool_trip_id]);

			$additional_rider = $ride_request->additional_rider;
		}

		$fare_estimation = $this->request_helper->GetDrivingDistance($ride_request->pickup_latitude, $ride_request->drop_latitude, $ride_request->pickup_longitude, $ride_request->drop_longitude);

		// Create Trip
		$trip = new Trips;
		$trip->user_id 			= $ride_request->user_id;
		$trip->pool_id 			= $pool_trip_id ?? '0';
		$trip->pickup_latitude 	= $ride_request->pickup_latitude;
		$trip->pickup_longitude = $ride_request->pickup_longitude;
		$trip->drop_latitude 	= $ride_request->drop_latitude;
		$trip->drop_longitude 	= $ride_request->drop_longitude;
		$trip->driver_id 		= $ride_request->driver_id;
		$trip->car_id 			= $ride_request->car_id;
		$trip->pickup_location 	= $ride_request->pickup_location;
		$trip->drop_location 	= $ride_request->drop_location;
		$trip->request_id 		= $ride_request->id;
		$trip->trip_path 		= $ride_request->trip_path;
		$trip->payment_mode 	= $ride_request->payment_mode;
		$trip->status 			= 'Scheduled';
		$trip->currency_code 	= $user->currency->code;
		$trip->peak_fare 		= $ride_request->peak_fare;
		$trip->extra_fare_percentage = $ride_request->extra_fare_percentage ?? 0;
		$trip->extra_fare_reason = $ride_request->extra_fare_reason;
		$trip->extra_fare_amount = 0;
		$trip->fare_estimation 	= $fare_estimation;
		$trip->additional_rider = $additional_rider ?? 0;
		$trip->otp 				= mt_rand(1000, 9999);
		$trip->seats 			= $ride_request->seats;
		$trip->save();

		try {
			$file = $trip->id. '_file.json';
			$destinationPath = public_path()."/trip_file/";

			if(!is_dir($destinationPath)) { 
				mkdir($destinationPath,0777,true);  
			}

			File::put($destinationPath.$file,'');
		} catch(\Exception $e) {
			
		}

		$push_data['push_title'] = __('messages.api.request_accepted');
        $push_data['data'] = [
            'accept_request' => [
            	'trip_id' => $trip->id
            ]
        ];

        $this->request_helper->SendPushNotification($ride_request->users,$push_data);

		if(isset($schedule_ride) && $schedule_ride->booking_type=='Manual Booking') {
			$push_title = __('messages.request_accepted');
	        $text 		= __('messages.api.your_otp_to_begin_trip').$trip->otp;

	        $push_data['push_title'] = $push_title;
	        $push_data['data'] = [
	            'custom_message' => [
	                'title' => $push_title,
	                'message_data' => $text,
	            ]
	        ];

	        $text = $push_title.$text;

	        $this->request_helper->checkAndSendMessage($ride_request->users,$text,$push_data);
		}

		$trip_detail = $this->getTripDetails($trip->id,$user_details);
		if(!$trip_detail['status']) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $trip_detail['status_message'],
            ]);
		}

		$return_data = array_merge([
            'status_code' => '1',
            'status_message' => __('messages.api.listed_successfully'),
        ],$trip_detail['data']);

		return response()->json($return_data);
	}
    /**

	 * Cancel the Trip Request

	 *

	 * @param Get method request inputs

	 * @return @return Response in Json

	 */

	public function cancelTripRequest(Request $request)

	{

	  $user_details = JWTAuth::parseToken()->authenticate();

	  // Step 1: Find the request by its ID

	  $requester = RideRequest::where('user_id', $user_details->id)->latest()->first();



	    $request_id =  $requester->id;

        $rideRequest = RideRequest::find($request_id);

    

        if (!$rideRequest) {

            // If the ride request doesn't exist, return an error response

            return response()->json([

                'status_code' => '0',

                'status_message' => 'Request not found.',

            ]);

        }

    

        // Step 2: Check if the request is already cancelled or accepted

        if ($rideRequest->status == 'Cancelled') {

            return response()->json([

                'status_code' => '0',

                'status_message' => 'Request already cancelled.',

            ]);

        }

    

        // Step 3: Update the request status to 'Cancelled'

        $rideRequest->status = 'Cancelled';

        $rideRequest->save();

    

        // Step 4: Cancel the push notification if it was sent

        if ($rideRequest->driver_id) {

            // Get the driver's ID

            $driver = User::find($rideRequest->driver_id);

            $firebaseService = resolve(\App\Services\FirebaseService::class);

    

            // Delete the push notification reference

            $firebaseService->deleteReference("Notification/" . $driver->id);

            

        	$push_data['push_title'] = __('messages.api.request_cancelled');

            $push_data['data'] = [

                'request_cancelled' => [

                	'message' => __('messages.api.request_cancelled')

                ]

            ];

            $driver_details = User::where('id', $rideRequest->driver_id)->first();

            $this->request_helper->SendPushNotification($driver_details,$push_data);

        }

    

        // Step 5: Prevent any other driver from receiving the notification

        // This can be handled by checking if any push notification was already scheduled

        // In this case, if the request is cancelled, we avoid sending new notifications

    

        // You could also consider adding additional logic here to reset any states

        // that may be preventing further cancellations.

        if ($rideRequest->driver_id) {
            $this->driver_score_service->recalculateDriver($rideRequest->driver_id, [
                'trigger' => 'trip_request_cancelled',
                'request_id' => $rideRequest->id,
            ]);
        }

    

        return response()->json([

            'status_code' => '1',

            'status_message' => 'Request Successfully  Cancelled',

        ]);
	}
	/**
	 * Send Message to the User
	 * 
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function send_message(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();

		$rules = [
			'receiver_id' 	=> 'required|exists:users,id',
			'message' 		=> 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $receiver = User::find($request->receiver_id);
        $trip = null;

        if ($request->filled('trip_id')) {
            $trip = Trips::find($request->trip_id);
        }

        if($user->user_type=='Rider') {
        	$rating = getDriverRating($user->id);
        } else {
        	$rating = getRiderRating($user->id);
        }

        $push_data['change_title'] = 1;
        $push_data['push_title'] = $user->first_name;
        $push_data['data'] = [
            'chat_notification' => [
                'title' 		=> $request->message,
                'message_data' 	=> $request->message,
                'user_name'  	=> $user->first_name,
                'trip_id'  		=> $request->trip_id ?? '',
                'rating'  		=> $rating,
                'user_id'  		=> $user->id,
				'user_image' 	=> $user->profile_picture->src ?? url('images/user.jpeg'),
            ]
        ];

        $this->request_helper->SendPushNotification($receiver,$push_data);

        event(new \App\Events\TripChatMessageSent(
            $trip?->id,
            $trip?->user_id ?? ($user->user_type == 'Rider' ? $user->id : null),
            $trip?->driver_id ?? ($user->user_type == 'Driver' ? $user->id : ($receiver->user_type == 'Driver' ? $receiver->id : null)),
            $trip?->company_id,
            [
                'trip_id' => $trip?->id ?? $request->trip_id,
                'message' => $request->message,
                'message_data' => $request->message,
                'sender_id' => $user->id,
                'sender_type' => $user->user_type,
                'receiver_id' => $receiver->id,
                'user_name' => $user->first_name,
                'user_image' => $user->profile_picture->src ?? url('images/user.jpeg'),
                'rating' => $rating,
                'sent_at' => now()->toIso8601String(),
            ]
        ));

        return response()->json([
            'status_code' => '1',
            'status_message' => __('messages.api.success'),
            'message_payload' => [
                'trip_id' => $trip?->id ?? $request->trip_id,
                'message' => $request->message,
                'message_data' => $request->message,
                'sender_id' => $user->id,
                'sender_type' => $user->user_type,
                'receiver_id' => $receiver->id,
                'user_name' => $user->first_name,
                'user_image' => $user->profile_picture->src ?? url('images/user.jpeg'),
                'rating' => $rating,
                'sent_at' => now()->toIso8601String(),
            ],
        ]);
	}
}
