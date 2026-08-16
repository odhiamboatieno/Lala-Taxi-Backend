<?php

namespace App\Services\Dispatch;

use App\Models\DriverLocation;
use App\Models\Trips;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class NearbyDriverService
{
    protected $driverPoolResolver = null;
    protected $operatorResolver = null;

    public function __construct(
        $driverPoolResolver = null,
        $operatorResolver = null,
    ) {
        $this->driverPoolResolver = $driverPoolResolver;
        $this->operatorResolver = $operatorResolver;
    }

    protected function resolveDriverPoolResolver()
    {
        if ($this->driverPoolResolver === null) {
            $fqcn = 'Modules\\SaaSDispatch\\Services\\DriverPoolResolver';
            if (class_exists($fqcn)) {
                $this->driverPoolResolver = app($fqcn);
            } else {
                $this->driverPoolResolver = new class {
                    public function applyScope($query, $context)
                    {
                        return $query;
                    }
                };
            }
        }
        return $this->driverPoolResolver;
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

    public function findNearbyDrivers(Trips $trip, array $excludeDriverIds = [], ?int $limit = null, array $preferredDriverIds = []): Collection
    {
        $limit ??= config('dispatch.initial_batch_size', 3);
        $radii = config('dispatch.search_radii_km', [3, 5, 8, 12]);
        $staleCutoff = now()->subSeconds(config('dispatch.driver_stale_after_seconds', 90));
        $operatorContext = $this->resolveOperatorResolver()->resolveForTrip($trip);

        Log::info('Dispatch: starting nearby driver search', [
            'trip_id' => $trip->id,
            'car_id' => $trip->car_id,
            'company_id' => $trip->company_id,
            'operator_type' => $operatorContext['operator_type'],
            'operator_id' => $operatorContext['operator_id'],
            'exclude_driver_ids' => $excludeDriverIds,
            'stale_cutoff' => $staleCutoff->toIso8601String(),
            'search_radii_km' => $radii,
            'limit' => $limit,
            'preferred_driver_ids' => $preferredDriverIds,
            'driver_location_rows_for_car' => DriverLocation::where('car_id', $trip->car_id)->count(),
            'company_matching_driver_location_rows' => DriverLocation::query()
                ->join('users', 'users.id', '=', 'driver_location.user_id')
                ->where('driver_location.car_id', $trip->car_id)
                ->tap(fn ($query) => $this->resolveDriverPoolResolver()->applyScope($query, $operatorContext))
                ->count(),
            'eligible_user_rows_before_radius' => DriverLocation::query()
                ->join('users', 'users.id', '=', 'driver_location.user_id')
                ->where('users.user_type', 'Driver')
                ->where('users.status', 'Active')
                ->whereNull('users.current_trip_id')
                ->where('driver_location.car_id', $trip->car_id)
                ->tap(fn ($query) => $this->applyRealtimeEligibility($query, $staleCutoff))
                ->tap(fn ($query) => $this->resolveDriverPoolResolver()->applyScope($query, $operatorContext))
                ->when($excludeDriverIds !== [], fn ($q) => $q->whereNotIn('users.id', $excludeDriverIds))
                ->count(),
            'candidate_state_snapshot' => DriverLocation::query()
                ->select(
                    'driver_location.user_id',
                    'driver_location.car_id',
                    'driver_location.status as driver_location_status',
                    'driver_location.latitude',
                    'driver_location.longitude',
                    'users.company_id',
                    'users.status',
                    'users.is_online',
                    'users.is_available',
                    'users.current_trip_id',
                    'users.last_seen_at'
                )
                ->join('users', 'users.id', '=', 'driver_location.user_id')
                ->where('users.user_type', 'Driver')
                ->where('driver_location.car_id', $trip->car_id)
                ->tap(fn ($query) => $this->resolveDriverPoolResolver()->applyScope($query, $operatorContext))
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'driver_id' => $row->user_id,
                    'car_id' => $row->car_id,
                    'driver_location_status' => $row->driver_location_status,
                    'latitude' => $row->latitude,
                    'longitude' => $row->longitude,
                    'company_id' => $row->company_id,
                    'status' => $row->status,
                    'is_online' => (bool) $row->is_online,
                    'is_available' => (bool) $row->is_available,
                    'current_trip_id' => $row->current_trip_id,
                    'last_seen_at' => $row->last_seen_at,
                ])
                ->values()
                ->all(),
        ]);

        foreach ($radii as $radius) {
            $query = DriverLocation::query()
                ->select(
                    'driver_location.*',
                    'users.id as driver_user_id',
                    DB::raw(
                        '(6371 * acos(
                            cos(radians('.(float) $trip->pickup_latitude.'))
                            * cos(radians(CAST(driver_location.latitude AS DECIMAL(10,7))))
                            * cos(radians(CAST(driver_location.longitude AS DECIMAL(10,7))) - radians('.(float) $trip->pickup_longitude.'))
                            + sin(radians('.(float) $trip->pickup_latitude.'))
                            * sin(radians(CAST(driver_location.latitude AS DECIMAL(10,7))))
                        )) as distance_km'
                    )
                )
                ->join('users', 'users.id', '=', 'driver_location.user_id')
                ->where('users.user_type', 'Driver')
                ->where('users.status', 'Active')
                ->whereNull('users.current_trip_id')
                ->where('driver_location.car_id', $trip->car_id)
                ->tap(fn ($query) => $this->applyRealtimeEligibility($query, $staleCutoff))
                ->tap(fn ($query) => $this->resolveDriverPoolResolver()->applyScope($query, $operatorContext))
                ->when($excludeDriverIds !== [], fn ($q) => $q->whereNotIn('users.id', $excludeDriverIds))
                ->having('distance_km', '<=', $radius)
                ->when($preferredDriverIds !== [], function ($q) use ($preferredDriverIds) {
                    $ids = implode(',', array_map('intval', $preferredDriverIds));

                    return $q->orderByRaw("CASE WHEN users.id IN ($ids) THEN 0 ELSE 1 END");
                })
                ->orderBy('distance_km')
                ->limit($limit)
                ->with('users.profile_picture', 'users.vehicle');

            $drivers = $query->get();

            Log::info('Dispatch: radius search result', [
                'trip_id' => $trip->id,
                'radius_km' => $radius,
                'match_count' => $drivers->count(),
                'matched_driver_ids' => $drivers->pluck('user_id')->values()->all(),
                'matched_distances_km' => $drivers->map(fn ($driver) => [
                    'driver_id' => $driver->user_id,
                    'distance_km' => round((float) $driver->distance_km, 3),
                ])->values()->all(),
            ]);

            if ($drivers->isNotEmpty()) {
                return $drivers;
            }
        }

        return collect();
    }

    public function getNearbyDrivers(float $lat, float $lng, int $radiusKm = 5): Collection
    {
        $staleCutoff = now()->subSeconds(config('dispatch.driver_stale_after_seconds', 90));

        $drivers = DriverLocation::query()
            ->select(
                'driver_location.*',
                'users.id as driver_user_id',
                DB::raw(
                    '(6371 * acos(
                        cos(radians('.$lat.'))
                        * cos(radians(CAST(driver_location.latitude AS DECIMAL(10,7))))
                        * cos(radians(CAST(driver_location.longitude AS DECIMAL(10,7))) - radians('.$lng.'))
                        + sin(radians('.$lat.'))
                        * sin(radians(CAST(driver_location.latitude AS DECIMAL(10,7))))
                    )) as distance_km'
                )
            )
            ->join('users', 'users.id', '=', 'driver_location.user_id')
            ->where('users.user_type', 'Driver')
            ->where('users.status', 'Active')
            ->tap(fn ($query) => $this->applyRealtimeEligibility($query, $staleCutoff))
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->get();

        return $drivers;
    }

    protected function applyRealtimeEligibility($query, $staleCutoff)
    {
        return $query
            ->where(function ($builder) {
                $builder->where(function ($inner) {
                    $inner->where('users.is_online', true)
                        ->where('users.is_available', true);
                })->orWhere('driver_location.status', 'Online');
            })
            ->where(function ($builder) use ($staleCutoff) {
                $builder->where('users.last_seen_at', '>=', $staleCutoff)
                    ->orWhere('driver_location.updated_at', '>=', $staleCutoff)
                    ->orWhere('driver_location.created_at', '>=', $staleCutoff);
            });
    }
}
