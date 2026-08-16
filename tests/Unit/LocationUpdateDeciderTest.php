<?php

namespace Tests\Unit;

use App\Support\LocationUpdateDecider;
use PHPUnit\Framework\TestCase;

class LocationUpdateDeciderTest extends TestCase
{
    public function test_broadcasts_when_driver_moves_meaningfully(): void
    {
        $this->assertTrue(
            LocationUpdateDecider::shouldBroadcast(-1.286389, 36.817223, -1.286000, 36.817800)
        );
    }

    public function test_does_not_broadcast_tiny_movement(): void
    {
        $this->assertFalse(
            LocationUpdateDecider::shouldBroadcast(-1.286389, 36.817223, -1.286390, 36.817224)
        );
    }
}
