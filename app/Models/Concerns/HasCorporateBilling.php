<?php

namespace App\Models\Concerns;

use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

trait HasCorporateBilling
{
    public static function bootHasCorporateBilling(): void
    {
        static::creating(fn ($model) => app(\App\Services\Corporate\CorporateBillingContext::class)->prepareCreation($model));
        static::updating(fn ($model) => app(\App\Services\Corporate\CorporateBillingContext::class)->guardMutation($model));
        static::created(function ($model) {
            if ($model instanceof \App\Models\Trips) {
                app(\App\Services\Corporate\CorporateBillingContext::class)->recordTrip($model);
            }
        });
    }

    public function initializeHasCorporateBilling(): void
    {
        $this->mergeCasts([
            'billing_snapshot' => 'array',
            'billing_finalized_at' => \App\Casts\UtcDateTime::class,
            'corporate_settled_at' => \App\Casts\UtcDateTime::class,
            'corporate_amount' => 'decimal:2',
            'corporate_financial_snapshot' => 'array',
        ]);
        $this->makeHidden('billing_snapshot');
        $this->makeHidden('corporate_financial_snapshot');
        $this->append('billing');
    }

    public function corporate_account()
    {
        return $this->belongsTo(CompanyCorporateAccount::class, 'corporate_account_id');
    }

    public function isCorporate(): bool
    {
        return $this->billing_mode === 'corporate';
    }

    public function getBillingAttribute(): array
    {
        return app(\App\Services\Corporate\CorporateBillingContext::class)->summary($this);
    }
}
