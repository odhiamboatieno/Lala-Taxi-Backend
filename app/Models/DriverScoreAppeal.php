<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverScoreAppeal extends Model
{
    protected $table = 'driver_score_appeals';

    protected $guarded = [];

    protected $casts = [
        'resolution_metadata' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function score()
    {
        return $this->belongsTo(DriverScore::class, 'driver_score_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo(DriverScoreEvent::class, 'driver_score_event_id', 'id');
    }
}
