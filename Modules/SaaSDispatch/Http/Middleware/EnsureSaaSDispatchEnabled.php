<?php

namespace Modules\SaaSDispatch\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Services\SaaSModuleManager;

class EnsureSaaSDispatchEnabled
{
    public function __construct(protected SaaSModuleManager $moduleManager)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        abort_unless($this->moduleManager->isEnabled(), 404);

        return $next($request);
    }
}
