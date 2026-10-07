<?php

/**
 * Flutterwave Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Payment
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
use App\Models\PoolTrip;
use App\Models\DriverOweAmount;
use App\Models\ReferralUser;
use App\Models\AppliedReferrals;
use App\Models\DriverOweAmountPayment;
use App\Http\Helper\InvoiceHelper;
use App\Http\Helper\RequestHelper;
use App\Models\Transaction;
use App\Models\PaymentTransaction;
use JWTAuth;
use Session;
use Log;

class FlutterwaveController extends Controller
{
    public function __construct(InvoiceHelper $invoice_helper)
    {
        $this->invoice_helper = $invoice_helper;
    }

    public function setSessonData($data, $user)
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
        session()->put('payment_data', $value);
    }

    public static function flutterwave(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        app(\App\Services\Corporate\CorporateCollectionGuard::class)->validateEntry($request, $user);
        set_payment_gateway_company_context($user->company_id ?? null);
        $payment_id = uniqid();

        $data['amount'] = $request->amount;
        $data['currency_code'] = $user->currency_code;
        $data['payment_type'] = 'flutterwave';
        $data['pay_for'] = $request->pay_for;
        $data['token'] = $request->token;
        $data['user_id'] = $user->id;

        return view('payment.flutterwave', $data);
    }

    public function flutterwave_payment(Request $request)
    {
        app(\App\Services\Corporate\CorporateCollectionGuard::class)->validateEntry($request);
        $user = User::find($request->user_id);
        set_payment_gateway_company_context($user->company_id ?? null);
        $curl = curl_init();
        $email = $request->email;
        $amount = $request->amount;

        $customer_email = $email;
        $amount_pay = $amount;
        $currency = $request->currency_code;
        $txref = "rave" . uniqid();

        // Store the transaction data to the database
        $Transaction = new Transaction();
        $Transaction->user_id = $request->user_id;
        $Transaction->payment_id = $txref;
        $Transaction->amount = $request->amount;
        $Transaction->payment_type = "flutterwave";
        $Transaction->currency = $request->currency_code;
        $Transaction->pay_for = $request->pay_for;
        $Transaction->save();
        Log::debug("Flutterwave Payment: Transaction created, payment_id=$txref, amount=$amount, currency=$currency, user_id={$request->user_id}");

        // Get your public key from the dashboard
        $PBFPubKey = payment_gateway('public_key', 'Flutterwave');

        $current_site_url = "https://" . $_SERVER['HTTP_HOST'];
        $redirect_url = $current_site_url . "/api/flutterwave_callback?email=$email&amount=$amount_pay";

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/hosted/pay",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount_pay,
                'customer_email' => $customer_email,
                'currency' => $currency,
                'txref' => $txref,
                'PBFPubKey' => $PBFPubKey,
                'redirect_url' => $redirect_url,
            ]),
            CURLOPT_HTTPHEADER => [
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            Log::error("Flutterwave Payment: cURL error: $err");
            die('Curl returned error: ' . $err);
        }

        $transaction = json_decode($response);

        if (!$transaction->data || !property_exists($transaction->data, 'link')) {
            $error_message = $transaction->message ?? 'Missing link in transaction data';
            Log::error("Flutterwave Payment: API error: $error_message");
            if ($transaction->message) {
                print_r('API returned error: ' . $transaction->message);
            } else {
                print_r('API returned error: Missing link in transaction data.');
            }
        } else {
            header('Location: ' . $transaction->data->link);
            exit;
        }
    }

    public function flutterwave_callback(Request $request)
    {
        $postData = request()->input('resp');
        $data = json_decode($postData, true);

        // Extract the required data
        $txRef = $data['tx']['txRef'] ?? null;
        $flwRef = $data['tx']['flwRef'] ?? null;
        $raveRef = $data['tx']['raveRef'] ?? null;
        $amount = $data['tx']['amount'] ?? 0;
        $responseCode = $data['tx']['chargeResponseCode'] ?? 'N/A';
        $status = $data['tx']['status'] ?? 'N/A';
        $currency = $data['tx']['currency'] ?? 'N/A';

        Log::debug("Flutterwave Callback: txRef=$txRef, flwRef=$flwRef, raveRef=$raveRef, responseCode=$responseCode, amount=$amount, currency=$currency");

        if ($responseCode == "00") {
            $transaction_id = $txRef;
            $payment_transaction_id = $txRef ?? $flwRef ?? 'FLW-' . uniqid(); // Ensure non-null transaction_id
            $flutterwave_payment = Transaction::where('payment_id', $txRef)->where('status', 'Pending')->latest()->first();
            $user = User::where('id', $flutterwave_payment->user_id)->first();

            if ($flutterwave_payment) {
                $flutterwave_transaction = Transaction::find($flutterwave_payment->id);

                if ($flutterwave_transaction) {
                    $flutterwave_transaction->status = 'Paid';
                    $flutterwave_transaction->transaction_id = $payment_transaction_id;
                    $flutterwave_transaction->payment_desc = $status;
                    $flutterwave_transaction->save();
                    Log::debug("Flutterwave Callback: Transaction updated, id={$flutterwave_transaction->id}, transaction_id=$payment_transaction_id");
                }

                if ($flutterwave_payment->pay_for == "wallet") {
                    $wallet = Wallet::whereUserId($flutterwave_payment->user_id)->first();
                    $payment_amount = floatval($flutterwave_payment->amount);

                    // Determine wallet currency (use user currency if no wallet exists)
                    $wallet_currency = $wallet ? $wallet->currency_code : $user->currency_code;

                    // Convert payment amount to wallet currency
                    $converted_amount = currencyConvert($wallet_currency, $flutterwave_payment->currency, $payment_amount);
                    Log::debug("Flutterwave Callback: Currency conversion, from_currency={$flutterwave_payment->currency}, to_currency=$wallet_currency, original_amount=$payment_amount, converted_amount=$converted_amount");

                    // Validate converted amount against decimal(8,2)
                    if ($converted_amount > 999999.99) {
                        Log::error("Flutterwave Callback: Converted amount exceeds decimal(8,2) limit, amount=$converted_amount, currency=$wallet_currency, user_id={$user->id}");
                        $paymentData = [
                            'status_code' => '0',
                            'status_message' => 'Payment amount too large for wallet.',
                        ];
                        return $this->returnView($paymentData);
                    }

                    // Validate existing wallet amount
                    if ($wallet && floatval($wallet->amount) > 999999.99) {
                        Log::warning("Flutterwave Callback: Suspicious wallet amount, wallet_id={$wallet->id}, amount={$wallet->amount}, currency=$wallet_currency, user_id={$user->id}");
                        $paymentData = [
                            'status_code' => '0',
                            'status_message' => 'Invalid wallet balance detected.',
                        ];
                        return $this->returnView($paymentData);
                    }

                    // Calculate new wallet amount
                    if ($wallet) {
                        $previous_amount = floatval($wallet->amount);
                        $wallet_amount = $previous_amount + $converted_amount;
                        Log::debug("Flutterwave Callback: Wallet calculation, wallet_id={$wallet->id}, previous=$previous_amount, added=$converted_amount, new=$wallet_amount, currency=$wallet_currency");
                    } else {
                        $wallet = new Wallet;
                        $wallet_amount = $converted_amount;
                        Log::debug("Flutterwave Callback: New wallet created for user_id={$flutterwave_payment->user_id}, amount=$wallet_amount, currency=$wallet_currency");
                    }

                    // Validate final wallet amount
                    if ($wallet_amount > 999999.99) {
                        Log::error("Flutterwave Callback: Final wallet amount exceeds decimal(8,2) limit, amount=$wallet_amount, currency=$wallet_currency, user_id={$user->id}");
                        $paymentData = [
                            'status_code' => '0',
                            'status_message' => 'Wallet balance would exceed limit.',
                        ];
                        return $this->returnView($paymentData);
                    }

                    $wallet->amount = number_format($wallet_amount, 2, '.', '');
                    $wallet->paykey = $transaction_id;
                    $wallet->currency_code = $wallet_currency;
                    $wallet->user_id = $user->id;
                    $wallet->save();
                    Log::debug("Flutterwave Callback: Wallet updated, wallet_id={$wallet->id}, user_id={$user->id}, amount=$wallet_amount, currency=$wallet_currency");

                    // Create PaymentTransaction record
                    $transaction = new PaymentTransaction;
                    $transaction->pay_for = "Wallet TopUp";
                    $transaction->amount = $converted_amount;
                    $transaction->transaction_id = $payment_transaction_id;
                    $transaction->currency_code = $wallet_currency;
                    $transaction->description = "Your app wallet has been successfully topped up. Current Balance Amount is: " . $wallet_amount;
                    $transaction->user_id = $flutterwave_payment->user_id;
                    $transaction->save();
                    Log::debug("Flutterwave Callback: PaymentTransaction created, id={$transaction->id}, amount={$transaction->amount}, currency={$transaction->currency_code}, transaction_id=$payment_transaction_id");

                    $paymentData = [
                        'status_code' => '1',
                        'status_message' => __('messages.wallet_add_success'),
                        'wallet_amount' => (string) floatval($wallet_amount),
                        'currency_code' => $wallet_currency,
                    ];
                    return $this->returnView($paymentData);
                } elseif ($flutterwave_payment->pay_for == "pay_to_admin") {
                    $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
                    if ($owe_amount && $owe_amount->amount > 0) {
                        $amount = floatval($flutterwave_payment->amount);
                        $converted_amount = currencyConvert($owe_amount->currency_code, $flutterwave_payment->currency, $amount);
                        Log::debug("Flutterwave Callback: Currency conversion for pay_to_admin, from_currency={$flutterwave_payment->currency}, to_currency={$owe_amount->currency_code}, original_amount=$amount, converted_amount=$converted_amount");

                        if ($converted_amount > 999999.99) {
                            Log::error("Flutterwave Callback: Converted amount exceeds decimal(8,2) limit for pay_to_admin, amount=$converted_amount, currency={$owe_amount->currency_code}, user_id={$user->id}");
                            $paymentData = [
                                'status_code' => '0',
                                'status_message' => 'Payment amount too large.',
                            ];
                            return $this->returnView($paymentData);
                        }

                        if ($amount > 0) {
                            if ($owe_amount->amount < $converted_amount) {
                                Log::error("Flutterwave Callback: Invalid amount, converted_amount=$converted_amount, owe_amount={$owe_amount->amount}, currency={$owe_amount->currency_code}");
                                $paymentData = [
                                    'status_code' => '0',
                                    'status_message' => trans('messages.api.invalid'),
                                ];
                                return $this->returnView($paymentData);
                            }

                            $total_owe_amount = $owe_amount->amount;
                            $remaining_amount = $total_owe_amount - $converted_amount;

                            $owe_amount->amount = number_format($remaining_amount, 2, '.', '');
                            $owe_amount->save();

                            $payment = new DriverOweAmountPayment;
                            $payment->user_id = $user->id;
                            $payment->transaction_id = $payment_transaction_id;
                            $payment->amount = $converted_amount;
                            $payment->status = 1;
                            $payment->currency_code = $owe_amount->currency_code;
                            $payment->save();

                            // Create PaymentTransaction record
                            $transaction = new PaymentTransaction;
                            $transaction->pay_for = "Pay to Admin";
                            $transaction->amount = $converted_amount;
                            $transaction->transaction_id = $payment_transaction_id;
                            $transaction->currency_code = $owe_amount->currency_code;
                            $transaction->description = "Payment to admin for owed amount. Remaining owe amount: " . $remaining_amount;
                            $transaction->user_id = $flutterwave_payment->user_id;
                            $transaction->save();
                            Log::debug("Flutterwave Callback: PaymentTransaction created for pay_to_admin, id={$transaction->id}, amount={$transaction->amount}, currency={$transaction->currency_code}, transaction_id=$payment_transaction_id");

                            $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
                        }

                        $referral_amount = ReferralUser::where('user_id', $user->id)->where('payment_status', 'Completed')->where('pending_amount', '>', 0)->get();
                        $referral_amount = number_format($referral_amount->sum('pending_amount'), 2, '.', '');

                        $paymentData = [
                            'status_code' => '1',
                            'status_message' => __('messages.payment_complete_success'),
                            'referral_amount' => $referral_amount,
                            'owe_amount' => $owe_amount->amount,
                            'currency_code' => $owe_amount->currency_code,
                        ];
                        return $this->returnView($paymentData);
                    }

                    $paymentData = [
                        'status_code' => '0',
                        'status_message' => __('messages.api.not_generate_amount'),
                    ];
                    return $this->returnView($paymentData);
                } elseif ($flutterwave_payment->pay_for == "trip") {
                    $trip = Trips::where('user_id', $user->id)->where('payment_status', "Pending")->where('payment_mode', "flutterwave")->latest()->first();

                    if (!$trip) {
                        Log::error("Flutterwave Callback: No pending trip found, user_id={$user->id}");
                        $paymentData = [
                            'status_code' => '0',
                            'status_message' => __('messages.api.no_pending_trip'),
                        ];
                        return $this->returnView($paymentData);
                    }

                    app(\App\Services\Corporate\CorporateCollectionGuard::class)->assertPersonalTrip($trip, $user);
                    if ($trip->is_calculation == 0) {
                        $data = [
                            'trip_id' => $trip->id,
                            'user_id' => $user->id,
                            'save_to_trip_table' => 1,
                        ];
                        $trip = $this->invoice_helper->calculation($data);
                    }

                    $converted_amount = currencyConvert($user->currency_code, $flutterwave_payment->currency, $trip->total_fare);
                    Log::debug("Flutterwave Callback: Currency conversion for trip, from_currency={$flutterwave_payment->currency}, to_currency={$user->currency_code}, original_amount={$trip->total_fare}, converted_amount=$converted_amount");

                    if ($converted_amount > 999999.99) {
                        Log::error("Flutterwave Callback: Converted amount exceeds decimal(8,2) limit for trip, amount=$converted_amount, currency={$user->currency_code}, user_id={$user->id}");
                        $paymentData = [
                            'status_code' => '0',
                            'status_message' => 'Payment amount too large.',
                        ];
                        return $this->returnView($paymentData);
                    }

                    $trip->status = 'Completed';
                    $trip->paykey = $transaction_id ?? '';
                    $trip->payment_status = 'Completed';
                    $trip->save();

                    // Create PaymentTransaction record
                    $transaction = new PaymentTransaction;
                    $transaction->pay_for = "Trip Payment";
                    $transaction->amount = $converted_amount;
                    $transaction->transaction_id = $payment_transaction_id;
                    $transaction->currency_code = $user->currency_code;
                    $transaction->description = "Travel From " . $trip->pickup_location . " to " . $trip->drop_location;
                    $transaction->user_id = $user->id;
                    $transaction->save();
                    Log::debug("Flutterwave Callback: PaymentTransaction created for trip, id={$transaction->id}, amount={$transaction->amount}, currency={$transaction->currency_code}, transaction_id=$payment_transaction_id");

                    if ($trip->pool_id > 0) {
                        $pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
                        $trips = $pool_trip->trips->whereIn('status', ['Scheduled', 'Begin trip', 'End trip', 'Rating', 'Payment'])->count();

                        if (!$trips) {
                            $pool_trip->status = 'Completed';
                            $pool_trip->save();
                        }
                    }

                    $data = [
                        'trip_id' => $trip->id,
                        'correlation_id' => $transaction_id ?? '',
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

            Log::error("Flutterwave Callback: Transaction not found, txRef=$txRef");
            $paymentData = [
                'status_code' => '0',
                'status_message' => __('messages.api.transaction_not_found'),
            ];
            return $this->returnView($paymentData);
        } else {
            Log::error("Flutterwave Callback: Payment failed, responseCode=$responseCode");
            $paymentData = [
                'status_code' => '0',
                'status_message' => trans('This Transaction was not successful, kindly try again later'),
            ];
            return $this->returnView($paymentData);
        }
    }

    public function returnView($payment_status)
    {
        return view('web_payment.view', compact('payment_status'));
    }

    public function setUserLocale($locale)
    {
        App::setLocale($locale);
        Session::put('language', $locale);
    }
}
