<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $tripId, public int $riderId, public ?int $driverId, public ?int $companyId, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("trip.{$this->tripId}"),
            new PrivateChannel("trip.rider.{$this->riderId}"),
        ];

        if ($this->driverId) {
            $channels[] = new PrivateChannel("driver.{$this->driverId}");
        }

        if ($this->companyId) {
            $channels[] = new PrivateChannel("company.{$this->companyId}.dispatch");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'TripCompleted';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
