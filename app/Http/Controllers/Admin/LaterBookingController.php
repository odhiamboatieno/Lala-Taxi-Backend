<?php

/**
 * Later Booking Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Later Booking
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\LaterBookingDataTable;
use App\Models\ScheduleRide;
use App\Models\ScheduleCancel;
use App\Models\PeakFareDetail;
use App\Models\User;
use App\Models\CancelReason;
use App\Models\Request as RideRequest;
use App\Models\ScheduleAssignmentAttempt;
use App\Http\Helper\RequestHelper;
use App\Services\Dispatch\ManualDispatchConfigService;
use Carbon\Carbon;

class LaterBookingController extends Controller
{
    protected $request_helper;
    protected $dispatchConfig;

    public function __construct(RequestHelper $request, ManualDispatchConfigService $dispatchConfig)
    {
        $this->request_helper = $request;
        $this->dispatchConfig = $dispatchConfig;
    }

    /**
     * Load Datatable for LaterBooking
     *
     * @return view file
     */
    public function index(LaterBookingDataTable $dataTable)
    {
        $login_user = (LOGIN_USER_TYPE == 'admin') ? 'Admin' : 'Company';

        $data['cancel_reasons'] = CancelReason::where('cancelled_by',$login_user)->where('status','Active')->get();
        $data['queue'] = request('queue', 'all');
        $data['queue_counts'] = $this->queueCounts();
        return $dataTable->render('admin.later_booking',$data);
    }

    public function cancel(Request $request)
    {
        $schedule = ScheduleRide::find($request->id);
        $schedule->status='Cancelled';
        $schedule->save();

        $cancel = new ScheduleCancel;
        $cancel->schedule_ride_id = $request->id;
        $cancel->cancel_reason = strip_tags($request->reason);
        $cancel->cancel_reason_id = $request->reason_id;
        $cancel->cancel_by = (LOGIN_USER_TYPE == 'admin')?'Admin':'Company';
        $cancel->save();

        if ($schedule->driver_id != 0) {
            $driver_details = @User::where('id', $schedule->driver_id)->first();
            $rider = User::find($schedule->user_id);

            $push_data['push_title'] = __('messages.api.schedule_cancelled');
            $push_data['data'] = [
                'manual_booking_trip_canceled_info' => [
                    'date'      => $schedule->schedule_date,
                    'time'      => $schedule->schedule_time,
                    'pickup_location'    => $schedule->pickup_location,
                    'pickup_latitude'    => $schedule->pickup_latitude,
                    'pickup_longitude'   => $schedule->pickup_longitude,
                    'rider_first_name'   => $rider->first_name,
                    'rider_last_name'    => $rider->last_name,
                    'rider_mobile_number'=> $rider->mobile_number,
                    'rider_country_code' => $rider->country_code
                ]
            ];

            $text = trans('messages.trip_booked_driver_message',['date'=>$schedule->schedule_date.' ' .$schedule->schedule_time,'pickup_location'=>$schedule->pickup_location,'drop_location'=>$schedule->drop_location]);

            $this->request_helper->checkAndSendMessage($driver_details,$text,$push_data);

        }

        return [
            'status_code'   => 1,
            'cancel_reason' => CancelReason::where('id',$request->reason_id)->first()->reason,
            'reason' => $cancel->cancel_reason,
        ];
    }

    public function immediate_request(Request $request)
    {
        $schedule = ScheduleRide::where('status','Car Not Found')->where('id',$request->id)->first();
        if ($schedule == null) {
            return response()->json([
                'status_code' => 0
            ]);
        }

        date_default_timezone_set($schedule->timezone);
        $current_date = date('Y-m-d');              
        $current_time = date('H:i');
        $schedule->save();

        $additional_fare = "";
        $peak_price = 0;
        if(isset($schedule->peak_id)!='') {
            $fare = PeakFareDetail::find($schedule->peak_id);
            if($fare) {
                $peak_price = $fare->price; 
                $additional_fare = "Peak";
            }
        }
        $data = [ 
            'rider_id'          => $schedule->user_id,
            'pickup_latitude'   => $schedule->pickup_latitude,
            'pickup_longitude'  => $schedule->pickup_longitude,
            'drop_latitude'     => $schedule->drop_latitude,
            'drop_longitude'    => $schedule->drop_longitude,
            'user_type'         => 'rider',
            'car_id'            => $schedule->car_id,
            'driver_group_id'   => null,
            'pickup_location'   => $schedule->pickup_location,
            'drop_location'     => $schedule->drop_location,
            'payment_method'    => $schedule->payment_method,
            'is_wallet'         => $schedule->is_wallet,
            'timezone'          => $schedule->timezone,
            'schedule_id'       => $schedule->id,
            'additional_fare'   => $additional_fare,
            'location_id'       => $schedule->location_id,
            'peak_price'        => $peak_price,
            'booking_type'      => $schedule->booking_type, 
            'driver_id'         => 0, 
        ];
        $car_details = $this->request_helper->find_driver($data);

        ScheduleAssignmentAttempt::create([
            'schedule_ride_id' => $schedule->id,
            'company_id' => $schedule->company_id,
            'dispatcher_id' => LOGIN_USER_TYPE === 'company' ? auth('company')->id() : auth()->id(),
            'dispatch_mode' => $schedule->dispatch_mode ?: 'auto',
            'status' => 'offered',
            'offered_at' => now(),
            'timeout_seconds' => $schedule->assignment_timeout_seconds ?: 30,
            'sequence_order' => ((int) $schedule->assignment_attempt_count) + 1,
            'metadata' => ['source' => 'immediate_request'],
        ]);

        $schedule->assignment_attempt_count = ((int) $schedule->assignment_attempt_count) + 1;
        $schedule->last_assignment_attempt_at = now();
        $schedule->assignment_status = 'offered';
        $schedule->save();

        $schedule = ScheduleRide::find($request->id);

        $accepted_request = RideRequest::where('schedule_id',$schedule->id)->where('status','Accepted')->first();

        $status = ($accepted_request != '') ? 'Scheduled' : $schedule->status;

        return response()->json([
            'status_code' => 1,
            'status_message' => $status
        ]);
    }

    protected function queueCounts(): array
    {
        $baseQuery = ScheduleRide::query()->where('booking_type', 'Manual Booking');
        if (LOGIN_USER_TYPE === 'company') {
            $companyId = auth('company')->id();
            $baseQuery->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhereHas('driver', function ($driverQuery) use ($companyId) {
                        $driverQuery->where('company_id', $companyId);
                    });
            });
        }

        $rides = (clone $baseQuery)->get();

        return [
            'all' => $rides->count(),
            'asap' => $rides->where('booking_mode', 'asap')->count(),
            'scheduled' => $rides->where('booking_mode', 'scheduled')->count(),
            'assigned' => $rides->whereIn('assignment_status', ['assigned', 'accepted'])->count(),
            'dispatch_due' => $rides->filter(function ($ride) {
                if (!$ride->dispatch_at || $ride->status !== 'Pending') {
                    return false;
                }

                return Carbon::parse($ride->dispatch_at)->between(now()->subMinutes(5), now()->addMinutes(30));
            })->count(),
            'completed' => $rides->where('status', 'Completed')->count(),
            'cancelled' => $rides->where('status', 'Cancelled')->count(),
            'no_show' => $rides->where('status', 'No Show')->count(),
        ];
    }
}
