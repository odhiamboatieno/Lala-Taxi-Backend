<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverOnlineSession extends Model
{
    protected $fillable = [
        'driver_id',
        'started_at',
        'ended_at',
        'total_seconds',
        'status',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'app_version',
        'device_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'start_lat' => 'float',
        'start_lng' => 'float',
        'end_lat' => 'float',
        'end_lng' => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
