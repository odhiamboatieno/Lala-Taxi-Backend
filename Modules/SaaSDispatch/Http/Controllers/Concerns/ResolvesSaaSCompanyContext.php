<?php

namespace Modules\SaaSDispatch\Http\Controllers\Concerns;

use App\Models\Company;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

trait ResolvesSaaSCompanyContext
{
    protected function companyContext(CompanyContextResolver $resolver, ?Company $company = null): array
    {
        $actingCompany = $resolver->actingCompany($company);

        return [
            'company' => $actingCompany,
            'isAdmin' => $resolver->isAdmin(),
            'isCompany' => $resolver->isCompany(),
            'routePrefix' => $resolver->routePrefix(),
            'actor' => $resolver->actor(),
        ];
    }
}
