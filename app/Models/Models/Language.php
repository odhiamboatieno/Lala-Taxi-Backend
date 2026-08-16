<?php
/**
 * Language Model
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

class Language extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'language';

    public $timestamps = false;

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
