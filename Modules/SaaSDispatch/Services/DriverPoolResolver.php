<?php

namespace Modules\SaaSDispatch\Services;

use Illuminate\Database\Eloquent\Builder;

class DriverPoolResolver
{
    public function __construct(protected SaaSModuleManager $moduleManager)
    {
    }

    public function applyScope(Builder $query, array $operatorContext, string $usersTable = 'users'): Builder
    {
        if (($operatorContext['operator_type'] ?? 'admin') === 'company' && !empty($operatorContext['company_id'])) {
            return $query->where($usersTable.'.company_id', (int) $operatorContext['company_id']);
        }

        if (($operatorContext['dispatch_mode'] ?? 'global') === 'global') {
            return $query;
        }

        $platformCompanyId = $this->moduleManager->platformCompanyId();

        return $query->where(function ($builder) use ($usersTable, $platformCompanyId) {
            $builder->where($usersTable.'.company_id', $platformCompanyId);

            if (config('saas_dispatch.allow_null_platform_drivers', true)) {
                $builder->orWhereNull($usersTable.'.company_id');
            }
        });
    }
}
