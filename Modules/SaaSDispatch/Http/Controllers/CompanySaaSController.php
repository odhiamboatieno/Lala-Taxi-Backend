<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Modules\SaaSDispatch\Entities\CompanySaasSetting;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Http\Requests\UpdateCompanySaaSSettingsRequest;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanySaaSController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function index()
    {
        return view('saasdispatch::companies.index', [
            'companies' => Company::query()->with('saas_setting')->latest('id')->paginate(20),
        ]);
    }

    public function edit(CompanyContextResolver $resolver, Company $company)
    {
        $context = $this->companyContext($resolver, $company);
        $setting = CompanySaasSetting::query()->firstOrNew(['company_id' => $context['company']->id], [
            'dispatch_mode' => config('saas_dispatch.default_dispatch_mode', 'global'),
            'status' => 'Active',
        ]);

        return view('saasdispatch::companies.settings', array_merge($context, [
            'setting' => $setting,
            'canManageEnablement' => true,
        ]));
    }

    public function update(UpdateCompanySaaSSettingsRequest $request, CompanyContextResolver $resolver, Company $company): RedirectResponse
    {
        $context = $this->companyContext($resolver, $company);

        CompanySaasSetting::query()->updateOrCreate(
            ['company_id' => $context['company']->id],
            $this->payload($request)
        );

        flashMessage('success', 'SaaS company settings updated successfully.');

        return redirect()->route('saasdispatch.companies.settings.edit', $context['company']);
    }

    public function editSelf(CompanyContextResolver $resolver)
    {
        $context = $this->companyContext($resolver);
        $setting = CompanySaasSetting::query()->firstOrNew(['company_id' => $context['company']->id], [
            'dispatch_mode' => config('saas_dispatch.default_dispatch_mode', 'global'),
            'status' => 'Active',
        ]);

        return view('saasdispatch::companies.settings', array_merge($context, [
            'setting' => $setting,
            'canManageEnablement' => false,
        ]));
    }

    public function updateSelf(UpdateCompanySaaSSettingsRequest $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $context = $this->companyContext($resolver);

        CompanySaasSetting::query()->updateOrCreate(
            ['company_id' => $context['company']->id],
            $this->payload($request)
        );

        flashMessage('success', 'Your SaaS company settings were updated successfully.');

        return redirect()->route('saasdispatch.self.settings.edit');
    }

    protected function payload(UpdateCompanySaaSSettingsRequest $request): array
    {
        return [
            'is_saas_enabled' => auth('admin')->check()
                ? $request->boolean('is_saas_enabled')
                : (bool) optional(CompanySaasSetting::query()->where('company_id', $request->route('company')?->id ?? auth('company')->id())->first())->is_saas_enabled,
            'dispatch_mode' => $request->input('dispatch_mode'),
            'use_own_locations' => $request->boolean('use_own_locations'),
            'use_own_fares' => $request->boolean('use_own_fares'),
            'use_own_fees' => $request->boolean('use_own_fees'),
            'use_own_vehicle_types' => $request->boolean('use_own_vehicle_types'),
            'use_own_dispatch_settings' => $request->boolean('use_own_dispatch_settings'),
            'use_own_branding' => $request->boolean('use_own_branding'),
            'domain' => $request->input('domain'),
            'subdomain' => $request->input('subdomain'),
            'status' => $request->input('status'),
        ];
    }
}
