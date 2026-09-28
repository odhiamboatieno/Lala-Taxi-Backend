<?php

/**
 * Token Auth Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Token Auth
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProfilePicture;
use App\Models\DriverLocation;
use App\Models\DriverAddress;
use App\Models\CarType;
use App\Models\Currency;
use App\Models\Trips;
use App\Models\Company;
use App\Models\Language;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\Request as RideRequest;
use Validator;
use Session;
use App;
use JWTAuth;
use Auth;

use App\Models\DriverDocuments;
use App\Models\MakeVehicle;
use App\Models\VehicleModel;
use App\Models\Vehicle;

class TokenAuthController extends Controller
{
    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        $this->request_helper = resolve(\App\Http\Helper\RequestHelper::class);
    }

    /**
     * Get User Details
     * 
     * @param Collection User
     *
     * @return Response Array
     */
    protected function getUserDetails($user)
    {
        $invoice_helper = resolve(\App\Http\Helper\InvoiceHelper::class);
        $promo_details = $invoice_helper->getUserPromoDetails($user->id);

        $user_data = [
            'user_id'           => $user->id,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'name'              => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'email_id'          => $user->email ?? '',
            'user_type'         => $user->user_type,
            'mobile_number'     => $user->mobile_number,
            'country_code'      => $user->country_code,
            'user_status'       => $user->status,
            'currency_symbol'   => optional($user->currency)->symbol ?? '',
            'currency_code'     => optional($user->currency)->code ?? '',
            'payout_id'         => $user->payout_id ?? '',
            'company_name'      => optional($user->company)->name ?? $user->company_name ?? '',
        ];

        // Also sent for rider because mobile team also handle these parameters in rider

        // Both key sets are always returned because the rider and driver apps
        // read the same response shape. The relations are only eager loaded for
        // the matching user_type, which avoids two pointless queries per login.
        $rider_location = null;
        if (strcasecmp((string) $user->user_type, 'Rider') === 0) {
            $user->load('rider_location');
            $rider_location = $user->rider_location;
        }

        $rider_details = [
            //'home'          => optional($rider_location)->home ?? '',
            //'work'          => optional($rider_location)->work ?? '',
            //'home_latitude' => optional($rider_location)->home_latitude ?? '',
           // 'home_longitude'=> optional($rider_location)->home_longitude ?? '',
            //'work_latitude' => optional($rider_location)->work_latitude ?? '',
           // 'work_longitude'=> optional($rider_location)->work_longitude ?? '',
        ];

        $driver_documents = null;
        $driver_address = null;
        if (strcasecmp((string) $user->user_type, 'Driver') === 0) {
            $user->load(['driver_documents', 'driver_address']);
            $driver_documents = $user->driver_documents;
            $driver_address = $user->driver_address;
        }

        $driver_details = [
            'car_details'       => CarType::active()->get(),
            'vehicle_id'        => optional($driver_documents)->vehicle_id ?? '',
            'vehicle_number'    => optional($driver_documents)->vehicle_number ?? '',
            'vehicle_type'      => optional($driver_documents)->vehicle_type ?? '',
            'license_front'     => optional($driver_documents)->license_front ?? '',
            'license_back'      => optional($driver_documents)->license_back ?? '',
            'insurance'         => optional($driver_documents)->insurance ?? '',
            'rc'                => optional($driver_documents)->rc ?? '',
            'permit'            => optional($driver_documents)->permit ?? '',
            'address_line1'     => optional($driver_address)->address_line1 ?? '',
            'address_line2'     => optional($driver_address)->address_line2 ?? '',
            'state'             => optional($driver_address)->state ?? '',
            'postal_code'       => optional($driver_address)->postal_code ?? '',
            'company_id'        => $user->company_id ?? '',
            'company_name'      => optional($user->company)->name ?? $user->company_name ?? '',
        ];

        return array_merge($user_data,$rider_details,$driver_details);
    }
 
    /**
     * User Resister
     *@param  Get method request inputs
     *
     * @return Response Json 
     */
    public function register(Request $request) 
    {
        $language = $request->language ?? 'en';
        App::setLocale($language);

        try {
            $auth_method = "App\Services\Auth\AuthVia".ucfirst($request->auth_type);
            $auth_service = resolve($auth_method);
        } catch(\Exception $e) {
            $auth_service = resolve(\App\Services\Auth\AuthViaEmail::class);            
        }

        $validate = $auth_service->validate($request);

        if($validate) {
            return $validate;
        }

        $user = $auth_service->create($request);
        $request['country_id'] = $user->country_id;
        $credentials = $request->only('mobile_number','country_id', 'password','user_type');
        
        if($request->auth_type=='email') {
            $return_data = $auth_service->login($credentials);
        } else {
            $token = JWTAuth::fromUser($user);

            $return_data = [
                'status_code'       => '1',
                'status_message'    => __('messages.user.register_successfully'),
                'access_token'      => $token,
            ];
        }

        if(!isset($return_data['status_code'])) {
            return $return_data;
        }

        $user_data = $this->getUserDetails($user);

        if($user->user_type=='driver' && isLiveEnv()) {

            $driver_documents = UserDocuments('Driver',$user);

            if(count($driver_documents)) {
                foreach($driver_documents as $documents) {
                    $driver_document = new DriverDocuments;
                    $driver_document->user_id       = $user->id;
                    $driver_document->type          = 'Driver';
                    $driver_document->vehicle_id    = 0;
                    $driver_document->document_id   = $documents['id'];    
                    $filename = asset('/images/cabme.png');
                    $driver_document->document = $filename;
                    if($documents['expiry_required']) {
                        $driver_document->expired_date  = \Carbon\Carbon::now()->format('Y-m-d');
                    }
                    $driver_document->status = '1';
                    $driver_document->save();
                }
            }

                
            $vehicle =  new Vehicle;

            $make = MakeVehicle::active()->first();
            $model = VehicleModel::whereVehicleMakeId($make->id)->active()->first();
            
            $vehicle->vehicle_name = $make->make_vehicle_name.' '.$model->model_name;
            $vehicle->company_id   = 1;

            $car_types = CarType::active()->pluck('id','car_name')->toArray();
            $vehicle_types_id = implode(',', array_values($car_types));

            $type_name = '';
            foreach($car_types as $car_name=>$type_id) {
                // for vehicle type name
                if($type_name!='') {
                    $delimeter = ',';
                } else {
                    $delimeter = '';
                }
                $type_name .= $delimeter.$car_name;
            }

            $vehicle->vehicle_number   = '1111';
            $vehicle->vehicle_id       = $vehicle_types_id;
            $vehicle->vehicle_type     = $type_name;
            $vehicle->vehicle_make_id  = $make->id; 
            $vehicle->vehicle_model_id = $model->id; 
            $vehicle->user_id          = $user->id;
            $vehicle->year             = '2020';
            $vehicle->color            = 'Green';
            $vehicle->is_active        = '1';
            $vehicle->status           = "Active";
            $vehicle->default_type     = '1';
            $vehicle->save();

            $vehicle_documents = UserDocuments('Vehicle',$user,$vehicle->id);
            if(count($vehicle_documents)) {
                foreach($vehicle_documents as $documents) {
                    $vehicle_document = new DriverDocuments;
                    $vehicle_document->user_id       = $user->id;
                    $vehicle_document->type          = 'Vehicle';
                    $vehicle_document->vehicle_id    = $vehicle->id;
                    $vehicle_document->document_id   = $documents['id'];    
                    $filename = asset('/images/Cabme.png');
                    $vehicle_document->document = $filename;
                    if($documents['expiry_required']) {
                        $vehicle_document->expired_date  = \Carbon\Carbon::now()->format('Y-m-d');
                    }
                    $vehicle_document->status = '1';
                    $vehicle_document->save();
                }
            }

            $user->status = 'Active';
            $user->save();
        }

        return response()->json(array_merge($return_data,$user_data));
    }

    /**
     * User Socail media Resister & Login 
     * @param Get method request inputs
     *
     * @return Response Json 
     */
    public function apple_callback(Request $request) 
    {
        $client_id = api_credentials('service_id','Apple');

        $client_secret = getAppleClientSecret();

        $params = [
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => url('api/apple_callback'),
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ];
        
        $curl_result = curlPost("https://appleid.apple.com/auth/token",$params);

        if(!isset($curl_result['id_token'])) {
            $return_data = [
                'status_code'       => '0',
                'status_message'    => $curl_result['error'],
            ];

            return response()->json($return_data);
        }

        $claims = explode('.', $curl_result['id_token'])[1];
        $user_data = json_decode(base64_decode($claims));

        $user = User::where('apple_id', $user_data->sub)->first();

        if($user == '') {
            $return_data = [
                'status_code'       => '1',
                'status_message'    => 'New User',
                'email_id'          => optional($user_data)->email ?? '',
                'apple_id'          => $user_data->sub,
            ];

            return response()->json($return_data);
        }

        $token = JWTAuth::fromUser($user);

        $user_details = $this->getUserDetails($user);

        $return_data = [
            'status_code'       => '2',
            'status_message'    => 'Login Successfully',
            'apple_email'       => optional($user_data)->email ?? '',
            'apple_id'          => $user_data->sub,
            'access_token'      => $token,
        ];

        return response()->json(array_merge($return_data,$user_details));
    }

    /**
     * User Socail media Resister & Login 
     * @param Get method request inputs
     *
     * @return Response Json 
     */
    public function socialsignup(Request $request) {
        
        $rules = [
            'auth_type'   => 'required|in:facebook,google,apple',
            'auth_id'     => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        if($request->auth_type == 'facebook') {
            $auth_column = 'fb_id';
        } else if($request->auth_type == 'google') {
            $auth_column = 'google_id';
        } else {
            $auth_column = 'apple_id';
        }

        $user_count = User::where($auth_column,$request->auth_id)->count();

        // Social Login Flow
        if($user_count == 0) {
            return response()->json([
                'status_code'   => '2',
                'status_message'=> 'New User',
            ]);
        }

        $rules =  [
            'device_type'  =>'required',
            'device_id'    =>'required'
        ];

        $messages = ['required'=>':attribute is required.'];
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $user = User::where($auth_column,$request->auth_id)->first();
        $user->device_id    = $request->device_id;
        $user->device_type  = $request->device_type;
        $user->language     = $request->language;
        $user->currency_code= get_currency_from_ip();
        $user->firebase_token = null;
        $user->save();

        $token = JWTAuth::fromUser($user);

        $return_data = [
            'status_code'       => '1',
            'status_message'    => 'Login Success',
            'access_token'      => $token,
        ];

        $user_data = $this->getUserDetails($user);

        return response()->json(array_merge($return_data,$user_data));
    }

    /**
     * User Login
     * @param  Get method request inputs
     *
     * @return Response Json 
     */
    public function login(Request $request)
    {
        $user_id = $request->mobile_number;
        $auth_column = 'mobile_number';

        $rules = [
            'mobile_number'   =>'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'password'        =>'required',
            'country_code'    =>'required',
            'device_type'     =>'required',
            'device_id'       =>'required',
        ];

        $validator = Validator::make($request->all(), $rules); 

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $language = $request->language ?? 'en';
        App::setLocale($language);

        $country_id = Country::whereShortName($request->country_code)->value('id');
        $attempt = Auth::attempt([$auth_column=>$user_id, 'password'=>$request->password, 'user_type'=>$request->user_type,'country_id'=>$country_id]);

        if(!$attempt) {
            return response()->json([
                'status_code'    => '0',
                'status_message' => __('messages.credentials'),
            ]);
        }

        $credentials = $request->only($auth_column,'password','user_type');
        $credentials['country_id'] = $country_id;

        try {
            $token = JWTAuth::attempt($credentials);
            if(!$token) {
                return response()->json([
                    'status_code'    => '0',
                    'status_message' => __('messages.credentials'),
                ]);
            }

        } catch (JWTException $e) {
            return response()->json([
                'status_code'    => '0',
                'status_message' => 'could_not_create_token',
            ]);
        }

        $user = User::with('company')->where($auth_column, $user_id)->whereUserType($request->user_type)->whereCountryId($country_id)->first();

        if($user->status == 'Suspended') {
            return response()->json([
                'status_code'     => '0',
                'status_message' => 'Your account has been suspended, contact support.',
           ]);
        }

        if($user->status == 'Inactive') {
            return response()->json([
                'status_code'     => '0',
                'status_message' => __('messages.inactive_admin'),
           ]);
        }

        if(isset($user->company) && $user->company->status != 'Active') {
            return response()->json([
                'status_code'     => '0',
                'status_message' => __('messages.inactive_company'),
           ]);
        }

        $currency_code = get_currency_from_ip();
        User::whereId($user->id)->update([
            'device_id'     => $request->device_id,
            'device_type'   => $request->device_type,
            'currency_code' => $currency_code,
            'language'      => $request->language,
            'firebase_token'=> null
        ]);

        $user = User::where('id', $user->id)->first();
        auth()->setUser($user);

        if(strtolower($request->user_type) != 'rider') {
            $first_car = CarType::active()->first();

            $data['user_id'] = $user->id;
            $data['status'] = 'Offline';

            if(isset(optional($user->driver_documents)->vehicle_id) && optional($user->driver_documents)->vehicle_id) {
                $car_id = optional($user->driver_documents)->vehicle_id;
                $car_id = explode(',', $car_id);
                $car_id = $car_id[0];
            } else {
                $car_id = $first_car->id;
            }
            
            $data['car_id'] = $car_id;

            DriverLocation::updateOrCreate(['user_id' => $user->id], $data);
            RideRequest::where('driver_id',$user->id)->where('status','Pending')->update(['status'=>'Cancelled']);
        }

        $language = $user->language ?? 'en';
        App::setLocale($language);

        $return_data = [
            'status_code'       => '1',
            'status_message'    => __('messages.login_success'),
            'access_token'      => $token,
        ];

        $user = $this->getUserDetails($user);
    
        return response()->json(array_merge($return_data,$user));   
    }

    public function language(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $user= User::find($user_details->id);

        if($user == '') {
            return response()->json([
                'status_code'    => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }
        $user->language = $request->language;
        $user->save();

        $language = $user->language ?? 'en';

        App::setLocale($language);

        return response()->json([
            'status_code'       => '1',
            'status_message'    => trans('messages.update_success'),
        ]);
    }
    
     /**
     * User Email Validation
     *
     * @return Response in Json
     */
    public function emailvalidation(Request $request)
    {
        $rules = ['email'=> 'required|max:255|email_id|unique:users'];

        // Email signup validation custom messages
        $messages = ['required'=>':attribute is required.'];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()) {
            return response()->json([
                'status_code'   => '0',
                'status_message'=> 'Email Already exist',
            ]);
        }

        return response()->json([
            'status_code'   => '1',
            'status_message'=> 'Email validation Success',
        ]);
    }

    /**
     * Forgot Password
     * 
     * @return Response in Json
     */ 
    public function forgotpassword(Request $request)
    {
        $rules = [
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'password'        =>'required|min:6',
            'country_code'    =>'required',
            'device_type'     =>'required',
            'device_id'       =>'required'
        ];
        $attributes = [
            'mobile_number'   => 'Mobile Number',
        ];

        $validator = Validator::make($request->all(), $rules, $attributes);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $country_id = Country::whereShortName($request->country_code)->value('id');
        $user_check = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->whereCountryId($country_id)->first();
        
        if($user_check == '') {
            return response()->json([
                'status_code'    => '0',
                'status_message' => __('messages.invalid_credentials'),
            ]);
        }

        $currency_code = get_currency_from_ip();
        $user = User::whereId($user_check->id)->first();
        $user->password = $request->password;
        $user->device_id = $request->device_id;
        $user->device_type = $request->device_type;
        $user->currency_code = $currency_code;
        $user->firebase_token = null;
        $user->save();

        $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->first();

        $token = JWTAuth::fromUser($user);

        auth()->setUser($user);

        if(strtolower($request->user_type) != 'rider') {
            $first_car = CarType::active()->first();
            $data = [   
                'user_id'  => $user->id,
                'status'   => 'Offline',
                'car_id'   => @$user->driver_documents->vehicle_id ?$user->driver_documents->vehicle_id:$first_car->id,
            ];
            
            DriverLocation::updateOrCreate(['user_id' => $user->id], $data);
            RideRequest::where('driver_id',$user->id)->where('status','Pending')->update(['status'=>'Cancelled']);
        }

        $return_data = [
            'status_code'       => '1',
            'status_message'    => __('messages.login_success'),
            'access_token'      => $token,
        ];

        $user_data = $this->getUserDetails($user);
        
        return response()->json(array_merge($return_data,$user_data));
    }

    /**
     * Mobile number verification
     * 
     * @return Response in Json
     */ 
    public function numbervalidation(Request $request)
    {
        if(isset($request->language)) {
            $language = $request->language;
        }
        else {
            $language = 'en';
        }
        App::setLocale($language);

        $rules = [
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'country_code'    =>'required',
        ];

        if($request->forgotpassword==1) {
            $rules['mobile_number'] = 'required|regex:/^[0-9]+$/|min:6|exists:users,mobile_number';
        }

        $messages = [
            'mobile_number.required' => trans('messages.mobile_num_required'),
            'mobile_number.exists'   => trans('messages.enter_registered_number'),
        ];

        $validator = Validator::make($request->all(), $rules,$messages);
      
        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $mobile_number = $request->mobile_number;

        $country_id = Country::whereShortName($request->country_code)->value('id');

        // Match either stored spelling (with or without the trunk '0').
        $withoutZero = ltrim((string) $mobile_number, '0');
        $user = User::where(function ($query) use ($mobile_number, $withoutZero) {
                $query->where('mobile_number', $mobile_number);
                if ($withoutZero !== '' && $withoutZero !== $mobile_number) {
                    $query->orWhere('mobile_number', $withoutZero);
                }
                if (strlen($withoutZero) === 9) {
                    $query->orWhere('mobile_number', '0' . $withoutZero);
                }
            })
            ->where('user_type', $request->user_type)
            ->whereCountryId($country_id)
            ->get();
        if($user->count() && $request->forgotpassword != 1 && !$request->is_login) {
            return response()->json([
                'status_message'  => trans('messages.mobile_number_exist'),
                'status_code'     => '0',
            ]);
        }

        if($user->count() <= 0 && $request->forgotpassword == 1) {
            return response()->json([
                'status_message'  => trans('messages.number_does_not_exists'),
                'status_code'     => '0',
            ]);
        }

        $otp = new App\Http\Helper\OtpHelperMobile;
        $sms_responce = $otp->sendOtp($request->mobile_number,$request->country_code);

        if($sms_responce['status_code'] == 0) {
            return response()->json([
                'status_message' => $sms_responce['message'],
                'status_code' => '0',
                'otp' => '',
            ]);
        }

        return response()->json([
            'status_code'    => '1',
            'status_message' => 'Success',
            'otp'            => canDisplayCredentials() ? strval($sms_responce['signup_otp'] ?? '') : '',
        ]);
    }


    public function otp_verification()
    {
        // An auth endpoint must never surface an opaque 500 to the client. The
        // previous version could die inside checkOtp() and return a bare
        // {"message":"Server Error"} with no usable context in the app log.
        try {
            $otp = new App\Http\Helper\OtpHelperMobile;
            $response = $otp->checkOtp(request()->otp, request()->mobile_number, request()->country_code);

            $response['status_message'] = $response['message'] ?? '';
            $response['status_code'] = strval($response['status_code'] ?? 0);

            if ($response['status_code'] == '1') {
                // Account creation and password recovery need to validate the
                // code without attempting to create a session for an account
                // that does not exist yet. Login does not send this flag and
                // continues through the token-issuing path below.
                if (filter_var(request()->verification_only, FILTER_VALIDATE_BOOLEAN)) {
                    return $response;
                }

                $country_code = request()->country_code;
                $country_id = Country::whereShortName($country_code)->value('id');

                if (!$country_id) {
                    $country_id = Country::where('phone_code', ltrim($country_code, '+'))->value('id');
                }

                $user_type = request()->user_type ?? 'Rider';
                if (ucfirst($user_type) === 'Rider') {
                    $user_type = 'Rider';
                } elseif (ucfirst($user_type) === 'Driver') {
                    $user_type = 'Driver';
                }

                // Numbers are stored inconsistently (some with the trunk '0',
                // some without), so match either spelling.
                $rawNumber = (string) request()->mobile_number;
                $withoutZero = ltrim($rawNumber, '0');
                $user = User::where(function ($query) use ($rawNumber, $withoutZero) {
                        $query->where('mobile_number', $rawNumber);
                        if ($withoutZero !== '' && $withoutZero !== $rawNumber) {
                            $query->orWhere('mobile_number', $withoutZero);
                        }
                        if (strlen($withoutZero) === 9) {
                            $query->orWhere('mobile_number', '0' . $withoutZero);
                        }
                    })
                    ->where('user_type', $user_type)
                    ->whereCountryId($country_id)
                    ->first();

                // The OTP was valid but no account matches this number and role.
                // Returning status_code 1 here would hand the app a success with
                // no access_token, so fail explicitly instead.
                if (!$user) {
                    return [
                        'status_code'    => '0',
                        'message'        => 'No account found for this mobile number. Please register first.',
                        'status_message' => 'No account found for this mobile number. Please register first.',
                    ];
                }

                $token = JWTAuth::fromUser($user);

                User::whereId($user->id)->update([
                    'device_id'     => request()->device_id ?? '',
                    'device_type'   => request()->device_type ?? '',
                    'language'      => request()->language ?? 'en',
                ]);

                $user_data = $this->getUserDetails($user);

                $response['access_token'] = $token;
                $response = array_merge($response, $user_data);
            }

            return $response;
        } catch (\Throwable $e) {
            \Log::error('otp_verification failed: ' . $e->getMessage(), [
                'mobile_number' => request()->mobile_number,
                'country_code'  => request()->country_code,
                'user_type'     => request()->user_type,
                'exception'     => get_class($e),
            ]);

            return response()->json([
                'status_code'    => '0',
                'message'        => 'Unable to verify OTP right now. Please try again.',
                'status_message' => 'Unable to verify OTP right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Updat Device ID and Device Type
     * @param  Get method request inputs
     *
     * @return Response Json 
     */
    public function updateDevice(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = [
            'user_type'    =>'required|in:Rider,Driver,rider,driver',
            'device_type'  =>'required',
            'device_id'    =>'required'
        ];
        $attributes = [
            'mobile_number'   => 'Mobile Number',
        ];
        $validator = Validator::make($request->all(), $rules, $attributes);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $user = User::where('id', $user_details->id)->first();

        if($user == '') {
            return response()->json([
                'status_code'       => '0',
                'status_message'    => trans('messages.api.invalid_credentials'),
            ]);
        }

        User::whereId($user_details->id)->update(['device_id'=>$request->device_id,'device_type'=>$request->device_type]);                
        return response()->json([
            'status_code'     => '1',
            'status_message'  => __('messages.api.updated_successfully'),
        ]);
    }

    public function logout(Request $request)
    {
        $user_details = JWTAuth::parseToken()->authenticate();

        $user = User::where('id', $user_details->id)->first();

        if($user == '') {
            return response()->json([
                'status_code'       => '0',
                'status_message'    => __('messages.api.invalid_credentials'),
            ]);
        }

        if($user->user_type == 'Driver') {

            $trips_count = Trips::where('driver_id',$user_details->id)->whereNotIn('status',['Completed','Cancelled'])->count();

            $driver_location = DriverLocation::where('user_id',$user_details->id)->first();

            if(optional($driver_location)->status == 'Trip' || $trips_count > 0) {
                return response()->json([
                    'status_code'    => '0',
                    'status_message' => __('messages.complete_your_trips'),
                ]); 
            }

            DriverLocation::where('user_id',$user_details->id)->update(['status'=>'Offline']);
            JWTAuth::invalidate($request->token);
            Session::flush();

            $user->device_type = Null;
            $user->device_id = '';
            $user->save();
            
            return response()->json([
                'status_code'     => '1',
                'status_message'  => "Logout Successfully",
            ]); 
        }

        $trips_count = Trips::where('user_id',$user_details->id)->whereNotIn('status',['Completed','Cancelled'])->count();
        if($trips_count) {
            return response()->json([
              'status_code'    => '0',
              'status_message' => __('messages.complete_your_trips'),
            ]);
        }
        //Deactive the Access Token
        JWTAuth::invalidate($request->token);

        Session::flush();

        $user->device_type = Null;
        $user->device_id = '';
        $user->save();

        return response()->json([
            'status_code'     => '1',
            'status_message'  => "Logout Successfully",
        ]);
    }

    public function currency_conversion(Request $request)
    {
        $user_details   = JWTAuth::parseToken()->authenticate();
        set_payment_gateway_company_context($user_details->company_id ?? null);

        $payment_methods = collect(PAYMENT_METHODS);
        $payment_methods = $payment_methods->reject(function($value) {
            $is_enabled = payment_gateway('is_enabled',ucfirst($value['key']));
            return ($is_enabled != '1');
        });
        $payment_types = $payment_methods->pluck('key')->implode(',');

        $request['payment_type'] = strtolower($request->payment_type);

        $rules  = [
            'amount' => 'required|numeric|min:0',
            'payment_type'  => 'required|in:'.$payment_types,
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        $currency_code  = $user_details->currency->code;
        $payment_currency = site_settings('payment_currency');

        $price = floatval($request->amount);

        $converted_amount = currencyConvert($currency_code,$payment_currency,$price);
        $bt_clientToken   = '';
        if(strtolower($request->payment_type)=='paypal' || strtolower($request->payment_type)=='braintree' ){
            try {
                $service = 'App\Services\Payments\\'.ucfirst($request->payment_type)."Payment";
                $gateway = resolve($service);
            }catch(\Exception $e) {
                $gateway = resolve(\App\Services\Payments\PaypalPayment::class);
            }
            $customer = $gateway->CreateCustomerToken($user_details);
            if($customer->status_code == "1")
                $bt_clientToken   = $customer->bt_clientToken;
            else 
                return response()->json($customer);                
        }


        return response()->json([
            'status_code'    => '1',
            'status_message' => 'Amount converted successfully',
            'currency_code'  => $payment_currency,
            'amount'         => $converted_amount,
            'braintree_clientToken' => $bt_clientToken,
        ]);
    }

    public function getSessionOrDefaultCode()
    {
        $currency_code = Currency::defaultCurrency()->first()->code;
    }

    public function currency_list() 
    {
        $currency_list = Currency::active()->orderBy('code')->get();
        $curreny_list_keys = ['code', 'symbol'];

        $currency_list = $currency_list->map(function ($item, $key) use($curreny_list_keys) {
            return array_combine($curreny_list_keys, [$item->code, $item->symbol]);
        })->all();

        if(!empty($currency_list)) { 
            return response()->json([
                'status_message' => 'Currency Details Listed Successfully',
                'status_code'     => '1',
                'currency_list'   => $currency_list
            ]);
        }
        return response()->json([
            'status_code'     => '0',
            'status_message' => 'Currency Details Not Found',
        ]);
    }

    public function language_list() 
    {
        $languages = Language::active()->get();

        $languages = $languages->map(function ($item, $key)  {
            return $item->value;
        })->all();

        if(!empty($languages)) { 
            return response()->json([
                'status_code'   => '1',
                'status_message'=> 'Successfully',
                'language_list' => $languages,
            ]);
        }
        return response()->json([
            'status_code'     => '0',
            'status_message' => 'language Details Not Found',
        ]);
    }
    public function company_list()
    {
        $company_list = Company::active()->select('id', 'name')->get();

        if ($company_list->isEmpty()) {
            return response()->json([
                'status_code'    => '0',
                'status_message' => 'No companies found',
            ]);
        }

        $companies = $company_list->map(function ($company) {
            return [
                'company_id'   => $company->id,
                'company_name' => $company->name,
            ];
        })->all();

        return response()->json([
            'status_code'    => '1',
            'status_message' => 'Company list fetched successfully',
            'company_list'   => $companies,
        ]);
    }
}
