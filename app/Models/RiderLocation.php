<?php

/**
 * Rider Location Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Rider Location
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderLocation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rider_location';

    public $timestamps = false;

    protected $guarded = [];
}