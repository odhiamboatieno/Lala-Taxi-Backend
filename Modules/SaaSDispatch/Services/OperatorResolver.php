<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\User;

class OperatorResolver
{
    public function __construct(
        protected SaaSModuleManager $moduleManager,
        protected CompanyConfigResolver $companyConfigResolver,
    ) {
    }

    public function resolveForPayload(?User $rider, array $payload = []): array
    {
        $platformCompanyId = $this->moduleManager->platformCompanyId();
        $requestedCompanyId = $payload['company_id'] ?? $payload['operator_id'] ?? $rider?->company_id ?? $platformCompanyId;
        $requestedOperatorType = $payload['operator_type'] ?? null;

        if ($requestedOperatorType === 'admin' || $this->moduleManager->isPlatformCompanyId($requestedCompanyId)) {
            return $this->platformContext();
        }

        $companyId = (int) $requestedCompanyId;
        $companyConfig = $this->companyConfigResolver->forCompany($companyId);

        return [
            'operator_type' => 'company',
            'operator_id' => $companyId,
            'company_id' => $companyId,
            'platform_company_id' => $platformCompanyId,
            'is_platform' => false,
            'company_config' => $companyConfig,
            'dispatch_mode' => $companyConfig['dispatch_mode'],
        ];
    }

    public function resolveForTrip(object $trip): array
    {
        $operatorType = $trip->operator_type ?? null;
        $operatorId = $trip->operator_id ?? null;
        $companyId = $trip->company_id ?? null;

        if ($operatorType === 'company' && $companyId) {
            $companyConfig = $this->companyConfigResolver->forCompany($companyId);

            return [
                'operator_type' => 'company',
                'operator_id' => $operatorId ? (int) $operatorId : (int) $companyId,
                'company_id' => (int) $companyId,
                'platform_company_id' => $this->moduleManager->platformCompanyId(),
                'is_platform' => false,
                'company_config' => $companyConfig,
                'dispatch_mode' => $companyConfig['dispatch_mode'],
            ];
        }

        if ($companyId && !$this->moduleManager->isPlatformCompanyId($companyId)) {
            return $this->resolveForPayload(null, [
                'operator_type' => 'company',
                'company_id' => $companyId,
                'operator_id' => $operatorId,
            ]);
        }

        return $this->platformContext();
    }

    public function resolveForDriver(User $driver): array
    {
        if ($driver->company_id && !$this->moduleManager->isPlatformCompanyId($driver->company_id)) {
            return $this->resolveForPayload(null, [
                'operator_type' => 'company',
                'company_id' => $driver->company_id,
            ]);
        }

        return $this->platformContext();
    }

    public function platformContext(): array
    {
        $platformCompanyId = $this->moduleManager->platformCompanyId();

        return [
            'operator_type' => 'admin',
            'operator_id' => $platformCompanyId,
            'company_id' => $platformCompanyId,
            'platform_company_id' => $platformCompanyId,
            'is_platform' => true,
            'company_config' => $this->companyConfigResolver->forCompany($platformCompanyId),
            'dispatch_mode' => 'global',
        ];
    }
}
