<?php

namespace App\Services\Corporate;

use App\Models\Company;

class CorporateCapabilities
{
    public function newBookingsEnabled(Company $company): bool
    {
        return $company->status === 'Active'
            && saas_dispatch_enabled()
            && saas_feature_module_enabled('accounts_invoicing')
            && ((int) $company->id === saas_platform_company_id() || company_saas_enabled((int) $company->id));
    }

    public function ensureManagementEnabled(Company $company): void
    {
        abort_unless($this->newBookingsEnabled($company), 404);
    }
}
