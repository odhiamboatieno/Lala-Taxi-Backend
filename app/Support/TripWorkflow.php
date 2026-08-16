<?php

namespace App\Support;

use InvalidArgumentException;

class TripWorkflow
{
    public const SERVICE_RIDE = 'ride';
    public const SERVICE_PARCEL = 'parcel';

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_SEARCHING_DRIVER = 'searching_driver';
    public const STATUS_DRIVER_ASSIGNED = 'driver_assigned';
    public const STATUS_DRIVER_ARRIVING = 'driver_arriving';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_STARTED = 'started';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_DRIVER_FOUND = 'no_driver_found';
    public const STATUS_LEGACY_RATING = 'Rating';
    public const STATUS_LEGACY_PAYMENT = 'Payment';
    public const STATUS_LEGACY_COMPLETED = 'Completed';

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_REQUESTED,
            self::STATUS_SEARCHING_DRIVER,
            self::STATUS_DRIVER_ASSIGNED,
            self::STATUS_DRIVER_ARRIVING,
            self::STATUS_ARRIVED,
            self::STATUS_STARTED,
        ];
    }

    public static function transitions(): array
    {
        return [
            self::STATUS_REQUESTED => [self::STATUS_SEARCHING_DRIVER, self::STATUS_CANCELLED],
            self::STATUS_SEARCHING_DRIVER => [self::STATUS_DRIVER_ASSIGNED, self::STATUS_CANCELLED],
            self::STATUS_DRIVER_ASSIGNED => [self::STATUS_DRIVER_ARRIVING, self::STATUS_CANCELLED],
            self::STATUS_DRIVER_ARRIVING => [self::STATUS_ARRIVED, self::STATUS_CANCELLED],
            self::STATUS_ARRIVED => [self::STATUS_STARTED, self::STATUS_CANCELLED],
            self::STATUS_STARTED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        ];
    }

    public static function canTransition(?string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        if ($from === null) {
            return $to === self::STATUS_REQUESTED;
        }

        return in_array($to, self::transitions()[$from] ?? [], true);
    }

    public static function assertTransition(?string $from, string $to): void
    {
        if (! self::canTransition($from, $to)) {
            throw new InvalidArgumentException("Invalid trip transition [{$from}] -> [{$to}]");
        }
    }

    public static function isParcel(string $serviceCategory): bool
    {
        return $serviceCategory === self::SERVICE_PARCEL;
    }

    public static function isRide(string $serviceCategory): bool
    {
        return $serviceCategory === self::SERVICE_RIDE;
    }
}
