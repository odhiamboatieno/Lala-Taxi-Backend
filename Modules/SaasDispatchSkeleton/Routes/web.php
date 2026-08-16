<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/saas-dispatch')->middleware(['web', 'admin_guest'])->group(function () {
    Route::get('/', function () {
        return 'SaaS Dispatch module is loaded.';
    })->name('saasdispatch.dashboard');
});
