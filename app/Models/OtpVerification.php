<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $table = 'otp_verifications';

    protected $fillable = [
        'mobile_number',
        'country_code',
        'otp',
        'purpose',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    // $dates was removed from Eloquent in Laravel 11, so these columns need to
    // be cast explicitly for expires_at comparisons to work on a Carbon value.
    protected $casts = [
        'attempts'     => 'integer',
        'expires_at'   => 'datetime',
        'verified_at'  => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
}
