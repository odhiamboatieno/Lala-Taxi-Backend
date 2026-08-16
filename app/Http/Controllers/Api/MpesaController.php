<?php

/**
 * M-Pesa Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Payment
 * @author      Wilaso
 * @version     4.0.4
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
use App\Models\PoolTrip;
use App\Models\DriverOweAmount;
use App\Models\ReferralUser;
use App\Models\AppliedReferrals;
use App\Models\DriverOweAmountPayment;
use App\Models\Transaction;
use App\Models\PaymentTransaction;
use App\Http\Helper\InvoiceHelper;
use App\Http\Helper\RequestHelper;
use Session;
use JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MpesaController extends Controller
{
    protected $invoice_helper;
    private $maxRetries = 3;
    private $retryDelay = 2000000; // 2 seconds in microseconds
    private $pollingTimeout = 60; // 60 seconds
    private $pollingInterval = 2; // Poll every 2 seconds

    public function __construct(InvoiceHelper $invoice_helper)
    {
        $this->invoice_helper = $invoice_helper;
    }

    public function setSessionData($data, $user)
    {
        session()->forget('payment_data');
        $value = [
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'],
            'payment_type' => request()->payment_type,
            'user_token' => request()->token,
            'pay_for' => request()->pay_for,
            'original_amount' => request()->amount,
            'applied_referral_amount' => request()->applied_referral_amount,
            'trip_id' => request()->trip_id,
        ];
        session()->put('payment_data', $value);
    }

    public function mpesa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:99999.99',
            'pay_for' => 'required|in:wallet,pay_to_admin,trip',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            Log::error("M-Pesa: Validation failed, error={$validator->errors()->first()}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => $validator->errors()->first(),
            ]);
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();
            set_payment_gateway_company_context($user->company_id ?? null);
        } catch (\Exception $e) {
            Log::error("M-Pesa: Invalid token, error={$e->getMessage()}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Invalid token',
            ]);
        }

        $payment_id = uniqid();
        $amount = floatval($request->amount);
        $data['currency_code'] = 'KES';
        if ($user->currency_code != 'KES') {
            $amount = currencyConvert($user->currency_code, $data['currency_code'], $amount);
        }
        $data['amount'] = intval(round($amount));
        $data['payment_type'] = 'mpesa';
        $data['pay_for'] = $request->pay_for;
        $data['token'] = $request->token;
        $data['user_id'] = $user->id;

        Log::debug("M-Pesa: Initiating payment, user_id={$user->id}, amount={$data['amount']}, pay_for={$data['pay_for']}, currency=KES");

        $this->setSessionData($data, $user);
        return view('payment.mpesa', $data);
    }

    public function lipaNaMpesaPassword()
    {
        $lipa_time = Carbon::rawParse('now')->format('YmdHms');
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $BusinessShortCode = 174379;
        $timestamp = $lipa_time;
        return base64_encode($BusinessShortCode . $passkey . $timestamp);
    }

    public function customerMpesaSTKPush(Request $request)
    {
        $user = User::find($request->user_id);
        set_payment_gateway_company_context($user->company_id ?? null);

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|regex:/^07[0-9]{8}$/',
            'amount' => 'required|numeric|min:1|max:99999.99',
            'user_id' => 'required|exists:users,id',
            'pay_for' => 'required|in:wallet,pay_to_admin,trip',
            'currency_code' => 'required|in:KES',
        ]);

        if ($validator->fails()) {
            Log::error("M-Pesa STK Push: Validation failed, error={$validator->errors()->first()}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => $validator->errors()->first(),
            ]);
        }

        // Validate phone number against sandbox test numbers
        $phone_number = $request->phone_number;
        $sandbox_numbers = ['0797049288', '0708374149'];
        if (!in_array($phone_number, $sandbox_numbers)) {
            Log::error("M-Pesa STK Push: Invalid sandbox phone number, phone=$phone_number, user_id={$request->user_id}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Invalid phone number for sandbox testing.',
            ]);
        }

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $phone_final = '254' . substr($phone_number, -9);
        $callback_url = payment_gateway('callback_url', 'Mpesa');

        // Validate callback URL
        if (empty($callback_url) || !filter_var($callback_url, FILTER_VALIDATE_URL)) {
            Log::error("M-Pesa STK Push: Invalid callback URL, callback_url=" . ($callback_url ?? 'null'));
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Payment configuration error. Please contact support.',
            ]);
        }

        $curl_post_data = [
            'BusinessShortCode' => payment_gateway('business_shortcode', 'Mpesa'),
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => intval(round(floatval($request->amount))),
            'PartyA' => $phone_final,
            'PartyB' => payment_gateway('business_shortcode', 'Mpesa'),
            'PhoneNumber' => $phone_final,
            'CallBackURL' => $callback_url,
            'AccountReference' => 'New Taxi',
            'TransactionDesc' => 'New Taxi Payment',
        ];

        Log::debug("M-Pesa STK Push: Initiating, user_id={$request->user_id}, amount={$request->amount}, phone=$phone_final, pay_for={$request->pay_for}, callback_url=$callback_url");

        $response = $this->makeCurlRequest($url, $curl_post_data);
        $checkout = json_decode($response);

        if (!$checkout || !isset($checkout->CheckoutRequestID)) {
            Log::error("M-Pesa STK Push: Failed to initiate payment, response=" . ($response ?? 'null'));
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Failed to initiate payment. Please try again.',
            ]);
        }

        $transaction = new Transaction();
        $transaction->user_id = $request->user_id;
        $transaction->payment_id = $checkout->CheckoutRequestID;
        $transaction->amount = floatval($request->amount);
        $transaction->payment_type = 'mpesa';
        $transaction->currency = $request->currency_code;
        $transaction->pay_for = $request->pay_for;
        $transaction->status = 'Pending';
        $transaction->save();

        Log::debug("M-Pesa STK Push: Transaction created, id={$transaction->id}, payment_id={$checkout->CheckoutRequestID}, amount={$request->amount}, currency=KES, user_id={$request->user_id}");

        if (empty($transaction->payment_id)) {
            $transaction->delete();
            Log::error("M-Pesa STK Push: Invalid payment_id, transaction deleted, user_id={$request->user_id}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Invalid phone number. Please try again.',
            ]);
        }

        return $this->mpesaSuccess($request->user_id, $checkout->CheckoutRequestID);
    }

    private function makeCurlRequest($url, $post_data, $retryCount = 0)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                if ($retryCount < $this->maxRetries) {
                    usleep($this->retryDelay);
                    Log::warning("M-Pesa API: Retry attempt " . ($retryCount + 1) . ", error=$error");
                    return $this->makeCurlRequest($url, $post_data, $retryCount + 1);
                }
                Log::error("M-Pesa API: Failed after $this->maxRetries retries, error=$error");
                return null;
            }
            return $response;
        } catch (\Exception $e) {
            Log::error("M-Pesa API: Exception, error={$e->getMessage()}");
            if ($retryCount < $this->maxRetries) {
                usleep($this->retryDelay);
                Log::warning("M-Pesa API: Retry attempt " . ($retryCount + 1) . ", error={$e->getMessage()}");
                return $this->makeCurlRequest($url, $post_data, $retryCount + 1);
            }
            return null;
        }
    }

    public function generateAccessToken()
    {
        $consumer_key = payment_gateway('consumer_key', 'Mpesa');
        $consumer_secret = payment_gateway('consumer_secret', 'Mpesa');
        $credentials = base64_encode("$consumer_key:$consumer_secret");
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error("M-Pesa Access Token: Failed, error=$error");
            return null;
        }

        $access_token = json_decode($response);
        $token = $access_token->access_token ?? null;
        Log::debug("M-Pesa Access Token: Generated, token=" . ($token ? 'success' : 'null'));
        return $token;
    }

    public function queryStkPush($checkoutRequestID)
    {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        $curl_post_data = [
            'BusinessShortCode' => payment_gateway('business_shortcode', 'Mpesa'),
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'CheckoutRequestID' => $checkoutRequestID,
        ];

        Log::debug("M-Pesa STK Query: Initiating, CheckoutRequestID=$checkoutRequestID");

        $response = $this->makeCurlRequest($url, $curl_post_data);
        $result = json_decode($response);

        if (!$result || !isset($result->ResultCode)) {
            Log::error("M-Pesa STK Query: Failed, response=" . ($response ?? 'null'));
            return ['status' => 'Pending', 'transaction_id' => $checkoutRequestID, 'description' => 'Query failed'];
        }

        $status = $result->ResultCode == '0' ? 'Paid' : 'Failed';
        $transaction_id = $result->MpesaReceiptNumber ?? $checkoutRequestID;
        $description = $result->ResultDesc ?? 'No description provided';

        Log::debug("M-Pesa STK Query: Result, CheckoutRequestID=$checkoutRequestID, status=$status, transaction_id=$transaction_id, description=$description");

        return [
            'status' => $status,
            'transaction_id' => $transaction_id,
            'description' => $description,
        ];
    }

    public function mpesaValidation(Request $request)
    {
        Log::debug("M-Pesa Validation: Received, data=" . json_encode($request->all()));
        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted validation request']);
    }

    public function mpesaConfirmation(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        Log::debug("M-Pesa Confirmation: Received, body=$body");

        if (!$data || !isset($data['Body']['stkCallback']['CheckoutRequestID'])) {
            Log::error("M-Pesa Confirmation: Invalid callback data, body=$body");
            return response()->json(['ResultCode' => '1', 'ResultDesc' => 'Invalid callback data']);
        }

        $resultCode = $data['Body']['stkCallback']['ResultCode'];
        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'];
        $transaction = Transaction::where('payment_type', 'mpesa')
            ->where('payment_id', $checkoutRequestID)
            ->where('status', 'Pending')
            ->latest()
            ->first();

        if (!$transaction) {
            Log::error("M-Pesa Confirmation: No transaction found, CheckoutRequestID=$checkoutRequestID");
            return response()->json(['ResultCode' => '1', 'ResultDesc' => 'Transaction not found']);
        }

        // Multi-tenant webhook resolution: pick the right credentials
        // based on the user that initiated the payment.  In `admin` mode
        // the resolver ignores the company id and uses the platform's
        // global credentials.
        $transactionUser = User::find($transaction->user_id);
        if ($transactionUser) {
            set_payment_gateway_company_context($transactionUser->company_id ?? null);
        }


        if ($resultCode == '0') {
            $mpesaReceiptNumber = $data['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] ?? null;
            $transactionDate = $data['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'] ?? null;
            $transaction->status = 'Paid';
            $transaction->transaction_id = $mpesaReceiptNumber ?? $checkoutRequestID;
            $transaction->payment_desc = $data['Body']['stkCallback']['ResultDesc'];
            $transaction->save();
            Log::debug("M-Pesa Confirmation: Transaction updated, id={$transaction->id}, status=Paid, transaction_id=" . ($mpesaReceiptNumber ?? $checkoutRequestID));
        } else {
            $transaction->status = 'Failed';
            $transaction->payment_desc = $data['Body']['stkCallback']['ResultDesc'];
            $transaction->save();
            Log::debug("M-Pesa Confirmation: Transaction updated, id={$transaction->id}, status=Failed, payment_desc={$transaction->payment_desc}");
        }

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Confirmation received']);
    }

    public function mpesaSuccess($user_id, $checkoutRequestID)
    {
        $user = User::find($user_id);
        set_payment_gateway_company_context($user->company_id ?? null);
        $start = microtime(true);

        while (microtime(true) - $start < $this->pollingTimeout) {
            $result = Transaction::where('user_id', $user_id)
                ->where('payment_type', 'mpesa')
                ->where('payment_id', $checkoutRequestID)
                ->latest()
                ->first();

            if (!$result) {
                Log::error("M-Pesa Success: No transaction found, user_id=$user_id, CheckoutRequestID=$checkoutRequestID");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'Transaction not found',
                ]);
            }

            if ($result->status == 'Failed') {
                Log::debug("M-Pesa Success: Transaction failed, id={$result->id}, user_id=$user_id, payment_desc={$result->payment_desc}");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => $result->payment_desc,
                ]);
            } elseif ($result->status == 'Paid') {
                return $this->processPaidTransaction($result, $user_id);
            }

            Log::debug("M-Pesa Success: Polling, user_id=$user_id, transaction_id={$result->id}, status={$result->status}");
            sleep($this->pollingInterval);
        }

        // Query transaction status after timeout
        $queryResult = $this->queryStkPush($checkoutRequestID);
        $result = Transaction::where('user_id', $user_id)
            ->where('payment_type', 'mpesa')
            ->where('payment_id', $checkoutRequestID)
            ->latest()
            ->first();

        if ($result && $queryResult['status'] != 'Pending') {
            $result->status = $queryResult['status'];
            $result->transaction_id = $queryResult['transaction_id'];
            $result->payment_desc = $queryResult['description'];
            $result->save();
            Log::debug("M-Pesa Success: Transaction updated via query, id={$result->id}, status={$result->status}, transaction_id={$result->transaction_id}");

            if ($result->status == 'Paid') {
                return $this->processPaidTransaction($result, $user_id);
            } else {
                Log::debug("M-Pesa Success: Transaction failed via query, id={$result->id}, user_id=$user_id, payment_desc={$result->payment_desc}");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => $result->payment_desc,
                ]);
            }
        }

        Log::error("M-Pesa Success: Payment timeout after $this->pollingTimeout seconds, user_id=$user_id, CheckoutRequestID=$checkoutRequestID");
        return $this->returnJson([
            'status' => 'error',
            'status_code' => '0',
            'status_message' => 'Payment was not successful. Please try again.',
        ]);
    }

    private function processPaidTransaction($result, $user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            Log::error("M-Pesa Success: User not found, user_id=$user_id");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'User not found',
            ]);
        }

        if ($result->pay_for == 'wallet') {
            $payment_amount = floatval($result->amount);
            $wallet_currency = $user->currency_code; // Use user currency
            $converted_amount = currencyConvert($result->currency, $wallet_currency, $payment_amount);
            Log::debug("M-Pesa Success: Currency conversion, from_currency={$result->currency}, to_currency=$wallet_currency, original_amount=$payment_amount, converted_amount=$converted_amount");

            // Validate converted amount
            if ($converted_amount > 99999.99) {
                Log::error("M-Pesa Success: Converted amount exceeds decimal(7,2) limit, amount=$converted_amount, currency=$wallet_currency, user_id=$user_id");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'Payment amount too large for wallet.',
                ]);
            }

            // Create PaymentTransaction
            $transaction = new PaymentTransaction;
            $transaction->pay_for = "Wallet TopUp";
            $transaction->amount = $converted_amount;
            $transaction->transaction_id = $result->transaction_id;
            $transaction->currency_code = $wallet_currency;
            $transaction->description = "Your app wallet has been successfully topped up.";
            $transaction->user_id = $user_id;
            $transaction->save();
            Log::debug("M-Pesa Success: PaymentTransaction created, id={$transaction->id}, amount=$converted_amount, currency=$wallet_currency, transaction_id={$result->transaction_id}");

            // Update wallet
            DB::beginTransaction();
            try {
                $wallet = Wallet::whereUserId($user_id)->first();
                if ($wallet) {
                    $previous_amount = floatval($wallet->amount);
                    if ($previous_amount > 99999.99) {
                        Log::warning("M-Pesa Success: Invalid wallet amount detected, wallet_id={$wallet->id}, amount=$previous_amount, currency=$wallet_currency, user_id=$user_id. Resetting to 0.00.");
                        $previous_amount = 0.00;
                    }
                    $wallet_amount = $previous_amount + $converted_amount;
                    Log::debug("M-Pesa Success: Wallet calculation, wallet_id={$wallet->id}, previous=$previous_amount, added=$converted_amount, new=$wallet_amount, currency=$wallet_currency");
                } else {
                    $wallet = new Wallet;
                    $previous_amount = 0.00;
                    $wallet_amount = $converted_amount;
                    Log::debug("M-Pesa Success: New wallet created for user_id=$user_id, amount=$wallet_amount, currency=$wallet_currency");
                }

                // Validate final wallet amount
                if ($wallet_amount > 99999.99) {
                    Log::error("M-Pesa Success: Final wallet amount exceeds decimal(7,2) limit, amount=$wallet_amount, currency=$wallet_currency, user_id=$user_id");
                    DB::rollBack();
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'Wallet balance would exceed limit.',
                    ]);
                }

                $wallet->amount = number_format($wallet_amount, 2, '.', '');
                $wallet->paykey = $result->transaction_id;
                $wallet->currency_code = $wallet_currency;
                $wallet->user_id = $user_id;
                $wallet->save();
                Log::debug("M-Pesa Success: Wallet updated, wallet_id={$wallet->id}, user_id=$user_id, amount=$wallet_amount, currency=$wallet_currency");

                $transaction->description .= " Current Balance Amount is: " . $wallet_amount;
                $transaction->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("M-Pesa Success: Wallet update failed, user_id=$user_id, error={$e->getMessage()}");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'Failed to update wallet. Transaction recorded.',
                ]);
            }

            return $this->returnJson([
                'status' => 'success',
                'status_code' => '1',
                'status_message' => __('messages.wallet_add_success'),
                'wallet_amount' => number_format($wallet_amount, 2, '.', ''),
                'redirect' => 'balance',
            ]);
        } elseif ($result->pay_for == 'pay_to_admin') {
            $owe_amount = DriverOweAmount::where('user_id', $user_id)->first();
            if ($owe_amount && $owe_amount->amount > 0) {
                $payment_amount = floatval($result->amount);
                $converted_amount = currencyConvert($result->currency, $owe_amount->currency_code, $payment_amount);
                Log::debug("M-Pesa Success: Currency conversion for pay_to_admin, from_currency={$result->currency}, to_currency={$owe_amount->currency_code}, original_amount=$payment_amount, converted_amount=$converted_amount");

                if ($converted_amount > 99999.99) {
                    Log::error("M-Pesa Success: Converted amount exceeds decimal(7,2) limit for pay_to_admin, amount=$converted_amount, currency={$owe_amount->currency_code}, user_id=$user_id");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'Payment amount too large.',
                    ]);
                }

                if ($owe_amount->amount < $converted_amount) {
                    Log::error("M-Pesa Success: Invalid amount for pay_to_admin, converted_amount=$converted_amount, owe_amount={$owe_amount->amount}, currency={$owe_amount->currency_code}, user_id=$user_id");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => trans('messages.api.invalid'),
                    ]);
                }

                // Create PaymentTransaction
                $transaction = new PaymentTransaction;
                $transaction->pay_for = "Pay to Admin";
                $transaction->amount = $converted_amount;
                $transaction->transaction_id = $result->transaction_id;
                $transaction->currency_code = $owe_amount->currency_code;
                $transaction->description = "Payment to admin for owed amount.";
                $transaction->user_id = $user_id;
                $transaction->save();
                Log::debug("M-Pesa Success: PaymentTransaction created for pay_to_admin, id={$transaction->id}, amount=$converted_amount, currency={$owe_amount->currency_code}, transaction_id={$result->transaction_id}");

                DB::beginTransaction();
                try {
                    $remaining_amount = $owe_amount->amount - $converted_amount;
                    $owe_amount->amount = number_format($remaining_amount, 2, '.', '');
                    $owe_amount->currency_code = $result->currency;
                    $owe_amount->save();
                    Log::debug("M-Pesa Success: Owe amount updated, user_id=$user_id, remaining_amount=$remaining_amount, currency={$result->currency}");

                    $payment = new DriverOweAmountPayment;
                    $payment->user_id = $user_id;
                    $payment->transaction_id = $result->transaction_id;
                    $payment->amount = $converted_amount;
                    $payment->status = 1;
                    $payment->currency_code = $owe_amount->currency_code;
                    $payment->save();
                    Log::debug("M-Pesa Success: DriverOweAmountPayment created, id={$payment->id}, amount=$converted_amount, currency={$owe_amount->currency_code}, transaction_id={$result->transaction_id}");

                    $transaction->description .= " Remaining owe amount: " . $remaining_amount;
                    $transaction->save();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("M-Pesa Success: Owe amount update failed, user_id=$user_id, error={$e->getMessage()}");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'Failed to update owe amount. Transaction recorded.',
                    ]);
                }

                $referral_amount = ReferralUser::where('user_id', $user_id)
                    ->where('payment_status', 'Completed')
                    ->where('pending_amount', '>', 0)
                    ->sum('pending_amount');

                return $this->returnJson([
                    'status' => 'success',
                    'status_code' => '1',
                    'status_message' => __('messages.payment_complete_success'),
                    'referral_amount' => number_format($referral_amount, 2, '.', ''),
                    'owe_amount' => $owe_amount->amount,
                    'redirect' => 'balance',
                ]);
            }

            Log::error("M-Pesa Success: No valid owe amount found, user_id=$user_id");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => __('messages.api.not_generate_amount'),
            ]);
        } elseif ($result->pay_for == 'trip') {
            $trip = Trips::where('user_id', $user_id)
                ->where('payment_status', 'Pending')
                ->where('payment_mode', 'mpesa')
                ->latest()
                ->first();

            if (!$trip) {
                Log::error("M-Pesa Success: No pending trip found, user_id=$user_id");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'No pending trip found',
                ]);
            }

            if ($trip->is_calculation == 0) {
                $data = [
                    'trip_id' => $trip->id,
                    'user_id' => $user_id,
                    'save_to_trip_table' => 1,
                ];
                $trip = $this->invoice_helper->calculation($data);
            }

            $converted_amount = currencyConvert($result->currency, $user->currency_code, $result->amount);
            Log::debug("M-Pesa Success: Currency conversion for trip, from_currency={$result->currency}, to_currency={$user->currency_code}, original_amount=$result->amount, converted_amount=$converted_amount");

            if ($converted_amount > 99999.99) {
                Log::error("M-Pesa Success: Converted amount exceeds decimal(7,2) limit for trip, amount=$converted_amount, currency={$user->currency_code}, user_id=$user_id");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'Payment amount too large.',
                ]);
            }

            // Create PaymentTransaction
            $transaction = new PaymentTransaction;
            $transaction->pay_for = "Trip Payment";
            $transaction->amount = $converted_amount;
            $transaction->transaction_id = $result->transaction_id;
            $transaction->currency_code = $user->currency_code;
            $transaction->description = "Travel From " . $trip->pickup_location . " to " . $trip->drop_location;
            $transaction->user_id = $user_id;
            $transaction->save();
            Log::debug("M-Pesa Success: PaymentTransaction created for trip, id={$transaction->id}, amount=$converted_amount, currency={$user->currency_code}, transaction_id={$result->transaction_id}");

            DB::beginTransaction();
            try {
                $trip->status = 'Completed';
                $trip->paykey = $result->transaction_id;
                $trip->payment_status = 'Completed';
                $trip->save();
                Log::debug("M-Pesa Success: Trip updated, id={$trip->id}, user_id=$user_id, status=Completed, transaction_id={$result->transaction_id}");

                if ($trip->pool_id > 0) {
                    $pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
                    $trips = $pool_trip->trips->whereIn('status', ['Scheduled', 'Begin trip', 'End trip', 'Rating', 'Payment'])->count();
                    if (!$trips) {
                        $pool_trip->status = 'Completed';
                        $pool_trip->save();
                        Log::debug("M-Pesa Success: Pool trip updated, id={$pool_trip->id}, status=Completed");
                    }
                }

                Payment::updateOrCreate(
                    ['trip_id' => $trip->id],
                    [
                        'trip_id' => $trip->id,
                        'correlation_id' => $result->transaction_id,
                        'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
                    ]
                );
                Log::debug("M-Pesa Success: Payment record updated, trip_id={$trip->id}, correlation_id={$result->transaction_id}");

                $push_title = 'Payment Completed';
                $push_data = [
                    'push_title' => $push_title,
                    'data' => [
                        'trip_payment' => [
                            'status' => 'Paid',
                            'trip_id' => $trip->id,
                            'rider_thumb_image' => $trip->rider_profile_picture,
                        ],
                    ],
                ];
                $request_helper = new RequestHelper();
                $request_helper->SendPushNotification($trip->driver, $push_data);
                Log::debug("M-Pesa Success: Push notification sent, trip_id={$trip->id}, driver_id={$trip->driver->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("M-Pesa Success: Trip update failed, user_id=$user_id, error={$e->getMessage()}");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => 'Failed to update trip. Transaction recorded.',
                ]);
            }

            return $this->returnJson([
                'status' => 'success',
                'status_code' => '1',
                'status_message' => __('messages.payment_complete_success'),
                'redirect' => 'balance',
            ]);
        }
    }

    private function returnJson($payment_status)
    {
        Log::debug("M-Pesa: Returning JSON response, status={$payment_status['status']}, status_code={$payment_status['status_code']}, status_message={$payment_status['status_message']}" . (isset($payment_status['wallet_amount']) ? ", wallet_amount={$payment_status['wallet_amount']}" : ""));
        return response()->json($payment_status);
    }

    public function setUserLocale($locale)
    {
        App::setLocale($locale);
        Session::put('language', $locale);
    }
}
