<?php

/**
 * Paytm Controller
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaytmController extends Controller
{
    protected $invoice_helper;

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

    public function payment()
    {
        $user = $this->set_user_deatils();
        set_payment_gateway_company_context($user->company_id ?? null);
        $this->setUserLocale($user->language);
        $data['currency_code'] = site_settings('payment_currency');
        $data['amount'] = currencyConvert($user->currency_code, $data['currency_code'], request()->amount);
        $this->setSessionData($data, $user);

        $business_logic = resolve(\App\Services\BusinessLogic::class);
        $validate = $business_logic->validate(session()->get('payment_data'), $this->get_user_deatils());
        if ($validate['status_code'] != '1') {
            if ($validate['status_code'] == '2') {
                $return = $business_logic->pay_to_admin(session()->get('payment_data'), $this->get_user_deatils());
            } else if ($validate['status_code'] == '3') {
                $return = $business_logic->tripPayment(session()->get('payment_data'), $this->get_user_deatils());
            } else {
                $return = response($validate);
            }
            return $this->returnJson($return);
        }

        $data['payment_method'] = $this->activePaymentMethod($user->company_id ?? null);
        foreach ($data['payment_method'] as $key => $method) {
            try {
                $service = 'App\Services\Payments\\' . ucfirst($method) . 'Payment';
                $this->payment = resolve($service);
            } catch (\Exception $e) {
                $this->payment = resolve(\App\Services\Payments\PaypalPayment::class);
            }
            $data['view'][$method] = $this->payment->view($user);
        }
        return view('payment.payment', $data);
    }

    public function activePaymentMethod($companyId = null)
    {
        return active_payment_gateway_sites($companyId);
    }

    public function get_user_deatils()
    {
        if (session()->has('payment_data.user_token') || request()->token) {
            if (request()->token) {
                return JWTAuth::parseToken()->authenticate();
            }
            $set_token = JWTAuth::setToken(session()->get('payment_data.user_token'));
            return $set_token->toUser();
        } else {
            return auth()->guard('web')->user();
        }
    }

    public function set_user_deatils()
    {
        if (request()->token) {
            return JWTAuth::parseToken()->authenticate();
        } else {
            return auth()->guard('web')->user();
        }
    }

    public function returnJson($payment_status)
    {
        $status = $payment_status['status'] ?? ($payment_status['status_code'] == '1' ? 'success' : 'error');
        $status_code = $payment_status['status_code'] ?? '0';
        $status_message = $payment_status['status_message'] ?? 'Unknown error';
        $response = [
            'status' => $status,
            'status_code' => $status_code,
            'status_message' => $status_message,
        ];
        if (isset($payment_status['wallet_amount'])) {
            $response['wallet_amount'] = $payment_status['wallet_amount'];
        }
        if (isset($payment_status['referral_amount'])) {
            $response['referral_amount'] = $payment_status['referral_amount'];
        }
        if (isset($payment_status['owe_amount'])) {
            $response['owe_amount'] = $payment_status['owe_amount'];
        }
        if ($status_code == '1') {
            $response['redirect'] = 'balance';
        }
        Log::debug("Paytm: Returning JSON response, status=$status, status_code=$status_code, status_message=$status_message" . (isset($payment_status['wallet_amount']) ? ", wallet_amount={$payment_status['wallet_amount']}" : ""));
        return response()->json($response);
    }

    public function getSaveCardData($user, $payment_method = '', $customer_id = '')
    {
        $card_details['payment_method'] = request()->paymentMethod_id;
        if ($customer_id != '') {
            $card_details['customer'] = $customer_id;
        }

        if ($payment_method) {
            $card_details = [
                'customer' => $payment_method->customer_id,
                'payment_method' => $payment_method->payment_method_id,
            ];
        }
        $data['payment_data'] = [
            'confirm' => true,
            'off_session' => true,
            'confirmation_method' => 'manual',
            'amount' => intval(session()->get('payment_data.amount') * 100),
            'currency' => site_settings('payment_currency'),
            'description' => session()->get('payment_data.pay_for') . ' Payment by ' . $user->first_name,
        ] + $card_details;
        $data['all_data'] = session()->get('payment_data');
        return $data;
    }

    public function getStripeData($service, $user)
    {
        if (request()->pay_key) {
            $data['payment_data'] = [];
            $data['all_data'] = session()->get('payment_data');
            return $data;
        }
        $card_details = '';
        $customer_id = '';
        if (request()->save_card_id || request()->save_card) {
            if (request()->save_card && !request()->save_card_id) {
                $payment_methods = PaymentMethod::where('user_id', $user->id)->first();
                $customer_id = $payment_methods ? $payment_methods->customer_id : '';

                if (!$customer_id) {
                    $customer = $service->createCustomer($user->email);
                    if ($customer->status == 'success') {
                        $customer_id = $customer->customer_id;
                    }
                }
                $card_details = $this->saveNewCard($user, $customer_id, request()->paymentMethod_id, $service);
                if (isset($card_details['status_code']) && $card_details['status_code'] == "0") {
                    return $card_details;
                }
            } else {
                $card_details = PaymentMethod::where('user_id', $user->id)->where('id', request()->save_card_id)->first();
            }
        } else {
            $customer = $service->createCustomer($user->email);
            if ($customer->status == 'success') {
                $customer_id = $customer->customer_id;
            }
            $attachCustomer = $service->attachCustomer($user, $customer_id, request()->paymentMethod_id);
            if (isset($attachCustomer['status_code']) && $attachCustomer['status_code'] == "0") {
                return $attachCustomer;
            }
            $card_details = '';
        }
        return $this->getSaveCardData($user, $card_details, $customer_id);
    }

    public function saveNewCard($user, $customer_id, $paymentMethod_id, $service)
    {
        $payment_details = new PaymentMethod;
        $payment_details->user_id = $user->id;
        $payment_details->customer_id = $customer_id;
        $attach = $service->attachCustomer($user, $customer_id, $paymentMethod_id);
        if (isset($attach['status_code']) && $attach['status_code'] == "0") {
            return $attach;
        }
        $payment_details->intent_id = $attach['intent_id'];
        $payment_details->payment_method_id = $attach['payment_method_id'];
        $payment_details->brand = $attach['brand'];
        $payment_details->last4 = $attach['last4'];
        $payment_details->save();

        PaymentMethod::where('user_id', $user->id)->where('id', '!=', $payment_details->id)->delete();
        return $payment_details;
    }

    public function getBraintreeData($service, $user)
    {
        $data['payment_data'] = session()->get('payment_data.amount');
        $data['key'] = request()->pay_key;
        $data['all_data'] = session()->get('payment_data');
        return $data;
    }

    public function getPaypalData($service, $user)
    {
        $data['payment_data'] = session()->get('payment_data.amount');
        $data['key'] = request()->pay_key;
        $data['all_data'] = session()->get('payment_data');
        return $data;
    }

    public function cancel()
    {
        $payment_status = [
            'status' => 'error',
            'status_code' => '0',
            'status_message' => 'Payment failed',
        ];
        return $this->returnJson($payment_status);
    }

    public function payment_action($payment, $paymentData)
    {
        $payment = json_decode(json_encode($payment), true);
        $data = array_merge($payment, $paymentData);

        $business_logic = resolve(\App\Services\BusinessLogic::class);
        if ($paymentData['pay_for'] == 'wallet') {
            return $business_logic->add_wallet($data, $this->get_user_deatils());
        } else if ($paymentData['pay_for'] == 'pay_to_admin') {
            return $business_logic->pay_to_admin($data, $this->get_user_deatils());
        } else {
            return $business_logic->tripPayment($data, $this->get_user_deatils());
        }
    }

    // Paytm

    public function paytm_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:99999.99',
            'pay_for' => 'required|in:wallet,pay_to_admin,trip',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            Log::error("Paytm: Validation failed, error={$validator->errors()->first()}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => $validator->errors()->first(),
            ]);
        }

        $user = $this->get_user_deatils();
        $order_id = uniqid();
        $amount = floatval($request->amount);
        $currency_code = 'INR'; // Paytm uses INR
        if ($user->currency_code != 'INR') {
            $amount = currencyConvert($user->currency_code, $currency_code, $amount);
        }

        $paytm = new Transaction();
        $paytm->user_id = $user->id;
        $paytm->payment_id = $order_id;
        $paytm->payment_type = "paytm";
        $paytm->currency = $currency_code;
        $paytm->amount = $amount;
        $paytm->pay_for = $request->pay_for;
        $paytm->status = 'Pending';
        $paytm->save();

        Log::debug("Paytm: Initiating payment, user_id={$user->id}, order_id=$order_id, amount=$amount, pay_for={$request->pay_for}, currency=INR");

        $data_for_request = $this->handlePaytmRequest($order_id, $amount);

        $paytm_txn_url = 'https://securegw-stage.paytm.in/theia/processTransaction';
        $paramList = $data_for_request['paramList'];
        $checkSum = $data_for_request['checkSum'];

        return view('payment.paytm', compact('paytm_txn_url', 'paramList', 'checkSum'));
    }

    public function handlePaytmRequest($order_id, $amount)
    {
        $this->getAllEncdecFunc();
        $this->getConfigPaytmSettings();

        $MID = payment_gateway('paytm_merchant', 'Paytm');
        $paytm_secret = payment_gateway('paytm_secret', 'Paytm');

        $checkSum = "";
        $paramList = [];

        $paramList["MID"] = $MID;
        $paramList["ORDER_ID"] = $order_id;
        $paramList["CUST_ID"] = $order_id;
        $paramList["INDUSTRY_TYPE_ID"] = 'Retail';
        $paramList["CHANNEL_ID"] = 'WEB';
        $paramList["TXN_AMOUNT"] = number_format($amount, 2, '.', '');
        $paramList["WEBSITE"] = 'WEBSTAGING';
        $paramList["CALLBACK_URL"] = url('/api/paytm_callback');
        $paytm_merchant_key = $paytm_secret;

        $checkSum = getChecksumFromArray($paramList, $paytm_merchant_key);

        return [
            'checkSum' => $checkSum,
            'paramList' => $paramList,
        ];
    }

    public function paytm_callback(Request $request)
    {
        $order_id = $request['ORDERID'];
        $paytm_payment = Transaction::where('payment_id', $order_id)->where('status', 'Pending')->latest()->first();

        if (!$paytm_payment) {
            Log::error("Paytm Callback: No transaction found, order_id=$order_id");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'Transaction not found',
            ]);
        }

        $user = User::find($paytm_payment->user_id);
        if (!$user) {
            Log::error("Paytm Callback: User not found, user_id={$paytm_payment->user_id}");
            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => 'User not found',
            ]);
        }

        if ('TXN_SUCCESS' === $request['STATUS']) {
            $transaction_id = $request['TXNID'];
            $paytm_payment->status = 'Paid';
            $paytm_payment->transaction_id = $transaction_id;
            $paytm_payment->payment_desc = $request['RESPMSG'] ?? 'Transaction successful';
            $paytm_payment->save();
            Log::debug("Paytm Callback: Transaction updated, id={$paytm_payment->id}, status=Paid, transaction_id=$transaction_id");

            if ($paytm_payment->pay_for == "wallet") {
                DB::beginTransaction();
                try {
                    $payment_amount = floatval($paytm_payment->amount);
                    $wallet_currency = $user->currency_code;
                    $converted_amount = currencyConvert($paytm_payment->currency, $wallet_currency, $payment_amount);
                    Log::debug("Paytm Callback: Currency conversion, from_currency={$paytm_payment->currency}, to_currency=$wallet_currency, original_amount=$payment_amount, converted_amount=$converted_amount");

                    if ($converted_amount > 99999.99) {
                        Log::error("Paytm Callback: Converted amount exceeds decimal(7,2) limit, amount=$converted_amount, currency=$wallet_currency, user_id={$user->id}");
                        DB::rollBack();
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
                    $transaction->transaction_id = $transaction_id;
                    $transaction->currency_code = $wallet_currency;
                    $transaction->description = "Your app wallet has been successfully topped up.";
                    $transaction->user_id = $user->id;
                    $transaction->save();
                    Log::debug("Paytm Callback: PaymentTransaction created, id={$transaction->id}, amount=$converted_amount, currency=$wallet_currency, transaction_id=$transaction_id");

                    // Update wallet
                    $wallet = Wallet::whereUserId($user->id)->first();
                    $wallet_amount = $converted_amount;
                    $previous_amount = 0.00;
                    if ($wallet) {
                        $previous_amount = floatval($wallet->amount);
                        if ($previous_amount > 99999.99) {
                            Log::warning("Paytm Callback: Invalid wallet amount detected, wallet_id={$wallet->id}, amount=$previous_amount, currency=$wallet_currency, user_id={$user->id}. Resetting to 0.00.");
                            $previous_amount = 0.00;
                        }
                        $wallet_amount = $previous_amount + $converted_amount;
                        Log::debug("Paytm Callback: Wallet calculation, wallet_id={$wallet->id}, previous=$previous_amount, added=$converted_amount, new=$wallet_amount, currency=$wallet_currency");
                    } else {
                        $wallet = new Wallet;
                        Log::debug("Paytm Callback: New wallet created for user_id={$user->id}, amount=$wallet_amount, currency=$wallet_currency");
                    }

                    if ($wallet_amount > 99999.99) {
                        Log::error("Paytm Callback: Final wallet amount exceeds decimal(7,2) limit, amount=$wallet_amount, currency=$wallet_currency, user_id={$user->id}");
                        DB::rollBack();
                        return $this->returnJson([
                            'status' => 'error',
                            'status_code' => '0',
                            'status_message' => 'Wallet balance would exceed limit.',
                        ]);
                    }

                    $wallet->amount = number_format($wallet_amount, 2, '.', '');
                    $wallet->paykey = $transaction_id;
                    $wallet->currency_code = $wallet_currency;
                    $wallet->user_id = $user->id;
                    $wallet->save();
                    Log::debug("Paytm Callback: Wallet updated, wallet_id={$wallet->id}, user_id={$user->id}, amount=$wallet_amount, currency=$wallet_currency");

                    $transaction->description .= " Current Balance Amount is: " . $wallet_amount;
                    $transaction->save();

                    DB::commit();

                    return $this->returnJson([
                        'status' => 'success',
                        'status_code' => '1',
                        'status_message' => __('messages.wallet_add_success'),
                        'wallet_amount' => number_format($wallet_amount, 2, '.', ''),
                        'redirect' => 'balance',
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Paytm Callback: Wallet update failed, user_id={$user->id}, error={$e->getMessage()}");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'Failed to update wallet. Transaction recorded.',
                    ]);
                }
            } else if ($paytm_payment->pay_for == "pay_to_admin") {
                $owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
                if ($owe_amount && $owe_amount->amount > 0) {
                    $payment_amount = floatval($paytm_payment->amount);
                    $converted_amount = currencyConvert($paytm_payment->currency, $owe_amount->currency_code, $payment_amount);
                    Log::debug("Paytm Callback: Currency conversion for pay_to_admin, from_currency={$paytm_payment->currency}, to_currency={$owe_amount->currency_code}, original_amount=$payment_amount, converted_amount=$converted_amount");

                    if ($converted_amount > 99999.99) {
                        Log::error("Paytm Callback: Converted amount exceeds decimal(7,2) limit for pay_to_admin, amount=$converted_amount, currency={$owe_amount->currency_code}, user_id={$user->id}");
                        return $this->returnJson([
                            'status' => 'error',
                            'status_code' => '0',
                            'status_message' => 'Payment amount too large.',
                        ]);
                    }

                    if ($owe_amount->amount < $converted_amount) {
                        Log::error("Paytm Callback: Invalid amount for pay_to_admin, converted_amount=$converted_amount, owe_amount={$owe_amount->amount}, currency={$owe_amount->currency_code}, user_id={$user->id}");
                        return $this->returnJson([
                            'status' => 'error',
                            'status_code' => '0',
                            'status_message' => trans('messages.api.invalid'),
                        ]);
                    }

                    DB::beginTransaction();
                    try {
                        $remaining_amount = $owe_amount->amount - $converted_amount;
                        $owe_amount->amount = number_format($remaining_amount, 2, '.', '');
                        $owe_amount->currency_code = $owe_amount->currency_code;
                        $owe_amount->save();
                        Log::debug("Paytm Callback: Owe amount updated, user_id={$user->id}, remaining_amount=$remaining_amount, currency={$owe_amount->currency_code}");

                        $payment = new DriverOweAmountPayment;
                        $payment->user_id = $user->id;
                        $payment->transaction_id = $transaction_id;
                        $payment->amount = $converted_amount;
                        $payment->status = 1;
                        $payment->currency_code = $owe_amount->currency_code;
                        $payment->save();
                        Log::debug("Paytm Callback: DriverOweAmountPayment created, id={$payment->id}, amount=$converted_amount, currency={$owe_amount->currency_code}, transaction_id=$transaction_id");

                        DB::commit();

                        $referral_amount = ReferralUser::where('user_id', $user->id)
                            ->where('payment_status', 'Completed')
                            ->where('pending_amount', '>', 0)
                            ->sum('pending_amount');

                        return $this->returnJson([
                            'status' => 'success',
                            'status_code' => '1',
                            'status_message' => __('messages.payment_complete_success'),
                            'referral_amount' => number_format($referral_amount, 2, '.', ''),
                            'owe_amount' => number_format($remaining_amount, 2, '.', ''),
                            'redirect' => 'balance',
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Paytm Callback: Owe amount update failed, user_id={$user->id}, error={$e->getMessage()}");
                        return $this->returnJson([
                            'status' => 'error',
                            'status_code' => '0',
                            'status_message' => 'Failed to update owe amount.',
                        ]);
                    }
                }

                Log::error("Paytm Callback: No valid owe amount found, user_id={$user->id}");
                return $this->returnJson([
                    'status' => 'error',
                    'status_code' => '0',
                    'status_message' => __('messages.api.not_generate_amount'),
                ]);
            } else if ($paytm_payment->pay_for == "trip") {
                $trip = Trips::where('user_id', $user->id)
                    ->where('payment_status', 'Pending')
                    ->where('payment_mode', 'paytm')
                    ->latest()
                    ->first();

                if (!$trip) {
                    Log::error("Paytm Callback: No pending trip found, user_id={$user->id}");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'No pending trip found',
                    ]);
                }

                if ($trip->is_calculation == 0) {
                    $data = [
                        'trip_id' => $trip->id,
                        'user_id' => $user->id,
                        'save_to_trip_table' => 1,
                    ];
                    $trip = $this->invoice_helper->calculation($data);
                }

                $converted_amount = currencyConvert($paytm_payment->currency, $user->currency_code, $paytm_payment->amount);
                Log::debug("Paytm Callback: Currency conversion for trip, from_currency={$paytm_payment->currency}, to_currency={$user->currency_code}, original_amount={$paytm_payment->amount}, converted_amount=$converted_amount");

                if ($converted_amount > 99999.99) {
                    Log::error("Paytm Callback: Converted amount exceeds decimal(7,2) limit for trip, amount=$converted_amount, currency={$user->currency_code}, user_id={$user->id}");
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
                $transaction->transaction_id = $transaction_id;
                $transaction->currency_code = $user->currency_code;
                $transaction->description = "Travel From " . $trip->pickup_location . " to " . $trip->drop_location;
                $transaction->user_id = $user->id;
                $transaction->save();
                Log::debug("Paytm Callback: PaymentTransaction created for trip, id={$transaction->id}, amount=$converted_amount, currency={$user->currency_code}, transaction_id=$transaction_id");

                DB::beginTransaction();
                try {
                    $trip->status = 'Completed';
                    $trip->paykey = $transaction_id;
                    $trip->payment_status = 'Completed';
                    $trip->save();
                    Log::debug("Paytm Callback: Trip updated, id={$trip->id}, user_id={$user->id}, status=Completed, transaction_id=$transaction_id");

                    if ($trip->pool_id > 0) {
                        $pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
                        $trips = $pool_trip->trips->whereIn('status', ['Scheduled', 'Begin trip', 'End trip', 'Rating', 'Payment'])->count();
                        if (!$trips) {
                            $pool_trip->status = 'Completed';
                            $pool_trip->save();
                            Log::debug("Paytm Callback: Pool trip updated, id={$pool_trip->id}, status=Completed");
                        }
                    }

                    Payment::updateOrCreate(
                        ['trip_id' => $trip->id],
                        [
                            'trip_id' => $trip->id,
                            'correlation_id' => $transaction_id,
                            'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
                        ]
                    );
                    Log::debug("Paytm Callback: Payment record updated, trip_id={$trip->id}, correlation_id=$transaction_id");

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
                    Log::debug("Paytm Callback: Push notification sent, trip_id={$trip->id}, driver_id={$trip->driver->id}");

                    DB::commit();

                    return $this->returnJson([
                        'status' => 'success',
                        'status_code' => '1',
                        'status_message' => __('messages.payment_complete_success'),
                        'redirect' => 'balance',
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Paytm Callback: Trip update failed, user_id={$user->id}, error={$e->getMessage()}");
                    return $this->returnJson([
                        'status' => 'error',
                        'status_code' => '0',
                        'status_message' => 'Failed to update trip. Transaction recorded.',
                    ]);
                }
            }
        } else if ('TXN_FAILURE' === $request['STATUS']) {
            $paytm_payment->status = 'Failed';
            $paytm_payment->payment_desc = $request['RESPMSG'] ?? 'Transaction failed';
            $paytm_payment->save();
            Log::debug("Paytm Callback: Transaction updated, id={$paytm_payment->id}, status=Failed, payment_desc={$paytm_payment->payment_desc}");

            return $this->returnJson([
                'status' => 'error',
                'status_code' => '0',
                'status_message' => trans($request['RESPMSG'] ?? 'Payment failed'),
            ]);
        }

        Log::error("Paytm Callback: Invalid status received, order_id=$order_id, status={$request['STATUS']}");
        return $this->returnJson([
            'status' => 'error',
            'status_code' => '0',
            'status_message' => 'Invalid transaction status',
        ]);
    }

    public function getAllEncdecFunc()
    {
        function encrypt_e($input, $ky)
        {
            $key = html_entity_decode($ky);
            $iv = "@@@@&&&&####$$$$";
            $data = openssl_encrypt($input, "AES-128-CBC", $key, 0, $iv);
            return $data;
        }

        function decrypt_e($crypt, $ky)
        {
            $key = html_entity_decode($ky);
            $iv = "@@@@&&&&####$$$$";
            $data = openssl_decrypt($crypt, "AES-128-CBC", $key, 0, $iv);
            return $data;
        }

        function generateSalt_e($length)
        {
            $random = "";
            srand((double) microtime() * 1000000);
            $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
            $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
            $data .= "0FGH45OP89";
            for ($i = 0; $i < $length; $i++) {
                $random .= substr($data, (rand() % (strlen($data))), 1);
            }
            return $random;
        }

        function checkString_e($value)
        {
            if ($value == 'null') {
                $value = '';
            }
            return $value;
        }

        function getChecksumFromArray($arrayList, $key, $sort = 1)
        {
            if ($sort != 0) {
                ksort($arrayList);
            }
            $str = getArray2Str($arrayList);
            $salt = generateSalt_e(4);
            $finalString = $str . "|" . $salt;
            $hash = hash("sha256", $finalString);
            $hashString = $hash . $salt;
            $checksum = encrypt_e($hashString, $key);
            return $checksum;
        }

        function getChecksumFromString($str, $key)
        {
            $salt = generateSalt_e(4);
            $finalString = $str . "|" . $salt;
            $hash = hash("sha256", $finalString);
            $hashString = $hash . $salt;
            $checksum = encrypt_e($hashString, $key);
            return $checksum;
        }

        function verifychecksum_e($arrayList, $key, $checksumvalue)
        {
            $arrayList = removeCheckSumParam($arrayList);
            ksort($arrayList);
            $str = getArray2StrForVerify($arrayList);
            $paytm_hash = decrypt_e($checksumvalue, $key);
            $salt = substr($paytm_hash, -4);
            $finalString = $str . "|" . $salt;
            $website_hash = hash("sha256", $finalString);
            $website_hash .= $salt;
            $validFlag = "FALSE";
            if ($website_hash == $paytm_hash) {
                $validFlag = "TRUE";
            }
            return $validFlag;
        }

        function verifychecksum_eFromStr($str, $key, $checksumvalue)
        {
            $paytm_hash = decrypt_e($checksumvalue, $key);
            $salt = substr($paytm_hash, -4);
            $finalString = $str . "|" . $salt;
            $website_hash = hash("sha256", $finalString);
            $website_hash .= $salt;
            $validFlag = "FALSE";
            if ($website_hash == $paytm_hash) {
                $validFlag = "TRUE";
            }
            return $validFlag;
        }

        function getArray2Str($arrayList)
        {
            $findme = 'REFUND';
            $findmepipe = '|';
            $paramStr = "";
            $flag = 1;
            foreach ($arrayList as $key => $value) {
                $pos = strpos($value, $findme);
                $pospipe = strpos($value, $findmepipe);
                if ($pos !== false || $pospipe !== false) {
                    continue;
                }
                if ($flag) {
                    $paramStr .= checkString_e($value);
                    $flag = 0;
                } else {
                    $paramStr .= "|" . checkString_e($value);
                }
            }
            return $paramStr;
        }

        function getArray2StrForVerify($arrayList)
        {
            $paramStr = "";
            $flag = 1;
            foreach ($arrayList as $key => $value) {
                if ($flag) {
                    $paramStr .= checkString_e($value);
                    $flag = 0;
                } else {
                    $paramStr .= "|" . checkString_e($value);
                }
            }
            return $paramStr;
        }

        function removeCheckSumParam($arrayList)
        {
            if (isset($arrayList["CHECKSUMHASH"])) {
                unset($arrayList["CHECKSUMHASH"]);
            }
            return $arrayList;
        }
    }

    public function getConfigPaytmSettings()
    {
        $MID = payment_gateway('paytm_merchant', 'Paytm');
        $paytm_secret = payment_gateway('paytm_secret', 'Paytm');
        $paytm_mode = payment_gateway('mode', 'Paytm');

        define('PAYTM_ENVIRONMENT', $paytm_mode);
        define('PAYTM_MERCHANT_KEY', $paytm_secret);
        define('PAYTM_MERCHANT_MID', $MID);
        define('PAYTM_MERCHANT_WEBSITE', 'WEBSTAGING');

        $PAYTM_STATUS_QUERY_NEW_URL = 'https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
        $PAYTM_TXN_URL = 'https://securegw-stage.paytm.in/theia/processTransaction';
        if (PAYTM_ENVIRONMENT == 'live') {
            $PAYTM_STATUS_QUERY_NEW_URL = 'https://securegw.paytm.in/merchant-status/getTxnStatus';
            $PAYTM_TXN_URL = 'https://securegw.paytm.in/theia/processTransaction';
        }

        define('PAYTM_REFUND_URL', '');
        define('PAYTM_STATUS_QUERY_URL', $PAYTM_STATUS_QUERY_NEW_URL);
        define('PAYTM_STATUS_QUERY_NEW_URL', $PAYTM_STATUS_QUERY_NEW_URL);
        define('PAYTM_TXN_URL', $PAYTM_TXN_URL);
    }

    public function setUserLocale($locale)
    {
        App::setLocale($locale);
        Session::put('language', $locale);
    }
}
