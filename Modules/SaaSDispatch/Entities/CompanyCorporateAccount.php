<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyCorporateAccount extends Model
{
    protected $fillable = [
        'company_id',
        'account_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'billing_cycle',
        'credit_limit',
        'status',
        'notes',
        'currency_code', 'billing_timezone', 'payment_terms_days', 'legal_name', 'billing_address',
        'archived_at', 'archived_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'archived_at' => 'datetime',
        'payment_terms_days' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function invoices()
    {
        return $this->hasMany(CompanyCorporateInvoice::class, 'corporate_account_id');
    }

    public function members()
    {
        return $this->hasMany(\App\Models\User::class, 'corporate_account_id');
    }

    public function portalUsers()
    {
        return $this->hasMany(\App\Models\CorporatePortalUser::class, 'corporate_account_id');
    }

    public function trips()
    {
        return $this->hasMany(\App\Models\Trips::class, 'corporate_account_id');
    }

    public function audits()
    {
        return $this->hasMany(CompanyCorporateAudit::class, 'corporate_account_id');
    }

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('Archive corporate accounts instead of deleting them.'));
    }
}
