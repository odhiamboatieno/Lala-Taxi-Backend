<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverScore extends Model
{
    protected $table = 'driver_scores';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'last_calculated_at' => 'datetime',
        'next_calculation_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(DriverScoreEvent::class, 'driver_score_id', 'id');
    }

    public function appeals()
    {
        return $this->hasMany(DriverScoreAppeal::class, 'driver_score_id', 'id');
    }
}
