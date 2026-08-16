<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Request as RideRequest;
use App\Models\Trips;
use Illuminate\Database\Eloquent\Model;

class CompanyNetworkTripShare extends Model
{
    protected $fillable = [
        'source_company_id',
        'target_company_id',
        'trip_id',
        'request_id',
        'status',
        'share_note',
        'response_note',
        'shared_at',
        'responded_at',
        'accepted_by_company_id',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trips::class, 'trip_id');
    }

    public function request()
    {
        return $this->belongsTo(RideRequest::class, 'request_id');
    }
}
