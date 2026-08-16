<?php

namespace Modules\ManualBooking\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureManualBookingEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (LOGIN_USER_TYPE === 'admin') {
            return $next($request);
        }

        if (!manual_booking_module_enabled()) {
            abort(403, 'Manual booking is currently disabled by the administrator.');
        }

        return $next($request);
    }
}
