<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverFeeSetting extends Model
{
    protected $table = 'company_driver_fee_settings';

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
