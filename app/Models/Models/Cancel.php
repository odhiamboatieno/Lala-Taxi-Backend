<?php

/**
 * Cancel Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Cancel
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cancel';

    protected $fillable = ['user_id','trip_id','cancel_reason_id','cancel_comments','cancelled_by'];

    /**
     * Join With Trip Table
     *
     */
    public function trip()
    {
    	return $this->hasOne(\App\Models\Trips::class,'id','trip_id');
    }

    /**
     * Join With Cancel Reson Table
     *
     */
    public function cancel_reason()
    {
        return $this->hasOne(\App\Models\CancelReason::class,'id','cancel_reason_id');
    }
   
}
