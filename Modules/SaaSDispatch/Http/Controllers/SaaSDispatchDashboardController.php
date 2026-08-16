<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyFareRule;
use Modules\SaaSDispatch\Entities\CompanyFeeSetting;
use Modules\SaaSDispatch\Entities\CompanySaasSetting;
use Modules\SaaSDispatch\Entities\CompanyServiceArea;
use Modules\SaaSDispatch\Entities\CompanyVehicleType;
use Modules\SaaSDispatch\Entities\GlobalVehicleCategory;
use Modules\SaaSDispatch\Services\CompanyContextResolver;
use Modules\SaaSDispatch\Services\SaaSModuleManager;

class SaaSDispatchDashboardController extends Controller
{
    public function index(CompanyContextResolver $contextResolver, SaaSModuleManager $moduleManager)
    {
        $company = $contextResolver->isCompany() ? $contextResolver->actingCompany() : null;

        return view('saasdispatch::dashboard', [
            'isAdmin' => $contextResolver->isAdmin(),
            'routePrefix' => $contextResolver->routePrefix(),
            'company' => $company,
            'moduleEnabled' => $moduleManager->isEnabled(),
            'platformCompanyId' => $moduleManager->platformCompanyId(),
            'stats' => [
                'companies' => CompanySaasSetting::query()->count(),
                'enabledCompanies' => CompanySaasSetting::query()->where('is_saas_enabled', true)->count(),
                'globalVehicleCategories' => GlobalVehicleCategory::query()->count(),
                'companyVehicleTypes' => CompanyVehicleType::query()->when($company, fn ($q) => $q->where('company_id', $company->id))->count(),
                'serviceAreas' => CompanyServiceArea::query()->when($company, fn ($q) => $q->where('company_id', $company->id))->count(),
                'fareRules' => CompanyFareRule::query()->when($company, fn ($q) => $q->where('company_id', $company->id))->count(),
                'feeSettings' => Schema::hasTable('company_fee_settings')
                    ? CompanyFeeSetting::query()->when($company, fn ($q) => $q->where('company_id', $company->id))->count()
                    : 0,
                'companiesTotal' => Company::query()->count(),
            ],
            'recentCompanies' => Company::query()
                ->with('saas_setting')
                ->latest('id')
                ->limit(8)
                ->get(),
            'featureModules' => $moduleManager->featureModules(),
        ]);
    }

    public function toggleModule(Request $request, SaaSModuleManager $moduleManager): RedirectResponse
    {
        abort_unless(auth('admin')->check(), 403);

        SiteSettings::query()->updateOrCreate(
            ['name' => config('saas_dispatch.module_site_setting_key', 'saas_dispatch_module_enabled')],
            ['value' => $request->boolean('enabled') ? '1' : '0']
        );

        flashMessage('success', $request->boolean('enabled')
            ? 'SaaS Dispatch module enabled successfully.'
            : 'SaaS Dispatch module disabled successfully.');

        return redirect()->route('saasdispatch.dashboard');
    }
}
