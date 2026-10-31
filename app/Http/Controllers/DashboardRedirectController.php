<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    /** Resolve legacy bookmarks/intended URLs without bypassing destination guards. */
    public function __invoke(): RedirectResponse
    {
        if (Auth::guard('admin')->check()) return redirect('/admin/dashboard');
        if (Auth::guard('company')->check()) return redirect('/company/dashboard');
        if (Auth::guard('corporate')->check()) return redirect()->route('corporate.portal.dashboard');

        $user = Auth::guard('web')->user();
        if ($user?->user_type === 'Rider') return redirect()->route('rider.trips');
        if ($user?->user_type === 'Driver') return redirect('/driver_profile');

        return redirect('/signin');
    }
}
