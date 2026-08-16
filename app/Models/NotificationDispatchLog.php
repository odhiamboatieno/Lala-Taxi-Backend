<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDispatchLog extends Model
{
    protected $table = 'notification_dispatch_logs';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(NotificationCampaign::class, 'campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }
}
