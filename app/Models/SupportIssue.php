<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportIssue extends Model
{
    protected $table = 'support_issues';

    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(SupportIssueMessage::class, 'support_issue_id', 'id');
    }
}
