<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DriverOweAmount;
use App\Models\DriverWithdrawalRequest;
use App\Models\SupportIssue;
use App\Models\Trips;
use Illuminate\Http\Request;
use JWTAuth;

class DriverAccountController extends Controller
{
    public function companyOptions()
    {
        $driver = JWTAuth::parseToken()->authenticate();

        $companies = Company::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Company options fetched successfully.',
            'current_company_id' => (string) ($driver->company_id ?? ''),
            'company_list' => $companies->map(function (Company $company) {
                return [
                    'company_id' => (string) $company->id,
                    'company_name' => $company->name,
                ];
            })->values(),
        ]);
    }

    public function updateCompany(Request $request)
    {
        $driver = JWTAuth::parseToken()->authenticate();

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = Company::query()->active()->find($validated['company_id']);

        if (! $company) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Selected company is not available.',
            ]);
        }

        $driver->company_id = $company->id;
        $driver->save();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver company updated successfully.',
            'company_id' => (string) $company->id,
            'company_name' => $company->name,
        ]);
    }

    public function deleteEligibility()
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $eligibility = $this->buildEligibilityPayload($driver->id);

        return response()->json([
            'status_code' => '1',
            'status_message' => $eligibility['can_delete']
                ? 'Driver account can be deleted.'
                : 'Driver account cannot be deleted right now.',
            ...$eligibility,
        ]);
    }

    public function deleteAccount()
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $eligibility = $this->buildEligibilityPayload($driver->id);

        if (! $eligibility['can_delete']) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Driver account cannot be deleted right now.',
                ...$eligibility,
            ]);
        }

        $driver->delete();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver account deleted successfully.',
        ]);
    }

    protected function buildEligibilityPayload(int $driverId): array
    {
        $pendingTrips = Trips::query()
            ->where('driver_id', $driverId)
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();

        $outstandingBalance = (float) DriverOweAmount::query()
            ->where('user_id', $driverId)
            ->value('amount');

        $pendingWithdrawals = DriverWithdrawalRequest::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['pending', 'processing', 'approved'])
            ->count();

        $pendingCases = SupportIssue::query()
            ->where('user_id', $driverId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $reasons = [];

        if ($pendingTrips > 0) {
            $reasons[] = 'You still have pending trips.';
        }
        if ($outstandingBalance > 0) {
            $reasons[] = 'You still have an outstanding balance owed to the company or admin.';
        }
        if ($pendingWithdrawals > 0) {
            $reasons[] = 'You still have pending withdrawal requests.';
        }
        if ($pendingCases > 0) {
            $reasons[] = 'You still have open complaints, cases, or support tickets.';
        }

        return [
            'can_delete' => count($reasons) === 0,
            'pending_trips_count' => $pendingTrips,
            'outstanding_balance' => number_format($outstandingBalance, 2, '.', ''),
            'pending_withdrawal_requests_count' => $pendingWithdrawals,
            'pending_cases_count' => $pendingCases,
            'reasons' => $reasons,
        ];
    }
}
