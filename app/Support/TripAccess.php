<?php

namespace App\Support;

use App\Models\Trips;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class TripAccess
{
    public function participant(Model $trip, User $user): void
    {
        $column = match ($user->user_type) { 'Rider' => 'user_id', 'Driver' => 'driver_id', default => null };
        abort_unless($column && (int) $trip->{$column} === (int) $user->id, 404, __('corporate.record_not_found'));
    }

    public function find(int $id, User $user, bool $driverOnly = false): Trips
    {
        abort_if($driverOnly && $user->user_type !== 'Driver', 403, __('corporate.access_denied'));
        $trip = Trips::findOrFail($id);
        $this->participant($trip, $user);
        return $trip;
    }

    public function mutate(Trips $candidate, User $user, array $allowedStatuses, callable $change): Trips
    {
        return DB::transaction(function () use ($candidate, $user, $allowedStatuses, $change) {
            // Match the finance lock order so an old client cannot reopen a charge during completion/settlement.
            if ($candidate->isCorporate()) {
                CompanyCorporateAccount::lockForUpdate()->findOrFail($candidate->corporate_account_id);
            }
            $trip = Trips::lockForUpdate()->findOrFail($candidate->id);
            $this->participant($trip, $user);
            abort_if($trip->billing_finalized_at || !in_array(strtolower((string) $trip->getRawOriginal('status')), $allowedStatuses, true),
                409, __('corporate.trip_not_active'));
            $change($trip);
            return $trip;
        }, 3);
    }
}
