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
    ];

    protected $casts = [
        'credit_limit' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function invoices()
    {
        return $this->hasMany(CompanyCorporateInvoice::class, 'corporate_account_id');
    }
}
