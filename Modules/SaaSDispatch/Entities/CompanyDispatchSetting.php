<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyDispatchSetting extends Model
{
    protected $fillable = [
        'company_id',
        'dispatch_strategy',
        'driver_search_radius',
        'max_driver_search_radius',
        'request_timeout_seconds',
        'retry_rounds',
        'allow_auto_dispatch',
        'allow_manual_dispatch',
        'allow_scheduled_trips',
        'allow_airport_trips',
        'allow_out_of_area_trips',
        'status',
    ];

    protected $casts = [
        'allow_auto_dispatch' => 'boolean',
        'allow_manual_dispatch' => 'boolean',
        'allow_scheduled_trips' => 'boolean',
        'allow_airport_trips' => 'boolean',
        'allow_out_of_area_trips' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
