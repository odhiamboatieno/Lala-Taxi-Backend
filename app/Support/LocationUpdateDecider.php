<?php

namespace App\Support;

class LocationUpdateDecider
{
    protected static function setting(string $key, mixed $default): mixed
    {
        try {
            return app()->bound('config') ? config($key, $default) : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function distanceMeters(?float $lat1, ?float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === null || $lng1 === null) {
            return INF;
        }

        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
            + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    public static function shouldBroadcast(?float $lat1, ?float $lng1, float $lat2, float $lng2): bool
    {
        return self::distanceMeters($lat1, $lng1, $lat2, $lng2) >= self::setting('dispatch.location_broadcast_distance_meters', 25);
    }

    public static function shouldPersistHistory(?float $lat1, ?float $lng1, float $lat2, float $lng2, int $secondsSinceLastRecord, bool $hasActiveTrip): bool
    {
        if ($hasActiveTrip) {
            return true;
        }

        if (self::distanceMeters($lat1, $lng1, $lat2, $lng2) >= self::setting('dispatch.location_history_distance_meters', 50)) {
            return true;
        }

        return $secondsSinceLastRecord >= self::setting('dispatch.location_history_interval_seconds', 30);
    }
}
