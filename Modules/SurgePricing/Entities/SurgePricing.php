<?php

namespace Modules\SurgePricing\Entities;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurgePricing extends Model
{
    protected $table = 'surge_pricings';

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'ride_rate_by_vehicle' => 'array',
        'weekdays' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'surge_pricing_locations', 'surge_pricing_id', 'location_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SurgePricingSlot::class, 'surge_pricing_id');
    }
}
