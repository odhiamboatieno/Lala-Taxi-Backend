<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyNetworkPartner extends Model
{
    protected $fillable = [
        'company_id',
        'partner_company_id',
        'status',
        'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function partnerCompany()
    {
        return $this->belongsTo(Company::class, 'partner_company_id');
    }
}
