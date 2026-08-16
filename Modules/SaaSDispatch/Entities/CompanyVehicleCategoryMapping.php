<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyVehicleCategoryMapping extends Model
{
    protected $fillable = [
        'company_id',
        'global_vehicle_category_id',
        'company_vehicle_type_id',
        'priority',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function globalVehicleCategory()
    {
        return $this->belongsTo(GlobalVehicleCategory::class, 'global_vehicle_category_id', 'id');
    }

    public function companyVehicleType()
    {
        return $this->belongsTo(CompanyVehicleType::class, 'company_vehicle_type_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
