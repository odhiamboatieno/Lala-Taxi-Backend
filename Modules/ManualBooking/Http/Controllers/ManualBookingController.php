<?php

/**
 * manual Booking Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    manual Booking
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace Modules\ManualBooking\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Models\ProfilePicture;
use App\Models\DriverLocation;
use App\Models\Location;
use App\Models\ManageFare;
use App\Models\CarType;
use App\Models\ScheduleRide;
use App\Models\PeakFareDetail;
use App\Models\Trips;
use App\Models\Currency;
use App\Models\FilterObject;
use App\Models\ManualBookingDraft;
use App\Models\ManualBookingTemplate;
use App\Models\SiteSettings;
use App\Models\Request as RideRequest;
use App\Models\ScheduleAssignmentAttempt;
use App\Http\Helper\RequestHelper;
use App\Services\Dispatch\ManualDispatchConfigService;
use Carbon\Carbon;
use Validator;
use DB;
use Auth;

class ManualBookingController extends Controller
{    
    protected $dispatchConfig;

    public function __construct(RequestHelper $request, ManualDispatchConfigService $dispatchConfig)
    {
        $this->request_helper = $request;
        $this->dispatchConfig = $dispatchConfig;
    }
    public function index($id=null)
    {
        $data['schedule_ride'] = ScheduleRide::with('users')->where('id',$id)->where('status','Pending')->where('booking_type','Manual Booking')->first();
        if ($id != null && $data['schedule_ride'] == null) {
           abort("404");
        }

        //Company can edit it's driver booking and Company's booking
        if (LOGIN_USER_TYPE=='company' && $id!=null && Auth::guard('company')->user()->id != $data['schedule_ride']->company_id && Auth::guard('company')->user()->id != @$data['schedule_ride']->driver->company_id) {
            abort("404");
        }

        $timezone = optional($data['schedule_ride'])->timezone ?? date_default_timezone_get();

        $date_obj = \Carbon\Carbon::now()->setTimezone($timezone);

        $data['timezon'] = $timezone;
        $data['country_code_option'] = Country::select('long_name','phone_code','id')->get();
        $data['vehicle_types'] = CarType::select('id','car_name')->get();
        $company = LOGIN_USER_TYPE == 'company' ? Auth::guard('company')->user() : null;
        $defaultDispatchCountry = $this->resolveDispatchCountry($data['schedule_ride'], $company, $data['country_code_option']);

        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {
            $default_currency = Currency::whereCode(session()->get('currency'))->first();
        }
        else {
            $default_currency = view()->shared('default_currency');
        }
        if (!$default_currency) {
            $default_currency = Currency::active()->defaultCurrency()->first()
                ?: Currency::active()->first()
                ?: Currency::first();
        }
        $data['currency_symbol'] = html_string(optional($default_currency)->symbol ?: session('symbol') ?: '$');
        $data['dispatch_settings'] = $this->dispatchConfig->resolve($company);
        $data['dispatch_queue_counts'] = $this->dispatchQueueCounts($company);
        $data['default_dispatch_country'] = $defaultDispatchCountry;
        $data['manual_booking_defaults'] = [
            'country_id'      => optional($defaultDispatchCountry)->id ?: 1,
            'country_name'    => optional($defaultDispatchCountry)->long_name ?: 'Default Country',
            'phone_code'      => (string) (optional($defaultDispatchCountry)->phone_code ?: ''),
            'currency_symbol' => trim(strip_tags((string) $data['currency_symbol'])) ?: '$',
            'timezone'        => $timezone ?: config('app.timezone', 'UTC'),
            'distance_unit'   => strtolower((string) (site_settings('distance_unit') ?: 'km')),
            'queue_total'     => array_sum($data['dispatch_queue_counts'] ?? []),
            'service_area'    => optional(Location::active()->orderBy('id')->first())->name ?: 'Default Service Area',
            'multi_country'   => false,
            'company_id'      => optional($company)->id,
        ];
        $data['manual_booking_drafts'] = $this->draftQuery()->latest('updated_at')->limit(10)->get();
        $data['manual_booking_templates'] = $this->templateQuery()->latest('updated_at')->limit(12)->get();
        $data['manual_booking_payload'] = $this->scheduleRidePayload($data['schedule_ride']);
        $data['manual_booking_success'] = session('manual_booking_success');

        return view('manualbooking::manual_booking.index',$data);
    }

    public function store(Request $request) {

        $rider = User::where('mobile_number',$request->mobile_number)->where('user_type','Rider')->first();

        if ($rider==null) {
            $rider = new User;
            $rider->first_name  = $request->first_name;
            $rider->last_name   = $request->last_name;
            $rider->email       = $request->email;
            $rider->country_code= $request->country_code;
            $rider->country_id  = $request->country_id;
            $rider->mobile_number = $request->mobile_number;
            $rider->user_type   = 'Rider';
            $rider->save();

            $profile               = new ProfilePicture;
            $profile->user_id      = $rider->id;
            $profile->src          = '';
            $profile->photo_source = 'Local';
            $profile->save();
        }

        $company = LOGIN_USER_TYPE == 'company' ? Auth::guard('company')->user() : null;
        $dispatchSettings = $this->dispatchConfig->resolve($company);
        $experienceTab = $this->normalizeBookingExperienceTab($request->input('booking_experience_tab', 'asap'));
        $bookingMode = $experienceTab === 'asap'
            ? 'asap'
            : $this->dispatchConfig->normalizeBookingMode($request->booking_mode ?: 'scheduled');
        $autoAssignEnabled = (bool) $request->has('auto_assign_status');
        $dispatchMode = $this->dispatchConfig->normalizeDispatchMode($request->dispatch_mode, $autoAssignEnabled);
        $assignNow = $request->boolean('assign_now') || $bookingMode === 'asap';
        $isDispatchOverride = $request->boolean('is_dispatch_override');

        try {
            $timezone = $this->request_helper->getTimeZone($request->pickup_latitude, $request->pickup_longitude);

            // logger('location timezone : '.$timezone);

            $polyline = @$this->request_helper->GetPolyline($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

            LogDistanceMatrix("manual booking store");
            $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);
        } catch(\Exception $e) {
            flashMessage('danger', 'Invalid Request');
            return redirect(LOGIN_USER_TYPE.'/later_booking');
        }
        
        $travel_minutes = round(floor(round($get_fare_estimation['time'] / 60)));
        $total_distance = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));

        $timezone = ($timezone == '') ? 'Asia/Kolkata' : $timezone;
        $nowInTimezone = Carbon::now($timezone);
        $scheduledAt = $bookingMode === 'asap'
            ? $nowInTimezone->copy()
            : Carbon::parse($request->date_time, $timezone);

        $minimumLeadMinutes = $this->dispatchConfig->minimumLeadMinutes($bookingMode, $dispatchSettings, $isDispatchOverride);
        if ($minimumLeadMinutes > 0 && $scheduledAt->lt($nowInTimezone->copy()->addMinutes($minimumLeadMinutes))) {
            flashMessage('danger', "Please select a booking time at least {$minimumLeadMinutes} minutes ahead, or enable override.");
            return back()->withInput();
        }

        $dispatchAt = $assignNow
            ? $nowInTimezone->copy()
            : $scheduledAt->copy()->subMinutes($this->dispatchConfig->scheduledDispatchLeadMinutes($dispatchSettings));

        if ($dispatchAt->lt($nowInTimezone)) {
            $dispatchAt = $nowInTimezone->copy();
        }

        $end_time = strtotime("+".$travel_minutes." minutes", strtotime($scheduledAt->format('Y-m-d H:i:s')));

        $fare_details = ManageFare::where('location_id',$request->location_id)->where('vehicle_id',$request->vehicle_type)->first();

        $base_fare = round($fare_details->base_fare + $fare_details->per_km * $total_distance);
        $fare_estimation = numberFormat($base_fare + round($fare_details->per_min * $travel_minutes));

        if ($request->manual_booking_id!=null) {
            $schedule=ScheduleRide::where('status','Pending')->where('id',$request->manual_booking_id)->first();
            if ($schedule==null) {
                flashMessage('danger', 'Invalid ID');
                return redirect(LOGIN_USER_TYPE.'/later_booking');
            }
        } else {
            $schedule = new ScheduleRide;
            //For company user login, company id is logined company id
            if (LOGIN_USER_TYPE=='company') {
                $schedule->company_id = Auth::guard('company')->user()->id;
            }
        }
        $schedule->user_id = $rider->id;
        $schedule->schedule_date = $scheduledAt->format('Y-m-d');
        $schedule->schedule_time = $scheduledAt->format('H:i');
        $schedule->schedule_end_date = date('Y-m-d', $end_time);
        $schedule->schedule_end_time = date('H:i', $end_time);
        $schedule->pickup_latitude = $request->pickup_latitude;
        $schedule->pickup_longitude = $request->pickup_longitude;
        $schedule->drop_latitude = $request->drop_latitude;
        $schedule->drop_longitude = $request->drop_longitude;
        $schedule->car_id = $request->vehicle_type;
        $schedule->pickup_location = $request->pickup_location;
        $schedule->drop_location = $request->drop_location;
        $schedule->via_location = $request->via_location;
        $schedule->via_latitude = $request->via_latitude ?: null;
        $schedule->via_longitude = $request->via_longitude ?: null;
        $schedule->status = 'Pending';
        $schedule->trip_path = @$polyline;
        $schedule->timezone = ($timezone=='')?'Asia/Kolkata':$timezone;
        $schedule->payment_method ='Cash';
        $schedule->is_wallet = 'No';
        $schedule->location_id = $request->location_id;
        $schedule->peak_id = $request->peak_id;
        $schedule->booking_type = 'Manual Booking';
        $schedule->booking_channel = $experienceTab;
        $schedule->workspace_label = $request->input('workspace_label') ?: $request->input('template_name') ?: $request->input('draft_name');
        $schedule->booking_mode = $bookingMode;
        $schedule->dispatch_mode = $dispatchMode;
        $schedule->scheduled_at = $scheduledAt;
        $schedule->dispatch_at = $dispatchAt;
        $schedule->assignment_status = ($autoAssignEnabled || !$request->auto_assign_id) ? 'unassigned' : 'assigned';
        $schedule->assignment_timeout_seconds = $this->dispatchConfig->assignmentTimeoutSeconds($bookingMode, $dispatchSettings);
        $schedule->assigned_driver_id = $request->auto_assign_id ?: null;
        $schedule->last_assignment_attempt_at = null;
        $schedule->dispatcher_note = $request->dispatcher_note;
        $schedule->driver_note = $request->driver_note;
        $schedule->priority = $request->priority ?: 'normal';
        $schedule->is_dispatch_override = $isDispatchOverride;
        $schedule->dispatch_policy_snapshot = $dispatchSettings;
        $schedule->assign_now = $assignNow;
        $schedule->fare_estimation = $fare_estimation;
        $schedule->driver_id = (!isset($request->auto_assign_status))?$request->auto_assign_id:0;
        $schedule->draft_id = $request->input('draft_id') ?: null;
        $schedule->template_id = $request->input('template_id') ?: null;
        $schedule->booking_for_type = $request->input('booking_for_type') ?: $request->input('customer_type');
        $schedule->booking_contact_name = $request->input('booking_contact_name') ?: $request->input('company_name');
        $schedule->booking_contact_phone = $request->input('booking_contact_phone') ?: $request->input('company_phone');
        $schedule->stop_points = $this->encodeStopPoints($request);
        $schedule->booking_capabilities = $this->extractCapabilities($request);
        $schedule->booking_meta = $this->extractBookingMeta($request, $rider, $bookingMode, $dispatchMode, $scheduledAt, $dispatchAt);
        $schedule->save();

        if (!$autoAssignEnabled && $schedule->driver_id) {
            $this->recordAssignmentAttempt($schedule, $schedule->driver_id, $dispatchMode, $assignNow ? 'assigned' : 'offered', [
                'source' => 'manual_booking_store',
                'booking_mode' => $bookingMode,
                'priority' => $schedule->priority,
            ]);
        }
        elseif ($autoAssignEnabled) {
            $this->recordAssignmentAttempt($schedule, null, $dispatchMode, $assignNow ? 'offered' : 'unassigned', [
                'source' => 'manual_booking_store',
                'booking_mode' => $bookingMode,
                'priority' => $schedule->priority,
            ]);
        }

        // Booking notification to driver
        if (!$autoAssignEnabled && !$assignNow) {
            $driver_details = @User::where('id', $schedule->driver_id)->first();
           
            $push_title = __('messages.api.trip_scheduled');
            $data = [
                'manual_booking_trip_booked_info' => [
                    'date' => $schedule->schedule_date,
                    'time' => $schedule->schedule_time,
                    'pickup_location' => $schedule->pickup_location,
                    'pickup_latitude' => $schedule->pickup_latitude,
                    'pickup_longitude' => $schedule->pickup_longitude,
                    'rider_first_name' => $rider->first_name,
                    'rider_last_name' => $rider->last_name,
                    'rider_mobile_number' => $rider->mobile_number,
                    'rider_country_code' => $rider->country_code,
                ]
            ];
            $push_data['push_title'] = $push_title;
            $push_data['data'] = $data;
            $text = trans('messages.trip_booked_driver_message',['date'=>$schedule->schedule_date.' ' .$schedule->schedule_time,'pickup_location'=>$schedule->pickup_location,'drop_location'=>$schedule->drop_location]);

            $this->request_helper->checkAndSendMessage($driver_details,$text,$push_data);
        }
    
        // booking message to user
        $text = trans('messages.trip_booked_user_message',['date'=>$schedule->schedule_date.' ' .$schedule->schedule_time]);
        if (!$autoAssignEnabled) {
            $driver = User::find($request->auto_assign_id);
            $text = $text.trans('messages.trip_booked_driver_detail',['first_name' => $driver->first_name, 'phone_number'=> $driver->mobile_number]);
        }

        $push_title = __('messages.api.trip_booked');
        $push_data['push_title'] = $push_title;
        $push_data['data'] = [
            'custom_message' => [
                'title' => $push_title,
                'message_data' => $text,
            ]
        ];

        $this->request_helper->checkAndSendMessage($rider,$text,$push_data);

        $waiting_time = $fare_details->waiting_time;
        $waiting_charge = $fare_details->currency_code.' '.$fare_details->waiting_charge;

        $push_title = __('messages.driver_arrive');
        $text       = __('messages.api.waiting_charge_apply_after',['amount' => $waiting_charge,'minutes' => $waiting_time]);

        $push_data['push_title'] = $push_title;
        $push_data['data'] = [
            'custom_message' => [
                'title' => $push_title,
                'message_data' => $text,
            ]
        ];

        $this->request_helper->checkAndSendMessage($rider,$text,$push_data);

        if ($assignNow) {
            $dispatchPayload = [ 
                'rider_id' => $schedule->user_id,
                'seats' => 1,
                'pickup_latitude' => $schedule->pickup_latitude,
                'pickup_longitude' => $schedule->pickup_longitude,
                'drop_latitude' => $schedule->drop_latitude,
                'drop_longitude' => $schedule->drop_longitude,
                'user_type' => 'rider',
                'car_id' => $schedule->car_id,
                'driver_group_id' => null,
                'pickup_location' => $schedule->pickup_location,
                'drop_location' => $schedule->drop_location,
                'payment_method' => $schedule->payment_method,
                'is_wallet' => $schedule->is_wallet,
                'timezone' => $schedule->timezone,
                'schedule_id' => $schedule->id,
                'additional_fare' => 0,
                'estimated_fare' => (float) $schedule->fare_estimation,
                'fare_estimation' => (string) $schedule->fare_estimation,
                'distance_estimate' => (string) $total_distance,
                'location_id' => $schedule->location_id,
                'peak_price' => 0,
                'booking_type' => $schedule->booking_type,
                'driver_id' => $schedule->driver_id,
                'trip_path' => $schedule->trip_path ?? '',
                'booking_for_type' => $schedule->booking_for_type ?: 'for_me',
                'booking_contact_name' => $schedule->booking_contact_name ?: '',
                'booking_contact_phone' => $schedule->booking_contact_phone ?: '',
                'stop_points' => $schedule->stop_points ?: '',
            ];

            if ($schedule->driver_id > 0 && in_array($dispatchMode, ['manual', 'force'], true)) {
                $this->request_helper->trip_assign($dispatchPayload);
                $schedule->refresh();
                $this->recordAssignmentAttempt($schedule, $request->auto_assign_id, $dispatchMode, 'assigned', [
                    'source' => 'manual_booking_dispatch_now',
                    'assignment_type' => 'direct',
                ]);
            }
            else {
                $this->request_helper->find_driver($dispatchPayload);
                $schedule->refresh();
            }
        }

        $returnSchedule = $this->storeReturnTripIfRequested($request, $schedule, $fare_details, $dispatchSettings);
        $recurringSchedules = $this->storeRecurringSchedulesIfRequested($request, $schedule, $fare_details, $dispatchSettings);
        $successSummary = [
            'schedule_id' => $schedule->id,
            'return_schedule_id' => optional($returnSchedule)->id,
            'recurring_count' => count($recurringSchedules),
            'rider_name' => trim($rider->first_name.' '.$rider->last_name),
            'pickup_location' => $schedule->pickup_location,
            'drop_location' => $schedule->drop_location,
            'fare_estimation' => $schedule->fare_estimation,
        ];
        
        if ($request->manual_booking_id!=null) {
            flashMessage('success', 'Updated Successfully');
            return redirect(LOGIN_USER_TYPE.'/later_booking');
        }
        flashMessage('success', 'Added Successfully');
        if ($request->boolean('stay_on_console')) {
            return redirect(LOGIN_USER_TYPE.'/manual_booking')->with('manual_booking_success', $successSummary);
        }
        return redirect(LOGIN_USER_TYPE.'/later_booking')->with('manual_booking_success', $successSummary);
    }

    public function saveDraft(Request $request)
    {
        $draft = ManualBookingDraft::updateOrCreate(
            ['id' => $request->input('draft_id')],
            array_merge($this->dispatcherContext(), [
                'name' => trim((string) ($request->input('name') ?: 'Untitled Draft')),
                'payload' => $this->sanitizeWorkspacePayload($request->input('payload', [])),
                'status' => 'Active',
            ])
        );

        return response()->json([
            'status' => 'success',
            'draft' => $draft,
            'message' => 'Draft saved successfully.',
        ]);
    }

    public function listDrafts()
    {
        return response()->json($this->draftQuery()->latest('updated_at')->get());
    }

    public function saveTemplate(Request $request)
    {
        $template = ManualBookingTemplate::updateOrCreate(
            ['id' => $request->input('template_id')],
            array_merge($this->dispatcherContext(), [
                'name' => trim((string) ($request->input('name') ?: 'Untitled Template')),
                'description' => trim((string) $request->input('description')),
                'sharing_scope' => in_array($request->input('sharing_scope'), ['private', 'company'], true) ? $request->input('sharing_scope') : 'private',
                'payload' => $this->sanitizeWorkspacePayload($request->input('payload', [])),
                'status' => 'Active',
            ])
        );

        return response()->json([
            'status' => 'success',
            'template' => $template,
            'message' => 'Template saved successfully.',
        ]);
    }

    public function listTemplates()
    {
        return response()->json($this->templateQuery()->latest('updated_at')->get());
    }

    public function customerHistory(Request $request)
    {
        $phone = trim((string) $request->input('mobile_number'));
        $userId = (int) $request->input('user_id');

        $rider = $userId
            ? User::find($userId)
            : User::where('user_type', 'Rider')->where('mobile_number', $phone)->first();

        if (! $rider) {
            return response()->json([
                'status' => 'success',
                'history' => [],
            ]);
        }

        $history = ScheduleRide::query()
            ->where('user_id', $rider->id)
            ->where('booking_type', 'Manual Booking')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function ($ride) {
                return [
                    'id' => $ride->id,
                    'status' => $ride->status,
                    'pickup' => $ride->pickup_location,
                    'drop' => $ride->drop_location,
                    'date_time' => trim($ride->schedule_date.' '.$ride->schedule_time),
                    'booking_mode' => $ride->booking_mode,
                    'fare_estimation' => $ride->fare_estimation,
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'history' => $history,
        ]);
    }

    //get user by phone number
    public function search_phone(Request $request) {

        $user_details = User::where('user_type',$request->type)->where(function($query) use($request) {
            $query->where('country_code', '')
            ->orWhere('country_code', $request->country_code);
        });

        if($request->text!="") {
            $user_details=$user_details->where('mobile_number', 'like', '%'.$request->text.'%');
        }

        $user_details = $user_details->get();

        $return_data = $user_details->map(function($user) {
            return [
                'id'            => $user->id,
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'email'         => $user->email,
                'mobile_number' => $user->getRawOriginal('mobile_number'),
                'gender'        => $user->gender,
                'filter'        => FilterObject::options('rider',$user->id)
            ];
        });

        return $return_data->toJson();
    }

    //get car type list
    public function search_cars(Request $request)
    {
        $day = date("N", strtotime($request->date_time));
        $current_time = date('H:i:00', strtotime($request->date_time));

        $match_location = Location::select(DB::raw("id,status,(ST_WITHIN( ST_GeomFromText(
                'POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available "))->having('available','1')->where('status','Active')->first();

        if($match_location == null){
            return response()->json([
                'trans_message' => trans('messages.location_unavailable'),
                'status_message' => 'location_unavailable',
                'status_code' => '0',
            ]);
        }elseif($request->pickup_location == $request->drop_location){
            return response()->json([
                'trans_message' => 'Pickup location and Drop location should not be same',
                'status_message' => 'location_unavailable',
                'status_code' => '0',
            ]);
        }else{
            $location_cars = ManageFare::where('location_id',$match_location->id)->get()->toArray();
            $vehicles = array_column($location_cars,'vehicle_id');
            $location_id = $match_location->id;

            if(!$request->filter)
                $request->filter = [];

            $handicap = $child_seat = $request_from = $gender = '';
            if($request->has('user_id') && $request->user_id !='') {
                // insert updated filter
                $filter_insert = FilterObject::optionsInsert('rider',$request->user_id,$request->filter);
                // get user info
                $user_details = User::find($request->user_id);
                $gender = $user_details->gender;
                // get filter
                $options = FilterObject::options('rider',$user_details->id);
            } else {
                $gender = $request->gender;
                $options = $request->filter;
            }

            if(in_array('4', $options)) {
                $request_from = '1';
            }
            if(in_array('2', $options)) {
                $handicap = '1';
            }
            if(in_array('3', $options)) {
                $child_seat = '1';
            }

            $nearest_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))
            ->with(['car_type', 'users'])
            ->with(['users.driver_trips' => function ($query) {
                $query->whereIn('status',['Scheduled','Begin trip','End trip','Rating']);
            }])
            ->with(['manage_fare' => function ($query) use ($location_id) {
                $query->where('location_id', $location_id);
            }])
            ->whereHas('users', function($q2) use($handicap,$child_seat,$request_from,$gender) {
                $q2->activeOnlyStrict()
                ->where(function($query)  {
                    // For company user login, only get that company's driver
                    if(LOGIN_USER_TYPE=='company') {
                        $query->where('company_id',Auth::guard('company')->user()->id);
                    }
                })
                ->whereHas('vehicle', function($q2) use($handicap,$child_seat,$request_from,$gender) {
                    if($handicap) {
                        $q2->whereHas('handicap');
                    }
                    if($child_seat) {
                        $q2->whereHas('child_seat');
                    }
                    if($request_from) {
                        $q2->whereHas('female_driver');
                    }
                    if($gender=='1') {
                        $q2->whereDoesntHave('female');
                    }
                });
            })
            ->whereHas('car_type', function($q2) use($vehicles) {
                $q2->where('status', 'Active')->whereIn('car_id',$vehicles)->where('is_pool', 'No');
            })
            ->whereHas('manage_fare', function($q2) use($location_id) {
                $q2->where('location_id', $location_id);
            })
            ->having('distance', '<=', Driver_Km)
            ->orderBy('distance', 'ASC')
            ->get();

            $nearest_car = collect($nearest_car)->groupBy('car_id')->values();

            if (isset($nearest_car) && !$nearest_car->isEmpty()) {
                LogDistanceMatrix("manual booking Search car");
                $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

                if ($get_fare_estimation['status'] == "success") {
                    $data = ManageFare::with([
                        'peak_fare' => function ($query)use($day,$current_time) {
                            $query->where(function($q) use($day) {
                                $q->where('day', $day)->orWhere('day', null);
                            })
                            ->where('start_time','<=',$current_time)
                            ->where('end_time','>=',$current_time);
                        }])
                    ->whereHas('peak_fare',function($q)use($day,$current_time){
                        $q->where(function($q) use($day) {
                            $q->where('day', $day)->orWhere('day', null);
                        })->where('start_time','<=',$current_time)->where('end_time','>=',$current_time);
                    })->where('location_id',$location_id)->groupBy('vehicle_id')->get();
                    $fare_details= [];
                    if($data){
                        foreach($data as $fare){
                            $fare_details[$fare->vehicle_id]= ['id' =>$fare->peak_fare[0]->id ,'car_id' => $fare->vehicle_id ,'price' => $fare->peak_fare[0]->price,'type' => $fare->peak_fare[0]->type];
                        }
                    }
                    $minutes = round(floor(round($get_fare_estimation['time'] / 60)));
                    if ($get_fare_estimation['distance'] == '') {
                        $get_fare_estimation['distance'] = 0;
                    }
                    $km = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));
                    $location = [];
                    $i = 0;
                    foreach ($nearest_car as $key => $list_car) {
                        $location = $list_car->map(function ($item) use ($km, $minutes) {
                            return ['latitude' => $item->latitude, 'longitude' => $item->longitude];
                        })->toArray();
                        if (count($location) > 0) {
                            LogDistanceMatrix("manual booking search car","first Nearest driver search");
                            $get_min_time = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $location[0]['latitude'], $request->pickup_longitude,$location[0]['longitude']);
                            $base_fare = round($list_car[$i]->manage_fare->base_fare + ($list_car[$i]->manage_fare->per_km * $km));
                            $fare_estimation = number_format(($base_fare + round($list_car[$i]->manage_fare->per_min * $minutes)), 2, '.', '');
                            if($fare_estimation < $list_car[$i]->manage_fare->min_fare){     
                                $fare_estimation = $list_car[$i]->manage_fare->min_fare;
                            }
                            $get_near_car_time = round(floor(round($get_min_time['time'] / 60)));
                            if ($get_min_time['status'] == "success") {
                                $get_near_car_time = round(floor(round($get_min_time['time'] / 60)));
                                if ($get_near_car_time == 0) {
                                    $get_near_car_time = 1;
                                }
                            } else {
                                return response()->json([
                                    'status_message' => $get_min_time['msg'],
                                    'status_code' => '0',
                                ]);
                            }
                        }
                            
                        $car_s[]  = ['car_id' => $list_car[$i]->car_id];

                        $peak_price = 0;
                        $apply_peak = "No";
                        $peak_id =0;
                        $peak_fare=0;

                        if(!empty($fare_details)){
                            if(array_key_exists($list_car[$i]->car_id,$fare_details)){ 
                                $peak_price = $fare_details[$list_car[$i]->car_id]['price'];
                                $peak_id = $fare_details[$list_car[$i]->car_id]['id'];
                                $apply_peak = "Yes";
                                $peak_fare = $fare_estimation * ($peak_price-1);
                                $fare_estimation = $fare_estimation * $peak_price;
                            }
                        }

                        $currency = Currency::defaultCurrency()->first()->original_symbol;

                        $car_array[$list_car[0]->car_id] = [ 
                            'id'=>$list_car[0]->car_id,
                            'car_id' => $list_car[$i]->car_id,
                            'car_name' => $list_car[$i]->car_type->car_name,
                            'driver_id' => $list_car[$i]->user_id,
                            'capacity' => $list_car[$i]->manage_fare->capacity,
                            'base_fare' => $list_car[$i]->manage_fare->base_fare,
                            'per_min' => $list_car[$i]->manage_fare->per_min,
                            'per_km' => $list_car[$i]->manage_fare->per_km,
                            'min_fare' => $list_car[$i]->manage_fare->min_fare,
                            'schedule_fare' => $list_car[$i]->manage_fare->schedule_fare,
                            'schedule_cancel_fare' => $list_car[$i]->manage_fare->schedule_cancel_fare,
                            'location' => $location,
                            'peak_fare' => (string) $peak_fare ,
                            'fare_estimation' => (string) $fare_estimation ,
                            'min_time' => (string) $get_near_car_time,
                            'minutes' => (string) $minutes,
                            'km' => (string) $km,
                            'apply_peak' =>  $apply_peak,
                            'peak_price' =>  $peak_price,
                            'location_id' => $location_id,
                            'peak_id' =>  $peak_id,
                            'car_image' =>  $list_car[$i]->car_type->vehicle_image,
                            'car_active_image' =>$list_car[$i]->car_type->active_image,
                            'currency' =>$currency,
                        ];
                    }
                }
                return response()->json([
                    'vehicle_type' =>$car_array,
                    'status_message' => trans('messages.cars_found'),
                    'status_code' => '1',
                ]);
            } else {
                return response()->json([
                    'status_message' => 'no_cars_found',
                    'trans_message' => trans('messages.no_cars_found'),
                    'status_code' => '0',
                ]);
            }
        }
    }

    //get drivers list
    public function driver_list(Request $request)
    {
        $data = [
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'drop_latitude' => $request->drop_latitude,
            'drop_longitude' => $request->drop_longitude,
            'car_id' => $request->car_id,
            'start_date' => date('Y-m-d', strtotime($request->date_time)),
            'start_time' => date('H:i', strtotime($request->date_time)),
            'schedule_id' => $request->schedule_id,
        ];

        $ignore_assigned = $this->request_helper->ignoreAssigned($data);
        $match_location = Location::select(DB::raw("id,status,(ST_WITHIN( ST_GeomFromText(
                'POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available "))->having('available','1')->where('status','Active')->first();
        if($match_location == null){
            return response()->json([
                'trans_message' => trans('messages.location_unavailable'),
                'status_message' => 'location_unavailable',
                'status_code' => '0',
            ]);
        } else {
            $location_cars = ManageFare::where('location_id',$match_location->id)->get()->toArray();
            $location_id = $match_location->id;

            if(!$request->filter)
                $request->filter = [];

            $handicap = $child_seat = $request_from = $gender = '';
            if($request->has('user_id') && $request->user_id !='') {
                // insert updated filter
                $filter_insert = FilterObject::optionsInsert('rider',$request->user_id,$request->filter);
                // get user info
                $user_details = User::find($request->user_id);
                $gender = $user_details->gender;
                // get filter
                $options = FilterObject::options('rider',$user_details->id);
            } else {
                $gender = $request->gender;
                $options = $request->filter;
            }

            if(in_array('4', $options)) {
                $request_from = '1';
            }
            if(in_array('2', $options)) {
                $handicap = '1';
            }
            if(in_array('3', $options)) {
                $child_seat = '1';
            }

            $list_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))
                ->having('distance', '<=', site_settings('driver_km'))
                ->where('car_id',$request->car_id)
                ->whereNotIn('user_id',$ignore_assigned)
                ->with(['car_type', 'users'])
                ->with(['manage_fare' => function ($query) use ($location_id) {
                    $query->where('location_id', $location_id);
                }])
                ->with(['users.driver_trips' => function ($query) {
                    $query->whereIn('status',['Scheduled','Begin trip','End trip','Rating']);
                }])
                ->whereHas('users', function($q2) use($handicap,$child_seat,$request_from,$gender) {
                    $q2->activeOnlyStrict()
                    ->where(function($query)  {
                        // For company user login, only get that company's driver
                        if(LOGIN_USER_TYPE=='company') {
                            $query->where('company_id',Auth::guard('company')->user()->id);
                        }
                    })
                    ->whereHas('vehicle', function($q2) use($handicap,$child_seat,$request_from,$gender) {
                        if($handicap) {
                            $q2->whereHas('handicap');
                        }
                        if($child_seat) {
                            $q2->whereHas('child_seat');
                        }
                        if($request_from) {
                            $q2->whereHas('female_driver');
                        }
                        if($gender=='1') {
                            $q2->whereDoesntHave('female');
                        }
                    });
                })
                ->whereHas('car_type', function ($q2) use($request,$location_id) {
                    $q2->where('status', 'Active');
                })->whereHas('manage_fare', function ($q2) use($location_id) {
                    $q2->where('location_id', $location_id);
                })->orderBy('distance', 'ASC')->get();

                $user=[];
                foreach ($list_car as $key => $car) {
                    $user[] = [
                        'id'=>$car->users->id,
                        'hidden_mobile_number'=>$car->users->hidden_mobile_number,
                        'mobile_number'=>$car->users->mobile_number,
                        'email'=>$car->users->email,
                        'first_name'=>$car->users->first_name,
                        'last_name'=>$car->users->last_name,
                        'company'=>$car->users->company->name,
                        'company_id'=>$car->users->company_id,
                        'src'=>$car->users->profile_picture->header_src,
                        'latitude'=>$car->latitude,
                        'longitude'=>$car->longitude,
                        'distance_km'=>round($car->distance, 1),
                        'vehicle_number'=>optional($car->users->vehicle)->vehicle_number,
                        'vehicle_type'=>optional($car->users->vehicle)->vehicle_type,
                        'driver_current_status'=>($car->status=='Trip' || $car->status=='Pool Trip') ? @$car->users->driver_trips->last()->status:$car->status,
                    ];
                }
        }
        return json_encode($user);
    }

    //get driver detail by id
    public function get_driver(Request $request)
    {
        $company = LOGIN_USER_TYPE == 'company' ? Auth::guard('company')->user() : null;
        $dispatchSettings = $this->dispatchConfig->resolve($company);
        $bookingMode = $this->dispatchConfig->normalizeBookingMode($request->booking_mode);
        $isDispatchOverride = (bool) $request->is_dispatch_override;
        $minimumLeadMinutes = $this->dispatchConfig->minimumLeadMinutes($bookingMode, $dispatchSettings, $isDispatchOverride);

        $book_time = $request->date_time;
        $current_time = date("Y-m-d H:i", strtotime("+".$request->utc_offset." minutes"));
        if ($minimumLeadMinutes > 0 && $book_time < date("Y-m-d H:i", strtotime($current_time . " +{$minimumLeadMinutes} minutes"))) {
            return response()->json([
                'status_message' => 'select_ahead_time',
                'trans_message' => "Please make sure that the booking time is {$minimumLeadMinutes} minutes ahead from current time, or enable dispatch override.",
                'status_code' => '0',
                'minimum_lead_minutes' => $minimumLeadMinutes,
            ]);
        }
        $driver = User::where('id',$request->driver_id)->select('id','first_name','last_name','mobile_number','email','gender')->first();
        return response()->json([
            'driver' => $driver,
            'status_code' => '1',
        ]);
    }

    public function zones()
    {
        $zones = Location::active()
            ->get(['id', 'name', 'coordinates'])
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'coordinates' => $location->co_ordinates[0] ?? [],
                ];
            });

        return response()->json($zones);
    }

    protected function recordAssignmentAttempt(ScheduleRide $schedule, ?int $driverId, string $dispatchMode, string $status, array $metadata = []): void
    {
        ScheduleAssignmentAttempt::create([
            'schedule_ride_id' => $schedule->id,
            'driver_id' => $driverId,
            'company_id' => $schedule->company_id,
            'dispatcher_id' => LOGIN_USER_TYPE === 'company' ? Auth::guard('company')->id() : Auth::id(),
            'dispatch_mode' => $dispatchMode,
            'status' => $status,
            'offered_at' => now(),
            'accepted_at' => in_array($status, ['assigned', 'accepted'], true) ? now() : null,
            'timeout_seconds' => $schedule->assignment_timeout_seconds ?: 30,
            'sequence_order' => ((int) $schedule->assignment_attempt_count) + 1,
            'metadata' => $metadata,
        ]);

        $schedule->assignment_attempt_count = ((int) $schedule->assignment_attempt_count) + 1;
        $schedule->last_assignment_attempt_at = now();
        if ($driverId) {
            $schedule->assigned_driver_id = $driverId;
        }
        $schedule->assignment_status = $status;
        $schedule->save();
    }

    protected function dispatchQueueCounts($company = null): array
    {
        $query = ScheduleRide::query()->where('booking_type', 'Manual Booking');

        if ($company) {
            $query->where(function ($builder) use ($company) {
                $builder->where('company_id', $company->id)
                    ->orWhereHas('driver', function ($driverQuery) use ($company) {
                        $driverQuery->where('company_id', $company->id);
                    });
            });
        }

        $rides = (clone $query)->get();

        return [
            'asap' => $rides->where('booking_mode', 'asap')->count(),
            'scheduled' => $rides->where('booking_mode', 'scheduled')->count(),
            'assigned' => $rides->whereIn('assignment_status', ['assigned', 'accepted'])->count(),
            'en_route' => $rides->where('assignment_status', 'en_route')->count(),
            'arrived' => $rides->where('assignment_status', 'arrived')->count(),
            'active' => $rides->whereIn('assignment_status', ['started', 'completed'])->count(),
            'completed' => $rides->where('status', 'Completed')->count(),
            'cancelled' => $rides->where('status', 'Cancelled')->count(),
            'no_show' => $rides->where('status', 'No Show')->count(),
            'dispatch_due' => $rides->filter(function ($ride) {
                if (!$ride->dispatch_at || $ride->status !== 'Pending') {
                    return false;
                }

                return Carbon::parse($ride->dispatch_at)->between(now()->subMinutes(5), now()->addMinutes(30));
            })->count(),
        ];
    }

    protected function resolveDispatchCountry($scheduleRide, $company, $countries)
    {
        $countries = collect($countries);
        $siteSettings = app()->bound('site_settings') ? resolve('site_settings') : SiteSettings::get();

        $candidateIds = array_filter([
            optional($siteSettings->where('name', 'admin_country_code')->first())->value,
            optional($company)->country_id,
            optional(optional($scheduleRide)->users)->country_id,
        ]);

        foreach ($candidateIds as $candidateId) {
            $country = $countries->firstWhere('id', (int) $candidateId);
            if ($country) {
                return $country;
            }
        }

        $candidatePhoneCode = optional(optional($scheduleRide)->users)->country_code;
        if ($candidatePhoneCode) {
            $country = $countries->firstWhere('phone_code', (string) $candidatePhoneCode);
            if ($country) {
                return $country;
            }
        }

        return $countries->firstWhere('id', 1) ?: $countries->first();
    }

    protected function draftQuery()
    {
        $query = ManualBookingDraft::query()->where('status', 'Active');
        $context = $this->dispatcherContext();

        if ($context['company_id']) {
            $query->where('company_id', $context['company_id']);
        }

        return $query->where('dispatcher_type', $context['dispatcher_type'])
            ->where('dispatcher_id', $context['dispatcher_id']);
    }

    protected function templateQuery()
    {
        $query = ManualBookingTemplate::query()->where('status', 'Active');
        $context = $this->dispatcherContext();

        return $query->where(function ($builder) use ($context) {
            $builder->where(function ($privateQuery) use ($context) {
                $privateQuery->where('sharing_scope', 'private')
                    ->where('dispatcher_type', $context['dispatcher_type'])
                    ->where('dispatcher_id', $context['dispatcher_id']);
            });

            if ($context['company_id']) {
                $builder->orWhere(function ($companyQuery) use ($context) {
                    $companyQuery->where('sharing_scope', 'company')
                        ->where('company_id', $context['company_id']);
                });
            }
        });
    }

    protected function dispatcherContext(): array
    {
        return [
            'company_id' => LOGIN_USER_TYPE === 'company' ? Auth::guard('company')->id() : null,
            'dispatcher_type' => LOGIN_USER_TYPE === 'company' ? 'company' : 'admin',
            'dispatcher_id' => LOGIN_USER_TYPE === 'company' ? Auth::guard('company')->id() : Auth::id(),
        ];
    }

    protected function sanitizeWorkspacePayload($payload): array
    {
        $payload = is_array($payload) ? $payload : [];

        return [
            'booking_experience_tab' => $payload['booking_experience_tab'] ?? 'asap',
            'booking_mode' => $payload['booking_mode'] ?? 'asap',
            'dispatch_mode' => $payload['dispatch_mode'] ?? 'auto',
            'pickup_location' => $payload['pickup_location'] ?? '',
            'pickup_latitude' => $payload['pickup_latitude'] ?? '',
            'pickup_longitude' => $payload['pickup_longitude'] ?? '',
            'drop_location' => $payload['drop_location'] ?? '',
            'drop_latitude' => $payload['drop_latitude'] ?? '',
            'drop_longitude' => $payload['drop_longitude'] ?? '',
            'via_location' => $payload['via_location'] ?? '',
            'via_latitude' => $payload['via_latitude'] ?? '',
            'via_longitude' => $payload['via_longitude'] ?? '',
            'date_time' => $payload['date_time'] ?? '',
            'vehicle_type' => $payload['vehicle_type'] ?? '',
            'priority' => $payload['priority'] ?? 'normal',
            'first_name' => $payload['first_name'] ?? '',
            'last_name' => $payload['last_name'] ?? '',
            'email' => $payload['email'] ?? '',
            'mobile_number' => $payload['mobile_number'] ?? '',
            'country_code' => $payload['country_code'] ?? '',
            'country_id' => $payload['country_id'] ?? '',
            'gender' => $payload['gender'] ?? '',
            'filter' => array_values($payload['filter'] ?? []),
            'capabilities' => array_values($payload['capabilities'] ?? []),
            'dispatcher_note' => $payload['dispatcher_note'] ?? '',
            'driver_note' => $payload['driver_note'] ?? '',
            'payment_method' => $payload['payment_method'] ?? 'Cash',
            'customer_type' => $payload['customer_type'] ?? '',
            'company_name' => $payload['company_name'] ?? '',
            'company_phone' => $payload['company_phone'] ?? '',
            'flight_number' => $payload['flight_number'] ?? '',
            'airline' => $payload['airline'] ?? '',
            'terminal' => $payload['terminal'] ?? '',
            'airport_direction' => $payload['airport_direction'] ?? '',
            'airport_name' => $payload['airport_name'] ?? '',
            'airport_meeting_point' => $payload['airport_meeting_point'] ?? '',
            'pickup_buffer_time' => $payload['pickup_buffer_time'] ?? '',
            'return_trip' => $payload['return_trip'] ?? [],
            'recurring' => $payload['recurring'] ?? [],
        ];
    }

    protected function extractBookingMeta(Request $request, User $rider, string $bookingMode, string $dispatchMode, Carbon $scheduledAt, Carbon $dispatchAt): array
    {
        $experienceTab = $this->normalizeBookingExperienceTab($request->input('booking_experience_tab', 'asap'));
        $capabilities = $this->extractCapabilities($request);

        return [
            'experience_tab' => $experienceTab,
            'booking_mode' => $bookingMode,
            'dispatch_mode' => $dispatchMode,
            'capabilities' => $capabilities,
            'customer_type' => $request->input('customer_type'),
            'company_name' => $request->input('company_name'),
            'company_phone' => $request->input('company_phone'),
            'payment_method' => $request->input('payment_method', 'Cash'),
            'customer_reference' => $request->input('customer_reference'),
            'dispatcher_note' => $request->input('dispatcher_note'),
            'driver_note' => $request->input('driver_note'),
            'advanced' => [
                'booking_type' => $request->input('advanced_booking_type'),
                'pickup_date' => $request->input('advanced_pickup_date'),
                'pickup_time' => $request->input('advanced_pickup_time'),
                'time_flexibility' => $request->input('advanced_time_flexibility'),
                'booking_reference' => $request->input('advanced_booking_reference'),
            ],
            'later' => [
                'pickup_date' => $request->input('later_pickup_date'),
                'pickup_time' => $request->input('later_pickup_time'),
                'repeat' => $request->input('later_repeat'),
                'prebooking_window' => $request->input('later_prebooking_window'),
            ],
            'airport' => [
                'name' => $request->input('airport_name'),
                'direction' => $request->input('airport_direction'),
                'airline' => $request->input('airline'),
                'flight_number' => $request->input('flight_number'),
                'flight_date' => $request->input('airport_flight_date'),
                'flight_time' => $request->input('airport_flight_time'),
                'terminal' => $request->input('terminal'),
                'pickup_terminal' => $request->input('airport_pickup_terminal'),
                'meeting_point' => $request->input('airport_meeting_point'),
                'pickup_buffer_time' => $request->input('pickup_buffer_time'),
                'pickup_sign' => $request->input('pickup_sign'),
            ],
            'return_trip' => [
                'enabled' => $request->boolean('return_trip_enabled'),
                'date' => $request->input('return_date'),
                'time' => $request->input('return_time'),
                'pickup_location' => $request->input('return_pickup_location'),
                'drop_location' => $request->input('return_drop_location'),
            ],
            'recurring' => [
                'enabled' => $request->boolean('recurring_enabled'),
                'type' => $request->input('recurring_type'),
                'start_date' => $request->input('recurring_start_date'),
                'start_time' => $request->input('recurring_start_time'),
                'end_option' => $request->input('recurring_end_option'),
                'pattern' => $request->input('recurring_pattern'),
                'occurrences' => (int) $request->input('recurring_occurrences', 1),
                'days' => array_values($request->input('recurring_days', [])),
                'until' => $request->input('recurring_until'),
            ],
            'passenger' => [
                'id' => $rider->id,
                'name' => trim($rider->first_name.' '.$rider->last_name),
                'email' => $rider->email,
                'phone' => $rider->mobile_number,
            ],
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'dispatch_at' => $dispatchAt->toDateTimeString(),
        ];
    }

    protected function encodeStopPoints(Request $request): ?string
    {
        $points = [];

        if ($request->filled('via_location')) {
            $points[] = [
                'label' => $request->input('via_location'),
                'latitude' => $request->input('via_latitude'),
                'longitude' => $request->input('via_longitude'),
            ];
        }

        return empty($points) ? null : json_encode($points);
    }

    protected function scheduleRidePayload(?ScheduleRide $scheduleRide): array
    {
        if (! $scheduleRide) {
            return [];
        }

        $meta = (array) ($scheduleRide->booking_meta ?? []);
        $stopPoints = json_decode((string) $scheduleRide->stop_points, true) ?: [];

        return [
            'id' => $scheduleRide->id,
            'booking_experience_tab' => $scheduleRide->booking_channel ?: ($meta['experience_tab'] ?? 'asap'),
            'booking_mode' => $scheduleRide->booking_mode ?: 'asap',
            'dispatch_mode' => $scheduleRide->dispatch_mode ?: 'auto',
            'pickup_location' => $scheduleRide->pickup_location,
            'pickup_latitude' => $scheduleRide->pickup_latitude,
            'pickup_longitude' => $scheduleRide->pickup_longitude,
            'drop_location' => $scheduleRide->drop_location,
            'drop_latitude' => $scheduleRide->drop_latitude,
            'drop_longitude' => $scheduleRide->drop_longitude,
            'via_location' => $scheduleRide->via_location ?: data_get(collect($stopPoints)->first(), 'label', ''),
            'via_latitude' => $scheduleRide->via_latitude,
            'via_longitude' => $scheduleRide->via_longitude,
            'date_time' => trim($scheduleRide->schedule_date.' '.$scheduleRide->schedule_time),
            'vehicle_type' => $scheduleRide->car_id,
            'priority' => $scheduleRide->priority,
            'dispatcher_note' => $scheduleRide->dispatcher_note,
            'driver_note' => $scheduleRide->driver_note,
            'payment_method' => $meta['payment_method'] ?? 'Cash',
            'capabilities' => $scheduleRide->booking_capabilities ?: ($meta['capabilities'] ?? []),
            'customer_type' => $meta['customer_type'] ?? $scheduleRide->booking_for_type,
            'company_name' => $meta['company_name'] ?? $scheduleRide->booking_contact_name,
            'company_phone' => $meta['company_phone'] ?? $scheduleRide->booking_contact_phone,
            'flight_number' => $meta['airport']['flight_number'] ?? '',
            'airline' => $meta['airport']['airline'] ?? '',
            'terminal' => $meta['airport']['terminal'] ?? '',
            'airport_direction' => $meta['airport']['direction'] ?? '',
            'airport_name' => $meta['airport']['name'] ?? '',
            'airport_meeting_point' => $meta['airport']['meeting_point'] ?? '',
            'pickup_buffer_time' => $meta['airport']['pickup_buffer_time'] ?? '',
            'pickup_sign' => $meta['airport']['pickup_sign'] ?? '',
            'return_trip' => $meta['return_trip'] ?? [],
            'recurring' => $meta['recurring'] ?? [],
        ];
    }

    protected function normalizeBookingExperienceTab(?string $value): string
    {
        $value = strtolower((string) $value);
        $allowed = ['asap', 'later', 'advanced', 'recurring', 'airport_transfer'];

        return in_array($value, $allowed, true) ? $value : 'asap';
    }

    protected function extractCapabilities(Request $request): array
    {
        $capabilities = $request->input('capabilities', []);

        if (is_string($capabilities)) {
            $decoded = json_decode($capabilities, true);
            $capabilities = is_array($decoded) ? $decoded : explode(',', $capabilities);
        }

        return collect($capabilities)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function storeReturnTripIfRequested(Request $request, ScheduleRide $parent, $fareDetails, array $dispatchSettings): ?ScheduleRide
    {
        if (! $request->boolean('return_trip_enabled') || ! $request->filled('return_date') || ! $request->filled('return_time')) {
            return null;
        }

        $returnAt = Carbon::parse($request->input('return_date').' '.$request->input('return_time'), $parent->timezone ?: config('app.timezone'));
        $dispatchAt = $returnAt->copy()->subMinutes($this->dispatchConfig->scheduledDispatchLeadMinutes($dispatchSettings));

        $schedule = $parent->replicate();
        $schedule->schedule_date = $returnAt->format('Y-m-d');
        $schedule->schedule_time = $returnAt->format('H:i');
        $schedule->schedule_end_date = $returnAt->format('Y-m-d');
        $schedule->schedule_end_time = $returnAt->format('H:i');
        $schedule->pickup_location = $request->input('return_pickup_location') ?: $parent->drop_location;
        $schedule->drop_location = $request->input('return_drop_location') ?: $parent->pickup_location;
        $schedule->pickup_latitude = $request->input('return_pickup_latitude') ?: $parent->drop_latitude;
        $schedule->pickup_longitude = $request->input('return_pickup_longitude') ?: $parent->drop_longitude;
        $schedule->drop_latitude = $request->input('return_drop_latitude') ?: $parent->pickup_latitude;
        $schedule->drop_longitude = $request->input('return_drop_longitude') ?: $parent->pickup_longitude;
        $schedule->scheduled_at = $returnAt;
        $schedule->dispatch_at = $dispatchAt->lt(now()) ? now() : $dispatchAt;
        $schedule->return_parent_id = $parent->id;
        $schedule->workspace_label = trim(($parent->workspace_label ?: 'Booking').' Return');
        $meta = (array) ($schedule->booking_meta ?? []);
        $meta['is_return_trip_child'] = true;
        $schedule->booking_meta = $meta;
        $schedule->save();

        return $schedule;
    }

    protected function storeRecurringSchedulesIfRequested(Request $request, ScheduleRide $parent, $fareDetails, array $dispatchSettings): array
    {
        if (! $request->boolean('recurring_enabled')) {
            return [];
        }

        $occurrences = max(1, min((int) $request->input('recurring_occurrences', 1), 12));
        $pattern = $request->input('recurring_pattern', 'weekly');
        $days = collect($request->input('recurring_days', []))->map(fn ($day) => (int) $day)->filter()->values()->all();
        $created = [];
        $cursor = Carbon::parse($parent->schedule_date.' '.$parent->schedule_time, $parent->timezone ?: config('app.timezone'));

        for ($i = 1; $i < $occurrences; $i++) {
            $next = $pattern === 'daily'
                ? $cursor->copy()->addDays($i)
                : $this->nextRecurringDate($cursor->copy(), $days, $i);

            if (! $next) {
                continue;
            }

            $dispatchAt = $next->copy()->subMinutes($this->dispatchConfig->scheduledDispatchLeadMinutes($dispatchSettings));
            $schedule = $parent->replicate();
            $schedule->schedule_date = $next->format('Y-m-d');
            $schedule->schedule_time = $next->format('H:i');
            $schedule->schedule_end_date = $next->format('Y-m-d');
            $schedule->schedule_end_time = $next->format('H:i');
            $schedule->scheduled_at = $next;
            $schedule->dispatch_at = $dispatchAt->lt(now()) ? now() : $dispatchAt;
            $schedule->return_parent_id = $parent->id;
            $schedule->workspace_label = trim(($parent->workspace_label ?: 'Booking').' #'.($i + 1));
            $meta = (array) ($schedule->booking_meta ?? []);
            $meta['is_recurring_child'] = true;
            $meta['recurring_index'] = $i + 1;
            $schedule->booking_meta = $meta;
            $schedule->save();
            $created[] = $schedule;
        }

        return $created;
    }

    protected function nextRecurringDate(Carbon $base, array $days, int $weekOffset): ?Carbon
    {
        if (empty($days)) {
            return $base->addWeeks($weekOffset);
        }

        $candidate = $base->copy()->addWeeks($weekOffset)->startOfWeek();
        foreach ($days as $day) {
            $option = $candidate->copy()->addDays(max(0, $day - 1))->setTimeFrom($base);
            if ($option->gt($base)) {
                return $option;
            }
        }

        return $candidate->copy()->addWeek()->addDays(max(0, ((int) $days[0]) - 1))->setTimeFrom($base);
    }
}
