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

class ApiCredentials extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_credentials';

    public $timestamps = false;
}
