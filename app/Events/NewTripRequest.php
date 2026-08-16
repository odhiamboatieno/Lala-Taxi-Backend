<?php

namespace App\Events;

use App\Events\Concerns\UsesDispatchBroadcastConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTripRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, UsesDispatchBroadcastConnection;

    public function __construct(public int $driverId, public array $payload)
    {
        $this->useDispatchBroadcastConnection();
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("driver.{$this->driverId}")];
    }

    public function broadcastAs(): string
    {
        return 'NewTripRequest';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
