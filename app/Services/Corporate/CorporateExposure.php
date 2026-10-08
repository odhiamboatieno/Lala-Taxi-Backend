<?php

namespace App\Services\Corporate;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class CorporateExposure
{
    public function summary(CompanyCorporateAccount $account): array
    {
        $charges = DB::table('trips')->where('corporate_account_id', $account->id)->where('billing_mode', 'corporate')
            ->whereNotNull('billing_finalized_at')->whereIn('billing_status', ['unbilled', 'invoiced']);
        if ((clone $charges)->where(fn ($query) => $query->whereNull('corporate_currency_code')->orWhere('corporate_currency_code', '!=', $account->currency_code))->exists()) {
            throw ValidationException::withMessages(['account' => __('corporate.invoice_inconsistent')]);
        }
        $exposure = CorporateMoney::aggregate($charges->orderBy('id')->select('id', 'corporate_amount')->lazyById(500)->pluck('corporate_amount'));
        $limit = CorporateMoney::decimal(CorporateMoney::minor($account->credit_limit ?? '0.00'));
        $configured = $limit !== '0.00';
        $over = $configured && (strlen($exposure) > strlen($limit) || (strlen($exposure) === strlen($limit) && strcmp($exposure, $limit) > 0));

        return ['currency_code' => $account->currency_code, 'outstanding_amount' => $exposure, 'credit_limit' => $limit,
            'credit_limit_configured' => $configured, 'over_credit_limit' => $over, 'credit_policy' => 'warn_only',
            'remaining_credit' => !$configured ? null : ($over ? '0.00' : CorporateMoney::decimal(CorporateMoney::minor($limit) - CorporateMoney::minor($exposure)))];
    }
}
