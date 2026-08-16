<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class GlobalVehicleCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_visible_to_rider',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_visible_to_rider' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
