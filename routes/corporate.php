<?php

use App\Http\Controllers\Corporate\PortalAuthController;
use App\Http\Controllers\Corporate\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('corporate')->name('corporate.portal.')->middleware('web')->group(function () {
    Route::get('login', [PortalAuthController::class, 'loginForm'])->name('login');
    Route::post('login', [PortalAuthController::class, 'login'])->middleware('throttle:corporate-portal-login')->name('login.submit');
    Route::get('forgot-password', [PortalAuthController::class, 'forgotForm'])->name('password.request');
    Route::post('forgot-password', [PortalAuthController::class, 'forgot'])->middleware('throttle:corporate-portal-recovery')->name('password.email');
    Route::get('reset-password/{token}', [PortalAuthController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PortalAuthController::class, 'reset'])->middleware('throttle:corporate-portal-reset')->name('password.update');
    Route::post('logout', [PortalAuthController::class, 'logout'])->name('logout');
    Route::middleware('corporate.session')->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('members', [PortalController::class, 'members'])->name('members');
        Route::post('members', [PortalController::class, 'assignMember'])->middleware('throttle:20,1')->name('members.store');
        Route::delete('members/{rider}', [PortalController::class, 'removeMember'])->name('members.remove');
        Route::get('staff', [PortalController::class, 'staff'])->name('staff');
        Route::post('staff', [PortalController::class, 'saveStaff'])->name('staff.store');
        Route::put('staff/{staff}', [PortalController::class, 'saveStaff'])->name('staff.update');
        Route::post('staff/{staff}/invite', [PortalController::class, 'inviteStaff'])->middleware('throttle:10,1')->name('staff.invite');
        Route::get('trips', [PortalController::class, 'trips'])->name('trips');
        Route::get('trips/{trip}', [PortalController::class, 'trip'])->name('trips.show');
        Route::get('invoices', [PortalController::class, 'invoices'])->name('invoices');
        Route::get('invoices/{invoice}', [PortalController::class, 'invoice'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [PortalController::class, 'download'])->name('invoices.download');
    });
});
