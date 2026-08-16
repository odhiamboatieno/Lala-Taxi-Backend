<?php

use Illuminate\Support\Facades\Route;
use Modules\SurgePricing\Http\Controllers\SurgePricingController;

Route::group([
    'prefix' => (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company') ? 'company' : 'admin',
    'middleware' => ['web', 'admin_guest'],
], function () {
    $permission = (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company') ? 'manage_company_surge_pricing' : 'manage_surge_pricing';

    Route::get('surge-pricing', [SurgePricingController::class, 'index'])->middleware("admin_can:{$permission}");
    Route::get('surge-pricing/create', [SurgePricingController::class, 'create'])->middleware("admin_can:{$permission}");
    Route::post('surge-pricing', [SurgePricingController::class, 'store'])->middleware("admin_can:{$permission}");
    Route::get('surge-pricing/{surgePricing}', [SurgePricingController::class, 'show'])->middleware("admin_can:{$permission}");
    Route::get('surge-pricing/{surgePricing}/edit', [SurgePricingController::class, 'edit'])->middleware("admin_can:{$permission}");
    Route::put('surge-pricing/{surgePricing}', [SurgePricingController::class, 'update'])->middleware("admin_can:{$permission}");
    Route::delete('surge-pricing/{surgePricing}', [SurgePricingController::class, 'destroy'])->middleware("admin_can:{$permission}");
    Route::post('surge-pricing/{surgePricing}/toggle', [SurgePricingController::class, 'toggle'])->middleware("admin_can:{$permission}");
});
