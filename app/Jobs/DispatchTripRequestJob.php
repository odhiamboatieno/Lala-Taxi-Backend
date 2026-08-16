<?php

namespace App\Jobs;

use App\Models\Trips;
use App\Services\Dispatch\TripDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchTripRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $tripId)
    {
    }

    public function handle(TripDispatchService $dispatchService): void
    {
        $trip = Trips::find($this->tripId);

        if ($trip) {
            $dispatchService->dispatchTripRequests($trip);
        }
    }
}
