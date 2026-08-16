<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class SaaSFeatureModule extends Model
{
    protected $table = 'saas_feature_modules';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_installed',
        'is_enabled',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_installed' => 'boolean',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
