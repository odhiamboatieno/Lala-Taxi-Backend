<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helper\InvoiceHelper;
use App\Models\FavoriteDriver;
use App\Models\ManageFare;
use App\Models\Request as RideRequest;
use App\Models\TripRequest;
use App\Models\Trips;
use App\Services\Dispatch\TripDispatchService;
use App\Support\TripWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use Throwable;

class RealtimeTripController extends Controller
{
    public function __construct(
        protected TripDispatchService $dispatchService,
        protected InvoiceHelper $invoiceHelper
    )
    {
    }

    public function requestTrip(Request $request)
    {
        try {
            $rider = JWTAuth::parseToken()->authenticate();

            Log::info('RealtimeTrip: requestTrip received', [
                'rider_id' => $rider?->id,
                'car_id' => $request->input('car_id'),
                'billing_mode' => $request->input('billing_mode'),
            ]);

            $validator = Validator::make($request->all(), [
                'pickup_latitude' => 'required|numeric',
                'pickup_longitude' => 'required|numeric',
                'drop_latitude' => 'required|numeric',
                'drop_longitude' => 'required|numeric',
                'pickup_location' => 'required|string|max:255',
                'drop_location' => 'required|string|max:255',
                'car_id' => 'required|exists:car_type,id',
                'payment_mode' => 'nullable|string|max:50',
                'billing_mode' => 'nullable|in:personal,corporate',
                'corporate_account_id' => 'nullable|integer',
                'estimated_fare' => 'nullable',
                'estimated_distance' => 'nullable',
                'estimated_duration' => 'nullable',
                'trip_path' => 'nullable|string',
                'operator_type' => 'nullable|in:admin,company',
                'operator_id' => 'nullable|integer',
                'company_id' => 'nullable|exists:companies,id',
                'company_vehicle_type_id' => 'nullable|integer',
                'global_vehicle_type_id' => 'nullable|integer',
                'global_vehicle_category_id' => 'nullable|integer',
                'seats' => 'nullable|integer|min:1|max:8',
            ]);

            if ($validator->fails()) {
                Log::warning('RealtimeTrip: requestTrip validation failed', [
                    'rider_id' => $rider?->id,
                    'errors' => $validator->errors()->toArray(),
                ]);

                return response()->json([
                    'status_code' => '0',
                    'status_message' => $validator->messages()->first(),
                ]);
            }

            $result = $this->dispatchService->requestTrip($rider, $validator->validated());
            $rideRequest = $result['request'];
            $trip = $result['trip'] ?? null;

            Log::info('RealtimeTrip: requestTrip completed successfully', [
                'trip_id' => $trip?->id,
                'rider_id' => $rider?->id,
                'trip_status' => $result['status'],
                'pending_request_id' => $rideRequest->id,
                'request_group_id' => $rideRequest->group_id,
            ]);

            return response()->json([
                'status_code' => '1',
                'status_message' => 'Trip request created successfully',
                'trip_id' => $trip?->id,
                'trip_status' => $result['status'],
                'request_id' => $rideRequest->id,
                'group_id' => $rideRequest->group_id,
                'request_timeout_seconds' => $this->dispatchService->requestTimeoutSeconds(),
                'server_time' => now()->toIso8601String(),
                'trip' => $trip
                    ? $this->serializeTrip($trip, $rider)
                    : $this->serializeSearchingRideRequest($rideRequest, $rider),
            ]);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'Trip request failed.';

            Log::warning('RealtimeTrip: requestTrip rejected', [
                'message' => $message,
                'errors' => $exception->errors(),
            ]);

            return response()->json([
                'status_code' => '0',
                'status_message' => $message,
            ], 422);
        } catch (Throwable $throwable) {
            Log::error('RealtimeTrip: requestTrip failed', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            return response()->json([
                'status_code' => '0',
                'status_message' => 'Trip request failed: '.$throwable->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, Trips $trip)
    {
        $actor = JWTAuth::parseToken()->authenticate();

        if ((int) $trip->user_id !== (int) $actor->id && (int) $trip->driver_id !== (int) $actor->id) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Unauthorized trip access.',
            ], 403);
        }

        $trip = $this->dispatchService->cancelTrip($trip, $actor, $request->input('reason'));

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip cancelled successfully',
            'trip' => $this->serializeTrip($trip, $actor),
        ]);
    }

    public function cancelRequest(Request $request, RideRequest $rideRequest)
    {
        $actor = JWTAuth::parseToken()->authenticate();
        $rideRequest = $this->dispatchService->cancelRideRequestGroup(
            $rideRequest,
            $actor,
            $request->input('reason')
        );

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip request cancelled successfully',
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
        ]);
    }

    public function timeoutRequest(RideRequest $rideRequest)
    {
        $actor = JWTAuth::parseToken()->authenticate();
        $rideRequest = $this->dispatchService->expireRideRequestGroup($rideRequest, $actor);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'No drivers found for this request.',
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
            'status' => TripWorkflow::STATUS_NO_DRIVER_FOUND,
        ]);
    }

    public function current()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $trip = Trips::query()
            ->when($user->user_type === 'Driver', fn ($q) => $q->where('driver_id', $user->id), fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', array_merge(TripWorkflow::activeStatuses(), [
                TripWorkflow::STATUS_LEGACY_RATING,
                TripWorkflow::STATUS_LEGACY_PAYMENT,
            ]))
            ->latest('id')
            ->first();

        if (! $trip && $user->user_type !== 'Driver') {
            $rideRequest = RideRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'Pending')
                ->latest('id')
                ->first();

            if ($rideRequest && $this->rideRequestHasExpired($rideRequest)) {
                $this->dispatchService->expireRideRequestGroup($rideRequest, $user);
                $rideRequest = null;
            }

            if ($rideRequest && ! RideRequest::query()->where('group_id', $rideRequest->group_id)->where('status', 'Accepted')->exists()) {
                return response()->json([
                    'status_code' => '1',
                    'status_message' => 'Ride request loaded successfully',
                    'trip' => $this->serializeSearchingRideRequest($rideRequest, $user),
                ]);
            }
        }

        if (! $trip) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'No active trip found.',
            ]);
        }

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip loaded successfully',
            'trip' => $this->serializeTrip($trip, $user),
        ]);
    }

    protected function rideRequestHasExpired(RideRequest $rideRequest): bool
    {
        return $rideRequest->created_at
            && $rideRequest->created_at->copy()
                ->addSeconds($this->dispatchService->requestTimeoutSeconds())
                ->lte(now());
    }

    public function show(Trips $trip)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ((int) $trip->user_id !== (int) $user->id && (int) $trip->driver_id !== (int) $user->id) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Unauthorized trip access.',
            ], 403);
        }

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip loaded successfully',
            'trip' => $this->serializeTrip($trip, $user),
        ]);
    }

    public function arrived(Trips $trip)
    {
        return $this->transition($trip, TripWorkflow::STATUS_ARRIVED, 'driver', 'Driver marked trip as arrived.');
    }

    public function start(Trips $trip)
    {
        return $this->transition($trip, TripWorkflow::STATUS_STARTED, 'driver', 'Trip started successfully.');
    }

    public function complete(Trips $trip)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ((int) $trip->driver_id !== (int) $user->id) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Only the assigned driver can update this trip.',
            ], 403);
        }

        $trip = $this->dispatchService->transitionTrip($trip, TripWorkflow::STATUS_COMPLETED, 'driver');

        if ($this->invoiceHelper->needsRealtimeRecalculation($trip)) {
            $this->invoiceHelper->calculation([
                'trip_id' => $trip->id,
                'user_type' => 'Driver',
                'user_id' => $user->id,
                'save_to_trip_table' => 1,
            ]);
            $trip = $trip->fresh();
        }

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Trip completed successfully.',
            'trip' => $this->serializeTrip($trip, $user),
        ]);
    }

    protected function transition(Trips $trip, string $status, string $changedBy, string $message)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ((int) $trip->driver_id !== (int) $user->id) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Only the assigned driver can update this trip.',
            ], 403);
        }

        $trip = $this->dispatchService->transitionTrip($trip, $status, $changedBy);

        return response()->json([
            'status_code' => '1',
            'status_message' => $message,
            'trip' => $this->serializeTrip($trip, $user),
        ]);
    }

    protected function serializeTrip(Trips $trip, $user = null): array
    {
        $favoriteEnabled = site_settings('favorite_driver_enabled') === '1';
        $isFavoriteDriver = false;
        if ($favoriteEnabled && $user && $user->user_type === 'Rider' && $trip->driver_id) {
            $isFavoriteDriver = FavoriteDriver::query()
                ->where('rider_id', $user->id)
                ->where('driver_id', $trip->driver_id)
                ->exists();
        }

        $fareMode = data_get($trip->fare_estimation, 'fare_mode', site_settings('fare_mode') ?: 'Taximeter');
        $estimatedFare = data_get($trip->fare_estimation, 'estimated_fare');
        $estimatedDistance = data_get($trip->fare_estimation, 'estimated_distance');
        $estimatedDuration = data_get($trip->fare_estimation, 'estimated_duration');
        $currencySymbol = html_entity_decode(optional($trip->currency)->original_symbol ?: optional($trip->currency)->symbol ?: '');
        $waitingTime = optional(optional($trip->car_type)->manage_fare)->waiting_time;
        $parcelInfo = $this->serializeParcelTripInfo($trip);

        return [
            'id' => $trip->id,
            'status' => $trip->status,
            'rider_id' => $trip->user_id,
            'driver_id' => $trip->driver_id,
            'operator_type' => $trip->operator_type ?: ($trip->company_id && function_exists('saas_dispatch_enabled') && saas_dispatch_enabled() && (int) $trip->company_id !== (int) config('saas_dispatch.platform_company_id', 1) ? 'company' : 'admin'),
            'operator_id' => $trip->operator_id ?: $trip->company_id,
            'company_id' => $trip->company_id,
            'company_vehicle_type_id' => $trip->company_vehicle_type_id,
            'global_vehicle_type_id' => $trip->global_vehicle_type_id ?: $trip->car_id,
            'global_vehicle_category_id' => $trip->global_vehicle_category_id,
            'service_category' => $trip->service_category ?? 'ride',
            'parcel_info' => $parcelInfo,
            'pickup_latitude' => (float) $trip->pickup_latitude,
            'pickup_longitude' => (float) $trip->pickup_longitude,
            'drop_latitude' => (float) $trip->drop_latitude,
            'drop_longitude' => (float) $trip->drop_longitude,
            'pickup_location' => $trip->pickup_location,
            'drop_location' => $trip->drop_location,
            'pickup' => [
                'lat' => (float) $trip->pickup_latitude,
                'lng' => (float) $trip->pickup_longitude,
                'address' => $trip->pickup_location,
            ],
            'dropoff' => [
                'lat' => (float) $trip->drop_latitude,
                'lng' => (float) $trip->drop_longitude,
                'address' => $trip->drop_location,
            ],
            'payment_mode' => $trip->payment_mode,
            'billing' => $trip->billing,
            'payment_status' => $trip->payment_status,
            'total_fare' => $trip->total_fare,
            'live_total_fare' => number_format($this->resolveRealtimeFarePreview($trip, $fareMode, $estimatedFare), 2, '.', ''),
            'driver_payout' => $trip->driver_payout,
            'currency_code' => $trip->currency_code,
            'currency_symbol' => $currencySymbol,
            'fare_mode' => $fareMode,
            'estimated_fare' => $estimatedFare,
            'fare_estimate' => $estimatedFare,
            'estimated_distance' => $estimatedDistance,
            'estimated_duration' => $estimatedDuration,
            'delivery_label' => $parcelInfo['category_title'] ?? '',
            'package_title' => $parcelInfo['category_title'] ?? '',
            'package_type' => $parcelInfo['category_key'] ?? '',
            'package_description' => $parcelInfo['parcel_description'] ?? '',
            'booking_contact_name' => $parcelInfo['recipient_name'] ?? '',
            'booking_contact_phone' => $parcelInfo['recipient_phone'] ?? '',
            'pickup_contact_name' => $parcelInfo['pickup_contact_name'] ?? '',
            'pickup_contact_phone' => $parcelInfo['pickup_contact_phone'] ?? '',
            'special_instructions' => $parcelInfo['special_instructions'] ?? '',
            'estimated_weight' => $parcelInfo['estimated_weight'] ?? '',
            'trip_path' => $trip->trip_path,
            'route_polyline' => $trip->trip_path,
            'polyline' => $trip->trip_path,
            'stop_points' => $trip->stop_points,
            'tips' => $trip->tips,
            'rider_name' => trim(($trip->users?->first_name ?? '').' '.($trip->users?->last_name ?? '')),
            'rider_phone' => $trip->users?->phone_number ?? '',
            'rider_thumb_image' => $trip->users?->profile_picture->src ?? url('images/user.jpeg'),
            'waiting_time' => $waitingTime,
            'arrive_time' => $trip->arrive_time ?? null,
            'total_km' => $trip->total_km,
            'otp' => $trip->otp,
            'driver_name' => trim(($trip->driver->first_name ?? '').' '.($trip->driver->last_name ?? '')),
            'driver_thumb_image' => $trip->driver->profile_picture->src ?? url('images/user.jpeg'),
            'vehicle_name' => optional($trip->driver->vehicle)->vehicle_name,
            'vehicle_number' => optional($trip->driver->vehicle)->vehicle_number,
            'rating' => $trip->driver_id ? getRiderRating($trip->driver_id) : '',
            'favorite_driver_enabled' => $favoriteEnabled,
            'is_favorite_driver' => $isFavoriteDriver,
            'accepted_at' => optional($trip->accepted_at)->toIso8601String(),
            'started_at' => optional($trip->started_at)->toIso8601String(),
            'completed_at' => optional($trip->completed_at)->toIso8601String(),
            'cancelled_at' => optional($trip->cancelled_at)->toIso8601String(),
            'created_at' => optional($trip->created_at)->toIso8601String(),
            'request_timeout_seconds' => $this->dispatchService->requestTimeoutSeconds(),
            'server_time' => now()->toIso8601String(),
            'admin_contact' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
            'manual_call_number' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
        ];
    }

    protected function serializeSearchingRideRequest(RideRequest $rideRequest, $user = null): array
    {
        $requestTimeoutSeconds = $this->dispatchService->requestTimeoutSeconds();
        $requestExpiresAt = $rideRequest->created_at
            ? $rideRequest->created_at->copy()->addSeconds($requestTimeoutSeconds)->toIso8601String()
            : null;
        $parcelInfo = $this->serializeParcelRequestInfo($rideRequest);

        return [
            'id' => null,
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
            'status' => TripWorkflow::STATUS_SEARCHING_DRIVER,
            'rider_id' => $rideRequest->user_id,
            'driver_id' => null,
            'operator_type' => $rideRequest->operator_type ?: ($rideRequest->company_id && function_exists('saas_dispatch_enabled') && saas_dispatch_enabled() && (int) $rideRequest->company_id !== (int) config('saas_dispatch.platform_company_id', 1) ? 'company' : 'admin'),
            'operator_id' => $rideRequest->operator_id ?: $rideRequest->company_id,
            'company_id' => $rideRequest->company_id,
            'company_vehicle_type_id' => $rideRequest->company_vehicle_type_id,
            'global_vehicle_type_id' => $rideRequest->global_vehicle_type_id ?: $rideRequest->car_id,
            'global_vehicle_category_id' => $rideRequest->global_vehicle_category_id,
            'service_category' => $rideRequest->service_category ?? 'ride',
            'parcel_info' => $parcelInfo,
            'favorite_driver_enabled' => site_settings('favorite_driver_enabled') === '1',
            'is_favorite_driver' => false,
            'pickup_latitude' => (float) $rideRequest->pickup_latitude,
            'pickup_longitude' => (float) $rideRequest->pickup_longitude,
            'drop_latitude' => (float) $rideRequest->drop_latitude,
            'drop_longitude' => (float) $rideRequest->drop_longitude,
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
            'fare_mode' => site_settings('fare_mode') ?: 'Taximeter',
            'estimated_fare' => $rideRequest->estimated_fare,
            'fare_estimate' => $rideRequest->estimated_fare,
            'estimated_distance' => $rideRequest->distance_estimate,
            'estimated_duration' => $rideRequest->estimated_duration ?? null,
            'payment_mode' => $rideRequest->payment_mode,
            'billing' => $rideRequest->billing,
            'payment_status' => 'Pending',
            'delivery_label' => $parcelInfo['category_title'] ?? '',
            'package_title' => $parcelInfo['category_title'] ?? '',
            'package_type' => $parcelInfo['category_key'] ?? '',
            'package_description' => $parcelInfo['parcel_description'] ?? '',
            'booking_contact_name' => $parcelInfo['recipient_name'] ?? '',
            'booking_contact_phone' => $parcelInfo['recipient_phone'] ?? '',
            'pickup_contact_name' => $parcelInfo['pickup_contact_name'] ?? '',
            'pickup_contact_phone' => $parcelInfo['pickup_contact_phone'] ?? '',
            'special_instructions' => $parcelInfo['special_instructions'] ?? '',
            'estimated_weight' => $parcelInfo['estimated_weight'] ?? '',
            'trip_path' => $rideRequest->trip_path ?? '',
            'route_polyline' => $rideRequest->trip_path ?? '',
            'polyline' => $rideRequest->trip_path ?? '',
            'stop_points' => $rideRequest->stop_points ?? '',
            'pickup_location' => $rideRequest->pickup_location,
            'drop_location' => $rideRequest->drop_location,
            'accepted_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'created_at' => optional($rideRequest->created_at)->toIso8601String(),
            'requested_at' => optional($rideRequest->created_at)->toIso8601String(),
            'search_started_at' => optional($rideRequest->created_at)->toIso8601String(),
            'request_timeout_seconds' => $requestTimeoutSeconds,
            'request_expires_at' => $requestExpiresAt,
            'server_time' => now()->toIso8601String(),
            'admin_contact' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
            'manual_call_number' => site_settings('admin_contact') ?: (defined('MANUAL_BOOK_CONTACT') ? MANUAL_BOOK_CONTACT : ''),
        ];
    }

    protected function serializeParcelTripInfo(Trips $trip): ?array
    {
        if (($trip->service_category ?? 'ride') !== 'parcel') {
            return null;
        }

        $rideRequest = $trip->ride_request;
        if (! $rideRequest) {
            return null;
        }

        return $this->serializeParcelRequestInfo($rideRequest);
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

    protected function resolveRealtimeFarePreview(Trips $trip, string $fareMode, $estimatedFare): float
    {
        $estimated = (float) ($estimatedFare ?? 0);
        if (strcasecmp($fareMode, 'Pre-Calculated') === 0) {
            return $estimated;
        }

        if ($trip->status !== TripWorkflow::STATUS_STARTED) {
            return $estimated > 0 ? $estimated : (float) $trip->total_fare;
        }

        $fare = ManageFare::query()
            ->where('location_id', optional($trip->ride_request)->location_id)
            ->where('vehicle_id', $trip->car_id)
            ->first();

        if (! $fare) {
            return (float) $trip->total_fare;
        }

        $startedAt = $trip->started_at;
        if (! $startedAt && ! empty($trip->begin_trip)) {
            $startedAt = \Carbon\Carbon::parse($trip->begin_trip);
        }
        $minutes = $startedAt ? max(0, (int) round($startedAt->diffInMinutes(now()))) : 0;
        $distanceKm = (float) $trip->total_km;
        $sanitizedDistanceKm = $this->invoiceHelper->sanitizeTripDistanceKm($trip);
        if ($distanceKm > 0 && $sanitizedDistanceKm > 0 && $distanceKm > $sanitizedDistanceKm) {
            $distanceKm = $sanitizedDistanceKm;
        }
        $liveFare = (float) $fare->base_fare
            + ((float) $fare->per_min * $minutes)
            + ((float) $fare->per_km * $distanceKm);

        if ($liveFare < (float) $fare->min_fare) {
            $liveFare = (float) $fare->min_fare;
        }

        return $liveFare + (float) $trip->tips + (float) $trip->toll_fee;
    }
}
