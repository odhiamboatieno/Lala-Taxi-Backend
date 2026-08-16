<?php

/**
 * Help Category Lang Model
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Help Category Lang
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class HelpCategoryLang extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'help_category_lang';

    public $timestamps = false;

    protected $fillable = ['name', 'description'];

    public function language() {
        return $this->belongsTo(\App\Models\Language::class,'locale','value');
    }
}
