<?php

/**
 * Driver Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Driver
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\DriverWithdrawalRequest;
use App\Models\Country;
use App\Models\CarType;
use App\Models\ProfilePicture;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\ReferralUser;
use App\Models\DriverOweAmount;
use App\Models\PayoutPreference;
use App\Models\PayoutCredentials;
use App\Models\Documents;
use App\Models\Rating;
use App\Services\Notifications\NotificationManager;
use Illuminate\Support\Collection;
use Modules\UserLevels\Services\UserLevelService;
use Illuminate\Support\Facades\Schema;
use Validator;
use DB;
use Auth;

class DriverController extends Controller
{
    /**
     * Load Datatable for Driver
     *
     * @param array $dataTable  Instance of Driver DataTable
     * @return datatable
     */
    public function index(Request $request)
    {
        $levelService = resolve(UserLevelService::class);
        $companyId = LOGIN_USER_TYPE == 'company' ? Auth::guard('company')->id() : null;
        $filters = [
            'search' => trim((string) $request->get('search')),
            'status_scope' => trim((string) $request->get('status_scope')),
            'level_id' => (int) $request->get('level_id'),
        ];

        $query = User::query()
            ->withTrashed()
            ->with(['profile_picture', 'company', 'vehicle'])
            ->where('user_type', 'Driver');

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($filters['status_scope'] === 'active') {
            $query->where('status', 'Active');
        }
        elseif ($filters['status_scope'] === 'inactive') {
            $query->where('status', 'Inactive');
        }

        if ($filters['level_id'] > 0 && Schema::hasTable('user_level_assignments')) {
            $levelId = $filters['level_id'];
            $query->whereHas('user_level_assignments', function ($builder) use ($levelId, $companyId) {
                $builder->where('level_type', 'driver')
                    ->where('user_level_id', $levelId);

                if ($companyId !== null) {
                    $builder->where('company_id', $companyId);
                }
                else {
                    $builder->whereNull('company_id');
                }
            });
        }

        $drivers = $query->latest('id')->paginate(12)->appends($request->query());
        $drivers->getCollection()->transform(function (User $driver) use ($levelService) {
            $levelCompanyId = ($driver->company_id && (int) $driver->company_id !== 1) ? (int) $driver->company_id : null;
            $metrics = $levelService->metricsForUser($driver, 'driver', $levelCompanyId);
            $assignment = $levelService->assignmentOrDefault($driver, 'driver', $levelCompanyId);
            $driver->profile_completion = $levelService->profileCompletion($driver, 'driver');
            $driver->level_badge = optional($assignment?->level)->name ?: 'Unassigned';
            $driver->completed_trips_count = (int) ($metrics['completed_trips'] ?? 0);
            $driver->total_earnings_amount = (float) ($metrics['earnings'] ?? 0);
            $driver->rating_average = (float) ($metrics['rating'] ?? 0);
            return $driver;
        });

        return view('admin.driver.view', [
            'summary' => $levelService->summaryForIndex('driver', $companyId),
            'level_cards' => $levelService->levelCards('driver', $companyId),
            'level_options' => $levelService->selectableLevels('driver', $companyId),
            'drivers' => $drivers,
            'filters' => $filters,
        ]);
    }

    public function get_documents(Request $request) {

        $country_code = $request->country_code;

        $vehicle_id = 0;
        if(isset($request->vehicle_id)) {
            $vehicle_id = $request->vehicle_id;
        }

        if($request->document_for=='Company') {
            $user = Company::find($request->user_id);
        } else {
            $user = User::find($request->user_id);
        }

        if(isset($user->country_code) && $country_code==$user->country_id) {
            $driver_doc = UserDocuments($request->document_for,$user,$vehicle_id);
            $driver_doc = json_decode($driver_doc);
            if($request->document_for=='Vehicle') {
                foreach($driver_doc as $key=>$doc) {
                    if(isset($doc->country_code)){
                        if($doc->country_code!='all' && $doc->country_code!=$user->country_id) {
                            unset($driver_doc[$key]);
                        }
                    }
                }
            }
        } else {
            $driver_doc = CheckDocument($request->document_for,$country_code);
        }

        return json_encode($driver_doc);  
    }

    public function get_documents_html(Request $request) {
        $country_code = $request->country_code;
        $document_for = $request->document_for ?? 'Driver';
        $user_id = $request->user_id;

        if ($document_for == 'Company') {
            $user = Company::find($user_id);
        } else {
            $user = User::find($user_id);
        }

        if (isset($user->country_code) && $country_code == $user->country_id) {
            $driver_doc = UserDocuments($document_for, $user, $request->vehicle_id ?? 0);
            $driver_doc = json_decode($driver_doc);
            if ($document_for == 'Vehicle') {
                foreach ($driver_doc as $key => $doc) {
                    if (isset($doc->country_code) && $doc->country_code != 'all' && $doc->country_code != $user->country_id) {
                        unset($driver_doc[$key]);
                    }
                }
            }
        } else {
            $driver_doc = CheckDocument($document_for, $country_code);
        }

        return view('admin.partials.driver_documents', ['documents' => $driver_doc])->render();
    }

    /**
     * Add a New Driver
     *
     * @param array $request  Input values
     * @return redirect     to Driver view
     */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            //Inactive Company could not add driver
            if (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status != 'Active') {
                abort(404);
            }
            $data['country_code_option'] = Country::pluck('long_name', 'id')->toArray();
            $data['company'] = Company::where('status','Active')->pluck('name','id');
            $driver_doc = Documents::Active()->where(['type' =>'Driver','country_code' =>'all'])->select('id','document_name','expire_on_date')->get();
            $data['driver_doc'] = json_encode($driver_doc);
            $levelService = resolve(UserLevelService::class);
            $companyId = LOGIN_USER_TYPE == 'company' ? Auth::guard('company')->id() : ((int) old('company_name') ?: null);
            $data['driver_level_options'] = $levelService->selectableLevels('driver', $companyId === 1 ? null : $companyId);
            return view('admin.driver.add',$data);
        }

        if($request->submit) {
            // Add Driver Validation Rules
            $rules = [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'mobile_number' => 'required|regex:/[0-9]{6}/',
                'password'      => 'required',
                'country_id'    => 'required',
                'gender'        => 'required',
                'user_type'     => 'required',
                'status'        => 'required',
            ];

            $driver_doc = CheckDocument('Driver',$request->country_id ?? 'all');

            if($driver_doc->count() > 0){
                foreach($driver_doc as $key => $value) {
                    $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif|max:10240';
                    $attributes['file_'.$value->id] = $value->document_name;
                    if($value->expire_on_date=='Yes') {
                        $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                        $attributes['expired_date_'.$value->id] = 'Expired Date';
                    }
                }
            }
            
            //Bank details are required only for company drivers & Not required for Admin drivers
            if ((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

            if (LOGIN_USER_TYPE!='company') {
                $rules['company_name'] = 'required';
            }

            // Add Driver Validation Custom Names
            $attributes['first_name']   = trans('messages.user.firstname');
            $attributes['last_name']    = trans('messages.user.lastname');
            $attributes['email']        = trans('messages.user.email');
            $attributes['password']     = trans('messages.user.paswrd');
            $attributes['country_id'] = trans('messages.user.country_code');
            $attributes['gender']       = trans('messages.profile.gender');
            $attributes['user_type']    = trans('messages.user.user_type');
            $attributes['status']       = trans('messages.driver_dashboard.status');
            $attributes['account_holder_name'] = 'Account Holder Name';
            $attributes['account_number'] = 'Account Number';
            $attributes['bank_name']    = 'Name of Bank';
            $attributes['bank_location']= 'Bank Location';
            $attributes['bank_code']    = 'BIC/SWIFT Code';

            // Edit Rider Validation Custom Fields message
            $messages = [
                'required'            => ':attribute is required.',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            ];

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);

            $validator->after(function ($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('country_id', $request->country_id)->count();

                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->count();

                if($user) {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                if($user_email) {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });

            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            if($request->status=="Active"){
                flashMessage('danger', 'Please ensure the driver has atleast one default vehicle, if not you can\'t activate.');
                return back()->withInput();
            }
            $get_country = Country::find($request->country_id);

            $user = new User;

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->country_code = $get_country->phone_code;
            $user->country_id   = $request->country_id;
            $user->gender       = $request->gender;
            $user->mobile_number= $request->mobile_number;
            $user->password     = $request->password;
            $user->status       = $request->status;
            $user->user_type    = $request->user_type;
            if(LOGIN_USER_TYPE=='company') {
                $user->company_id = Auth::guard('company')->user()->id;
            } else {
                $user->company_id = $request->company_name;
            }
            $user->save();
            $this->syncDriverLevel($user, $request);

            $user_pic = new ProfilePicture;
            $user_pic->user_id      = $user->id;
            $user_pic->src          = '';
            $user_pic->photo_source = 'Local';
            $user_pic->save();

            $user_address = new DriverAddress;
            $user_address->user_id       = $user->id;
            $user_address->address_line1 = $request->address_line1 ? $request->address_line1 :'';
            $user_address->address_line2 = $request->address_line2 ? $request->address_line2:'';
            $user_address->city          = $request->city ? $request->city:'';
            $user_address->state         = $request->state ? $request->state:'';
            $user_address->postal_code   = $request->postal_code ? $request->postal_code:'';
            $user_address->save();

            if($user->company_id != null && $user->company_id != 1) {
                $payout_preference = PayoutPreference::firstOrNew(['user_id' => $user->id,'payout_method' => "BankTransfer"]);
                $payout_preference->user_id = $user->id;
                $payout_preference->country = "IN";
                $payout_preference->account_number  = $request->account_number;
                $payout_preference->holder_name     = $request->account_holder_name;
                $payout_preference->holder_type     = "company";
                $payout_preference->paypal_email    = $request->account_number;

                $payout_preference->phone_number    = $request->mobile_number ?? '';
                $payout_preference->branch_code     = $request->bank_code ?? '';
                $payout_preference->bank_name       = $request->bank_name ?? '';
                $payout_preference->bank_location   = $request->bank_location ?? '';
                $payout_preference->payout_method   = "BankTransfer";
                $payout_preference->address_kanji   = json_encode([]);
                $payout_preference->save();

                $payout_credentials = PayoutCredentials::firstOrNew(['user_id' => $user->id,'type' => "BankTransfer"]);
                $payout_credentials->user_id = $user->id;
                $payout_credentials->preference_id = $payout_preference->id;
                $payout_credentials->payout_id = $request->account_number;
                $payout_credentials->type = "BankTransfer";
                $payout_credentials->default = 'yes';
                $payout_credentials->save();
            }

            $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/users/'.$user->id;
            $target_path = asset($target_dir).'/';

            if($driver_doc){                
                foreach ($driver_doc as $key => $value) {
                    $document_name = $value->doc_name;
                    $document = $request->file('file_'.$value->id);
                    $extension = $document->getClientOriginalExtension();
                    $file_name = $document_name."_".time().".".$extension;
                    $options = compact('target_dir','file_name');
                    $upload_result = $image_uploader->upload($document,$options);
                    if(!$upload_result['status']) {
                        flashMessage('danger', $upload_result['status_message']);
                        return back();
                    }
                    $user_doc = new DriverDocuments;
                    $user_doc->user_id = $user->id;
                    $user_doc->document_id = $value->id;
                    $user_doc->document = $target_path.$upload_result['file_name'];
                    $document_status = $value->doc_name."_status";
                    $user_doc->status = $request->$document_status;

                    $expired_date_key = 'expired_date_'.$value->id;
                    $user_doc->expired_date = $request->$expired_date_key;
                    $user_doc->save();
                }
            }
         
            flashMessage('success', trans('messages.user.add_success'));
            return redirect(LOGIN_USER_TYPE.'/driver');
        }

        return redirect(LOGIN_USER_TYPE.'/driver');
    }

    /**
     * Update Driver Details
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result']  = $user  = User::find($request->id);

            //If login user is company then company can edit only that company's driver details
            if($data['result']) {
                $data['address']            = DriverAddress::where('user_id',$request->id)->first();
                $data['country_code_option'] = Country::pluck('long_name', 'id')->toArray();
                $data['company']            = Company::where('status','Active')->pluck('name','id');
                $data['path']               = url('images/users/'.$request->id);
                $levelService = resolve(UserLevelService::class);
                $levelCompanyId = ($user->company_id && (int) $user->company_id !== 1) ? (int) $user->company_id : null;
                $data['driver_level_options'] = $levelService->selectableLevels('driver', $levelCompanyId);
                $data['driver_level_assignment'] = $levelService->assignmentOrDefault($user, 'driver', $levelCompanyId);
                $data['driver_level_metrics'] = $levelService->metricsForUser($user, 'driver', $levelCompanyId);
                $data['driver_profile_completion'] = $levelService->profileCompletion($user, 'driver');
                $data = array_merge($data, $this->driverProfilePayload($user, $levelService, $levelCompanyId));
                return view('admin.driver.edit', $data);
            } else {
                flashMessage('danger', 'Invalid ID');
                return redirect(LOGIN_USER_TYPE.'/driver'); 
            }
        }
        
        if($request->submit) {
            if ($request->submit === 'toggle_status') {
                $user = User::find($request->id);
                if (! $user) {
                    flashMessage('danger', 'Invalid ID');
                    return redirect(LOGIN_USER_TYPE.'/driver');
                }

                $user->status = $user->status === 'Active' ? 'Inactive' : 'Active';
                $user->save();

                flashMessage('success', 'Driver status updated successfully');
                return back();
            }

            if ($request->submit === 'suspend_driver') {
                $user = User::find($request->id);
                if (! $user) {
                    flashMessage('danger', 'Invalid ID');
                    return redirect(LOGIN_USER_TYPE.'/driver');
                }

                $user->status = 'Suspended';
                $user->save();

                flashMessage('success', 'Driver suspended successfully');
                return back();
            }

            // Edit Driver Validation Rules
            $rules = [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'status'        => 'required',
                'country_id'    => 'required',
                'gender'        => 'required',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
            ];

            //Bank details are updated only for company's drivers.
            if((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

            if(LOGIN_USER_TYPE!='company') {
                $rules['company_name'] = 'required';
            }

            // Edit Driver Validation Custom Fields Name
            $attributes = [
                'first_name'    => trans('messages.user.firstname'),
                'last_name'     => trans('messages.user.lastname'),
                'email'         => trans('messages.user.email'),
                'status'        => trans('messages.driver_dashboard.status'),
                'mobile_number' => trans('messages.profile.phone'),
                'country_id'   => trans('messages.user.country_code'),
                'gender'        => trans('messages.profile.gender'),
                'account_holder_name' => 'Account Holder Name',
                'account_number'=> 'Account Number',
                'bank_name'     => 'Name of Bank',
                'bank_location' => 'Bank Location',
                'bank_code'     => 'BIC/SWIFT Code',
            ];

            // Edit Rider Validation Custom Fields message
            $messages = [
                'required'            => ':attribute is required.',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            ];

            $user = User::find($request->id);
            if($user->country_id != $request->country_id) {
                $driver_doc = CheckDocument('Driver',$request->country_id ?? 'all');
                if($driver_doc->count() > 0){
                    foreach ($driver_doc as $key => $value) {
                        $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif|max:10240';
                        $attributes['file_'.$value->id] = $value->document_name;
                        if($value->expire_on_date=='Yes') {
                            $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                            $attributes['expired_date_'.$value->id] = 'Expired Date';
                        }
                    }
                }
            } else {
                $driver_document = UserDocuments('Driver',$user,0);
                $result = json_decode($driver_document, true);
                foreach ($result as $key => $value) {
                    if($value['document'] == ''){
                        $rules['file_'.$value['id']] = 'required|mimes:jpg,jpeg,png,gif|max:10240';
                        $attributes['file_'.$value['id']] = $value['document_name'];
                    }
                    if($value['expiry_required']==1) {
                        $rules['expired_date_'.$value['id']] = 'required|date|date_format:Y-m-d|after_or_equal:today';
                        $attributes['expired_date_'.$value['id']] = 'Expired Date';
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);
            if($request->mobile_number!="") {
                $validator->after(function ($validator) use($request) {
                    $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('country_id', $request->country_id)->where('id','!=', $request->id)->count();
                    if($user) {
                       $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                    }
                });
            }
           
            $validator->after(function ($validator) use($request) {
                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=', $request->id)->count();
                if($user_email) {
                    $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });

            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user = User::find($request->id);
            $previousStatus = $user->status;
            $driverDocsBeforeApproved = DriverDocuments::where('type', 'Driver')->where('vehicle_id', 0)->where('user_id', $user->id)->count() > 0
                && DriverDocuments::where('type', 'Driver')->where('vehicle_id', 0)->where('user_id', $user->id)->where('status', '!=', 1)->count() === 0;
            if($request->status =="Active" && !$user->vehicle){
                flashMessage('danger', 'Please ensure the driver has atleast one default vehicle, if not you can\'t activate.');
                return back();
            }
            $get_country = Country::find($request->country_id);

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->status       = $request->status;
            $user->country_code = $get_country->phone_code;
            $user->gender       = $request->gender;
            if($request->mobile_number!="") {
                $user->mobile_number = $request->mobile_number;
            }
            $user->user_type    = $request->user_type;
            if($request->password != '') {
                $user->password = $request->password;
            }
            if(LOGIN_USER_TYPE=='company') {
                $user->company_id = Auth::guard('company')->user()->id;
            } else {
                $user->company_id = $request->company_name;
            }
            $user->country_id = $request->country_id;
            $user->save();
            $this->syncDriverLevel($user, $request);

            if ($request->hasFile('profile_image')) {
                $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
                $targetDir = '/images/users/'.$user->id;
                $fileName = 'profile_'.time().'.'.$request->file('profile_image')->getClientOriginalExtension();
                $uploadResult = $imageUploader->upload($request->file('profile_image'), compact('targetDir', 'fileName'));

                if (! $uploadResult['status']) {
                    flashMessage('danger', $uploadResult['status_message']);
                    return back();
                }

                ProfilePicture::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'src' => asset($targetDir).'/'.$uploadResult['file_name'],
                        'photo_source' => 'Local',
                    ]
                );
            }

            Vehicle::where('user_id',$user->id)->update(['company_id'=>$user->company_id]);

            $user_address = DriverAddress::where('user_id',  $user->id)->first();
            if($user_address == '') {
                $user_address = new DriverAddress;
            }
            $user_address->user_id       = $user->id;
            $user_address->address_line1 = $request->address_line1;
            $user_address->address_line2 = $request->address_line2;
            $user_address->city          = $request->city;
            $user_address->state         = $request->state;
            $user_address->postal_code   = $request->postal_code;
            $user_address->save();

            if($user->company_id != null && $user->company_id != 1) {
                $payout_preference = PayoutPreference::firstOrNew(['user_id' => $user->id,'payout_method' => "BankTransfer"]);
                $payout_preference->user_id = $user->id;
                $payout_preference->country = "IN";
                $payout_preference->account_number  = $request->account_number;
                $payout_preference->holder_name     = $request->account_holder_name;
                $payout_preference->holder_type     = "company";
                $payout_preference->paypal_email    = $request->account_number;
                $payout_preference->phone_number    = $request->mobile_number ?? '';
                $payout_preference->branch_code     = $request->bank_code ?? '';
                $payout_preference->bank_name       = $request->bank_name ?? '';
                $payout_preference->bank_location   = $request->bank_location ?? '';
                $payout_preference->payout_method   = "BankTransfer";
                $payout_preference->address_kanji   = json_encode([]);
                $payout_preference->save();

                $payout_credentials = PayoutCredentials::firstOrNew(['user_id' => $user->id,'type' => "BankTransfer"]);
                $payout_credentials->user_id = $user->id;
                $payout_credentials->preference_id = $payout_preference->id;
                $payout_credentials->payout_id = $request->account_number;
                $payout_credentials->type = "BankTransfer";                
                $payout_credentials->default = 'yes';
                $payout_credentials->save();
            }

            $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/users/'.$user->id;
            $target_path = asset($target_dir).'/';

            $driver_doc = CheckDocument('Driver',$request->country_id);
            if($driver_doc){
                foreach ($driver_doc as $key => $value) {
                    if($request->hasFile('file_'.$value->id)){
                        $document_name = $value->doc_name;
                        $document = $request->file('file_'.$value->id);
                        $extension = $document->getClientOriginalExtension();
                        $file_name = $document_name."_".time().".".$extension;
                        $options = compact('target_dir','file_name');
                        $upload_result = $image_uploader->upload($document,$options);
                        
                        if(!$upload_result['status']) {
                            flashMessage('danger', $upload_result['status_message']);
                            return back();
                        }

                        $user_doc = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->where('document_id',$value->id)->first();

                        if($user_doc == ''){
                            $user_doc = new DriverDocuments;
                        }
                        $user_doc->user_id = $user->id;
                        $user_doc->document_id = $value->id;
                        $user_doc->document = $target_path.$upload_result['file_name'];
                        $document_status = $value->doc_name."_status";
                        $user_doc->status = $request->$document_status; 
                        $user_doc->save();
                    }
                }

                $oldDocuments = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->whereNotIn('document_id',$driver_doc->pluck('id')->toArray())->pluck('id');
                /*Delete document from table and folder*/
                if(count($oldDocuments) > 0){
                    foreach ($oldDocuments as $key => $value) {
                        $driver = DriverDocuments::find($value);
                        $driver_doc = resolve(\App\Contracts\ImageHandlerInterface::class);
                        $driver_doc->delete($driver->document,['file_path' => '/images/users/'.$user->id.'/']);
                        $driver->delete();
                    }
                }
                /*End Here*/

                foreach ($driver_doc as $key => $value) {
                    $document_status = $value->doc_name."_status";
                    $user_doc = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->where('document_id',$value->id)->first();
                    $user_doc->status = $request->$document_status; 
                    $expired_date_key = 'expired_date_'.$value->id;
                    $user_doc->expired_date = $request->$expired_date_key;
                    $user_doc->save();
                }
            }

            $driverDocsAfterApproved = DriverDocuments::where('type', 'Driver')->where('vehicle_id', 0)->where('user_id', $user->id)->count() > 0
                && DriverDocuments::where('type', 'Driver')->where('vehicle_id', 0)->where('user_id', $user->id)->where('status', '!=', 1)->count() === 0;

            $notificationManager = resolve(NotificationManager::class);
            if ($previousStatus !== 'Active' && $user->status === 'Active') {
                $notificationManager->sendEvent('driver_registration.registration_approved', $user, [
                    'company' => $user->company,
                ]);
            }

            if (! $driverDocsBeforeApproved && $driverDocsAfterApproved) {
                $notificationManager->sendEvent('driver_registration.identity_image_approved', $user, [
                    'company' => $user->company,
                ]);
            } elseif ($driverDocsBeforeApproved && ! $driverDocsAfterApproved) {
                $notificationManager->sendEvent('driver_registration.identity_image_rejected', $user, [
                    'company' => $user->company,
                ]);
            }

            flashMessage('success', 'Updated Successfully');
        }
        return redirect(LOGIN_USER_TYPE.'/driver');
    }

    /**
     * Delete Driver
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function delete(Request $request)
    {
        $result= $this->canDestroy($request->id);

        if($result['status'] == 0) {
            flashMessage('error',$result['message']);
            return back();
        }
        $driver_owe_amount = DriverOweAmount::where('user_id',$request->id)->first();
        if($driver_owe_amount->amount == 0) {
            $driver_owe_amount->delete();
        }
        try {
            User::find($request->id)->delete();
        }
        catch(\Exception $e) {
            $driver_owe_amount = DriverOweAmount::where('user_id',$request->id)->first();
            if($driver_owe_amount == '') {
                DriverOweAmount::create([
                    'user_id' => $request->id,
                    'amount' => 0,
                    'currency_code' => 'USD',
                ]);
            }
            flashMessage('error','Driver have some trips, So can\'t delete this driver');
            // flashMessage('error',$e->getMessage());
            return back();
        }

        flashMessage('success', 'Deleted Successfully');
        return redirect(LOGIN_USER_TYPE.'/driver');
    }

    // Check Given User deletable or not
    public function canDestroy($user_id)
    {
        $return  = ['status' => '1', 'message' => ''];

        //Company can delete only this company's drivers.
        if(LOGIN_USER_TYPE=='company') {
            $user = User::find($user_id);
            if ($user->company_id != Auth::guard('company')->user()->id) {
                $return = ['status' => 0, 'message' => 'Invalid ID'];
                return $return;
            }
        }

        $driver_trips   = Trips::where('driver_id',$user_id)->count();
        $user_referral  = ReferralUser::where('user_id',$user_id)->orWhere('referral_id',$user_id)->count();

        if($driver_trips) {
            $return = ['status' => 0, 'message' => 'Driver have some trips, So can\'t delete this driver'];
        }
        else if($user_referral) {
            $return = ['status' => 0, 'message' => 'Rider have referrals, So can\'t delete this driver'];
        }
        return $return;
    }

    protected function syncDriverLevel(User $user, Request $request): void
    {
        $levelService = resolve(UserLevelService::class);
        $companyId = ($user->company_id && (int) $user->company_id !== 1) ? (int) $user->company_id : null;
        $levelService->assignLevel($user, 'driver', $companyId, $request->integer('driver_level_id') ?: null);
    }

    protected function driverProfilePayload(User $driver, UserLevelService $levelService, ?int $companyId): array
    {
        $tripBase = Trips::query()->where('driver_id', $driver->id);
        $completedTrips = (clone $tripBase)->where('status', 'Completed');
        $allTrips = (clone $tripBase)->with(['users.profile_picture', 'currency', 'trip_rating', 'payment', 'car_type'])->latest('id')->take(8)->get();
        $reviews = Rating::query()->with('rider.profile_picture')->where('driver_id', $driver->id)->where('rider_rating', '>', 0)->latest('id')->take(8)->get();

        $documents = collect(json_decode(UserDocuments('Driver', $driver, 0), true) ?: []);
        $documentGroups = [
            'identity' => $documents->filter(fn ($doc) => str_contains(strtolower($doc['document_name'] ?? ''), 'license') || str_contains(strtolower($doc['document_name'] ?? ''), 'identity')),
            'other' => $documents->filter(fn ($doc) => str_contains(strtolower($doc['document_name'] ?? ''), 'permit') || str_contains(strtolower($doc['document_name'] ?? ''), 'insurance') || str_contains(strtolower($doc['document_name'] ?? ''), 'rc')),
        ];
        $documentGroups['additional'] = $documents->reject(function ($doc) use ($documentGroups) {
            return $documentGroups['identity']->contains(fn ($item) => $item['id'] == $doc['id'])
                || $documentGroups['other']->contains(fn ($item) => $item['id'] == $doc['id']);
        });

        $walletStats = $this->driverWalletStats($driver);
        $metrics = $levelService->metricsForUser($driver, 'driver', $companyId);
        $completedCount = (int) ($metrics['completed_trips'] ?? 0);
        $cancelledCount = (int) ($metrics['cancelled_trips'] ?? 0);
        $totalTrips = max((int) ($metrics['total_trips'] ?? 0), 1);
        $reviewCount = $reviews->count();
        $positiveReviews = $reviews->where('rider_rating', '>=', 4)->count();
        $driverLevel = $levelService->assignmentOrDefault($driver, 'driver', $companyId);
        $todayTripsCount = (clone $tripBase)->whereDate('created_at', now()->toDateString())->count();
        $todayIdleRate = max(0, 100 - min(100, $todayTripsCount * 10));
        $activeSeconds = (int) (clone $completedTrips)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->sum(function ($trip) {
                return max(0, optional($trip->completed_at)->timestamp - optional($trip->started_at)->timestamp);
            });
        $levelPoints = (float) optional($driverLevel)->points;
        $levelTargetPoints = $this->levelTargetPoints(optional($driverLevel)->level?->target_config);

        return [
            'driver_level_assignment' => $driverLevel,
            'driver_documents_grouped' => $documentGroups,
            'driver_recent_trips' => $allTrips,
            'driver_recent_reviews' => $reviews,
            'driver_recent_transactions' => $allTrips->where('status', 'Completed'),
            'driver_vehicle_cards' => $driver->vehicles()->with('car_type')->get(),
            'driver_wallet_stats' => $walletStats,
            'driver_trip_stats' => [
                'completed' => $completedCount,
                'cancelled' => $cancelledCount,
                'lowest_fare' => (float) ((clone $completedTrips)->min('total_fare') ?: 0),
                'highest_fare' => (float) ((clone $completedTrips)->max('total_fare') ?: 0),
                'reviews_given' => $reviewCount,
                'active_hours' => $this->formatDurationHours($activeSeconds),
                'level_points' => round($levelPoints, 2),
                'level_target_points' => $levelTargetPoints,
                'wallet_money' => round((float) optional($driver->wallet)->original_amount, 2),
            ],
            'driver_rate_info' => [
                'average_earning_per_day' => $completedCount > 0 ? round(($walletStats['total_earning'] ?? 0) / max(1, (clone $completedTrips)->selectRaw('COUNT(DISTINCT DATE(created_at)) as d')->value('d') ?: 1), 2) : 0,
                'positive_review_rate' => $reviewCount > 0 ? round(($positiveReviews / $reviewCount) * 100, 2) : 0,
                'success_rate' => round(($completedCount / $totalTrips) * 100, 2),
                'cancellation_rate' => round(($cancelledCount / $totalTrips) * 100, 2),
                'today_idle_rate' => round($todayIdleRate, 2),
                'rating' => (float) ($metrics['rating'] ?? 0),
                'rating_count' => $reviewCount,
            ],
        ];
    }

    protected function driverWalletStats(User $driver): array
    {
        $pendingWithdrawal = (float) DriverWithdrawalRequest::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['pending', 'processing', 'approved'])
            ->sum('amount');

        $alreadyWithdrawn = (float) DriverWithdrawalRequest::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('amount');

        $collectableCash = (float) Trips::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'Completed')
            ->whereIn('payment_mode', ['Cash', 'Cash & Wallet'])
            ->sum(DB::raw('COALESCE(total_fare,0) - COALESCE(wallet_amount,0) - COALESCE(promo_amount,0)'));

        $withdrawableAmount = (float) Trips::query()
            ->join('payment', 'payment.trip_id', '=', 'trips.id')
            ->where('trips.driver_id', $driver->id)
            ->where('trips.status', 'Completed')
            ->where('payment.driver_payout_status', 'Pending')
            ->sum('trips.driver_payout');

        $earningColumn = Schema::hasColumn('trips', 'driver_payout')
            ? 'driver_payout'
            : (Schema::hasColumn('trips', 'driver_earnings') ? 'driver_earnings' : null);

        $totalEarning = $earningColumn
            ? (float) Trips::query()
                ->where('driver_id', $driver->id)
                ->where('status', 'Completed')
                ->sum($earningColumn)
            : 0;

        return [
            'collectable_cash' => round($collectableCash, 2),
            'pending_withdraw' => round($pendingWithdrawal, 2),
            'already_withdrawn' => round($alreadyWithdrawn, 2),
            'withdrawable_amount' => round($withdrawableAmount, 2),
            'total_earning' => round($totalEarning, 2),
        ];
    }

    protected function levelTargetPoints($targetConfig): float
    {
        if (! is_array($targetConfig)) {
            return 0;
        }

        $points = 0;
        array_walk_recursive($targetConfig, function ($value, $key) use (&$points) {
            if (strtolower((string) $key) === 'points' && is_numeric($value)) {
                $points += (float) $value;
            }
        });

        return $points;
    }

    protected function formatDurationHours(int $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        return $hours.':'.$minutes.'h';
    }


}
