<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyVehicleType extends Model
{
    protected $fillable = [
        'company_id',
        'global_vehicle_category_id',
        'name',
        'slug',
        'description',
        'is_pool',
        'icon',
        'vehicle_image',
        'active_image',
        'app_image',
        'passenger_capacity',
        'luggage_capacity',
        'is_ev',
        'is_accessible',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_ev' => 'boolean',
        'is_accessible' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function globalVehicleCategory()
    {
        return $this->belongsTo(GlobalVehicleCategory::class, 'global_vehicle_category_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
