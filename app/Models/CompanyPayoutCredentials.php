<?php

/**
 * Company Payout Credentials Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Company Payout Credentials
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayoutCredentials extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'type'];
    
    // Return the companies default payout_preference details
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class,'company_id','id');
    }

    // Return the company default payout_preference details
    public function company_payout_preference()
    {
        return $this->belongsTo(\App\Models\CompanyPayoutPreference::class,'preference_id','id');
    }
}