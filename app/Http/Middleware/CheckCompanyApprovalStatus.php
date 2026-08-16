<?php

namespace App\Http\Middleware;

use App\Support\CompanyOnboarding;
use Auth;
use Closure;

class CheckCompanyApprovalStatus
{
    public function handle($request, Closure $next)
    {
        if ($request->segment(1) !== 'company' || Auth::guard('company')->guest()) {
            return $next($request);
        }

        if (!CompanyOnboarding::enabled()) {
            return $next($request);
        }

        $company = Auth::guard('company')->user();
        $currentPath = trim($request->path(), '/');
        $allowedPrefixes = [
            'company/onboarding',
            'company/account-under-review',
            'company/logout',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if ($currentPath === $prefix || strpos($currentPath, $prefix . '/') === 0) {
                if (CompanyOnboarding::isApproved($company) && $prefix !== 'company/logout') {
                    return redirect('company/dashboard');
                }

                return $next($request);
            }
        }

        $redirect = CompanyOnboarding::dashboardRouteForStatus($company);
        if ($redirect) {
            return redirect($redirect);
        }

        return $next($request);
    }
}
