<?php

namespace App\Events;

use App\Events\Concerns\UsesDispatchBroadcastConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverOnlineStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, UsesDispatchBroadcastConnection;

    public function __construct(public int $companyId, public int $driverId, public array $payload)
    {
        $this->useDispatchBroadcastConnection();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("company.{$this->companyId}.dispatch"),
            new PrivateChannel("driver.{$this->driverId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DriverOnlineStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
