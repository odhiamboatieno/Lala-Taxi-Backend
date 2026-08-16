<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Trips;
use App\Models\User;
use App\Services\Dispatch\TripDispatchService;
use App\Support\TripWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\SaaSDispatch\Http\Requests\AssignDriverRequest;

class CompanyDispatchController extends Controller
{
    public function __construct(protected TripDispatchService $dispatchService)
    {
    }

    public function liveTrips(Request $request): JsonResponse
    {
        [$actor, $companyId] = $this->resolveActorAndCompanyId($request);
        Gate::forUser($actor)->authorize('saas-dispatch.access', $companyId);

        $trips = Trips::query()
            ->where('company_id', $companyId)
            ->whereIn('status', TripWorkflow::activeStatuses())
            ->latest('id')
            ->get([
                'id',
                'user_id',
                'driver_id',
                'status',
                'pickup_location',
                'drop_location',
                'pickup_latitude',
                'pickup_longitude',
                'drop_latitude',
                'drop_longitude',
            ]);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Live trips loaded successfully.',
            'trips' => $trips,
        ]);
    }

    public function liveDrivers(Request $request): JsonResponse
    {
        [$actor, $companyId] = $this->resolveActorAndCompanyId($request);
        Gate::forUser($actor)->authorize('saas-dispatch.access', $companyId);

        $drivers = User::query()
            ->where('user_type', 'Driver')
            ->where('company_id', $companyId)
            ->where('is_online', true)
            ->get([
                'id',
                'first_name',
                'last_name',
                'is_online',
                'is_available',
                'current_trip_id',
                'current_lat',
                'current_lng',
                'last_seen_at',
            ]);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Live drivers loaded successfully.',
            'drivers' => $drivers,
        ]);
    }

    public function assignDriver(AssignDriverRequest $request, Trips $trip): JsonResponse
    {
        [$actor, $companyId] = $this->resolveActorAndCompanyId($request);
        Gate::forUser($actor)->authorize('saas-dispatch.assign-driver', [$companyId, $trip]);

        $driver = User::query()
            ->where('id', $request->integer('driver_id'))
            ->where('company_id', $companyId)
            ->where('user_type', 'Driver')
            ->firstOrFail();

        $trip = $this->dispatchService->assignTripToDriver($trip, $driver, 'dispatcher');

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver assigned successfully.',
            'trip' => [
                'id' => $trip->id,
                'driver_id' => $trip->driver_id,
                'status' => $trip->status,
            ],
        ]);
    }

    protected function resolveActorAndCompanyId(Request $request): array
    {
        $company = Auth::guard('company')->user();
        if ($company instanceof Company) {
            return [$company, (int) $company->id];
        }

        $admin = Auth::guard('admin')->user();
        if ($admin instanceof Admin) {
            $companyId = (int) $request->query('company_id', $request->input('company_id'));
            abort_if($companyId <= 0, 422, 'A valid company_id is required.');

            return [$admin, $companyId];
        }

        abort(403, 'Unauthorized dispatch access.');
    }
}
