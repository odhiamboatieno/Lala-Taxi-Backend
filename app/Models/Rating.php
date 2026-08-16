<?php

/**
 * Rating Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Rating
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rating';

    protected $fillable = ['user_id','trip_id','driver_id','rater_id','rater_type','rider_rating','rider_comments','driver_rating','driver_comments'];

    // Join with user table for rider
    public function rider()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id','id');
    }

    // Join with user table for driver
    public function driver()
    {
        return $this->belongsTo(\App\Models\User::class,'driver_id','id');
    }

    // Join with trip table
    public function trip()
    {
        return $this->belongsTo(\App\Models\Trips::class,'trip_id','id');
    }   
}
