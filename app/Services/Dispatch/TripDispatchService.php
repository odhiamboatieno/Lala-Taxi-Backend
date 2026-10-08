<?php

namespace App\Services\Dispatch;

use App\Events\DriverLocationUpdated;
use App\Events\NewTripRequest;
use App\Events\TripAssignedToOtherDriver;
use App\Events\TripCancelled;
use App\Events\TripCompleted;
use App\Events\TripDriverAssigned;
use App\Events\TripRequestCancelled;
use App\Events\TripRequestExpired;
use App\Events\TripSearchingStarted;
use App\Events\TripStatusUpdated;
use App\Http\Helper\RequestHelper;
use App\Jobs\AdvanceRideRequestJob;
use App\Jobs\DispatchTripRequestJob;
use App\Jobs\ExpireTripRequestJob;
use App\Models\FavoriteDriver;
use App\Models\Request as RideRequest;
use App\Models\TripRequest;
use App\Models\Trips;
use App\Models\User;
use App\Support\TripWorkflow;
use App\Services\LocationExtraFareService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\SurgePricing\Services\SurgePricingResolver;
use Throwable;

class TripDispatchService
{
    protected $dispatchContextResolver = null;
    protected $operatorResolver = null;

    public function __construct(
        protected NearbyDriverService $nearbyDriverService,
        $dispatchContextResolver = null,
        $operatorResolver = null,
    ) {
        $this->dispatchContextResolver = $dispatchContextResolver;
        $this->operatorResolver = $operatorResolver;
    }

    protected function resolveDispatchContextResolver()
    {
        if ($this->dispatchContextResolver === null) {
            $fqcn = 'Modules\\SaaSDispatch\\Services\\DispatchContextResolver';
            if (class_exists($fqcn)) {
                $this->dispatchContextResolver = app($fqcn);
            } else {
                $this->dispatchContextResolver = new class {
                    public function forPayload($rider, array $payload): array
                    {
                        return [
                            'company_id' => $rider->company_id ?? null,
                            'operator_type' => $rider->company_id ? 'company' : 'admin',
                            'operator_id' => $rider->company_id ?? null,
                            'location_id' => $payload['location_id'] ?? null,
                            'car_id' => $payload['car_id'] ?? null,
                            'company_vehicle_type_id' => null,
                            'global_vehicle_type_id' => $payload['car_id'] ?? null,
                            'global_vehicle_category_id' => null,
                            'is_platform' => !$rider->company_id,
                            'platform_company_id' => null,
                        ];
                    }
                };
            }
        }
        return $this->dispatchContextResolver;
    }

    protected function resolveOperatorResolver()
    {
        if ($this->operatorResolver === null) {
            $fqcn = 'Modules\\SaaSDispatch\\Services\\OperatorResolver';
            if (class_exists($fqcn)) {
                $this->operatorResolver = app($fqcn);
            } else {
                $this->operatorResolver = new class {
                    public function resolveForDriver($driver): array
                    {
                        if ($driver && $driver->company_id) {
                            return ['company_id' => $driver->company_id, 'operator_type' => 'company', 'operator_id' => $driver->company_id];
                        }
                        return ['company_id' => null, 'operator_type' => 'admin', 'operator_id' => null];
                    }

                    public function platformContext(): array
                    {
                        return ['company_id' => null];
                    }

                    public function resolveForTrip(object $trip): array
                    {
                        if ($trip && $trip->company_id) {
                            return ['company_id' => $trip->company_id, 'operator_type' => $trip->operator_type ?: 'admin', 'operator_id' => $trip->operator_id ?: $trip->company_id];
                        }
                        return ['company_id' => null, 'operator_type' => 'admin', 'operator_id' => null];
                    }
                };
            }
        }
        return $this->operatorResolver;
    }

    public function requestTrip(User $rider, array $payload): array
    {
        $payload = array_merge($payload, app(\App\Services\Corporate\CorporateBillingContext::class)->resolveForBooking($rider, $payload));
        if ($payload['billing_mode'] === 'corporate') {
            $payload['payment_mode'] = 'Corporate';
        }
        $activeTrip = $this->resolveBlockingActiveTrip($rider);

        if ($activeTrip) {
            throw ValidationException::withMessages([
                'trip' => 'Rider already has an active trip.',
            ]);
        }

        RideRequest::where('user_id', $rider->id)->where('status', 'Pending')
            ->where(function ($query) use ($payload) {
                $query->where('billing_mode', '!=', $payload['billing_mode'])
                    ->orWhere('corporate_account_id', '!=', $payload['corporate_account_id'])
                    ->when($payload['corporate_account_id'] === null, fn ($q) => $q->orWhereNotNull('corporate_account_id'));
            })->get()->unique(fn ($request) => $request->group_id ?: $request->id)
            ->each(fn ($request) => $this->cancelRideRequestGroup($request, $rider, __('corporate.payer_changed')));

        $reusableRequest = $this->findReusableRideRequest($rider, $payload);
        $dispatchPayload = $reusableRequest
            ? $this->payloadFromRideRequest($reusableRequest, $payload)
            : $this->normalizeRideRequestPayload($rider, $payload);

        $groupId = $reusableRequest?->group_id;

        if ($groupId) {
            $pendingRequest = RideRequest::query()
                ->where('group_id', $groupId)
                ->where('status', 'Pending')
                ->latest('id')
                ->first();

            if ($pendingRequest) {
                Log::info('Dispatch: returning existing pending ride request for rider', [
                    'rider_id' => $rider->id,
                    'request_id' => $pendingRequest->id,
                    'group_id' => $groupId,
                    'driver_id' => $pendingRequest->driver_id,
                ]);

                return [
                    'request' => $pendingRequest,
                    'trip' => null,
                    'status' => TripWorkflow::STATUS_SEARCHING_DRIVER,
                ];
            }
        }

        $rideRequest = $this->dispatchRideRequest($rider, $dispatchPayload, $groupId ? (int) $groupId : null);

        if (! $rideRequest) {
            throw ValidationException::withMessages([
                'trip' => 'No drivers available right now. Please retry in a moment.',
            ]);
        }

        Log::info('Dispatch: ride request created', [
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
            'rider_id' => $rider->id,
            'driver_id' => $rideRequest->driver_id,
            'car_id' => $rideRequest->car_id,
            'pickup_latitude' => $rideRequest->pickup_latitude,
            'pickup_longitude' => $rideRequest->pickup_longitude,
            'drop_latitude' => $rideRequest->drop_latitude,
            'drop_longitude' => $rideRequest->drop_longitude,
            'status' => $rideRequest->status,
        ]);

        $this->safeBroadcast(new TripSearchingStarted($rider->id, [
            'trip_id' => null,
            'request_id' => $rideRequest->id,
            'status' => TripWorkflow::STATUS_SEARCHING_DRIVER,
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
            'timestamp' => now()->toIso8601String(),
        ]));

        return [
            'request' => $rideRequest,
            'trip' => null,
            'status' => TripWorkflow::STATUS_SEARCHING_DRIVER,
        ];
    }

