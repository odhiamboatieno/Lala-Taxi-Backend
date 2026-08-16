<?php

/**
 * Driver Address Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Driver Address
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverAddress extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'driver_address';

    public $timestamps = false;
}