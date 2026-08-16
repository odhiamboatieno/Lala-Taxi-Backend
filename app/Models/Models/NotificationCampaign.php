<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCampaign extends Model
{
    protected $table = 'notification_campaigns';

    protected $guarded = [];

    protected $casts = [
        'recipient_ids' => 'array',
        'scheduled_for' => 'datetime',
        'next_run_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function dispatch_logs()
    {
        return $this->hasMany(NotificationDispatchLog::class, 'campaign_id');
    }
}
