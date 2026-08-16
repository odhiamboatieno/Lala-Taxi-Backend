<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyMessage extends Model
{
    protected $fillable = [
        'thread_id',
        'sender_type',
        'sender_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function thread()
    {
        return $this->belongsTo(CompanyMessageThread::class, 'thread_id');
    }
}
