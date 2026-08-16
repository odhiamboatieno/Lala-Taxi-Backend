<?php

namespace App\Events;

use App\Events\Concerns\UsesDispatchBroadcastConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverRatingTriggerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, UsesDispatchBroadcastConnection;

    public function __construct(
        public int $driverId,
        public int $tripId,
        public array $payload = []
    ) {
        $this->useDispatchBroadcastConnection();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("driver.{$this->driverId}"),
            new PrivateChannel("trip.{$this->tripId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DriverRatingTriggerEvent';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'driver_id' => $this->driverId,
            'trip_id' => $this->tripId,
            'show_rating' => true,
            'already_rated' => false,
            'reason' => 'payment_completed',
        ], $this->payload);
    }
}
