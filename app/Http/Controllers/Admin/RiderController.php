<?php

/**
 * Rider Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Rider
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trips;
use App\Models\Wallet;
use App\Models\UsersPromoCode;
use App\Models\Country;
use App\Models\ProfilePicture;
use App\Models\PaymentMethod;
use App\Models\RiderLocation;
use App\Models\ApiCredentials;
use App\Models\ReferralUser;
use App\Models\Rating;
use App\Http\Start\Helpers;
use App\Services\Notifications\NotificationManager;
use Illuminate\Support\Facades\Schema;
use Modules\UserLevels\Services\UserLevelService;
use Validator;
use DB;

class RiderController extends Controller
{
    protected function isCompanyPanel(): bool
    {
        return defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company';
    }

    protected function levelCompanyId(): ?int
    {
        return $this->isCompanyPanel() ? (int) auth('company')->id() : null;
    }

    protected function riderIndexUrl(): string
    {
        return (defined('LOGIN_USER_TYPE') ? LOGIN_USER_TYPE : 'admin').'/rider';
    }

    protected function scopedRiderQuery()
    {
        $query = User::query()->where('user_type', 'Rider');

        if ($this->isCompanyPanel()) {
            $query->where('company_id', auth('company')->id());
        }

        return $query;
    }

    protected function findScopedRider($id): ?User
    {
        return $this->scopedRiderQuery()->find($id);
    }

    /**
     * Load Datatable for Rider
     *
     * @param array $dataTable  Instance of RiderDataTable
     * @return datatable
     */
    public function index(Request $request)
    {
        $levelService = resolve(UserLevelService::class);
        $filters = [
            'search' => trim((string) $request->get('search')),
            'status_scope' => trim((string) $request->get('status_scope')),
            'level_id' => (int) $request->get('level_id'),
        ];

        $query = User::query()
            ->with(['profile_picture', 'rider_location', 'wallet'])
            ->where('user_type', 'Rider');

        if ($this->isCompanyPanel()) {
            $query->where('company_id', auth('company')->id());
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
            $companyId = $this->levelCompanyId();
            $query->whereHas('user_level_assignments', function ($builder) use ($levelId, $companyId) {
                $builder->where('level_type', 'rider')
                    ->where('company_id', $companyId)
                    ->where('user_level_id', $levelId);
            });
        }

        $riders = $query->latest('id')->paginate(12)->appends($request->query());
        $riders->getCollection()->transform(function (User $rider) use ($levelService) {
            $metrics = $levelService->metricsForUser($rider, 'rider');
            $assignment = $levelService->assignmentOrDefault($rider, 'rider', $this->levelCompanyId());
            $rider->profile_completion = $levelService->profileCompletion($rider, 'rider');
            $rider->level_badge = optional($assignment?->level)->name ?: 'Unassigned';
            $rider->completed_trips_count = (int) ($metrics['completed_trips'] ?? 0);
            $rider->total_spend_amount = (float) ($metrics['spend'] ?? 0);
            $rider->success_rate = (float) ($metrics['success_rate'] ?? 0);
            return $rider;
        });

        return view('admin.rider.view', [
            'summary' => $levelService->summaryForIndex('rider', $this->levelCompanyId()),
            'level_cards' => $levelService->levelCards('rider', $this->levelCompanyId()),
            'level_options' => $levelService->selectableLevels('rider', $this->levelCompanyId()),
            'riders' => $riders,
            'filters' => $filters,
        ]);
    }

    /**
     * Add a New Rider
     *
     * @param array $request  Input values
     * @return redirect     to Rider view
     */
    public function add(Request $request)
    {
        if($request->isMethod('GET')) {
            $data['country_code_option'] = Country::select('long_name','phone_code','id')->get();
            $data['google_api'] = ApiCredentials::where('id','');
            $data['rider_level_options'] = resolve(UserLevelService::class)->selectableLevels('rider', $this->levelCompanyId());
            return view('admin.rider.add',$data);
        }
        if($request->submit) {
            $rules = [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
                'password'      => 'required',
                'country_code'  => 'required',
                'gender'        => 'required',
                'user_type'     => 'required',
                'status'        => 'required',
            ];

            // Add Rider Validation Custom Names
            $attributes = [
                'first_name' => trans('messages.user.firstname'),
                'last_name' => trans('messages.user.lastname'),
                'email' => trans('messages.user.email'),
                'password' => trans('messages.user.paswrd'),
                'country_code' => trans('messages.user.country_code'),
                'gender' => trans('messages.profile.gender'),
                'user_type' => trans('messages.user.user_type'),
                'mobile_number' => trans('messages.user.mobile'),
                'status' => trans('messages.driver_dashboard.status'),
            ];

            // Edit Rider Validation Custom Fields message
            $messages = [
                'required' => ':attribute '.trans('messages.home.field_is_required').'',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            ];

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);
            $validator->after(function($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('country_id', $request->country_id)->count();
                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->count();

                if($user) {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                if($user_email) {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user = new User;
            $previousStatus = $user->status;
            $previousFaceVerification = $user->facial_verification_status ?? null;

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->country_code = $request->country_code;
            $user->country_id   = $request->country_id;
            $user->mobile_number= $request->mobile_number;
            $user->gender       = $request->gender;
            $user->password     = $request->password;
            $user->user_type    = $request->user_type;
            $user->status       = $request->status;
            if ($this->isCompanyPanel() && Schema::hasColumn('users', 'company_id')) {
                $user->company_id = auth('company')->id();
            }
            $user->save();
            $this->syncRiderLevel($user, $request);

            $user_pic = new ProfilePicture;
            $user_pic->user_id      = $user->id;
            $user_pic->src          = "";
            $user_pic->photo_source = 'Local';
            $user_pic->save();

            $location = new RiderLocation;
            $location->user_id          = $user->id;
            $location->home             = $request->home_location?$request->home_location:'';
            $location->work             = $request->work_location ? $request->work_location :'';
            $location->home_latitude    = $request->home_latitude ? $request->home_latitude :'';
            $location->home_longitude   = $request->home_longitude ? $request->home_longitude : '';
            $location->work_latitude    = $request->work_latitude ? $request->work_latitude :'';
            $location->work_longitude   = $request->work_longitude ? $request->work_longitude : '';
            $location->save();
           
            flashMessage('success', 'Added Successfully');
        }
        return redirect($this->riderIndexUrl());
    }

    /**
     * Update Rider Details
     *
     * @param array $request    Input values
     * @return redirect     to Rider View
     */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result'] = $this->findScopedRider($request->id);
            if($data['result']) {
                $data['country_code_option']=Country::select('long_name','phone_code','id')->get();
                $data['location']=RiderLocation::where('user_id', $request->id)->first();
                $levelService = resolve(UserLevelService::class);
                $data['rider_level_options'] = $levelService->selectableLevels('rider', $this->levelCompanyId());
                $data['rider_level_assignment'] = $levelService->assignmentOrDefault($data['result'], 'rider', $this->levelCompanyId());
                $data['rider_level_metrics'] = $levelService->metricsForUser($data['result'], 'rider', $this->levelCompanyId());
                $data['rider_profile_completion'] = $levelService->profileCompletion($data['result'], 'rider');
                $data['customer_documents'] = collect();
                $data = array_merge($data, $this->riderProfilePayload($data['result'], $levelService));
                return view('admin.rider.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
            return redirect($this->riderIndexUrl());
        }
        if($request->submit) {
            $rules = [
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
                'country_code'  => 'required',
                'gender'        => 'required',
                'status'        => 'required',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
            ];
            // Edit Rider Validation Custom Fields message
            $messages = [
                'required' => ':attribute '.trans('messages.home.field_is_required').'',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            ];
            // Edit Rider Validation Custom Fields Name
            $attributes = [
                'first_name' => trans('messages.user.firstname'),
                'last_name' => trans('messages.user.lastname'),
                'email' => trans('messages.user.email'),
                'password' => trans('messages.user.paswrd'),
                'country_code' => trans('messages.user.country_code'),
                'gender' => trans('messages.profile.gender'),
                'mobile_number' => trans('messages.user.mobile'),
                'status' => trans('messages.driver_dashboard.status'),
            ];

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);
            
            if($request->mobile_number != "") {
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

            $user = $this->findScopedRider($request->id);
            if (! $user) {
                flashMessage('danger', 'Invalid ID');
                return redirect($this->riderIndexUrl());
            }

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->country_code = $request->country_code;
            $user->gender       = $request->gender;
            if($request->mobile_number != "") {
                $user->mobile_number= $request->mobile_number;
            }
            $user->user_type    = $request->user_type;
            if($request->password != '') {
                $user->password = $request->password;
            }
            $user->country_id = $request->country_id;
            $user->status       = $request->status;
            if (Schema::hasColumn('users', 'kyc_status')) {
                $user->kyc_status = $request->get('kyc_status', $user->kyc_status);
            }
            if (Schema::hasColumn('users', 'facial_verification_status')) {
                $user->facial_verification_status = $request->get('facial_verification_status', $user->facial_verification_status);
            }
            if (Schema::hasColumn('users', 'verification_status')) {
                $user->verification_status = $request->get('verification_status', $user->verification_status);
            }
            $user->save();
            $this->syncRiderLevel($user, $request);

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

            $location = RiderLocation::where('user_id',$request->id)->first();
            if($location == '') {
                $location   = new RiderLocation;
            }
            $location->user_id          =   $request->id;
            $location->home             =   $request->home_location?$request->home_location:'';
            $location->work             =   $request->work_location ? $request->work_location :'';
            $location->home_latitude    =   $request->home_latitude ? $request->home_latitude :'';
            $location->home_longitude   =   $request->home_longitude ? $request->home_longitude : '';
            $location->work_latitude    =   $request->work_latitude ? $request->work_latitude :'';
            $location->work_longitude   =   $request->work_longitude ? $request->work_longitude : '';
            $location->save();

            $notificationManager = resolve(NotificationManager::class);
            if ($previousStatus !== 'Active' && $user->status === 'Active') {
                $notificationManager->sendEvent('rider_registration.account_approved', $user, [
                    'company' => $user->company,
                ]);
            }

            if (($previousFaceVerification !== ($user->facial_verification_status ?? null))
                && in_array(strtolower((string) ($user->facial_verification_status ?? '')), ['approved', 'verified', 'completed', 'success'])) {
                $notificationManager->sendEvent('rider_registration.face_verification_completed', $user, [
                    'company' => $user->company,
                ]);
            }

            flashMessage('success', trans('messages.user.update_success'));
        }

        return redirect($this->riderIndexUrl());
    }

    /**
     * Delete Rider
     *
     * @param array $request    Input values
     * @return redirect     to Rider View
     */
    public function delete(Request $request)
    {
        $rider = $this->findScopedRider($request->id);
        if (! $rider) {
            flashMessage('error', 'Invalid rider');
            return back();
        }

        $result= $this->canDestroy($rider->id);

        if($result['status'] == 0) {
            flashMessage('error',$result['message']);
            return back();
        }
        try {
            PaymentMethod::where('user_id',$rider->id)->delete();
            $rider->delete();
        }
        catch(\Exception $e) {
            flashMessage('error','Rider have wallet or promo or trips, So can\'t delete this rider.');
            return back();
        }

        flashMessage('success', 'Deleted Successfully');
        return redirect($this->riderIndexUrl());
    }

    // Check Given User deletable or not
    public function canDestroy($user_id)
    {
        $return  = ['status' => '1', 'message' => ''];

        $user_promo = UsersPromoCode::where('user_id',$user_id)->count();
        $user_wallet = Wallet::where('user_id',$user_id)->count();
        $user_trips = Trips::where('user_id',$user_id)->count();
        $user_referral = ReferralUser::where('user_id',$user_id)->orWhere('referral_id',$user_id)->count();

        if($user_promo || $user_wallet || $user_trips) {
            $return = ['status' => 0, 'message' => 'Rider have wallet or promo or trips, So can\'t delete this rider'];
        }
        else if($user_referral) {
            $return = ['status' => 0, 'message' => 'Rider have referrals, So can\'t delete this rider'];
        }
        return $return;
    }

    protected function syncRiderLevel(User $user, Request $request): void
    {
        resolve(UserLevelService::class)->assignLevel($user, 'rider', $this->levelCompanyId(), $request->integer('rider_level_id') ?: null);
    }

    protected function riderProfilePayload(User $rider, UserLevelService $levelService): array
    {
        $tripBase = Trips::query()->where('user_id', $rider->id);
        $completedTrips = (clone $tripBase)->where('status', 'Completed');
        $allTrips = (clone $tripBase)
            ->with(['driver.profile_picture', 'currency', 'trip_rating', 'payment', 'car_type'])
            ->latest('id')
            ->take(8)
            ->get();
        $reviews = Rating::query()
            ->with('driver.profile_picture')
            ->where('user_id', $rider->id)
            ->where('driver_rating', '>', 0)
            ->latest('id')
            ->take(8)
            ->get();

        $companyId = $this->levelCompanyId();
        $metrics = $levelService->metricsForUser($rider, 'rider', $companyId);
        $completedCount = (int) ($metrics['completed_trips'] ?? 0);
        $cancelledCount = (int) ($metrics['cancelled_trips'] ?? 0);
        $totalTrips = max((int) ($metrics['total_trips'] ?? 0), 1);
        $reviewCount = $reviews->count();
        $positiveReviews = $reviews->where('driver_rating', '>=', 4)->count();
        $digitalPayments = (int) ($metrics['digital_payments'] ?? 0);
        $walletBalance = (float) optional($rider->wallet)->original_amount;
        $riderLevel = $levelService->assignmentOrDefault($rider, 'rider', $companyId);
        $activeSeconds = (int) (clone $completedTrips)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->sum(function ($trip) {
                return max(0, optional($trip->completed_at)->timestamp - optional($trip->started_at)->timestamp);
            });
        $levelPoints = (float) optional($riderLevel)->points;
        $levelTargetPoints = $this->levelTargetPoints(optional($riderLevel)->level?->target_config);

        return [
            'rider_recent_trips' => $allTrips,
            'rider_recent_reviews' => $reviews,
            'rider_recent_transactions' => $allTrips->where('status', 'Completed')->values(),
            'rider_trip_stats' => [
                'completed' => $completedCount,
                'cancelled' => $cancelledCount,
                'lowest_fare' => (float) ((clone $completedTrips)->min('total_fare') ?: 0),
                'highest_fare' => (float) ((clone $completedTrips)->max('total_fare') ?: 0),
                'wallet_balance' => round($walletBalance, 2),
                'reviews_given' => $reviewCount,
                'active_hours' => $this->formatDurationHours($activeSeconds),
                'level_points' => round($levelPoints, 2),
                'level_target_points' => $levelTargetPoints,
            ],
            'rider_rate_info' => [
                'digital_payment_rate' => $totalTrips > 0 ? round(($digitalPayments / $totalTrips) * 100, 2) : 0,
                'success_rate' => (float) ($metrics['success_rate'] ?? 0),
                'reviews_given_rate' => $completedCount > 0 ? round(($reviewCount / max(1, $completedCount)) * 100, 2) : 0,
                'cancellation_rate' => (float) ($metrics['cancellation_rate'] ?? 0),
                'rating' => (float) ($metrics['rating'] ?? 0),
                'rating_count' => $reviewCount,
                'positive_review_rate' => $reviewCount > 0 ? round(($positiveReviews / $reviewCount) * 100, 2) : 0,
            ],
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
