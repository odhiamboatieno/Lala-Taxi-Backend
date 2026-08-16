<?php

/**
 * Company Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Company
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Company extends Authenticatable
{
    use Notifiable;

    protected $guard = 'company';

    protected $table = 'companies';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'dispatch_settings' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'submitted_for_approval_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['first_name'];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Set password Attribute
     *
     */
    public function setPasswordAttribute($input)
    {
        $this->attributes['password'] = bcrypt($input);
    }

    /**
     * Scope to get Active Records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Join with company documents table
     *
     */
    public function company_document()
    {
        return $this->hasOne(\App\Models\CompanyDocuments::class,'company_id','id');
    }

    /**
     * Join with Company Payout Credentials table
     *
     */
    public function payout_credentials()
    {
        return $this->hasMany(\App\Models\CompanyPayoutCredentials::class,'company_id','id');
    }

    // Return the drivers default payout credential details
    public function default_payout_credentials()
    {
        $payout_methods = getPayoutMethods();

        $payout_methods = array_map(function($value) {
            return snakeToCamel($value,true);
        }, $payout_methods);
        return $this->hasOne(\App\Models\CompanyPayoutCredentials::class)->whereIn('type',$payout_methods)->where('default','yes');
    }

    /**
     * Join with drivers table
     *
     */
    public function drivers()
    {
        return $this->hasMany(\App\Models\User::class,'company_id','id');
    }

    public function driver_fee_setting()
    {
        return $this->hasOne(\App\Models\CompanyDriverFeeSetting::class, 'company_id', 'id');
    }

    public function saas_setting()
    {
        return $this->hasOne(\Modules\SaaSDispatch\Entities\CompanySaasSetting::class, 'company_id', 'id');
    }

    public function service_areas()
    {
        return $this->hasMany(\Modules\SaaSDispatch\Entities\CompanyServiceArea::class, 'company_id', 'id');
    }

    public function vehicle_types()
    {
        return $this->hasMany(\Modules\SaaSDispatch\Entities\CompanyVehicleType::class, 'company_id', 'id');
    }

    public function fare_rules()
    {
        return $this->hasMany(\Modules\SaaSDispatch\Entities\CompanyFareRule::class, 'company_id', 'id');
    }

    public function fee_settings()
    {
        return $this->hasMany(\Modules\SaaSDispatch\Entities\CompanyFeeSetting::class, 'company_id', 'id');
    }

    public function dispatch_setting()
    {
        return $this->hasOne(\Modules\SaaSDispatch\Entities\CompanyDispatchSetting::class, 'company_id', 'id');
    }

    public function payment_gateways()
    {
        return $this->hasMany(\Modules\SaaSDispatch\Entities\CompanyPaymentGateway::class, 'company_id', 'id');
    }

    /**
     * Get first name attribute
     *
     */
    public function getFirstNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get Last name attribute
     *
     */
    public function getLastNameAttribute()
    {
        $name_array = explode(' ', $this->name);
        return end($name_array);
    }

    // Get Mobile number with Protected format
    public function getHiddenMobileNumberAttribute()
    {
        return $this->attributes['mobile_number'] ?? '-';
    }

    // get driver applied owe amount
    public function getTotalOweAmountAttribute()
    {
        return Trips::CompanyTripsOnly($this->id)->sum('owe_amount');
    }

    // get driver applied owe amount
    public function getAppliedOweAmountAttribute()
    {
        $company_id = $this->id;
        $company_owe = Trips::CompanyTripsOnly($company_id)->sum('applied_owe_amount');
        $driver_owe_amount_paymnt = DriverOweAmountPayment::whereHas('user',function($q) use ($company_id){
            $q->where('company_id',$company_id);
        })->sum('amount');

        $applied_owe_amount = $company_owe + $driver_owe_amount_paymnt;
        return $applied_owe_amount;
    }

    // get driver applied owe amount
    public function getRemainingOweAmountAttribute()
    {
        $company_id = $this->id;
        $company_driver_owe_amount = DriverOweAmount::whereHas('user',function($q) use ($company_id){
            $q->where('company_id',$company_id);
        })->sum('amount');
        return $company_driver_owe_amount;
    }

    // Add referral amount to wallet
    public function addAmountToWallet($to_user,$user_type,$currency_code,$wallet_amount)
    {
        $wallet = Wallet::where('user_id', $to_user)->first();
        if(isset($wallet)) {
            $amount = $this->currency_convert($currency_code,$wallet->getOriginal('currency_code'),$wallet_amount);
            $final_wallet = intval($wallet->getOriginal('amount')) + intval($amount);
            $wallet->amount = $final_wallet;
        }
        else {
            $wallet = new Wallet;
            $wallet->user_id = $to_user;
            $wallet->amount = $wallet_amount;
            $wallet->currency_code = $currency_code;
        }
        $wallet->save();

        $this->completeReferral($to_user);
        return '';
    }

    public function getEnvMobileNumberAttribute()
    {
        return $this->attributes['mobile_number'] ?? '';
    }

    public function getEmailAttribute()
    {
        return $this->attributes['email'] ?? '';
    }

    // phone number restrictions
    public function getMobileNumberAttribute()
    {
        return $this->attributes['mobile_number'] ?? '';
    }
}
