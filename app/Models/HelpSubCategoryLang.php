<?php

/**
 * Help Sub Category Lang Model
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Help Sub Category Lang
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class HelpSubCategoryLang extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'help_sub_category_lang';

    protected $fillable = ['name', 'description'];

    public $timestamps = false;

    public function language() {
        return $this->belongsTo(\App\Models\Language::class,'locale','value');
    }
}
