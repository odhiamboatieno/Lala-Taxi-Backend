<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyFeeSetting extends Model
{
    protected $fillable = [
        'company_id',
        'fee_name',
        'fee_type',
        'fee_value',
        'applies_to',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
