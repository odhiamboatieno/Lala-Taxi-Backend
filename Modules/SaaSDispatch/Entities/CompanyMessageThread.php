<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyMessageThread extends Model
{
    protected $fillable = [
        'company_id',
        'subject',
        'participant_type',
        'participant_id',
        'status',
        'last_message_at',
        'green_flash',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'green_flash' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function messages()
    {
        return $this->hasMany(CompanyMessage::class, 'thread_id');
    }
}
