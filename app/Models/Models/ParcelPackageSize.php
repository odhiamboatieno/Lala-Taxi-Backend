<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelPackageSize extends Model
{
    protected $table = 'parcel_package_sizes';

    protected $fillable = [
        'key', 'title', 'subtitle', 'description',
        'price_multiplier', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