    protected function resolveBlockingActiveTrip(User $rider): ?Trips
    {
        $activeTrip = Trips::query()
            ->where('user_id', $rider->id)
            ->whereIn('status', TripWorkflow::activeStatuses())
            ->latest('id')
            ->first();

        if (! $activeTrip) {
            return null;
        }

        if (in_array($activeTrip->status, [
            TripWorkflow::STATUS_REQUESTED,
            TripWorkflow::STATUS_SEARCHING_DRIVER,
        ], true)) {
            Log::warning('Dispatch: auto-cancelling replaceable rider trip before creating a new one', [
                'rider_id' => $rider->id,
                'trip_id' => $activeTrip->id,
                'status' => $activeTrip->status,
                'created_at' => optional($activeTrip->created_at)->toIso8601String(),
            ]);

            $this->cancelTrip($activeTrip, $rider, 'Superseded by a new trip request.');

            return null;
        }

        return $activeTrip;
    }

    protected function findReusableRideRequest(User $rider, array $payload): ?RideRequest
    {
        $rideRequest = RideRequest::query()
            ->where('user_id', $rider->id)
            ->where('car_id', $payload['car_id'])
            ->where('pickup_latitude', (string) $payload['pickup_latitude'])
            ->where('pickup_longitude', (string) $payload['pickup_longitude'])
            ->where('drop_latitude', (string) $payload['drop_latitude'])
            ->where('drop_longitude', (string) $payload['drop_longitude'])
            ->where('billing_mode', $payload['billing_mode'] ?? 'personal')
            ->where('corporate_account_id', $payload['corporate_account_id'] ?? null)
            ->latest('id')
            ->first();

        if (! $rideRequest || ! $rideRequest->group_id || $rideRequest->status !== 'Pending') {
            return null;
        }

        if ($rideRequest->created_at && $rideRequest->created_at->copy()->addSeconds($this->driverRequestTimeoutSeconds())->lte(now())) {
            return null;
        }

        $groupAccepted = RideRequest::query()
            ->where('group_id', $rideRequest->group_id)
            ->where('status', 'Accepted')
            ->exists();

        if ($groupAccepted) {
            return null;
        }

        $requestIds = RideRequest::query()
            ->where('group_id', $rideRequest->group_id)
            ->pluck('id');

        if ($requestIds->isNotEmpty() && Trips::query()->whereIn('request_id', $requestIds)->exists()) {
            return null;
        }

        return $rideRequest;
    }

    protected function normalizeRideRequestPayload(User $rider, array $payload): array
    {
        $serviceCategory = $payload['service_category'] ?? 'ride';
        $resolved = $this->resolveDispatchContextResolver()->forPayload($rider, $payload);

        return array_merge(app(\App\Services\Corporate\CorporateBillingContext::class)->resolveForBooking($rider, $payload), [
            'pickup_latitude' => (string) $payload['pickup_latitude'],
            'pickup_longitude' => (string) $payload['pickup_longitude'],
            'drop_latitude' => (string) $payload['drop_latitude'],
            'drop_longitude' => (string) $payload['drop_longitude'],
            'pickup_location' => $payload['pickup_location'],
            'drop_location' => $payload['drop_location'],
            'car_id' => (int) $payload['car_id'],
            'payment_mode' => $payload['payment_mode'] ?? 'cash',
            'fare_mode' => $payload['fare_mode'] ?? (site_settings('fare_mode') ?: 'Taximeter'),
            'estimated_fare' => $payload['estimated_fare'] ?? null,
            'estimated_distance' => $payload['estimated_distance'] ?? null,
            'estimated_duration' => $payload['estimated_duration'] ?? null,
            'trip_path' => $payload['trip_path'] ?? '',
            'seats' => (int) ($payload['seats'] ?? 1),
            'timezone' => $payload['timezone'] ?? config('app.timezone', 'UTC'),
            'extra_fare_percentage' => $payload['extra_fare_percentage'] ?? 0,
            'extra_fare_reason' => $payload['extra_fare_reason'] ?? null,
            'service_category' => $serviceCategory,
            'parcel_info' => $serviceCategory === 'parcel' ? ($payload['parcel_info'] ?? null) : null,
            'booking_for_type' => $payload['booking_for_type'] ?? 'for_me',
            'booking_contact_name' => $payload['booking_contact_name'] ?? '',
            'booking_contact_phone' => $payload['booking_contact_phone'] ?? '',
            'stop_points' => $payload['stop_points'] ?? '',
        ], [
            'company_id' => $resolved['company_id'],
            'operator_type' => $resolved['operator_type'],
            'operator_id' => $resolved['operator_id'],
            'location_id' => (int) ($resolved['location_id'] ?? $payload['location_id'] ?? 1),
            'company_vehicle_type_id' => $resolved['company_vehicle_type_id'],
            'global_vehicle_type_id' => $resolved['global_vehicle_type_id'],
            'global_vehicle_category_id' => $resolved['global_vehicle_category_id'],
            'car_id' => (int) ($resolved['car_id'] ?? $payload['car_id']),
        ]);
    }

    protected function payloadFromRideRequest(RideRequest $rideRequest, array $overrides = []): array
    {
        app(\App\Services\Corporate\CorporateBillingContext::class)->validatePending($rideRequest);
        return array_merge([
            'pickup_latitude' => (string) $rideRequest->pickup_latitude,
            'pickup_longitude' => (string) $rideRequest->pickup_longitude,
            'drop_latitude' => (string) $rideRequest->drop_latitude,
            'drop_longitude' => (string) $rideRequest->drop_longitude,
            'pickup_location' => $rideRequest->pickup_location,
            'drop_location' => $rideRequest->drop_location,
            'car_id' => (int) $rideRequest->car_id,
            'payment_mode' => $rideRequest->payment_mode,
            'fare_mode' => $overrides['fare_mode'] ?? (site_settings('fare_mode') ?: 'Taximeter'),
            'estimated_fare' => $rideRequest->estimated_fare ?? ($overrides['estimated_fare'] ?? null),
            'estimated_distance' => $rideRequest->distance_estimate ?? ($overrides['estimated_distance'] ?? null),
            'estimated_duration' => $overrides['estimated_duration'] ?? null,
            'trip_path' => $rideRequest->trip_path ?? '',
            'company_id' => $overrides['company_id'] ?? $rideRequest->company_id,
            'operator_type' => $overrides['operator_type'] ?? $rideRequest->operator_type,
            'operator_id' => $overrides['operator_id'] ?? $rideRequest->operator_id,
            'seats' => (int) ($rideRequest->seats ?? 1),
            'timezone' => $rideRequest->timezone ?: config('app.timezone', 'UTC'),
            'location_id' => (int) ($rideRequest->location_id ?: 1),
            'extra_fare_percentage' => $rideRequest->extra_fare_percentage ?? 0,
            'extra_fare_reason' => $rideRequest->extra_fare_reason,
            'company_vehicle_type_id' => $overrides['company_vehicle_type_id'] ?? $rideRequest->company_vehicle_type_id,
            'global_vehicle_type_id' => $overrides['global_vehicle_type_id'] ?? $rideRequest->global_vehicle_type_id,
            'global_vehicle_category_id' => $overrides['global_vehicle_category_id'] ?? $rideRequest->global_vehicle_category_id,
            'service_category' => $rideRequest->service_category ?? 'ride',
            'booking_for_type' => $rideRequest->booking_for_type ?? 'for_me',
            'booking_contact_name' => $rideRequest->booking_contact_name ?? '',
            'booking_contact_phone' => $rideRequest->booking_contact_phone ?? '',
            'stop_points' => $rideRequest->stop_points ?? '',
        ], $overrides, app(\App\Services\Corporate\CorporateBillingContext::class)->attributes($rideRequest));
    }

