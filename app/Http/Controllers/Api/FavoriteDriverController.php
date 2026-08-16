<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FavoriteDriver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class FavoriteDriverController extends Controller
{
    public function index()
    {
        $rider = JWTAuth::parseToken()->authenticate();

        $favorites = FavoriteDriver::query()
            ->with(['driver.profile_picture', 'driver.vehicle'])
            ->where('rider_id', $rider->id)
            ->latest('id')
            ->get()
            ->map(function (FavoriteDriver $favorite) {
                $driver = $favorite->driver;

                return [
                    'id' => $favorite->id,
                    'driver_id' => $favorite->driver_id,
                    'driver_name' => trim(($driver->first_name ?? '').' '.($driver->last_name ?? '')),
                    'driver_image' => $driver->profile_picture->src ?? url('images/user.jpeg'),
                    'vehicle_name' => optional($driver->vehicle)->vehicle_name,
                    'vehicle_number' => optional($driver->vehicle)->vehicle_number,
                ];
            })
            ->values();

        return response()->json([
            'status_code' => '1',
            'status_message' => __('messages.api.listed_successfully'),
            'favorite_driver_enabled' => $this->isFeatureEnabled(),
            'favorites' => $favorites,
        ]);
    }

    public function status(Request $request)
    {
        $rider = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $driverId = (int) $request->driver_id;

        return response()->json([
            'status_code' => '1',
            'status_message' => __('messages.api.success'),
            'favorite_driver_enabled' => $this->isFeatureEnabled(),
            'is_favorite' => FavoriteDriver::query()
                ->where('rider_id', $rider->id)
                ->where('driver_id', $driverId)
                ->exists(),
        ]);
    }

    public function toggle(Request $request)
    {
        $rider = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        if (! $this->isFeatureEnabled()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Favourite driver feature is disabled.',
            ]);
        }

        $driver = User::query()->where('user_type', 'Driver')->find($request->driver_id);

        if (! $driver) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Selected user is not a driver.',
            ]);
        }

        $existing = FavoriteDriver::query()
            ->where('rider_id', $rider->id)
            ->where('driver_id', $driver->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'status_code' => '1',
                'status_message' => 'Driver removed from favourites.',
                'favorite_driver_enabled' => true,
                'is_favorite' => false,
            ]);
        }

        FavoriteDriver::create([
            'rider_id' => $rider->id,
            'driver_id' => $driver->id,
        ]);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver added to favourites.',
            'favorite_driver_enabled' => true,
            'is_favorite' => true,
        ]);
    }

    protected function isFeatureEnabled(): bool
    {
        return site_settings('favorite_driver_enabled') === '1';
    }
}
