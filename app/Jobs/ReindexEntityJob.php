<?php

namespace App\Jobs;

use App\Services\DashboardSearchIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReindexEntityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $entityType, public $entityId)
    {
    }

    public function handle(DashboardSearchIndexer $indexer): void
    {
        $indexer->indexEntity($this->entityType, $this->entityId);
    }
}
