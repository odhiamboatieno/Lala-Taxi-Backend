<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Modules\SaaSDispatch\Entities\CompanyVehicleType;
use Modules\SaaSDispatch\Entities\GlobalVehicleCategory;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Http\Requests\CompanyVehicleTypeRequest;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyVehicleTypeController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderIndex($this->companyContext($resolver, $company));
    }

    public function indexSelf(CompanyContextResolver $resolver)
    {
        return $this->renderIndex($this->companyContext($resolver));
    }

    public function create(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderCreate($this->companyContext($resolver, $company));
    }

    public function createSelf(CompanyContextResolver $resolver)
    {
        return $this->renderCreate($this->companyContext($resolver));
    }

    public function store(CompanyVehicleTypeRequest $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        CompanyVehicleType::query()->create(array_merge($this->payload($request), ['company_id' => $context['company']->id]));
        flashMessage('success', 'Vehicle type created successfully.');

        return redirect()->route('saasdispatch.companies.vehicle-types.index', $context['company']);
    }

    public function storeSelf(CompanyVehicleTypeRequest $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        CompanyVehicleType::query()->create(array_merge($this->payload($request), ['company_id' => $context['company']->id]));
        flashMessage('success', 'Vehicle type created successfully.');

        return redirect()->route('saasdispatch.self.vehicle-types.index');
    }

    public function update(CompanyVehicleTypeRequest $request, CompanyContextResolver $resolver, Company $company, CompanyVehicleType $companyVehicleType): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);
        $companyVehicleType->update($this->payload($request));
        flashMessage('success', 'Vehicle type updated successfully.');

        return redirect()->route('saasdispatch.companies.vehicle-types.index', $context['company']);
    }

    public function updateSelf(CompanyVehicleTypeRequest $request, CompanyContextResolver $resolver, CompanyVehicleType $companyVehicleType): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);
        $companyVehicleType->update($this->payload($request));
        flashMessage('success', 'Vehicle type updated successfully.');

        return redirect()->route('saasdispatch.self.vehicle-types.index');
    }

    public function destroy(CompanyContextResolver $resolver, Company $company, CompanyVehicleType $companyVehicleType): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);
        $companyVehicleType->delete();
        flashMessage('success', 'Vehicle type deleted successfully.');

        return redirect()->route('saasdispatch.companies.vehicle-types.index', $context['company']);
    }

    public function destroySelf(CompanyContextResolver $resolver, CompanyVehicleType $companyVehicleType): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);
        $companyVehicleType->delete();
        flashMessage('success', 'Vehicle type deleted successfully.');

        return redirect()->route('saasdispatch.self.vehicle-types.index');
    }

    public function edit(CompanyContextResolver $resolver, Company $company, CompanyVehicleType $companyVehicleType)
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);

        return $this->renderEdit($context, $companyVehicleType);
    }

    public function editSelf(CompanyContextResolver $resolver, CompanyVehicleType $companyVehicleType)
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyVehicleType->company_id === (int) $context['company']->id, 404);

        return $this->renderEdit($context, $companyVehicleType);
    }

    protected function renderIndex(array $context)
    {
        return view('saasdispatch::company_vehicle_types.index', array_merge($context, [
            'vehicleTypes' => CompanyVehicleType::query()
                ->with('globalVehicleCategory')
                ->where('company_id', $context['company']->id)
                ->latest('id')
                ->get(),
            'globalVehicleCategories' => GlobalVehicleCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]));
    }

    protected function renderCreate(array $context)
    {
        return view('saasdispatch::company_vehicle_types.add', array_merge($context, [
            'globalVehicleCategories' => GlobalVehicleCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]));
    }

    protected function renderEdit(array $context, CompanyVehicleType $companyVehicleType)
    {
        return view('saasdispatch::company_vehicle_types.edit', array_merge($context, [
            'result' => $companyVehicleType,
            'globalVehicleCategories' => GlobalVehicleCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]));
    }

    protected function payload(CompanyVehicleTypeRequest $request): array
    {
        $payload = [
            'global_vehicle_category_id' => $request->input('global_vehicle_category_id') ?: null,
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'is_pool' => $request->input('is_pool'),
            'icon' => $request->input('icon'),
            'passenger_capacity' => (int) $request->input('passenger_capacity'),
            'luggage_capacity' => (int) $request->input('luggage_capacity', 0),
            'is_ev' => $request->boolean('is_ev'),
            'is_accessible' => $request->boolean('is_accessible'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->input('status'),
        ];

        $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
        $targetDir = '/images/car_image';

        foreach (['vehicle_image', 'active_image', 'app_image'] as $field) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $image = $request->file($field);
            $extension = $image->getClientOriginalExtension();
            $fileName = $field.'_'.time().'.'.$extension;
            $options = ['target_dir' => $targetDir, 'file_name' => $fileName];
            $uploadResult = $imageUploader->upload($image, $options);

            if (!$uploadResult['status']) {
                abort(422, $uploadResult['status_message']);
            }

            $payload[$field] = $uploadResult['file_name'];
        }

        return $payload;
    }
}
