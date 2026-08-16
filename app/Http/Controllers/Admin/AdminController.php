<?php

/**
 * Admin Controller
 *
 * @package     LalaTaxi
 * @subpackage  Controller
 * @category    Admin
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */


namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\Country;
use App\Models\Role;
use App\Models\Currency;
use App\Models\Company;
use App\Models\Location;
use App\Models\Rating;
use App\Http\Start\Helpers;
use App\Support\TripOperations;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Validator;
use Session;
use Modules\OperationTeam\Entities\OperationTeamMember;

class AdminController extends Controller
{
    /**
     * Load Index View for Dashboard
     *
     * @return view index
     */
    public function index()
    {
        $users = DB::table('users');
        $data['users_count'] = with(clone $users)->count();

        //if login user is company then only get company user
        $data['total_driver'] = with(clone $users)->where('user_type','Driver')
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { 
                $query->where('company_id',Auth::guard('company')->user()->id);
            }
        })
        ->count();
        $data['total_rider'] = with(clone $users)->where('user_type','Rider')
        ->where(function($query) {
            if (LOGIN_USER_TYPE == 'company') {
                $companyId = Auth::guard('company')->user()->id;
                $query->whereIn('id', function ($q) use ($companyId) {
                    $q->select('user_id')->from('trips')
                      ->join('users as d', 'd.id', '=', 'trips.driver_id')
                      ->where('d.company_id', $companyId);
                });
            }
        })
        ->count();

        //if login user is company then only get company drivers
        $data['today_driver_count'] = with(clone $users)->whereDate('created_at', '=', date('Y-m-d'))
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { 
                $query->where('company_id',Auth::guard('company')->user()->id);
            }
        })
        ->where('user_type','Driver')
        ->count();
        $data['today_rider_count'] = with(clone $users)->whereDate('created_at', '=', date('Y-m-d'))->where('user_type','Rider')
        ->where(function($query) {
            if (LOGIN_USER_TYPE == 'company') {
                $companyId = Auth::guard('company')->user()->id;
                $query->whereIn('id', function ($q) use ($companyId) {
                    $q->select('user_id')->from('trips')
                      ->join('users as d', 'd.id', '=', 'trips.driver_id')
                      ->where('d.company_id', $companyId);
                });
            }
        })
        ->count();

        $default_currency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {  //if login user is company then get session currency
            $default_currency = Currency::whereCode(session()->get('currency'))->first();
        }
        $data['currency_code'] = $default_currency->symbol;
        $currency_rate = $default_currency->rate;
        
        $all_trips = DB::table('trips')
            ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            });

        $revenue_trips = DB::table('trips')
            ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })->select(DB::raw('( 
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.'))
                        - sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))
                        + sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.'))  ) as company_revenue '),
                    DB::raw('( sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.peak_amount / currency.rate) * '.$currency_rate.')) - sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.schedule_fare / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))  ) as admin_revenue ')
                        );


        if(LOGIN_USER_TYPE=='company') {  //if login user is company then revenue calculated from company trips
            $data['today_revenue'] = with(clone $revenue_trips)
                ->whereDate('trips.created_at', '=', date('Y-m-d'))
                ->where('trips.status','Completed')
                ->join('users', function($join) {
                    $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
                })->first()->company_revenue;
        } else {
            $data['today_revenue'] =  with(clone $revenue_trips)
                ->whereDate('trips.created_at', '=', date('Y-m-d'))
                ->where('trips.status','Completed')
                ->first()->admin_revenue;

        }
        //if login user is company then get only company driver's trip
        $today_trips = with(clone $all_trips)->whereDate('trips.created_at', '=', date('Y-m-d'));
        if(LOGIN_USER_TYPE=='company') {   
            $today_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['today_trips'] = $today_trips->count();

        //if login user is company then get only company driver's trip

        $total_trips = with(clone $all_trips);
        if(LOGIN_USER_TYPE=='company') {   
            $total_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['total_trips'] = $total_trips->count();


        //if login user is company then get only company driver's trip

        $total_success_trips = with(clone $all_trips)->where('trips.status','Completed');
        if(LOGIN_USER_TYPE=='company') {   
            $total_success_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['total_success_trips'] = $total_success_trips->count();

        $total_revenue = with(clone $revenue_trips)->where('trips.status','Completed');
        if(LOGIN_USER_TYPE=='company') {   
            $total_revenue->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            });
        }

        $data['total_revenue'] = LOGIN_USER_TYPE=='company' ? $total_revenue->first()->company_revenue:$total_revenue->first()->admin_revenue;
        
     

        if(LOGIN_USER_TYPE=='company') {
            $admin_driver_paid_amount = with(clone $revenue_trips)
            ->where('trips.status','Completed')
            ->where('driver_payout','>',0)
            ->where('payment_mode','<>','Cash')
            ->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            })
            ->join('payment', function($join) {
                $join->on('trips.id', '=', 'payment.trip_id');
            })
            ->select(DB::raw('sum(ROUND((trips.driver_payout/currency.rate) * '.$currency_rate.')) as driver_payout'));

            $admin_paid_amount = with(clone $admin_driver_paid_amount)->where('payment.admin_payout_status','Paid')->first();
            $admin_pending_amount = with(clone $admin_driver_paid_amount)->where('payment.admin_payout_status','Pending')->first();

            $data['admin_paid_amount'] = $admin_paid_amount ? $admin_paid_amount->driver_payout:0;
            $data['admin_pending_amount'] = $admin_pending_amount ? $admin_pending_amount->driver_payout:0;

        }
       

        $data['recent_trips'] = RideRequest::
        with(['trips','users','car_type','request'])
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { //if login user is company then get only company driver's trip
                $query->whereHas('driver',function($q1){
                    $q1->where('company_id',Auth::guard('company')->user()->id);
                });
            }
        })
        ->groupBy('group_id')
        ->orderBy('group_id','desc')
        ->limit(10)->get();


        $quarter1 = ['01', '02', '03'];
        $quarter2 = ['04', '05', '06'];
        $quarter3 = ['07', '08', '09'];
        $quarter4 = ['10', '11', '12'];
        $chart = Trips::
        whereRaw('YEAR(trips.created_at) = ?',[date('Y')])
        ->where('trips.status', 'Completed')
        ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })
        ->select(DB::raw('( 
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.'))
                        - sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))
                        + sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.'))  ) as company_revenue '),
                    DB::raw('( sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.peak_amount / currency.rate) * '.$currency_rate.')) - sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.schedule_fare / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))  ) as admin_revenue ')
                        );

        if(LOGIN_USER_TYPE=='company') {   
            $chart->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('users.company_id',Auth::guard('company')->user()->id);
            });
        }





        $quarter1_chart=clone($chart);
        $quarter2_chart=clone($chart);
        $quarter3_chart=clone($chart);
        $quarter4_chart=clone($chart);

        //if login user is company then total earning is sum of trip amount .If login user is admin then total revenue is sum of admin commission

        $quarter_amount[1]=floatval($quarter1_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter1)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[2]=floatval($quarter2_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter2)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[3]=floatval($quarter3_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter3)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[4]=floatval($quarter4_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter4)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));

        $chart_array = [];
        $year = date('Y');
        for($quarter=1;$quarter<=4;$quarter++)
        {
            $array['y'] = $year.' Q'.$quarter;
            $array['amount'] = number_format($quarter_amount[$quarter],2,'.','');
            $chart_array[] = $array;
        }
        $data['line_chart_data'] = json_encode($chart_array);
        $data['dashboard_overview'] = $this->buildDashboardOverview();
        $data['location_trip_statistics'] = $this->buildLocationTripStatistics();
        $data['earnings_chart'] = $this->buildEarningsChart();
        $data['driver_leaderboard'] = $this->buildLeaderboardData('Driver');
        $data['customer_leaderboard'] = $this->buildLeaderboardData('Rider');
        $data['recent_transactions'] = $this->buildRecentTransactions();
        $data['recent_trip_activity'] = $this->buildRecentTripActivity();
        $data['dashboard_title'] = LOGIN_USER_TYPE == 'company' ? 'Company Operations Dashboard' : 'Admin Operations Dashboard';
        $data['dashboard_subtitle'] = LOGIN_USER_TYPE == 'company'
            ? 'Monitor your company performance, service zones, parcel activity, and operator earnings in one place.'
            : 'Monitor the platform, parcel demand, operator performance, and live business statistics in one place.';

        return view('admin.index', $data);
    }

    protected function buildDashboardOverview(): array
    {
        $completedTrips = $this->completedDashboardTripsQuery('all_time');

        $parcelTrips = $this->completedDashboardTripsQuery('all_time');
        $this->applyParcelFilter($parcelTrips);

        $scheduledTrips = $this->completedDashboardTripsQuery('all_time');
        $this->applyScheduledFilter($scheduledTrips);

        $regularTrips = $this->completedDashboardTripsQuery('all_time');
        $this->excludeParcelTrips($regularTrips);
        $this->excludeScheduledTrips($regularTrips);

        $parcelEarningsQuery = $this->completedDashboardTripsQuery('all_time');
        $this->applyParcelFilter($parcelEarningsQuery);

        $regularEarningsQuery = $this->completedDashboardTripsQuery('all_time');
        $this->excludeParcelTrips($regularEarningsQuery);
        $this->excludeScheduledTrips($regularEarningsQuery);

        $scheduledEarningsQuery = $this->completedDashboardTripsQuery('all_time');
        $this->applyScheduledFilter($scheduledEarningsQuery);

        return [
            'active_customers' => [
                'label' => 'Total Active Customers',
                'value' => $this->dashboardUsersQuery('Rider')->where('status', 'Active')->count(),
                'icon' => 'ri-group-line',
                'tone' => 'sky',
            ],
            'active_drivers' => [
                'label' => 'Total Active Drivers',
                'value' => $this->dashboardUsersQuery('Driver')->where('status', 'Active')->count(),
                'icon' => 'ri-taxi-line',
                'tone' => 'mint',
            ],
            'total_earnings' => [
                'label' => LOGIN_USER_TYPE == 'company' ? 'Company Earnings' : 'Total Earnings',
                'value' => round((float) $completedTrips->sum('trips.total_fare'), 2),
                'icon' => 'ri-money-dollar-circle-line',
                'tone' => 'sun',
                'money' => true,
            ],
            'parcel_statistics' => [
                'label' => 'Total Parcel',
                'value' => $parcelTrips->count(),
                'secondary_value' => round((float) $parcelEarningsQuery->sum('trips.total_fare'), 2),
                'secondary_label' => 'Parcel Earnings',
                'icon' => 'ri-box-3-line',
                'tone' => 'violet',
                'money_secondary' => true,
            ],
            'regular_statistics' => [
                'label' => 'Regular Ride',
                'value' => $regularTrips->count(),
                'secondary_value' => round((float) $regularEarningsQuery->sum('trips.total_fare'), 2),
                'secondary_label' => 'Regular Ride Earnings',
                'icon' => 'ri-car-line',
                'tone' => 'emerald',
                'money_secondary' => true,
            ],
            'scheduled_statistics' => [
                'label' => 'Scheduled Ride',
                'value' => $scheduledTrips->count(),
                'secondary_value' => round((float) $scheduledEarningsQuery->sum('trips.total_fare'), 2),
                'secondary_label' => 'Scheduled Ride Earnings',
                'icon' => 'ri-calendar-schedule-line',
                'tone' => 'rose',
                'money_secondary' => true,
            ],
        ];
    }

    protected function buildLocationTripStatistics(): array
    {
        $periods = [
            'last_7_days' => 'Last 7 days',
            'this_month' => 'This month',
            'all_time' => 'All time',
        ];

        $datasets = [];
        foreach ($periods as $key => $label) {
            $datasets[$key] = [
                'label' => $label,
                'items' => $this->buildLocationTripStatisticsForPeriod($key),
            ];
        }

        return [
            'title' => 'Location-Based Trip Statistics',
            'subtitle' => 'Trip volume by active zone',
            'default_period' => 'all_time',
            'periods' => $datasets,
        ];
    }

    protected function buildLocationTripStatisticsForPeriod(string $period): array
    {
        if (!Schema::hasTable('locations') || !Schema::hasTable('request') || !Schema::hasColumn('request', 'location_id')) {
            return [];
        }

        $completedStatuses = $this->sqlList(TripOperations::completedStatuses());
        $cancelledStatuses = $this->sqlList(TripOperations::cancelledStatuses());
        $liveStatuses = $this->sqlList(TripOperations::liveStatuses());

        $query = DB::table('locations')
            ->leftJoin('request', 'request.location_id', '=', 'locations.id')
            ->leftJoin('trips', 'trips.request_id', '=', 'request.id');

        if (LOGIN_USER_TYPE == 'company') {
            $companyId = Auth::guard('company')->id();
            $query->leftJoin('users as zone_driver', 'zone_driver.id', '=', 'trips.driver_id')
                ->where(function ($scope) use ($companyId) {
                    if (Schema::hasColumn('trips', 'company_id')) {
                        $scope->where('trips.company_id', $companyId)
                            ->orWhere('zone_driver.company_id', $companyId);
                    } else {
                        $scope->where('zone_driver.company_id', $companyId);
                    }
                });
        }

        $this->applyDashboardPeriodFilter($query, $period, 'trips.created_at');

        $overallTrips = (clone $query)->whereNotNull('trips.id')->count('trips.id');
        if ($overallTrips === 0) {
            return [];
        }

        return (clone $query)
            ->select('locations.id', 'locations.name')
            ->selectRaw('COUNT(trips.id) as total_trips')
            ->selectRaw("SUM(CASE WHEN trips.status IN ({$liveStatuses}) THEN 1 ELSE 0 END) as on_trip_trips")
            ->selectRaw("SUM(CASE WHEN trips.status IN ({$completedStatuses}) THEN 1 ELSE 0 END) as completed_trips")
            ->selectRaw("SUM(CASE WHEN trips.status IN ({$cancelledStatuses}) THEN 1 ELSE 0 END) as cancelled_trips")
            ->whereNotNull('trips.id')
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('total_trips')
            ->limit(8)
            ->get()
            ->map(function ($item) use ($overallTrips) {
                $item->volume_percentage = round((((int) $item->total_trips) / max($overallTrips, 1)) * 100, 1);
                return (array) $item;
            })
            ->all();
    }

    protected function buildEarningsChart(): array
    {
        $periods = [
            'all_time' => 'All time',
            'this_year' => 'This year',
            'this_month' => 'This month',
            'last_7_days' => 'Last 7 days',
        ];

        $datasets = [];
        foreach ($periods as $key => $label) {
            $datasets[$key] = [
                'label' => $label,
                'data' => $this->buildEarningsChartDataset($key),
            ];
        }

        return [
            'title' => LOGIN_USER_TYPE == 'company' ? 'Company Earnings Statistics' : 'Admin Earnings Statistics',
            'subtitle' => LOGIN_USER_TYPE == 'company' ? 'Gross bookings vs company earnings' : 'Gross bookings vs platform earnings',
            'default_period' => 'all_time',
            'series_title' => LOGIN_USER_TYPE == 'company' ? 'Company Earnings' : 'Platform Earnings',
            'periods' => $datasets,
        ];
    }

    protected function buildEarningsChartDataset(string $period): array
    {
        $query = $this->completedDashboardTripsQuery($period);
        $operatorExpression = $this->dashboardOperatorRevenueExpression();

        if (in_array($period, ['this_month', 'last_7_days'])) {
            $rows = (clone $query)
                ->selectRaw('DATE(trips.created_at) as bucket_key')
                ->selectRaw('SUM(COALESCE(trips.total_fare, 0)) as gross_total')
                ->selectRaw('SUM('.$operatorExpression.') as operator_total')
                ->groupBy('bucket_key')
                ->orderBy('bucket_key')
                ->get()
                ->keyBy('bucket_key');

            $start = $period === 'this_month' ? now()->startOfMonth() : now()->copy()->subDays(6)->startOfDay();
            $end = $period === 'this_month' ? now()->copy()->endOfDay() : now()->copy()->endOfDay();
            $labels = [];
            $gross = [];
            $operator = [];

            while ($start->lte($end)) {
                $bucketKey = $start->format('Y-m-d');
                $row = $rows->get($bucketKey);
                $labels[] = $start->format('d M');
                $gross[] = round((float) ($row->gross_total ?? 0), 2);
                $operator[] = round((float) ($row->operator_total ?? 0), 2);
                $start->addDay();
            }

            return compact('labels', 'gross', 'operator');
        }

        if ($period === 'this_year') {
            $start = now()->startOfYear();
            $end = now()->copy()->endOfYear();
        } else {
            $start = now()->copy()->subMonths(11)->startOfMonth();
            $end = now()->copy()->endOfMonth();
        }

        $rows = (clone $query)
            ->selectRaw("DATE_FORMAT(trips.created_at, '%Y-%m') as bucket_key")
            ->selectRaw('SUM(COALESCE(trips.total_fare, 0)) as gross_total')
            ->selectRaw('SUM('.$operatorExpression.') as operator_total')
            ->groupBy('bucket_key')
            ->orderBy('bucket_key')
            ->get()
            ->keyBy('bucket_key');

        $labels = [];
        $gross = [];
        $operator = [];

        while ($start->lte($end)) {
            $bucketKey = $start->format('Y-m');
            $row = $rows->get($bucketKey);
            $labels[] = $start->format('M Y');
            $gross[] = round((float) ($row->gross_total ?? 0), 2);
            $operator[] = round((float) ($row->operator_total ?? 0), 2);
            $start->addMonth();
        }

        return compact('labels', 'gross', 'operator');
    }

    protected function buildLeaderboardData(string $userType): array
    {
        $periods = [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'all_time' => 'All Time',
        ];

        $datasets = [];
        foreach ($periods as $key => $label) {
            $datasets[$key] = [
                'label' => $label,
                'items' => $this->buildLeaderboardPeriodData($userType, $key),
            ];
        }

        return [
            'type' => $userType,
            'default_period' => 'all_time',
            'periods' => $datasets,
        ];
    }

    protected function buildLeaderboardPeriodData(string $userType, string $period): array
    {
        $column = $userType === 'Driver' ? 'trips.driver_id' : 'trips.user_id';
        $earningsColumn = $userType === 'Driver' ? 'trips.driver_payout' : 'trips.total_fare';

        $rows = $this->completedDashboardTripsQuery($period)
            ->whereNotNull($column)
            ->selectRaw("{$column} as user_id")
            ->selectRaw('COUNT(*) as metric_total')
            ->selectRaw("SUM(COALESCE({$earningsColumn}, 0)) as earnings_total")
            ->groupBy('user_id')
            ->orderByDesc('metric_total')
            ->orderByDesc('earnings_total')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $userIds = $rows->pluck('user_id')->filter()->values();
        $users = User::with('profile_picture')->whereIn('id', $userIds)->get()->keyBy('id');
        $ratingColumn = $userType === 'Driver' ? 'driver_rating' : 'rider_rating';
        $ratingKey = $userType === 'Driver' ? 'driver_id' : 'user_id';

        $ratings = Rating::query()
            ->whereIn($ratingKey, $userIds)
            ->select($ratingKey)
            ->selectRaw("AVG(COALESCE({$ratingColumn}, 0)) as rating_avg")
            ->selectRaw("COUNT({$ratingColumn}) as rating_count")
            ->groupBy($ratingKey)
            ->get()
            ->keyBy($ratingKey);

        return $rows->map(function ($row) use ($users, $ratings, $userType, $ratingKey) {
            $user = $users->get($row->user_id);
            $rating = $ratings->get($row->user_id);
            $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

            return [
                'user_id' => (int) $row->user_id,
                'name' => $name !== '' ? $name : ($userType === 'Driver' ? 'Driver' : 'Customer').' #'.$row->user_id,
                'avatar' => optional(optional($user)->profile_picture)->src ?? url('images/user.jpeg'),
                'metric_total' => (int) $row->metric_total,
                'earnings_total' => round((float) $row->earnings_total, 2),
                'rating_avg' => round((float) ($rating->rating_avg ?? 0), 1),
                'rating_count' => (int) ($rating->rating_count ?? 0),
            ];
        })->values()->all();
    }

    protected function buildRecentTransactions(): array
    {
        return $this->completedDashboardTripsQuery('this_month')
            ->leftJoin('users as rider_user', 'rider_user.id', '=', 'trips.user_id')
            ->leftJoin('users as driver_user', 'driver_user.id', '=', 'trips.driver_id')
            ->leftJoin('profile_picture as rider_profile_picture', 'rider_profile_picture.user_id', '=', 'trips.user_id')
            ->leftJoin('profile_picture as driver_profile_picture', 'driver_profile_picture.user_id', '=', 'trips.driver_id')
            ->select(
                'trips.id',
                'trips.total_fare',
                'trips.payment_mode',
                'trips.payment_status',
                'trips.created_at',
                'trips.updated_at',
                'rider_user.first_name as rider_first_name',
                'rider_user.last_name as rider_last_name',
                'driver_user.first_name as driver_first_name',
                'driver_user.last_name as driver_last_name',
                'rider_profile_picture.src as rider_avatar',
                'driver_profile_picture.src as driver_avatar'
            )
            ->orderByDesc('trips.updated_at')
            ->limit(4)
            ->get()
            ->map(function ($trip) {
                $mode = trim((string) $trip->payment_mode);
                $transactionLabel = in_array($mode, ['Cash', 'Cash & Wallet'])
                    ? 'Credited to receivable balance'
                    : 'Credited to payable balance';

                return [
                    'amount' => round((float) $trip->total_fare, 2),
                    'title' => $transactionLabel,
                    'reference' => '#Trip'.$trip->id,
                    'meta' => trim(($trip->rider_first_name ?? '').' '.($trip->rider_last_name ?? '')) ?: 'Trip payment',
                    'rider_name' => trim(($trip->rider_first_name ?? '').' '.($trip->rider_last_name ?? '')) ?: 'Rider',
                    'driver_name' => trim(($trip->driver_first_name ?? '').' '.($trip->driver_last_name ?? '')) ?: 'Driver',
                    'rider_avatar' => $trip->rider_avatar ?: url('images/user.jpeg'),
                    'driver_avatar' => $trip->driver_avatar ?: url('images/user.jpeg'),
                    'time' => Carbon::parse($trip->updated_at ?? $trip->created_at)->format('d M h:i a'),
                    'status' => $trip->payment_status ?: 'Pending',
                ];
            })
            ->all();
    }

    protected function buildRecentTripActivity(): array
    {
        return $this->dashboardTripsTableQuery()
            ->leftJoin('car_type', 'car_type.id', '=', 'trips.car_id')
            ->select('trips.id', 'trips.status', 'trips.created_at', 'car_type.car_name', 'car_type.vehicle_image')
            ->orderByDesc('trips.created_at')
            ->limit(6)
            ->get()
            ->map(function ($trip) {
                $vehicleImage = '';
                if (!empty($trip->vehicle_image)) {
                    $vehicleImage = filter_var($trip->vehicle_image, FILTER_VALIDATE_URL)
                        ? $trip->vehicle_image
                        : url('images/car_image/'.ltrim($trip->vehicle_image, '/'));
                }

                return [
                    'trip_id' => $trip->id,
                    'status' => $trip->status ?: 'Pending',
                    'vehicle' => $trip->car_name ?: 'Trip',
                    'vehicle_image' => $vehicleImage,
                    'time' => Carbon::parse($trip->created_at)->format('d M h:i a'),
                    'tone' => $this->dashboardStatusTone($trip->status),
                ];
            })
            ->all();
    }

    protected function dashboardUsersQuery(string $userType)
    {
        $query = User::query()->where('user_type', $userType);

        if (LOGIN_USER_TYPE == 'company') {
            $companyId = Auth::guard('company')->id();

            if ($userType === 'Driver') {
                $query->where('company_id', $companyId);
            } else {
                $tripUserIds = $this->dashboardTripsTableQuery()->select('trips.user_id')->distinct();
                $query->whereIn('id', $tripUserIds);
            }
        }

        return $query;
    }

    protected function dashboardTripsTableQuery()
    {
        $query = DB::table('trips');

        if (LOGIN_USER_TYPE == 'company') {
            $companyId = Auth::guard('company')->id();
            $query->leftJoin('users as scope_driver', 'scope_driver.id', '=', 'trips.driver_id')
                ->where(function ($scope) use ($companyId) {
                    if (Schema::hasColumn('trips', 'company_id')) {
                        $scope->where('trips.company_id', $companyId)
                            ->orWhere('scope_driver.company_id', $companyId);
                    } else {
                        $scope->where('scope_driver.company_id', $companyId);
                    }
                });
        }

        return $query;
    }

    protected function completedDashboardTripsQuery(string $period)
    {
        $query = $this->dashboardTripsTableQuery()->whereIn('trips.status', TripOperations::completedStatuses());
        $this->applyDashboardPeriodFilter($query, $period, 'trips.created_at');

        return $query;
    }

    protected function applyDashboardPeriodFilter($query, string $period, string $column = 'created_at'): void
    {
        if ($period === 'today') {
            $query->whereDate($column, now());
            return;
        }

        if ($period === 'this_week') {
            $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]);
            return;
        }

        if ($period === 'this_month') {
            $query->whereBetween($column, [now()->startOfMonth(), now()->endOfMonth()]);
            return;
        }

        if ($period === 'this_year') {
            $query->whereBetween($column, [now()->startOfYear(), now()->endOfYear()]);
            return;
        }

        if ($period === 'last_7_days') {
            $query->whereBetween($column, [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        }
    }

    protected function applyParcelFilter($query): void
    {
        if (Schema::hasColumn('trips', 'parcel_category_id')) {
            $query->whereNotNull('trips.parcel_category_id');
            return;
        }

        $query->whereRaw('1 = 0');
    }

    protected function excludeParcelTrips($query): void
    {
        if (Schema::hasColumn('trips', 'parcel_category_id')) {
            $query->whereNull('trips.parcel_category_id');
        }
    }

    protected function applyScheduledFilter($query): void
    {
        if (!Schema::hasTable('request') || !Schema::hasColumn('trips', 'request_id') || !Schema::hasColumn('request', 'schedule_id')) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->leftJoin('request as schedule_request', 'schedule_request.id', '=', 'trips.request_id')
            ->whereNotNull('schedule_request.schedule_id')
            ->where('schedule_request.schedule_id', '!=', 'Null')
            ->where('schedule_request.schedule_id', '!=', 0)
            ->where('schedule_request.schedule_id', '!=', '');
    }

    protected function excludeScheduledTrips($query): void
    {
        if (!Schema::hasTable('request') || !Schema::hasColumn('trips', 'request_id') || !Schema::hasColumn('request', 'schedule_id')) {
            return;
        }

        $query->leftJoin('request as regular_request', 'regular_request.id', '=', 'trips.request_id')
            ->where(function ($scheduleFilter) {
                $scheduleFilter->whereNull('regular_request.schedule_id')
                    ->orWhere('regular_request.schedule_id', 'Null')
                    ->orWhere('regular_request.schedule_id', 0)
                    ->orWhere('regular_request.schedule_id', '');
            });
    }

    protected function dashboardOperatorRevenueExpression(): string
    {
        if (LOGIN_USER_TYPE == 'company') {
            $positiveParts = [
                'COALESCE(trips.subtotal_fare, 0)',
                'COALESCE(trips.driver_peak_amount, 0)',
                'COALESCE(trips.tips, 0)',
                'COALESCE(trips.waiting_charge, 0)',
                'COALESCE(trips.toll_fee, 0)',
                'COALESCE(trips.additional_rider_amount, 0)',
            ];

            $negativeParts = [
                'COALESCE(trips.driver_or_company_commission, 0)',
            ];

            if (Schema::hasColumn('trips', 'company_driver_fee_amount')) {
                $negativeParts[] = 'COALESCE(trips.company_driver_fee_amount, 0)';
            }

            return '('.implode(' + ', $positiveParts).' - '.implode(' - ', $negativeParts).')';
        }

        return '(COALESCE(trips.access_fee, 0) + (COALESCE(trips.peak_amount, 0) - COALESCE(trips.driver_peak_amount, 0)) + COALESCE(trips.schedule_fare, 0) + COALESCE(trips.driver_or_company_commission, 0))';
    }

    protected function dashboardStatusTone(?string $status): string
    {
        if (in_array($status, TripOperations::completedStatuses())) {
            return 'success';
        }

        if (in_array($status, TripOperations::cancelledStatuses())) {
            return 'danger';
        }

        if (in_array($status, TripOperations::liveStatuses())) {
            return 'warning';
        }

        return 'info';
    }

    protected function sqlList(array $values): string
    {
        return collect($values)
            ->map(function ($value) {
                return "'".str_replace("'", "''", $value)."'";
            })
            ->implode(',');
    }

    /**
     * Load Datatable for Admin Users
     *
     * @param array $dataTable  Instance of AdminuserDataTable
     * @return datatable
     */
    public function view(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $admins = Admin::query();

        if ($search !== '') {
            $admins->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return view('admin.admin_users.view', [
            'admins' => $admins->orderBy('id')->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Load Login View
     *
     * @return view login
     */
    public function login()
    {
        return view('admin.login');
    }

    /**
     * Add Admin User Details
     *
     * @param array $request    Input values
     * @return redirect     to Admin Users View
     */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['roles'] = Role::all()->pluck('name','id');
            $data['countries'] = Country::codeSelect();

            return view('admin.admin_users.add', $data);  
        }

        if($request->submit) {
            // Add Admin User Validation Rules
            $rules = [
                'username'      => 'required|unique:admins',
                'email'         => 'required|email|unique:admins',
                'password'      => 'required',
                'role'          => 'required',
                'status'        => 'required',
                'country_code'  => 'required',
                'mobile_number' => 'required|numeric',
            ];

            // Add Admin User Validation Custom Names
            $attributes = [
                'username'      => 'Username',
                'email'         => 'Email',
                'password'      => 'Password',
                'role'          => 'Role',
                'status'        => 'Status',
                'country_code'  => 'Country Code',
                'mobile_number' => 'Mobile Number',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $admin = new Admin;
            $admin->username = $request->username;
            $admin->email    = $request->email;
            $admin->password = $request->password;
            $admin->status   = $request->status;
            $admin->country_code = Country::find($request->country_code)->phone_code;
            $admin->country_id = $request->country_code;
            $admin->mobile_number   = $request->mobile_number;
            $admin->save();

            $admin->attachRole($request->role); 
           
            flashMessage('success', 'Added Successfully'); 
        }

        return redirect('admin/admin_user');
    }

    /**
     * Update Admin User Details
     *
     * @param array $request    Input values
     * @return redirect     to Admin Users View
     */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result']  = Admin::find($request->id);
            $data['roles'] = Role::all()->pluck('name','id');
            $data['countries'] = Country::codeSelect();
            if($data['result']) {
                return view('admin.admin_users.edit', $data);    
            }
            flashMessage('danger', 'Invalid ID');
            return redirect('admin/admin_user');
        }
        if($request->submit) {
            // Edit Admin User Validation Rules
            $rules = [
                'username'   => 'required|unique:admins,username,'.$request->id,
                'email'      => 'required|email|unique:admins,email,'.$request->id,
                'country_code'     => 'required',
                'mobile_number'     => 'required|numeric',
                'role'       => 'required',
                'status'     => 'required'
            ];

            // Edit Admin User Validation Custom Fields Name
            $attributes = [
                'username'   => 'Username',
                'email'      => 'Email',
                'country_code' => 'Country Code',
                'mobile_number' => 'Mobile Number',
                'role'       => 'Role',
                'status'     => 'Status'
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $admins = Admin::active()->count();
            if($admins==1 && $request->status=='Inactive') {
                flashMessage('danger', 'You can\'t inactive the last one. Atleast one should be available.');
                return back();
            }

            $admin = Admin::find($request->id);

            $admin->username = $request->username;
            $admin->email    = $request->email;
           $admin->country_code = Country::find($request->country_code)->phone_code;
            $admin->country_id = $request->country_code;
            $admin->mobile_number = $request->mobile_number;
            $admin->status   = $request->status;
            
            if($request->filled("password")) {
                $admin->password = $request->password;
            }
            $admin->save();

            $role_id = Role::role_user($request->id)->role_id;

            if($role_id!=$request->role) {
                $admin->detachRole($role_id);
                $admin->attachRole($request->role);
            }
        
            flashMessage('success', 'Updated Successfully');

            // Redirect to dashboard when current user not have a permission to view admin users
            if(!Auth::guard('admin')->user()->can('manage_admin')) {
                return redirect('admin/dashboard');
            }

        }
        return redirect('admin/admin_user');
    }

    /**
     * Login Authentication
     *
     * @param array $request Input values
     * @return redirect     to dashboard
     */
    public function authenticate(Request $request)
    {
        if($request->getmethod() == 'GET') {
            return redirect()->route('admin_login');
        }

        if ($request->user_type == 'Company') {
            $login_column = is_numeric($request->username)?'mobile_number':'email';

            $company = Company::where($login_column, $request->username)->first();
            if ($company && $company->status != "Inactive") {
                
                $guard = Auth::guard('company')->attempt([$login_column => $request->username, 'password' => $request->password]);
                if ($guard) {
                    return redirect()->intended('company/dashboard');
                }
                flashMessage('danger', 'Log In Failed. Please Check Your Email(or)Mobile/Password');
                request()->flashExcept('password');
                return redirect('signin_company')->withInput(request()->except('password'));
            }

            $member = OperationTeamMember::query()
                ->with(['role', 'company'])
                ->where($login_column, $request->username)
                ->first();

            if ($member && $member->company && $member->company->status != "Inactive") {
                if (!$member->status || !$member->role || !$member->role->status) {
                    flashMessage('danger', 'Log In Failed. Your employee access is inactive.');
                    request()->flashExcept('password');
                    return redirect('signin_company')->withInput(request()->except('password'));
                }

                if (Hash::check($request->password, $member->password)) {
                    Auth::guard('company')->login($member->company);
                    session(['operation_team_member_id' => $member->id]);
                    return redirect()->intended('company/dashboard');
                }

                flashMessage('danger', 'Log In Failed. Please Check Your Email(or)Mobile/Password');
                request()->flashExcept('password');
                return redirect('signin_company')->withInput(request()->except('password'));
            }

            if ($company && $company->status == "Inactive") {
                flashMessage('danger', 'Log In Failed. You are Blocked by Admin.');
                request()->flashExcept('password');
                return redirect('signin_company')->withInput(request()->except('password'));
            }

            if ($member && $member->company && $member->company->status == "Inactive") {
                flashMessage('danger', 'Log In Failed. You are Blocked by Admin.');
                request()->flashExcept('password');
                return redirect('signin_company')->withInput(request()->except('password'));
            }
        }
        else{
            $admin = Admin::where('username', $request->username)->first();

            if(isset($admin) && $admin->status != 'Inactive') {
                if(Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
                    return redirect()->intended('admin/dashboard');
                }

                flashMessage('danger', 'Log In Failed. Please Check Your Username/Password');
                request()->flashExcept('password');
                return redirect('admin/login')->withInput(request()->except('password'));
            }
        }

        flashMessage('danger', 'Log In Failed. You are Blocked by Admin.');
        request()->flashExcept('password');
        return redirect($request->user_type == 'Company' ? 'signin_company' : 'admin/login')->withInput(request()->except('password'));
    }

    /**
     * Admin Logout
     */
    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect('admin/login');
    }


    public function delete(Request $request)
    {
        $admins = Admin::active()->count();
        if($admins==1) {
            flashMessage('danger', 'You can\'t delete the last one. Atleast one should be available.');
            return back();
        }

        $admin = Admin::where('id',$request->id)->first();
        if($admin) {
            $roles_user = DB::table('role_user')->where('user_id',$request->id)->delete();
            $admin = $admin->delete();
            flashMessage('success', 'Deleted Successfully');
        } else {
            flashMessage('danger', 'You can\'t able to delete');
        }
        return redirect('admin/admin_user');
    }
}
