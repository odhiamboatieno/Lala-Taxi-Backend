<?php

namespace App\Jobs;

use App\Models\TripRequest;
use App\Services\Dispatch\TripDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireTripRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $tripRequestId)
    {
    }

    public function handle(TripDispatchService $dispatchService): void
    {
        $tripRequest = TripRequest::find($this->tripRequestId);

        if ($tripRequest) {
            $dispatchService->expireTripRequest($tripRequest);
        }
    }
}
