<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\Fees;
use Illuminate\Support\Facades\Schema;
use Modules\SaaSDispatch\Entities\CompanyFeeSetting;

class FeeResolver
{
    public function resolve(array $operatorContext)
    {
        if ($operatorContext['is_platform'] || !data_get($operatorContext, 'company_config.use_own_fees')) {
            return Fees::query()->get();
        }

        if (!Schema::hasTable('company_fee_settings')) {
            return data_get($operatorContext, 'dispatch_mode') === 'hybrid'
                ? Fees::query()->get()
                : collect();
        }

        $companyFees = CompanyFeeSetting::query()
            ->active()
            ->where('company_id', $operatorContext['company_id'])
            ->get();

        if ($companyFees->isNotEmpty()) {
            return $companyFees;
        }

        if (data_get($operatorContext, 'dispatch_mode') === 'hybrid') {
            return Fees::query()->get();
        }

        return collect();
    }
}