    protected function dispatchRideRequest(User $rider, array $payload, ?int $groupId = null): ?RideRequest
    {
        $dispatchContext = $this->resolveDispatchContextResolver()->forPayload($rider, $payload);

        $searchTrip = new Trips();
        $searchTrip->forceFill([
            'car_id' => (int) ($dispatchContext['car_id'] ?? $payload['car_id']),
            'company_id' => $dispatchContext['company_id'],
            'operator_type' => $dispatchContext['operator_type'],
            'operator_id' => $dispatchContext['operator_id'],
            'company_vehicle_type_id' => $dispatchContext['company_vehicle_type_id'],
            'global_vehicle_type_id' => $dispatchContext['global_vehicle_type_id'],
            'global_vehicle_category_id' => $dispatchContext['global_vehicle_category_id'],
            'pickup_latitude' => (string) $payload['pickup_latitude'],
            'pickup_longitude' => (string) $payload['pickup_longitude'],
            'drop_latitude' => (string) $payload['drop_latitude'],
            'drop_longitude' => (string) $payload['drop_longitude'],
            'pickup_location' => $payload['pickup_location'],
            'drop_location' => $payload['drop_location'],
            'fare_estimation' => [
                'fare_mode' => $payload['fare_mode'] ?? (site_settings('fare_mode') ?: 'Taximeter'),
                'estimated_fare' => $payload['estimated_fare'] ?? null,
                'estimated_distance' => $payload['estimated_distance'] ?? null,
                'estimated_duration' => $payload['estimated_duration'] ?? null,
            ],
            'seats' => (int) ($payload['seats'] ?? 1),
        ]);

        Log::info('Dispatch: prepared transient ride search trip context', [
            'rider_id' => $rider->id,
            'car_id' => $searchTrip->getAttribute('car_id'),
            'company_id' => $searchTrip->getAttribute('company_id'),
            'operator_type' => $searchTrip->getAttribute('operator_type'),
            'operator_id' => $searchTrip->getAttribute('operator_id'),
            'pickup_latitude' => $searchTrip->getAttribute('pickup_latitude'),
            'pickup_longitude' => $searchTrip->getAttribute('pickup_longitude'),
            'drop_latitude' => $searchTrip->getAttribute('drop_latitude'),
            'drop_longitude' => $searchTrip->getAttribute('drop_longitude'),
        ]);

        $excludedDriverIds = $groupId
            ? RideRequest::query()->where('group_id', $groupId)->pluck('driver_id')->filter()->values()->all()
            : [];

        $preferredDriverIds = $this->resolvePreferredDriverIds($rider, $excludedDriverIds);

        $candidate = $this->nearbyDriverService
            ->findNearbyDrivers($searchTrip, $excludedDriverIds, 1, $preferredDriverIds)
            ->first();

        if (! $candidate) {
            Log::warning('Dispatch: no eligible drivers found for ride request group', [
                'rider_id' => $rider->id,
            'group_id' => $groupId,
            'excluded_driver_ids' => $excludedDriverIds,
            'preferred_driver_ids' => $preferredDriverIds,
            'pickup_latitude' => $payload['pickup_latitude'],
            'pickup_longitude' => $payload['pickup_longitude'],
            'car_id' => $payload['car_id'],
            ]);

            return null;
        }

        $driver = User::find($candidate->user_id);
        $assignmentContext = $dispatchContext;

        if ($driver && ($dispatchContext['is_platform'] ?? false) && $driver->company_id && (int) $driver->company_id !== (int) ($dispatchContext['platform_company_id'] ?? $dispatchContext['company_id'])) {
            $driverContext = $this->resolveOperatorResolver()->resolveForDriver($driver);
            $assignmentContext['company_id'] = $driverContext['company_id'];
            $assignmentContext['operator_type'] = $driverContext['operator_type'];
            $assignmentContext['operator_id'] = $driverContext['operator_id'];
        }

        $baseExtraFare = app(LocationExtraFareService::class)->getConfig($assignmentContext['location_id'] ?? $payload['location_id'] ?? null);
        $surge = app(SurgePricingResolver::class)->resolve(
            $assignmentContext['location_id'] ?? $payload['location_id'] ?? null,
            Carbon::now($payload['timezone'] ?? config('app.timezone', 'UTC')),
            optional($driver)->company_id,
            $payload['service_category'] ?? 'ride',
            (int) ($assignmentContext['car_id'] ?? $payload['car_id'])
        );
        $mergedExtraFare = app(SurgePricingResolver::class)->mergeWithExtraFare($baseExtraFare, $surge);

        $rideRequest = RideRequest::create([
            'billing_mode' => $payload['billing_mode'] ?? null,
            'corporate_account_id' => $payload['corporate_account_id'] ?? null,
            'user_id' => $rider->id,
            'company_id' => $assignmentContext['company_id'],
            'operator_type' => $assignmentContext['operator_type'],
            'operator_id' => $assignmentContext['operator_id'],
            'group_id' => $groupId,
            'seats' => $payload['seats'] ?? 1,
            'pickup_latitude' => $payload['pickup_latitude'],
            'pickup_longitude' => $payload['pickup_longitude'],
            'drop_latitude' => $payload['drop_latitude'],
            'drop_longitude' => $payload['drop_longitude'],
            'pickup_location' => $payload['pickup_location'],
            'drop_location' => $payload['drop_location'],
            'service_category' => $payload['service_category'] ?? 'ride',
            'booking_for_type' => $payload['booking_for_type'] ?? 'for_me',
            'booking_contact_name' => $payload['booking_contact_name'] ?? '',
            'booking_contact_phone' => $payload['booking_contact_phone'] ?? '',
            'stop_points' => $payload['stop_points'] ?? '',
            'driver_id' => $candidate->user_id,
            'car_id' => $assignmentContext['car_id'] ?? $payload['car_id'],
            'company_vehicle_type_id' => $assignmentContext['company_vehicle_type_id'],
            'global_vehicle_type_id' => $assignmentContext['global_vehicle_type_id'],
            'global_vehicle_category_id' => $assignmentContext['global_vehicle_category_id'],
            'payment_mode' => ucfirst((string) ($payload['payment_mode'] ?? 'cash')),
            'schedule_id' => 'Null',
            'location_id' => $assignmentContext['location_id'] ?? $payload['location_id'] ?? 1,
            'additional_fare' => '',
            'peak_fare' => '0',
            'additional_rider' => '0',
            'timezone' => $payload['timezone'] ?? config('app.timezone', 'UTC'),
            'trip_path' => $payload['trip_path'] ?? '',
            'status' => 'Pending',
            'estimated_fare' => $payload['estimated_fare'] ?? null,
            'distance_estimate' => $payload['estimated_distance'] ?? null,
            'extra_fare_percentage' => $mergedExtraFare['enabled'] ? ($mergedExtraFare['percentage'] ?? 0) : ($payload['extra_fare_percentage'] ?? 0),
            'extra_fare_reason' => $mergedExtraFare['enabled'] ? ($mergedExtraFare['reason'] ?? null) : ($payload['extra_fare_reason'] ?? null),
            'surge_pricing_id' => $surge['surge_pricing_id'] ?? null,
            'surge_name' => $surge['name'] ?? null,
            'surge_percentage' => round((float) ($surge['percentage'] ?? 0), 2),
        ]);

        if (! $rideRequest->group_id) {
            $rideRequest->group_id = $rideRequest->id;
            $rideRequest->save();
        }

        $expiresAt = now()->addSeconds($this->driverRequestTimeoutSeconds());
        $requestPayload = $this->buildRideRequestPayload(
            $rideRequest,
            $rider,
            $payload,
            $expiresAt,
            'ride_request_'.$rideRequest->id
        );

        $this->safeBroadcast(new NewTripRequest($candidate->user_id, $requestPayload));

        $this->sendIncomingRideWakePush($candidate->user_id, $requestPayload);

        Log::info('Dispatch: realtime ride request emitted to driver', [
            'request_id' => $rideRequest->id,
            'group_id' => $rideRequest->group_id,
            'driver_id' => $candidate->user_id,
            'channel' => 'private-driver.'.$candidate->user_id,
        ]);

        AdvanceRideRequestJob::dispatch($rideRequest->id)
            ->delay($expiresAt);

        return $rideRequest->fresh();
    }

