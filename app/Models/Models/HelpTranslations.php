<?php

/**
 * Help Translations Model
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Help Translations
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpTranslations extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description'];
    
    public function language() {
    	return $this->belongsTo(\App\Models\Language::class,'locale','value');
    }
}
