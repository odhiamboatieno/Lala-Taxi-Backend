<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Start\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\LocationExtraFareService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Modules\SaaSDispatch\Entities\CompanyServiceArea;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Http\Requests\CompanyServiceAreaRequest;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyServiceAreaController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function __construct(
        protected Helpers $helper,
        protected LocationExtraFareService $extraFareService,
    )
    {
    }

    public function index(Request $request, CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderPage($request, $this->companyContext($resolver, $company));
    }

    public function indexSelf(Request $request, CompanyContextResolver $resolver)
    {
        return $this->renderPage($request, $this->companyContext($resolver));
    }

    public function create(Request $request, CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderPage($request, $this->companyContext($resolver, $company));
    }

    public function createSelf(Request $request, CompanyContextResolver $resolver)
    {
        return $this->renderPage($request, $this->companyContext($resolver));
    }

    public function store(CompanyServiceAreaRequest $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        CompanyServiceArea::query()->create(array_merge($this->payload($request), ['company_id' => $context['company']->id]));
        flashMessage('success', 'Zone created successfully.');

        return redirect()->route('saasdispatch.companies.service-areas.index', $context['company']);
    }

    public function storeSelf(CompanyServiceAreaRequest $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        CompanyServiceArea::query()->create(array_merge($this->payload($request), ['company_id' => $context['company']->id]));
        flashMessage('success', 'Zone created successfully.');

        return redirect()->route('saasdispatch.self.service-areas.index');
    }

    public function update(CompanyServiceAreaRequest $request, CompanyContextResolver $resolver, Company $company, CompanyServiceArea $companyServiceArea): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);
        $companyServiceArea->update($this->payload($request));
        flashMessage('success', 'Zone updated successfully.');

        return redirect()->route('saasdispatch.companies.service-areas.index', $context['company']);
    }

    public function updateSelf(CompanyServiceAreaRequest $request, CompanyContextResolver $resolver, CompanyServiceArea $companyServiceArea): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);
        $companyServiceArea->update($this->payload($request));
        flashMessage('success', 'Zone updated successfully.');

        return redirect()->route('saasdispatch.self.service-areas.index');
    }

    public function destroy(CompanyContextResolver $resolver, Company $company, CompanyServiceArea $companyServiceArea): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);
        $companyServiceArea->delete();
        flashMessage('success', 'Zone deleted successfully.');

        return redirect()->route('saasdispatch.companies.service-areas.index', $context['company']);
    }

    public function destroySelf(CompanyContextResolver $resolver, CompanyServiceArea $companyServiceArea): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);
        $companyServiceArea->delete();
        flashMessage('success', 'Zone deleted successfully.');

        return redirect()->route('saasdispatch.self.service-areas.index');
    }

    public function edit(Request $request, CompanyContextResolver $resolver, Company $company, CompanyServiceArea $companyServiceArea)
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);

        return $this->renderPage($request, $context, $companyServiceArea);
    }

    public function editSelf(Request $request, CompanyContextResolver $resolver, CompanyServiceArea $companyServiceArea)
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);

        return $this->renderPage($request, $context, $companyServiceArea);
    }

    public function bulkExtraFare(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $company = $request->route('company');
        $context = $this->companyContext($resolver, $company);
        $enabled = (bool) $request->boolean('enabled');
        $percentage = $enabled ? round((float) $request->input('extra_fare_percentage'), 2) : 0;
        $reason = $enabled ? trim((string) $request->input('extra_fare_reason')) : null;

        CompanyServiceArea::query()
            ->where('company_id', $context['company']->id)
            ->update([
                'extra_fare_enabled' => $enabled,
                'extra_fare_percentage' => $percentage,
                'extra_fare_reason' => $reason,
            ]);

        flashMessage('success', $enabled ? 'All-zone extra fare applied successfully' : 'All-zone extra fare disabled successfully');

        return redirect()->to($this->indexUrl($context));
    }

    public function toggleExtraFare(Request $request, CompanyContextResolver $resolver)
    {
        $company = $request->route('company');
        $companyServiceArea = $request->route('companyServiceArea');
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);

        $enabled = (bool) $request->boolean('enabled');
        $companyServiceArea->extra_fare_enabled = $enabled;
        if (! $enabled) {
            $companyServiceArea->extra_fare_percentage = 0;
            $companyServiceArea->extra_fare_reason = null;
        } elseif ((float) $companyServiceArea->extra_fare_percentage <= 0) {
            $companyServiceArea->extra_fare_percentage = 0;
        }
        $companyServiceArea->save();

        return response()->json([
            'status' => true,
            'message' => 'Zone extra fare status updated',
        ]);
    }

    public function toggleStatus(Request $request, CompanyContextResolver $resolver)
    {
        $company = $request->route('company');
        $companyServiceArea = $request->route('companyServiceArea');
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyServiceArea->company_id === (int) $context['company']->id, 404);

        $companyServiceArea->status = $request->boolean('enabled') ? 'Active' : 'Inactive';
        $companyServiceArea->save();

        return response()->json([
            'status' => true,
            'message' => 'Zone status updated',
            'location_status' => $companyServiceArea->status,
        ]);
    }

    protected function renderPage(Request $request, array $context, ?CompanyServiceArea $editingLocation = null)
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search'));
        $companyId = (int) $context['company']->id;

        $serviceAreas = CompanyServiceArea::query()
            ->where('company_id', $companyId)
            ->when($status === 'active', fn ($query) => $query->where('status', 'Active'))
            ->when($status === 'inactive', fn ($query) => $query->where('status', 'Inactive'))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('id')
            ->get();

        $tripRows = DB::table('request')
            ->select('pickup_latitude', 'pickup_longitude')
            ->whereNotNull('pickup_latitude')
            ->whereNotNull('pickup_longitude')
            ->when(\Schema::hasColumn('request', 'company_id'), function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            }, function ($query) use ($companyId) {
                $query->join('users as request_driver', 'request_driver.id', '=', 'request.driver_id')
                    ->where('request_driver.company_id', $companyId);
            })
            ->get();

        $zoneRows = $serviceAreas->values()->map(function (CompanyServiceArea $area, int $index) use ($tripRows, $context) {
            $tripCount = collect($tripRows)->filter(function ($trip) use ($area) {
                return $this->pointInPolygon((float) $trip->pickup_latitude, (float) $trip->pickup_longitude, data_get($area->co_ordinates, '0', []));
            })->count();

            return [
                'index' => $index + 1,
                'id' => $area->id,
                'name' => $area->name,
                'trip_request_count' => $tripCount,
                'trip_request_volume' => $this->extraFareService->volumeLabel($tripCount),
                'extra_fare_enabled' => (bool) $area->extra_fare_enabled,
                'extra_fare_percentage' => round((float) $area->extra_fare_percentage, 2),
                'extra_fare_reason' => (string) $area->extra_fare_reason,
                'status' => $area->status,
                'can_delete' => true,
                'pickup_enabled' => (bool) $area->pickup_enabled,
                'dropoff_enabled' => (bool) $area->dropoff_enabled,
                'edit_url' => $context['isAdmin']
                    ? route('saasdispatch.companies.service-areas.edit', [$context['company'], $area])
                    : route('saasdispatch.self.service-areas.edit', $area),
                'extra_fare_url' => $context['isAdmin']
                    ? route('saasdispatch.companies.service-areas.edit', [$context['company'], $area])
                    : route('saasdispatch.self.service-areas.edit', $area),
                'delete_url' => $context['isAdmin']
                    ? route('saasdispatch.companies.service-areas.destroy', [$context['company'], $area])
                    : route('saasdispatch.self.service-areas.destroy', $area),
                'delete_method' => 'DELETE',
            ];
        });

        return view('admin.locations.page', array_merge($context, [
            'editingLocation' => $editingLocation,
            'formatted_coords' => old('coordinates')
                ? $this->helper->getFormattedCoordinates(old('coordinates'))
                : old('formatted_coords', $editingLocation?->co_ordinates ?? []),
            'statusFilter' => $status,
            'search' => $search,
            'zoneRows' => $zoneRows,
            'zoneCount' => $zoneRows->count(),
            'globalExtraFare' => $this->extraFareService->globalSummary($serviceAreas),
            'instructionGif' => asset('images/Instruction.gif'),
            'titleSub' => 'Manage Zones',
            'pageTitle' => 'Manage Zones',
            'isSaasServiceAreaPage' => true,
            'locationPageConfig' => [
                'index_url' => $this->indexUrl($context),
                'form_action' => $editingLocation
                    ? ($context['isAdmin']
                        ? route('saasdispatch.companies.service-areas.update', [$context['company'], $editingLocation])
                        : route('saasdispatch.self.service-areas.update', $editingLocation))
                    : ($context['isAdmin']
                        ? route('saasdispatch.companies.service-areas.store', $context['company'])
                        : route('saasdispatch.self.service-areas.store')),
                'form_method' => $editingLocation ? 'PUT' : 'POST',
                'toggle_extra_fare_base_url' => $context['isAdmin']
                    ? url('admin/saas-dispatch/companies/'.$context['company']->id.'/service-areas/toggle-extra-fare')
                    : url('company/saas-dispatch/service-areas/toggle-extra-fare'),
                'toggle_status_base_url' => $context['isAdmin']
                    ? url('admin/saas-dispatch/companies/'.$context['company']->id.'/service-areas/toggle-status')
                    : url('company/saas-dispatch/service-areas/toggle-status'),
                'bulk_extra_fare_url' => $context['isAdmin']
                    ? route('saasdispatch.companies.service-areas.bulk-extra-fare', $context['company'])
                    : route('saasdispatch.self.service-areas.bulk-extra-fare'),
                'cancel_url' => $this->indexUrl($context),
                'submit_label' => $editingLocation ? 'Update' : 'Submit',
                'cancel_label' => $editingLocation ? 'Reset' : 'Reset',
            ],
        ]));
    }

    protected function payload(CompanyServiceAreaRequest $request): array
    {
        $coordinates = $request->input('coordinates');

        return [
            'name' => $request->input('name'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'zone_name' => $request->input('zone_name'),
            'polygon' => 'POLYGON((' . $coordinates . '))',
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'dropoff_enabled' => $request->boolean('dropoff_enabled'),
            'extra_fare_enabled' => $request->boolean('extra_fare_enabled'),
            'extra_fare_percentage' => $request->boolean('extra_fare_enabled') ? round((float) $request->input('extra_fare_percentage'), 2) : 0,
            'extra_fare_reason' => $request->boolean('extra_fare_enabled') ? trim((string) $request->input('extra_fare_reason')) : null,
            'status' => $request->input('status'),
        ];
    }

    protected function indexUrl(array $context): string
    {
        return $context['isAdmin']
            ? route('saasdispatch.companies.service-areas.index', $context['company'])
            : route('saasdispatch.self.service-areas.index');
    }

    protected function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $pointCount = count($polygon);

        if ($pointCount < 3) {
            return false;
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
}
