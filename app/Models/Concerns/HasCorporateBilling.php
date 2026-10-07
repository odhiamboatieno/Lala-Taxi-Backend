<?php

namespace App\Models\Concerns;

use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

trait HasCorporateBilling
{
    public function initializeHasCorporateBilling(): void
    {
        $this->mergeCasts([
            'billing_snapshot' => 'array',
            'billing_finalized_at' => 'datetime',
            'corporate_settled_at' => 'datetime',
            'corporate_amount' => 'decimal:2',
        ]);
        $this->makeHidden('billing_snapshot');
    }

    public function corporate_account()
    {
        return $this->belongsTo(CompanyCorporateAccount::class, 'corporate_account_id');
    }

    public function isCorporate(): bool
    {
        return $this->billing_mode === 'corporate';
    }
}
