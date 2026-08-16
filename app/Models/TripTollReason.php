<?php
/**
 * Language Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    TollReason
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripTollReason extends Model
{
    
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['trip_id','reason'];
    
}