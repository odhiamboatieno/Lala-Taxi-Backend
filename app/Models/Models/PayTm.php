<?php

/**
 * ApiCredential Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    ApiCredential
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayTm extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'paytm_payments';

    public $timestamps = false;
}
