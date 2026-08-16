<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripAssignedToOtherDriver implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $driverId, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("driver.{$this->driverId}")];
    }

    public function broadcastAs(): string
    {
        return 'TripAssignedToOtherDriver';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
