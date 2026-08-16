<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelTask extends Model
{
    protected $table = 'parcel_tasks';

    protected $fillable = [
        'key', 'title', 'icon_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
