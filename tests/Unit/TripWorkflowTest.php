<?php

namespace Tests\Unit;

use App\Support\TripWorkflow;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TripWorkflowTest extends TestCase
{
    public function test_valid_transition_sequence_is_allowed(): void
    {
        $this->assertTrue(TripWorkflow::canTransition('searching_driver', 'driver_assigned'));
        $this->assertTrue(TripWorkflow::canTransition('driver_assigned', 'driver_arriving'));
        $this->assertTrue(TripWorkflow::canTransition('driver_arriving', 'arrived'));
        $this->assertTrue(TripWorkflow::canTransition('arrived', 'started'));
        $this->assertTrue(TripWorkflow::canTransition('started', 'completed'));
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TripWorkflow::assertTransition('searching_driver', 'completed');
    }
}
