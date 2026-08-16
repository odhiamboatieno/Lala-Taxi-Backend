<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\DriverScoreEvent;
use App\Models\Location;
use App\Models\ParcelRequest;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\RiderLocation;
use App\Models\Trips;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class MapController extends Controller
{
    public function index(Request $request)
    {
        return $this->drivers($request);
    }

    public function drivers(Request $request)
    {
        return $this->renderFleetView($request, 'drivers');
    }

    public function onTrip(Request $request)
    {
        return $this->renderFleetView($request, 'on_trip');
    }

    public function idle(Request $request)
    {
        return $this->renderFleetView($request, 'idle');
    }

    public function customers(Request $request)
    {
        return $this->renderFleetView($request, 'customers');
    }

    public function alerts(Request $request)
    {
        return $this->renderFleetView($request, 'alerts');
    }

    public function mapdata(Request $request): JsonResponse
    {
        return $this->liveData($request, 'drivers');
    }

    public function liveData(Request $request, string $mode = 'drivers'): JsonResponse
    {
        return response()->json($this->buildFleetPayload($request, $mode));
    }

    public function resolveAlert(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $alert = DriverScoreEvent::query()
            ->with('driver')
            ->findOrFail($id);

        if ($this->companyId() && (int) optional($alert->driver)->company_id !== $this->companyId()) {
            abort(403);
        }

        $metadata = $alert->metadata ?? [];
        $metadata['fleet_map_resolved'] = true;
        $metadata['fleet_map_resolved_at'] = now()->toIso8601String();
        $metadata['fleet_map_resolved_user_type'] = LOGIN_USER_TYPE;
        $metadata['fleet_map_resolved_user_id'] = LOGIN_USER_TYPE === 'company'
            ? Auth::guard('company')->id()
            : Auth::guard('admin')->id();

        $alert->metadata = $metadata;
        if (!$alert->admin_confirmed_at) {
            $alert->admin_confirmed_at = now();
        }
        if (LOGIN_USER_TYPE === 'admin' && !$alert->admin_confirmed_by) {
            $alert->admin_confirmed_by = Auth::guard('admin')->id();
        }
        $alert->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Safety alert marked as resolved.',
            ]);
        }

        flashMessage('success', 'Safety alert marked as resolved.');

        return redirect()->back();
    }

    public function heat_map(Request $request)
    {
        return $this->renderHeatMapView($request, 'overview');
    }

    public function heat_map_compare(Request $request)
    {
        return $this->renderHeatMapView($request, 'compare');
    }

    public function heat_map_data(Request $request): JsonResponse
    {
        return response()->json($this->buildHeatMapPayload($request, 'overview'));
    }

    public function heat_map_compare_data(Request $request): JsonResponse
    {
        return response()->json($this->buildHeatMapPayload($request, 'compare'));
    }

    protected function renderFleetView(Request $request, string $mode)
    {
        $payload = $this->buildFleetPayload($request, $mode);

        return view('admin.map', [
            'fleetPayload' => $payload,
            'mode' => $mode,
        ]);
    }

    protected function renderHeatMapView(Request $request, string $mode)
    {
        $payload = $this->buildHeatMapPayload($request, $mode);

        return view('admin.heat_map', [
            'heatMapPayload' => $payload,
            'mode' => $mode,
        ]);
    }

    protected function buildHeatMapPayload(Request $request, string $mode): array
    {
        $companyId = $this->companyId();
        $selectedLocationId = (int) $request->input('location_id', 0);
        $selectedZoneName = trim((string) $request->input('search', ''));
        $rangePreset = $request->input('range', 'all');

        [$fromDate, $toDate] = $this->heatMapDateRange($request, $rangePreset);

        $locations = $this->mapZones($companyId);

        $selectedLocation = $selectedLocationId ? $locations->firstWhere('id', $selectedLocationId) : null;

        $zoneStats = $this->buildHeatMapZoneStats($companyId, $fromDate, $toDate, $selectedZoneName)->values();
        $zoneStatsWithoutAggregate = $zoneStats->filter(fn (array $zone) => (int) $zone['id'] > 0)->values();

        $overviewPoints = $this->buildHeatMapPoints($companyId, $fromDate, $toDate, $selectedLocation);
        $compareYears = $this->buildHeatMapCompareYears($companyId, $selectedLocation, $selectedZoneName);

        return [
            'mode' => $mode,
            'selected_location_id' => $selectedLocationId,
            'selected_zone_name' => $selectedZoneName,
            'range' => $rangePreset,
            'from_date' => optional($fromDate)->format('Y-m-d'),
            'to_date' => optional($toDate)->format('Y-m-d'),
            'range_label' => $this->heatMapRangeLabel($fromDate, $toDate, $rangePreset),
            'tabs' => [
                'overview' => [
                    'label' => 'Overview',
                    'url' => url(LOGIN_USER_TYPE.'/heat-map').$this->heatMapQueryString($selectedLocationId, $selectedZoneName, $fromDate, $toDate, $rangePreset),
                ],
                'compare' => [
                    'label' => 'Compare',
                    'url' => url(LOGIN_USER_TYPE.'/heat-map/compare').$this->heatMapQueryString($selectedLocationId, $selectedZoneName, $fromDate, $toDate, $rangePreset),
                ],
            ],
            'locations' => $locations->all(),
            'zone_stats' => $zoneStats->all(),
            'selected_zone' => $selectedLocation,
            'overview' => [
                'points' => $overviewPoints,
                'total_trips' => array_sum(array_column($zoneStatsWithoutAggregate->all(), 'ride_count')),
                'total_parcels' => array_sum(array_column($zoneStatsWithoutAggregate->all(), 'parcel_count')),
                'map_center' => $this->heatMapCenter($selectedLocation, $overviewPoints),
            ],
            'compare' => [
                'years' => $compareYears,
                'chart' => [
                    'categories' => array_column($compareYears, 'year'),
                    'rides' => array_column($compareYears, 'ride_count'),
                    'parcels' => array_column($compareYears, 'parcel_count'),
                ],
            ],
            'polygons' => $selectedLocation
                ? [$selectedLocation['coordinates']]
                : $locations->pluck('coordinates')->filter()->values()->all(),
            'updated_at' => now()->format('d M Y H:i:s'),
        ];
    }

    protected function buildFleetPayload(Request $request, string $mode): array
    {
        $companyId = $this->companyId();
        $selectedLocationId = (int) $request->input('location_id', 0);
        $selectedItemId = (int) $request->input('selected', 0);

        $locations = $this->mapZones($companyId);

        $allDrivers = $this->filterByLocation(
            $this->buildDriverItems($companyId),
            $locations,
            $selectedLocationId
        )->values();

        $onTripDrivers = $allDrivers->where('status_key', 'on_trip')->values();
        $idleDrivers = $allDrivers->where('status_key', 'idle')->values();

        $customers = $this->filterByLocation(
            $this->buildCustomerItems($companyId),
            $locations,
            $selectedLocationId
        )->values();

        $alerts = $this->filterByLocation(
            $this->buildAlertItems($companyId),
            $locations,
            $selectedLocationId
        )->values();

        $driversById = $allDrivers->keyBy('id');
        $customersById = $customers->keyBy('id');

        $alerts = $alerts->map(function (array $alert) use ($driversById, $customersById) {
            if (($alert['driver_id'] ?? 0) && $driversById->has($alert['driver_id'])) {
                $alert['driver'] = $driversById->get($alert['driver_id']);
            }

            if (($alert['rider_id'] ?? 0) && $customersById->has($alert['rider_id'])) {
                $alert['customer'] = $customersById->get($alert['rider_id']);
            }

            return $alert;
        })->values();

        $customerAlerts = $alerts->groupBy('rider_id');
        $driverAlerts = $alerts->groupBy('driver_id');

        $customers = $customers->map(function (array $customer) use ($customerAlerts) {
            $customer['related_alerts'] = ($customerAlerts->get($customer['id']) ?? collect())
                ->take(3)
                ->values()
                ->all();

            return $customer;
        })->values();

        $allDrivers = $allDrivers->map(function (array $driver) use ($driverAlerts) {
            $driver['related_alerts'] = ($driverAlerts->get($driver['id']) ?? collect())
                ->take(3)
                ->values()
                ->all();

            return $driver;
        })->values();

        $datasets = [
            'drivers' => $allDrivers,
            'on_trip' => $onTripDrivers,
            'idle' => $idleDrivers,
            'customers' => $customers,
            'alerts' => $alerts,
        ];

        $selectedItem = $this->resolveSelectedItem(
            $mode,
            $datasets[$mode] ?? collect(),
            $selectedItemId
        );

        return [
            'mode' => $mode,
            'title' => 'User Live View',
            'subtitle' => 'Monitor your users from here',
            'selected_location_id' => $selectedLocationId,
            'selected_item_id' => $selectedItem['id'] ?? null,
            'tabs' => [
                'drivers' => [
                    'label' => 'All Drivers',
                    'url' => url(LOGIN_USER_TYPE.'/map'.($selectedLocationId ? '?location_id='.$selectedLocationId : '')),
                    'count' => $allDrivers->count(),
                ],
                'on_trip' => [
                    'label' => 'On-Trip',
                    'url' => url(LOGIN_USER_TYPE.'/map/on-trip'.($selectedLocationId ? '?location_id='.$selectedLocationId : '')),
                    'count' => $onTripDrivers->count(),
                ],
                'idle' => [
                    'label' => 'Idle',
                    'url' => url(LOGIN_USER_TYPE.'/map/idle'.($selectedLocationId ? '?location_id='.$selectedLocationId : '')),
                    'count' => $idleDrivers->count(),
                ],
                'customers' => [
                    'label' => 'Customers',
                    'url' => url(LOGIN_USER_TYPE.'/map/customers'.($selectedLocationId ? '?location_id='.$selectedLocationId : '')),
                    'count' => $customers->count(),
                ],
            ],
            'alerts_url' => url(LOGIN_USER_TYPE.'/map/alerts'.($selectedLocationId ? '?location_id='.$selectedLocationId : '')),
            'alerts_count' => $alerts->count(),
            'locations' => $locations->all(),
            'items' => ($datasets[$mode] ?? collect())->values()->all(),
            'selected_item' => $selectedItem,
            'polygons' => $locations->pluck('coordinates')->filter()->values()->all(),
            'alert_items' => $alerts->all(),
            'updated_at' => now()->format('d M Y H:i:s'),
            'live_data_url' => url(LOGIN_USER_TYPE.'/map/live-data/'.$mode).($selectedLocationId ? '?location_id='.$selectedLocationId : ''),
            'resolve_alert_base_url' => url(LOGIN_USER_TYPE.'/map/alerts'),
        ];
    }

    protected function buildDriverItems(?int $companyId): Collection
    {
        $ratings = Rating::query()
            ->selectRaw('driver_id, ROUND(AVG(rider_rating), 1) as avg_rating')
            ->whereNotNull('driver_id')
            ->groupBy('driver_id')
            ->pluck('avg_rating', 'driver_id');

        return User::query()
            ->with([
                'profile_picture',
                'company',
                'driver_location',
                'driver_locations',
                'vehicle.car_type',
                'vehicle.make',
                'vehicle.model',
                'vehicles.car_type',
            ])
            ->where('user_type', 'Driver')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (User $driver) use ($ratings) {
                $vehicle = $driver->vehicle ?: $driver->vehicles->first();
                $location = $this->bestDriverLocation($driver);
                $tripSummary = $this->driverTripSummary($driver->id);

                return [
                    'id' => (int) $driver->id,
                    'type' => 'driver',
                    'name' => trim($driver->first_name.' '.$driver->last_name),
                    'phone' => '+'.$driver->country_code.$driver->mobile_number,
                    'hidden_phone' => '+'.$driver->country_code.$driver->hidden_mobile_number,
                    'email' => $driver->email,
                    'image' => optional($driver->profile_picture)->src ?: url('images/user.jpeg'),
                    'lat' => optional($location)->latitude !== null && optional($location)->latitude !== '' ? (float) optional($location)->latitude : null,
                    'lng' => optional($location)->longitude !== null && optional($location)->longitude !== '' ? (float) optional($location)->longitude : null,
                    'status' => optional($location)->status ?: $driver->status,
                    'status_key' => $this->driverStatusKey(optional($location)->status, $driver->status),
                    'company_name' => $driver->company_name ?: 'Admin Fleet',
                    'joined_date' => optional($driver->created_at)->format('d M Y'),
                    'rating' => (float) ($ratings[$driver->id] ?? 0),
                    'level_name' => $this->userLevelName($driver),
                    'vehicle_category' => optional(optional($vehicle)->car_type)->car_name ?: '-',
                    'vehicle_number' => optional($vehicle)->vehicle_number ?: '-',
                    'vehicle_brand' => optional(optional($vehicle)->make)->make_vehicle_name ?: (optional($vehicle)->vehicle_name ?: '-'),
                    'vehicle_model' => optional(optional($vehicle)->model)->model_name ?: (optional(optional($vehicle)->car_type)->car_name ?: '-'),
                    'service_labels' => $driver->vehicles->pluck('car_type.car_name')->filter()->unique()->values()->all(),
                    'trip_count' => $tripSummary['trip_count'],
                    'completed_trip_count' => $tripSummary['completed_trip_count'],
                    'active_trip' => $tripSummary['active_trip'],
                ];
            })
            ->values();
    }

    protected function bestDriverLocation(User $driver): ?DriverLocation
    {
        $locations = $driver->relationLoaded('driver_locations')
            ? $driver->driver_locations
            : $driver->driver_locations()->get();

        $validLocations = $locations->filter(function (DriverLocation $location) {
            return $location->latitude !== null
                && $location->latitude !== ''
                && $location->longitude !== null
                && $location->longitude !== '';
        });

        if ($validLocations->isEmpty()) {
            return $driver->driver_location;
        }

        return $validLocations
            ->sortByDesc(fn (DriverLocation $location) => optional($location->updated_at)->timestamp ?: 0)
            ->first();
    }

    protected function buildCustomerItems(?int $companyId): Collection
    {
        $tripCounts = Trips::query()
            ->selectRaw('user_id, COUNT(*) as total_trips')
            ->when($companyId, function ($query) use ($companyId) {
                $query->whereHas('driver', function ($driverQuery) use ($companyId) {
                    $driverQuery->where('company_id', $companyId);
                });
            })
            ->groupBy('user_id')
            ->pluck('total_trips', 'user_id');

        return User::query()
            ->with([
                'profile_picture',
                'rider_location',
            ])
            ->where('user_type', 'Rider')
            ->when($companyId, function ($query) use ($companyId) {
                $query->whereIn('id', function ($subQuery) use ($companyId) {
                    $subQuery->from('trips')
                        ->join('users as driver_users', 'driver_users.id', '=', 'trips.driver_id')
                        ->where('driver_users.company_id', $companyId)
                        ->select('trips.user_id')
                        ->distinct();
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (User $customer) use ($companyId, $tripCounts) {
                $latestTrip = Trips::query()
                    ->where('user_id', $customer->id)
                    ->when($companyId, function ($query) use ($companyId) {
                        $query->whereHas('driver', function ($driverQuery) use ($companyId) {
                            $driverQuery->where('company_id', $companyId);
                        });
                    })
                    ->latest('id')
                    ->first();

                return [
                    'id' => (int) $customer->id,
                    'type' => 'customer',
                    'name' => trim($customer->first_name.' '.$customer->last_name),
                    'phone' => '+'.$customer->country_code.$customer->mobile_number,
                    'hidden_phone' => '+'.$customer->country_code.$customer->hidden_mobile_number,
                    'email' => $customer->email,
                    'image' => optional($customer->profile_picture)->src ?: url('images/user.jpeg'),
                    'lat' => optional($customer->rider_location)->latitude !== null ? (float) optional($customer->rider_location)->latitude : null,
                    'lng' => optional($customer->rider_location)->longitude !== null ? (float) optional($customer->rider_location)->longitude : null,
                    'joined_date' => optional($customer->created_at)->format('d M Y'),
                    'trip_count' => (int) ($tripCounts[$customer->id] ?? 0),
                    'latest_trip' => $latestTrip ? [
                        'id' => (int) $latestTrip->id,
                        'status' => $latestTrip->status,
                        'pickup' => $latestTrip->pickup_location,
                        'drop' => $latestTrip->drop_location,
                        'driver_name' => optional($latestTrip->driver)->full_name,
                        'driver_phone' => optional($latestTrip->driver)->phone_number,
                        'trip_type' => optional($latestTrip->car_type)->car_name,
                    ] : null,
                ];
            })
            ->values();
    }

    protected function buildAlertItems(?int $companyId): Collection
    {
        return DriverScoreEvent::query()
            ->with([
                'driver.profile_picture',
                'driver.driver_location',
                'trip.users.profile_picture',
                'trip.driver.profile_picture',
                'trip.driver.driver_location',
                'trip.car_type',
            ])
            ->where(function ($query) {
                $query->whereIn('event_type', ['safety_incident_confirmed', 'fraud_flag_confirmed'])
                    ->orWhere('title', 'like', '%safety%')
                    ->orWhere('description', 'like', '%brak%')
                    ->orWhere('description', 'like', '%risk%')
                    ->orWhere('description', 'like', '%alert%');
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->whereHas('driver', function ($driverQuery) use ($companyId) {
                    $driverQuery->where('company_id', $companyId);
                });
            })
            ->latest('event_date')
            ->latest('id')
            ->get()
            ->filter(function (DriverScoreEvent $event) {
                return !((bool) data_get($event->metadata, 'fleet_map_resolved', false));
            })
            ->map(function (DriverScoreEvent $event) {
                $metadata = $event->metadata ?? [];
                $trip = $event->trip;
                $driver = $event->driver ?: optional($trip)->driver;
                $rider = optional($trip)->users;

                $lat = data_get($metadata, 'latitude')
                    ?? data_get($metadata, 'lat')
                    ?? optional(optional($driver)->driver_location)->latitude
                    ?? optional($trip)->pickup_latitude;

                $lng = data_get($metadata, 'longitude')
                    ?? data_get($metadata, 'lng')
                    ?? optional(optional($driver)->driver_location)->longitude
                    ?? optional($trip)->pickup_longitude;

                return [
                    'id' => (int) $event->id,
                    'type' => 'alert',
                    'name' => $event->title,
                    'title' => $event->title,
                    'description' => $event->description ?: 'Potential safety issue detected.',
                    'event_type' => $event->event_type,
                    'image' => optional(optional($rider ?: $driver)->profile_picture)->src ?: url('images/user.jpeg'),
                    'lat' => $lat !== null ? (float) $lat : null,
                    'lng' => $lng !== null ? (float) $lng : null,
                    'driver_id' => (int) ($driver->id ?? 0),
                    'rider_id' => (int) ($rider->id ?? 0),
                    'trip_id' => (int) ($trip->id ?? 0),
                    'trip_ref' => $trip ? '#'.$trip->id : null,
                    'driver_name' => $driver ? $driver->full_name : null,
                    'driver_phone' => $driver ? $driver->phone_number : null,
                    'customer_name' => $rider ? $rider->full_name : null,
                    'status' => 'Open',
                    'created_at' => optional($event->event_date ?: $event->created_at)->format('d M Y h:i A'),
                    'pickup' => $trip->pickup_location ?? data_get($metadata, 'pickup_location'),
                    'drop' => $trip->drop_location ?? data_get($metadata, 'drop_location'),
                    'trip_type' => optional($trip?->car_type)->car_name,
                    'resolve_url' => url(LOGIN_USER_TYPE.'/map/alerts/'.$event->id.'/resolve'),
                ];
            })
            ->filter(function (array $alert) {
                return $alert['lat'] !== null && $alert['lng'] !== null;
            })
            ->values();
    }

    protected function filterByLocation(Collection $items, Collection $locations, int $selectedLocationId): Collection
    {
        if (!$selectedLocationId) {
            return $items;
        }

        $location = $locations->firstWhere('id', $selectedLocationId);
        if (!$location) {
            return $items;
        }

        $polygon = data_get($location, 'coordinates.0', []);
        if (!$polygon) {
            return $items;
        }

        return $items->filter(function (array $item) use ($polygon) {
            if ($item['lat'] === null || $item['lng'] === null) {
                return false;
            }

            return $this->pointInPolygon((float) $item['lat'], (float) $item['lng'], $polygon);
        })->values();
    }

    protected function resolveSelectedItem(string $mode, Collection $items, int $selectedItemId): ?array
    {
        if (!$selectedItemId) {
            return null;
        }

        $selected = $items->firstWhere('id', $selectedItemId);
        if ($selected) {
            return $selected;
        }

        return null;
    }

    protected function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $pointCount = count($polygon);

        if ($pointCount < 3) {
            return true;
        }

        for ($i = 0, $j = $pointCount - 1; $i < $pointCount; $j = $i++) {
            $xi = (float) ($polygon[$i]['lat'] ?? 0);
            $yi = (float) ($polygon[$i]['lng'] ?? 0);
            $xj = (float) ($polygon[$j]['lat'] ?? 0);
            $yj = (float) ($polygon[$j]['lng'] ?? 0);

            $intersects = (($yi > $lng) !== ($yj > $lng))
                && ($lat < (($xj - $xi) * ($lng - $yi) / (($yj - $yi) ?: 0.0000001) + $xi));

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    protected function driverStatusKey(?string $driverLocationStatus, ?string $userStatus): string
    {
        $status = strtolower((string) $driverLocationStatus);

        if (in_array($status, ['trip', 'pool trip'], true)) {
            return 'on_trip';
        }

        if (in_array($status, ['online', 'active'], true) && strtolower((string) $userStatus) === 'active') {
            return 'idle';
        }

        return 'offline';
    }

    protected function driverTripSummary(int $driverId): array
    {
        $tripCount = Trips::query()->where('driver_id', $driverId)->count();
        $completedTripCount = Trips::query()->where('driver_id', $driverId)->where('status', 'Completed')->count();
        $activeTrip = Trips::query()
            ->where('driver_id', $driverId)
            ->whereNotIn('status', ['Completed', 'Cancelled', 'Payment', 'Rating'])
            ->latest('id')
            ->first();

        return [
            'trip_count' => $tripCount,
            'completed_trip_count' => $completedTripCount,
            'active_trip' => $activeTrip ? [
                'id' => (int) $activeTrip->id,
                'status' => $activeTrip->status,
                'pickup' => $activeTrip->pickup_location,
                'drop' => $activeTrip->drop_location,
            ] : null,
        ];
    }

    protected function userLevelName(User $user): ?string
    {
        if (!class_exists(\Modules\UserLevels\Entities\UserLevelAssignment::class)) {
            return null;
        }

        $assignment = \Modules\UserLevels\Entities\UserLevelAssignment::query()
            ->with('level')
            ->where('user_id', $user->id)
            ->when($this->companyId(), function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('company_id', $this->companyId())
                        ->orWhereNull('company_id');
                });
            }, function ($query) {
                $query->whereNull('company_id');
            })
            ->where('level_type', strtolower((string) $user->user_type))
            ->latest('assigned_at')
            ->latest('id')
            ->first();

        return optional(optional($assignment)->level)->name;
    }

    protected function companyId(): ?int
    {
        return LOGIN_USER_TYPE === 'company' ? (int) Auth::guard('company')->id() : null;
    }

    protected function heatMapDateRange(Request $request, string $preset): array
    {
        $today = now();
        $from = null;
        $to = null;

        if ($request->filled('from_date') && $request->filled('to_date')) {
            return [
                Carbon::parse($request->input('from_date'))->startOfDay(),
                Carbon::parse($request->input('to_date'))->endOfDay(),
            ];
        }

        switch ($preset) {
            case 'today':
                $from = $today->copy()->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
            case 'yesterday':
                $from = $today->copy()->subDay()->startOfDay();
                $to = $today->copy()->subDay()->endOfDay();
                break;
            case '7_days':
                $from = $today->copy()->subDays(6)->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
            case '30_days':
                $from = $today->copy()->subDays(29)->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
            case 'this_month':
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfDay();
                break;
            case 'last_month':
                $from = $today->copy()->subMonthNoOverflow()->startOfMonth();
                $to = $today->copy()->subMonthNoOverflow()->endOfMonth();
                break;
        }

        return [$from, $to];
    }

    protected function heatMapRangeLabel(?Carbon $fromDate, ?Carbon $toDate, string $preset): string
    {
        if ($fromDate && $toDate) {
            return $fromDate->format('m/d/Y').' - '.$toDate->format('m/d/Y');
        }

        return match ($preset) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7_days' => 'Last 7 Days',
            '30_days' => 'Last 30 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            default => 'All Time',
        };
    }

    protected function heatMapQueryString(int $locationId, string $search, ?Carbon $fromDate, ?Carbon $toDate, string $range): string
    {
        $query = array_filter([
            'location_id' => $locationId ?: null,
            'search' => $search ?: null,
            'from_date' => $fromDate?->format('Y-m-d'),
            'to_date' => $toDate?->format('Y-m-d'),
            'range' => $fromDate && $toDate ? null : ($range !== 'all' ? $range : null),
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });

        return $query ? '?'.http_build_query($query) : '';
    }

    protected function buildHeatMapZoneStats(?int $companyId, ?Carbon $fromDate, ?Carbon $toDate, string $search): Collection
    {
        if ($this->shouldUseCompanyServiceAreas($companyId)) {
            return $this->buildCompanyServiceAreaZoneStats($companyId, $fromDate, $toDate, $search);
        }

        $rideCounts = RideRequest::query()
            ->selectRaw('request.location_id, COUNT(DISTINCT request.group_id) as ride_count')
            ->leftJoin('parcel_requests', 'parcel_requests.request_id', '=', 'request.id')
            ->when($companyId, function ($query) use ($companyId) {
                $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                    ->where('request_driver.company_id', $companyId);
            })
            ->when($fromDate && $toDate, fn ($query) => $query->whereBetween('request.created_at', [$fromDate, $toDate]))
            ->whereNull('parcel_requests.id')
            ->groupBy('request.location_id')
            ->pluck('ride_count', 'request.location_id');

        $parcelCounts = RideRequest::query()
            ->selectRaw('request.location_id, COUNT(DISTINCT request.group_id) as parcel_count')
            ->join('parcel_requests', 'parcel_requests.request_id', '=', 'request.id')
            ->when($companyId, function ($query) use ($companyId) {
                $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                    ->where('request_driver.company_id', $companyId);
            })
            ->when($fromDate && $toDate, fn ($query) => $query->whereBetween('request.created_at', [$fromDate, $toDate]))
            ->groupBy('request.location_id')
            ->pluck('parcel_count', 'request.location_id');

        return Location::query()
            ->active()
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get()
            ->map(function (Location $location) use ($rideCounts, $parcelCounts) {
                return [
                    'id' => (int) $location->id,
                    'name' => $location->name,
                    'ride_count' => (int) ($rideCounts[$location->id] ?? 0),
                    'parcel_count' => (int) ($parcelCounts[$location->id] ?? 0),
                    'total_count' => (int) (($rideCounts[$location->id] ?? 0) + ($parcelCounts[$location->id] ?? 0)),
                ];
            })
            ->prepend([
                'id' => 0,
                'name' => 'All Zone',
                'ride_count' => (int) array_sum($rideCounts->toArray()),
                'parcel_count' => (int) array_sum($parcelCounts->toArray()),
                'total_count' => (int) (array_sum($rideCounts->toArray()) + array_sum($parcelCounts->toArray())),
            ])
            ->values();
    }

    protected function buildHeatMapPoints(?int $companyId, ?Carbon $fromDate, ?Carbon $toDate, ?array $selectedLocation): array
    {
        if ($this->shouldUseCompanyServiceAreas($companyId)) {
            return $this->buildCompanyServiceAreaHeatPoints($companyId, $fromDate, $toDate, $selectedLocation);
        }

        $query = RideRequest::query()
            ->select('request.pickup_latitude', 'request.pickup_longitude', DB::raw('COUNT(*) as weight'))
            ->when($companyId, function ($builder) use ($companyId) {
                $builder->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                    ->where('request_driver.company_id', $companyId);
            })
            ->when($fromDate && $toDate, fn ($builder) => $builder->whereBetween('request.created_at', [$fromDate, $toDate]))
            ->groupBy('request.pickup_latitude', 'request.pickup_longitude')
            ->orderByDesc('weight');

        if ($selectedLocation) {
            $query->where('request.location_id', $selectedLocation['id']);
        }

        return $query->get()->map(function ($row) {
            return [
                'lat' => (float) $row->pickup_latitude,
                'lng' => (float) $row->pickup_longitude,
                'weight' => (int) $row->weight,
            ];
        })->all();
    }

    protected function buildHeatMapCompareYears(?int $companyId, ?array $selectedLocation, string $search): array
    {
        $years = [
            now()->year - 2,
            now()->year - 1,
            now()->year,
        ];

        $selectedLocationId = $selectedLocation['id'] ?? null;

        return collect($years)->map(function (int $year) use ($companyId, $selectedLocationId) {
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to = Carbon::create($year, 12, 31)->endOfDay();

            if ($this->shouldUseCompanyServiceAreas($companyId)) {
                $selectedZone = $selectedLocationId
                    ? $this->mapZones($companyId)->firstWhere('id', $selectedLocationId)
                    : null;

                $rows = $this->companyHeatMapRequestRows($companyId, $from, $to);
                if ($selectedZone) {
                    $polygon = data_get($selectedZone, 'coordinates.0', []);
                    $rows = $rows->filter(fn (array $row) => $this->pointInPolygon((float) $row['lat'], (float) $row['lng'], $polygon))->values();
                }

                return [
                    'year' => $year,
                    'label' => $year.' (01 Jan - 31 Dec)',
                    'ride_count' => (int) $rows->where('is_parcel', false)->count(),
                    'parcel_count' => (int) $rows->where('is_parcel', true)->count(),
                    'total_count' => (int) $rows->count(),
                    'points' => $this->aggregateHeatMapPoints($rows),
                ];
            }

            $points = $this->buildHeatMapPoints(
                $companyId,
                $from,
                $to,
                $selectedLocationId ? ['id' => $selectedLocationId] : null
            );

            $rideCount = RideRequest::query()
                ->leftJoin('parcel_requests', 'parcel_requests.request_id', '=', 'request.id')
                ->when($companyId, function ($query) use ($companyId) {
                    $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                        ->where('request_driver.company_id', $companyId);
                })
                ->whereBetween('request.created_at', [$from, $to])
                ->when($selectedLocationId, fn ($query) => $query->where('request.location_id', $selectedLocationId))
                ->whereNull('parcel_requests.id')
                ->distinct('request.group_id')
                ->count('request.group_id');

            $parcelCount = RideRequest::query()
                ->join('parcel_requests', 'parcel_requests.request_id', '=', 'request.id')
                ->when($companyId, function ($query) use ($companyId) {
                    $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                        ->where('request_driver.company_id', $companyId);
                })
                ->whereBetween('request.created_at', [$from, $to])
                ->when($selectedLocationId, fn ($query) => $query->where('request.location_id', $selectedLocationId))
                ->distinct('request.group_id')
                ->count('request.group_id');

            return [
                'year' => $year,
                'label' => $year.' (01 Jan - 31 Dec)',
                'ride_count' => (int) $rideCount,
                'parcel_count' => (int) $parcelCount,
                'total_count' => (int) ($rideCount + $parcelCount),
                'points' => $points,
            ];
        })->all();
    }

    protected function heatMapCenter(?array $selectedLocation, array $points): array
    {
        if ($selectedLocation && !empty($selectedLocation['coordinates'][0][0])) {
            return [
                'lat' => (float) ($selectedLocation['coordinates'][0][0]['lat'] ?? 0),
                'lng' => (float) ($selectedLocation['coordinates'][0][0]['lng'] ?? 0),
            ];
        }

        if (!empty($points[0])) {
            return [
                'lat' => (float) $points[0]['lat'],
                'lng' => (float) $points[0]['lng'],
            ];
        }

        return [
            'lat' => 0,
            'lng' => 0,
        ];
    }

    protected function mapZones(?int $companyId, string $search = ''): Collection
    {
        if ($this->shouldUseCompanyServiceAreas($companyId)) {
            return CompanyServiceArea::query()
                ->active()
                ->where('company_id', $companyId)
                ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->get()
                ->map(function (CompanyServiceArea $area) {
                    return [
                        'id' => (int) $area->id,
                        'name' => $area->name,
                        'coordinates' => $area->co_ordinates ?? [],
                        'source' => 'company_service_area',
                    ];
                })
                ->values();
        }

        return Location::query()
            ->active()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get()
            ->map(function (Location $location) {
                return [
                    'id' => (int) $location->id,
                    'name' => $location->name,
                    'coordinates' => $location->co_ordinates ?? [],
                    'source' => 'location',
                ];
            })
            ->values();
    }

    protected function shouldUseCompanyServiceAreas(?int $companyId): bool
    {
        if (
            !$companyId ||
            !function_exists('saas_dispatch_enabled') ||
            !saas_dispatch_enabled() ||
            !class_exists('Modules\SaaSDispatch\Entities\CompanyServiceArea')
        ) {
            return false;
        }

        $settings = function_exists('company_saas_settings')
            ? company_saas_settings($companyId)
            : null;

        return (bool) optional($settings)->is_saas_enabled && (bool) optional($settings)->use_own_locations;
    }

    protected function companyHeatMapRequestRows(?int $companyId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $query = RideRequest::query()
            ->leftJoin('parcel_requests', 'parcel_requests.request_id', '=', 'request.id')
            ->selectRaw('request.group_id')
            ->selectRaw('request.pickup_latitude')
            ->selectRaw('request.pickup_longitude')
            ->selectRaw('MAX(CASE WHEN parcel_requests.id IS NULL THEN 0 ELSE 1 END) as is_parcel')
            ->whereNotNull('request.pickup_latitude')
            ->whereNotNull('request.pickup_longitude')
            ->when($fromDate && $toDate, fn ($builder) => $builder->whereBetween('request.created_at', [$fromDate, $toDate]))
            ->groupBy('request.group_id', 'request.pickup_latitude', 'request.pickup_longitude');

        if ($companyId) {
            if (Schema::hasColumn('request', 'company_id')) {
                $query->where('request.company_id', $companyId);
            } else {
                $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                    ->where('request_driver.company_id', $companyId);
            }
        }

        return $query->get()->map(function ($row) {
            return [
                'group_id' => $row->group_id,
                'lat' => (float) $row->pickup_latitude,
                'lng' => (float) $row->pickup_longitude,
                'is_parcel' => (bool) $row->is_parcel,
            ];
        })->values();
    }

    protected function buildCompanyServiceAreaZoneStats(?int $companyId, ?Carbon $fromDate, ?Carbon $toDate, string $search): Collection
    {
        $zones = $this->mapZones($companyId, $search);
        $rows = $this->companyHeatMapRequestRows($companyId, $fromDate, $toDate);

        $zoneStats = $zones->map(function (array $zone) use ($rows) {
            $polygon = data_get($zone, 'coordinates.0', []);
            $matchingRows = $polygon
                ? $rows->filter(fn (array $row) => $this->pointInPolygon((float) $row['lat'], (float) $row['lng'], $polygon))->values()
                : collect();

            $rideCount = (int) $matchingRows->where('is_parcel', false)->count();
            $parcelCount = (int) $matchingRows->where('is_parcel', true)->count();

            return [
                'id' => (int) $zone['id'],
                'name' => $zone['name'],
                'ride_count' => $rideCount,
                'parcel_count' => $parcelCount,
                'total_count' => $rideCount + $parcelCount,
            ];
        });

        return $zoneStats
            ->prepend([
                'id' => 0,
                'name' => 'All Zone',
                'ride_count' => (int) $rows->where('is_parcel', false)->count(),
                'parcel_count' => (int) $rows->where('is_parcel', true)->count(),
                'total_count' => (int) $rows->count(),
            ])
            ->values();
    }

    protected function buildCompanyServiceAreaHeatPoints(?int $companyId, ?Carbon $fromDate, ?Carbon $toDate, ?array $selectedLocation): array
    {
        $rows = $this->companyHeatMapRequestRows($companyId, $fromDate, $toDate);

        if ($selectedLocation) {
            $polygon = data_get($selectedLocation, 'coordinates.0', []);
            if ($polygon) {
                $rows = $rows->filter(fn (array $row) => $this->pointInPolygon((float) $row['lat'], (float) $row['lng'], $polygon))->values();
            }
        }

        return $this->aggregateHeatMapPoints($rows);
    }

    protected function aggregateHeatMapPoints(Collection $rows): array
    {
        return $rows
            ->groupBy(fn (array $row) => number_format((float) $row['lat'], 6, '.', '').','.number_format((float) $row['lng'], 6, '.', ''))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'lat' => (float) $first['lat'],
                    'lng' => (float) $first['lng'],
                    'weight' => $group->count(),
                ];
            })
            ->values()
            ->all();
    }
}
