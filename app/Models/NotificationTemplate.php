<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'notification_templates';

    protected $guarded = [];

    protected $casts = [
        'placeholders' => 'array',
        'push_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];
}
