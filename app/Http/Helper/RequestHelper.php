<?php

/**
 * Request Helper
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Request
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */
namespace App\Http\Helper;

use App\Models\DriverLocation;
use App\Models\PaymentGateway;
use App\Models\Request;
use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\CarType;
use App\Models\User;
use App\Models\Trips;
use App\Models\ManageFare;
use App\Models\PoolTrip;
use App\Models\PeakFareDetail;
use App\Models\FilterObject;
use App\Services\LocationExtraFareService;
use Auth;
use DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Carbon\Carbon;    
use DateTime;
use Storage;
use Cache;
use Modules\SurgePricing\Services\SurgePricingResolver;

class RequestHelper
{
    protected function mergedExtraFareConfig(?int $locationId, ?int $companyId = null, string $mode = 'ride', ?int $vehicleTypeId = null, ?string $timezone = null, $moment = null): array
    {
        $location = $locationId ? \App\Models\Location::find($locationId) : null;
        $baseExtraFare = app(LocationExtraFareService::class)->getConfig($location);

        if ($moment instanceof Carbon) {
            $resolvedMoment = $moment;
        }
        elseif ($moment) {
            $resolvedMoment = Carbon::parse($moment, $timezone ?: config('app.timezone'));
        }
        else {
            $resolvedMoment = Carbon::now($timezone ?: config('app.timezone'));
        }

        $surge = app(SurgePricingResolver::class)->resolve(
            $locationId,
            $resolvedMoment,
            $companyId,
            $mode,
            $vehicleTypeId
        );

        return app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFare, $surge);
    }

    /**
     * Find Nearest Drivers
     *
     * @param Array $array
     * @return void
     */
    public function find_driver($array)
    {
        /*
         *query for get nearest drivers with in given kilomerters and getting currently online status drivers
         *driver_group_id first time goes null so getting all drivers details.- important
         *driver_group_id creates for one riderid with multiple nearest locations drivers - important
        */

        $user_details = User::find($array['rider_id']);
        $billing = app(\App\Services\Corporate\CorporateBillingContext::class);
        $source = null;
        if (!empty($array['schedule_id']) && is_numeric($array['schedule_id'])) {
            $source = ScheduleRide::findOrFail($array['schedule_id']);
        } elseif (!empty($array['driver_group_id'])) {
            $source = RideRequest::where('group_id', $array['driver_group_id'])->orderBy('id')->firstOrFail();
        }
        if ($source) {
            abort_unless((int) $source->user_id === (int) $user_details->id, 403);
            $billing->validatePending($source);
            $context = $billing->attributes($source);
        } else {
            $context = $billing->resolveForBooking($user_details, $array);
        }
        $array = array_merge($array, $context);
        if ($context['billing_mode'] === 'corporate') {
            $array['payment_method'] = $array['payment_mode'] = 'Corporate';
        }

        $handicap = $child_seat = $request_from = '';
        $options = FilterObject::options('rider', $user_details->id);
        if (in_array('4', $options)) {
            $request_from = '1';
        }
        if (in_array('2', $options)) {
            $handicap = '1';
        }
        if (in_array('3', $options)) {
            $child_seat = '1';
        }

        $this->clearPending();
        $array['seats'] = $array['seats'] ?? '1';
        $data_array = [
            'pickup_latitude' => $array['pickup_latitude'],
            'pickup_longitude' => $array['pickup_longitude'],
            'drop_latitude' => $array['drop_latitude'],
            'drop_longitude' => $array['drop_longitude'],
            'car_id' => $array['car_id'],
            'schedule_id' => $array['schedule_id'],
            'seats' => $array['seats'],
        ];
        ini_set('max_execution_time', 300);
        date_default_timezone_set($array['timezone']);
        
        $offline_hours = site_settings('offline_hours');
        $minimumTimestamp = Carbon::now('UTC')->subHours($offline_hours);
        $vehicle_type = CarType::where('id', $array['car_id'])->first();

        $ignore_drivers = $this->ignoreAssigned($data_array);
        $excludeDrivers = collect($array['exclude_driver_ids'] ?? [])
            ->filter(function ($value) {
                return (int) $value > 0;
            })
            ->map(function ($value) {
                return (int) $value;
            })
            ->values()
            ->all();

        $nearest_car = DriverLocation::select(
            DB::raw('*, ( 6371 * acos( cos( radians(' . $array['pickup_latitude'] . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $array['pickup_longitude'] . ') ) + sin( radians(' . $array['pickup_latitude'] . ') ) * sin( radians( latitude ) ) ) ) as distance')
        )
        ->havingRaw(('case WHEN status="Online" THEN distance<='.site_settings('driver_km').' ELSE distance<='.site_settings('pickup_km').' END'))
        ->where('car_id', $array['car_id'])
        ->where(function ($query) {
            $query->where('status', 'Online')
                ->orWhere('status', 'Pool Trip');
        })
        ->where('updated_at', '>=', $minimumTimestamp)
        ->whereNotIn('user_id', $ignore_drivers)
        ->when(!empty($excludeDrivers), function ($query) use ($excludeDrivers) {
            $query->whereNotIn('user_id', $excludeDrivers);
        })
        ->whereHas('users', function ($q1) use ($handicap, $child_seat, $request_from, $user_details) {
            $q1->activeOnlyStrict()
                ->whereHas('vehicle', function ($q2) use ($handicap, $child_seat, $request_from, $user_details) {
                    if ($handicap) {
                        $q2->whereHas('handicap');
                    }
                    if ($child_seat) {
                        $q2->whereHas('child_seat');
                    }
                    if ($request_from) {
                        $q2->whereHas('female_driver');
                    }
                    if ($user_details->gender == '1') {
                        $q2->whereDoesntHave('female');
                    }
                });
        });

        $firbase = resolve(\App\Services\FirebaseService::class);

        if ($array['driver_group_id'] != null && $array['driver_group_id'] != "") {
            $nearest_car = $nearest_car->whereHas('request', function ($subQuery) use ($array) {
                $subQuery->where('group_id', $array['driver_group_id'])->whereIn('status', ['Cancelled', 'Pending'])->whereNotIn('status', ['Accepted']);
            }, '<', 1);
        }

        Logger('Driver Request Limit ' . site_settings('driver_request_limit'));
        
        $nearest_car = $nearest_car->orderBy('distance', 'ASC')->take(site_settings('driver_request_limit'))->get();

        Logger('Nearest Car Count ' . $nearest_car->count());

        $nearest_car = $nearest_car->filter(function ($near_car) use ($data_array) {
            if ($near_car->status == "Online") {
                return true;
            }

            $pool_trip = $near_car->pool_trip;
            if ($pool_trip->seats < $data_array['seats'] || $pool_trip->car_id != $near_car->car_id) {
                return false;
            }

            // get pending pool trips count
            $pending_trips = $pool_trip->trips->whereIn('status', ['Scheduled', 'Begin trip', 'End trip']);

            if ($pending_trips->count() == 1) {
                $pending_trips = array_values($pending_trips->toArray());
                $driver_location_to_drop = getDistanceBetweenPoints($near_car->latitude, $near_car->longitude, $pending_trips[0]['drop_latitude'], $pending_trips[0]['drop_longitude']);
                if ($driver_location_to_drop <= site_settings('drop_km')) {
                    return true;
                }
            }

            $trip_destinations = PoolTrip::with(['trips' => function ($query) use ($data_array) {
                $query->with('driver_location')->select(
                    DB::raw(
                        '*,
                        (CASE 
                        WHEN status="Scheduled" OR status="Begin trip" OR status="End Trip" THEN ( 6371 * acos( cos( radians(' . $data_array['drop_latitude'] . ') ) * cos( radians( drop_latitude ) ) * cos(radians( drop_longitude ) - radians(' . $data_array['drop_longitude'] . ') ) + sin( radians(' . $data_array['drop_latitude'] . ') ) * sin( radians( drop_latitude ) ) ) ) 
                        ELSE 999999999 END) as distance'
                    )
                )
                ->having('distance', '<', site_settings('drop_km'));
            }])
            ->find($near_car->pool_trip_id);

            $trip_destinations_count = $trip_destinations->trips->count();

            return ($trip_destinations_count > 0);
        })->values();

        if ($array['is_wallet'] == 'Yes') {
            if ($array['payment_method'] == '') {
                $payment_method_store = 'Wallet';
            } else {
                $payment_method_store = $array['payment_method'] . ' & Wallet';
            }
        } else {
            $payment_method_store = ucfirst($array['payment_method']);
        }

        $i = 0;

        $requestSendCount = RideRequest::where('group_id', $array['driver_group_id'])->where('status', 'Cancelled')->count();
        
        if ($i < $nearest_car->count() && $requestSendCount < site_settings('driver_request_limit')) {

            $nearest_car = $nearest_car[$i];
            $driver_details = User::where('id', $nearest_car->user_id)->first();
            //check the request are accepted or not
            $request_accepted = RideRequest::where('group_id', $array['driver_group_id'])->where('status', 'Accepted')->count();

            //some times request inserts duplicates so we check already insert or not for same rider id and driver id with this group
            $request_already = RideRequest::where('user_id', $array['rider_id'])->where('driver_id', $nearest_car->user_id)->where('group_id', $array['driver_group_id'])->count();

            if (!$request_accepted) {
                if (!$request_already) {
                    $last_second = RideRequest::where('driver_id', $nearest_car->user_id)->where('status', 'Pending')->count();
                    if (!$last_second) {

                        $get_min_time = $this->GetDrivingDistance($array['pickup_latitude'], $nearest_car->latitude, $array['pickup_longitude'], $nearest_car->longitude);

                        if ($get_min_time['status'] != "success") {
                            /*return response()->json([
                                'status_code' => '0',
                                'status_message' => $get_min_time['msg'],
                            ]);*/
                            return false;
                        }

                        $get_near_car_time = round(floor(round($get_min_time['time'] / 60)));
                        if ($get_near_car_time == 0) {
                            $get_near_car_time = 1;
                        }

                        $request = new Request;
                        $driverCompanyId = optional($driver_details)->company_id;
                        $serviceCategory = $array['service_category'] ?? 'ride';
                        $mergedExtraFare = $this->mergedExtraFareConfig(
                            $array['location_id'] ?? null,
                            $driverCompanyId,
                            $serviceCategory,
                            (int) ($array['car_id'] ?? 0),
                            $array['timezone'] ?? config('app.timezone')
                        );
                        $request->user_id = $array['rider_id'];
                        $request->group_id = $array['driver_group_id'] ?? null;
                        $request->billing_mode = $array['billing_mode'];
                        $request->corporate_account_id = $array['corporate_account_id'];
                        $request->seats = $array['seats'] ?? 1;
                        $request->pickup_latitude = $array['pickup_latitude'] ?? null;
                        $request->pickup_longitude = $array['pickup_longitude'] ?? null;
                        $request->drop_latitude = $array['drop_latitude'] ?? null;
                        $request->drop_longitude = $array['drop_longitude'] ?? null;
                        $request->driver_id = $nearest_car->user_id;
                        $request->car_id = $array['car_id'] ?? null;
                        $request->pickup_location = $array['pickup_location'] ?? '';
                        $request->drop_location = $array['drop_location'] ?? '';
                        $request->booking_for_type = $array['booking_for_type'] ?? 'for_me';
                        $request->booking_contact_name = $array['booking_contact_name'] ?? '';
                        $request->booking_contact_phone = $array['booking_contact_phone'] ?? '';
                        $request->stop_points = $array['stop_points'] ?? '';
                        $request->payment_mode = $payment_method_store;
                        $request->status = 'Pending';
                        $request->timezone = $array['timezone'] ?? config('app.timezone');
                        $request->schedule_id = $array['schedule_id'] ?? null;
                        $request->location_id = $array['location_id'] ?? null;
                        $request->additional_fare = $array['additional_fare'] ?? 0;
                        $request->estimated_fare = $array['estimated_fare'] ?? $array['fare_estimation'] ?? 0;
                        $request->distance_estimate = $array['distance_estimate'] ?? null;
                        $request->extra_fare_percentage = round((float) ($mergedExtraFare['enabled'] ? ($mergedExtraFare['percentage'] ?? 0) : 0), 2);
                        $request->extra_fare_reason = $mergedExtraFare['enabled'] ? ($mergedExtraFare['reason'] ?? null) : null;
                        $request->surge_pricing_id = $mergedExtraFare['surge']['surge_pricing_id'] ?? null;
                        $request->surge_name = $mergedExtraFare['surge']['name'] ?? null;
                        $request->surge_percentage = round((float) ($mergedExtraFare['surge']['percentage'] ?? 0), 2);
                        $request->peak_fare = $array['peak_price'] ?? 0;
                        $request->additional_rider = $array['additional_rider'] ?? fees('additional_rider_fare');
                        $request->trip_path = $array['trip_path'] ?? '';
                        $request->created_at = date('Y-m-d H:i:s');
                        $request->save();

                        $group_id = @RideRequest::select('group_id')->orderBy('group_id', 'DESC')->first()->group_id;
                        if ($group_id == null) {
                            $group_id = 1;
                        } else {
                            if ($array['driver_group_id']) {
                                $group_id = $array['driver_group_id'];
                            } else {
                                $group_id = $request->id;
                            }
                        }

                        $array['driver_group_id'] = $group_id;
                        $last_id = $request->id;
                        $last_created_at = $request->created_at;
                        RideRequest::where('id', $request->id)->update(['group_id' => $group_id]);
                        $last_created_count = @RideRequest::where('driver_id', $nearest_car->user_id)->where('created_at', $last_created_at)->get();
                        if ($last_created_count->count() > 1) {
                            $result_req = RideRequest::find($request->id);
                            RideRequest::where('id', '!=', $last_created_count[0]->id)->where('driver_id', $last_created_count[0]->driver_id)->where('created_at', $last_created_at)->forceDelete();
                        }
                        $check = RideRequest::find(@$request->id);
                        if (@$check) {
                            $car_fare = $nearest_car->car_type->manage_fare;
                            $push_data['push_title'] = __('messages.api.trip_request');
                            $push_data['data'] = [
                                'ride_request' => [
                                    'billing' => $request->billing,
                                    'payment_mode' => $request->payment_mode,
                                    'title'             => __('messages.api.trip_request'),
                                    'request_id'        => $request->id,
                                    'pickup_location'   => $array['pickup_location'],
                                    'drop_location'     => $array['drop_location'],
                                    'min_time'          => $get_near_car_time,
                                    'fare_estimation'   => @$array['fare_estimation'],
                                    'estimated_fare'    => @$array['estimated_fare'],
                                    'distance_estimate' => @$array['distance_estimate'],
                                    'pickup_latitude'   => @$array['pickup_latitude'],
                                    'pickup_longitude'  => @$array['pickup_longitude'],
                                    'is_pool'           => ($car_fare->capacity > 1 && $vehicle_type->is_pool == "Yes") ? true : false,
                                    'seat_count'        => $array['seats']
                                ]
                            ];
                            $last_driver = session('last_driver');
                            if ($last_driver > 0) {
                                \Log::error('delete firbase Notification ' . $last_driver);
                                $firbase->deleteReference("Notification/" . $last_driver);
                            }

                            session(['last_driver' => $driver_details->id]);
                            $this->SendPushNotification($driver_details, $push_data);
                        }

                        /*Driver Request Time Updated For Next Driver Move*/
                        $nexttick = time() + site_settings('driver_request_seconds');
                        $active = true;
                        while ($active) {
                            if (time() >= $nexttick) {
                                $array['request'] = $request;
                                $active = $this->delay_calling($array);
                            }
                        }
                    } else {
                        $this->find_driver($array);
                    }
                }
            }
        } else {
            $last_driver = session('last_driver');
            if ($last_driver > 0) {
                $firbase->deleteReference("Notification/" . $last_driver);
            }

            $check_group_finish = RideRequest::where('group_id', $array['driver_group_id'])->where('status', 'Pending')->count();

            if ($check_group_finish) {
                Logger('Else Check');
                $this->find_driver($array);
            } else {
                $rider_details = @User::where('id', $array['rider_id'])->first();
                $push_title = __('messages.no_cars_found');
                $data = [
                    'no_cars' => [
                        'status' => 0,
                        'title' => $push_title,
                    ]
                ];
                if (!isset($array['booking_type']) || $array['booking_type'] == 'Schedule Booking') {
                    $push_data['push_title'] = $push_title;
                    $push_data['data'] = $data;
                    $this->SendPushNotification($rider_details, $push_data);
                }
                if ($array['schedule_id'] != '') {
                    $data = ['schedule_cars' => ['status' => 0]];
                    ScheduleRide::where('id', $array['schedule_id'])->update(['status' => 'Car Not Found']);
                }
            }
        }
        \Log::error('function excute successfully');
    }

    /**
     * If manual booking then directly assign trip to driver
     *
     * @param Array $array
     * @return void
     */
    public function trip_assign($array)
    {
        return DB::transaction(fn () => $this->persistAssignedTrip($array), 3);
    }

    protected function persistAssignedTrip($array)
    {
        $additional_fare = "";
        $peak_price = 0;
        //change ScheduleRide status to completed
        $schedule = ScheduleRide::findOrFail($array['schedule_id']);
        app(\App\Services\Corporate\CorporateBillingContext::class)->assertCanAccept($schedule);
        $schedule = ScheduleRide::query()->lockForUpdate()->findOrFail($schedule->id);
        abort_unless($schedule->status === 'Pending', 409);
        $driver = User::find($schedule->driver_id);
        $mergedExtraFare = $this->mergedExtraFareConfig(
            $schedule->location_id,
            optional($driver)->company_id,
            $schedule->service_category ?? 'ride',
            (int) $schedule->car_id,
            $schedule->timezone,
            trim(($schedule->schedule_date ?? '').' '.($schedule->schedule_time ?? '')) ?: null
        );
        $scheduleExtraFarePercentage = (float) ($schedule->extra_fare_percentage ?? ($mergedExtraFare['enabled'] ? $mergedExtraFare['percentage'] : 0));
        $scheduleExtraFareReason = $schedule->extra_fare_reason ?? (($mergedExtraFare['enabled'] ?? false) ? $mergedExtraFare['reason'] : null);
        
        if (isset($schedule->peak_id) != '') {
            $fare = PeakFareDetail::find($schedule->peak_id);
            if ($fare) {
                $peak_price = $fare->price;
                $additional_fare = "Peak";
            }
        }

        date_default_timezone_set($schedule->timezone);

        //Insert record in RideRequest table
        $ride_request = new RideRequest;
        $ride_request->user_id = $schedule->user_id;
        $ride_request->pickup_latitude = $schedule->pickup_latitude;
        $ride_request->pickup_longitude = $schedule->pickup_longitude;
        $ride_request->drop_latitude = $schedule->drop_latitude;
        $ride_request->drop_longitude = $schedule->drop_longitude;
        $ride_request->driver_id = $schedule->driver_id;
        $ride_request->car_id = $schedule->car_id;
        $ride_request->pickup_location = $schedule->pickup_location;
        $ride_request->drop_location = $schedule->drop_location;
        $ride_request->booking_for_type = $schedule->booking_for_type ?? 'for_me';
        $ride_request->booking_contact_name = $schedule->booking_contact_name ?? '';
        $ride_request->booking_contact_phone = $schedule->booking_contact_phone ?? '';
        $ride_request->stop_points = $schedule->stop_points ?? '';
        $ride_request->payment_mode = $schedule->payment_method;
        $ride_request->status = 'Accepted';
        $ride_request->timezone = $schedule->timezone;
        $ride_request->schedule_id = $schedule->id;
        $ride_request->location_id = $schedule->location_id;
        $ride_request->additional_fare = $additional_fare;
        $ride_request->peak_fare = $peak_price;
        $ride_request->estimated_fare = $schedule->fare_estimation;
        $ride_request->extra_fare_percentage = $scheduleExtraFarePercentage;
        $ride_request->extra_fare_reason = $scheduleExtraFareReason;
        $ride_request->surge_pricing_id = $schedule->surge_pricing_id ?? ($mergedExtraFare['surge']['surge_pricing_id'] ?? null);
        $ride_request->surge_name = $schedule->surge_name ?? ($mergedExtraFare['surge']['name'] ?? null);
        $ride_request->surge_percentage = $schedule->surge_percentage ?? round((float) ($mergedExtraFare['surge']['percentage'] ?? 0), 2);
        $ride_request->additional_rider = fees('additional_rider_fare');
        $ride_request->save();

        $group_id = @RideRequest::select('group_id')->orderBy('group_id', 'DESC')->first()->group_id;
        if ($group_id == null) {
            $group_id = 1;
        } else {
            $group_id = $ride_request->id;
        }

        $ride_request->group_id = $group_id;
        $ride_request->save();

        $url = \App::runningInConsole() ? SITE_URL : url('/');
        $src = $url . '/images/user.jpeg';
        
        //Insert record in Trips table
        $car_type = CarType::find($ride_request->car_id);
        $fare_details = ManageFare::where('location_id', $ride_request->location_id)->where('vehicle_id', $ride_request->car_id)->first();
        $driver_status = $car_type->is_pool == 'Yes' ? 'Pool Trip' : 'Trip';

        if ($car_type->is_pool == 'Yes') {
            $driver_location = DriverLocation::where('user_id', $schedule->driver_id)->where('car_id', $ride_request->car_id)->first();
            if (!$driver_location->pool_trip_id) {
                $pool_trip = new PoolTrip;
                $pool_trip->driver_id = $schedule->driver_id;
                $pool_trip->pickup_latitude = $ride_request->pickup_latitude;
                $pool_trip->pickup_longitude = $ride_request->pickup_longitude;
                $pool_trip->drop_latitude = $ride_request->drop_latitude;
                $pool_trip->drop_longitude = $ride_request->drop_longitude;
                $pool_trip->seats = $fare_details->capacity - 1;
                $pool_trip->pickup_location = $ride_request->pickup_location;
                $pool_trip->drop_location = $ride_request->drop_location;
            } else {
                $pool_trip = PoolTrip::find($driver_location->pool_trip_id);
                $pool_trip->drop_latitude = $ride_request->drop_latitude;
                $pool_trip->drop_longitude = $ride_request->drop_longitude;
                $pool_trip->seats = $pool_trip->seats - 1;
                $pool_trip->drop_location = $ride_request->drop_location;
            }

            $pool_trip->status = 'Scheduled';
            $pool_trip->currency_code = $schedule->driver->currency->code;
            $pool_trip->save();

            $pool_trip_id = $pool_trip->id;
            DriverLocation::where('user_id', $schedule->driver_id)->update(['pool_trip_id' => $pool_trip_id]);
        }

        $trip = new Trips;
        $trip->user_id = $schedule->user_id;
        $trip->pool_id = $pool_trip_id ?? '0';
        $trip->otp = mt_rand(1000, 9999);
        $trip->pickup_latitude = $schedule->pickup_latitude;
        $trip->pickup_longitude = $schedule->pickup_longitude;
        $trip->drop_latitude = $schedule->drop_latitude;
        $trip->drop_longitude = $schedule->drop_longitude;
        $trip->driver_id = $schedule->driver_id;
        $trip->car_id = $schedule->car_id;
        $trip->pickup_location = $schedule->pickup_location;
        $trip->drop_location = $schedule->drop_location;
        $trip->request_id = $ride_request->id;
        $trip->trip_path = $schedule->trip_path;
        $trip->payment_mode = $schedule->payment_method;
        $trip->status = 'Scheduled';
        $trip->currency_code = $schedule->users->currency->code;
        $trip->peak_fare = $ride_request->peak_fare;
        $trip->extra_fare_percentage = $ride_request->extra_fare_percentage ?? 0;
        $trip->extra_fare_reason = $ride_request->extra_fare_reason;
        $trip->surge_pricing_id = $ride_request->surge_pricing_id;
        $trip->surge_name = $ride_request->surge_name;
        $trip->surge_percentage = $ride_request->surge_percentage ?? 0;
        $trip->extra_fare_amount = 0;
        $trip->additional_rider = $ride_request->additional_rider;
        $trip->save();

        DriverLocation::where('user_id', $ride_request->driver_id)->update(['status' => $driver_status]);

        $schedule = ScheduleRide::find($array['schedule_id']);
        $schedule->status = 'Completed';
        $schedule->save();

        $apply_extra_fee = fees('additional_fee');
        
        $driver_details = @User::where('id', $ride_request->driver_id)->first();
        
        $get_near_car_time = 1;
        $device_type = $driver_details->device_type;
        $device_id = $driver_details->device_id;
        $user_type = $driver_details->user_type;
        $push_title = "Trip Assigned";
        
        $data = ['manual_booking_trip_assigned' => [
            'billing' => $trip->billing,
            'status'            => 'Arrive Now',
            'request_id'        => $ride_request->id,
            'pickup_location'   => $array['pickup_location'],
            'min_time'          => $get_near_car_time,
            'pickup_latitude'   => @$array['pickup_latitude'],
            'pickup_longitude'  => @$array['pickup_longitude'],
            'pickup_location'   => @$array['pickup_location'],
            'drop_longitude'    => @$array['drop_longitude'],
            'drop_latitude'     => @$array['drop_latitude'],
            'drop_location'     => @$array['drop_location'],
            'trip_id'           => $trip->id,
            'otp'               => $trip->otp,
            'rider_id'          => $ride_request->users->id,
            'rider_name'        => $ride_request->users->first_name,
            'mobile_number'     => $ride_request->users->phone_number,
            'rider_thumb_image' => (@$ride_request->users->profile_picture == null) ? $src : $ride_request->users->profile_picture->src,
            'rating_value'      => '',
            'car_type'          => $ride_request->car_type->car_name,
            'car_active_image'  => $ride_request->car_type->active_image,
            'payment_method'    => $ride_request->payment_mode,
            'booking_type'      => (@$trip->ride_request->schedule_ride->booking_type == null) ? "" : @$trip->ride_request->schedule_ride->booking_type,
            'apply_trip_additional_fee' => ($apply_extra_fee == 'Yes'),
        ]];

        $push_data['push_title'] = $push_title;
        $push_data['data'] = $data;

        $this->checkAndSendMessage($driver_details, '', $push_data);

        $push_title = __('messages.api.manual_booking_update');
        $text = 'Your one-time password to begin trip is ' . $trip->otp;
        $push_data['push_title'] = $push_title;
        $push_data['data'] = [
            'custom_message' => [
                'title' => $push_title,
                'message_data' => $text,
            ]
        ];

        $this->checkAndSendMessage($trip->users, $text, $push_data);
    }

    /**
     * Delay Calling Requests
     *
     * @param Array $array
     * @return void
     */
    public function delay_calling($array)
    {
        $request_status = RideRequest::where('id', $array['request']->id)->get();
        
        if ($request_status->count()) {
            if ($request_status[0]->status == 'Pending') {
                RideRequest::where('id', $array['request']->id)->update(['status' => 'Cancelled']);
                $this->find_driver($array);
            }
        } else {
            $this->find_driver($array);
        }
        return false;
    }

    /**
     * Send Push Notification to iOS device
     *
     * @param String $push_title
     * @param Array $data
     * @param String $user_type
     * @param String $device_id
     * @param Boolean $change_title
     * @param Array $firebase_data
     * @return void
     */
    public function push_notification_ios($push_title, $data, $user_type, $device_id, $change_title = 0, $firebase_data = [])
    {
        $credentialsPath = $this->getFirebaseServiceAccountPath();
        logger('Firebase credentials (iOS): ' . ($credentialsPath ?: 'null'));
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            logger('Push notification error (iOS): Firebase credentials not found or invalid at ' . ($credentialsPath ?: base_path()));
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $messaging = $factory->createMessaging();

            $title = $this->resolveFirebaseNotificationTitle($push_title, $data, $user_type, $change_title);
            $body = $this->resolveFirebaseNotificationBody($push_title, $data);

            $notification = Notification::create($title, $body);
                //->withSound('default');
            $message = CloudMessage::withTarget('token', $device_id)
                ->withNotification($notification)
                ->withData($this->buildFirebaseDataPayload($data, $firebase_data))
                ->withApnsConfig([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ]);

            $messaging->send($message);
            logger('Push notification sent to iOS device: ' . $device_id);
        } catch (\Exception $e) {
            logger('Push notification error (iOS): ' . $e->getMessage());
        }
    }

    /**
     * Send Push Notification to Android device
     *
     * @param String $push_title
     * @param Array $data
     * @param String $user_type
     * @param String $device_id
     * @param Boolean $change_title
     * @param Array $firebase_data
     * @return void
     */
    public function push_notification_android($push_title, $data, $user_type, $device_id, $change_title = 0, $firebase_data = [])
    {
        $credentialsPath = $this->getFirebaseServiceAccountPath();
        logger('Firebase credentials (Android): ' . ($credentialsPath ?: 'null'));
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            logger('Push notification error (Android): Firebase credentials not found or invalid at ' . ($credentialsPath ?: base_path()));
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $messaging = $factory->createMessaging();

            $title = $this->resolveFirebaseNotificationTitle($push_title, $data, $user_type, $change_title);
            $body = $this->resolveFirebaseNotificationBody($push_title, $data);

            $notification = Notification::create($title, $body);
                //->withSound('default');
            $message = CloudMessage::withTarget('token', $device_id)
                ->withNotification($notification)
                ->withData($this->buildFirebaseDataPayload($data, $firebase_data))
                ->withAndroidConfig([
                    'ttl' => '15s',
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                    ],
                ]);

            $messaging->send($message);
            logger('Push notification sent to Android device: ' . $device_id);
        } catch (\Exception $e) {
            logger('Push notification error (Android): ' . $e->getMessage());
        }
    }

    /**
     * Get Driving Distance
     *
     * @param Float $lat1
     * @param Float $lat2
     * @param Float $long1
     * @param Float $long2
     * @return array $distance_data
     */
    public function GetDrivingDistance($lat1, $lat2, $long1, $long2)
    {
        $google_service = resolve("google_service");
        return $google_service->GetDrivingDistance($lat1, $lat2, $long1, $long2);
    }

    /**
     * Get Polyline
     *
     * @param Float $lat1
     * @param Float $lat2
     * @param Float $long1
     * @param Float $long2
     * @return String $polyline
     */
    public function GetPolyline($lat1, $lat2, $long1, $long2)
    {
        $google_service = resolve("google_service");
        return $google_service->GetPolyline($lat1, $lat2, $long1, $long2);
    }

    public function GetRoutePolyline($origin, $destination, $waypoints = [])
    {
        $google_service = resolve("google_service");
        return $google_service->GetRoutePolyline($origin, $destination, $waypoints);
    }

    /**
     * Get Country
     *
     * @param Float $lat1
     * @param Float $long1
     * @return String $country
     */
    public function GetCountry($lat1, $long1)
    {
        $google_service = resolve("google_service");
        return $google_service->GetCountry($lat1, $long1);
    }

    /**
     * Get Location data with Geocode
     *
     * @param Float $lat1
     * @param Float $long1
     * @return String $address
     */
    public function GetLocation($lat1, $long1)
    {
        $google_service = resolve("google_service");
        return $google_service->GetLocation($lat1, $long1);
    }

    /**
     * Clear all the pending ride requests
     *
     * @return Void
     */
    protected function clearPending()
    {
        $request = RideRequest::where('created_at', '<', Carbon::now()->subMinutes(2)->toDateTimeString())->where('status', 'Pending')->get();
        if ($request) {
            $firbase = resolve(\App\Services\FirebaseService::class);
            foreach ($request as $request_val) {
                $firbase->deleteReference("Notification/" . $request_val->driver_id);
                RideRequest::where('id', $request_val->id)->update(['status' => 'Cancelled']);
            }
        }
    }

    /**
     * Ignore manually assigned drivers from find driver
     *
     * @return Array $drivers
     */
    public function ignoreAssigned($arr = [])
    {
        $current_date = date('Y-m-d');
        $current_time = date('H:i');
        if (isset($arr['start_date']) && isset($arr['start_time'])) {
            $current_date = $arr['start_date'];
            $current_time = $arr['start_time'];
        }

        $pickup_latitude = $arr['pickup_latitude'];
        $pickup_longitude = $arr['pickup_longitude'];
        $drop_latitude = $arr['drop_latitude'];
        $drop_longitude = $arr['drop_longitude'];
        $car_id = $arr['car_id'];

        $time = strtotime($current_time);
        $startTime = date("H:i", strtotime('-10 minutes', $time));

        return ScheduleRide::select('driver_id')->where('driver_id', '>', '0')->where('car_id', $car_id)->where('schedule_date', $current_date)->where('status', 'Pending')->whereRaw("time(schedule_end_time) > '$startTime' ")->where('id', '!=', $arr['schedule_id'])->pluck('driver_id')->toArray();
    }

    /**
     * Check Device Id And Send Message
     *
     * @return Boolean
     */
    public function checkAndSendMessage($user, $text, $push_data)
    {
        $device_type = $user->device_type;
        $device_id = $user->device_id;
        $user_type = $user->user_type;
        $to = $user->phone_number;
        if ($text != '') {
            $sms_gateway = resolve(\App\Contracts\SMSInterface::class);
            $sms_gateway->send($to, $text);
        }

        return $this->SendPushNotification($user, $push_data);
    }

    /**
     * Send Push Notification to Users based on their device
     *
     * @return Boolean
     */
    public function SendPushNotification($user, $push_data)
    {
        $device_type = $user->device_type;
        $device_id = $user->device_id;
        $user_type = $user->user_type;

        if (empty($device_id)) {
            return false;
        }

        $push_title = $push_data['push_title'];
        $data = $push_data['data'];
        $change_title = $push_data['change_title'] ?? 0;

        $firebase_data = $data;
        $firebase_data['id'] = time();
        $firebase_data['end_time'] = $firebase_data['id'] + getDriverSec();
        $firebase_data['title'] = $push_title;

        $firebase = resolve(\App\Services\FirebaseService::class);
        try {
            $firebase->updateReference("Notification/" . $user->id, json_encode(["custom" => $firebase_data]));
        } catch (\Throwable $e) {
            logger("Firebase Notification Reference Failure: " . $e->getMessage());
        }

        try {
            if ($device_type == 1) {
                $sent = $firebase->sendAppleNotification($push_title, $data, $user_type, $device_id, $change_title, $firebase_data);
                logger("iOS Push");
            } else {
                $sent = $firebase->sendAndroidNotification($push_title, $data, $user_type, $device_id, $change_title, $firebase_data);
                logger("Android Push");
            }
        } catch (\Throwable $e) {
            logger("Push Failure: " . $e->getMessage());
            return false;
        }

        return (bool) ($sent ?? false);
    }

    /**
     * Get Timezone
     *
     * @param Float $lat1
     * @param Float $long1
     * @return String $timezone
     */
    public function getTimeZone($lat1, $long1)
    {
        $google_service = resolve("google_service");
        return $google_service->getTimeZone($lat1, $long1);
    }

    /**
     * Resolve the Firebase service account JSON file path from api_credentials.
     *
     * The admin send-message flow should use the same credential source as the
     * Firebase service instead of relying on config/services.php.
     *
     * @return string|null
     */
    protected function getFirebaseServiceAccountPath()
    {
        $credential = api_credentials('service_account', 'Firebase');

        if (!$credential || $credential === '#') {
            return null;
        }

        return base_path() . $credential;
    }

    /**
     * Firebase Cloud Messaging data payload values must be strings.
     *
     * Keep the nested application payload under the `custom` key as JSON and
     * cast any top-level metadata to strings to avoid "Array to string conversion".
     *
     * @param array $data
     * @param array $firebase_data
     * @return array<string,string>
     */
    protected function buildFirebaseDataPayload(array $data, array $firebase_data = [])
    {
        $payload = [
            'custom' => json_encode($data),
        ];

        foreach ($firebase_data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $payload[$key] = json_encode($value);
                continue;
            }

            $payload[$key] = (string) $value;
        }

        return $payload;
    }

    /**
     * Resolve the visible push notification title.
     *
     * @param string $push_title
     * @param array $data
     * @param string $user_type
     * @param int|bool $change_title
     * @return string
     */
    protected function resolveFirebaseNotificationTitle($push_title, array $data, $user_type, $change_title = 0)
    {
        $defaultTitle = $change_title ? $push_title : $user_type;

        if (isset($data['custom_message']) && is_array($data['custom_message'])) {
            return $data['custom_message']['push_title']
                ?? $data['custom_message']['title']
                ?? $defaultTitle;
        }

        if (isset($data['chat_notification']) && is_array($data['chat_notification'])) {
            return $data['chat_notification']['user_name']
                ?? $defaultTitle;
        }

        return $defaultTitle;
    }

    /**
     * Resolve the visible push notification body.
     *
     * @param string $push_title
     * @param array $data
     * @return string
     */
    protected function resolveFirebaseNotificationBody($push_title, array $data)
    {
        if (isset($data['custom_message']) && is_array($data['custom_message'])) {
            return $data['custom_message']['message_data']
                ?? $data['custom_message']['title']
                ?? $push_title;
        }

        if (isset($data['chat_notification']) && is_array($data['chat_notification'])) {
            return $data['chat_notification']['message_data']
                ?? $data['chat_notification']['title']
                ?? $push_title;
        }

        return $push_title;
    }
}
