<?php

use App\Models\Trips;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('trip.rider.{riderId}', function ($user, $riderId) {
    return $user
        && $user->user_type === 'Rider'
        && (int) $user->id === (int) $riderId;
});

Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return $user
        && $user->user_type === 'Driver'
        && (int) $user->id === (int) $driverId;
});

Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    $trip = Trips::find($tripId);

    if (! $trip) {
        return false;
    }

    $company = Auth::guard('company')->user();
    $admin = Auth::guard('admin')->user();

    if ($admin) {
        return true;
    }

    if ($company) {
        return (int) $trip->company_id === (int) $company->id;
    }

    if (! $user) {
        return false;
    }

    if ($user->user_type === 'Rider' && (int) $trip->user_id === (int) $user->id) {
        return true;
    }

    if ($user->user_type === 'Driver' && (int) $trip->driver_id === (int) $user->id) {
        return true;
    }

    return false;
});

Broadcast::channel('company.{companyId}.dispatch', function ($user = null, $companyId = null) {
    $company = Auth::guard('company')->user();
    $admin = Auth::guard('admin')->user();
    $user = $user ?: Auth::user();
    $companyId = (int) $companyId;

    if ($admin) {
        return true;
    }

    if ($company) {
        return (int) $company->id === $companyId;
    }

    return $user
        && $user->user_type === 'Driver'
        && (int) $user->company_id === $companyId;
});

Broadcast::channel('company.{companyId}.drivers', function ($user, $companyId) {
    if (! $user
        || $user->user_type !== 'Driver'
        || (int) $user->company_id !== (int) $companyId
        || ! (bool) $user->is_online) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => trim($user->first_name.' '.$user->last_name),
        'is_available' => (bool) $user->is_available,
    ];
});
