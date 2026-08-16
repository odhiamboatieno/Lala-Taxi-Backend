<?php

namespace Modules\SaaSDispatch\Services;

use Illuminate\Support\Collection;
use Modules\SaaSDispatch\Entities\SaaSFeatureModule;

class SaaSModuleManager
{
    protected array $defaultFeatureStates = [
        'company_documents' => true,
        'networking' => true,
        'accounts_invoicing' => true,
        'data_suite' => true,
        'messages' => true,
        'support_system_status' => true,
    ];

    public function isEnabled(): bool
    {
        return \saas_dispatch_enabled();
    }

    public function platformCompanyId(): int
    {
        return \saas_platform_company_id();
    }

    public function isPlatformCompanyId($companyId): bool
    {
        if ($companyId === null || $companyId === '') {
            return (bool) config('saas_dispatch.allow_null_platform_drivers', true);
        }

        return (int) $companyId === $this->platformCompanyId();
    }

    public function normalizeOperatorType(?string $operatorType, $companyId = null): string
    {
        if ($operatorType === 'company' && !$this->isPlatformCompanyId($companyId)) {
            return 'company';
        }

        return 'admin';
    }

    public function featureModules(): Collection
    {
        if (! class_exists(SaaSFeatureModule::class)) {
            return collect();
        }

        return SaaSFeatureModule::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function featureEnabled(string $slug): bool
    {
        $module = SaaSFeatureModule::query()->where('slug', $slug)->first();

        if (! $module) {
            return (bool) ($this->defaultFeatureStates[$slug] ?? false);
        }

        return (bool) $module->is_installed && (bool) $module->is_enabled;
    }
}
