<?php

namespace Tests\Unit;

use App\Services\Corporate\CorporateMoney;
use PHPUnit\Framework\TestCase;

class CorporateMoneyTest extends TestCase
{
    public function test_fixed_point_roundtrips_and_sums_without_float_rounding(): void
    {
        foreach (['0.00', '0.01', '1180.00', '9999999999.99'] as $value) {
            $this->assertSame($value, CorporateMoney::decimal(CorporateMoney::minor($value)));
        }
        $this->assertSame('0.30', CorporateMoney::sum(['0.10', '0.20']));
        $this->assertSame(120, CorporateMoney::minor('1.2'));
    }

    public function test_invalid_precision_negative_and_overflow_values_are_rejected(): void
    {
        foreach (['-1', '1.001', '1e3', '10000000000.00', '1,000.00', 'NaN'] as $value) {
            try {
                CorporateMoney::minor($value);
                $this->fail('Invalid monetary input must fail.');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->expectException(\InvalidArgumentException::class);
        CorporateMoney::sum(['9999999999.99', '0.01']);
    }
}
