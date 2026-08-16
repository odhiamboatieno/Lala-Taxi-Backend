<?php

/**
 * Payout Preference Model
 *
 * @package     Witaxi
 * @subpackage  Model
 * @category    Payout Preference
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeZone;
use DateTime;
use Config;

class PayoutPreference extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'payout_preference';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'payout_method'];

	public $appends = ['updated_time', 'updated_date','account_holder_name'];

	// Join with users table
	public function users()
	{
		return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
	}

	// Get Updated time for Payout Information
	public function getUpdatedTimeAttribute()
	{
		if (request()->segment(1) == 'api') {
			$date = PHP_DATE_FORMAT;
			$datemonth = date($date, strtotime($this->attributes['updated_at']));
			return $datemonth . ' at ' . date('H:i', strtotime($date));
		}

		$new_str = new DateTime($this->attributes['updated_at'], new DateTimeZone(Config::get('app.timezone')));

       	$new_str->setTimeZone(new DateTimeZone('Asia/Kolkata'));

       	$datemonth = date(PHP_DATE_FORMAT, strtotime($this->attributes['updated_at']));
       	return $datemonth;
	}

	// Get Updated date for Payout Information
	public function getUpdatedDateAttribute()
	{
		if (request()->segment(1) == 'api') {
			$date = PHP_DATE_FORMAT;
			$datemonth = date($date, strtotime($this->attributes['updated_at']));
			return $datemonth;
		}

		$new_str = new DateTime($this->attributes['updated_at'], new DateTimeZone(Config::get('app.timezone')));

        $new_str->setTimeZone(new DateTimeZone('Asia/Kolkata'));

        $datemonth = date(PHP_DATE_FORMAT, strtotime($this->attributes['updated_at']));
        return $datemonth;
	}

	public function getDocumentAttribute()
	{
		$image_name = $this->attributes['document_image'];
		$user_id = $this->attributes['user_id'];
		$url = url('images/users/' . $user_id . '/uploads/' . $image_name);
		return $url;
	}

	//account_holder_name
	public function getAccountHolderNameAttribute()
	{
		return  $this->attributes['holder_name'];
	}

	// get mandatory field for create stripe token
	public static function getMandatory($country = 'US')
	{
		$mandatory = [];
		$mandatory['AT'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['AU'] = ['bsb' => 'required','account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['BE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['CA'] = ['transit_number' => 'required','account_number' => 'required','institution_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['GB'] = ['sort_code' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['HK'] = ['clearing_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['JP'] = ['bank_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'bank_name' => 'required', 'branch_name' => 'required', 'account_owner_name' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['BR'] = ['bank_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['NZ'] = ['routing_number' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['SG'] = ['bank_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['US'] = ['routing_number' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required', 'ssn_last_4' => 'required'];
		$mandatory['CH'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['DE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['DK'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['ES'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['FI'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['FR'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['IE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['IT'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['LU'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['NL'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['NO'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['PT'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['SE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['MX'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['OT'] = ['account_number' => 'required', 'bank_name' => 'required', '', 'account_holder_name' => 'required','branch_name' => 'required'];

		return @$mandatory[$country] ? @$mandatory[$country] : NULL;
	}

	// get mandatory field for create stripe token
	public static function getMandatoryField()
	{
		$mandatory['AU'] = ['bsb' => 'required','account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required', 'routing_number' => 'required'];
		$mandatory['CA'] = ['transit_number' => 'required','account_number' => 'required','institution_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required', 'routing_number' => 'required'];
		$mandatory['GB'] = ['sort_code' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['HK'] = ['clearing_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['JP'] = ['bank_code' => 'required','bank_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'bank_name' => 'required', 'branch_name' => 'required', 'account_owner_name' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['NZ'] = ['routing_number' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['SG'] = ['bank_code' => 'required', 'account_number' => 'required', 'branch_code' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['US'] = ['routing_number' => 'required', 'account_number' => 'required', 'account_holder_name' => 'required', 'currency' => 'required', 'ssn_last_4' => 'required'];
		$mandatory['AT'] = ['iban' => 'required','account_number'=>'required','account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['BE'] = ['iban' => 'required','account_holder_name' => 'required','currency' => 'required','account_number'=>'required'];
		$mandatory['CH'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['DE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number' => 'required'];
		$mandatory['DK'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number' => 'required'];
		$mandatory['ES'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['FI'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number' => 'required'];
		$mandatory['FR'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number' => 'required'];
		$mandatory['IE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['IT'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['LU'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['NL'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['NO'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['PT'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['SE'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required','account_number'=>'required'];
		$mandatory['MX'] = ['iban' => 'required', 'account_holder_name' => 'required', 'currency' => 'required'];
		$mandatory['OT'] = ['account_number' => 'required', 'bank_name' => 'required', 'account_holder_name' => 'required','branch_name' => 'required'];

		return $mandatory;
	}


	// get mandatory field for create stripe token
	public static function getAllMandatory()
	{
		$mandatory = [];
		$mandatory['AT'] = ['IBAN'];
		$mandatory['AU'] = ['BSB', 'Account Number'];
		$mandatory['BE'] = ['IBAN'];
		$mandatory['CA'] = ['Transit Number', 'Account Number', 'Institution Number'];
		$mandatory['GB'] = ['Sort Code', 'Account Number'];
		$mandatory['HK'] = ['Clearing Code', 'Account Number', 'Branch Code'];
		$mandatory['JP'] = ['Bank Code', 'Account Number', 'Branch Code', 'Bank Name', 'Branch Name', 'Account Owner Name '];
		$mandatory['NZ'] = ['Routing Number', 'Account Number'];
		$mandatory['SG'] = ['Bank Code', 'Account Number', 'Branch Code'];
		$mandatory['BR'] = ['Bank Code', 'Account Number', 'Branch Code'];
		$mandatory['US'] = ['Routing Number', 'Account Number'];
		$mandatory['CH'] = ['IBAN'];
		$mandatory['DE'] = ['IBAN'];
		$mandatory['DK'] = ['IBAN'];
		$mandatory['ES'] = ['IBAN'];
		$mandatory['FI'] = ['IBAN'];
		$mandatory['FR'] = ['IBAN'];
		$mandatory['IE'] = ['IBAN'];
		$mandatory['IT'] = ['IBAN'];
		$mandatory['LU'] = ['IBAN'];
		$mandatory['NL'] = ['IBAN'];
		$mandatory['NO'] = ['IBAN'];
		$mandatory['PT'] = ['IBAN'];
		$mandatory['SE'] = ['IBAN'];
		$mandatory['SE'] = ['IBAN'];
		$mandatory['MX'] = ['CLABE'];
		$mandatory['OT'] = ['Account Holder Name', 'Account Number','','Bank Name','Branch Name'];
		return $mandatory;
	}
}