<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyPeakFareDetail extends Model
{
    protected $fillable = [
        'company_fare_rule_id',
        'day',
        'start_time',
        'end_time',
        'price',
        'type',
    ];

    public function fareRule()
    {
        return $this->belongsTo(CompanyFareRule::class, 'company_fare_rule_id', 'id');
    }
}
