<?php

/**
 * Paypal Payout Service
 *
 * @package     Witaxi
 * @subpackage  Services\Payouts
 * @category    Paypal
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Services\Payouts;

use App\Models\Country;

class BankTransferPayout
{
    /**
     * validate Request
     *
     * @param Request $[request]
     * @return Mixed
     */
    public function validateRequest($request)
    {
        $rules = [
            'account_holder_name' => 'required',
            'account_number'=> 'required',
            'bank_name'     => 'required',
            'bank_code'     => 'required',
            'bank_location' => 'required',
        ];

        $messages = [
            'required' => ':attribute '.trans('messages.home.field_is_required'),
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if(isApiRequest()) {
                return response()->json([
                    'status_code' => '0',
                    'status_message' => $validator->messages()->first(),
                ]);
            }
            return back()->withErrors($validator)->withInput();
        }
        return false;
    }

    public function createPayoutPreference($request)
    {
        $recipient['email'] = $request->account_number;
        $recipient['id'] = $request->account_number;
    	return [
    		'status'			=> true,
    		'recipient' 		=> arrayToObject($recipient),
    	];
    }

    public function makePayout($payout_account,$pay_data)
    {
        return [
            'status' => true,
            'status_message' => 'Payout status updated successfully',
            'transaction_id' => '',
        ];
    }
}