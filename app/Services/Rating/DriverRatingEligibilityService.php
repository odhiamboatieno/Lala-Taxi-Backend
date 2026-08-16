<?php

namespace App\Services\Rating;

use App\Events\DriverRatingTriggerEvent;
use App\Models\Rating;
use App\Models\Trips;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DriverRatingEligibilityService
{
    public function status(int $tripId): array
    {
        $trip = Trips::query()->find($tripId);

        if (! $trip) {
            return $this->response(false, $tripId, 'trip_not_found', false);
        }

        $alreadyRated = $this->driverRatingExists($trip);
        if ($alreadyRated) {
            return $this->response(false, $trip->id, 'already_rated', true);
        }

        if (! $this->tripCompleted($trip)) {
            return $this->response(false, $trip->id, 'trip_not_completed', false);
        }

        if (! $this->paymentSettled($trip)) {
            return $this->response(false, $trip->id, 'payment_not_completed', false);
        }

        return $this->response(true, $trip->id, 'payment_completed', false);
    }

    public function shouldShowRating(int $tripId): bool
    {
        return (bool) $this->status($tripId)['show_rating'];
    }

    public function assertCanRate(Trips $trip): ?array
    {
        $status = $this->status((int) $trip->id);

        if ($status['show_rating']) {
            return null;
        }

        return $status;
    }

    public function emitIfEligible(Trips $trip): void
    {
        if (! $trip->driver_id || ! $this->shouldShowRating((int) $trip->id)) {
            return;
        }

        if (Schema::hasColumn('trips', 'driver_rating_triggered_at') && $trip->driver_rating_triggered_at) {
            return;
        }

        if (Schema::hasColumn('trips', 'driver_rating_triggered_at')) {
            Trips::query()
                ->whereKey($trip->id)
                ->whereNull('driver_rating_triggered_at')
                ->update(['driver_rating_triggered_at' => now()]);
        }

        try {
            event(new DriverRatingTriggerEvent((int) $trip->driver_id, (int) $trip->id));
        } catch (\Throwable $exception) {
            Log::warning('Driver rating trigger broadcast failed', [
                'trip_id' => $trip->id,
                'driver_id' => $trip->driver_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function driverRatingExists(Trips $trip): bool
    {
        return Rating::query()
            ->where('trip_id', $trip->id)
            ->where(function ($query) use ($trip) {
                $query->where('driver_rating', '>', 0);

                if (Schema::hasColumn('rating', 'rater_id') && Schema::hasColumn('rating', 'rater_type')) {
                    $query->orWhere(function ($raterQuery) use ($trip) {
                        $raterQuery->where('rater_id', $trip->driver_id)
                            ->where('rater_type', 'driver');
                    });
                }
            })
            ->exists();
    }

    protected function tripCompleted(Trips $trip): bool
    {
        return strtolower((string) $trip->getOriginal('status')) === 'completed';
    }

    protected function paymentSettled(Trips $trip): bool
    {
        $paymentMode = strtolower((string) $trip->payment_mode);
        $paymentStatus = strtolower((string) $trip->payment_status);

        if (str_contains($paymentMode, 'cash')) {
            $cashCollected = ! Schema::hasColumn('trips', 'cash_collected') || (bool) $trip->cash_collected;
            $driverConfirmedCash = ! Schema::hasColumn('trips', 'driver_confirmed_cash') || (bool) $trip->driver_confirmed_cash;

            return $cashCollected && $driverConfirmedCash;
        }

        return in_array($paymentStatus, ['paid', 'settled', 'completed'], true);
    }

    protected function response(bool $showRating, int $tripId, string $reason, bool $alreadyRated): array
    {
        return [
            'show_rating' => $showRating,
            'trip_id' => $tripId,
            'reason' => $reason,
            'already_rated' => $alreadyRated,
        ];
    }
}
