<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyFeeSetting;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Http\Requests\CompanyFeeSettingRequest;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyFeeSettingController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderEdit($this->companyContext($resolver, $company));
    }

    public function indexSelf(CompanyContextResolver $resolver)
    {
        return $this->renderEdit($this->companyContext($resolver));
    }

    public function store(CompanyFeeSettingRequest $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        $this->saveFeeSettings($context['company']->id, $request);
        flashMessage('success', 'Fee settings updated successfully.');

        return redirect()->route('saasdispatch.companies.fee-settings.index', $context['company']);
    }

    public function storeSelf(CompanyFeeSettingRequest $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        $this->saveFeeSettings($context['company']->id, $request);
        flashMessage('success', 'Fee settings updated successfully.');

        return redirect()->route('saasdispatch.self.fee-settings.index');
    }

    public function update(CompanyFeeSettingRequest $request, CompanyContextResolver $resolver, Company $company, CompanyFeeSetting $companyFeeSetting): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyFeeSetting->company_id === (int) $context['company']->id, 404);
        $this->saveFeeSettings($context['company']->id, $request);
        flashMessage('success', 'Fee settings updated successfully.');

        return redirect()->route('saasdispatch.companies.fee-settings.index', $context['company']);
    }

    public function updateSelf(CompanyFeeSettingRequest $request, CompanyContextResolver $resolver, CompanyFeeSetting $companyFeeSetting): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyFeeSetting->company_id === (int) $context['company']->id, 404);
        $this->saveFeeSettings($context['company']->id, $request);
        flashMessage('success', 'Fee settings updated successfully.');

        return redirect()->route('saasdispatch.self.fee-settings.index');
    }

    public function destroy(CompanyContextResolver $resolver, Company $company, CompanyFeeSetting $companyFeeSetting): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        abort_unless((int) $companyFeeSetting->company_id === (int) $context['company']->id, 404);
        $companyFeeSetting->delete();
        flashMessage('success', 'Fee setting deleted successfully.');

        return redirect()->route('saasdispatch.companies.fee-settings.index', $context['company']);
    }

    public function destroySelf(CompanyContextResolver $resolver, CompanyFeeSetting $companyFeeSetting): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        abort_unless((int) $companyFeeSetting->company_id === (int) $context['company']->id, 404);
        $companyFeeSetting->delete();
        flashMessage('success', 'Fee setting deleted successfully.');

        return redirect()->route('saasdispatch.self.fee-settings.index');
    }

    protected function renderEdit(array $context)
    {
        if (!Schema::hasTable('company_fee_settings')) {
            return view('saasdispatch::company_fee_settings.edit', array_merge($context, [
                'feeSettings' => $this->defaultFeeSettings(),
            ]));
        }

        $settings = CompanyFeeSetting::query()
            ->where('company_id', $context['company']->id)
            ->get()
            ->keyBy('fee_name');

        return view('saasdispatch::company_fee_settings.edit', array_merge($context, [
            'feeSettings' => [
                'access_fee' => data_get($settings, 'access_fee.fee_value', 0),
                'driver_peak_fare' => data_get($settings, 'driver_peak_fare.fee_value', 0),
                'driver_access_fee' => data_get($settings, 'driver_access_fee.fee_value', 0),
                'additional_fee' => (float) data_get($settings, 'additional_fee.fee_value', 0) > 0 ? 'Yes' : 'No',
                'additional_rider_fare' => data_get($settings, 'additional_rider_fare.fee_value', 0),
            ],
        ]));
    }

    protected function saveFeeSettings(int $companyId, CompanyFeeSettingRequest $request): void
    {
        if (!Schema::hasTable('company_fee_settings')) {
            return;
        }

        $definitions = [
            'access_fee' => ['type' => 'percentage', 'applies_to' => 'rider', 'value' => $request->input('access_fee', 0)],
            'driver_peak_fare' => ['type' => 'percentage', 'applies_to' => 'driver', 'value' => $request->input('driver_peak_fare', 0)],
            'driver_access_fee' => ['type' => 'percentage', 'applies_to' => 'driver', 'value' => $request->input('driver_service_fee', 0)],
            'additional_fee' => ['type' => 'fixed', 'applies_to' => 'trip', 'value' => $request->input('additional_fee', 'No') === 'Yes' ? 1 : 0],
            'additional_rider_fare' => ['type' => 'percentage', 'applies_to' => 'rider', 'value' => $request->input('additional_rider_fare', 0)],
        ];

        foreach ($definitions as $feeName => $definition) {
            CompanyFeeSetting::query()->updateOrCreate(
                ['company_id' => $companyId, 'fee_name' => $feeName],
                [
                    'fee_type' => $definition['type'],
                    'fee_value' => $definition['value'],
                    'applies_to' => $definition['applies_to'],
                    'status' => 'Active',
                ]
            );
        }
    }

    protected function defaultFeeSettings(): array
    {
        return [
            'access_fee' => 0,
            'driver_peak_fare' => 0,
            'driver_access_fee' => 0,
            'additional_fee' => 'No',
            'additional_rider_fare' => 0,
        ];
    }
}
