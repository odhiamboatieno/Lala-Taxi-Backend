<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyDispatchSetting;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Http\Requests\CompanyDispatchSettingRequest;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyDispatchSettingController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function edit(CompanyContextResolver $resolver, Company $company)
    {
        return $this->renderEdit($this->companyContext($resolver, $company));
    }

    public function editSelf(CompanyContextResolver $resolver)
    {
        return $this->renderEdit($this->companyContext($resolver));
    }

    public function update(CompanyDispatchSettingRequest $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);
        if (Schema::hasTable('company_dispatch_settings')) {
            CompanyDispatchSetting::query()->updateOrCreate(
                ['company_id' => $context['company']->id],
                $this->payload($request)
            );
        }
        flashMessage('success', 'Dispatch settings updated successfully.');

        return redirect()->route('saasdispatch.companies.dispatch-settings.edit', $context['company']);
    }

    public function updateSelf(CompanyDispatchSettingRequest $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);
        if (Schema::hasTable('company_dispatch_settings')) {
            CompanyDispatchSetting::query()->updateOrCreate(
                ['company_id' => $context['company']->id],
                $this->payload($request)
            );
        }
        flashMessage('success', 'Dispatch settings updated successfully.');

        return redirect()->route('saasdispatch.self.dispatch-settings.edit');
    }

    protected function renderEdit(array $context)
    {
        if (!Schema::hasTable('company_dispatch_settings')) {
            return view('saasdispatch::company_dispatch_settings.edit', array_merge($context, [
                'dispatchSetting' => arrayToObject($this->defaultDispatchSetting()),
            ]));
        }

        return view('saasdispatch::company_dispatch_settings.edit', array_merge($context, [
            'dispatchSetting' => CompanyDispatchSetting::query()->firstOrNew(
                ['company_id' => $context['company']->id],
                $this->defaultDispatchSetting()
            ),
        ]));
    }

    protected function payload(CompanyDispatchSettingRequest $request): array
    {
        return [
            'dispatch_strategy' => $request->input('dispatch_strategy'),
            'driver_search_radius' => $request->input('driver_search_radius'),
            'max_driver_search_radius' => $request->input('max_driver_search_radius'),
            'request_timeout_seconds' => (int) $request->input('request_timeout_seconds'),
            'retry_rounds' => (int) $request->input('retry_rounds'),
            'allow_auto_dispatch' => $request->boolean('allow_auto_dispatch'),
            'allow_manual_dispatch' => $request->boolean('allow_manual_dispatch'),
            'allow_scheduled_trips' => $request->boolean('allow_scheduled_trips'),
            'allow_airport_trips' => $request->boolean('allow_airport_trips'),
            'allow_out_of_area_trips' => $request->boolean('allow_out_of_area_trips'),
            'status' => $request->input('status'),
        ];
    }

    protected function defaultDispatchSetting(): array
    {
        return [
            'dispatch_strategy' => null,
            'driver_search_radius' => 5,
            'max_driver_search_radius' => 12,
            'request_timeout_seconds' => 30,
            'retry_rounds' => 3,
            'allow_auto_dispatch' => true,
            'allow_manual_dispatch' => true,
            'allow_scheduled_trips' => true,
            'allow_airport_trips' => true,
            'allow_out_of_area_trips' => false,
            'status' => 'Active',
        ];
    }
}
