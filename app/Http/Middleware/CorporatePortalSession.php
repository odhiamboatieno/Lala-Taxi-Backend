<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporatePortalSession
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('corporate');
        $user = $guard->user();
        if (!$user) return redirect()->route('corporate.portal.login')->header('Cache-Control', 'private, no-store');
        $user->refresh();
        if ($user->status !== 'Active' || !$user->password_set_at || !in_array($user->role, ['admin', 'finance', 'viewer'], true)
            || (int) $request->session()->get('corporate.session_version') !== $user->session_version
            || !$user->account || ($user->account->status !== 'Active' && !$user->account->archived_at)) {
            $guard->logout();
            $request->session()->forget('corporate.session_version');
            $request->session()->regenerate();
            return redirect()->route('corporate.portal.login')->withErrors(['email' => __('corporate.portal_access_revoked')])
                ->header('Cache-Control', 'private, no-store');
        }
        $request->attributes->set('corporate_portal_user', $user);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
