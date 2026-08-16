<?php

use Illuminate\Support\Facades\Route;
use Modules\SaaSDispatch\Http\Controllers\CompanyDispatchController;

Route::prefix('api')
    ->middleware(['api', 'auth:company,admin', 'saas.dispatch.enabled'])
    ->group(function () {
        Route::get('company/dispatch/live-trips', [CompanyDispatchController::class, 'liveTrips']);
        Route::get('company/dispatch/live-drivers', [CompanyDispatchController::class, 'liveDrivers']);
        Route::post('company/trips/{trip}/assign-driver', [CompanyDispatchController::class, 'assignDriver']);
    });
