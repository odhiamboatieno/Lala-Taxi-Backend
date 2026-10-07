<?php

/**
 * Rider Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Rider
 * @author      Wilaso
 * @version     3.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Banner;
use App\Models\CarType;
use App\Models\DriverLocation;
use App\Models\EmergencySos;
use App\Models\Request as RideRequest;
use App\Models\RiderLocation;
use App\Models\ScheduleRide;
use App\Models\Trips;
use App\Models\User;
use App\Models\Country;
use App\Models\PeakFareDetail;
use App\Models\Location;
use App\Models\ReferralUser;
use App\Models\ManageFare;
use App\Models\CancelReason;
use App\Models\ScheduleCancel;
use App\Models\PoolTrip;
use App\Models\FilterObject;
use App\Models\Support;
use App\Models\SupportDriver;
use App\Services\LocationExtraFareService;
use Carbon\Carbon;
use App;
use DB;
use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Log;
use Modules\SurgePricing\Services\SurgePricingResolver;

class RiderController extends Controller
{
    public function __construct()
    {
        $this->request_helper = resolve(\App\Http\Helper\RequestHelper::class);
    }

    /**
     * Rider Request to Search Cars
     * @param  Get method request inputs
     *
     * @return Response Json
     */
    public function searchCars(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = [
            'pickup_latitude' 	=> 'required',
            'pickup_longitude' 	=> 'required',
            'drop_latitude' 	=> 'required',
            'drop_longitude' 	=> 'required',
            'user_type' 		=> 'required|in:Rider,rider',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        if($request->timezone) {
            date_default_timezone_set($request->timezone);
            $current_time = date('H:i:00');
        }

        $current_time = date('H:i:00');
        $day = date('N');

        $is_schedule = $request->is_schedule;
        if(isset($request->schedule_date)!='' && $is_schedule=='1') {
            $day = date('N',strtotime($request->schedule_date));
            $current_time = $request->schedule_time.':00';
        }

        $offline_hours = site_settings('offline_hours');
        $minimumTimestamp = Carbon::now('UTC')->subHours($offline_hours);

        $fare_estimation = 0;
        $get_near_car_time = 0;

        // Find location from pickup latitude & longitude
        $match_location = Location::select(
            DB::raw("id,status,(ST_WITHIN( ST_GeomFromText('POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available ")
        )->having('available','1')->where('status','Active')->first();

        if(!$match_location) {
            return response()->json([
                'status_message' => trans('messages.location_unavailable'),
                'status_code' => '0',
            ]);
        }

        $location_cars = ManageFare::where('location_id',$match_location->id)->get()->toArray();

        $vehicles 	= array_column($location_cars,'vehicle_id');
        $location_id= $match_location->id;
        $baseExtraFareConfig = app(LocationExtraFareService::class)->getConfig($match_location);

        $lat_lng_array = [
            "pickup_latitude" 	=> $request->pickup_latitude,
            "pickup_longitude" 	=> $request->pickup_longitude,
            "drop_latitude" 	=> $request->drop_latitude,
            "drop_longitude" 	=> $request->drop_longitude,
        ];

        $handicap = $child_seat = $request_from = '';
        $options = FilterObject::options('rider',$user_details->id);
        if(in_array('4', $options)) {
            $request_from = '1';
        }
        if(in_array('2', $options)) {
            $handicap = '1';
        }
        if(in_array('3', $options)) {
            $child_seat = '1';
        }

        // Find nearest cars in location
        $nearest_car = DriverLocation::select(
            DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance')
        )
        ->with([
            'car_type'=>function($q) use($location_id) {
                $q->with(['manage_fare'  => function($q) use($location_id) {
                    $q->where('location_id',$location_id);
                }]);
            },
            'users',
            'pool_trip'
        ])
        ->where('updated_at','>=', $minimumTimestamp)
        ->where(function($query) use($is_schedule) {
            if($is_schedule!='1'){
                $query->where('status', 'Online')->orWhere('status','Pool Trip');
            }
        })
        ->whereHas('users', function($q1) use($handicap,$child_seat,$request_from,$user_details) {
            $q1->activeOnlyStrict()
            ->whereHas('vehicle', function($q2) use($handicap,$child_seat,$request_from,$user_details) {
                if($handicap) {
                    $q2->whereHas('handicap');
                }
                if($child_seat) {
                    $q2->whereHas('child_seat');
                }
                if($request_from) {
                    $q2->whereHas('female_driver');
                }
                if($user_details->gender=='1') {
                    $q2->whereDoesntHave('female');
                }
            });
        })
        ->whereHas('car_type', function($q2) use ($vehicles,$is_schedule) {
            $q2->where('status', 'Active')->whereIn('car_id',$vehicles);
            if($is_schedule=='1')
                $q2->where('is_pool', 'No');
        })
        ->havingRaw(('case WHEN status="Online" THEN distance<='.site_settings('driver_km').' ELSE distance<='.site_settings('pickup_km').' END'))
        ->orderBy('distance', 'ASC')
        ->take(5)
        ->get();

        if($is_schedule!='1') {
            $nearest_car = $nearest_car->filter(function($near_car) use($lat_lng_array) {
                if($near_car->status == "Online") {
                    return true;
                }
                
                $pool_trip = $near_car->pool_trip;
                if($pool_trip->seats < 1 || $pool_trip->car_id!=$near_car->car_id) {
                    return false;
                }

                // get pending pool trips count
                $pending_trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip']);
                if($pending_trips->count()==1) {
                    $pending_trips = array_values($pending_trips->toArray());
                    $driver_location_to_drop = getDistanceBetweenPoints($near_car->latitude,$near_car->longitude,$pending_trips[0]['drop_latitude'],$pending_trips[0]['drop_longitude']);
                    if($driver_location_to_drop <= site_settings('drop_km')) {
                        return true;
                    }
                }

                $trip_destinations = PoolTrip::with(['trips' => function($query) use($lat_lng_array) {
                    $query->with('driver_location')->select(
                        \DB::raw(
                            '*,
                            (CASE 
                            WHEN status="Scheduled" OR status="Begin trip" OR status="End Trip" THEN ( 6371 * acos( cos( radians(' . $lat_lng_array['drop_latitude'] . ') ) * cos( radians( drop_latitude ) ) * cos(radians( drop_longitude ) - radians(' . $lat_lng_array['drop_longitude'] . ') ) + sin( radians(' . $lat_lng_array['drop_latitude'] . ') ) * sin( radians( drop_latitude ) ) ) ) 
                            ELSE 999999999 END) as distance'
                        )
                    )
                    ->having('distance', '<', site_settings('drop_km'));
                }])
                ->find($near_car->pool_trip_id);

                $trip_destinations_count = $trip_destinations->trips->count();

                return ($trip_destinations_count > 0);
            });
        }

        $nearest_car = collect($nearest_car)->groupBy('car_id')->values();
        LogDistanceMatrix("Search cars");
        $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);
        if ($get_fare_estimation['status'] != "success") {
            return response()->json([
                'status_code' => '0',
                'status_message' => $get_fare_estimation['msg'],
            ]);
        }

        if(!$get_fare_estimation['distance']) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Same location'
            ]);
        }

        $minutes = round(floor(round($get_fare_estimation['time'] / 60)));
        $km = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));
        $additional_rider = fees('additional_rider_fare');
        if (isset($nearest_car) && !$nearest_car->isEmpty()) {
            /* Start Peak Price */
            $data = ManageFare::with([
                'peak_fare' => function($query) use($day,$current_time) {
                    $query->where(function($q) use($day,$current_time) {
                        $q->where('day', $day)
                        ->whereRaw("(start_time <= '".$current_time."' and end_time >= '".$current_time."')");
                    })
                    ->orWhere(function($q) use($day,$current_time) {
                        $q->where('day', null)
                        ->whereRaw(
                            "(SELECT CASE WHEN ( start_time < end_time ) THEN (start_time <= '".$current_time."' and end_time >= '".$current_time."') ELSE (('".$current_time."' between start_time and '23:59:00') or ('".$current_time."' between '00:00:00' and end_time)) END)"
                        );
                    });
                }
            ])
            ->whereHas('peak_fare',function($query) use($day,$current_time) {
                $query->where(function($q) use($day,$current_time) {
                    $q->where('day', $day)
                    ->whereRaw("start_time <= '".$current_time."' and end_time >= '".$current_time."'");
                })
                ->orWhere(function($q) use($day,$current_time) {
                    $q->where('day', null)
                    ->whereRaw(
                        "(SELECT CASE WHEN ( start_time <= end_time ) THEN (start_time <= '".$current_time."' and end_time >= '".$current_time."') ELSE (('".$current_time."' between start_time and '23:59:00') or ('".$current_time."' between '00:00:00' and end_time)) END)"
                    );
                });
            })
            ->where('location_id',$location_id)
            ->groupBy('vehicle_id')
            ->get();

            $fare_details = $data->mapWithKeys(function($fare) {
                $peak_fare = $fare->peak_fare->first();
                $fare_data = [
                    'id' 		=> $peak_fare->id,
                    'car_id' 	=> $fare->vehicle_id,
                    'price' 	=> $peak_fare->price,
                    'type'		=> $peak_fare->type,
                ];
                return [$fare->vehicle_id => $fare_data];
            })->toArray();
            /* End Peak Price */

            $location = $drivers = [];
            $i = 0;
            foreach ($nearest_car as $key => $list_car) {
                $location = $list_car->map(function ($item) use ($km, $minutes) {
                    return [
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude
                    ];
                })->toArray();

                $drivers = $list_car->map(function ($item) use ($km, $minutes) {
                    return [
                        'id' => $item->user_id,
                    ];
                })->toArray();

                if (count($location) > 0) {
                    LogDistanceMatrix("Search cars","first nearest driver");
                    $get_min_time = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $location[0]['latitude'], $request->pickup_longitude,$location[0]['longitude']);

                    $base_fare = round($list_car[$i]->car_type->manage_fare->base_fare + ($list_car[$i]->car_type->manage_fare->per_km * $km));
                    $fare_estimation = number_format(($base_fare + round($list_car[$i]->car_type->manage_fare->per_min * $minutes)), 2, '.', '');

                    if($fare_estimation < $list_car[$i]->car_type->manage_fare->min_fare) {     
                        $fare_estimation = $list_car[$i]->car_type->manage_fare->min_fare;
                    }

                    if($get_min_time['status'] != "success") {
                        return response()->json([
                            'status_code' => '0',
                            'status_message' => $get_min_time['msg'],
                        ]);
                    }
                    $get_near_car_time = round(floor(round($get_min_time['time'] / 60)));
                    if($get_near_car_time == 0) {
                        $get_near_car_time = 1;
                    }
                }
                
                $car_s[]  = ['car_id' => $list_car[$i]->car_id];

                $peak_price = 0;
                $apply_peak = "No";
                $peak_id =0;

                if(!empty($fare_details)) {
                    if(array_key_exists($list_car[$i]->car_id,$fare_details)) { 
                        $peak_price = $fare_details[$list_car[$i]->car_id]['price'];
                        $peak_id = $fare_details[$list_car[$i]->car_id]['id'];
                        $apply_peak = "Yes";
                        $fare_estimation = $fare_estimation * $peak_price;
                    }
                }

                $driverCompanyId = optional($list_car[$i]->users)->company_id;
                $surge = app(SurgePricingResolver::class)->resolve(
                    (int) $location_id,
                    Carbon::now($request->timezone ?: config('app.timezone')),
                    $driverCompanyId,
                    'ride',
                    (int) $list_car[$i]->car_id
                );
                $extraFareConfig = app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFareConfig, $surge);

                $extraFareAmount = 0;
                if ($extraFareConfig['enabled']) {
                    $extraFareAmount = number_format(($extraFareConfig['percentage'] / 100) * $fare_estimation, 2, '.', '');
                    $fare_estimation += $extraFareAmount;
                }

                $additional_rider_amount = 0;
                if(isset($request->seat_count) && $request->seat_count > 1 ) {
                    $additional_rider_amount = number_format(($additional_rider / 100) *  $fare_estimation , 2, '.', '');
                    $fare_estimation += $additional_rider_amount;
                }

                $car_fare = $list_car[$i]->car_type->manage_fare;

                $car_array[$list_car[$i]->car_id] = [ 
                    'car_id' 		=> $list_car[$i]->car_id,
                    'car_name' 		=> $list_car[$i]->car_type->car_name,
                    'is_pool' 		=> ($car_fare->capacity > 1 && $list_car[$i]->car_type->is_pool == "Yes"),
                    'driver_id' 	=> $list_car[$i]->user_id,
                    'capacity' 		=> $car_fare->capacity,
                    'base_fare' 	=> $car_fare->base_fare,
                    'waiting_time' 	=> $car_fare->waiting_time,
                    'waiting_charge'=> $car_fare->waiting_charge,
                    'per_min' 		=> $car_fare->per_min,
                    'per_km' 		=> $car_fare->per_km,
                    'min_fare' 		=> $car_fare->min_fare,
                    'location' 		=> $location,
                    'drivers' 		=> $drivers,
                    'fare_estimation' => (string) $fare_estimation ,
                    'extra_fare_percentage' => (string) ($extraFareConfig['enabled'] ? $extraFareConfig['percentage'] : 0),
                    'extra_fare_amount' => (string) $extraFareAmount,
                    'extra_fare_reason' => $extraFareConfig['enabled'] ? $extraFareConfig['reason'] : '',
                    'surge_pricing_id' => $surge['surge_pricing_id'] ?? null,
                    'surge_name' => $surge['name'] ?? null,
                    'surge_percentage' => round((float) ($surge['percentage'] ?? 0), 2),
                    'min_time' 		=> (string) $get_near_car_time,
                    'apply_peak' 	=> $apply_peak,
                    'peak_price' 	=> $peak_price,
                    'location_id' 	=> $location_id,
                    'additional_rider_percentage' => $additional_rider,
                    'peak_id' 		=> $peak_id,
                    'car_image' 	=> $list_car[$i]->car_type->vehicle_image,
                    'car_active_image' =>$list_car[$i]->car_type->active_image,
                    'app_image'     => $list_car[$i]->car_type->app_image,
                ];
            }
        }

        $cars = CarType::with(['manage_fare' =>function($q) use ($location_id) {
            $q->where('location_id',$location_id);
        }])
        ->whereIn('id',$vehicles)
        ->where('status', 'Active');

        if($is_schedule=='1') {
            $cars->where('is_pool', 'No');
        }

        if(isset($car_s)) {
            $car_id = array_column($car_s, 'car_id');
            $cars = $cars->whereNotIn('id', $car_id)->get();
        } else {
            $cars = $cars->get();
        }

        foreach($cars as $key => $value) {
            $base_fare = round($value->manage_fare->base_fare + ($value->manage_fare->per_km * $km));
            $fare_estimation = number_format(($base_fare + round($value->manage_fare->per_min * $minutes)), 2, '.', '');

            if($fare_estimation < $value->manage_fare->min_fare) {     
                $fare_estimation = $value->manage_fare->min_fare;
            }

            $surge = app(SurgePricingResolver::class)->resolve(
                (int) $location_id,
                Carbon::now($request->timezone ?: config('app.timezone')),
                null,
                'ride',
                (int) $value->id
            );
            $extraFareConfig = app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFareConfig, $surge);

            $extraFareAmount = 0;
            if ($extraFareConfig['enabled']) {
                $extraFareAmount = number_format(($extraFareConfig['percentage'] / 100) * $fare_estimation, 2, '.', '');
                $fare_estimation += $extraFareAmount;
            }

            if(isset($request->seat_count) && $request->seat_count > 1 ) {
                $additional_rider_amount = number_format(($additional_rider / 100) *  $fare_estimation , 2, '.', '');
                $fare_estimation += $additional_rider_amount;
            }

            $car_array[$value->id] = [
                'car_id' 		=> $value->id,
                'car_name' 		=> $value->car_name,
                'is_pool' 		=> ($value->manage_fare->capacity > 1 && $value->is_pool == "Yes"),
                'driver_id' 	=> 0,
                'capacity' 		=> $value->manage_fare->capacity,
                'base_fare' 	=> $value->manage_fare->base_fare,
                'waiting_time' 	=> $value->manage_fare->waiting_time,
                'waiting_charge'=> $value->manage_fare->waiting_charge,
                'per_min' 		=> $value->manage_fare->per_min,
                'per_km' 		=> $value->manage_fare->per_km,
                'min_fare' 		=> $value->manage_fare->min_fare,
                'schedule_fare' => $value->manage_fare->schedule_fare,
                'schedule_cancel_fare' => $value->manage_fare->schedule_cancel_fare,
                'location' 		=> [],
                'drivers' 		=> [],
                'fare_estimation' => $fare_estimation,
                'extra_fare_percentage' => (string) ($extraFareConfig['enabled'] ? $extraFareConfig['percentage'] : 0),
                'extra_fare_amount' => (string) $extraFareAmount,
                'extra_fare_reason' => $extraFareConfig['enabled'] ? $extraFareConfig['reason'] : '',
                'surge_pricing_id' => $surge['surge_pricing_id'] ?? null,
                'surge_name' => $surge['name'] ?? null,
                'surge_percentage' => round((float) ($surge['percentage'] ?? 0), 2),
                'additional_rider_percentage' => $additional_rider,
                'min_time'		=> 'No cabs',
                "apply_peak"	=> "No",
                "peak_price" 	=> 0,
                'location_id' 	=> $location_id,
                'peak_id' 		=>  0,								
                'car_image' 	=> $value->vehicle_image,
                'car_active_image' => $value->active_image,
                'app_image'     => $value->app_image,
            ];
        }

        if(!isset($car_array)) {
            return response()->json([
                'status_message' => trans('messages.no_cars_found'),
                'status_code' => '0',
            ]);
        }

        return response()->json([
            'nearest_car' => $car_array,
            'status_message' => trans('messages.cars_found'),
            'status_code' => '1',
        ]);
    }

    /**
     * Update Location of Rider
     *
     * @param Get method request inputs
     * @return @return Response in Json
     */
    public function updateriderlocation(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = [
            'latitude' => 'required',
            'longitude' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }
        $user_check = User::where('id', $user_details->id)->first();

        if ($user_check == '') {
            return response()->json([
                'status_code'	 => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }

        $data = [
            'user_id' => $user_details->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        RiderLocation::updateOrCreate(['user_id' => $user_details->id], $data);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Updated Successfully',
        ]);
    }

    /**
     * Ride Request from Rider
     * @param  Get method request inputs
     *
     * @return Response Json
     */
    public function requestCars(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rider_id = $user_details->id;

        if ($request->request_id) {
            $rules = [
                'status' => 'required|in:Cancelled,cancelled',
            ];
        }
        else {
            $rules = [
                'pickup_latitude' => 'required',
                'pickup_longitude' => 'required',
                'drop_latitude' => 'required',
                'drop_longitude' => 'required',
                'user_type' => 'required|in:Rider,rider',
                'car_id' => 'required|exists:car_type,id',
                'pickup_location' => 'required',
                'drop_location' => 'required',
                'device_id' => 'required',
                'device_type' => 'required',
                'payment_method' => 'required',
                'estimated_fare' => 'required',
                'distance_estimate' => 'required',
            ];
            $group_id = '';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $additional_fare = "";
        $peak_price = 0;
        $locationExtraFareService = app(LocationExtraFareService::class);

        if(isset($request->peak_id)!='') {
            $fare = PeakFareDetail::find($request->peak_id);
            if($fare) {
                $peak_price = $fare->price; 
                $additional_fare = "Peak";
            }
        }

        $additional_rider = fees('additional_rider_fare');

        if ($request->request_id) {
            RideRequest::where('id', $request->request_id)->update(['status' => $request->status]);

            $ride_request = RideRequest::where('id', $request->request_id)->first();

            $data = [
                'rider_id' => $ride_request->user_id,
                'pickup_latitude' => $ride_request->pickup_latitude,
                'pickup_longitude' => $ride_request->pickup_longitude,
                'drop_latitude' => $ride_request->drop_latitude,
                'drop_longitude' => $ride_request->drop_longitude,
                'user_type' => $ride_request->user_type,
                'car_id' => $ride_request->car_id,
                'driver_group_id' => $ride_request->group_id,
                'pickup_location' => $ride_request->pickup_location,
                'drop_location' => $ride_request->drop_location,
                'payment_method' => $ride_request->payment_method,
                'is_wallet' => $ride_request->is_wallet,
                'timezone' => $ride_request->timezone,
                'schedule_id' => $ride_request->schedule_id,
                'additional_fare'  =>$additional_fare,
                'location_id' => $ride_request->location_id,
                'peak_price'  => $peak_price,
                'additional_rider'  => $additional_rider,
                'trip_path'  	=> $ride_request->trip_path,
                'estimated_fare' => $request->estimated_fare,
                'distance_estimate' => number_format($request->distance_estimate / 1000, 1),
                'payment_mode' => $request->payment_mode,
                'fare_estimation'  => $request->fare_estimation ?? '0',
                'seats'  => $ride_request->seats ?? '1',
                'extra_fare_percentage' => $ride_request->extra_fare_percentage ?? 0,
                'extra_fare_reason' => $ride_request->extra_fare_reason,
            ];

            $car_details = $this->request_helper->find_driver($data);

            return $car_details;
        }

        User::whereId($rider_id)->update(['device_id' => $request->device_id, 'device_type' => $request->device_type]);

        $locationExtraFare = $locationExtraFareService->getConfig(Location::find($request->location_id));

        $data = [
            'rider_id' => $rider_id,
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'drop_latitude' => $request->drop_latitude,
            'drop_longitude' => $request->drop_longitude,
            'user_type' => $request->user_type,
            'car_id' => $request->car_id,
            'driver_group_id' => $request->group_id,
            'pickup_location' => $request->pickup_location,
            'drop_location' => $request->drop_location,
            'payment_method' => $request->payment_method,
            'is_wallet' => $request->is_wallet,
            'timezone' => $request->timezone,
            'schedule_id' => (string) $request->schedule_id,
            'additional_fare' => $additional_fare,
            'additional_rider'  => $additional_rider,
            'location_id' => $request->location_id,				 
            'peak_price'  => $peak_price,
            'trip_path'  	=> $request->polyline ?? '',
            'estimated_fare' => $request->estimated_fare,
            'distance_estimate' => number_format($request->distance_estimate / 1000, 1),
            'payment_mode' => $request->payment_mode,
            'fare_estimation'  => $request->fare_estimation ?? '0',
            'seats'  => $request->seat_count ?? '1',
            'extra_fare_percentage' => $locationExtraFare['enabled'] ? $locationExtraFare['percentage'] : 0,
            'extra_fare_reason' => $locationExtraFare['enabled'] ? $locationExtraFare['reason'] : null,
            'booking_for_type' => $request->booking_for_type ?? 'for_me',
            'booking_contact_name' => $request->booking_contact_name ?? '',
            'booking_contact_phone' => $request->booking_contact_phone ?? '',
            'stop_points' => $request->stop_points ?? '',
        ];

        $car_details = $this->request_helper->find_driver($data);

        return $car_details;
    }

    /**
     * Display the promo details
     * @param  Get method request inputs
     *
     * @return Response Json
     */
    public function promo_details(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();
        $user = User::where('id', $user_details->id)->first();
        if ($user == '') {
            return response()->json([
                'status_code'	 => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }

        $invoice_helper = resolve(\App\Http\Helper\InvoiceHelper::class);
        $promo_details = $invoice_helper->getUserPromoDetails($user_details->id);

        $wallet_amount = getUserWalletAmount($user_details->id);

        $user = [
            'status_code' 	=> '1',
            'status_message'=> __('messages.api.success'),
            'wallet_amount' => $wallet_amount,
            'promo_details' => $promo_details,
            'stripe_key' 	=> STRIPE_KEY,
        ];
        return response()->json($user);
    }

    /**
     * Track the Driver Location
     * @param  Get method request inputs
     *
     * @return Response Json
     */
    public function track_driver(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = [
            'user_type' => 'required',
            'trip_id' => 'required|exists:trips,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }
        $user = User::where('id', $user_details->id)->first();

        if (!$user) {
            return response()->json([
                'status_code'	 => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }

        $driver_details = Trips::where('id', $request->trip_id)->first();
        $driver_latitude = $driver_details->driver_location->latitude;
        $driver_longitude = $driver_details->driver_location->longitude;

        $user = [
            'status_code' => '1',
            'status_message' => 'Success',
            'driver_latitude' => $driver_latitude,
            'driver_longitude' => $driver_longitude,
        ];

        return response()->json($user);
    }

    /**
     * Display the SOS details
     * @param  Get method request inputs
     * @return Response Json
     */
    public function sos(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();
        $user = User::where('id', $user_details->id)->first();
        $count = EmergencySos::where('user_id', $user_details->id)->get()->count();

        if ($request->input('mobile_number') != '') {
            $request->replace(['mobile_number' => preg_replace("/[^\w]+/", "", $request->input('mobile_number')), 'action' => $request->input('action'), 'name' => $request->input('name'),'country_code' => $request->input('country_code'), 'id' => $request->input('id')]);
        }

        if ($request->action != "view") {
            $rules = ['mobile_number' => 'required|numeric', 'action' => 'required'];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status_code'     => '0',
                    'status_message' => $validator->messages()->first(),
                ]);
            }
        }
        $user = User::where('id', $user_details->id)->first();

        if (!$user) {
            return response()->json([
                'status_code'	 => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }

        $mobile_number = $request->mobile_number;
        $emer = EmergencySos::where('mobile_number', $mobile_number)->where('user_id', $user_details->id)->first();
        $count = EmergencySos::where('user_id', $user_details->id)->get()->count();
        $contact_details = EmergencySos::where('user_id', $user_details->id)->get();
        if ($request->action == 'update') {
            if ($emer) {
                return response()->json(['status_message' => trans('messages.mobile_number_exist'), 'status_code' => '0', 'contact_count' => $count, 'contact_details' => $contact_details]);
            }

            $emercency = new EmergencySos;
            $emercency->name = $request->name;

            $country = Country::whereShortName($request->country_code)->first();
            $emercency->country_code = $country->phone_code;
            $emercency->country_id 	= $country->id;

            $emercency->mobile_number = $mobile_number;
            $emercency->user_id = $user_details->id;
            $emercency->save();
            $count = EmergencySos::where('user_id', $user_details->id)->get()->count();
            $contact_details = EmergencySos::where('user_id', $user_details->id)->get();
            return response()->json(['status_message' => "Added Successfully", 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
        }
        else if ($request->action == 'delete') {
            $del = EmergencySos::find($request->id);

            if ($del == null) {
                return response()->json(['status_message' => "Not found given request", 'status_code' => '0', 'contact_count' => $count, 'contact_details' => $contact_details]);
            }

            $del->delete();
            $count = EmergencySos::where('user_id', $user_details->id)->get()->count();
            $contact_details = EmergencySos::where('user_id', $user_details->id)->get();

            return response()->json(['status_message' => "Delete Successfully", 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
        }
        else {
            return response()->json(['status_message' => trans('messages.success'), 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
        }
    }

    /**
     * SOS alert Message to Admin and Rider Added Mobile numbers
     * @param  Get method request inputs
     * @return Response Json
     */
    public function sosalert(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();
        $contact_details = EmergencySos::where('user_id', $user_details->id)->get();
        $address = $this->request_helper->GetLocation($request->latitude, $request->longitude);
        $maps_link = 'https://maps.google.com/?q='.$request->latitude.','.$request->longitude;

        if ($address == '') {
            sleep(5);
            $address = $this->request_helper->GetLocation($request->latitude, $request->longitude);
        }

        $admin_details = Admin::where('status', 'Active')->select('country_code','mobile_number')->first();
        $mobile = '+'.$admin_details->country_code.$admin_details->mobile_number;

        $message = 'Emergency Alert';
        $message .= ' From: ' . trim(($user_details->first_name ?? '').' '.($user_details->last_name ?? ''));
        $message .= ' Phone: ' . $user_details->mobile_number;
        $message .= ' Address: ' . $address;
        $message .= ' Map: ' . $maps_link;
        $sms_gateway = resolve(\App\Contracts\SMSInterface::class);
        if ($contact_details->count() > 0) {
            foreach ($contact_details as $details) {
                $sms_gateway->send('+'.$details->country_code.$details->mobile_number,$message);
            }
            $sms_gateway->send($mobile,$message);
            return response()->json(['status_message' => 'Success', 'status_code' => '1']);
        }
        $sms_gateway->send($mobile,$message);
        return response()->json(['status_message' => 'Success', 'status_code' => '2']);
    }

    /**
     * Save Schedule Ride
     * @param  Get method request inputs
     * @return Response Json
     */
    public function save_schedule_ride(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();
        $rider_id = $user_details->id;
        if ($request->schedule_id != '') {
            $rules = [
                'schedule_id' => 'required',
                'schedule_date' => 'required',
                'schedule_time' => 'required',
            ];
        }
        else {
            $rules = [
                'schedule_date' => 'required',
                'schedule_time' => 'required',
                'pickup_longitude' => 'required',
                'pickup_latitude' => 'required',
                'drop_latitude' => 'required',
                'drop_longitude' => 'required',
                'car_id' => 'required|exists:car_type,id',
                'pickup_location' => 'required',
                'drop_location' => 'required',
                'device_id' => 'required',
                'payment_method' => 'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $status_code = "1";
        if ($request->schedule_id) {
            $schedule_ride = ScheduleRide::find($request->schedule_id);
            $schedule_ride->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
            $schedule_ride->schedule_time = $request->schedule_time;
            $schedule_ride->status = 'Pending';

            $schedule_ride->fare_estimation = ScheduleRide::getFareEstimation($schedule_ride);
            $schedule_ride->save();

            $status_message = __('messages.api.schedule_ride_updated');
        }
        else {
            $trip_path = $request->polyline ?? '';
            $peak_id= 0;

            if(isset($request->peak_id)) {
                $peak_id = $request->peak_id;
            }

            $schedule_ride = new ScheduleRide;
            $schedule_ride->user_id = $rider_id;
            $schedule_ride->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
            $schedule_ride->schedule_time = $request->schedule_time;
            $schedule_ride->pickup_latitude = $request->pickup_latitude;
            $schedule_ride->pickup_longitude = $request->pickup_longitude;
            $schedule_ride->drop_latitude = $request->drop_latitude;
            $schedule_ride->drop_longitude = $request->drop_longitude;
            $schedule_ride->car_id = $request->car_id;
            $schedule_ride->pickup_location = $request->pickup_location;
            $schedule_ride->drop_location = urldecode($request->drop_location);
            $schedule_ride->status = 'Pending';
            $schedule_ride->trip_path = $trip_path;
            $schedule_ride->timezone = $request->timezone;
            $schedule_ride->payment_method =$request->payment_method;
            $schedule_ride->is_wallet = $request->is_wallet;
            $schedule_ride->location_id = $request->location_id;
            $schedule_ride->peak_id = $peak_id;
            $schedule_ride->booking_for_type = $request->booking_for_type ?? 'for_me';
            $schedule_ride->booking_contact_name = $request->booking_contact_name ?? '';
            $schedule_ride->booking_contact_phone = $request->booking_contact_phone ?? '';
            $schedule_ride->stop_points = $request->stop_points ?? '';
            $schedule_ride->save();

            $schedule_ride->fare_estimation = ScheduleRide::getFareEstimation($schedule_ride);
            $schedule_ride->save();

            $status_message = __('messages.api.schedule_ride_created');
        }

        $schedule_rides = ScheduleRide::where('user_id', $rider_id)->where('status','Pending')->orderBy('id','DESC')->limit(10)->get();

        return response()->json(compact('status_code','status_message','schedule_rides'));
    }

    /**
     * Cancel Saved Schedule Ride
     * @param  Get method request inputs
     * @return Response Json
     */
    public function schedule_ride_cancel(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();
        $rules = [
            'trip_id' => 'required',
            'cancel_reason_id' => 'required',
        ];

        $messages = [
            'trip_id.required' => trans('messages.required.trip_id') . ' ' . trans('messages.field_is_required') . '',
            'cancel_reason_id.required' => trans('messages.required.cancel_reason_id') . ' ' . trans('messages.field_is_required') . '',
        ];

        $cancelled_by = 'Driver';
        if ($user_details->user_type == 'Rider') {
            $cancelled_by = 'Rider';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($request,$cancelled_by){
            $cancel_reason_exists= CancelReason::active()->where('cancelled_by',$cancelled_by)->where('id',$request->cancel_reason_id)->first();
            if (!$cancel_reason_exists) {
                $validator->errors()->add('cancel_reason_id', 'Id not exists');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status_code'     => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $rider_id = $user_details->id;
        $request_table = ScheduleRide::find($request->trip_id);
        $request_table->status = 'Cancelled';
        $request_table->save();

        $data = [
            'schedule_ride_id' => $request->trip_id,
            'cancel_reason' => @$request->cancel_comments != '' ? $request->cancel_comments : '',
            'cancel_by' => $cancelled_by,
            'cancel_reason_id' => $request->cancel_reason_id,
        ];
        
        ScheduleCancel::updateOrCreate(['schedule_ride_id' => $request->trip_id], $data);

        //Send Sms
        $trips = ScheduleRide::where('id', $request->trip_id)->first();
        $m_number = $trips->users->phone_number;
        $message = 'Your Schedule Ride had been Cancelled.';
        $sms_gateway = resolve(\App\Contracts\SMSInterface::class);
        $sms_gateway->send($m_number,$message);

        return ['status_code' => '1', 'status_message' => trans('messages.success')];
    }

    public function check_version(Request $request) {
        $referral_settings = app()->bound('referral_settings') ? resolve('referral_settings') : \App\Models\ReferralSetting::get();
        $referral_settings = $referral_settings->where('user_type',ucfirst($request->user_type))->where('name','apply_referral')->first();
        
        $app_version_condition = ['no_update','force_update','skip_update'];
        $index = 0;
        $user_type = (strtolower($request->user_type) != 'rider') ? 1:0;
        
        $latest_verion = '';
        $get_latest_verion = DB::table('app_version')->select('force_update')->whereRaw('version > (select version from `app_version` where `device_type` = "'.$request->device_type.'" and `user_type` = "'.$user_type.'" and `version` = "'.$request->version.'")')->where('device_type',$request->device_type)->where('user_type',$user_type)->get();
        if($get_latest_verion->count()){
            $force_update_count = $get_latest_verion->where('force_update',1)->count();
            if($force_update_count)
                $index = 1;
            else
                $index = 2;
        }	
        $force_update 	   = $app_version_condition[$index];

        $social_logins = explode(',',site_settings('social_logins'));

        $support = Support::active()->get()->map(function($value){
            return [
                'id'	=> $value->id,
                'name'	=> $value->name,
                'link'	=> $value->link ?? '',
                'image'=> $value->image_src ?? '',
            ];
        });
        $support2 = SupportDriver::active()->get()->map(function($value){
            return [
                'id'	=> $value->id,
                'name'	=> $value->name,
                'link'	=> $value->link ?? '',
                'image'=> $value->image_src ?? '',
            ];
        });
        $banners = collect();
        if (Schema::hasTable('banners')) {
            $banners = Banner::active()->get()->map(function($value) {
                return [
                    'id'    => $value->id,
                    'name'  => $value->name ?? '',
                    'type'  => $value->type ?? 'Ride',
                    'title' => $value->title ?? $value->name ?? '',
                    'subtitle' => $value->subtitle ?? '',
                    'link'  => $value->link ?? '',
                    'image' => $value->image_src ?? '',
                ];
            });
        }
        
        if($user_type = (strtolower($request->user_type) == 'rider')) {
            return [
                'status_code'		=> '1',
                'status_message' 	=> 'Success',
                'force_update'		=> $force_update,
                'enable_referral' 	=> true,
                'client_id'			=> api_credentials('service_id','Apple'),
                "apple_login" 		=> in_array('apple',$social_logins),
                'facebook_login' 	=> in_array('facebook',$social_logins),
                'google_login' 		=> in_array('google',$social_logins),
                'otp_enabled' 		=> site_settings('otp_verification')=='1' ? true:false,
                'default_otp' 	    => site_settings('default_otp'),
                'driver_request_seconds' => (int) (site_settings('driver_request_seconds') ?: 30),
                'admin_contact'     => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
                'manual_call_number' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
                'server_time'       => now()->toIso8601String(),
                'support'			=> $support ?? [],
                'banner'            => $banners ?? [],
            ];
        } else {
            return [
                'status_code'		=> '1',
                'status_message' 	=> 'Success',
                'force_update'		=> $force_update,
                'enable_referral' 	=> true,
                'otp_enabled' 		=> site_settings('otp_verification')=='1' ? true:false,
                'default_otp' 	    => site_settings('default_otp'),
                'driver_request_seconds' => (int) (site_settings('driver_request_seconds') ?: 30),
                'admin_contact'     => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
                'manual_call_number' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
                'server_time'       => now()->toIso8601String(),
                'support'			=> $support2 ?? [],
                'banner'            => $banners ?? [],
                'biometric_login_enabled' => site_settings('biometric_login_enabled') == '1' ? true : false,
            ];
        }
    }

    /**
     * Get Nearest Vehicles
     * @param  Get method request inputs
     *
     * @return Response Json
     */
    public function get_nearest_vehicles(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = [
            'latitude' 	=> 'required',
            'longitude' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $staleCutoff = now()->subSeconds(config('dispatch.driver_stale_after_seconds', 90));

        // Find nearest cars in location
        $nearest_car = DriverLocation::select(
            DB::raw('*, ( 3959 * acos( cos( radians('.$request->latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$request->longitude.') ) + sin( radians('.$request->latitude.') ) * sin( radians( latitude ) ) ) ) as distance')
        )
        ->with('car_type', 'users')
        ->where(function($q) {
            $q->where('driver_location.status', 'Online')
            ->orWhere('driver_location.status', 'Pool Trip');
        })
        ->whereHas('users', function($query) use ($staleCutoff) {
            $query->activeOnlyStrict()
                ->where('is_online', true)
                ->where(function ($availability) {
                    $availability->where('is_available', true)
                        ->orWhereNotNull('current_trip_id');
                });
        })
        ->where(function ($freshness) use ($staleCutoff) {
            $freshness->where('driver_location.updated_at', '>=', $staleCutoff)
                ->orWhere('driver_location.created_at', '>=', $staleCutoff)
                ->orWhereHas('users', function ($userQuery) use ($staleCutoff) {
                    $userQuery->where('last_seen_at', '>=', $staleCutoff);
                });
        })
        ->whereHas('car_type', function($q) {
            $q->where('status', 'Active')
            ->whereRaw(('case WHEN driver_location.status="Pool Trip" THEN car_type.is_pool="Yes" ELSE 1 END'));
        })
        ->havingRaw(('case WHEN status="Online" THEN distance<='.site_settings('driver_km').' ELSE distance<='.site_settings('pickup_km').' END'))
        ->orderBy('distance', 'ASC')
        ->get();

        $data = $nearest_car->map(function($car) {
            return [
                'driver_id' => $car->user_id,
                'vehicle_id' => $car->car_id,
                'vehicle_type' => $car->car_type->car_name,
                'car_name' => $car->car_type->car_name,
                'car_image' => optional($car->car_type)->vehicle_image ?? '',
                'car_active_image' => optional($car->car_type)->active_image ?? '',
                'app_image' => optional($car->car_type)->app_image ?? '',
                'latitude' 	=> $car->latitude,
                'longitude' => $car->longitude,
                'is_pool' => $car->car_type->is_pool=='Yes',
            ];
        });

        return [
            'status_code'	=> '1',
            'status_message' => __('messages.api.listed_successfully'),
            'data'	=> $data,
        ];
    }

    public function updateRiderPosition(Request $request)
    {
        try {
            Log::error('update_rider_location endpoint called', ['params' => $request->all()]);

            // Authenticate the user
            $user = JWTAuth::parseToken()->authenticate();
            Log::error('User authenticated', ['user_id' => $user->id]);

            // Validate input
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            // Update user's location
            $user->latitude = $validated['latitude'];
            $user->longitude = $validated['longitude'];
            $user->save();

            Log::error('User location updated', [
                'user_id' => $user->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude']
            ]);

            return response()->json([
                'status_code' => '1',
                'status_message' => 'Location updated successfully',
            ], 200);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            Log::error('Token invalid', ['error' => $e->getMessage()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Invalid token',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::error('Token expired', ['error' => $e->getMessage()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Token expired',
            ], 401);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Validation failed: ' . implode(', ', $e->errors()['latitude'] ?? $e->errors()['longitude'] ?? ['Invalid input']),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in update_rider_location', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get_request_history(Request $request)
    {
        try {
            Log::error('get_request_history endpoint called', ['params' => $request->all()]);

            // Authenticate the user
            $user_details = JWTAuth::parseToken()->authenticate();
            Log::error('User authenticated', ['user_id' => $user_details->id]);

            // Handle POST request to update rider's current location (if needed)
            if ($request->isMethod('post')) {
                Log::error('POST request received for get_request_history', ['params' => $request->all()]);
                $latitude = $request->input('latitude');
                $longitude = $request->input('longitude');

                if (is_null($latitude) || is_null($longitude)) {
                    Log::error('Missing latitude or longitude');
                    return response()->json([
                        'status_code' => '0',
                        'status_message' => 'Latitude and longitude are required',
                    ], 400);
                }

                $user_details->latitude = $latitude;
                $user_details->longitude = $longitude;
                $user_details->save();

                Log::error('User location updated in get_request_history', [
                    'user_id' => $user_details->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);

                return response()->json([
                    'status_code' => '1',
                    'status_message' => 'Location updated successfully',
                ], 200);
            }

            // Handle GET request to fetch trip history or suggestions
            $orderHistory = RideRequest::where('user_id', $user_details->id)
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($value) {
                    return [
                        'id' => $value->id,
                        'request_status' => $value->status ?? '',
                        'request_pickup' => $value->pickup_location,
                        'request_drop' => $value->drop_location ?? '',
                        'formatted_address' => $value->drop_location ?? '',
                        'drop_latitude' => $value->drop_latitude,
                        'drop_longitude' => $value->drop_longitude,
                        'pickup_latitude' => $value->pickup_latitude,
                        'pickup_longitude' => $value->pickup_longitude,
                    ];
                });

            Log::error('Order history retrieved', ['count' => $orderHistory->count()]);

            if ($orderHistory->isNotEmpty()) {
                return response()->json([
                    'status_code' => '1',
                    'status_message' => 'Success',
                    'request_list' => $orderHistory,
                ], 200);
            }

            // Use the user's stored location from the users table
            $latitude = $user_details->latitude;
            $longitude = $user_details->longitude;

            if (is_null($latitude) || is_null($longitude)) {
                Log::error('No past trips and no current location available, using fallback location');
                // Fallback to a default location (Nairobi, Kenya)
                $latitude = -1.286389;
                $longitude = 36.817223;
            }

            $suggestions = $this->getNearbySuggestions($latitude, $longitude);
            Log::error('Suggestions generated', ['suggestions' => $suggestions]);

            return response()->json([
                'status_code' => '1',
                'status_message' => is_null($user_details->latitude) ? 'No past trips, suggested locations provided (default location)' : 'No past trips, suggested locations provided',
                'request_list' => $suggestions,
            ], 200);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            Log::error('Token invalid', ['error' => $e->getMessage()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Invalid token',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::error('Token expired', ['error' => $e->getMessage()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Token expired',
            ], 401);
        } catch (\Exception $e) {
            Log::error('Error in get_request_history', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getNearbySuggestions($latitude, $longitude)
    {
        $apiKey = $this->googleMapsServerKey();
        if (empty($apiKey)) {
            Log::error('Google Maps API key missing');
            // Return fallback suggestions without API
            return [
                [
                    'id' => null,
                    'request_status' => 'suggested',
                    'request_pickup' => 'Current Location',
                    'request_drop' => 'City Center',
                    'formatted_address' => 'Explore nearby city center',
                    'drop_latitude' => $latitude,
                    'drop_longitude' => $longitude,
                    'pickup_latitude' => $latitude,
                    'pickup_longitude' => $longitude,
                ],
                [
                    'id' => null,
                    'request_status' => 'suggested',
                    'request_pickup' => 'Current Location',
                    'request_drop' => 'Local Mall',
                    'formatted_address' => 'Explore nearby mall',
                    'drop_latitude' => $latitude,
                    'drop_longitude' => $longitude,
                    'pickup_latitude' => $latitude,
                    'pickup_longitude' => $longitude,
                ],
                [
                    'id' => null,
                    'request_status' => 'suggested',
                    'request_pickup' => 'Current Location',
                    'request_drop' => 'Tourist Attraction',
                    'formatted_address' => 'Explore nearby attraction',
                    'drop_latitude' => $latitude,
                    'drop_longitude' => $longitude,
                    'pickup_latitude' => $latitude,
                    'pickup_longitude' => $longitude,
                ],
            ];
        }

        $types = ['hospital', 'school', 'airport', 'shopping_mall', 'tourist_attraction', 'city_hall'];
        $suggestions = [];

        shuffle($types);
        $selectedTypes = array_slice($types, 0, 3);

        foreach ($selectedTypes as $type) {
            $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$latitude},{$longitude}&radius=5000&type={$type}&key={$apiKey}";
            Log::error('Calling Google Places API', ['url' => $url]);

            $response = $this->googleGet($url, []);

            if ($response !== null && $response->successful()) {
                $results = $response->json()['results'] ?? [];
                Log::error('Google Places API response', ['results_count' => count($results)]);
                if (!empty($results)) {
                    $place = $results[0];
                    $suggestions[] = [
                        'id' => null,
                        'request_status' => 'suggested',
                        'request_pickup' => 'Current Location',
                        'request_drop' => $place['name'] ?? 'Unknown',
                        'formatted_address' => $place['vicinity'] ?? $place['name'] ?? 'Unknown',
                        'drop_latitude' => $place['geometry']['location']['lat'] ?? 0.0,
                        'drop_longitude' => $place['geometry']['location']['lng'] ?? 0.0,
                        'pickup_latitude' => $latitude,
                        'pickup_longitude' => $longitude,
                    ];
                }
            } else {
                Log::error('Google Places API failed', [
                    'status' => $response?->status(),
                    'body' => $response?->body(),
                ]);
            }
        }

        while (count($suggestions) < 3) {
            $suggestions[] = [
                'id' => null,
                'request_status' => 'suggested',
                'request_pickup' => 'Current Location',
                'request_drop' => 'Nearby Location',
                'formatted_address' => 'Explore nearby',
                'drop_latitude' => $latitude,
                'drop_longitude' => $longitude,
                'pickup_latitude' => $latitude,
                'pickup_longitude' => $longitude,
            ];
        }

        return array_slice($suggestions, 0, 3);
    }

    /**
     * The Google Maps server key configured in the admin panel
     * (api_credentials table) is the single source of truth.
     */
    private function googleMapsServerKey()
    {
        return api_credentials('server_key', 'GoogleMap');
    }

    /**
     * Never let a Google Maps connection/timeout error become a 500. A null
     * return means the request could not be completed at all.
     */
    private function googleGet(string $url, array $query)
    {
        try {
            return Http::timeout(12)->get($url, $query);
        } catch (\Throwable $e) {
            Log::error('Google Maps request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Google returns HTTP 200 even when it refuses a request, so a plain
     * $response->successful() check hides REQUEST_DENIED / OVER_QUERY_LIMIT
     * behind an empty result. Surface those instead of faking success.
     */
    private function googleMapsFailure($payload, $subject)
    {
        $status = $payload['status'] ?? 'UNKNOWN_ERROR';

        if (in_array($status, ['OK', 'ZERO_RESULTS'], true)) {
            return null;
        }

        $message = $payload['error_message'] ?? null;

        if ($status === 'REQUEST_DENIED') {
            $message = $message
                ? $message.' '
                : '';
            $message .= 'Check that the Google Maps server key is valid and that the Places API and Geocoding API are enabled for it.';
        } elseif (!$message) {
            $message = 'Google Maps returned '.$status.'.';
        }

        return response()->json([
            'status_code' => '0',
            'status_message' => trim('Unable to load '.$subject.': '.$message),
            'google_status' => $status,
        ]);
    }

    public function place_autocomplete(Request $request)
    {
        JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'input' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $serverKey = $this->googleMapsServerKey();
        if (empty($serverKey)) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Google Maps server key is not configured',
            ]);
        }

        $query = [
            'input' => $request->input,
            'key' => $serverKey,
        ];

        if ($request->filled('session_token')) {
            $query['sessiontoken'] = $request->session_token;
        }

        $query['components'] = $request->filled('components')
            ? $request->components
            : 'country:ke';

        $response = $this->googleGet('https://maps.googleapis.com/maps/api/place/autocomplete/json', $query);

        if ($response === null || !$response->successful()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Unable to fetch places right now',
            ]);
        }

        $payload = $response->json();
        if ($error = $this->googleMapsFailure($payload, 'places')) {
            return $error;
        }

        $predictions = collect($payload['predictions'] ?? [])->map(function ($item) {
            return [
                'place_id' => $item['place_id'] ?? '',
                'primary_text' => data_get($item, 'structured_formatting.main_text', $item['description'] ?? ''),
                'secondary_text' => data_get($item, 'structured_formatting.secondary_text', ''),
                'description' => $item['description'] ?? '',
            ];
        })->values();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Success',
            'predictions' => $predictions,
        ]);
    }

    public function place_details(Request $request)
    {
        JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'place_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $serverKey = $this->googleMapsServerKey();
        if (empty($serverKey)) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Google Maps server key is not configured',
            ]);
        }

        $response = $this->googleGet('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $request->place_id,
            'fields' => 'formatted_address,geometry/location,name,place_id',
            'key' => $serverKey,
        ]);

        if ($response === null || !$response->successful()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Unable to fetch place details right now',
            ]);
        }

        $payload = $response->json();
        if ($error = $this->googleMapsFailure($payload, 'place details')) {
            return $error;
        }

        $place = $payload['result'] ?? [];

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Success',
            'place' => [
                'place_id' => $place['place_id'] ?? $request->place_id,
                'name' => $place['name'] ?? '',
                'address' => $place['formatted_address'] ?? '',
                'latitude' => data_get($place, 'geometry.location.lat', 0),
                'longitude' => data_get($place, 'geometry.location.lng', 0),
            ],
        ]);
    }

    public function reverse_geocode(Request $request)
    {
        JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $serverKey = $this->googleMapsServerKey();
        if (empty($serverKey)) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Google Maps server key is not configured',
            ]);
        }

        $response = $this->googleGet('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $request->latitude.','.$request->longitude,
            'key' => $serverKey,
        ]);

        if ($response === null || !$response->successful()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Unable to resolve this map location right now',
            ]);
        }

        $payload = $response->json();
        if ($error = $this->googleMapsFailure($payload, 'this map location')) {
            return $error;
        }

        $result = collect($payload['results'] ?? [])->first() ?? [];

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Success',
            'place' => [
                'address' => $result['formatted_address'] ?? '',
                'latitude' => (float) $request->latitude,
                'longitude' => (float) $request->longitude,
            ],
        ]);
    }

    public function parcel_delivery_options(Request $request)
    {
        JWTAuth::parseToken()->authenticate();

        $categories = \App\Models\ParcelCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'key', 'title', 'description', 'icon_path', 'base_fare', 'multiplier'])
            ->toArray();

        $quickTasks = \App\Models\ParcelTask::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'key', 'title', 'icon_path'])
            ->toArray();

        $packageSizes = \App\Models\ParcelPackageSize::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'key', 'title', 'subtitle', 'description', 'price_multiplier'])
            ->toArray();

        $banners = \App\Models\Banner::where('type', 'Parcel')
            ->where('status', 'Active')
            ->orderBy('id')
            ->get(['id', 'title', 'subtitle', 'image', 'link'])
            ->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'image' => $banner->image_src ?? '',
                    'link' => $banner->link,
                ];
            })
            ->toArray();

        $settings = \App\Models\ParcelSetting::all()->pluck('value', 'key')->toArray();

        return response()->json([
            'status_code' => '1',
            'status_message' => __('messages.api.listed_successfully'),
            'categories' => $categories,
            'quick_tasks' => $quickTasks,
            'package_sizes' => $packageSizes,
            'banners' => $banners,
            'settings' => [
                'max_stops' => 2,
                'proof_of_delivery_signature_required' => ($settings['signature_required'] ?? 'true') === 'true',
                'proof_of_delivery_photo_required' => ($settings['photo_required'] ?? 'true') === 'true',
                'recipient_required' => true,
                'max_weight_kg' => $settings['max_weight_kg'] ?? 50,
            ],
        ]);
    }

    public function parcel_delivery_request(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'category' => 'required|string|exists:parcel_categories,key',
            'task_type' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'pickup_location' => 'required|string|max:255',
            'drop_latitude' => 'required|numeric',
            'drop_longitude' => 'required|numeric',
            'drop_location' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:120',
            'recipient_phone' => 'required|string|max:40',
            'recipient_phone_dial_code' => 'required|string|max:10',
            'recipient_phone_iso_code' => 'required|string|max:5',
            'recipient_address' => 'required|string|max:255',
            'car_id' => 'required|exists:car_type,id',
            'payment_mode' => 'required|string',
            'parcel_description' => 'nullable|string',
            'estimated_weight' => 'nullable|string|max:50',
            'special_instructions' => 'nullable|string',
            'pickup_contact_name' => 'nullable|string|max:120',
            'pickup_contact_phone' => 'nullable|string|max:40',
            'stop_points' => 'nullable|array',
            'stop_points.*.latitude' => 'required_with:stop_points|numeric',
            'stop_points.*.longitude' => 'required_with:stop_points|numeric',
            'stop_points.*.address' => 'required_with:stop_points|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $distance = $this->calculateDistance(
                $request->pickup_latitude, $request->pickup_longitude,
                $request->drop_latitude, $request->drop_longitude
            );

            $carType = \App\Models\CarType::findOrFail($request->car_id);
            $category = \App\Models\ParcelCategory::where('key', $request->category)->firstOrFail();
            $parcelLocation = app(LocationExtraFareService::class)->resolveLocationByCoordinates($request->pickup_latitude, $request->pickup_longitude);
            $baseExtraFareConfig = app(LocationExtraFareService::class)->getConfig($parcelLocation);
            $surge = app(SurgePricingResolver::class)->resolve(
                $parcelLocation?->id,
                now(),
                null,
                'parcel',
                (int) $request->car_id
            );
            $fareEstimate = $this->calculateParcelFare(
                $distance,
                $carType,
                $category,
                app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFareConfig, $surge)
            );

            $dispatchService = app(\App\Services\Dispatch\TripDispatchService::class);
            $result = $dispatchService->requestTrip($user, [
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'drop_latitude' => $request->drop_latitude,
                'drop_longitude' => $request->drop_longitude,
                'pickup_location' => $request->pickup_location,
                'drop_location' => $request->drop_location,
                'car_id' => $request->car_id,
                'payment_mode' => $request->payment_mode,
                'service_category' => 'parcel',
                'stop_points' => $request->stop_points ? json_encode($request->stop_points) : '',
                'estimated_fare' => $fareEstimate['total'],
                'estimated_distance' => $distance,
                'location_id' => $parcelLocation?->id,
                'extra_fare_percentage' => $fareEstimate['extra_fare_percentage'] ?? 0,
                'extra_fare_reason' => $fareEstimate['extra_fare_reason'] ?? null,
                'surge_pricing_id' => $surge['surge_pricing_id'] ?? null,
                'surge_name' => $surge['name'] ?? null,
                'surge_percentage' => round((float) ($surge['percentage'] ?? 0), 2),
                'timezone' => $request->timezone ?? $user->timezone ?? config('app.timezone'),
                'booking_for_type' => 'for_me',
                'booking_contact_name' => $request->pickup_contact_name ?? '',
                'booking_contact_phone' => $request->pickup_contact_phone ?? '',
                'parcel_info' => [
                    'category_key' => $request->category,
                    'category_title' => $category->title,
                    'task_type' => $request->task_type,
                    'recipient_name' => $request->recipient_name,
                    'recipient_phone' => $request->recipient_phone,
                    'recipient_phone_dial_code' => $request->recipient_phone_dial_code ?? '+254',
                    'recipient_phone_iso_code' => $request->recipient_phone_iso_code ?? 'KE',
                    'recipient_address' => $request->recipient_address,
                    'parcel_description' => $request->parcel_description,
                    'estimated_weight' => $request->estimated_weight,
                    'special_instructions' => $request->special_instructions,
                    'pickup_contact_name' => $request->pickup_contact_name,
                    'pickup_contact_phone' => $request->pickup_contact_phone,
                ],
            ]);

            $rideRequest = $result['request'];

            \App\Models\ParcelRequest::create([
                'request_id' => $rideRequest->id,
                'category' => $request->category,
                'task_type' => $request->task_type,
                'recipient_name' => $request->recipient_name,
                'recipient_phone' => $request->recipient_phone,
                'recipient_phone_dial_code' => $request->recipient_phone_dial_code ?? '+254',
                'recipient_phone_iso_code' => $request->recipient_phone_iso_code ?? 'KE',
                'recipient_address' => $request->recipient_address,
                'parcel_description' => $request->parcel_description,
                'estimated_weight' => $request->estimated_weight,
                'special_instructions' => $request->special_instructions,
                'pickup_contact_name' => $request->pickup_contact_name,
                'pickup_contact_phone' => $request->pickup_contact_phone,
                'proof_of_delivery_required' => true,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            $settings = \App\Models\ParcelSetting::all()->pluck('value', 'key')->toArray();

            return response()->json([
                'status_code' => '1',
                'status_message' => __('messages.api.request_created'),
                'trip' => $result['trip'] ? $result['trip']->toArray() : null,
                'request' => $result['request'] ? $result['request']->toArray() : null,
                'request_id' => $rideRequest->id,
                'fare_estimate' => $fareEstimate,
                'service_category' => 'parcel',
                'request_timeout_seconds' => $settings['request_timeout'] ?? 120,
                'server_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Parcel delivery request failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status_code' => '0',
                'status_message' => __('messages.api.something_went_wrong'),
            ], 500);
        }
    }

    public function parcel_fare_estimate(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'drop_latitude' => 'required|numeric',
            'drop_longitude' => 'required|numeric',
            'category' => 'required|exists:parcel_categories,key',
            'car_id' => 'required|exists:car_type,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status_code' => '0', 'status_message' => $validator->messages()->first()], 422);
        }

        $distance = $this->calculateDistance(
            $request->pickup_latitude, $request->pickup_longitude,
            $request->drop_latitude, $request->drop_longitude
        );

        $carType = \App\Models\CarType::findOrFail($request->car_id);
        $category = \App\Models\ParcelCategory::where('key', $request->category)->firstOrFail();
        $parcelLocation = app(LocationExtraFareService::class)->resolveLocationByCoordinates($request->pickup_latitude, $request->pickup_longitude);
        $baseExtraFareConfig = app(LocationExtraFareService::class)->getConfig($parcelLocation);
        $surge = app(SurgePricingResolver::class)->resolve(
            $parcelLocation?->id,
            now(),
            null,
            'parcel',
            (int) $request->car_id
        );
        $fare = $this->calculateParcelFare(
            $distance,
            $carType,
            $category,
            app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFareConfig, $surge)
        );

        $nearbyDrivers = app(\App\Services\Dispatch\NearbyDriverService::class)
            ->getNearbyDrivers($request->pickup_latitude, $request->pickup_longitude, 5);

        return response()->json([
            'status_code' => '1',
            'status_message' => __('messages.api.listed_successfully'),
            'fare' => $fare,
            'distance_km' => $distance,
            'estimated_duration_minutes' => round($distance * 2.5),
            'nearby_drivers' => count($nearbyDrivers),
            'service_category' => 'parcel',
        ]);
    }

    public function getParcelRequestDetails($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $parcelRequest = \App\Models\ParcelRequest::where('id', $id)
            ->whereHas('request', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['request.trip', 'request.carType', 'parcelCategory'])
            ->first();

        if (!$parcelRequest) {
            return response()->json(['status_code' => '0', 'status_message' => 'Parcel request not found'], 404);
        }

        return response()->json([
            'status_code' => '1',
            'parcel_request' => $parcelRequest->toArray(),
        ]);
    }

    public function getParcelTripDetails($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $trip = \App\Models\Trips::where('id', $id)
            ->where('service_category', 'parcel')
            ->whereHas('request', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['driver', 'carType', 'parcelRequest', 'parcelCategory', 'parcelTask'])
            ->first();

        if (!$trip) {
            return response()->json(['status_code' => '0', 'status_message' => 'Parcel trip not found'], 404);
        }

        $tasks = $trip->parcelTask->sortBy('sequence')->map(function ($task) use ($trip) {
            return [
                'id' => $task->id,
                'key' => $task->key,
                'title' => $task->title,
                'description' => $task->description,
                'icon_path' => $task->icon_path ? asset($task->icon_path) : null,
                'completed' => $task->pivot->completed_at !== null,
                'completed_at' => $task->pivot->completed_at,
            ];
        })->values();

        return response()->json([
            'status_code' => '1',
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'fare' => $trip->fare,
                'distance' => $trip->distance,
                'payment_status' => $trip->payment_status,
                'driver' => $trip->driver ? [
                    'id' => $trip->driver->id,
                    'first_name' => $trip->driver->first_name,
                    'last_name' => $trip->driver->last_name,
                    'phone' => $trip->driver->phone,
                    'rating' => $trip->driver->rating,
                ] : null,
                'category' => $trip->parcelCategory ? $trip->parcelCategory->title : null,
                'parcel_request' => $trip->parcelRequest ? [
                    'recipient_name' => $trip->parcelRequest->recipient_name,
                    'recipient_phone' => $trip->parcelRequest->recipient_phone,
                    'pickup_address' => $trip->parcelRequest->pickup_address,
                    'dropoff_address' => $trip->parcelRequest->dropoff_address,
                    'weight_kg' => $trip->parcelRequest->weight_kg,
                    'description' => $trip->parcelRequest->description,
                ] : null,
                'tasks' => $tasks,
                'pod_signature' => $trip->parcel_pod_signature ? asset($trip->parcel_pod_signature) : null,
                'pod_photo' => $trip->parcel_pod_photo ? asset($trip->parcel_pod_photo) : null,
            ],
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    private function calculateParcelFare($distanceKm, $carType, $category, array $extraFareConfig = [])
    {
        $fare = \App\Models\ParcelFare::findByCategoryAndVehicle($category->id, $carType->id);

        if ($fare) {
            $baseFare = $fare->base_fare;
            $perKmRate = $fare->per_km_rate;
            $minFare = $fare->min_fare;
        } else {
            $baseFare = $category->base_fare;
            $perKmRate = $carType->price_per_km ?? 15;
            $minFare = $baseFare;
        }

        $distanceFare = $distanceKm * $perKmRate;
        $subtotal = ($baseFare + $distanceFare) * $category->multiplier;
        $subtotal = max($subtotal, $minFare);

        $settings = \App\Models\ParcelSetting::all()->pluck('value', 'key')->toArray();
        $platformFeePercent = $settings['platform_fee_percent'] ?? 10;
        $platformFee = $subtotal * ($platformFeePercent / 100);
        $extraFareAmount = 0;
        if (($extraFareConfig['enabled'] ?? false)) {
            $extraFareAmount = ($subtotal + $platformFee) * (((float) $extraFareConfig['percentage']) / 100);
        }
        $total = $subtotal + $platformFee + $extraFareAmount;

        return [
            'base_fare' => round($baseFare, 2),
            'distance_fare' => round($distanceFare, 2),
            'distance_km' => $distanceKm,
            'per_km_rate' => round($perKmRate, 2),
            'platform_fee' => round($platformFee, 2),
            'extra_fare_percentage' => round((float) (($extraFareConfig['enabled'] ?? false) ? ($extraFareConfig['percentage'] ?? 0) : 0), 2),
            'extra_fare_amount' => round($extraFareAmount, 2),
            'extra_fare_reason' => ($extraFareConfig['enabled'] ?? false) ? ($extraFareConfig['reason'] ?? null) : null,
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
            'currency' => config('app.currency', 'KES'),
        ];
    }

    public function submitParcelProofOfDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:parcel_requests,id',
            'signature' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->errors()->first(),
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $parcelRequest = \App\Models\ParcelRequest::where('id', $request->request_id)
            ->whereHas('request', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->first();

        if (!$parcelRequest) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Parcel request not found',
            ]);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'pod_' . $parcelRequest->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = public_path('images/parcel_pod');

            if (!\File::exists($path)) {
                \File::makeDirectory($path, 0755, true);
            }

            $file->move($path, $filename);
            $parcelRequest->pod_photo = 'images/parcel_pod/' . $filename;
        }

        if ($request->filled('signature')) {
            $signatureData = $request->signature;
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData)) {
                $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                $signatureData = base64_decode($signatureData);
                $filename = 'sig_' . $parcelRequest->id . '_' . time() . '.png';
                $path = public_path('images/parcel_pod');

                if (!\File::exists($path)) {
                    \File::makeDirectory($path, 0755, true);
                }

                file_put_contents($path . '/' . $filename, $signatureData);
                $parcelRequest->pod_signature = 'images/parcel_pod/' . $filename;
            }
        }

        $parcelRequest->proof_of_delivery_required = false;
        $parcelRequest->save();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Proof of delivery submitted',
            'data' => [
                'request_id' => $parcelRequest->id,
                'pod_signature' => $parcelRequest->pod_signature ? asset($parcelRequest->pod_signature) : null,
                'pod_photo' => $parcelRequest->pod_photo ? asset($parcelRequest->pod_photo) : null,
            ],
        ]);
    }
}
?>
