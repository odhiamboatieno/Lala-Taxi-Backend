<?php

namespace App\Jobs;

use App\Services\Dispatch\TripDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdvanceRideRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $requestId)
    {
    }

    public function handle(TripDispatchService $dispatchService): void
    {
        $dispatchService->advanceRideRequestById($this->requestId);
    }
}
