<?php

/**
 * Send sms via Twillio
 *
 * @package     LalaTaxi
 * @subpackage  Services
 * @category    Auth Service
 * @author      Wilaso
 * @version     2.2
 * @link        https://Wilaso.com
*/

namespace App\Services\SMS;

use Illuminate\Http\Request;
use App\Contracts\SMSInterface;
use Twilio\Rest\Client;


class TwillioSms implements SMSInterface
{
	private $client,$verify;

	

		/**
     * Initialize Twillo credentials
     *
     * @return void
     */
	public function initialize()
	{
		// Twilio throws a TypeError (not an Exception) when the credentials are
		// missing, which escaped the catch(\Exception) blocks below and turned a
		// send failure into an opaque 500. Fail with a normal result instead.
		$sid    = api_credentials('sid','Twillo');
		$token  = api_credentials('token','Twillo');
		$from   = api_credentials('from','Twillo');
		$svcSid = api_credentials('service_sid','Twillo');

		if (!$sid || !$token) {
			throw new \RuntimeException('Twilio is not configured: missing API credentials.');
		}

		$this->client = new Client($sid, $token);

		if ($svcSid) {
			$this->verify = $this->client->verify->v2->services($svcSid);
		}
	}

	/**
     * Send OTP message
     *
     * @param String $phone_number
     * @return Array SMS Response
     */
	protected function sendOTP($phone_number)
	{
		try {
			$data = $this->verify->verifications->create($phone_number, "sms");
			return ['status_code' => 1,'message'=>'Success','status'=>true];
		} 
		catch(\Throwable $e) {
			return ['status_code' => 0,'message'=>$e->getMessage() ,'status'=>false];
		}
	}

	/**
     * Verify OTP number
     *
     * @param String $phone_number, String $verification_code
     * @return Array SMS Response
     */
	protected function verifyOTP($phone_number, $verification_code)
	{
		try {

			$verification = $this->verify->verificationChecks->create($verification_code, ['to'=>$phone_number]);

			if($verification->valid)
				return ['status_code' => 1,'message'=>'Success','status'=>true];
			else 
				return ['status_code' => 0,'message'=>__('messages.signup.wrong_otp'),'status'=>false];
		} 
		catch(\Throwable $e) {
			return ['status_code' => 0,'message'=>__('messages.signup.wrong_otp'),'status'=>false];
		}
	}

	/**
     * Send Text message to mobile
     *
     * @param String $phone_number, String $verification_code
     * @return Array SMS Response
     */
	public function send($phone_number, $text='',$verification_code=false)
	{
		// initialize() and the SDK both throw \Throwable (TypeError included),
		// so catch broadly or a misconfigured gateway becomes a 500.
		try {
			$this->initialize();
		}
		catch(\Throwable $e) {
			return ['status_code' => 0, 'message' => $e->getMessage(), 'status' => false];
		}

		if(!$text && !$verification_code && !$this->verify) {
			return ['status_code' => 0, 'message' => 'Twilio Verify service is not configured.', 'status' => false];
		}

		if($text){
			$result = $this->SendTextMessage($phone_number,$text);
		}
		else if($verification_code) {
			$result = $this->verifyOTP($phone_number, $verification_code);
		} else {
			$result = $this->sendOTP($phone_number);
		}
		return $result;
	}
	/**
     * Send Text message to mobile
     *
     * @param String $[to] user phone number
     * @param String $[text] [message to be send]
     * @return Array SMS Response
     */
	public function SendTextMessage($to,$text)
	{
		try {
			// Use the client to do fun stuff like send text messages!
			$this->client->messages->create(
			    // the number you'd like to send the message to
			    $to,
			    [
			        // A Twilio phone number you purchased at twilio.com/console
			        'from' => api_credentials('from','Twillo'),
			        // the body of the text message you'd like to send
			        'body' => $text
			    ]
			);
			return ['status_code' => 1,'message'=>'Success','status'=>true];
		} 

		catch(\Throwable $e) {
			return ['status_code' => 0,'message'=>$e->getMessage(),'status'=>false];
		}
		return ['status_code' => 1,'message'=>'Success','status'=>true];

	}
}
