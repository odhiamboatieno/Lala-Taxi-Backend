<?php

/**
 * Applied Referrals Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Applied Referrals
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppliedReferrals extends Model
{
    use CurrencyConversion;

    public $timestamps = true;

    public $convert_fields = ['amount'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','amount','currency_code'];    
}
