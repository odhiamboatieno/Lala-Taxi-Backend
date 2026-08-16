<?php

namespace App\Console\Commands;

use App\Services\DriverComplianceService;
use Illuminate\Console\Command;

class SyncDocumentStatuses extends Command
{
    protected $signature = 'documents:sync-status';
    protected $description = 'Expire driver and company documents whose expiry dates have passed.';

    public function handle(DriverComplianceService $complianceService): int
    {
        $complianceService->syncDocumentExpiryStatuses();
        $this->info('Document statuses synced successfully.');
        return self::SUCCESS;
    }
}
