<?php

/**
 * Wallet Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Wallet
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use Excel;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\ReferralUser;
use App\Services\Notifications\NotificationManager;
use Validator;
use DB;

class WalletController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers
    protected $notifications;

    public function __construct(NotificationManager $notifications)
    {
        $this->view_data['main_title']  = 'Manage Wallet';
        $user_type = strtolower(request()->user_type ?? 'rider');
        $this->view_data['user_type'] =  $this->user_type = ($user_type == 'rider') ? 'Rider':'Driver';
        $this->view_data['navigation'] = 'manage_wallet';
        $this->view_data['sub_title'] = $this->user_type.' Wallet';

        $this->base_url = $this->view_data['base_url'] = 'admin/wallet/'.$user_type;

        $this->helper = new Helpers;
        $this->notifications = $notifications;
    }

    /**
     * Load Datatable for Wallet
     *
     * @return view file
     */
     public function index(Request $request)
    {        
        $search = trim((string) $request->get('search'));
        $wallets = User::query()
            ->where('users.user_type', $this->user_type)
            ->join('wallet', 'users.id', '=', 'wallet.user_id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'wallet.currency_code', 'wallet.amount', 'users.gender')
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'wallet.currency_code', 'wallet.amount', 'users.gender');

        if ($search !== '') {
            $wallets->where(function ($query) use ($search) {
                $query->where('users.id', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('wallet.currency_code', 'like', "%{$search}%");
            });
        }

        $this->view_data['wallets'] = $wallets->orderByDesc('users.id')->paginate(20)->withQueryString();
        $this->view_data['search'] = $search;
        return view('admin.wallet.index',$this->view_data);
    }

    /**
     * Add a New wallet 
     *
     * @param array $request  Input values
     * @return redirect     to wallet view
     */
    public function add(Request $request)
    {
        $this->view_data['users_list']    = User::leftJoin('wallet', 'users.id', '=', 'wallet.user_id')
        ->whereNull('wallet.user_id')->where('user_type',$this->user_type)->whereStatus('Active')->pluck('first_name','id');

        if(!$_POST) {
            $this->view_data['currency']   = Currency::codeSelect();
            return view('admin.wallet.add',$this->view_data);
        }
        else if($request->submit) {

            $rules = [
                'user_id'       => 'required|unique:wallet,user_id',
                'amount'        => 'required|numeric|digits_between:1,4',
                'currency_code' => 'required',
            ];

            $niceNames = [
                'user_id'       => 'User Id',
                'amount'        => 'Amount',
                'currency_code' => 'Currency code',
            ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $wallet = new Wallet;
            $wallet->user_id        = $request->user_id;
            $wallet->amount         = $request->amount;
            $wallet->currency_code  = $request->currency_code;
            $wallet->save();

            if ($user = User::find($request->user_id)) {
                $this->notifications->sendEvent('other.fund_added_by_admin', $user, [], [], [
                    'walletAmount' => $request->currency_code.' '.number_format((float) $request->amount, 2),
                ]);
            }

            $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function
            return redirect($this->base_url);
        }
        return redirect($this->base_url);
    }

    /**
     * Update Wallet Details
     *
     * @param array $request    Input values
     * @return redirect     to wallet View
     */
    public function update(Request $request)
    {
        if(!$_POST) {
            $this->view_data['result'] = Wallet::where('user_id',$request->id)->first();
            $this->view_data['users_list'] = User::where('user_type',$this->user_type)->whereStatus('Active')->pluck('first_name','id');

            $this->view_data['currency']   = Currency::codeSelect();
            return view('admin.wallet.edit', $this->view_data);
        }
        else if($request->submit) {

            $referral_earning_exists = ReferralUser::where('user_id',$request->prev_user_id)->where('payment_status','Completed')->where('pending_amount','>',0)->exists();

            if ($referral_earning_exists) {
                $this->helper->flash_message('error', 'User have pending referral amount, So can\'t edit.'); // Call flash message function
                return back();
            }


            $rules = [
                'amount'        => 'required|numeric|digits_between:1,4',
                'currency_code' => 'required',
            ];

            $niceNames = [
                'amount'        => 'Amount',
                'currency_code' => 'Currency code',
            ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $wallet = [
                'user_id'        => $request->prev_user_id,
                'amount'         => $request->amount,
                'currency_code'  => $request->currency_code
            ];

            Wallet::where('user_id',$request->prev_user_id)->update($wallet);

            if ($user = User::find($request->prev_user_id)) {
                $this->notifications->sendEvent('other.fund_added_by_admin', $user, [], [], [
                    'walletAmount' => $request->currency_code.' '.number_format((float) $request->amount, 2),
                ]);
            }

            // Call flash message function
            $this->helper->flash_message('success', 'Update Successfully');
            return redirect($this->base_url);
        }
        return redirect($this->base_url);
    }

    public function delete(Request $request)
    {

        $referral_earning_exists = ReferralUser::where('user_id',$request->id)->where('payment_status','Completed')->where('pending_amount','>',0)->exists();


        if ($referral_earning_exists) {
            $this->helper->flash_message('error', 'User have pending referral amount, So can\'t delete.'); // Call flash message function
            return back();
        }

        $check_wallet = Wallet::where('user_id',$request->id)->first();
        if($check_wallet) {
            Wallet::where('user_id',$request->id)->delete();
            $this->helper->flash_message('success', 'Wallet Deleted Successfully'); // Call flash message function
            return redirect($this->base_url);
        }

        $this->helper->flash_message('error', 'Invalid Wallet ID'); // Call flash message function
        return redirect($this->base_url);
    }
}
