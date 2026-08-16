<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverScoreEvent extends Model
{
    protected $table = 'driver_score_events';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'weight_snapshot' => 'array',
        'event_date' => 'datetime',
        'admin_confirmed_at' => 'datetime',
        'excluded_from_scoring' => 'boolean',
        'requires_admin_confirmation' => 'boolean',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function score()
    {
        return $this->belongsTo(DriverScore::class, 'driver_score_id', 'id');
    }

    public function supportIssue()
    {
        return $this->belongsTo(SupportIssue::class, 'support_issue_id', 'id');
    }

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id', 'id');
    }

    public function appeals()
    {
        return $this->hasMany(DriverScoreAppeal::class, 'driver_score_event_id', 'id');
    }
}
