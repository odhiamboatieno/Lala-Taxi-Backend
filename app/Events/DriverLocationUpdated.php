<?php

namespace App\Events;

use App\Events\Concerns\UsesDispatchBroadcastConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, UsesDispatchBroadcastConnection;

    public function __construct(
        public ?int $tripId,
        public ?int $riderId,
        public ?int $companyId,
        public array $payload
    ) {
        $this->useDispatchBroadcastConnection();
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->tripId) {
            $channels[] = new PrivateChannel("trip.{$this->tripId}");
        }

        if ($this->riderId) {
            $channels[] = new PrivateChannel("trip.rider.{$this->riderId}");
        }

        if ($this->companyId) {
            $channels[] = new PrivateChannel("company.{$this->companyId}.dispatch");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'DriverLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
