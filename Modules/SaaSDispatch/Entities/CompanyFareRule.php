<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyFareRule extends Model
{
    protected $fillable = [
        'company_id',
        'company_vehicle_type_id',
        'company_service_area_id',
        'base_fare',
        'capacity',
        'per_km',
        'schedule_fare',
        'currency_code',
        'apply_peak',
        'apply_night',
        'waiting_time',
        'waiting_charge',
        'per_minute',
        'minimum_fare',
        'waiting_charge_per_minute',
        'cancellation_fee',
        'night_surcharge_type',
        'night_surcharge_value',
        'airport_fee',
        'commission_type',
        'commission_value',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(CompanyVehicleType::class, 'company_vehicle_type_id', 'id');
    }

    public function serviceArea()
    {
        return $this->belongsTo(CompanyServiceArea::class, 'company_service_area_id', 'id');
    }

    public function peakFareDetails()
    {
        return $this->hasMany(CompanyPeakFareDetail::class, 'company_fare_rule_id', 'id');
    }

    public function nightFareDetail()
    {
        return $this->hasOne(CompanyPeakFareDetail::class, 'company_fare_rule_id', 'id')->where('type', 'night');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
