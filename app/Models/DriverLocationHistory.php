<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLocationHistory extends Model
{
    protected $table = 'driver_locations';

    protected $guarded = [];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id');
    }
}
