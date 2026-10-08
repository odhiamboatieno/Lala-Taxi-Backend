<?php

namespace App\Services\Corporate;

use App\Models\Company;

class CorporateCapabilities
{
    public function newBookingsEnabled(Company $company): bool
    {
        return (bool) config('corporate.bookings_enabled', false) && $this->managementEnabled($company);
    }

    public function managementEnabled(Company $company): bool
    {
        return $company->status === 'Active'
            && saas_dispatch_enabled()
            && saas_feature_module_enabled('accounts_invoicing')
            && ((int) $company->id === saas_platform_company_id() || company_saas_enabled((int) $company->id));
    }

    public function ensureManagementEnabled(Company $company): void
    {
        abort_unless($this->servicingEnabled($company), 404);
    }

    public function servicingEnabled(Company $company): bool
    {
        return $this->managementEnabled($company)
            || \Modules\SaaSDispatch\Entities\CompanyCorporateAccount::where('company_id', $company->id)->exists();
    }

    public function platformServicingEnabled(): bool
    {
        return (saas_dispatch_enabled() && saas_feature_module_enabled('accounts_invoicing'))
            || (\Illuminate\Support\Facades\Schema::hasTable('company_corporate_accounts')
                && \Modules\SaaSDispatch\Entities\CompanyCorporateAccount::exists());
    }
}