    protected function buildRideRequestPayload(RideRequest $rideRequest, User $rider, array $payload, Carbon $expiresAt, string $uuid): array
    {
        return [
            'billing' => $rideRequest->billing,
            'payment_mode' => $rideRequest->payment_mode,
            'trip_request_uuid' => $uuid,
            'notification_type' => 'trip_request',
            'trip_id' => null,
            'request_id' => $rideRequest->id,
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
            'estimated_distance' => $payload['estimated_distance'] ?? $rideRequest->distance_estimate ?? null,
            'estimated_duration' => $payload['estimated_duration'] ?? null,
            'fare_mode' => $payload['fare_mode'] ?? (site_settings('fare_mode') ?: 'Taximeter'),
            'fare_estimate' => $payload['estimated_fare'] ?? $rideRequest->estimated_fare ?? null,
            'service_category' => $payload['service_category'] ?? $rideRequest->service_category ?? 'ride',
            'booking_contact_name' => $payload['booking_contact_name'] ?? $rideRequest->booking_contact_name ?? '',
            'booking_contact_phone' => $payload['booking_contact_phone'] ?? $rideRequest->booking_contact_phone ?? '',
            'parcel_info' => $payload['parcel_info'] ?? null,
            'expires_at' => $expiresAt->toIso8601String(),
            'request_timeout_seconds' => $expiresAt->diffInSeconds(now()),
            'rider_basic_info' => [
                'id' => $rider->id,
                'name' => trim($rider->first_name.' '.$rider->last_name),
            ],
        ];
    }

    protected function buildTripRequestPayload(Trips $trip, TripRequest $tripRequest): array
    {
        return [
            'billing' => $trip->billing,
            'payment_mode' => $trip->payment_mode,
            'trip_request_uuid' => 'trip_request_'.$tripRequest->id,
            'notification_type' => 'trip_request',
            'trip_id' => $trip->id,
            'request_id' => $tripRequest->id,
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
            'estimated_distance' => data_get($trip->fare_estimation, 'estimated_distance'),
            'estimated_duration' => data_get($trip->fare_estimation, 'estimated_duration'),
            'fare_mode' => data_get($trip->fare_estimation, 'fare_mode', site_settings('fare_mode') ?: 'Taximeter'),
            'fare_estimate' => data_get($trip->fare_estimation, 'estimated_fare'),
            'expires_at' => optional($tripRequest->expires_at)->toIso8601String(),
            'request_timeout_seconds' => $tripRequest->expires_at ? max(0, now()->diffInSeconds($tripRequest->expires_at, false)) : null,
            'rider_basic_info' => [
                'id' => $trip->user_id,
                'name' => trim($trip->users?->first_name.' '.$trip->users?->last_name),
            ],
        ];
    }

