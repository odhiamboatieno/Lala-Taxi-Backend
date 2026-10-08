<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardSearchController extends Controller
{
    public function __invoke(Request $request, DashboardSearch $search): JsonResponse
    {
        $scope = $request->segment(1) === 'company' ? 'company' : 'admin';
        $user = auth($scope)->user();
        abort_unless($user, 401);
        $member = $scope === 'company' ? current_operation_team_member() : null;
        if ($scope === 'company' && session()->has('operation_team_member_id')) {
            abort_unless($member && (int) $member->company_id === (int) $user->id && $member->status && $member->role && $member->role->status, 403);
        }
        if (is_string($request->input('q'))) {
            $request->merge(['q' => trim($request->input('q'))]);
        }
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $can = fn (string $permission) => $scope === 'company' ? operation_team_can($permission) : $user->can($permission);
        $featureEnabled = function (string $feature) use ($scope): bool {
            if ($feature === 'accounts_invoicing') {
                $capabilities = app(\App\Services\Corporate\CorporateCapabilities::class);
                return $scope === 'admin' ? $capabilities->platformServicingEnabled() : $capabilities->servicingEnabled(auth('company')->user());
            }
            if ($feature === 'manual_booking') {
                return manual_booking_module_enabled() && ($scope !== 'company' || auth('company')->user()->status === 'Active');
            }
            return company_saas_enabled() && ($feature === 'saas' || saas_feature_module_enabled($feature));
        };

        return response()->json($search->search(trim($validated['q']), $scope, $scope === 'company' ? (int) $user->id : null, $can, $featureEnabled))
            ->header('Cache-Control', 'no-store, private');
    }
}
