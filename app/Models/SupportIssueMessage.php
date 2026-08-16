<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportIssueMessage extends Model
{
    protected $table = 'support_issue_messages';

    protected $guarded = [];

    public function issue()
    {
        return $this->belongsTo(SupportIssue::class, 'support_issue_id', 'id');
    }
}