    protected function sendIncomingRideWakePush(int $driverId, array $requestPayload): void
    {
        $driver = User::find($driverId);
        if (! $driver || empty($driver->device_id)) {
            return;
        }

        $pickup = data_get($requestPayload, 'pickup.address', 'Pickup requested');
        $dropoff = data_get($requestPayload, 'dropoff.address', 'destination pending');

        try {
            resolve(RequestHelper::class)->SendPushNotification($driver, [
                'push_title' => 'New ride request',
                'change_title' => 1,
                'data' => [
                    'notification_type' => 'trip_request',
                    'trip_request_uuid' => $requestPayload['trip_request_uuid'],
                    'request_id' => $requestPayload['request_id'],
                    'trip_id' => $requestPayload['trip_id'],
                    'expires_at' => $requestPayload['expires_at'],
                    'ttl' => '0',
                    'priority' => 'high',
                    'trip_request' => $requestPayload,
                    'custom_message' => [
                        'push_title' => 'New ride request',
                        'title' => 'New ride request',
                        'message_data' => $pickup.' to '.$dropoff,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Dispatch: incoming ride push fallback failed', [
                'driver_id' => $driverId,
                'request_id' => $requestPayload['request_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function driverRequestTimeoutSeconds(): int
    {
        $seconds = (int) (site_settings('driver_request_seconds') ?: 30);

        return $seconds > 0 ? $seconds : 30;
    }

    public function requestTimeoutSeconds(): int
    {
        return $this->driverRequestTimeoutSeconds();
    }

    protected function resolvePreferredDriverIds(User $rider, array $excludedDriverIds): array
    {
        if (site_settings('favorite_driver_enabled') !== '1') {
            return [];
        }

        return FavoriteDriver::query()
            ->where('rider_id', $rider->id)
            ->when($excludedDriverIds !== [], fn ($q) => $q->whereNotIn('driver_id', $excludedDriverIds))
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function advanceRideRequestById(int $requestId): void
    {
        $rideRequest = null;

        DB::transaction(function () use ($requestId, &$rideRequest) {
            $rideRequest = RideRequest::query()->lockForUpdate()->find($requestId);

            if (! $rideRequest || $rideRequest->status !== 'Pending') {
                $rideRequest = null;
                return;
            }

            $rideRequest->update(['status' => 'Cancelled']);
        });

        if ($rideRequest) {
            $this->dispatchNextRideRequest($rideRequest);
        }
    }

    public function cancelRideRequestGroup(RideRequest $rideRequest, User $actor, ?string $reason = null): RideRequest
    {
        return DB::transaction(function () use ($rideRequest, $actor, $reason) {
            $rideRequest = RideRequest::query()->lockForUpdate()->findOrFail($rideRequest->id);

            if ((int) $rideRequest->user_id !== (int) $actor->id) {
                throw ValidationException::withMessages([
                    'request' => 'Unauthorized request access.',
                ]);
            }

            if ($rideRequest->status === 'Accepted') {
                throw ValidationException::withMessages([
                    'request' => 'Trip request has already been accepted.',
                ]);
            }

            RideRequest::query()
                ->where('group_id', $rideRequest->group_id ?: $rideRequest->id)
                ->where('status', 'Pending')
                ->get()
                ->each(function (RideRequest $pending) use ($reason, $actor) {
                    $pending->update(['status' => 'Cancelled']);

                    $this->safeBroadcast(new TripRequestCancelled($pending->driver_id, [
                        'trip_id' => null,
                        'request_id' => $pending->id,
                        'status' => TripWorkflow::STATUS_CANCELLED,
                        'reason' => $reason,
                        'cancelled_by' => strtolower($actor->user_type),
                        'timestamp' => now()->toIso8601String(),
                    ]));
                });

            $this->safeBroadcast(new TripCancelled(0, $rideRequest->user_id, null, null, [
                'trip_id' => null,
                'request_id' => $rideRequest->id,
                'group_id' => $rideRequest->group_id ?: $rideRequest->id,
                'status' => TripWorkflow::STATUS_CANCELLED,
                'reason' => $reason,
                'cancelled_by' => strtolower($actor->user_type),
                'timestamp' => now()->toIso8601String(),
            ]));

            return $rideRequest->fresh();
        });
    }

    public function expireRideRequestGroup(RideRequest $rideRequest, User $actor): RideRequest
    {
        return DB::transaction(function () use ($rideRequest, $actor) {
            $rideRequest = RideRequest::query()->lockForUpdate()->findOrFail($rideRequest->id);

            if ((int) $rideRequest->user_id !== (int) $actor->id) {
                throw ValidationException::withMessages([
                    'request' => 'Unauthorized request access.',
                ]);
            }

            RideRequest::query()
                ->where('group_id', $rideRequest->group_id ?: $rideRequest->id)
                ->where('status', 'Pending')
                ->get()
                ->each(function (RideRequest $pending) {
                    $pending->update(['status' => 'Expired']);

                    $this->safeBroadcast(new TripRequestExpired($pending->driver_id, [
                        'trip_id' => null,
                        'request_id' => $pending->id,
                        'status' => TripWorkflow::STATUS_NO_DRIVER_FOUND,
                        'timestamp' => now()->toIso8601String(),
                    ]));
                });

            $this->safeBroadcast(new TripCancelled(0, $rideRequest->user_id, null, null, [
                'trip_id' => null,
                'request_id' => $rideRequest->id,
                'group_id' => $rideRequest->group_id ?: $rideRequest->id,
                'status' => TripWorkflow::STATUS_NO_DRIVER_FOUND,
                'timestamp' => now()->toIso8601String(),
            ]));

            return $rideRequest->fresh();
        });
    }

    public function rejectRideRequest(int $requestId, User $driver): RideRequest
    {
        $rideRequest = RideRequest::query()->findOrFail($requestId);

        if ((int) $rideRequest->driver_id !== (int) $driver->id) {
            throw ValidationException::withMessages([
                'request' => 'Unauthorized driver request access.',
            ]);
        }

        if ($rideRequest->status === 'Pending') {
            $rideRequest->update(['status' => 'Cancelled']);
            $this->dispatchNextRideRequest($rideRequest);
        }

        return $rideRequest->fresh();
    }

    public function acceptRideRequest(int $requestId, User $driver): Trips
    {
        return DB::transaction(function () use ($requestId, $driver) {
            $candidate = RideRequest::findOrFail($requestId);
            if ((int) $candidate->driver_id !== (int) $driver->id) {
                throw ValidationException::withMessages(['request' => 'Unauthorized driver request access.']);
            }
            app(\App\Services\Corporate\CorporateBillingContext::class)->assertCanAccept($candidate);
            RideRequest::query()->when($candidate->group_id,
                fn ($query) => $query->where('group_id', $candidate->group_id),
                fn ($query) => $query->where('id', $candidate->id))
                ->orderBy('id')->lockForUpdate()->firstOrFail();
            $rideRequest = RideRequest::query()
                ->with('users')
                ->lockForUpdate()
                ->findOrFail($requestId);

            if ((int) $rideRequest->driver_id !== (int) $driver->id) {
                throw ValidationException::withMessages([
                    'request' => 'Unauthorized driver request access.',
                ]);
            }

            if ($rideRequest->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'request' => 'Trip request is no longer available.',
                ]);
            }

            if (RideRequest::query()
                ->where('group_id', $rideRequest->group_id)
                ->where('status', 'Accepted')
                ->exists()) {
                throw ValidationException::withMessages([
                    'request' => 'Trip request has already been accepted.',
                ]);
            }

            $rideRequest->update(['status' => 'Accepted']);

            $parcelCategory = null;
            $parcelTask = null;
            if ($rideRequest->service_category === 'parcel') {
                $parcelRequest = \App\Models\ParcelRequest::where('request_id', $rideRequest->id)->first();
                if ($parcelRequest) {
                    $parcelCategory = \App\Models\ParcelCategory::where('key', $parcelRequest->category)->first();
                    $parcelTask = \App\Models\ParcelTask::where('key', $parcelRequest->task_type)->first();
                }
            }

            $trip = Trips::create([
                'user_id' => $rideRequest->user_id,
                'company_id' => $rideRequest->company_id ?: ($driver->company_id ?: $this->resolveOperatorResolver()->platformContext()['company_id']),
                'operator_type' => $rideRequest->operator_type ?: $this->resolveOperatorResolver()->resolveForDriver($driver)['operator_type'],
                'operator_id' => $rideRequest->operator_id ?: $this->resolveOperatorResolver()->resolveForDriver($driver)['operator_id'],
                'pool_id' => 0,
                'pickup_latitude' => $rideRequest->pickup_latitude,
                'pickup_longitude' => $rideRequest->pickup_longitude,
                'drop_latitude' => $rideRequest->drop_latitude,
                'drop_longitude' => $rideRequest->drop_longitude,
                'pickup_location' => $rideRequest->pickup_location,
                'drop_location' => $rideRequest->drop_location,
                'service_category' => $rideRequest->service_category ?? 'ride',
                'car_id' => $rideRequest->global_vehicle_type_id ?: $rideRequest->car_id,
                'company_vehicle_type_id' => $rideRequest->company_vehicle_type_id,
                'global_vehicle_type_id' => $rideRequest->global_vehicle_type_id ?: $rideRequest->car_id,
                'global_vehicle_category_id' => $rideRequest->global_vehicle_category_id,
                'request_id' => $rideRequest->id,
                'driver_id' => $driver->id,
                'trip_path' => $rideRequest->trip_path ?? '',
                'map_image' => '',
                'seats' => $rideRequest->seats ?? 1,
                'total_time' => 0,
                'total_km' => 0,
                'time_fare' => 0,
                'distance_fare' => 0,
                'base_fare' => 0,
                'additional_rider' => 0,
                'additional_rider_amount' => 0,
                'peak_fare' => 0,
                'peak_amount' => 0,
                'driver_peak_amount' => 0,
                'schedule_fare' => 0,
                'access_fee' => 0,
                'tips' => 0,
                'waiting_charge' => 0,
                'toll_fee' => 0,
                'extra_fare_percentage' => $rideRequest->extra_fare_percentage ?? 0,
                'extra_fare_amount' => 0,
                'extra_fare_reason' => $rideRequest->extra_fare_reason,
                'surge_pricing_id' => $rideRequest->surge_pricing_id,
                'surge_name' => $rideRequest->surge_name,
                'surge_percentage' => $rideRequest->surge_percentage ?? 0,
                'wallet_amount' => 0,
                'promo_amount' => 0,
                'subtotal_fare' => 0,
                'total_fare' => 0,
                'driver_payout' => 0,
                'driver_or_company_commission' => 0,
                'owe_amount' => 0,
                'remaining_owe_amount' => 0,
                'applied_owe_amount' => 0,
                'to_trip_id' => '',
                'paykey' => '',
                'payment_mode' => $rideRequest->payment_mode,
                'payment_status' => 'Pending',
                'is_calculation' => '0',
                'currency_code' => $rideRequest->users?->currency_code ?: ($driver->currency_code ?: 'USD'),
                'service_category' => $rideRequest->service_category ?? 'ride',
                'parcel_category_id' => $parcelCategory?->id,
                'parcel_task_id' => $parcelTask?->id,
                'fare_estimation' => [
                    'fare_mode' => site_settings('fare_mode') ?: 'Taximeter',
                    'estimated_fare' => $rideRequest->estimated_fare ?? null,
                    'estimated_distance' => $rideRequest->distance_estimate ?? null,
                    'estimated_duration' => null,
                ],
                'status' => TripWorkflow::STATUS_DRIVER_ASSIGNED,
                'otp' => (string) mt_rand(1000, 9999),
                'accepted_at' => now(),
            ]);

            $driver->update([
                'current_trip_id' => $trip->id,
                'is_available' => false,
                'is_online' => true,
                'last_seen_at' => now(),
            ]);

            $trip = $this->transitionTrip($trip, TripWorkflow::STATUS_DRIVER_ARRIVING, 'driver');

            RideRequest::query()
                ->where('group_id', $rideRequest->group_id)
                ->where('id', '!=', $rideRequest->id)
                ->where('status', 'Pending')
                ->get()
                ->each(function (RideRequest $pending) use ($trip) {
                    $pending->update(['status' => 'Cancelled']);

                    $this->safeBroadcast(new TripAssignedToOtherDriver($pending->driver_id, [
                        'trip_id' => $trip->id,
                        'request_id' => $pending->id,
                    ]));
                });

            $this->safeBroadcast(new TripDriverAssigned($trip->user_id, $trip->id, [
                'trip_id' => $trip->id,
                'driver_id' => $driver->id,
                'billing' => $trip->billing,
                'payment_mode' => $trip->payment_mode,
                'payment_status' => $trip->payment_status,
                'driver_name' => trim($driver->first_name.' '.$driver->last_name),
                'vehicle' => [
                    'name' => optional($driver->vehicle)->vehicle_name,
                    'number' => optional($driver->vehicle)->vehicle_number,
                ],
                'driver_location' => [
                    'lat' => (float) $driver->current_lat,
                    'lng' => (float) $driver->current_lng,
                ],
                'eta' => $rideRequest->estimated_duration ?? null,
            ]));

            $this->broadcastTripStatus($trip, TripWorkflow::STATUS_SEARCHING_DRIVER, 'driver');

            return $trip->fresh();
        });
    }

    protected function dispatchNextRideRequest(RideRequest $rideRequest): void
    {
        $rider = User::query()->find($rideRequest->user_id);

        if (! $rider) {
            return;
        }

        try {
            $payload = $this->payloadFromRideRequest($rideRequest);
        } catch (ValidationException $exception) {
            if (!$rideRequest->isCorporate()) {
                throw $exception;
            }
            if (!RideRequest::where('group_id', $rideRequest->group_id)->where('status', 'Accepted')->exists()) {
                $this->cancelRideRequestGroup($rideRequest, $rider, __('corporate.booking_unavailable'));
            }
            Log::warning('Corporate dispatch retry stopped', ['request_id' => $rideRequest->id]);
            return;
        }
        $nextRequest = $this->dispatchRideRequest($rider, $payload, (int) $rideRequest->group_id);

        if (! $nextRequest) {
            Log::info('Dispatch: ride request group exhausted without driver acceptance', [
                'rider_id' => $rideRequest->user_id,
                'group_id' => $rideRequest->group_id,
            ]);
        }
    }

    public function dispatchTripRequests(Trips $trip): void
    {
        $trip->refresh();

        if ($trip->status !== TripWorkflow::STATUS_SEARCHING_DRIVER) {
            Log::info('Dispatch: trip skipped because status is no longer dispatchable', [
                'trip_id' => $trip->id,
                'status' => $trip->status,
            ]);
            return;
        }

        $sentDriverIds = TripRequest::where('trip_id', $trip->id)->pluck('driver_id')->all();
        $candidates = $this->nearbyDriverService->findNearbyDrivers(
            $trip,
            $sentDriverIds,
            config('dispatch.initial_batch_size', 3)
        );

        Log::info('Dispatch: nearby driver search completed', [
            'trip_id' => $trip->id,
            'car_id' => $trip->car_id,
            'company_id' => $trip->company_id,
            'already_sent_driver_ids' => $sentDriverIds,
            'candidate_count' => $candidates->count(),
            'candidate_driver_ids' => $candidates->pluck('user_id')->values()->all(),
        ]);

        if ($candidates->isEmpty()) {
            Log::warning('Dispatch: no eligible drivers found for trip', [
                'trip_id' => $trip->id,
                'car_id' => $trip->car_id,
                'company_id' => $trip->company_id,
                'pickup_latitude' => $trip->pickup_latitude,
                'pickup_longitude' => $trip->pickup_longitude,
            ]);
        }

        foreach ($candidates as $candidate) {
            $tripRequest = TripRequest::create([
                'trip_id' => $trip->id,
                'driver_id' => $candidate->user_id,
                'rider_id' => $trip->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds(config('dispatch.request_timeout_seconds', 20)),
            ]);

            Log::info('Dispatch: trip request row created for driver', [
                'trip_id' => $trip->id,
                'trip_request_id' => $tripRequest->id,
                'driver_id' => $candidate->user_id,
                'expires_at' => optional($tripRequest->expires_at)->toIso8601String(),
            ]);

            $requestPayload = $this->buildTripRequestPayload($trip, $tripRequest);

            $this->safeBroadcast(new NewTripRequest($candidate->user_id, $requestPayload));

            $this->sendIncomingRideWakePush($candidate->user_id, $requestPayload);

            Log::info('Dispatch: NewTripRequest broadcast emitted', [
                'trip_id' => $trip->id,
                'trip_request_id' => $tripRequest->id,
                'driver_id' => $candidate->user_id,
                'channel' => 'private-driver.'.$candidate->user_id,
            ]);

            ExpireTripRequestJob::dispatch($tripRequest->id)->delay($tripRequest->expires_at);
        }
    }

    public function expireTripRequest(TripRequest $tripRequest): void
    {
        $dispatchNext = false;

        DB::transaction(function () use (&$dispatchNext, $tripRequest) {
            $tripRequest = TripRequest::query()->lockForUpdate()->find($tripRequest->id);

            if (! $tripRequest || $tripRequest->status !== 'pending') {
                return;
            }

            $tripRequest->update([
                'status' => 'expired',
                'responded_at' => now(),
            ]);

            $this->safeBroadcast(new TripRequestExpired($tripRequest->driver_id, [
                'trip_id' => $tripRequest->trip_id,
                'request_id' => $tripRequest->id,
            ]));

            $trip = Trips::find($tripRequest->trip_id);

            if ($trip && $trip->status === TripWorkflow::STATUS_SEARCHING_DRIVER) {
                $dispatchNext = ! TripRequest::where('trip_id', $trip->id)->where('status', 'pending')->exists();
            }
        });

        if ($dispatchNext) {
            DispatchTripRequestJob::dispatch($tripRequest->trip_id);
        }
    }

    public function rejectTripRequest(TripRequest $tripRequest, User $driver): TripRequest
    {
        if ((int) $tripRequest->driver_id !== (int) $driver->id) {
            throw ValidationException::withMessages([
                'request' => 'Unauthorized driver request access.',
            ]);
        }

        $tripRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        if (! TripRequest::where('trip_id', $tripRequest->trip_id)->where('status', 'pending')->exists()) {
            DispatchTripRequestJob::dispatch($tripRequest->trip_id);
        }

        return $tripRequest->fresh();
    }

    public function acceptTripRequest(TripRequest $tripRequest, User $driver): Trips
    {
        return DB::transaction(function () use ($tripRequest, $driver) {
            $candidate = TripRequest::findOrFail($tripRequest->id);
            if ((int) $candidate->driver_id !== (int) $driver->id) {
                throw ValidationException::withMessages(['request' => 'Unauthorized driver request access.']);
            }
            // Lock/revalidate the payer and rider before locking the offer/trip, just
            // as request-backed acceptance does. Ignore only this searching trip.
            app(\App\Services\Corporate\CorporateBillingContext::class)->assertCanAccept(Trips::findOrFail($candidate->trip_id));
            $tripRequest = TripRequest::query()
                ->with(['trip.users', 'driver.profile_picture', 'driver.vehicle'])
                ->lockForUpdate()
                ->findOrFail($tripRequest->id);

            if ((int) $tripRequest->driver_id !== (int) $driver->id) {
                throw ValidationException::withMessages([
                    'request' => 'Unauthorized driver request access.',
                ]);
            }

            if ($tripRequest->status !== 'pending' || ($tripRequest->expires_at && $tripRequest->expires_at->isPast())) {
                throw ValidationException::withMessages([
                    'request' => 'Trip request is no longer available.',
                ]);
            }

            $trip = Trips::query()->lockForUpdate()->findOrFail($tripRequest->trip_id);

            if ($trip->status !== TripWorkflow::STATUS_SEARCHING_DRIVER) {
                throw ValidationException::withMessages([
                    'trip' => 'Trip is no longer accepting drivers.',
                ]);
            }

            $tripRequest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            $previousStatus = $trip->status;

            $trip->update([
                'driver_id' => $driver->id,
                'company_id' => $trip->company_id ?: ($driver->company_id ?: $this->resolveOperatorResolver()->platformContext()['company_id']),
                'operator_type' => $trip->operator_type ?: $this->resolveOperatorResolver()->resolveForDriver($driver)['operator_type'],
                'operator_id' => $trip->operator_id ?: $this->resolveOperatorResolver()->resolveForDriver($driver)['operator_id'],
                'status' => TripWorkflow::STATUS_DRIVER_ARRIVING,
                'accepted_at' => now(),
            ]);

            $driver->update([
                'current_trip_id' => $trip->id,
                'is_available' => false,
                'is_online' => true,
                'last_seen_at' => now(),
            ]);

            TripRequest::where('trip_id', $trip->id)
                ->where('id', '!=', $tripRequest->id)
                ->where('status', 'pending')
                ->get()
                ->each(function (TripRequest $pending) use ($trip) {
                    $pending->update([
                        'status' => 'cancelled',
                        'responded_at' => now(),
                    ]);

                    $this->safeBroadcast(new TripAssignedToOtherDriver($pending->driver_id, [
                        'trip_id' => $trip->id,
                        'request_id' => $pending->id,
                    ]));
                });

            $this->safeBroadcast(new TripDriverAssigned($trip->user_id, $trip->id, [
                'trip_id' => $trip->id,
                'driver_id' => $driver->id,
                'billing' => $trip->billing,
                'payment_mode' => $trip->payment_mode,
                'payment_status' => $trip->payment_status,
                'driver_name' => trim($driver->first_name.' '.$driver->last_name),
                'vehicle' => [
                    'name' => optional($driver->vehicle)->vehicle_name,
                    'number' => optional($driver->vehicle)->vehicle_number,
                ],
                'driver_location' => [
                    'lat' => (float) $driver->current_lat,
                    'lng' => (float) $driver->current_lng,
                ],
                'eta' => data_get($trip->fare_estimation, 'estimated_duration'),
            ]));

            $this->broadcastTripStatus($trip, $previousStatus, 'driver');

            return $trip->fresh();
        });
    }

    public function assignTripToDriver(Trips $trip, User $driver, string $changedBy = 'dispatcher'): Trips
    {
        $tripRequest = TripRequest::create([
            'trip_id' => $trip->id,
            'driver_id' => $driver->id,
            'rider_id' => $trip->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds(config('dispatch.request_timeout_seconds', 20)),
        ]);

        return $this->acceptTripRequest($tripRequest, $driver);
    }

    public function cancelTrip(Trips $trip, User $actor, ?string $reason = null): Trips
    {
        return DB::transaction(function () use ($trip, $actor, $reason) {
            $trip = Trips::query()->lockForUpdate()->findOrFail($trip->id);
            $previousStatus = $trip->status;

            if (! in_array($trip->status, array_merge(TripWorkflow::activeStatuses(), [TripWorkflow::STATUS_DRIVER_ASSIGNED]), true)) {
                throw ValidationException::withMessages([
                    'trip' => 'Trip can no longer be cancelled.',
                ]);
            }

            $trip->update([
                'status' => TripWorkflow::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            TripRequest::where('trip_id', $trip->id)
                ->where('status', 'pending')
                ->get()
                ->each(function (TripRequest $pending) use ($trip) {
                    $pending->update([
                        'status' => 'cancelled',
                        'responded_at' => now(),
                    ]);

                    $this->safeBroadcast(new TripRequestCancelled($pending->driver_id, [
                        'trip_id' => $trip->id,
                        'request_id' => $pending->id,
                    ]));
                });

            if ($trip->driver_id) {
                User::where('id', $trip->driver_id)->update([
                    'current_trip_id' => null,
                    'is_available' => true,
                ]);
            }

            $this->broadcastTripStatus($trip, $previousStatus, strtolower($actor->user_type));
            $this->safeBroadcast(new TripCancelled($trip->id, $trip->user_id, $trip->driver_id, $trip->company_id, [
                'trip_id' => $trip->id,
                'billing' => $trip->billing,
                'payment_mode' => $trip->payment_mode,
                'payment_status' => $trip->payment_status,
                'reason' => $reason,
                'cancelled_by' => strtolower($actor->user_type),
                'timestamp' => now()->toIso8601String(),
            ]));

            return $trip->fresh();
        });
    }

    public function transitionTrip(Trips $trip, string $targetStatus, string $changedBy): Trips
    {
        return DB::transaction(function () use ($trip, $targetStatus, $changedBy) {
            if ($trip->isCorporate()) {
                \Modules\SaaSDispatch\Entities\CompanyCorporateAccount::query()->lockForUpdate()->findOrFail($trip->corporate_account_id);
            }
            $trip = Trips::query()->lockForUpdate()->findOrFail($trip->id);
            $previousStatus = $trip->status;
            if ($trip->isCorporate() && $targetStatus === TripWorkflow::STATUS_COMPLETED && $trip->completed_at
                && in_array(strtolower((string) $trip->getRawOriginal('status')), ['payment', 'rating', 'completed'], true)) {
                return app(\App\Http\Helper\InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
            }

            TripWorkflow::assertTransition($previousStatus, $targetStatus);

            $attributes = ['status' => $targetStatus];

            if ($targetStatus === TripWorkflow::STATUS_STARTED) {
                $attributes['started_at'] = now();
                $attributes['begin_trip'] = $trip->begin_trip ?: now();
            }

            if ($targetStatus === TripWorkflow::STATUS_COMPLETED) {
                $attributes['completed_at'] = now('UTC');
                $attributes['end_trip'] = $trip->end_trip ?: now();
                $attributes['status'] = TripWorkflow::STATUS_LEGACY_PAYMENT;
            }

            $trip->update($attributes);

            if ($trip->isCorporate() && $targetStatus === TripWorkflow::STATUS_COMPLETED) {
                $trip = app(\App\Http\Helper\InvoiceHelper::class)->calculation(['trip_id' => $trip->id, 'save_to_trip_table' => 1]);
            }
            if ($targetStatus === TripWorkflow::STATUS_COMPLETED && $trip->driver_id) {
                User::where('id', $trip->driver_id)->update([
                    'current_trip_id' => null,
                    'is_available' => true,
                ]);
            }

            $this->broadcastTripStatus($trip, $previousStatus, $changedBy);

            if ($targetStatus === TripWorkflow::STATUS_COMPLETED) {
                $this->safeBroadcast(new TripCompleted($trip->id, $trip->user_id, $trip->driver_id, $trip->company_id, [
                    'trip_id' => $trip->id,
                    'status' => $trip->status,
                    'billing' => $trip->billing,
                    'payment_mode' => $trip->payment_mode,
                    'payment_status' => $trip->payment_status,
                    'total_fare' => $trip->total_fare,
                    'driver_payout' => $trip->driver_payout,
                    'currency_code' => $trip->currency_code,
                    'timestamp' => now()->toIso8601String(),
                ]));
            }

            return $trip->fresh();
        });
    }

    public function broadcastTripStatus(Trips $trip, ?string $previousStatus, string $changedBy): void
    {
        $this->safeBroadcast(new TripStatusUpdated($trip->id, $trip->user_id, $trip->driver_id, $trip->company_id, [
            'trip_id' => $trip->id,
            'status' => $trip->status,
            'billing' => $trip->billing,
            'payment_mode' => $trip->payment_mode,
            'payment_status' => $trip->payment_status,
            'total_fare' => $trip->total_fare,
            'driver_payout' => $trip->driver_payout,
            'currency_code' => $trip->currency_code,
            'previous_status' => $previousStatus,
            'changed_by' => $changedBy,
            'timestamp' => now()->toIso8601String(),
        ]), [
            'trip_id' => $trip->id,
            'event' => TripStatusUpdated::class,
        ]);
    }

    public function broadcastDriverLocation(Trips $trip, array $payload): void
    {
        $this->safeBroadcast(new DriverLocationUpdated($trip->id, $trip->user_id, $trip->company_id, $payload), [
            'trip_id' => $trip->id,
            'event' => DriverLocationUpdated::class,
        ]);
    }

    protected function safeBroadcast(object $event, array $context = []): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => $this->safeBroadcast($event, $context));
            return;
        }
        try {
            event($event);
        }
        catch (Throwable $exception) {
            Log::warning('Dispatch: realtime broadcast failed', array_merge($context, [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]));
        }
    }

    protected function baseTripAttributes(User $rider, array $payload): array
    {
        $currencyCode = $rider->currency_code ?: 'USD';
        $paymentMode = $payload['payment_mode'] ?? 'Cash';

        return [
            'user_id' => $rider->id,
            'company_id' => $payload['company_id'] ?? $rider->company_id,
            'operator_type' => $payload['operator_type'] ?? null,
            'operator_id' => $payload['operator_id'] ?? null,
            'pool_id' => 0,
            'pickup_latitude' => $payload['pickup_latitude'],
            'pickup_longitude' => $payload['pickup_longitude'],
            'drop_latitude' => $payload['drop_latitude'],
            'drop_longitude' => $payload['drop_longitude'],
            'pickup_location' => $payload['pickup_location'],
            'drop_location' => $payload['drop_location'],
            'car_id' => $payload['car_id'],
            'company_vehicle_type_id' => $payload['company_vehicle_type_id'] ?? null,
            'global_vehicle_type_id' => $payload['global_vehicle_type_id'] ?? $payload['car_id'],
            'global_vehicle_category_id' => $payload['global_vehicle_category_id'] ?? null,
            'request_id' => null,
            'driver_id' => null,
            'trip_path' => $payload['trip_path'] ?? '',
            'map_image' => '',
            'seats' => $payload['seats'] ?? 1,
            'service_category' => $payload['service_category'] ?? 'ride',
            'total_time' => 0,
            'total_km' => 0,
            'time_fare' => 0,
            'distance_fare' => 0,
            'base_fare' => 0,
            'additional_rider' => 0,
            'additional_rider_amount' => 0,
            'peak_fare' => 0,
            'peak_amount' => 0,
            'driver_peak_amount' => 0,
            'schedule_fare' => 0,
            'access_fee' => 0,
            'tips' => 0,
            'waiting_charge' => 0,
            'toll_fee' => 0,
            'wallet_amount' => 0,
            'promo_amount' => 0,
            'subtotal_fare' => 0,
            'total_fare' => 0,
            'driver_payout' => 0,
            'driver_or_company_commission' => 0,
            'owe_amount' => 0,
            'remaining_owe_amount' => 0,
            'applied_owe_amount' => 0,
            'to_trip_id' => '',
            'paykey' => '',
            'payment_mode' => $paymentMode,
            'payment_status' => 'Pending',
            'is_calculation' => '0',
            'currency_code' => $currencyCode,
            'fare_estimation' => [
                'fare_mode' => $payload['fare_mode'] ?? (site_settings('fare_mode') ?: 'Taximeter'),
                'estimated_fare' => $payload['estimated_fare'] ?? null,
                'estimated_distance' => $payload['estimated_distance'] ?? null,
                'estimated_duration' => $payload['estimated_duration'] ?? null,
            ],
            'status' => TripWorkflow::STATUS_SEARCHING_DRIVER,
            'otp' => '',
        ];
    }
}
