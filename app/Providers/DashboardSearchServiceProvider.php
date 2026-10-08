<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CancelReason;
use App\Models\CarType;
use App\Models\Company;
use App\Models\Documents;
use App\Models\DriverScore;
use App\Models\DriverWithdrawalRequest;
use App\Models\Fees;
use App\Models\Help;
use App\Models\HelpCategory;
use App\Models\HelpSubCategory;
use App\Models\Location;
use App\Models\MakeVehicle;
use App\Models\ManageFare;
use App\Models\NotificationCampaign;
use App\Models\Pages;
use App\Models\ParcelCategory;
use App\Models\ParcelFare;
use App\Models\ParcelPackageSize;
use App\Models\ParcelRequest;
use App\Models\ParcelTask;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Rating;
use App\Models\ReferralUser;
use App\Models\Request as RideRequest;
use App\Models\Role;
use App\Models\ScheduleRide;
use App\Models\Support;
use App\Models\SupportIssue;
use App\Models\TollReason;
use App\Models\Trips;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\Wallet;
use App\Observers\DashboardSearchObserver;
use App\Observers\UserSearchObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DashboardSearchServiceProvider extends ServiceProvider
{
    /**
     * Maps an index entity type to the Eloquent model that owns its rows. Only
     * models with a real class are observed; module-backed sources are still
     * refreshed by the `search:index` command and the request-time self-heal.
     */
    private const MODEL_MAP = [
        'vehicle' => Vehicle::class,
        'trip' => Trips::class,
        'booking' => ScheduleRide::class,
        'request' => RideRequest::class,
        'payment' => Payment::class,
        'rating' => Rating::class,
        'wallet' => Wallet::class,
        'driver_withdrawal' => DriverWithdrawalRequest::class,
        'referral_user' => ReferralUser::class,
        'driver_score' => DriverScore::class,
        'parcel_category' => ParcelCategory::class,
        'parcel_package_size' => ParcelPackageSize::class,
        'parcel_task' => ParcelTask::class,
        'parcel_pricing' => ParcelFare::class,
        'parcel_trip' => ParcelRequest::class,
        'vehicle_type' => CarType::class,
        'vehicle_make' => MakeVehicle::class,
        'vehicle_model' => VehicleModel::class,
        'location' => Location::class,
        'peak_fare' => ManageFare::class,
        'toll_reason' => TollReason::class,
        'cancel_reason' => CancelReason::class,
        'fee' => Fees::class,
        'help_article' => Help::class,
        'help_category' => HelpCategory::class,
        'help_subcategory' => HelpSubCategory::class,
        'support' => Support::class,
        'support_issue' => SupportIssue::class,
        'document' => Documents::class,
        'notification_campaign' => NotificationCampaign::class,
        'promo_code' => PromoCode::class,
        'page' => Pages::class,
        'company' => Company::class,
        'admin_user' => Admin::class,
        'role' => Role::class,
    ];

    public function boot(): void
    {
        try {
            if (!Schema::hasTable('dashboard_search_index')) {
                return;
            }
        } catch (\Throwable $exception) {
            return;
        }

        foreach (self::MODEL_MAP as $type => $model) {
            if (!class_exists($model)) {
                continue;
            }
            // Listen on Eloquent's model-scoped event strings instead of using
            // observer registration. Model::observe() instantiates the target
            // model (some carry the CurrencyConversion trait whose constructor
            // queries the Currency table and crashes when no default currency
            // row exists), and the event helper() methods collide with legacy
            // models that define their own saved()/restored() methods.
            $observer = new DashboardSearchObserver($type);
            Event::listen("eloquent.saved: {$model}", fn ($instance) => $observer->saved($instance));
            Event::listen("eloquent.deleted: {$model}", fn ($instance) => $observer->deleted($instance));
            Event::listen("eloquent.restored: {$model}", fn ($instance) => $observer->restored($instance));
        }

        if (class_exists(User::class)) {
            $userObserver = new UserSearchObserver();
            Event::listen('eloquent.saved: '.User::class, fn ($user) => $userObserver->saved($user));
            Event::listen('eloquent.deleted: '.User::class, fn ($user) => $userObserver->deleted($user));
            Event::listen('eloquent.restored: '.User::class, fn ($user) => $userObserver->restored($user));
        }
    }
}
