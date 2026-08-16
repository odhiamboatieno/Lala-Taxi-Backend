<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MapController;
use Modules\ManualBooking\Http\Controllers\LaterBookingController;
use Modules\ManualBooking\Http\Controllers\ManualBookingController;

Route::group([
    'prefix' => (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE == 'company') ? 'company' : 'admin',
    'middleware' => ['web', 'admin_guest', 'manual.booking.enabled'],
], function () {
    Route::group(['middleware' => 'admin_can:manage_manual_booking'], function () {
        Route::get('manual_booking_zones', [ManualBookingController::class, 'zones']);
        Route::get('manual_booking_heat_map', [MapController::class, 'heat_map_data']);
        Route::get('manual_booking/{id?}', [ManualBookingController::class, 'index']);
        Route::post('manual_booking/store', [ManualBookingController::class, 'store']);
        Route::post('search_phone', [ManualBookingController::class, 'search_phone']);
        Route::post('search_cars', [ManualBookingController::class, 'search_cars']);
        Route::post('get_driver', [ManualBookingController::class, 'get_driver']);
        Route::post('driver_list', [ManualBookingController::class, 'driver_list']);
        Route::get('manual_booking/drafts', [ManualBookingController::class, 'listDrafts']);
        Route::post('manual_booking/drafts', [ManualBookingController::class, 'saveDraft']);
        Route::get('manual_booking/templates', [ManualBookingController::class, 'listTemplates']);
        Route::post('manual_booking/templates', [ManualBookingController::class, 'saveTemplate']);
        Route::post('manual_booking/customer-history', [ManualBookingController::class, 'customerHistory']);
        Route::get('later_booking', [LaterBookingController::class, 'index']);
        Route::post('immediate_request', [LaterBookingController::class, 'immediate_request']);
        Route::post('manual_booking/cancel', [LaterBookingController::class, 'cancel']);
    });
});
