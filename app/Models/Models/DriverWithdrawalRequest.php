<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverWithdrawalRequest extends Model
{
    protected $fillable = [
        'driver_id',
        'payout_credential_id',
        'amount',
        'currency_code',
        'status',
        'note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function payoutCredential()
    {
        return $this->belongsTo(PayoutCredentials::class, 'payout_credential_id');
    }
}
