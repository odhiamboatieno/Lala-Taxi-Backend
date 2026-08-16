<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class SaaSSupportTicket extends Model
{
    protected $table = 'saas_support_tickets';

    protected $fillable = [
        'company_id',
        'created_by_type',
        'created_by_id',
        'subject',
        'category',
        'priority',
        'status',
        'description',
        'assigned_admin_id',
        'last_message_at',
        'resolved_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function messages()
    {
        return $this->hasMany(SaaSSupportTicketMessage::class, 'ticket_id');
    }
}
