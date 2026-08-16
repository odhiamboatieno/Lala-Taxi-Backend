<?php

namespace Modules\SurgePricing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgePricingSlot extends Model
{
    protected $table = 'surge_pricing_slots';

    protected $guarded = [];

    protected $casts = [
        'slot_date' => 'date',
    ];

    public function surgePricing(): BelongsTo
    {
        return $this->belongsTo(SurgePricing::class, 'surge_pricing_id');
    }
}
