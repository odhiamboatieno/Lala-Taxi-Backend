<?php

/**
 * Referrals Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Referrals
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ReferralUser;

class ReferralsController extends Controller
{
    public function __construct()
    {
        $this->base_url = $this->view_data['base_url'] = 'admin/referrals';
        $this->view_data['main_title'] = 'Referral';
        $this->view_data['user_type'] = $this->user_type = ucfirst(request()->segment(3));
        $this->view_data['sub_title'] = $this->user_type.' '. $this->view_data['main_title'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $referrals = ReferralUser::query()
            ->with(['user', 'referral_user'])
            ->where('user_type', $this->user_type)
            ->where('payment_status', '!=', 'Expired')
            ->select('user_id')
            ->groupBy('user_id');

        if ($search !== '') {
            $referrals->where(function ($query) use ($search) {
                $query->where('user_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('referral_user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $this->view_data['search'] = $search;
        $this->view_data['referrals'] = $referrals->paginate(20)->withQueryString();
        return view('admin.referrals.view',$this->view_data);
    }

    /**
     * Display a referral detail
     *
     * @return \Illuminate\Http\Response
     */
    public function referral_details(Request $request)
    {
        $referral_details = ReferralUser::with('user','referral_user')->where('user_id',$request->id)->get();
        if($referral_details->count() == 0) {
            flashMessage('error','Invalid ID');
            return back();
        }
        $user_type = strtolower($referral_details[0]->user_type);
        return view('admin.referrals.details',compact('referral_details','user_type'));
    }
}
