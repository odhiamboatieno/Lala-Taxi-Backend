<?php

namespace Modules\SaaSDispatch\Entities;

use Illuminate\Database\Eloquent\Model;

class SaaSSupportTicketMessage extends Model
{
    protected $table = 'saas_support_ticket_messages';

    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SaaSSupportTicket::class, 'ticket_id');
    }
}
