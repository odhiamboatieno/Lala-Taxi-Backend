<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NotificationCampaign;
use App\Models\User;
use App\Services\Notifications\NotificationManager;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SendmessageController extends Controller
{
    protected $notifications;

    public function __construct(NotificationManager $notifications)
    {
        $this->notifications = $notifications;
    }

    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->store($request);
        }

        return view('admin.send_message', [
            'recipientStats' => $this->recipientStats(),
            'campaigns' => $this->campaignQuery($request)->paginate(12)->withQueryString(),
        ]);
    }

    protected function store(Request $request)
    {
        $rules = [
            'title' => 'required|max:100',
            'message' => 'required|max:500',
            'user_type' => 'required',
            'message_type' => 'required|in:push,sms,both',
            'to' => 'required|in:to_all,to_specific',
            'schedule_type' => 'required|in:now,later',
            'recurrence' => 'required|in:none,daily,weekly,monthly,semiannual',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];

        if ($request->to === 'to_specific') {
            $rules['users'] = 'required';
        }

        if ($request->schedule_type === 'later') {
            $rules['scheduled_for'] = 'required|date';
        }

        $validator = Validator::make($request->all(), $rules, [], [
            'users' => LOGIN_USER_TYPE === 'company' ? 'Drivers' : 'Users',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/notification_campaigns';
            $file_name = 'campaign_'.time().'.'.$request->file('image')->getClientOriginalExtension();
            $uploadResult = $imageUploader->upload($request->file('image'), compact('target_dir', 'file_name'));

            if (! $uploadResult['status']) {
                flashMessage('error', $uploadResult['status_message']);
                return back()->withInput();
            }

            $imagePath = ltrim($target_dir.'/'.$uploadResult['file_name'], '/');
        }

        $campaign = NotificationCampaign::create([
            'company_id' => $this->scopeCompanyId(),
            'created_by_type' => LOGIN_USER_TYPE,
            'created_by_id' => $this->creatorId(),
            'title' => $request->title,
            'message' => $request->message,
            'channel' => $request->message_type,
            'target_mode' => $request->to === 'to_specific' ? 'specific' : 'all',
            'audience_type' => $request->user_type,
            'recipient_ids' => $this->selectedRecipientIds($request),
            'image_path' => $imagePath,
            'status' => $request->schedule_type === 'later' ? 'scheduled' : 'queued',
            'recurrence' => $request->recurrence,
            'scheduled_for' => $request->schedule_type === 'later' ? $request->scheduled_for : now(),
            'next_run_at' => $request->schedule_type === 'later' ? $request->scheduled_for : now(),
            'is_active' => true,
        ]);

        if ($request->schedule_type === 'now') {
            $this->notifications->processCampaign($campaign);
        }

        flashMessage('success', 'Notification campaign saved successfully');

        return redirect(LOGIN_USER_TYPE.'/send_message');
    }

    public function get_send_users(Request $request)
    {
        $type=$request->type;
        if($type == "Company") {
            $company_details = DB::table('companies')->select('id','name','mobile_number', DB::raw('"Company" as user_type'), DB::raw('name as first_name'))->where('status','Active')->where('id','!=',1)->get();
            return $company_details->toJson();
        }

        $user_details = DB::table('users')->select('id','first_name','mobile_number','user_type')->where('status','Active');

        if($type != "all") {
            $user_details=$user_details->where('user_type',$type);
        }

        if (LOGIN_USER_TYPE == 'company') {
            $user_details = $user_details->where('company_id',Auth::guard('company')->user()->id);
        }

        if ($type == 'all' && LOGIN_USER_TYPE != 'company') {
            $user_details = $user_details->get();
            $company_details= DB::table('companies')->select('id','mobile_number', DB::raw('"Company" as user_type'), DB::raw('name as first_name'))->where('status','Active')->where('id','!=',1)->get();
            $collection = collect([$user_details,$company_details]);
            $user_details = $collection->collapse();
            $user_details->all();
            return $user_details;
        }
        return $user_details->get()->toJson();
    }

    public function resend(int $id)
    {
        $campaign = $this->findCampaign($id);
        if (! $campaign) {
            flashMessage('error', 'Campaign not found');
            return back();
        }

        $copy = $campaign->replicate(['status', 'next_run_at', 'last_sent_at', 'successful_count', 'failed_count', 'last_recipient_count', 'created_at', 'updated_at']);
        $copy->status = 'queued';
        $copy->scheduled_for = now();
        $copy->next_run_at = now();
        $copy->is_active = true;
        $copy->save();

        $this->notifications->processCampaign($copy);

        flashMessage('success', 'Notification resent successfully');

        return back();
    }

    public function toggle(int $id)
    {
        $campaign = $this->findCampaign($id);
        if (! $campaign) {
            flashMessage('error', 'Campaign not found');
            return back();
        }

        $campaign->is_active = ! $campaign->is_active;
        $campaign->status = $campaign->is_active ? 'active' : 'paused';
        $campaign->save();

        flashMessage('success', 'Campaign status updated');

        return back();
    }

    public function destroy(int $id)
    {
        $campaign = $this->findCampaign($id);
        if (! $campaign) {
            flashMessage('error', 'Campaign not found');
            return back();
        }

        $campaign->dispatch_logs()->delete();
        $campaign->delete();

        flashMessage('success', 'Campaign deleted');

        return back();
    }

    protected function recipientStats(): array
    {
        $companyId = $this->scopeCompanyId();

        $drivers = User::query()->where('status', 'Active')->where('user_type', 'Driver');
        $riders = User::query()->where('status', 'Active')->where('user_type', 'Rider');

        if ($companyId > 0) {
            $drivers->where('company_id', $companyId);
            $riders->where('company_id', $companyId);
        }

        return [
            'drivers' => $drivers->count(),
            'riders' => $companyId > 0 ? 0 : $riders->count(),
            'companies' => $companyId > 0 ? 0 : Company::query()->where('status', 'Active')->where('id', '!=', 1)->count(),
        ];
    }

    protected function campaignQuery(Request $request)
    {
        $query = NotificationCampaign::query()
            ->where('company_id', $this->scopeCompanyId())
            ->withCount('dispatch_logs')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    protected function selectedRecipientIds(Request $request): array
    {
        if ($request->to !== 'to_specific') {
            return [];
        }

        return collect(explode(',', (string) $request->users))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    protected function scopeCompanyId(): int
    {
        return LOGIN_USER_TYPE === 'company' ? (int) Auth::guard('company')->id() : 0;
    }

    protected function creatorId(): ?int
    {
        return LOGIN_USER_TYPE === 'company'
            ? (int) Auth::guard('company')->id()
            : optional(Auth::guard('admin')->user())->id;
    }

    protected function findCampaign(int $id): ?NotificationCampaign
    {
        return NotificationCampaign::query()
            ->where('company_id', $this->scopeCompanyId())
            ->find($id);
    }
}
