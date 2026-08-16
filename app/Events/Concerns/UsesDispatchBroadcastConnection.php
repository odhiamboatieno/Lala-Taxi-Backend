<?php

namespace App\Events\Concerns;

use Illuminate\Broadcasting\InteractsWithBroadcasting;

trait UsesDispatchBroadcastConnection
{
    use InteractsWithBroadcasting;

    protected function useDispatchBroadcastConnection(): void
    {
        $this->broadcastVia('reverb');
    }
}
