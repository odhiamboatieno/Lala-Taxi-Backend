<?php

namespace Modules\SaaSDispatch\Policies;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Trips;

class CompanyDispatchPolicy
{
    public function access(Admin|Company $actor, int $companyId): bool
    {
        if ($actor instanceof Admin) {
            return $companyId > 0;
        }

        return (int) $actor->id === $companyId;
    }

    public function assignDriver(Admin|Company $actor, int $companyId, Trips $trip): bool
    {
        if (!$this->access($actor, $companyId)) {
            return false;
        }

        if ($trip->company_id === null) {
            return true;
        }

        return (int) $trip->company_id === $companyId;
    }
}
