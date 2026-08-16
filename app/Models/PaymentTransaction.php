<?php

/**
 * PaymentMethod Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    PaymentMethod
 * @author      Wilaso
 * @version     1.7
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_transactions';

   
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    
}
