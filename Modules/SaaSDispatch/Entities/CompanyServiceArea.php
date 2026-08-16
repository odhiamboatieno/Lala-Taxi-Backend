<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyServiceArea extends Model
{
    protected $appends = ['co_ordinates'];

    protected $fillable = [
        'company_id',
        'name',
        'country',
        'state',
        'city',
        'zone_name',
        'polygon',
        'pickup_enabled',
        'dropoff_enabled',
        'extra_fare_enabled',
        'extra_fare_percentage',
        'extra_fare_reason',
        'status',
    ];

    protected $casts = [
        'pickup_enabled' => 'boolean',
        'dropoff_enabled' => 'boolean',
        'extra_fare_enabled' => 'boolean',
        'extra_fare_percentage' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function getCoOrdinatesAttribute(): array
    {
        if (!$this->polygon) {
            return [];
        }

        $formattedCoordinates = [];
        $allCoordinates = explode('((', $this->polygon);

        if (!isset($allCoordinates[1])) {
            return [];
        }

        $coordinateData = str_replace(['))'], '', $allCoordinates[1]);
        $coordinateData = explode(',', $coordinateData);
        $returnValue = [];

        foreach ($coordinateData as $i => $coords) {
            $coord = explode(' ', trim($coords));
            $returnValue[$i]['lat'] = isset($coord[0]) ? (float) $coord[0] : 0.0;
            $returnValue[$i]['lng'] = isset($coord[1]) ? (float) $coord[1] : 0.0;
        }

        $formattedCoordinates[0] = $returnValue;

        return $formattedCoordinates;
    }
}
