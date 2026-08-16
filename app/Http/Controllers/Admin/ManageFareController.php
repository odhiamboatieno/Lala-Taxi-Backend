<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\CarType;
use App\Models\Currency;
use App\Models\Location;
use App\Models\ManageFare;
use App\Models\PeakFareDetail;
use App\Models\Request as RideRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ManageFareController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $locations = Location::active()->orderBy('name')->get();
        $fares = ManageFare::with('car_type')->get()->groupBy('location_id');

        $zones = $locations->map(function ($location, $index) use ($fares) {
            $locationFares = $fares->get($location->id, collect());
            $vehicleNames = $locationFares->map(fn ($fare) => optional($fare->car_type)->car_name)->filter()->values();
            $firstFare = $locationFares->first();

            return [
                'index' => $index + 1,
                'id' => $location->id,
                'name' => $location->name,
                'configured' => $locationFares->isNotEmpty(),
                'configured_count' => $locationFares->count(),
                'vehicle_names' => $vehicleNames,
                'setup_url' => $firstFare
                    ? url('admin/edit_manage_fare/'.$firstFare->id)
                    : url('admin/add_manage_fare?location_id='.$location->id),
            ];
        });

        if ($search !== '') {
            $zones = $zones->filter(function ($zone) use ($search) {
                if (stripos($zone['name'], $search) !== false) {
                    return true;
                }

                return collect($zone['vehicle_names'])->contains(function ($vehicleName) use ($search) {
                    return stripos((string) $vehicleName, $search) !== false;
                });
            })->values();
        }

        return view('admin.manage_fare.view', [
            'zones' => $zones,
            'search' => $search,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            $configuredLocationIds = ManageFare::query()->distinct()->pluck('location_id')->all();
            $locationId = (int) $request->get('location_id');

            if (!$locationId) {
                $availableZones = Location::active()
                    ->whereNotIn('id', $configuredLocationIds)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($location, $index) {
                        return [
                            'index' => $index + 1,
                            'id' => $location->id,
                            'name' => $location->name,
                            'vehicle_names' => CarType::active()->orderBy('car_name')->pluck('car_name')->values(),
                            'setup_url' => url('admin/add_manage_fare?location_id='.$location->id),
                        ];
                    });

                return view('admin.manage_fare.view', [
                    'zones' => $availableZones,
                    'search' => '',
                    'showOnlyNew' => true,
                ]);
            }

            abort_if(in_array($locationId, $configuredLocationIds), 404);

            return $this->renderZoneSetup($locationId, null, false);
        }

        $locationId = (int) $request->input('location_id');
        $existingCount = ManageFare::query()->where('location_id', $locationId)->count();
        if ($existingCount > 0) {
            return back()->withInput()->withErrors(['location_id' => 'Fare setup already exists for this zone.']);
        }

        $payload = $this->validateBatchFareRequest($request, false);

        foreach ($payload['fares'] as $vehicleId => $fareData) {
            $fare = new ManageFare();
            $fare->location_id = $locationId;
            $fare->vehicle_id = (int) $vehicleId;
            $this->fillManageFare($fare, $fareData);
            $fare->save();
            $this->syncPeakAndNightFareDetails($fare, $fareData);
        }

        flashMessage('success', 'Fare setup created successfully for this zone.');

        return redirect('admin/manage_fare');
    }

    public function update(Request $request)
    {
        $currentFare = ManageFare::findOrFail($request->id);
        $locationId = (int) $currentFare->location_id;

        if ($request->isMethod('GET')) {
            return $this->renderZoneSetup($locationId, $currentFare, true);
        }

        $payload = $this->validateBatchFareRequest($request, true, $locationId);

        foreach ($payload['fares'] as $vehicleId => $fareData) {
            $fare = ManageFare::firstOrNew([
                'location_id' => $locationId,
                'vehicle_id' => (int) $vehicleId,
            ]);

            $this->fillManageFare($fare, $fareData);
            $fare->save();
            $this->syncPeakAndNightFareDetails($fare, $fareData);
        }

        flashMessage('success', 'Fare setup updated successfully.');

        return redirect('admin/manage_fare');
    }

    public function delete(Request $request)
    {
        $manageFare = ManageFare::find($request->id);

        if (!is_null($manageFare)) {
            $pendingRequest = $this->checkPendingTrips($manageFare->location_id, $manageFare->vehicle_id);

            if ($pendingRequest > 0) {
                flashMessage('error', 'Trip with this Fare is pending. So Could not Delete Fare');
                return redirect('admin/manage_fare');
            }

            PeakFareDetail::where('fare_id', $manageFare->id)->delete();
            $manageFare->delete();
            flashMessage('success', 'Manage Fare Details successfully deleted');
        } else {
            flashMessage('error', 'Manage Fare Details already deleted');
        }

        return redirect('admin/manage_fare');
    }

    protected function renderZoneSetup(int $locationId, ?ManageFare $currentFare, bool $isEdit)
    {
        $location = Location::active()->findOrFail($locationId);
        $vehicleTypes = CarType::active()->orderBy('car_name')->get();
        $existingFares = ManageFare::with('peak_fare')->where('location_id', $locationId)->get()->keyBy('vehicle_id');
        $defaultCurrency = Currency::active()->defaultCurrency()->first();
        $currencyCode = optional($defaultCurrency)->code;

        $fareRows = $vehicleTypes->map(function ($vehicleType) use ($existingFares, $currencyCode) {
            $fare = $existingFares->get($vehicleType->id);
            $peakRules = collect(optional($fare)->peak_fare ?: [])->where('type', 'peak')->values()->all();
            $nightRule = collect(optional($fare)->peak_fare ?: [])->firstWhere('type', 'night');

            return [
                'vehicle_id' => $vehicleType->id,
                'vehicle_name' => $vehicleType->car_name,
                'fare_id' => optional($fare)->id,
                'base_fare' => old('fares.'.$vehicleType->id.'.base_fare', optional($fare)->base_fare),
                'min_fare' => old('fares.'.$vehicleType->id.'.min_fare', optional($fare)->min_fare),
                'per_min' => old('fares.'.$vehicleType->id.'.per_min', optional($fare)->per_min),
                'per_km' => old('fares.'.$vehicleType->id.'.per_km', optional($fare)->per_km),
                'schedule_fare' => old('fares.'.$vehicleType->id.'.schedule_fare', optional($fare)->schedule_fare),
                'capacity' => old('fares.'.$vehicleType->id.'.capacity', optional($fare)->capacity ?: 1),
                'currency_code' => old('fares.'.$vehicleType->id.'.currency_code', optional($fare)->currency_code ?: $currencyCode),
                'waiting_time' => old('fares.'.$vehicleType->id.'.waiting_time', optional($fare)->waiting_time ?: 0),
                'waiting_charge' => old('fares.'.$vehicleType->id.'.waiting_charge', optional($fare)->waiting_charge ?: 0),
                'apply_peak' => old('fares.'.$vehicleType->id.'.apply_peak', optional($fare)->apply_peak ?: 'No'),
                'apply_night' => old('fares.'.$vehicleType->id.'.apply_night', optional($fare)->apply_night ?: 'No'),
                'peak_fare_details' => old('fares.'.$vehicleType->id.'.peak_fare_details', count($peakRules) ? $peakRules : [['day' => '', 'start_time' => '', 'end_time' => '', 'price' => '']]),
                'night_fare_details' => old('fares.'.$vehicleType->id.'.night_fare_details', $nightRule ?: ['start_time' => '', 'end_time' => '', 'price' => '']),
            ];
        })->values();

        return view('admin.manage_fare.form', [
            'isEdit' => $isEdit,
            'location' => $location,
            'currentFare' => $currentFare,
            'vehicleRows' => $fareRows,
            'currencyOptions' => Currency::codeSelect(),
            'time_options' => $this->helper->create_time_range('0:00', '23:00', '1 hour'),
            'day_options' => $this->helper->get_day_options(),
            'formAction' => $isEdit ? url('admin/edit_manage_fare/'.$currentFare->id) : url('admin/add_manage_fare'),
            'backUrl' => url('admin/manage_fare'),
        ]);
    }

    protected function validateBatchFareRequest(Request $request, bool $isEdit, ?int $locationId = null): array
    {
        $rules = [
            'location_id' => 'required|exists:locations,id',
            'fares' => 'required|array|min:1',
        ];

        $attributes = [
            'location_id' => 'Zone',
        ];

        foreach ($request->input('fares', []) as $vehicleId => $fareData) {
            $prefix = 'fares.'.$vehicleId.'.';
            $vehicleName = optional(CarType::find($vehicleId))->car_name ?: 'Vehicle';

            $rules[$prefix.'base_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'min_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'per_min'] = 'required|numeric|min:0';
            $rules[$prefix.'per_km'] = 'required|numeric|min:0';
            $rules[$prefix.'schedule_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'capacity'] = 'required|integer|min:1';
            $rules[$prefix.'currency_code'] = 'required';
            $rules[$prefix.'waiting_time'] = 'nullable|numeric|min:0';
            $rules[$prefix.'waiting_charge'] = 'nullable|numeric|min:0';
            $rules[$prefix.'apply_peak'] = 'required|in:Yes,No';
            $rules[$prefix.'apply_night'] = 'required|in:Yes,No';

            $attributes[$prefix.'base_fare'] = $vehicleName.' Base Fare';
            $attributes[$prefix.'min_fare'] = $vehicleName.' Minimum Fare';
            $attributes[$prefix.'per_min'] = $vehicleName.' Per Minutes';
            $attributes[$prefix.'per_km'] = $vehicleName.' Per Kilometer';
            $attributes[$prefix.'schedule_fare'] = $vehicleName.' Schedule Fare';
            $attributes[$prefix.'capacity'] = $vehicleName.' Capacity';
            $attributes[$prefix.'currency_code'] = $vehicleName.' Currency';
            $attributes[$prefix.'waiting_time'] = $vehicleName.' Waiting Time';
            $attributes[$prefix.'waiting_charge'] = $vehicleName.' Waiting Charge';

            if (($fareData['apply_peak'] ?? 'No') === 'Yes') {
                foreach (($fareData['peak_fare_details'] ?? []) as $index => $peakFareDetail) {
                    $peakPrefix = $prefix.'peak_fare_details.'.$index.'.';
                    $rules[$peakPrefix.'day'] = 'required';
                    $rules[$peakPrefix.'start_time'] = 'required';
                    $rules[$peakPrefix.'end_time'] = 'required';
                    $rules[$peakPrefix.'price'] = 'required|numeric|min:1.1';
                }
            }

            if (($fareData['apply_night'] ?? 'No') === 'Yes') {
                $nightPrefix = $prefix.'night_fare_details.';
                $rules[$nightPrefix.'start_time'] = 'required';
                $rules[$nightPrefix.'end_time'] = 'required';
                $rules[$nightPrefix.'price'] = 'required|numeric|min:1.1';
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            'min' => 'The :attribute must be at least :min.',
        ], $attributes);

        $validator->after(function ($validator) use ($request, $isEdit, $locationId) {
            $selectedLocationId = (int) $request->input('location_id');
            if (!$selectedLocationId) {
                return;
            }

            if (!$isEdit && ManageFare::where('location_id', $selectedLocationId)->exists()) {
                $validator->errors()->add('location_id', 'Fare setup already exists for this zone.');
            }

            if ($isEdit && $locationId !== null && $selectedLocationId !== $locationId) {
                $validator->errors()->add('location_id', 'Zone cannot be changed on the grouped edit screen.');
            }
        });

        return $validator->validate();
    }

    protected function fillManageFare(ManageFare $fare, array $fareData): void
    {
        $fare->base_fare = $fareData['base_fare'];
        $fare->min_fare = $fareData['min_fare'];
        $fare->per_min = $fareData['per_min'];
        $fare->per_km = $fareData['per_km'];
        $fare->schedule_fare = $fareData['schedule_fare'];
        $fare->capacity = $fareData['capacity'];
        $fare->currency_code = $fareData['currency_code'];
        $fare->apply_peak = $fareData['apply_peak'];
        $fare->apply_night = $fareData['apply_night'];
        $fare->waiting_time = $fareData['waiting_time'] ?? 0;
        $fare->waiting_charge = ($fareData['waiting_time'] ?? 0) > 0 ? ($fareData['waiting_charge'] ?? 0) : 0;
    }

    protected function syncPeakAndNightFareDetails(ManageFare $fare, array $fareData): void
    {
        PeakFareDetail::where('fare_id', $fare->id)->delete();

        if (($fareData['apply_peak'] ?? 'No') === 'Yes') {
            foreach (($fareData['peak_fare_details'] ?? []) as $peakFareDetail) {
                if (blank($peakFareDetail['day'] ?? null) || blank($peakFareDetail['start_time'] ?? null) || blank($peakFareDetail['end_time'] ?? null)) {
                    continue;
                }

                $peakFare = new PeakFareDetail();
                $peakFare->fare_id = $fare->id;
                $peakFare->day = $peakFareDetail['day'];
                $peakFare->start_time = $peakFareDetail['start_time'];
                $peakFare->end_time = $peakFareDetail['end_time'];
                $peakFare->price = $peakFareDetail['price'] ?? 0;
                $peakFare->type = 'peak';
                $peakFare->save();
            }
        }

        if (($fareData['apply_night'] ?? 'No') === 'Yes') {
            $nightFareDetail = $fareData['night_fare_details'] ?? [];
            if (filled($nightFareDetail['start_time'] ?? null) && filled($nightFareDetail['end_time'] ?? null)) {
                $nightFare = new PeakFareDetail();
                $nightFare->fare_id = $fare->id;
                $nightFare->start_time = $nightFareDetail['start_time'];
                $nightFare->end_time = $nightFareDetail['end_time'];
                $nightFare->price = $nightFareDetail['price'] ?? 0;
                $nightFare->type = 'night';
                $nightFare->save();
            }
        }
    }

    public function checkPendingTrips($locationId, $vehicleId)
    {
        return RideRequest::whereHas('trips', function ($query) use ($locationId, $vehicleId) {
            $query->whereHas('driver_location', function ($driverQuery) {
                $driverQuery->where('status', 'Trip');
            })->where('car_id', $vehicleId);
        })->where('location_id', $locationId)->count();
    }
}
