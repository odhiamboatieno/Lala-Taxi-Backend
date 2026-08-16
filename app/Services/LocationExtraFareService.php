<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LocationExtraFareService
{
    public function resolveLocationByCoordinates($latitude, $longitude, bool $activeOnly = true): ?Location
    {
        $query = Location::select(
            DB::raw("id,name,status,extra_fare_enabled,extra_fare_percentage,extra_fare_reason,(ST_WITHIN(ST_GeomFromText('POINT(".$latitude.' '.$longitude.")'),ST_GeomFromText(coordinates))) as available")
        )->having('available', '1');

        if ($activeOnly) {
            $query->where('status', 'Active');
        }

        return $query->first();
    }

    public function getConfig($location): array
    {
        if (! $location instanceof Location) {
            $location = $location ? Location::find($location) : null;
        }

        $enabled = (bool) optional($location)->extra_fare_enabled;
        $percentage = (float) optional($location)->extra_fare_percentage;
        $reason = trim((string) optional($location)->extra_fare_reason);

        return [
            'location_id' => optional($location)->id,
            'enabled' => $enabled && $percentage > 0,
            'percentage' => $percentage > 0 ? round($percentage, 2) : 0.0,
            'reason' => $reason,
        ];
    }

    public function calculate(float $baseAmount, $location): array
    {
        $config = $this->getConfig($location);

        if (! $config['enabled'] || $baseAmount <= 0) {
            return array_merge($config, [
                'amount' => 0.0,
                'total' => round($baseAmount, 2),
            ]);
        }

        $amount = round(($config['percentage'] / 100) * $baseAmount, 2);

        return array_merge($config, [
            'amount' => $amount,
            'total' => round($baseAmount + $amount, 2),
        ]);
    }

    public function globalSummary(Collection $locations): array
    {
        $zones = $locations->values();
        $enabledZones = $zones->filter(fn ($location) => (bool) $location->extra_fare_enabled && (float) $location->extra_fare_percentage > 0);

        if ($enabledZones->isEmpty()) {
            return [
                'enabled' => false,
                'percentage' => 0.0,
                'reason' => '',
                'consistency' => 'none',
            ];
        }

        $first = $enabledZones->first();
        $consistent = $enabledZones->every(function ($location) use ($first) {
            return (bool) $location->extra_fare_enabled === (bool) $first->extra_fare_enabled
                && round((float) $location->extra_fare_percentage, 2) === round((float) $first->extra_fare_percentage, 2)
                && trim((string) $location->extra_fare_reason) === trim((string) $first->extra_fare_reason);
        });

        return [
            'enabled' => $enabledZones->count() === $zones->count() && $consistent,
            'percentage' => round((float) $first->extra_fare_percentage, 2),
            'reason' => (string) $first->extra_fare_reason,
            'consistency' => $consistent ? 'uniform' : 'mixed',
        ];
    }

    public function volumeLabel(int $tripCount): string
    {
        if ($tripCount >= 50) {
            return 'High';
        }

        if ($tripCount >= 15) {
            return 'Medium';
        }

        return 'Low';
    }
}
