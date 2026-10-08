<?php

namespace App\Observers;

use App\Jobs\ReindexEntityJob;
use App\Jobs\RemoveIndexEntityJob;

/**
 * Generic observer registered for every model that backs an indexed entity
 * type. The event is pushed to the queue so admin writes are never slowed
 * down by index maintenance.
 */
class DashboardSearchObserver
{
    public function __construct(private string $entityType)
    {
    }

    public function saved($model): void
    {
        if ($model->getKey() === null) {
            return;
        }
        ReindexEntityJob::dispatch($this->entityType, $model->getKey());
    }

    public function deleted($model): void
    {
        if ($model->getKey() === null) {
            return;
        }
        RemoveIndexEntityJob::dispatch($this->entityType, $model->getKey());
    }

    public function restored($model): void
    {
        $this->saved($model);
    }
}
