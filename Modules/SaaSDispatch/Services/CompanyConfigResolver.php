<?php

namespace Modules\SaaSDispatch\Services;

use Modules\SaaSDispatch\Entities\CompanySaasSetting;

class CompanyConfigResolver
{
    public function __construct(protected SaaSModuleManager $moduleManager)
    {
    }

    public function forCompany($companyId): array
    {
        $companyId = $companyId !== null && $companyId !== '' ? (int) $companyId : null;
        $moduleEnabled = $this->moduleManager->isEnabled();
        $platformCompanyId = $this->moduleManager->platformCompanyId();

        if ($companyId === null || $this->moduleManager->isPlatformCompanyId($companyId)) {
            return $this->defaults($platformCompanyId, false, 'global');
        }

        $setting = CompanySaasSetting::query()->where('company_id', $companyId)->first();
        $dispatchMode = $setting?->dispatch_mode ?: config('saas_dispatch.default_dispatch_mode', 'global');
        $saasEnabledForCompany = $moduleEnabled && (bool) ($setting?->is_saas_enabled);

        if (!$saasEnabledForCompany) {
            return $this->defaults($companyId, false, 'global', $setting);
        }

        if ($dispatchMode === 'saas') {
            return [
                'company_id' => $companyId,
                'dispatch_mode' => 'saas',
                'is_module_enabled' => true,
                'is_saas_enabled' => true,
                'use_own_locations' => true,
                'use_own_fares' => true,
                'use_own_fees' => true,
                'use_own_vehicle_types' => true,
                'use_own_dispatch_settings' => true,
                'use_own_branding' => (bool) ($setting?->use_own_branding),
                'setting' => $setting,
            ];
        }

        if ($dispatchMode === 'hybrid') {
            return [
                'company_id' => $companyId,
                'dispatch_mode' => 'hybrid',
                'is_module_enabled' => true,
                'is_saas_enabled' => true,
                'use_own_locations' => (bool) ($setting?->use_own_locations),
                'use_own_fares' => (bool) ($setting?->use_own_fares),
                'use_own_fees' => (bool) ($setting?->use_own_fees),
                'use_own_vehicle_types' => (bool) ($setting?->use_own_vehicle_types),
                'use_own_dispatch_settings' => (bool) ($setting?->use_own_dispatch_settings),
                'use_own_branding' => (bool) ($setting?->use_own_branding),
                'setting' => $setting,
            ];
        }

        return $this->defaults($companyId, true, 'global', $setting);
    }

    protected function defaults(int $companyId, bool $moduleEnabled, string $dispatchMode, ?CompanySaasSetting $setting = null): array
    {
        return [
            'company_id' => $companyId,
            'dispatch_mode' => $dispatchMode,
            'is_module_enabled' => $moduleEnabled,
            'is_saas_enabled' => false,
            'use_own_locations' => false,
            'use_own_fares' => false,
            'use_own_fees' => false,
            'use_own_vehicle_types' => false,
            'use_own_dispatch_settings' => false,
            'use_own_branding' => false,
            'setting' => $setting,
        ];
    }
}
