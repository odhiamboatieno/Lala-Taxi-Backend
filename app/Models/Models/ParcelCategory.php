<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParcelCategory extends Model
{
    protected $table = 'parcel_categories';

    protected $fillable = [
        'key', 'title', 'description', 'icon_path',
        'base_fare', 'multiplier', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function fares(): HasMany
    {
        return $this->hasMany(ParcelFare::class, 'parcel_category_id');
    }
}
