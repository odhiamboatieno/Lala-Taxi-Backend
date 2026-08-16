<?php

namespace App\Services\Dispatch;

use App\Events\DriverOnlineStatusUpdated;
use App\Models\DriverLocation;
use App\Models\DriverLocationHistory;
use App\Models\Trips;
use App\Models\User;
use App\Services\DriverOnlineSessionService;
use App\Support\LocationUpdateDecider;
use App\Support\TripWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverLocationService
{
    public function __construct(
        protected TripDispatchService $tripDispatchService,
        protected DriverOnlineSessionService $onlineSessionService
    )
    {
    }

    public function setOnlineState(User $driver, bool $online, bool $available = true, array $meta = []): User
    {
        $driver->forceFill([
            'is_online' => $online,
            'is_available' => $online ? $available : false,
            'last_seen_at' => now(),
            'online_started_at' => $online ? ($driver->online_started_at ?: now()) : null,
        ])->save();

        if ($online) {
            $this->onlineSessionService->ensureOpenSession($driver, $meta);
        } else {
            $this->onlineSessionService->endSession($driver, $meta);
        }

        $driver->refresh();

        if ($this->hasCoordinates($meta)) {
            $this->syncDriverLocationRows(
                $driver,
                (float) $meta['lat'],
                (float) $meta['lng'],
                isset($meta['heading']) ? (float) $meta['heading'] : null
            );
        }

        Log::info('Dispatch: driver online state updated', [
            'driver_id' => $driver->id,
            'is_online' => (bool) $driver->is_online,
            'is_available' => (bool) $driver->is_available,
            'company_id' => $driver->company_id,
            'current_trip_id' => $driver->current_trip_id,
            'last_seen_at' => optional($driver->last_seen_at)->toIso8601String(),
        ]);

        event(new DriverOnlineStatusUpdated($driver->company_id, $driver->id, [
            'driver_id' => $driver->id,
            'is_online' => (bool) $driver->is_online,
            'is_available' => (bool) $driver->is_available,
            'last_seen_at' => optional($driver->last_seen_at)->toIso8601String(),
            'online_started_at' => optional($driver->online_started_at)->toIso8601String(),
        ]));

        return $driver->fresh();
    }

    protected function hasCoordinates(array $payload): bool
    {
        return isset($payload['lat'], $payload['lng'])
            && $payload['lat'] !== ''
            && $payload['lng'] !== ''
            && is_numeric($payload['lat'])
            && is_numeric($payload['lng']);
    }

    public function update(User $driver, array $payload): User
    {
        return DB::transaction(function () use ($driver, $payload) {
            $driver = User::query()->lockForUpdate()->findOrFail($driver->id);

            $previousLat = $driver->current_lat ? (float) $driver->current_lat : null;
            $previousLng = $driver->current_lng ? (float) $driver->current_lng : null;
            $lat = (float) $payload['lat'];
            $lng = (float) $payload['lng'];
            $trip = null;

            if (! empty($payload['trip_id'])) {
                $trip = Trips::find($payload['trip_id']);
            } elseif ($driver->current_trip_id) {
                $trip = Trips::find($driver->current_trip_id);
            }

            $lastRecordedAt = DriverLocationHistory::where('driver_id', $driver->id)
                ->latest('recorded_at')
                ->value('recorded_at');

            $secondsSinceLastRecord = $lastRecordedAt
                ? now()->diffInSeconds($lastRecordedAt)
                : PHP_INT_MAX;

            $driver->forceFill([
                'current_lat' => $lat,
                'current_lng' => $lng,
                'heading' => $payload['heading'] ?? null,
                'speed' => $payload['speed'] ?? null,
                'last_seen_at' => now(),
                'is_online' => true,
                'online_started_at' => $driver->online_started_at ?: now(),
            ])->save();

            $this->onlineSessionService->ensureOpenSession($driver, [
                'lat' => $lat,
                'lng' => $lng,
            ]);

            $driver->refresh();

            Log::info('Dispatch: driver realtime location received', [
                'driver_id' => $driver->id,
                'lat' => $lat,
                'lng' => $lng,
                'trip_id' => $trip?->id,
                'is_available' => (bool) $driver->is_available,
                'company_id' => $driver->company_id,
            ]);

            $this->syncDriverLocationRows($driver, $lat, $lng, $payload['heading'] ?? null);

            if (LocationUpdateDecider::shouldPersistHistory(
                $previousLat,
                $previousLng,
                $lat,
                $lng,
                $secondsSinceLastRecord,
                (bool) $trip
            )) {
                DriverLocationHistory::create([
                    'driver_id' => $driver->id,
                    'trip_id' => $trip?->id,
                    'lat' => $lat,
                    'lng' => $lng,
                    'heading' => $payload['heading'] ?? null,
                    'speed' => $payload['speed'] ?? null,
                    'accuracy' => $payload['accuracy'] ?? null,
                    'recorded_at' => now(),
                ]);
            }

            if ($trip && $trip->status === TripWorkflow::STATUS_DRIVER_ASSIGNED) {
                $trip = $this->tripDispatchService->transitionTrip($trip, TripWorkflow::STATUS_DRIVER_ARRIVING, 'driver');
            }

            if ($trip && $trip->status === TripWorkflow::STATUS_STARTED) {
                $this->incrementTripDistance($trip, $previousLat, $previousLng, $lat, $lng);
            }

            if ($trip && LocationUpdateDecider::shouldBroadcast($previousLat, $previousLng, $lat, $lng)) {
                $this->tripDispatchService->broadcastDriverLocation($trip, [
                    'driver_id' => $driver->id,
                    'trip_id' => $trip->id,
                    'lat' => $lat,
                    'lng' => $lng,
                    'heading' => $payload['heading'] ?? null,
                    'speed' => $payload['speed'] ?? null,
                    'recorded_at' => now()->toIso8601String(),
                ]);
            }

            event(new DriverOnlineStatusUpdated($driver->company_id, $driver->id, [
                'driver_id' => $driver->id,
                'is_online' => (bool) $driver->is_online,
                'is_available' => (bool) $driver->is_available,
                'last_seen_at' => optional($driver->last_seen_at)->toIso8601String(),
                'online_started_at' => optional($driver->online_started_at)->toIso8601String(),
                'current_lat' => $lat,
                'current_lng' => $lng,
                'heading' => $payload['heading'] ?? null,
                'speed' => $payload['speed'] ?? null,
            ]));

            return $driver->fresh();
        });
    }

    protected function incrementTripDistance(Trips $trip, ?float $previousLat, ?float $previousLng, float $lat, float $lng): void
    {
        if ($previousLat === null || $previousLng === null) {
            return;
        }

        $incrementKm = $this->haversineKilometers($previousLat, $previousLng, $lat, $lng);
        if ($incrementKm <= 0 || $incrementKm > 5) {
            return;
        }

        $trip->total_km = number_format(((float) $trip->total_km) + $incrementKm, 5, '.', '');
        $trip->save();
    }

    protected function haversineKilometers(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    protected function syncDriverLocationRows(User $driver, float $lat, float $lng, ?float $heading = null): void
    {
        $status = $driver->current_trip_id ? 'Trip' : ($driver->is_online ? 'Online' : 'Offline');
        $vehicleIds = [];

        if ($driver->vehicle && $driver->vehicle->vehicle_id) {
            $vehicleIds = array_filter(explode(',', $driver->vehicle->vehicle_id));
        }

        if ($vehicleIds === [] && $driver->vehicle_type_id) {
            $vehicleIds = [$driver->vehicle_type_id];
        }

        if ($vehicleIds === []) {
            DriverLocation::query()
                ->where('user_id', $driver->id)
                ->update([
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'heading' => $heading,
                    'status' => $status,
                ]);

            Log::warning('Dispatch: driver location rows updated without vehicle ids', [
                'driver_id' => $driver->id,
                'status' => $status,
                'lat' => $lat,
                'lng' => $lng,
            ]);

            return;
        }

        foreach ($vehicleIds as $vehicleId) {
            DriverLocation::updateOrCreate(
                ['user_id' => $driver->id, 'car_id' => $vehicleId],
                [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'heading' => $heading,
                    'status' => $status,
                    'pool_trip_id' => null,
                ]
            );
        }

        Log::info('Dispatch: driver location rows synced', [
            'driver_id' => $driver->id,
            'vehicle_ids' => array_values($vehicleIds),
            'status' => $status,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }
}
