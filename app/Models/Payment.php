<?php

/**
 * Payment Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Payment
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment';

    protected $fillable = ['trip_id', 'correlation_id', 'admin_transaction_id','driver_transaction_id','driver_payout_status','admin_payout_status'];

    public $timestamps = false;
}
