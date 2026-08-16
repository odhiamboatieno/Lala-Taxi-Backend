<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualBookingTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'dispatcher_type',
        'dispatcher_id',
        'name',
        'description',
        'sharing_scope',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
