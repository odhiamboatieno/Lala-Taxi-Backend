<?php

use Illuminate\Support\Facades\Route;
use Modules\UserLevels\Http\Controllers\CustomerLevelController;
use Modules\UserLevels\Http\Controllers\DriverLevelController;

Route::group([
    'prefix' => (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE == 'company') ? 'company' : 'admin',
    'middleware' => ['web', 'admin_guest'],
], function () {
    // Note: every `admin_can` middleware below also accepts the
    // `manage_admin` alternative so that operation team members whose
    // role has the umbrella `manage_admin` permission can still act on
    // behalf of a SaaS company.  The legacy `index / store / update /
    // destroy / toggle` actions all rely on this fallback.

    Route::get('driver-levels', [DriverLevelController::class, 'index'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.index');
    Route::get('driver-levels/create', [DriverLevelController::class, 'create'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.create');
    Route::post('driver-levels', [DriverLevelController::class, 'store'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.store');
    Route::get('driver-levels/{userLevel}/edit', [DriverLevelController::class, 'edit'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.edit');
    Route::put('driver-levels/{userLevel}', [DriverLevelController::class, 'update'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.update');
    Route::delete('driver-levels/{userLevel}', [DriverLevelController::class, 'destroy'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.destroy');
    Route::post('driver-levels/{userLevel}/toggle', [DriverLevelController::class, 'toggle'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.toggle');

    // SaaS: adopt an admin-defined driver level for the current company
    // and (optionally) override its values.  `manage_admin` is the
    // operation-team fallback permission.
    Route::post('driver-levels/inherit/{adminLevel}', [DriverLevelController::class, 'inheritFromAdmin'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->whereNumber('adminLevel')
        ->name('userlevels.driver-levels.inherit');
    Route::post('driver-levels/{userLevel}/unlink', [DriverLevelController::class, 'unlinkFromAdmin'])
        ->middleware('admin_can:manage_driver_levels|manage_admin')
        ->name('userlevels.driver-levels.unlink');

    Route::get('customer-levels', [CustomerLevelController::class, 'index'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.index');
    Route::get('customer-levels/create', [CustomerLevelController::class, 'create'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.create');
    Route::post('customer-levels', [CustomerLevelController::class, 'store'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.store');
    Route::get('customer-levels/{userLevel}/edit', [CustomerLevelController::class, 'edit'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.edit');
    Route::put('customer-levels/{userLevel}', [CustomerLevelController::class, 'update'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.update');
    Route::delete('customer-levels/{userLevel}', [CustomerLevelController::class, 'destroy'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.destroy');
    Route::post('customer-levels/{userLevel}/toggle', [CustomerLevelController::class, 'toggle'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.toggle');

    // SaaS: adopt an admin-defined customer (rider) level for the
    // current company and (optionally) override its values.
    Route::post('customer-levels/inherit/{adminLevel}', [CustomerLevelController::class, 'inheritFromAdmin'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->whereNumber('adminLevel')
        ->name('userlevels.customer-levels.inherit');
    Route::post('customer-levels/{userLevel}/unlink', [CustomerLevelController::class, 'unlinkFromAdmin'])
        ->middleware('admin_can:manage_rider_levels|manage_admin')
        ->name('userlevels.customer-levels.unlink');
});
