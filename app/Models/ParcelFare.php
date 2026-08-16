<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelFare extends Model
{
    protected $table = 'parcel_fares';

    protected $fillable = [
        'parcel_category_id', 'car_type_id', 'location_id',
        'base_fare', 'per_km_rate', 'min_fare', 'is_active',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'min_fare' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function parcelCategory(): BelongsTo
    {
        return $this->belongsTo(ParcelCategory::class, 'parcel_category_id');
    }

    public function carType(): BelongsTo
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public static function findByCategoryAndVehicle(int $categoryId, int $carTypeId, ?int $locationId = null)
    {
        $query = self::where('parcel_category_id', $categoryId)
            ->where('car_type_id', $carTypeId)
            ->where('is_active', true);

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                    ->orWhereNull('location_id');
            });
        } else {
            $query->whereNull('location_id');
        }

        return $query->orderByRaw('location_id IS NULL')->first();
    }
}
