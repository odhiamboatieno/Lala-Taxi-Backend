<?php

namespace App\Services\Corporate;

use App\Models\Trips;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CorporateCollectionGuard
{
    public function assertPersonalTrip(Trips $trip, User $rider): void
    {
        abort_unless((int) $trip->user_id === (int) $rider->id && $rider->user_type === 'Rider', 403);
        if ($trip->isCorporate()) {
            throw ValidationException::withMessages(['trip_id' => __('corporate.no_personal_collection')]);
        }
    }

    /** Bind a gateway request to the authenticated payer before any external charge. */
    public function validateEntry(Request $request, ?User $rider = null): void
    {
        if ($request->input('pay_for', session('payment_data.pay_for')) !== 'trip') {
            return;
        }

        $rider = $rider ?? \JWTAuth::parseToken()->authenticate();
        abort_unless($rider instanceof User, 401);
        if ($request->filled('user_id')) {
            abort_unless((int) $request->user_id === (int) $rider->id, 403);
        }

        $id = $request->input('trip_id', session('payment_data.trip_id'));
        if (!$id) {
            throw ValidationException::withMessages(['trip_id' => __('validation.required', ['attribute' => 'trip_id'])]);
        }
        $this->assertPersonalTrip(Trips::findOrFail($id), $rider);
    }
}
