<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyDriverFeeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CompanyDriverFeeSettingsController extends Controller
{
    public function index(Request $request)
    {
        if (LOGIN_USER_TYPE != 'company' || Auth::guard('company')->guest()) {
            abort(403);
        }

        $company = Auth::guard('company')->user();
        $setting = CompanyDriverFeeSetting::firstOrNew(['company_id' => $company->id]);

        if ($request->isMethod('GET')) {
            return view('admin.company_driver_fees.index', compact('setting'));
        }

        $rules = [
            'fee_type' => 'required|in:percentage,weekly_subscription,monthly_subscription',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        $attributes = [
            'fee_type' => 'Fee type',
            'amount' => 'Amount',
            'status' => 'Status',
            'title' => 'Label',
            'notes' => 'Notes',
        ];

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $setting->company_id = $company->id;
        $setting->fee_type = $request->fee_type;
        $setting->amount = $request->amount;
        $setting->status = $request->status;
        $setting->title = $request->title;
        $setting->notes = $request->notes;
        $setting->save();

        flashMessage('success', 'Company driver fee settings updated successfully');

        return redirect(LOGIN_USER_TYPE.'/company-driver-fees');
    }
}
