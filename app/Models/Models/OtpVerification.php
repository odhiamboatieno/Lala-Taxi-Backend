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
        'expires_at',
        'verified_at',
    ];

    protected $dates = [
        'expires_at',
        'verified_at',
        'created_at',
        'updated_at',
    ];
}
