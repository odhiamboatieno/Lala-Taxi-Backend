<?php

namespace App\Services\Corporate;

use InvalidArgumentException;

final class CorporateMoney
{
    private const MAX_MINOR = 999999999999;

    public static function minor(string $amount): int
    {
        if (!preg_match('/^([0-9]{1,10})(?:\.([0-9]{1,2}))?$/D', $amount, $match)) {
            throw new InvalidArgumentException('Expected a non-negative DECIMAL(12,2) amount.');
        }
        $minor = ((int) $match[1] * 100) + (int) str_pad($match[2] ?? '', 2, '0');
        if ($minor > self::MAX_MINOR) {
            throw new InvalidArgumentException('Amount exceeds DECIMAL(12,2).');
        }

        return $minor;
    }

    public static function decimal(int $minor): string
    {
        if ($minor < 0 || $minor > self::MAX_MINOR) {
            throw new InvalidArgumentException('Amount exceeds non-negative DECIMAL(12,2).');
        }

        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function sum(iterable $amounts): string
    {
        $minor = 0;
        foreach ($amounts as $amount) {
            $minor += self::minor((string) $amount);
        }

        return self::decimal($minor);
    }
}
