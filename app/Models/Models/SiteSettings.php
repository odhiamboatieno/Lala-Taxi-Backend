<?php

/**
 * Site Settings Model
 *
 * @package     LalaTaxi
 * @subpackage  Model
 * @category    Site Settings
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'site_settings';

    protected $fillable = [
        'name',
        'value',
    ];

    public $timestamps = false;
}
