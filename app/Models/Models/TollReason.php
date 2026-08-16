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

class TollReason extends Model
{
    
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reason','status'];

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
