<?php

/**
 * OTP Helper
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Helper
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */
namespace App\Http\Helper;

class OtpHelper
{
	/**
	 * Send OTP
	 *
	 * @param integer $country_code
	 * @param integer $mobile_number
	 * @return Array $response
	 */
	public function sendOtp($mobile_number, $country_code)
	{
		return resolve(\App\Services\Otp\OtpManager::class)->sendOtp($mobile_number, $country_code);
	}

	/**
	 * Resend OTP
	 *
	 * @return Array $response
	 */
	public function resendOtp()
	{
		return resolve(\App\Services\Otp\OtpManager::class)->resendOtp();
	}

	/**
	 * Check Given OTP
	 *
	 * @param integer $otp
	 * @param integer $mobile_number
	 * @return Array $response
	 */
	public function checkOtp($otp, $mobile_number = null, $country_code = '')
	{
		return resolve(\App\Services\Otp\OtpManager::class)->checkOtp($otp, $mobile_number, $country_code);
	}
}
