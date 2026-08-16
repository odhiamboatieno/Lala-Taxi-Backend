<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyDataSuiteController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('data_suite');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        $tripQuery = Trips::query()->where('company_id', $companyId);

        return view('saasdispatch::data_suite.index', array_merge($context, [
            'stats' => [
                'totalTrips' => (clone $tripQuery)->count(),
                'completedTrips' => (clone $tripQuery)->where('status', 'Completed')->count(),
                'cancelledTrips' => (clone $tripQuery)->whereIn('status', ['Cancelled', 'cancelled'])->count(),
                'activeDrivers' => User::query()->where('company_id', $companyId)->where('user_type', 'Driver')->where('status', 'Active')->count(),
                'riders' => User::query()->where('company_id', $companyId)->where('user_type', 'Rider')->count(),
                'requests' => RideRequest::query()->where('company_id', $companyId)->count(),
            ],
            'dailyRevenue' => (clone $tripQuery)
                ->selectRaw('DATE(created_at) as trip_date, COUNT(*) as trips_count, SUM(COALESCE(total_fare, 0)) as gross_revenue')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('trip_date', 'desc')
                ->limit(14)
                ->get(),
        ]));
    }
}
