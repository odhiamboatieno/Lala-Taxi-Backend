<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripSearchingStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $riderId, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("trip.rider.{$this->riderId}")];
    }

    public function broadcastAs(): string
    {
        return 'TripSearchingStarted';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
