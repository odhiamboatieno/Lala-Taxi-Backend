<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualBookingDraft extends Model
{
    protected $fillable = [
        'company_id',
        'dispatcher_type',
        'dispatcher_id',
        'name',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
