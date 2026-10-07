<?php

/**
 * Trip Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Trip
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Trips;
use App\Models\PayTm;
use App\Models\PaymentGateway;
use App\Models\PoolTrip;
use App\Models\DriverOweAmount;
use App\Models\ReferralUser;
use App\Models\AppliedReferrals;
use App\Models\DriverOweAmountPayment;
use App\Http\Helper\InvoiceHelper;
use App\Http\Helper\RequestHelper;
use Session;
class PaymentController extends Controller
{
    public function __construct(InvoiceHelper $invoice_helper)
	{
		$this->invoice_helper = $invoice_helper;
	}
	public function setSessonData($data,$user)
	{
        session()->forget('payment_data');
		$value['amount']                   = $data['amount'];
        $value['currency_code']            = $data['currency_code'];
        $value['payment_type']             = request()->payment_type;
        $value['user_token']               = request()->token;
        $value['pay_for']                  = request()->pay_for;
        $value['original_amount']          = request()->amount;
        $value['applied_referral_amount']  = request()->applied_referral_amount;
        $value['trip_id']                  = request()->trip_id;
        session()->put('payment_data',$value);
	}

	public function payment()
	{
		$user = $this->set_user_deatils();
		set_payment_gateway_company_context($user->company_id ?? null);
		$this->setUserLocale($user->language);
		$data['currency_code'] = site_settings('payment_currency');
        $data['amount'] = currencyConvert($user->currency_code,$data['currency_code'],request()->amount);
        $this->setSessonData($data,$user);

        $business_logic = resolve(\App\Services\BusinessLogic::class);
		$validate = $business_logic->validate(session()->get('payment_data'),$this->get_user_deatils());
		// only referal in pay to admin 
		if($validate['status_code']!='1')
		{
			if($validate['status_code']=='2')
				$return = $business_logic->pay_to_admin(session()->get('payment_data'),$this->get_user_deatils());
			else if($validate['status_code']=='3')
				$return = $business_logic->tripPayment(session()->get('payment_data'),$this->get_user_deatils());
			else 
				$return = response($validate);
			return $this->returnView($return);
		}

		$data['payment_method'] = $this->activePaymentMethod($user->company_id ?? null);
		foreach ($data['payment_method'] as  $key => $method) {
			try {
				$service = 'App\Services\Payments\\'.ucfirst($method)."Payment";
				$this->payment = resolve($service);
			}catch(\Exception $e) {
				$this->payment = resolve(\App\Services\Payments\PaypalPayment::class);
	        }
	        $data['view'][$method] = $this->payment->view($user);
		}
		return view('payment.payment',$data);
	}
	public function activePaymentMethod($companyId = null)
	{
		return active_payment_gateway_sites($companyId);
	}


	public function get_user_deatils()
	{
		if(session()->has('payment_data.user_token') || request()->token){
			if(request()->token){
				return \JWTAuth::parseToken()->authenticate();
			}
        	$set_token = \JWTAuth::setToken(session()->get('payment_data.user_token'));
			return  $set_token->toUser();
		}
		else
			return auth()->guard('web')->user();
	}
	public function set_user_deatils()
	{
		if(request()->token)
			return \JWTAuth::parseToken()->authenticate();
		else
			return auth()->guard('web')->user();
	}

	public function success()
	{

		$user = $this->get_user_deatils();
		set_payment_gateway_company_context($user->company_id ?? null);
		$this->setUserLocale($user->language);
		$payment_type = request()->payment_type ?? (session()->get('payment_data.payment_type'));
		$service = 'App\Services\Payments\\'.ucfirst($payment_type).'Payment';
		$this->payment = resolve($service);

		$method = 'get'.request()->payment_type.'Data';
     	$paymentData = ($this)->$method($this->payment,$user);
     	if( isset($paymentData['status_code']) && $paymentData['status_code'] == "0")
			return $this->returnView($paymentData);

		$payment_status = $this->payment->Payment($paymentData['payment_data'],request()->pay_key);

		if($payment_status->status_code == "1" && !($payment_status->is_two_step ?? false) ){
			$payment_status = $this->payment_action($payment_status,$paymentData['all_data']);
		}
		elseif ($payment_status->is_two_step ?? false) {
			return back()->withErrors(['two_step_id' => $payment_status->two_step_id]);
		}
		return $this->returnView($payment_status);
	} 

	public function returnView($payment_status)
	{
		return view('web_payment.view',compact('payment_status'));
	}


	public function getSaveCardData($user,$payment_method='',$customer_id='')
    {

    	$card_details['payment_method'] = request()->paymentMethod_id;
    	if($customer_id!='')
    		$card_details['customer'] = $customer_id;

    	if($payment_method){
	        $card_details =  [
		                'customer'      => $payment_method->customer_id,
		                'payment_method'=> $payment_method->payment_method_id,
		            ];
		}
        $data['payment_data'] = [
                'confirm'               => true,
                'off_session'           => true,
                'confirmation_method'   => 'manual',
                "amount"        		=> intval(session()->get('payment_data.amount') * 100),
                'currency'      		=> site_settings('payment_currency'),
                'description'   		=> session()->get('payment_data.pay_for').' Payment by '.$user->first_name,
            ]+$card_details;
        $data['all_data'] = session()->get('payment_data');
        return $data;
    }


	public function getStripeData($service,$user)
	{
		//for 3d secure cards 
		if(request()->pay_key)
		{
			$data['payment_data'] = [];
			$data['all_data'] = session()->get('payment_data');
			return $data;
		}
		$card_details   = '';
		$customer_id   = '';
        if(request()->save_card_id || request()->save_card){
            if(request()->save_card && !request()->save_card_id){
                $payment_methods    = PaymentMethod::where('user_id',$user->id)->first();
                $customer_id        = $payment_methods ? $payment_methods->customer_id : '';

                if(!$customer_id)
                {
                    $customer = $service->createCustomer($user->email);
                    if($customer->status=='success')
                        $customer_id = $customer->customer_id;
                }
                $card_details = $this->saveNewCard($user,$customer_id,request()->paymentMethod_id,$service);
                if(isset($card_details['status_code']) && $card_details['status_code'] == "0") 
                	return $card_details;
            }
            else{
                $card_details = PaymentMethod::where('user_id',$user->id)->where('id',request()->save_card_id)->first();
            }
        }else{
        	$customer = $service->createCustomer($user->email);
                if($customer->status=='success')
                    $customer_id = $customer->customer_id;
            $attachCustomer = $service->attachCustomer($user,$customer_id,request()->paymentMethod_id);
            if( isset($attachCustomer['status_code']) && $attachCustomer['status_code'] == "0") 
                return $attachCustomer;
            $card_details = '';
        }
        return  $this->getSaveCardData($user,$card_details,$customer_id);

	}

	public function saveNewCard($user,$customer_id,$paymentMethod_id,$service)
    {
        $payment_details                = new PaymentMethod;
        $payment_details->user_id       = $user->id;
        $payment_details->customer_id   = $customer_id;
        $attach                         = $service->attachCustomer($user,$customer_id,$paymentMethod_id);
        if(isset($attach['status_code']) && $attach['status_code'] == "0") 
            return $attach;
        $payment_details->intent_id         = $attach['intent_id'];
        $payment_details->payment_method_id = $attach['payment_method_id'];
        $payment_details->brand             = $attach['brand'];
        $payment_details->last4             = $attach['last4'];
        $payment_details->save();
        
		//delete other cards
    	PaymentMethod::where('user_id',$user->id)->where('id','!=',$payment_details->id)->delete();
        return $payment_details;
    }



	public function getBraintreeData($service,$user)
	{
		$data['payment_data'] = session()->get('payment_data.amount');
        $data['key'] = request()->pay_key;
		$data['all_data'] = session()->get('payment_data');
        return $data;
	}
	public function getPaypalData($service,$user)
	{
		$data['payment_data'] = session()->get('payment_data.amount');
        $data['key'] = request()->pay_key;
		$data['all_data'] = session()->get('payment_data');
        return $data;
	}

	public function cancel()
	{
		$payment_status = ['status_code'=>'0','message'=>'Payment failed'];
		return $this->returnView($payment_status);
	} 

	public function payment_action($payment,$paymentData)
	{
		$payment = json_decode(json_encode($payment),true);
		$data = array_merge($payment,$paymentData);

		$business_logic = resolve(\App\Services\BusinessLogic::class);
		if($paymentData['pay_for']=='wallet')
			return $business_logic->add_wallet($data,$this->get_user_deatils());
		else if($paymentData['pay_for']=='pay_to_admin')
			return $business_logic->pay_to_admin($data,$this->get_user_deatils());
		else
			return $business_logic->tripPayment($data,$this->get_user_deatils());
	}


       //PayTm

public function paytm_payment(Request $request)
{
    $user = $this->get_user_deatils();

    $MID = PaymentGateway::where('name', 'paytm_merchant')->where('site', 'Paytm')->first();
    $MID = $MID->value;
    $paytm_secret = PaymentGateway::where('name', 'paytm_secret')->where('site', 'Paytm')->first();
    $paytm_secret = $paytm_secret->value;
    
    $paytm_mode = PaymentGateway::where('name', 'mode')->where('site', 'Paytm')->first();
    $paytm_mode = $paytm_mode->value;

    $order_id = uniqid();
    $user_id =$user->id;
    
    $payTm = new PayTm();
    $payTm->user_id =$user->id;
    $payTm->payment_id = $order_id;
    //$payTm->currency =  $user->currency_code;
    $payTm->amount = $request->amount;
    $payTm->pay_for = $request->pay_for;
    $payTm-> save();

  
    $amount =$request->amount;
    define('PAYTM_ENVIRONMENT', $paytm_mode); // PROD
    define('PAYTM_MERCHANT_KEY', $paytm_secret); //Change this constant's value with Merchant key received from Paytm.
    define('PAYTM_MERCHANT_MID', $MID); //Change this constant's value with MID (Merchant ID) received from Paytm.
    define('PAYTM_MERCHANT_WEBSITE', 'DEFAULT'); //Change this constant's value with Website name received from Paytm.
    if (PAYTM_ENVIRONMENT == 'sandbox') {
    $PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
    $PAYTM_TXN_URL='https://securegw-stage.paytm.in/theia/processTransaction';
    }
    else if (PAYTM_ENVIRONMENT == 'live') {
    	$PAYTM_STATUS_QUERY_NEW_URL='https://securegw.paytm.in/merchant-status/getTxnStatus';
    	$PAYTM_TXN_URL='https://securegw.paytm.in/theia/processTransaction';
    }
    
    define('PAYTM_REFUND_URL', '');
    define('PAYTM_STATUS_QUERY_URL', $PAYTM_STATUS_QUERY_NEW_URL);
    define('PAYTM_STATUS_QUERY_NEW_URL', $PAYTM_STATUS_QUERY_NEW_URL);
    define('PAYTM_TXN_URL', $PAYTM_TXN_URL);

   $data_for_request = $this->handlePaytmRequest( $order_id, $amount, $user_id);


   //$paytm_txn_url = 'https://securegw-stage.paytm.in/theia/processTransaction';
   $paramList = $data_for_request['paramList'];
   $checkSum = $data_for_request['checkSum'];

   return view( 'payment.paytm', compact( 'PAYTM_TXN_URL', 'paramList', 'checkSum'));
}

 public function handlePaytmRequest($order_id, $amount, $user_id ) {
	// Load all functions of encdec_paytm.php and config-paytm.php
	$checkSum = "";
    $paramList = [];
    
    
    $ORDER_ID = $order_id;
    $CUST_ID =  $user_id;
    $INDUSTRY_TYPE_ID = 'Retail';
    $CHANNEL_ID ='WEB';
    $TXN_AMOUNT =  $amount;
    
    // Create an array having all required parameters for creating checksum.
    $paramList["MID"] = PAYTM_MERCHANT_MID;
    $paramList["ORDER_ID"] = $ORDER_ID;
    $paramList["CUST_ID"] = $CUST_ID;
    $paramList["INDUSTRY_TYPE_ID"] = $INDUSTRY_TYPE_ID;
    $paramList["CHANNEL_ID"] = $CHANNEL_ID;
    $paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
    $paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;
    $paramList["CALLBACK_URL"] = url( '/api/paytm_callback' );
    
    
    //Here checksum string will return by getChecksumFromArray() function.
    $checkSum = $this->getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);


		return [
			'checkSum' => $checkSum,
			'paramList' => $paramList
		];
	}



function encrypt_e($input, $ky) {
	$key   = html_entity_decode($ky);
	$iv = "@@@@&&&&####$$$$";
	$data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
	return $data;
}

function decrypt_e($crypt, $ky) {
	$key   = html_entity_decode($ky);
	$iv = "@@@@&&&&####$$$$";
	$data = openssl_decrypt ( $crypt , "AES-128-CBC" , $key, 0, $iv );
	return $data;
}

function generateSalt_e($length) {
	$random = "";
	srand((float) microtime() * 1000000);

	$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
	$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
	$data .= "0FGH45OP89";

	for ($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand() % (strlen($data))), 1);
	}

	return $random;
}

function checkString_e($value) {
	if ($value == 'null')
		$value = '';
	return $value;
}

function getChecksumFromArray($arrayList, $key, $sort=1) {
	if ($sort != 0) {
		ksort($arrayList);
	}
	$str = $this->getArray2Str($arrayList);
	$salt = $this->generateSalt_e(4);
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = $this->encrypt_e($hashString, $key);
	return $checksum;
}
function getChecksumFromString($str, $key) {
	
	$salt = $this->generateSalt_e(4);
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = $this->encrypt_e($hashString, $key);
	return $checksum;
}

function verifychecksum_e($arrayList, $key, $checksumvalue) {
	$arrayList = $this->removeCheckSumParam($arrayList);
	ksort($arrayList);
	$str = $this->getArray2StrForVerify($arrayList);
	$paytm_hash = $this->decrypt_e($checksumvalue, $key);
	$salt = substr($paytm_hash, -4);

	$finalString = $str . "|" . $salt;

	$website_hash = hash("sha256", $finalString);
	$website_hash .= $salt;

	$validFlag = "FALSE";
	if ($website_hash == $paytm_hash) {
		$validFlag = "TRUE";
	} else {
		$validFlag = "FALSE";
	}
	return $validFlag;
}

function verifychecksum_eFromStr($str, $key, $checksumvalue) {
	$paytm_hash = $this->decrypt_e($checksumvalue, $key);
	$salt = substr($paytm_hash, -4);

	$finalString = $str . "|" . $salt;

	$website_hash = hash("sha256", $finalString);
	$website_hash .= $salt;

	$validFlag = "FALSE";
	if ($website_hash == $paytm_hash) {
		$validFlag = "TRUE";
	} else {
		$validFlag = "FALSE";
	}
	return $validFlag;
}

function getArray2Str($arrayList) {
	$findme   = 'REFUND';
	$findmepipe = '|';
	$paramStr = "";
	$flag = 1;	
	foreach ($arrayList as $key => $value) {
		$pos = strpos($value, $findme);
		$pospipe = strpos($value, $findmepipe);
		if ($pos !== false || $pospipe !== false) 
		{
			continue;
		}
		
		if ($flag) {
			$paramStr .= $this->checkString_e($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . $this->checkString_e($value);
		}
	}
	return $paramStr;
}

function getArray2StrForVerify($arrayList) {
	$paramStr = "";
	$flag = 1;
	foreach ($arrayList as $key => $value) {
		if ($flag) {
			$paramStr .= $this->checkString_e($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . $this->checkString_e($value);
		}
	}
	return $paramStr;
}

function redirect2PG($paramList, $key) {
	$hashString = $this->getchecksumFromArray($paramList);
	$checksum = $this->encrypt_e($hashString, $key);
}

function removeCheckSumParam($arrayList) {
	if (isset($arrayList["CHECKSUMHASH"])) {
		unset($arrayList["CHECKSUMHASH"]);
	}
	return $arrayList;
}

function getTxnStatus($requestParamList) {
	return $this->callAPI(PAYTM_STATUS_QUERY_URL, $requestParamList);
}

function getTxnStatusNew($requestParamList) {
	return $this->callNewAPI(PAYTM_STATUS_QUERY_NEW_URL, $requestParamList);
}

function initiateTxnRefund($requestParamList) {
	$CHECKSUM = $this->getRefundChecksumFromArray($requestParamList,PAYTM_MERCHANT_KEY,0);
	$requestParamList["CHECKSUM"] = $CHECKSUM;
	return $this->callAPI(PAYTM_REFUND_URL, $requestParamList);
}

function callAPI($apiURL, $requestParamList) {
	$jsonResponse = "";
	$responseParamList = [];
	$JsonData =json_encode($requestParamList);
	$postData = 'JsonData='.urlencode($JsonData);
	$ch = curl_init($apiURL);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [                                                                         
	'Content-Type: application/json', 
	'Content-Length: ' . strlen($postData)]                                                                       
	);  
	$jsonResponse = curl_exec($ch);   
	$responseParamList = json_decode($jsonResponse,true);
	return $responseParamList;
}

function callNewAPI($apiURL, $requestParamList) {
	$jsonResponse = "";
	$responseParamList = [];
	$JsonData =json_encode($requestParamList);
	$postData = 'JsonData='.urlencode($JsonData);
	$ch = curl_init($apiURL);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [                                                                         
	'Content-Type: application/json', 
	'Content-Length: ' . strlen($postData)]                                                                       
	);  
	$jsonResponse = curl_exec($ch);   
	$responseParamList = json_decode($jsonResponse,true);
	return $responseParamList;
}
function getRefundChecksumFromArray($arrayList, $key, $sort=1) {
	if ($sort != 0) {
		ksort($arrayList);
	}
	$str = $this->getRefundArray2Str($arrayList);
	$salt = $this->generateSalt_e(4);
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = encrypt_e($hashString, $key);
	return $checksum;
}
function getRefundArray2Str($arrayList) {	
	$findmepipe = '|';
	$paramStr = "";
	$flag = 1;	
	foreach ($arrayList as $key => $value) {		
		$pospipe = strpos($value, $findmepipe);
		if ($pospipe !== false) 
		{
			continue;
		}
		
		if ($flag) {
			$paramStr .= $this->checkString_e($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . $this->checkString_e($value);
		}
	}
	return $paramStr;
}
function callRefundAPI($refundApiURL, $requestParamList) {
	$jsonResponse = "";
	$responseParamList = [];
	$JsonData =json_encode($requestParamList);
	$postData = 'JsonData='.urlencode($JsonData);
	$ch = curl_init($apiURL);	
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_URL, $refundApiURL);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	$headers = [];
	$headers[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
	$jsonResponse = curl_exec($ch);   
	$responseParamList = json_decode($jsonResponse,true);
	return $responseParamList;
}


    public function getConfigPaytmSettings() {
        
            $MID = PaymentGateway::where('name', 'paytm_merchant')->where('site', 'Paytm')->first();
            $MID = $MID->value;
            $paytm_secret = PaymentGateway::where('name', 'paytm_secret')->where('site', 'Paytm')->first();
            $paytm_secret = $paytm_secret->value;
    
            $paytm_mode = PaymentGateway::where('name', 'mode')->where('site', 'Paytm')->first();
		
        define('PAYTM_ENVIRONMENT', $paytm_mode); // PROD
		define('PAYTM_MERCHANT_KEY', $paytm_secret); //Change this constant's value with Merchant key received from Paytm.
		define('PAYTM_MERCHANT_MID', $MID); //Change this constant's value with MID (Merchant ID) received from Paytm.
		define('PAYTM_MERCHANT_WEBSITE', 'WEBSTAGING'); //Change this constant's value with Website name received from Paytm.
        if (PAYTM_ENVIRONMENT == 'sandbox') {
		$PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
		$PAYTM_TXN_URL='https://securegw-stage.paytm.in/theia/processTransaction';
        }
		else if (PAYTM_ENVIRONMENT == 'live') {
			$PAYTM_STATUS_QUERY_NEW_URL='https://securegw.paytm.in/merchant-status/getTxnStatus';
			$PAYTM_TXN_URL='https://securegw.paytm.in/theia/processTransaction';
		}

		define('PAYTM_REFUND_URL', '');
		define('PAYTM_STATUS_QUERY_URL', $PAYTM_STATUS_QUERY_NEW_URL);
		define('PAYTM_STATUS_QUERY_NEW_URL', $PAYTM_STATUS_QUERY_NEW_URL);
		define('PAYTM_TXN_URL', $PAYTM_TXN_URL);
    }

    
    public function paytm_callback(Request $request)
{
    $order_id = $request['ORDERID'];

    if ('TXN_SUCCESS' === $request['STATUS']) {
        $transaction_id = $request['TXNID'];
        $paytm_payment = PayTm::where('payment_id', $order_id)->where('status', 'Pending')->latest()->first();

        if ($paytm_payment) {
            $user = User::find($paytm_payment->user_id);

            if ($user && $paytm_payment->pay_for == "wallet") {
                $wallet = Wallet::where('user_id', $user->id)->first();

                $wallet_amount = $paytm_payment->amount;

                if ($wallet) {
                    $wallet_amount = floatval($wallet->amount) + floatval($wallet_amount);
                } else {
                    $wallet = new Wallet;
                    $wallet->user_id = $user->id;
                }

                $wallet->amount = $wallet_amount;
                $wallet->paykey = $transaction_id;
                $wallet->currency_code = $user->currency_code;
                $wallet->save();

                $paymentData = [
                    'status_code' => '1',
                    'status_message' => __('messages.wallet_add_success'),
                    'wallet_amount' => (string)floatval($wallet_amount),
                ];
			        return $this->returnView($paymentData);
			        
			    }else if($paytm_payment->pay_for=="pay_to_admin"){
			        
			      $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
		if ($owe_amount && $owe_amount->amount > 0) {
		

			//pay to admin from payout preference start
			$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();

			$amount = $paytm_payment->amount;


			if($amount > 0) {
				if($owe_amount->amount < $amount) {
				
					$paymentData = ([
				    'status_code' => '0',
					'status_message' => trans('messages.api.invalid'),
					
			        ]);
			        return $this->returnView($paymentData);
				}


				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				$total_owe_amount = $owe_amount->amount;
				$currency_code = $owe_amount->currency_code;
				$remaining_amount = $total_owe_amount - $amount;

				$currency_code = $user->currency_code;
				


				//owe amount
				$owe_amount->amount = $remaining_amount;
				$owe_amount->currency_code = $currency_code;
				$owe_amount->save();

				$payment = new DriverOweAmountPayment;
				$payment->user_id = $user->id;
				$payment->transaction_id = $transaction_id;
				$payment->amount = $paytm_payment->amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			}

			$referral_amount = ReferralUser::where('user_id',$user->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
			$referral_amount = number_format($referral_amount->sum('pending_amount'),2,'.','');

			
			 $paymentData = ([
				'status_code' 	=> '1',
				'status_message'=> __('messages.payment_complete_success'),
				'referral_amount' => $referral_amount,
				'owe_amount' 	=> $owe_amount->amount,
				'currency_code' => $owe_amount->currency_code,
			     ]);
			 return $this->returnView($paymentData);
	        	}
			        
			      $paymentData = ([
				 'status_code' => '0',
			     'status_message' => __('messages.api.not_generate_amount'),
			     ]);
                 return $this->returnView($paymentData);   
			        
			        
			        
			    } else if ($paytm_payment->pay_for == "trip") {
    $trip_id = Trips::where('user_id', $user->id)->where('payment_status', "Pending")->where('payment_mode', "paytm")->latest()->first();
    
    if ($trip_id) {
        $trip = Trips::find($trip_id->id);

        if ($trip->is_calculation == 0) {
            $data = [
                'trip_id' => $trip->id,
                'user_id' => $user->id,
                'save_to_trip_table' => 1,
            ];
            $trip = $this->invoice_helper->calculation($data);
        }

        $trip->status = 'Completed';
        $trip->paykey = $transaction_id ?? '';
        $trip->payment_status = 'Completed';
        $trip->save();

        if ($trip->pool_id > 0) {
            $pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
            $trips = $pool_trip->trips->whereIn('status', ['Scheduled', 'Begin trip', 'End trip', 'Rating', 'Payment'])->count();

            if (!$trips) {
                // update status
                $pool_trip->status = 'Completed';
                $pool_trip->save();
            }
        }

        $data = [
            'trip_id' => $trip->id,
            'correlation_id' => $pay_result->transaction_id ?? '',
            'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
        ];
        Payment::updateOrCreate(['trip_id' => $trip->id], $data);

        // Push notification
        $push_title = "Payment Completed";
        $push_data['push_title'] = $push_title;
        $push_data['data'] = [
            'trip_payment' => [
                'status' => 'Paid',
                'trip_id' => $trip->id,
                'rider_thumb_image' => $trip->rider_profile_picture
            ]
        ];
        $request_helper = new RequestHelper();
        $request_helper->SendPushNotification($trip->driver, $push_data);

        $paymentData = [
            'status_code' => '1',
            'status_message' => __('messages.payment_complete_success'),
        ];
        return $this->returnView($paymentData);
    }
}
}
 
    } else if( 'TXN_FAILURE' === $request['STATUS'] ){
			$paymentData = ([
				'status_code' => '0',
				'status_message' => trans($request['RESPMSG']),
			]);
			return $this->returnView($paymentData);
		}
	}

// Mpesa CallBack

public function mpesa_callback( Request $request ) {
		$order_id = $request['ORDERID'];

		if ( 'TXN_SUCCESS' === $request['STATUS'] ) {
			$transaction_id = $request['TXNID'];
			$paytm_payment = PayTm::where('payment_id',$request['ORDERID'])->where('status','Pending')->latest();
			$user = User::where('id',$paytm_payment->user_id)->first();
			if($paytm_payment){
			    if($paytm_payment->pay_for=="wallet"){
			        
			        
			        $wallet = Wallet::whereUserId($paytm_payment->user_id)->first();
			        

                    $wallet_amount = $paytm_payment->amount;
            		if($wallet) {
            			$wallet_amount = floatval($wallet->original_amount) + floatval($wallet_amount);
            		}
            		else {
            			$wallet = new Wallet;
            		}
            
            		$wallet->amount = $wallet_amount;
            		$wallet->paykey = $transaction_id;
            		$wallet->currency_code = $user->currency_code;
            		$wallet->user_id = $user->id;
            		$wallet->save();
            
            	
			         $paymentData = ([
				     'status_code' => '1',
					 'status_message' 	=> __('messages.wallet_add_success'),
					 'wallet_amount' 	=>  (string) floatval($wallet_amount),
			        ]);
			        return $this->returnView($paymentData);
			        
			    }else if($paytm_payment->pay_for=="pay_to_admin"){
			        
			      $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
		if ($owe_amount && $owe_amount->amount > 0) {
		

			//pay to admin from payout preference start
			$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();

			$amount = $paytm_payment->amount;


			if($amount > 0) {
				if($owe_amount->amount < $amount) {
				
					$paymentData = ([
				    'status_code' => '0',
					'status_message' => trans('messages.api.invalid'),
					
			        ]);
			        return $this->returnView($paymentData);
				}


				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				$total_owe_amount = $owe_amount->amount;
				$currency_code = $owe_amount->currency_code;
				$remaining_amount = $total_owe_amount - $amount;

				$currency_code = $user->currency_code;
				


				//owe amount
				$owe_amount->amount = $remaining_amount;
				$owe_amount->currency_code = $currency_code;
				$owe_amount->save();

				$payment = new DriverOweAmountPayment;
				$payment->user_id = $user->id;
				$payment->transaction_id = $transaction_id;
				$payment->amount = $paytm_payment->amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			}

			$referral_amount = ReferralUser::where('user_id',$user->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
			$referral_amount = number_format($referral_amount->sum('pending_amount'),2,'.','');

			
			 $paymentData = ([
				'status_code' 	=> '1',
				'status_message'=> __('messages.payment_complete_success'),
				'referral_amount' => $referral_amount,
				'owe_amount' 	=> $owe_amount->amount,
				'currency_code' => $owe_amount->currency_code,
			     ]);
			 return $this->returnView($paymentData);
	        	}
			        
			      $paymentData = ([
				 'status_code' => '0',
			     'status_message' => __('messages.api.not_generate_amount'),
			     ]);
                 return $this->returnView($paymentData);   
			        
			        
			        
			    }else if($paytm_payment->pay_for=="trip"){
			        $trip_id = Trips::where('user_id',$user->id)->where('payment_status',"Pending")->where('payment_mode',"paytm")->latest();
			        $trip = Trips::find($trip_id->id); 

            		if($trip->is_calculation == 0) {
            			$data = [
            				'trip_id' => $trip->id,
            				'user_id' => $user->id,
            				'save_to_trip_table' => 1,
            			];
            			$trip = $this->invoice_helper->calculation($data);
            		}
            
            		$trip->status = 'Completed';
            		$trip->paykey = $transaction_id ?? '';
            		$trip->payment_status = 'Completed';
            		$trip->save();
            
            		if($trip->pool_id>0) {
            
            			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
            			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])->count();
            			
            			if(!$trips) {
            				// update status
            				$pool_trip->status = 'Completed';
            				$pool_trip->save();
            			}
            		}
            
            		$data = [
            			'trip_id' => $trip->id,
            			'correlation_id' => $pay_result->transaction_id ?? '',
            			'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
            		];
            		Payment::updateOrCreate(['trip_id' => $trip->id], $data);
            
            		//Push notification 
            		$push_title = "Payment Completed";
            		$push_data['push_title'] = $push_title;
                    $push_data['data'] = [
                    	'trip_payment' => [
                    		'status' => 'Paid',
                    		'trip_id' => $trip->id,
                    		'rider_thumb_image' => $trip->rider_profile_picture
                    	]
                    ];
                    $request_helper = new RequestHelper();
                    $request_helper->SendPushNotification($trip->driver,$push_data);
            
            	
            		$paymentData = ([
    				'status_code' => '1',
    				'status_message' 	=> __('messages.payment_complete_success'),
        			]);
        			return $this->returnView($paymentData);
			        
			        
			    }
			    
			}
		
			

		} else if( 'TXN_FAILURE' === $request['STATUS'] ){
			$paymentData = ([
				'status_code' => '0',
				'status_message' => trans($request['RESPMSG']),
			]);
			return $this->returnView($paymentData);
		}
	}
	
	
	 public static function flutterwave()
    {
        $data['amount'] = 100;
		$data['currency_code'] = 'USD';
		$data['payment_type'] = 'paypal';
		$data['pay_for'] = 'wallet';
		$data['token'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdmlub3RoLmNvbS9nb2Zlci9wdWJsaWMvYXBpL2xvZ2luIiwiaWF0IjoxNjE2MTMzNjg0LCJleHAiOjE2MTg3NjE2ODQsIm5iZiI6MTYxNjEzMzY4NCwianRpIjoiMzY4ejZXNnhLWWJDdGdKeCIsInN1YiI6MTAxMDcsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.IIJaQNbwXXvZzjmKD8TYB3ktPLPacEtobdWcNo-YKMc';

		//return array('view'=>'paypal','data'=>$data);
		return view('payment.flutterwave',$data);
    
    }
    public function flutterwave_payment(Request $request)
	{
         $curl = curl_init();
         $email="gordonjohnao@gmail.com";
         $amount=1;
    
        $customer_email = $email; 
        $amount_pay = $amount;
        $currency = "NGN";
        $txref = "rave" . uniqid(); // ensure you generate unique references per transaction.
        // get your public key from the dashboard.
        $PBFPubKey = "FLWPUBK_TEST-f2a7a1ca6cdf04f4ab00d9006073ff6d-X"; 
        $redirect_url = "https://cyrus.Wilaso.com/api/flutterwave_callback?email=$email&amount=$amount_pay"; // Set your own redirect URL
    
         curl_setopt_array($curl, [
         CURLOPT_URL => "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/hosted/pay",
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_CUSTOMREQUEST => "POST",
         CURLOPT_POSTFIELDS => json_encode([
            'amount'=>$amount_pay,
            'customer_email'=>$customer_email,
            'currency'=>$currency,
            'txref'=>$txref,
            'PBFPubKey'=>$PBFPubKey,
            'redirect_url'=>$redirect_url,
          ]),
          CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "cache-control: no-cache"
          ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        if($err){
          // there was an error contacting the rave API
          die('Curl returned error: ' . $err);
        }
    
        $transaction = json_decode($response);
    
        if(!$transaction->data && !$transaction->data->link){
          // there was an error from the API
          print_r('API returned error: ' . $transaction->message);
        }
    
        // redirect to page so User can pay
    
        header('Location: ' . $transaction->data->link); 
    		//return $this->returnView($payment_status);
   } 
   
  
   public function futterwave_callback( Request $request ) {
       
        $body = @file_get_contents("php://input");
		$response = json_decode($body);
		
		if ( $response->body->status == 'successful') {
			$transaction_id = $response->body->raveRef;
			$paytm_payment = PayTm::where('payment_id',$request['ORDERID'])->where('status','Pending')->latest();
			$user = User::where('id',$paytm_payment->user_id)->first();
			if($paytm_payment){
			    if($paytm_payment->pay_for=="wallet"){
			        
			        
			        $wallet = Wallet::whereUserId($paytm_payment->user_id)->first();
			        

                    $wallet_amount = $paytm_payment->amount;
            		if($wallet) {
            			$wallet_amount = floatval($wallet->original_amount) + floatval($wallet_amount);
            		}
            		else {
            			$wallet = new Wallet;
            		}
            
            		$wallet->amount = $wallet_amount;
            		$wallet->paykey = $transaction_id;
            		$wallet->currency_code = $user->currency_code;
            		$wallet->user_id = $user->id;
            		$wallet->save();
            
            	
			         $paymentData = ([
				     'status_code' => '1',
					 'status_message' 	=> __('messages.wallet_add_success'),
					 'wallet_amount' 	=>  (string) floatval($wallet_amount),
			        ]);
			        return $this->returnView($paymentData);
			        
			    }else if($paytm_payment->pay_for=="pay_to_admin"){
			        
			      $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
		if ($owe_amount && $owe_amount->amount > 0) {
		

			//pay to admin from payout preference start
			$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();

			$amount = $paytm_payment->amount;


			if($amount > 0) {
				if($owe_amount->amount < $amount) {
				
					$paymentData = ([
				    'status_code' => '0',
					'status_message' => trans('messages.api.invalid'),
					
			        ]);
			        return $this->returnView($paymentData);
				}


				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				$total_owe_amount = $owe_amount->amount;
				$currency_code = $owe_amount->currency_code;
				$remaining_amount = $total_owe_amount - $amount;

				$currency_code = $user->currency_code;
				


				//owe amount
				$owe_amount->amount = $remaining_amount;
				$owe_amount->currency_code = $currency_code;
				$owe_amount->save();

				$payment = new DriverOweAmountPayment;
				$payment->user_id = $user->id;
				$payment->transaction_id = $transaction_id;
				$payment->amount = $paytm_payment->amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			}

			$referral_amount = ReferralUser::where('user_id',$user->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
			$referral_amount = number_format($referral_amount->sum('pending_amount'),2,'.','');

			
			 $paymentData = ([
				'status_code' 	=> '1',
				'status_message'=> __('messages.payment_complete_success'),
				'referral_amount' => $referral_amount,
				'owe_amount' 	=> $owe_amount->amount,
				'currency_code' => $owe_amount->currency_code,
			     ]);
			 return $this->returnView($paymentData);
	        	}
			        
			      $paymentData = ([
				 'status_code' => '0',
			     'status_message' => __('messages.api.not_generate_amount'),
			     ]);
                 return $this->returnView($paymentData);   
			        
			        
			        
			    }else if($paytm_payment->pay_for=="trip"){
			        $trip_id = Trips::where('user_id',$user->id)->where('payment_status',"Pending")->where('payment_mode',"paytm")->latest();
			        $trip = Trips::find($trip_id->id); 

            		if($trip->is_calculation == 0) {
            			$data = [
            				'trip_id' => $trip->id,
            				'user_id' => $user->id,
            				'save_to_trip_table' => 1,
            			];
            			$trip = $this->invoice_helper->calculation($data);
            		}
            
            		$trip->status = 'Completed';
            		$trip->paykey = $transaction_id ?? '';
            		$trip->payment_status = 'Completed';
            		$trip->save();
            
            		if($trip->pool_id>0) {
            
            			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
            			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])->count();
            			
            			if(!$trips) {
            				// update status
            				$pool_trip->status = 'Completed';
            				$pool_trip->save();
            			}
            		}
            
            		$data = [
            			'trip_id' => $trip->id,
            			'correlation_id' => $pay_result->transaction_id ?? '',
            			'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
            		];
            		Payment::updateOrCreate(['trip_id' => $trip->id], $data);
            
            		//Push notification 
            		$push_title = "Payment Completed";
            		$push_data['push_title'] = $push_title;
                    $push_data['data'] = [
                    	'trip_payment' => [
                    		'status' => 'Paid',
                    		'trip_id' => $trip->id,
                    		'rider_thumb_image' => $trip->rider_profile_picture
                    	]
                    ];
                    $request_helper = new RequestHelper();
                    $request_helper->SendPushNotification($trip->driver,$push_data);
            
            	
            		$paymentData = ([
    				'status_code' => '1',
    				'status_message' 	=> __('messages.payment_complete_success'),
        			]);
        			return $this->returnView($paymentData);
			        
			        
			    }
			    
			}
		
			

		} else if( 'TXN_FAILURE' === $request['STATUS'] ){
			$paymentData = ([
				'status_code' => '0',
				'status_message' => trans($request['RESPMSG']),
			]);
			return $this->returnView($paymentData);
		}
	}

	

	public function setUserLocale($locale)
	{
		App::setLocale($locale);
        Session::put('language', $locale);
	}


}
