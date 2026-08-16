<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyVerificationOtp extends Model
{
    protected $table = 'company_verification_otps';

    protected $guarded = [];

    protected $dates = [
        'expires_at',
        'verified_at',
        'created_at',
        'updated_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
