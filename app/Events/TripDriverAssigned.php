<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripDriverAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $riderId, public int $tripId, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("trip.rider.{$this->riderId}"),
            new PrivateChannel("trip.{$this->tripId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TripDriverAssigned';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
