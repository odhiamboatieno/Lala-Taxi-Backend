<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class CorporateFinanceAuthorization
{
    public function authorize(CompanyCorporateAccount $account, Admin|Company $actor): void
    {
        $this->authorizeOwner((int) $account->company_id, $actor);
    }

    public function authorizeOwner(int $companyId, Admin|Company $actor): void
    {
        if ($actor instanceof Admin) {
            abort_unless($actor->hasPermission(['manage_company_accounts_invoicing']), 403);
        } else {
            abort_unless($companyId === (int) $actor->id && operation_team_can('manage_company_accounts_invoicing'), 403);
        }
    }

    public function identifier(Admin|Company|null $actor): string
    {
        return $actor ? ($actor instanceof Admin ? 'admin:' : 'company:').$actor->id : 'system';
    }
}
