<?php

namespace App\Jobs;

use App\Services\DashboardSearchIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReindexAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public bool $rebuild = false)
    {
    }

    public function handle(DashboardSearchIndexer $indexer): void
    {
        $indexer->syncAll($this->rebuild);
    }
}
