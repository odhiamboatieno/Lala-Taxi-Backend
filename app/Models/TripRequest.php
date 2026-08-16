<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRequest extends Model
{
    protected $table = 'trip_requests';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
