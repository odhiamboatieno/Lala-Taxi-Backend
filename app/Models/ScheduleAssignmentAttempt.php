<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleAssignmentAttempt extends Model
{
    protected $table = 'schedule_assignment_attempts';

    protected $fillable = [
        'schedule_ride_id',
        'driver_id',
        'company_id',
        'dispatcher_id',
        'dispatch_mode',
        'status',
        'offered_at',
        'accepted_at',
        'rejected_at',
        'timed_out_at',
        'rejection_reason',
        'timeout_seconds',
        'sequence_order',
        'metadata',
    ];

    protected $casts = [
        'offered_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'timed_out_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scheduleRide()
    {
        return $this->belongsTo(ScheduleRide::class, 'schedule_ride_id');
    }
}
