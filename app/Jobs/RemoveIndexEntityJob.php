<?php

namespace App\Jobs;

use App\Services\DashboardSearchIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveIndexEntityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $entityType, public $entityId)
    {
    }

    public function handle(DashboardSearchIndexer $indexer): void
    {
        $indexer->removeEntity($this->entityType, $this->entityId);
    }
}
