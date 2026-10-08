<?php

namespace App\Console\Commands;

use App\Services\DashboardSearchIndexer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SearchIndexAudit extends Command
{
    protected $signature = 'search:index:audit
        {--fix : Re-sync types whose drift is non-zero}';

    protected $description = 'Report drift between live records and the dashboard search index';

    public function handle(DashboardSearchIndexer $indexer): int
    {
        if (!Schema::hasTable('dashboard_search_index')) {
            $this->error('The dashboard_search_index table is missing. Run migrations first.');

            return self::FAILURE;
        }

        $report = $indexer->driftReport();
        foreach ($report['types'] as $type => $info) {
            $this->line($info['diff'] === 0
                ? "PASS {$type} ({$info['table']}: {$info['indexed']} indexed / {$info['source']} source)"
                : "DRIFT {$type} ({$info['table']}: {$info['indexed']} indexed / {$info['source']} source, delta {$info['diff']})");
        }

        if ($report['ok']) {
            $this->info('Index is in sync.');

            return self::SUCCESS;
        }

        if ($this->option('fix')) {
            $this->warn('Rebuilding drifted sources...');
            foreach ($report['types'] as $type => $info) {
                if ($info['diff'] !== 0) {
                    $indexer->syncType($type);
                }
            }
            $this->info('Drift resolved.');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}