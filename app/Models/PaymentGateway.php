<?php

/**
 * Payment Gateway Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Payment Gateway
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_gateway';

    public $timestamps = false;
}
