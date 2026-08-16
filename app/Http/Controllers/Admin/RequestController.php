<?php

/**
 * Request Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Request
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Request as RideRequest;
use App\Models\Currency;
use DB;

class RequestController extends Controller
{
    /**
     * Load Datatable for Request
     *
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $requests = DB::table('request')
            ->join('users', 'users.id', '=', 'request.user_id')
            ->join('car_type', 'car_type.id', '=', 'request.car_id')
            ->leftJoin('trips', 'trips.request_id', '=', 'request.id')
            ->leftJoin('currency', 'currency.code', '=', 'trips.currency_code')
            ->select([
                'request.id',
                'request.group_id',
                'request.payment_mode',
                'request.updated_at',
                'users.first_name',
                'users.last_name',
                'trips.payment_status',
                'trips.total_fare',
                'currency.symbol as currency_symbol',
            ])
            ->groupBy('request.group_id', 'request.id', 'request.payment_mode', 'request.updated_at', 'users.first_name', 'users.last_name', 'trips.payment_status', 'trips.total_fare', 'currency.symbol');

        if (LOGIN_USER_TYPE == 'company') {
            $requests->join('users as drivers', function ($join) {
                $join->on('drivers.id', '=', 'request.driver_id')
                    ->where('drivers.company_id', auth('company')->id());
            });
        }

        if ($search !== '') {
            $requests->where(function ($query) use ($search) {
                $query->where('request.id', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('request.payment_mode', 'like', "%{$search}%");
            });
        }

        $requests = $requests->orderByDesc('request.updated_at')->paginate(20)->withQueryString();

        $requests->getCollection()->transform(function ($requestRow) {
            $acceptedRequest = DB::table('request')->where('group_id', $requestRow->group_id)->where('status', 'Accepted')->first();
            $pendingRequest = DB::table('request')->where('group_id', $requestRow->group_id)->where('status', 'Pending')->exists();

            if ($acceptedRequest) {
                $tripStatus = optional(DB::table('trips')->where('request_id', $acceptedRequest->id)->first())->status;
                $requestRow->request_status = $tripStatus ?: 'Accepted';
            } elseif ($pendingRequest) {
                $requestRow->request_status = 'Searching';
            } else {
                $requestRow->request_status = 'No one accepted';
            }

            $requestRow->payment_status_label = $requestRow->payment_status ?: 'Not Paid';
            $requestRow->total_amount_label = $requestRow->total_fare !== null ? html_entity_decode($requestRow->currency_symbol).' '.$requestRow->total_fare : 'N/A';
            $requestRow->date_time_label = $requestRow->updated_at ? \Carbon\Carbon::parse($requestRow->updated_at)->diffForHumans() : 'just now';

            return $requestRow;
        });

        return view('admin.request.view', [
            'requests' => $requests,
            'search' => $search,
        ]);
    }

    public function detail_request(Request $request)
    {
        $request_id = $request->id;

        $data['request_details'] = RideRequest::with('users','driver','trips')
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') {
                $query->whereHas('driver',function($q1){
                    $q1->where('company_id',auth('company')->user()->id);
                });
            }
        })
        ->where('id',$request_id)
        ->first();

        if($data['request_details']) {
            $request_status = RideRequest::where('group_id',$data['request_details']->group_id)->where('status','Accepted');
            if($dt = $request_status->first()) {
              $data['driver_name']  = $dt->driver->first_name.' '.$dt->driver->last_name;
              $data['company_name'] = $dt->driver->company->name ?? '';
            }

            $data['trip_path'] = $data['request_details']->trip_path;

            $pending_request_status = DB::table('request')->where('group_id',$data['request_details']->group_id)->where('status','Pending');
            $data['invoice_data'] = [];
            if($request_status->count()) {
                $ride_request = $request_status->first();
                $req_id  = $ride_request->id;
                $trips_controller = resolve(\App\Http\Controllers\Admin\TripsController::class);
                $data['invoice_data'] = $trips_controller->getAdminInvoice($ride_request->accepted_trips);
                $trip_status = @DB::table('trips')->where('request_id',$req_id)->get()->first()->status;

                $data['trip_data'] = $ride_request->accepted_trips;
                $data['trip_path'] = $ride_request->accepted_trips->trip_path;
                $data['is_tripped'] = true;
                $data['request_status'] = $trip_status;
            }
            elseif($pending_request_status->count()) {
                $data['request_status'] = "Searching";
            }
            else {
                $data['request_status'] = "No one accepted";
            }
            //For company user login, get session currency
            if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {
                $data['default_currency'] = Currency::whereCode(session()->get('currency'))->first();
            }

            return view('admin.request.details', $data);
        }

        flashMessage('danger', 'Invalid ID');
        return redirect(LOGIN_USER_TYPE.'/request');
    }
}
