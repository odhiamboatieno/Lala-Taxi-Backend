<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\Company;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\SaaSDispatch\Entities\CompanyFareRule;
use Modules\SaaSDispatch\Entities\CompanyPeakFareDetail;
use Modules\SaaSDispatch\Entities\CompanyServiceArea;
use Modules\SaaSDispatch\Entities\CompanyVehicleType;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyFareRuleController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function __construct(protected Helpers $helper)
    {
    }

    public function index(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderIndex($this->companyContext($resolver, $company), request());
    }

    public function indexSelf(CompanyContextResolver $resolver)
    {
        return $this->renderIndex($this->companyContext($resolver), request());
    }

    public function create(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderCreate($this->companyContext($resolver, $company), request());
    }

    public function createSelf(CompanyContextResolver $resolver)
    {
        return $this->renderCreate($this->companyContext($resolver), request());
    }

    public function store(Request $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        return $this->persistBatchFareRules($request, $context, false);
    }

    public function storeSelf(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        return $this->persistBatchFareRules($request, $context, false);
    }

    public function update(Request $request, CompanyContextResolver $resolver, Company $company, CompanyFareRule $companyFareRule): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);

        return $this->persistBatchFareRules($request, $context, true, $companyFareRule);
    }

    public function updateSelf(Request $request, CompanyContextResolver $resolver, CompanyFareRule $companyFareRule): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);

        return $this->persistBatchFareRules($request, $context, true, $companyFareRule);
    }

    public function destroy(CompanyContextResolver $resolver, Company $company, CompanyFareRule $companyFareRule): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);
        $companyFareRule->peakFareDetails()->delete();
        $companyFareRule->delete();
        flashMessage('success', 'Fare rule deleted successfully.');

        return redirect()->route('saasdispatch.companies.fare-rules.index', $context['company']);
    }

    public function destroySelf(CompanyContextResolver $resolver, CompanyFareRule $companyFareRule): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);
        $companyFareRule->peakFareDetails()->delete();
        $companyFareRule->delete();
        flashMessage('success', 'Fare rule deleted successfully.');

        return redirect()->route('saasdispatch.self.fare-rules.index');
    }

    public function edit(CompanyContextResolver $resolver, Company $company, CompanyFareRule $companyFareRule)
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);

        return $this->renderEdit($context, $companyFareRule);
    }

    public function editSelf(CompanyContextResolver $resolver, CompanyFareRule $companyFareRule)
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyFareRule->company_id === (int) $context['company']->id, 404);

        return $this->renderEdit($context, $companyFareRule);
    }

    protected function renderIndex(array $context, Request $request)
    {
        $search = trim((string) $request->get('search'));
        $serviceAreas = CompanyServiceArea::query()->where('company_id', $context['company']->id)->orderBy('name')->get();
        $fareRules = CompanyFareRule::query()->with('vehicleType')->where('company_id', $context['company']->id)->get()->groupBy('company_service_area_id');

        $zones = $serviceAreas->map(function ($serviceArea, $index) use ($fareRules, $context) {
            $areaFares = $fareRules->get($serviceArea->id, collect());
            $vehicleNames = $areaFares->map(fn ($fareRule) => optional($fareRule->vehicleType)->name)->filter()->values();
            $firstFareRule = $areaFares->first();

            return [
                'index' => $index + 1,
                'id' => $serviceArea->id,
                'name' => $serviceArea->name,
                'configured' => $areaFares->isNotEmpty(),
                'configured_count' => $areaFares->count(),
                'vehicle_names' => $vehicleNames,
                'setup_url' => $firstFareRule
                    ? ($context['isAdmin']
                        ? route('saasdispatch.companies.fare-rules.edit', [$context['company'], $firstFareRule])
                        : route('saasdispatch.self.fare-rules.edit', $firstFareRule))
                    : ($context['isAdmin']
                        ? route('saasdispatch.companies.fare-rules.create', $context['company']).'?service_area_id='.$serviceArea->id
                        : route('saasdispatch.self.fare-rules.create').'?service_area_id='.$serviceArea->id),
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

        return view('saasdispatch::company_fare_rules.index', array_merge($context, [
            'zones' => $zones,
            'search' => $search,
        ]));
    }

    protected function renderCreate(array $context, Request $request)
    {
        $configuredAreaIds = CompanyFareRule::query()->where('company_id', $context['company']->id)->distinct()->pluck('company_service_area_id')->filter()->all();
        $serviceAreaId = (int) $request->get('service_area_id');

        if (!$serviceAreaId) {
            $serviceAreas = CompanyServiceArea::query()
                ->where('company_id', $context['company']->id)
                ->whereNotIn('id', $configuredAreaIds)
                ->orderBy('name')
                ->get()
                ->map(function ($serviceArea, $index) use ($context) {
                    return [
                        'index' => $index + 1,
                        'id' => $serviceArea->id,
                        'name' => $serviceArea->name,
                        'vehicle_names' => CompanyVehicleType::query()->where('company_id', $context['company']->id)->orderBy('name')->pluck('name')->values(),
                        'setup_url' => $context['isAdmin']
                            ? route('saasdispatch.companies.fare-rules.create', $context['company']).'?service_area_id='.$serviceArea->id
                            : route('saasdispatch.self.fare-rules.create').'?service_area_id='.$serviceArea->id,
                    ];
                });

            return view('saasdispatch::company_fare_rules.index', array_merge($context, [
                'zones' => $serviceAreas,
                'search' => '',
                'showOnlyNew' => true,
            ]));
        }

        abort_if(in_array($serviceAreaId, $configuredAreaIds), 404);

        return $this->renderZoneSetup($context, $serviceAreaId, null, false);
    }

    protected function renderEdit(array $context, CompanyFareRule $companyFareRule)
    {
        return $this->renderZoneSetup($context, (int) $companyFareRule->company_service_area_id, $companyFareRule, true);
    }

    protected function renderZoneSetup(array $context, int $serviceAreaId, ?CompanyFareRule $currentFareRule, bool $isEdit)
    {
        $serviceArea = CompanyServiceArea::query()->where('company_id', $context['company']->id)->findOrFail($serviceAreaId);
        $vehicleTypes = CompanyVehicleType::query()->where('company_id', $context['company']->id)->orderBy('name')->get();
        $existingRules = CompanyFareRule::query()->with('peakFareDetails')->where('company_id', $context['company']->id)->where('company_service_area_id', $serviceAreaId)->get()->keyBy('company_vehicle_type_id');
        $defaultCurrency = Currency::active()->defaultCurrency()->first();
        $currencyCode = optional($defaultCurrency)->code;

        $fareRows = $vehicleTypes->map(function ($vehicleType) use ($existingRules, $currencyCode) {
            $fareRule = $existingRules->get($vehicleType->id);
            $peakRules = collect(optional($fareRule)->peakFareDetails ?: [])->where('type', 'peak')->values()->all();
            $nightRule = collect(optional($fareRule)->peakFareDetails ?: [])->firstWhere('type', 'night');

            return [
                'vehicle_id' => $vehicleType->id,
                'vehicle_name' => $vehicleType->name,
                'fare_id' => optional($fareRule)->id,
                'base_fare' => old('fares.'.$vehicleType->id.'.base_fare', optional($fareRule)->base_fare),
                'minimum_fare' => old('fares.'.$vehicleType->id.'.minimum_fare', optional($fareRule)->minimum_fare),
                'per_min' => old('fares.'.$vehicleType->id.'.per_min', optional($fareRule)->per_minute),
                'per_km' => old('fares.'.$vehicleType->id.'.per_km', optional($fareRule)->per_km),
                'schedule_fare' => old('fares.'.$vehicleType->id.'.schedule_fare', optional($fareRule)->schedule_fare),
                'capacity' => old('fares.'.$vehicleType->id.'.capacity', optional($fareRule)->capacity ?: 1),
                'currency_code' => old('fares.'.$vehicleType->id.'.currency_code', optional($fareRule)->currency_code ?: $currencyCode),
                'waiting_time' => old('fares.'.$vehicleType->id.'.waiting_time', optional($fareRule)->waiting_time ?: 0),
                'waiting_charge' => old('fares.'.$vehicleType->id.'.waiting_charge', optional($fareRule)->waiting_charge ?: 0),
                'apply_peak' => old('fares.'.$vehicleType->id.'.apply_peak', optional($fareRule)->apply_peak ?: 'No'),
                'apply_night' => old('fares.'.$vehicleType->id.'.apply_night', optional($fareRule)->apply_night ?: 'No'),
                'status' => old('fares.'.$vehicleType->id.'.status', optional($fareRule)->status ?: 'Active'),
                'peak_fare_details' => old('fares.'.$vehicleType->id.'.peak_fare_details', count($peakRules) ? $peakRules : [['day' => '', 'start_time' => '', 'end_time' => '', 'price' => '']]),
                'night_fare_details' => old('fares.'.$vehicleType->id.'.night_fare_details', $nightRule ?: ['start_time' => '', 'end_time' => '', 'price' => '']),
            ];
        })->values();

        $formAction = $isEdit
            ? ($context['isAdmin'] ? route('saasdispatch.companies.fare-rules.update', [$context['company'], $currentFareRule]) : route('saasdispatch.self.fare-rules.update', $currentFareRule))
            : ($context['isAdmin'] ? route('saasdispatch.companies.fare-rules.store', $context['company']) : route('saasdispatch.self.fare-rules.store'));

        return view('saasdispatch::company_fare_rules.form', array_merge($context, [
            'isEdit' => $isEdit,
            'serviceArea' => $serviceArea,
            'currentFareRule' => $currentFareRule,
            'vehicleRows' => $fareRows,
            'currencyOptions' => Currency::codeSelect(),
            'time_options' => $this->helper->create_time_range('0:00', '23:00', '1 hour'),
            'day_options' => $this->helper->get_day_options(),
            'formAction' => $formAction,
            'backUrl' => $context['isAdmin'] ? route('saasdispatch.companies.fare-rules.index', $context['company']) : route('saasdispatch.self.fare-rules.index'),
        ]));
    }

    protected function persistBatchFareRules(Request $request, array $context, bool $isEdit, ?CompanyFareRule $currentFareRule = null): RedirectResponse
    {
        $serviceAreaId = $isEdit ? (int) $currentFareRule->company_service_area_id : (int) $request->input('company_service_area_id');
        $payload = $this->validateBatchRequest($request, $context['company']->id, $isEdit, $serviceAreaId);

        foreach ($payload['fares'] as $vehicleId => $fareData) {
            $fareRule = CompanyFareRule::query()->firstOrNew([
                'company_id' => $context['company']->id,
                'company_service_area_id' => $serviceAreaId,
                'company_vehicle_type_id' => (int) $vehicleId,
            ]);

            $fareRule->fill([
                'base_fare' => $fareData['base_fare'],
                'capacity' => $fareData['capacity'],
                'per_km' => $fareData['per_km'],
                'schedule_fare' => $fareData['schedule_fare'],
                'currency_code' => $fareData['currency_code'],
                'apply_peak' => $fareData['apply_peak'],
                'apply_night' => $fareData['apply_night'],
                'waiting_time' => $fareData['waiting_time'] ?? 0,
                'waiting_charge' => ($fareData['waiting_time'] ?? 0) > 0 ? ($fareData['waiting_charge'] ?? 0) : 0,
                'per_minute' => $fareData['per_min'],
                'minimum_fare' => $fareData['minimum_fare'],
                'status' => $fareData['status'] ?? 'Active',
            ]);
            $fareRule->save();

            CompanyPeakFareDetail::query()->where('company_fare_rule_id', $fareRule->id)->delete();

            if (($fareData['apply_peak'] ?? 'No') === 'Yes') {
                foreach (($fareData['peak_fare_details'] ?? []) as $peakFareDetail) {
                    if (blank($peakFareDetail['day'] ?? null) || blank($peakFareDetail['start_time'] ?? null) || blank($peakFareDetail['end_time'] ?? null)) {
                        continue;
                    }

                    CompanyPeakFareDetail::query()->create([
                        'company_fare_rule_id' => $fareRule->id,
                        'day' => $peakFareDetail['day'],
                        'start_time' => $peakFareDetail['start_time'],
                        'end_time' => $peakFareDetail['end_time'],
                        'price' => $peakFareDetail['price'] ?? 0,
                        'type' => 'peak',
                    ]);
                }
            }

            if (($fareData['apply_night'] ?? 'No') === 'Yes') {
                $nightFareDetail = $fareData['night_fare_details'] ?? [];
                if (filled($nightFareDetail['start_time'] ?? null) && filled($nightFareDetail['end_time'] ?? null)) {
                    CompanyPeakFareDetail::query()->create([
                        'company_fare_rule_id' => $fareRule->id,
                        'start_time' => $nightFareDetail['start_time'],
                        'end_time' => $nightFareDetail['end_time'],
                        'price' => $nightFareDetail['price'] ?? 0,
                        'type' => 'night',
                    ]);
                }
            }
        }

        flashMessage('success', $isEdit ? 'Fare setup updated successfully.' : 'Fare setup created successfully.');

        return redirect($context['isAdmin']
            ? route('saasdispatch.companies.fare-rules.index', $context['company'])
            : route('saasdispatch.self.fare-rules.index'));
    }

    protected function validateBatchRequest(Request $request, int $companyId, bool $isEdit, int $serviceAreaId): array
    {
        $rules = [
            'company_service_area_id' => 'required|exists:company_service_areas,id',
            'fares' => 'required|array|min:1',
        ];

        foreach ($request->input('fares', []) as $vehicleId => $fareData) {
            $prefix = 'fares.'.$vehicleId.'.';
            $rules[$prefix.'base_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'minimum_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'per_min'] = 'required|numeric|min:0';
            $rules[$prefix.'per_km'] = 'required|numeric|min:0';
            $rules[$prefix.'schedule_fare'] = 'required|numeric|min:0';
            $rules[$prefix.'capacity'] = 'required|integer|min:1';
            $rules[$prefix.'currency_code'] = 'required';
            $rules[$prefix.'waiting_time'] = 'nullable|numeric|min:0';
            $rules[$prefix.'waiting_charge'] = 'nullable|numeric|min:0';
            $rules[$prefix.'apply_peak'] = 'required|in:Yes,No';
            $rules[$prefix.'apply_night'] = 'required|in:Yes,No';
            $rules[$prefix.'status'] = 'required|in:Active,Inactive';

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

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request, $companyId, $isEdit, $serviceAreaId) {
            $selectedAreaId = (int) $request->input('company_service_area_id');
            $belongsToCompany = CompanyServiceArea::query()->where('company_id', $companyId)->whereKey($selectedAreaId)->exists();
            if (!$belongsToCompany) {
                $validator->errors()->add('company_service_area_id', 'Selected service area does not belong to the active company.');
            }

            if (!$isEdit && CompanyFareRule::query()->where('company_id', $companyId)->where('company_service_area_id', $selectedAreaId)->exists()) {
                $validator->errors()->add('company_service_area_id', 'Fare setup already exists for this zone.');
            }

            if ($isEdit && $selectedAreaId !== $serviceAreaId) {
                $validator->errors()->add('company_service_area_id', 'Zone cannot be changed on the grouped edit screen.');
            }
        });

        return $validator->validate();
    }
}
