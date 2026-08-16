<?php

/**
 * Heat Map Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    HeatMap
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use Carbon\Carbon;
use JWTAuth;

class MapController extends Controller
{
    /**
     * Send data for heat map
     *
     * @return Json
     */
    public function heat_map(Request $request)
    {
        if (!$request->timezone) {
            return response()->json([
                'status_code' => '0' , 
                'status_message' => 'timezone is required',
            ]); 
        }
        // $timezone = isValidTimezone($request->timezone) ? $request->timezone : 'UTC';
        $timezone = $request->timezone;

        $user_details = JWTAuth::parseToken()->authenticate();
        
        $heat_map_hours = max(1, (int) site_settings('heat_map_hours'));

        $current_date = Carbon::now($timezone)->format('Y-m-d');
        $current_time = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $prev_time = Carbon::now('UTC')->subHours($heat_map_hours)->format('Y-m-d H:i:s');

        $ride_requests = RideRequest::whereBetween('created_at', [$prev_time, $current_time])
            ->whereNotNull('pickup_latitude')
            ->whereNotNull('pickup_longitude')
            ->orderByDesc('id')
            ->get(['id', 'pickup_latitude', 'pickup_longitude', 'group_id'])
            ->unique('group_id');

        $heat_map_data = $ride_requests->map(function($request) {
            return [
                'id' => $request->id,
                'lat' => $request->pickup_latitude,
                'lng' => $request->pickup_longitude,
            ];
        })->values();

        $today_trips = Trips::select('total_fare')->where('driver_id',$user_details->id)->whereIn('status',['Payment','Rating','Completed'])->whereDate('end_trip',$current_date);
        $today_earnings = round($today_trips->sum('total_fare'));
        $today_booking  = $today_trips->count();

        return response()->json([
            'status_code'       => '1' , 
            'status_message'    => 'Success',
            'today_trip'        => $today_booking,
            'today_amount'      => $today_earnings,
            'heat_map_data'     => $heat_map_data,
        ]);        
    }
}