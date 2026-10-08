<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverOnlineSession;
use App\Models\Request as RideRequest;
use App\Models\Rating;
use App\Models\Trips;
use App\Models\TripRequest;
use App\Models\User;
use App\Services\Dispatch\DriverLocationService;
use App\Services\Dispatch\TripDispatchService;
use App\Services\DriverComplianceService;
use App\Services\DriverOnlineSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class RealtimeDriverController extends Controller
{
    public function __construct(
        protected DriverLocationService $driverLocationService,
        protected TripDispatchService $dispatchService,
        protected DriverOnlineSessionService $onlineSessionService
    ) {
        $this->middleware(function (Request $request, $next) {
            abort_unless($request->attributes->get('verified_jwt_user')?->user_type === 'Driver', 403);
            return $next($request);
        });
    }

    public function online(Request $request)
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $compliance = resolve(DriverComplianceService::class)->compliancePayload($driver);
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
            ], 422);
        }

        $driver = $this->driverLocationService->setOnlineState($driver, true, true, [
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'device_id' => $request->input('device_id'),
            'app_version' => $request->input('app_version'),
        ]);
        $sessionStatus = $this->onlineSessionService->buildStatus($driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver is now online.',
            'driver' => $this->serializeDriver($driver),
            'session' => $sessionStatus,
        ]);
    }

    public function offline(Request $request)
    {
        $driver = JWTAuth::parseToken()->authenticate();

        if ($driver->current_trip_id) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Driver cannot go offline during an active trip.',
            ], 422);
        }

        $driver = $this->driverLocationService->setOnlineState($driver, false, false, [
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'device_id' => $request->input('device_id'),
            'app_version' => $request->input('app_version'),
        ]);
        $sessionStatus = $this->onlineSessionService->buildStatus($driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver is now offline.',
            'driver' => $this->serializeDriver($driver),
            'session' => $sessionStatus,
        ]);
    }

    public function updateLocation(Request $request)
    {
        $driver = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'heading' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'trip_id' => 'nullable|exists:trips,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        if ($request->filled('trip_id')) {
            abort_unless(Trips::whereKey($request->trip_id)->where('driver_id', $driver->id)
                ->whereIn('status', \App\Support\TripWorkflow::activeStatuses())->exists(), 403);
        }
        $driver = $this->driverLocationService->update($driver, $validator->validated());

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver location updated successfully.',
            'driver' => $this->serializeDriver($driver),
        ]);
    }

    public function currentRequest()
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $rideRequest = RideRequest::query()
            ->with('users', 'parcelRequest')
            ->where('driver_id', $driver->id)
            ->where('status', 'Pending')
            ->latest('id')
            ->first();

        if (! $rideRequest) {
            Log::info('Dispatch: driver polled current request but none was pending', [
                'driver_id' => $driver->id,
            ]);

            return response()->json([
                'status_code' => '0',
                'status_message' => 'No pending request found.',
            ]);
        }

        Log::info('Dispatch: driver polled current request successfully', [
            'driver_id' => $driver->id,
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
        ]);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Pending request loaded successfully.',
            'trip_request' => $this->serializeRideRequest($rideRequest),
        ]);
    }

    public function home()
    {
        $driver = JWTAuth::parseToken()->authenticate()->fresh(['currency']);
        $sessionStatus = $this->onlineSessionService->buildStatus($driver);

        $timezone = config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $dayStart = $now->copy()->startOfDay()->timezone(config('app.timezone'));
        $dayEnd = $now->copy()->endOfDay()->timezone(config('app.timezone'));

        $completedTripStatuses = ['Rating', 'Payment', 'Completed'];

        $tripsToday = Trips::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', $completedTripStatuses)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->count();
        $totalTripsToday = Trips::query()
            ->where('driver_id', $driver->id)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->count();

        $earningsToday = (float) Trips::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', $completedTripStatuses)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->sum('total_fare');

        $ratingCount = Rating::query()
            ->where('driver_id', $driver->id)
            ->where('rider_rating', '>', 0)
            ->count();

        $driverRating = (float) getDriverRating($driver->id);
        $currencySymbol = html_entity_decode(optional($driver->currency)->original_symbol ?: optional($driver->currency)->symbol ?: '');

        $onlineStartedAt = $sessionStatus['current_session_started_at'];
        $onlineDurationSeconds = (int) ($sessionStatus['current_session_seconds'] ?? 0);

        [$peakHoursTitle, $peakHoursSubtitle, $peakHoursActive] = $this->resolvePeakHours($now);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver home loaded successfully.',
            'driver' => array_merge($this->serializeDriver($driver), [
                'first_name' => (string) $driver->first_name,
                'last_name' => (string) $driver->last_name,
                'display_name' => trim($driver->first_name . ' ' . $driver->last_name),
                'profile_image' => optional($driver->profile_picture)->src ?? '',
                'driver_rating' => number_format($driverRating, 2, '.', ''),
                'rating_count' => $ratingCount,
                'currency_symbol' => $currencySymbol,
            ]),
            'stats' => [
                'earnings_today' => $earningsToday,
                'earnings_today_display' => $currencySymbol . number_format($earningsToday, 0),
                'trips_today' => $tripsToday,
                'total_trips_today' => $totalTripsToday,
                'completed_trips_today' => $tripsToday,
                'online_duration_seconds' => $onlineDurationSeconds,
                'online_started_at' => $onlineStartedAt,
                'current_session_started_at' => $sessionStatus['current_session_started_at'],
                'current_session_seconds' => $sessionStatus['current_session_seconds'],
                'today_online_seconds' => $sessionStatus['today_online_seconds'],
                'week_online_seconds' => $sessionStatus['week_online_seconds'],
                'month_online_seconds' => $sessionStatus['month_online_seconds'],
                'fare_mode' => site_settings('fare_mode') ?: 'Taximeter',
            ],
            'peak_hours' => [
                'active' => $peakHoursActive,
                'title' => $peakHoursTitle,
                'subtitle' => $peakHoursSubtitle,
            ],
            'session' => $sessionStatus,
        ]);
    }

    public function sessionStatus()
    {
        $driver = JWTAuth::parseToken()->authenticate()->fresh();
        $status = $this->onlineSessionService->buildStatus($driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver session status loaded successfully.',
            'session' => $status,
        ]);
    }

    public function sessionHistory()
    {
        $driver = JWTAuth::parseToken()->authenticate()->fresh();
        $status = $this->onlineSessionService->buildStatus($driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver session history loaded successfully.',
            'today_online_seconds' => $status['today_online_seconds'],
            'week_online_seconds' => $status['week_online_seconds'],
            'month_online_seconds' => $status['month_online_seconds'],
            'recent_sessions' => $status['recent_sessions'],
        ]);
    }

    public function accept(int $tripRequest)
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $trip = $this->dispatchService->acceptRideRequest($tripRequest, $driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip request accepted successfully.',
            'trip' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'driver_id' => $trip->driver_id,
                'rider_id' => $trip->user_id,
            ],
        ]);
    }

    public function reject(int $tripRequest)
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $tripRequest = $this->dispatchService->rejectRideRequest($tripRequest, $driver);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip request rejected successfully.',
            'trip_request' => [
                'id' => $tripRequest->id,
                'trip_id' => null,
                'status' => $tripRequest->status,
            ],
        ]);
    }

    protected function serializeDriver($driver): array
    {
        return [
            'id' => $driver->id,
            'is_online' => (bool) $driver->is_online,
            'is_available' => (bool) $driver->is_available,
            'current_trip_id' => $driver->current_trip_id,
            'last_seen_at' => optional($driver->last_seen_at)->toIso8601String(),
            'online_started_at' => optional($driver->online_started_at)->toIso8601String(),
            'current_lat' => $driver->current_lat ? (float) $driver->current_lat : null,
            'current_lng' => $driver->current_lng ? (float) $driver->current_lng : null,
            'heading' => $driver->heading !== null ? (float) $driver->heading : null,
            'speed' => $driver->speed !== null ? (float) $driver->speed : null,
        ];
    }

    protected function serializeRideRequest(RideRequest $rideRequest): array
    {
        $currencySymbol = html_entity_decode(optional($rideRequest->users?->currency)->original_symbol ?: optional($rideRequest->users?->currency)->symbol ?: '');
        $parcelInfo = $this->serializeParcelRequestInfo($rideRequest);

        return [
            'id' => $rideRequest->id,
            'billing' => $rideRequest->billing,
            'payment_mode' => $rideRequest->payment_mode,
            'request_id' => $rideRequest->id,
            'trip_request_uuid' => 'ride_request_'.$rideRequest->id,
            'notification_type' => 'trip_request',
            'trip_id' => null,
            'status' => strtolower((string) $rideRequest->status),
            'service_category' => $rideRequest->service_category ?? 'ride',
            'parcel_info' => $parcelInfo,
            'expires_at' => $rideRequest->created_at
                ? $rideRequest->created_at->copy()->addSeconds((int) (site_settings('driver_request_seconds') ?: 30))->toIso8601String()
                : now()->addSeconds((int) (site_settings('driver_request_seconds') ?: 30))->toIso8601String(),
            'pickup' => [
                'lat' => (float) $rideRequest->pickup_latitude,
                'lng' => (float) $rideRequest->pickup_longitude,
                'address' => $rideRequest->pickup_location,
            ],
            'dropoff' => [
                'lat' => (float) $rideRequest->drop_latitude,
                'lng' => (float) $rideRequest->drop_longitude,
                'address' => $rideRequest->drop_location,
            ],
            'pickup_location' => $rideRequest->pickup_location,
            'drop_location' => $rideRequest->drop_location,
            'pickup_latitude' => (float) $rideRequest->pickup_latitude,
            'pickup_longitude' => (float) $rideRequest->pickup_longitude,
            'drop_latitude' => (float) $rideRequest->drop_latitude,
            'drop_longitude' => (float) $rideRequest->drop_longitude,
            'estimated_distance' => $rideRequest->distance_estimate ?? null,
            'estimated_duration' => $rideRequest->estimated_duration ?? null,
            'fare_estimate' => $rideRequest->estimated_fare ?? null,
            'estimated_fare' => $rideRequest->estimated_fare ?? null,
            'fare_mode' => site_settings('fare_mode') ?: 'Taximeter',
            'currency_symbol' => $currencySymbol,
            'seats' => $rideRequest->seats,
            'booking_contact_name' => $rideRequest->booking_contact_name ?? ($parcelInfo['recipient_name'] ?? ''),
            'booking_contact_phone' => $rideRequest->booking_contact_phone ?? ($parcelInfo['recipient_phone'] ?? ''),
            'delivery_label' => $parcelInfo['category_title'] ?? '',
            'package_title' => $parcelInfo['category_title'] ?? '',
            'package_type' => $parcelInfo['category_key'] ?? '',
            'package_description' => $parcelInfo['parcel_description'] ?? '',
            'special_instructions' => $parcelInfo['special_instructions'] ?? '',
            'estimated_weight' => $parcelInfo['estimated_weight'] ?? '',
            'rider_basic_info' => [
                'id' => $rideRequest->user_id,
                'name' => trim(($rideRequest->users?->first_name ?? '').' '.($rideRequest->users?->last_name ?? '')),
                'phone' => $rideRequest->users?->mobile_number ?? '',
            ],
        ];
    }

    protected function serializeParcelRequestInfo(RideRequest $rideRequest): ?array
    {
        if (($rideRequest->service_category ?? 'ride') !== 'parcel') {
            return null;
        }

        $parcelRequest = $rideRequest->parcelRequest;
        if (! $parcelRequest) {
            return null;
        }

        $category = \App\Models\ParcelCategory::query()
            ->where('key', $parcelRequest->category)
            ->first();
        $task = \App\Models\ParcelTask::query()
            ->where('key', $parcelRequest->task_type)
            ->first();

        return [
            'category_key' => $parcelRequest->category,
            'category_title' => $category?->title ?? $parcelRequest->category,
            'task_key' => $parcelRequest->task_type,
            'task_title' => $task?->title ?? $parcelRequest->task_type,
            'parcel_description' => $parcelRequest->parcel_description,
            'special_instructions' => $parcelRequest->special_instructions,
            'estimated_weight' => $parcelRequest->estimated_weight ?? '',
            'recipient_name' => $parcelRequest->recipient_name,
            'recipient_phone' => $parcelRequest->recipient_phone,
            'recipient_phone_dial_code' => $parcelRequest->recipient_phone_dial_code ?? '',
            'recipient_address' => $parcelRequest->recipient_address ?: $rideRequest->drop_location,
            'pickup_contact_name' => $parcelRequest->pickup_contact_name ?: trim(($rideRequest->users?->first_name ?? '').' '.($rideRequest->users?->last_name ?? '')),
            'pickup_contact_phone' => $parcelRequest->pickup_contact_phone ?: ($rideRequest->users?->mobile_number ?? ''),
        ];
    }

    protected function resolvePeakHours(Carbon $now): array
    {
        $minutes = ($now->hour * 60) + $now->minute;
        $morningStart = 7 * 60;
        $morningEnd = 9 * 60;
        $eveningStart = 17 * 60;
        $eveningEnd = 20 * 60;

        $active = ($minutes >= $morningStart && $minutes < $morningEnd)
            || ($minutes >= $eveningStart && $minutes < $eveningEnd);

        if ($active) {
            return [
                'Peak hours active',
                'Higher demand — expect more requests',
                true,
            ];
        }

        return [
            'Peak hours: 7–9 AM, 5–8 PM',
            'Higher fares during these times',
            false,
        ];
    }
}
